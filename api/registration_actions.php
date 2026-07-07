<?php
/**
 * api/registration_actions.php - Secure registration approval and rejection backend handler
 */
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';
require_once '../include/mailer.php';

// Verify authentication and authorization
$currUserId = $_SESSION['user_id'] ?? null;
$currUserRole = $_SESSION['user_role'] ?? '';

if (!$currUserId || !in_array($currUserRole, ['Collector', 'System Administrator', 'Administrator'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only Admins and Collectors can perform this action.']);
    exit;
}

// Parse payload
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$action = $data['action'] ?? '';
$requestId = !empty($data['request_id']) ? (int)$data['request_id'] : 0;

if (!$requestId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters. Action and Request ID are required.']);
    exit;
}

try {
    // Fetch the registration request details
    $reqSql = "SELECT r.*, dept.department_name, rol.role_name, tal.taluka_name, vil.village_name 
               FROM user_registration_requests r
               LEFT JOIN departments dept ON r.department_id = dept.department_id
               LEFT JOIN roles rol ON r.role_id = rol.role_id
               LEFT JOIN talukas tal ON r.taluka_id = tal.taluka_id
               LEFT JOIN villages vil ON r.village_id = vil.village_id
               WHERE r.id = ? AND r.request_status = 'Pending' 
               LIMIT 1";
    $stmt = $conn->prepare($reqSql);
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$request) {
        echo json_encode(['status' => 'error', 'message' => 'Pending registration request not found.']);
        exit;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $browser = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);

    if ($action === 'approve') {
        // Double-check duplicates in the active users table
        $checkDup = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR employee_code = ? LIMIT 1");
        $checkDup->bind_param("ss", $request['email'], $request['employee_code']);
        $checkDup->execute();
        $dupResult = $checkDup->get_result();
        $checkDup->close();

        if ($dupResult->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Cannot approve. Username or email is already registered in the system.']);
            exit;
        }

        $conn->begin_transaction();

        // 1. Insert user into users table as Active
        $insertUserSql = "INSERT INTO users 
            (employee_code, full_name, email, mobile, department_id, role_id, district_id, taluka_id, village_id, password_hash, status, approved_by, approved_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'Active', ?, NOW())";
        
        $stmtUser = $conn->prepare($insertUserSql);
        $stmtUser->bind_param("ssssiiiisi", 
            $request['employee_code'], 
            $request['applicant_name'], 
            $request['email'], 
            $request['mobile'], 
            $request['department_id'], 
            $request['role_id'], 
            $request['taluka_id'], 
            $request['village_id'], 
            $request['password_hash'],
            $currUserId
        );
        $stmtUser->execute();
        $newUserId = $stmtUser->insert_id;
        $stmtUser->close();

        // 2. Update status of the request
        $updateReqSql = "UPDATE user_registration_requests 
                         SET request_status = 'Approved', approved_by = ?, approved_at = NOW() 
                         WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateReqSql);
        $stmtUpdate->bind_param("ii", $currUserId, $requestId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // 3. Log Audit Log for approver
        $auditSql = "INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) 
                     VALUES (?, 'User', 'Registration Approved', ?, 'Pending', 'Approved', ?, ?)";
        $stmtAudit = $conn->prepare($auditSql);
        $stmtAudit->bind_param("iiss", $currUserId, $requestId, $ip, $browser);
        $stmtAudit->execute();
        $stmtAudit->close();

        // 4. Create notification for the approved user
        $notifTitle = "Registration Approved";
        $notifMsg = "Your registration request has been approved. You can now login to the system using your credentials. Please change your password after first login.";
        $notifRedirect = "dashboard.php";
        $stmtNotif = $conn->prepare("INSERT INTO notifications (notification_type, title, message, sender_id, receiver_id, status, redirect_url) VALUES ('System', ?, ?, ?, ?, 'Unread', ?)");
        $stmtNotif->bind_param("ssiis", $notifTitle, $notifMsg, $currUserId, $newUserId, $notifRedirect);
        $stmtNotif->execute();
        $stmtNotif->close();

        $conn->commit();

        // 5. Send Approval SMTP Email
        if (SMTP_ENABLED) {
            try {
                $subject = "Your Connect Amravati Account Has Been Approved!";
                $base_url = (isset($_SERVER['HTTP_HOST'])
                    ? 'http://' . $_SERVER['HTTP_HOST'] . '/Connect-Amravati-Zeal-FIP'
                    : 'http://localhost/Connect-Amravati-Zeal-FIP');
                $loginUrl = $base_url . "/login.php";

                $email_html = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                        <h2 style='color: #16a34a;'>Dear {$request['applicant_name']},</h2>
                        <p>We are pleased to inform you that your registration request on the <strong>Connect Amravati Portal</strong> has been approved.</p>
                        <p>Your account is now activated. Here are your credentials:</p>
                        <div style='background-color: #f0fdf4; padding: 15px; border-radius: 6px; border: 1px solid #bbf7d0; margin: 20px 0;'>
                            <p style='margin: 5px 0;'><strong>Employee Code:</strong> {$request['employee_code']}</p>
                            <p style='margin: 5px 0;'><strong>Username (Email):</strong> {$request['email']}</p>
                            <p style='margin: 5px 0;'><strong>Role:</strong> {$request['role_name']}</p>
                            <p style='margin: 5px 0;'><strong>Department:</strong> {$request['department_name']}</p>
                        </div>
                        <p>Please log in using the button below. <strong>Important: Please change your password immediately after first login.</strong></p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$loginUrl}' style='display: inline-block; padding: 12px 25px; background-color: #1e3a8a; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;'>Login to Your Account</a>
                        </div>
                        <p style='margin-top: 20px; font-size: 0.85em; color: #666;'>This is an automated message. Please do not reply directly to this email.</p>
                    </div>
                </body>
                </html>
                ";

                send_smtp_email(
                    $request['email'],
                    $subject,
                    $email_html,
                    SMTP_USER,
                    SMTP_FROM_NAME,
                    SMTP_HOST,
                    SMTP_PORT,
                    SMTP_USER,
                    SMTP_PASS,
                    SMTP_SECURE,
                    5 // 5s timeout
                );
            } catch (Exception $e) {
                error_log('[Registration Approval Email Error] ' . $e->getMessage());
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Registration request approved successfully! User is now active.']);
        exit;

    } elseif ($action === 'reject') {
        $remarks = isset($data['remarks']) ? trim($data['remarks']) : '';
        if (empty($remarks)) {
            echo json_encode(['status' => 'error', 'message' => 'Rejection remarks/reason are required.']);
            exit;
        }

        $conn->begin_transaction();

        // 1. Update status of the request
        $updateReqSql = "UPDATE user_registration_requests 
                         SET request_status = 'Rejected', rejection_reason = ?, rejected_by = ?, rejected_at = NOW() 
                         WHERE id = ?";
        $stmtUpdate = $conn->prepare($updateReqSql);
        $stmtUpdate->bind_param("sii", $remarks, $currUserId, $requestId);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        // 2. Log Audit Log for rejecter
        $auditSql = "INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) 
                     VALUES (?, 'User', 'Registration Rejected', ?, 'Pending', 'Rejected', ?, ?)";
        $stmtAudit = $conn->prepare($auditSql);
        $stmtAudit->bind_param("iiss", $currUserId, $requestId, $ip, $browser);
        $stmtAudit->execute();
        $stmtAudit->close();

        // 3. Create an inactive user record in the users table so we can associate a notification record
        $inactivePass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $insertUserSql = "INSERT INTO users 
            (employee_code, full_name, email, mobile, department_id, role_id, district_id, taluka_id, village_id, password_hash, status, approved_by, approved_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 'Inactive', NULL, NULL)";
        
        $stmtUser = $conn->prepare($insertUserSql);
        $stmtUser->bind_param("ssssiiiis", 
            $request['employee_code'], 
            $request['applicant_name'], 
            $request['email'], 
            $request['mobile'], 
            $request['department_id'], 
            $request['role_id'], 
            $request['taluka_id'], 
            $request['village_id'], 
            $inactivePass
        );
        $stmtUser->execute();
        $newUserId = $stmtUser->insert_id;
        $stmtUser->close();

        // 4. Create notification for the rejected user
        $notifTitle = "Registration Rejected";
        $notifMsg = "Your registration request has been rejected. Reason: " . $remarks;
        $notifRedirect = "login.php";
        $stmtNotif = $conn->prepare("INSERT INTO notifications (notification_type, title, message, sender_id, receiver_id, status, redirect_url) VALUES ('System', ?, ?, ?, ?, 'Unread', ?)");
        $stmtNotif->bind_param("ssiis", $notifTitle, $notifMsg, $currUserId, $newUserId, $notifRedirect);
        $stmtNotif->execute();
        $stmtNotif->close();

        $conn->commit();

        // 5. Send Rejection SMTP Email
        if (SMTP_ENABLED) {
            try {
                $subject = "Connect Amravati Registration Request Rejection Notification";
                $email_html = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                        <h2 style='color: #dc2626;'>Dear {$request['applicant_name']},</h2>
                        <p>Thank you for submitting your registration request on the <strong>Connect Amravati Portal</strong>.</p>
                        <p>After reviewing your request, the administrative authority has unfortunately <strong style='color:#dc2626;'>rejected</strong> your registration application.</p>
                        <div style='background-color: #fef2f2; padding: 15px; border-radius: 6px; border: 1px solid #fecaca; margin: 20px 0;'>
                            <p style='margin: 5px 0;'><strong>Employee Code:</strong> {$request['employee_code']}</p>
                            <p style='margin: 5px 0;'><strong>Rejection Reason:</strong> {$remarks}</p>
                        </div>
                        <p>If you believe this is a mistake or have questions, please reach out to the system administrator or your reporting department authority.</p>
                        <p style='margin-top: 30px; font-size: 0.85em; color: #666;'>This is an automated message. Please do not reply directly to this email.</p>
                    </div>
                </body>
                </html>
                ";

                send_smtp_email(
                    $request['email'],
                    $subject,
                    $email_html,
                    SMTP_USER,
                    SMTP_FROM_NAME,
                    SMTP_HOST,
                    SMTP_PORT,
                    SMTP_USER,
                    SMTP_PASS,
                    SMTP_SECURE,
                    5 // 5s timeout
                );
            } catch (Exception $e) {
                error_log('[Registration Rejection Email Error] ' . $e->getMessage());
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Registration request rejected. Email notification sent.']);
        exit;
    }

} catch (Exception $e) {
    if (isset($conn) && $conn->in_transaction()) {
        $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
