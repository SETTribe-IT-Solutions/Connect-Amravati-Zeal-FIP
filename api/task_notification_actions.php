<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';
$sName  = $_SESSION['user_name'] ?? 'Employee';

$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

// Helper: Audit Logging
function logAuditAction($conn, $userId, $task_id, $action, $remarks) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $module = 'Task';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) VALUES (?, ?, ?, ?, NULL, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ississs", $userId, $module, $action, $task_id, $remarks, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper: Create notification
function createTaskNotification($conn, $type, $title, $message, $task_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, task_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $task_id, $sender_id, $receiver_id);
        $stmt->execute();
        $notification_id = $stmt->insert_id;
        $stmt->close();
        
        // Log notification delivery
        $channel = 'System';
        $delivery_status = 'Sent';
        $log_sql = "INSERT INTO notification_delivery_logs (notification_id, channel, delivery_status, delivery_time) VALUES ($notification_id, '$channel', '$delivery_status', NOW())";
        $conn->query($log_sql);
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$taskId = (int)($_POST['task_id'] ?? $_GET['task_id'] ?? 0);

if ($taskId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

// Fetch task info
$taskRes = $conn->query("SELECT * FROM tasks WHERE task_id = $taskId");
$task = $taskRes ? $taskRes->fetch_assoc() : null;

if (!$task) {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    exit;
}

$taskTitle = $task['task_title'];
$creatorId = (int)$task['created_by'];
$assignedUserId = (int)$task['assigned_user_id'];
$oldStatus = $task['status'];

if ($action === 'accept') {
    // Check if task is assigned to this user
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    // Update task status to Accepted
    $conn->query("UPDATE tasks SET status = 'Accepted' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Accepted' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Status History
    $stmt = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Accepted', ?, NOW(), 'Task accepted by employee')");
    if ($stmt) {
        $stmt->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Accepted', "$sName has accepted the task: $taskTitle");
    
    // Send Notification to creator
    createTaskNotification($conn, 'Task', 'Task Accepted', "$sName has accepted the assigned task: $taskTitle", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task accepted successfully']);
    exit;
}
elseif ($action === 'reject') {
    // Check if task is assigned to this user
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    $reason = trim($_POST['reason'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    
    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Rejection reason is mandatory']);
        exit;
    }
    if (empty($remarks)) {
        echo json_encode(['status' => 'error', 'message' => 'Detailed remarks are mandatory']);
        exit;
    }
    
    if (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Mandatory proof upload is missing']);
        exit;
    }
    
    // Handle proof file upload
    $upload_dir = '../uploads/tasks/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES['proof_file']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'];
    
    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported file format. Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP']);
        exit;
    }
    
    $new_filename = 'REJ_PROOF_' . uniqid() . '.' . $file_ext;
    $db_file_path = 'uploads/tasks/' . $new_filename;
    
    if (!move_uploaded_file($_FILES['proof_file']['tmp_name'], $upload_dir . $new_filename)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save proof upload file']);
        exit;
    }
    
    // Insert rejection proofs record
    $stmt = $conn->prepare("INSERT INTO task_rejection_proofs (task_id, user_id, rejection_reason, remarks, file_path) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iisss", $taskId, $userId, $reason, $remarks, $db_file_path);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update task status to Pending Verification
    $conn->query("UPDATE tasks SET status = 'Pending Verification' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Pending Verification' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Save document to task_documents as well for backward compatibility
    $stmtDoc = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
    if ($stmtDoc) {
        $orig_name = $_FILES['proof_file']['name'];
        $stmtDoc->bind_param("isss", $taskId, $orig_name, $db_file_path, $userId);
        $stmtDoc->execute();
        $stmtDoc->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Pending Verification', ?, NOW(), ?)");
    if ($stmtHist) {
        $hist_remarks = "Rejected. Reason: $reason. Remarks: $remarks";
        $stmtHist->bind_param("isis", $taskId, $oldStatus, $userId, $hist_remarks);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Save to task_remarks as well
    $stmtRem = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Pending Verification')");
    if ($stmtRem) {
        $stmtRem->bind_param("iis", $taskId, $userId, $remarks);
        $stmtRem->execute();
        $stmtRem->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Rejected', "$sName has rejected the task: $taskTitle. Reason: $reason");
    
    // Send Notification to creator
    createTaskNotification($conn, 'Task', 'Task Rejected - Verification Required', "$sName has rejected Task: $taskTitle and submitted proof for verification.", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Rejection submitted for verification successfully']);
    exit;
}
elseif ($action === 'approve_rejection') {
    // Only L1 can verify and approve rejections
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only L1 Officers/Admins can approve rejections.']);
        exit;
    }
    
    // Update task status to Rejected Approved (Rejected Approved)
    $conn->query("UPDATE tasks SET status = 'Approved Rejection' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Approved Rejection' WHERE task_id = $taskId");
    
    // Verification Logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Approve Rejection', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Approved Rejection', ?, NOW(), 'Rejection approved by manager')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Verification Approved', "Task rejection approved by manager: $taskTitle");
    
    // Send Notification to employee
    createTaskNotification($conn, 'Task', 'Task Rejection Approved', "Your rejection request for Task: $taskTitle has been approved.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task rejection approved successfully']);
    exit;
}
elseif ($action === 'reject_rejection') {
    // Only L1 can verify and deny rejections
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only L1 Officers/Admins can deny rejections.']);
        exit;
    }
    
    // Update task status to Reassigned
    $conn->query("UPDATE tasks SET status = 'Reassigned' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Reassigned' WHERE task_id = $taskId");
    
    // Verification Logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Deny Rejection', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Reassigned', ?, NOW(), 'Rejection denied. Task reassigned.')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Verification Denied', "Task rejection denied by manager: $taskTitle");
    
    // Send Notification to employee
    createTaskNotification($conn, 'Task', 'Task Rejection Denied', "Your rejection request for Task: $taskTitle has been denied. Please complete the assigned task.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task rejection denied. Task reassigned to employee.']);
    exit;
}
elseif ($action === 'complete') {
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    $achievements = trim($_POST['achievements'] ?? '');
    if (empty($achievements)) {
        echo json_encode(['status' => 'error', 'message' => 'Achievement details are mandatory']);
        exit;
    }
    
    $attachment_path = null;
    if (isset($_FILES['complete_file']) && $_FILES['complete_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/tasks/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['complete_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'];
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'CMP_' . uniqid() . '.' . $file_ext;
            $attachment_path = 'uploads/tasks/' . $new_filename;
            move_uploaded_file($_FILES['complete_file']['tmp_name'], $upload_dir . $new_filename);
            
            // Insert doc
            $stmtDoc = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
            if ($stmtDoc) {
                $orig_name = $_FILES['complete_file']['name'];
                $stmtDoc->bind_param("isss", $taskId, $orig_name, $attachment_path, $userId);
                $stmtDoc->execute();
                $stmtDoc->close();
            }
        }
    }
    
    $conn->query("UPDATE tasks SET status = 'Completed', completion_date = NOW() WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Completed' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Completed', ?, NOW(), ?)");
    if ($stmtHist) {
        $stmtHist->bind_param("isis", $taskId, $oldStatus, $userId, $achievements);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Task Remarks
    $stmtRem = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Completed')");
    if ($stmtRem) {
        $stmtRem->bind_param("iis", $taskId, $userId, $achievements);
        $stmtRem->execute();
        $stmtRem->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Completed', "$sName has completed task: $taskTitle");
    
    // Notification to creator
    createTaskNotification($conn, 'Task', 'Task Completed', "$sName has completed Task: $taskTitle.", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task marked as completed successfully']);
    exit;
}
elseif ($action === 'verify') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $conn->query("UPDATE tasks SET status = 'Verified' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Verified' WHERE task_id = $taskId");
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Verified', ?, NOW(), 'Task marked as verified by manager')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Verified', "Task verified by manager: $taskTitle");
    
    // Notification to employee
    createTaskNotification($conn, 'Task', 'Task Verified', "Your completed Task: $taskTitle has been verified.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task marked as verified successfully']);
    exit;
}
elseif ($action === 'request_clarification') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $msg = trim($_POST['message'] ?? 'Clarification requested for your task rejection.');
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Request Clarification', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Clarification Request', "Clarification requested on task: $taskTitle");
    
    // Notification to employee
    createTaskNotification($conn, 'Task', 'Clarification Request', $msg, $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Clarification request sent to employee']);
    exit;
}
elseif ($action === 'get_rejection_details') {
    // Return details from task_rejection_proofs
    $rejRes = $conn->query("SELECT r.*, u.full_name FROM task_rejection_proofs r LEFT JOIN users u ON r.user_id = u.user_id WHERE r.task_id = $taskId ORDER BY r.uploaded_at DESC LIMIT 1");
    $rej = $rejRes ? $rejRes->fetch_assoc() : null;
    
    if ($rej) {
        echo json_encode(['status' => 'success', 'rejection' => $rej]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Rejection details not found']);
    }
    exit;
}
elseif ($action === 'take_action') {
    // 1. Fetch assigned user email and details
    $emailRes = $conn->query("
        SELECT t.task_title, u.email, u.full_name, u.user_id as assignee_id 
        FROM tasks t 
        JOIN task_assignments ta ON t.task_id = ta.task_id 
        JOIN users u ON ta.assigned_to_user = u.user_id 
        WHERE t.task_id = $taskId LIMIT 1
    ");
    $taskData = $emailRes ? $emailRes->fetch_assoc() : null;

    if ($taskData && !empty($taskData['email'])) {
        require_once __DIR__ . '/../include/mailer.php';
        
        $assigneeId = (int)$taskData['assignee_id'];

        // Check how many overdue tasks the user has in the current quarter
        $countRes = $conn->query("
            SELECT COUNT(*) as overdue_count 
            FROM tasks t 
            JOIN task_assignments ta ON t.task_id = ta.task_id 
            WHERE ta.assigned_to_user = $assigneeId 
            AND t.status = 'Overdue' 
            AND QUARTER(t.created_at) = QUARTER(CURDATE()) 
            AND YEAR(t.created_at) = YEAR(CURDATE())
        ");
        $countData = $countRes ? $countRes->fetch_assoc() : ['overdue_count' => 0];
        $overdueCount = (int)$countData['overdue_count'];

        $to = $taskData['email'];
        
        if ($overdueCount > 5) {
            $subject = "TERMINATION NOTICE: Critical Overdue Limit Reached";
            $email_html = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ef4444; border-radius: 8px; background-color: #fef2f2;'>
                    <h2 style='color: #991b1b; text-align: center; text-transform: uppercase;'>Official Termination Notice</h2>
                    <p>Dear {$taskData['full_name']},</p>
                    <p>Our records indicate that you have accumulated <strong>$overdueCount overdue tasks</strong> in the current quarter, which exceeds the acceptable threshold.</p>
                    <p>Specifically, administrative action was taken today regarding the following overdue task:</p>
                    <div style='background-color: #ffffff; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0;'>
                        <p style='margin: 0; font-weight: bold;'>Task Title: {$taskData['task_title']}</p>
                        <p style='margin: 5px 0 0 0; color: #b91c1c;'>Status: Overdue</p>
                    </div>
                    <p style='color: #7f1d1d; font-weight: bold;'>As a result of chronic negligence, you are hereby issued a 10-day suspension/termination notice.</p>
                    <p>Please contact HR and your supervisor immediately to discuss this matter.</p>
                    <br>
                    <p>Sincerely,</p>
                    <p><strong>Administration</strong><br>Connect Amravati</p>
                </div>
            </body>
            </html>
            ";
            $messageResponse = "Termination email dispatched successfully. Candidate has > 5 overdue tasks ($overdueCount).";
        } else {
            $subject = "STRICT WARNING: Action Taken on Overdue Task '{$taskData['task_title']}'";
            $email_html = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f59e0b; border-radius: 8px; background-color: #fffbeb;'>
                    <h2 style='color: #b45309;'>Strict Warning: Action Taken on Overdue Task</h2>
                    <p>Dear {$taskData['full_name']},</p>
                    <p>This is a strict official warning. Administrative action has been taken regarding your assigned task because it has missed its deadline:</p>
                    <div style='background-color: #ffffff; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;'>
                        <p style='margin: 0; font-weight: bold;'>Task Title: {$taskData['task_title']}</p>
                        <p style='margin: 5px 0 0 0; color: #b45309;'>Status: Overdue</p>
                    </div>
                    <p>You currently have <strong>$overdueCount overdue task(s)</strong> this quarter. Be advised that accumulating more than 5 overdue tasks in a quarter will result in an automatic 10-day suspension/termination.</p>
                    <p>Please log in to the system immediately to complete this task and prevent further escalation.</p>
                    <br>
                    <p>Sincerely,</p>
                    <p><strong>Administration</strong><br>Connect Amravati</p>
                </div>
            </body>
            </html>
            ";
            $messageResponse = "Strict warning email dispatched successfully. Candidate has $overdueCount overdue tasks.";
        }
        
        try {
            if (SMTP_ENABLED) {
                send_smtp_email(
                    $to, $subject, $email_html, 
                    SMTP_USER, SMTP_FROM_NAME, 
                    SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE
                );
            }
            
            // Log audit
            logAuditAction($conn, $userId, $taskId, 'Action Taken', "Sent overdue action email to {$to} (Quarter Count: $overdueCount)");
            
            // Add notification
            $notifTitle = $overdueCount > 5 ? 'Termination Notice' : 'Strict Warning';
            $notifMsg = $overdueCount > 5 ? "You have been issued a 10-day suspension notice due to $overdueCount overdue tasks." : "A strict warning has been issued for your overdue task: {$taskData['task_title']}";
            createTaskNotification($conn, 'Task', $notifTitle, $notifMsg, $taskId, $userId, $assigneeId);

            echo json_encode(['status' => 'success', 'message' => $messageResponse]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Action recorded, but email failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Candidate email not found or task unassigned.']);
    }
    exit;
}
elseif ($action === 'reassign_task') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $newAssigneeId = (int)($_POST['new_assignee_id'] ?? 0);
    if (!$newAssigneeId) {
        echo json_encode(['status' => 'error', 'message' => 'New assignee is required.']);
        exit;
    }

    // Get current status
    $statusRes = $conn->query("SELECT status, task_title FROM tasks WHERE task_id = $taskId LIMIT 1");
    if ($statusRes && $statusRes->num_rows > 0) {
        $row = $statusRes->fetch_assoc();
        $oldStatus = $row['status'];
        $taskTitle = $row['task_title'];

        // Update tasks table
        $conn->query("UPDATE tasks SET assigned_user_id = $newAssigneeId, status = 'Reassigned' WHERE task_id = $taskId");
        
        // Update task_assignments table
        $conn->query("UPDATE task_assignments SET assigned_to_user = $newAssigneeId, status = 'Reassigned' WHERE task_id = $taskId");
        
        // Add to task_status_history
        $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Reassigned', ?, NOW(), 'Task reassigned to new member by administrator.')");
        if ($stmtHist) {
            $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
            $stmtHist->execute();
            $stmtHist->close();
        }

        // Notify new assignee
        createTaskNotification($conn, 'Task', 'Task Reassigned', "You have been reassigned a new task: $taskTitle.", $taskId, $userId, $newAssigneeId);

        // Audit Log
        logAuditAction($conn, $userId, $taskId, 'Task Reassigned', "Task reassigned to user ID $newAssigneeId");

        echo json_encode(['status' => 'success', 'message' => 'Task reassigned successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
    }
    exit;
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}
?>
