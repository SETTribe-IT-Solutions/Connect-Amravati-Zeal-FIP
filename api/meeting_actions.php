<?php
session_start();
header('Content-Type: application/json');

// Database Connection
require_once __DIR__ . '/../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';

// Helper: Audit Logging
function logAction($conn, $userId, $module, $action, $recordId, $oldVal, $newVal) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ississss", $userId, $module, $action, $recordId, $oldVal, $newVal, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper: Create notification
function createNotification($conn, $type, $title, $message, $meeting_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, meeting_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $meeting_id, $sender_id, $receiver_id);
        $stmt->execute();
        $stmt->close();
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Check permission: Collector and L1 can Create/Manage Meetings. L2/L3 cannot.
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

if ($action === 'create') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions to create meetings']);
        exit;
    }
    
    $title = trim($_POST['title'] ?? '');
    $agenda = trim($_POST['agenda'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $meeting_date = $_POST['meeting_date'] ?? '';
    $meeting_time = $_POST['meeting_time'] ?? '';
    $duration = (int)($_POST['duration'] ?? 60);
    $location = trim($_POST['location'] ?? '');
    $meeting_type = $_POST['meeting_type'] ?? 'Offline';
    $meeting_link = trim($_POST['meeting_link'] ?? '');
    $meeting_password = trim($_POST['meeting_password'] ?? '');
    $meeting_platform = trim($_POST['meeting_platform'] ?? '');
    $audience_type = $_POST['audience_type'] ?? 'All';
    
    if (empty($title) || empty($meeting_date) || empty($meeting_time)) {
        echo json_encode(['status' => 'error', 'message' => 'Meeting title, date and time are required']);
        exit;
    }
    
    // File uploads
    $attachment_agenda = null;
    $attachment_supporting = null;
    $upload_dir = '../uploads/meetings/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
    
    $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    
    if (isset($_FILES['attachment_agenda']) && $_FILES['attachment_agenda']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['attachment_agenda']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $new_name = 'AGENDA_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment_agenda']['tmp_name'], $upload_dir . $new_name)) {
                $attachment_agenda = 'uploads/meetings/' . $new_name;
            }
        }
    }
    if (isset($_FILES['attachment_supporting']) && $_FILES['attachment_supporting']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['attachment_supporting']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts)) {
            $new_name = 'SUPPORT_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment_supporting']['tmp_name'], $upload_dir . $new_name)) {
                $attachment_supporting = 'uploads/meetings/' . $new_name;
            }
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO meetings (title, agenda, description, meeting_date, meeting_time, duration, location, meeting_type, meeting_link, meeting_password, meeting_platform, audience_type, attachment_agenda, attachment_supporting, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Scheduled', ?)");
    if ($stmt) {
        $stmt->bind_param("sssssissssssssi", $title, $agenda, $description, $meeting_date, $meeting_time, $duration, $location, $meeting_type, $meeting_link, $meeting_password, $meeting_platform, $audience_type, $attachment_agenda, $attachment_supporting, $userId);
        if ($stmt->execute()) {
            $meeting_id = $stmt->insert_id;
            $stmt->close();
            
            // Insert reminders triggers (24h, 1h, 15m, live)
            $conn->query("INSERT INTO meeting_reminders (meeting_id, reminder_type, sent_status) VALUES ($meeting_id, '24h', 0)");
            $conn->query("INSERT INTO meeting_reminders (meeting_id, reminder_type, sent_status) VALUES ($meeting_id, '1h', 0)");
            $conn->query("INSERT INTO meeting_reminders (meeting_id, reminder_type, sent_status) VALUES ($meeting_id, '15m', 0)");
            $conn->query("INSERT INTO meeting_reminders (meeting_id, reminder_type, sent_status) VALUES ($meeting_id, 'live', 0)");
            
            // Include mailer to send email invitations
            require_once '../include/mailer.php';

            // Gather targeted user IDs
            $targeted_users = [];
            if ($audience_type === 'Custom' && isset($_POST['custom_users']) && is_array($_POST['custom_users'])) {
                foreach ($_POST['custom_users'] as $uid) {
                    $targeted_users[] = (int)$uid;
                }
            } else {
                $roleLevelFilter = '';
                if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                
                $usersRes = $conn->query("SELECT user_id FROM users " . $roleLevelFilter);
                if ($usersRes) {
                    while ($row = $usersRes->fetch_assoc()) {
                        $targeted_users[] = (int)$row['user_id'];
                    }
                }
            }
            
            // Process participants: insert into meeting_participants, create notification, and send email
            if (!empty($targeted_users)) {
                $unique_users = array_unique($targeted_users);
                foreach ($unique_users as $pUserId) {
                    $conn->query("INSERT INTO meeting_participants (meeting_id, user_id, rsvp_status) VALUES ($meeting_id, $pUserId, 'Pending')");
                    
                    createNotification($conn, 'Meeting', 'New Meeting: ' . $title, "You have been invited to a meeting on " . $meeting_date . " at " . $meeting_time, $meeting_id, $userId, $pUserId);
                    
                    // Fetch user details for email
                    $uRes = $conn->query("SELECT email, full_name FROM users WHERE user_id = $pUserId LIMIT 1");
                    if ($uRes && $uRow = $uRes->fetch_assoc()) {
                        if (!empty($uRow['email'])) {
                            $email_html = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc; border-radius: 8px;'>
                                <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);'>
                                    <h2 style='color: #0f172a; margin-top: 0;'>Meeting Invitation: {$title}</h2>
                                    <p style='color: #334155;'>Hello {$uRow['full_name']},</p>
                                    <p style='color: #334155;'>You have been invited to a meeting via Connect Amravati.</p>
                                    <div style='background-color: #f1f5f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                                        <p style='margin: 5px 0;'><strong>Date:</strong> {$meeting_date}</p>
                                        <p style='margin: 5px 0;'><strong>Time:</strong> {$meeting_time}</p>
                                        <p style='margin: 5px 0;'><strong>Duration:</strong> {$duration} mins</p>
                                        <p style='margin: 5px 0;'><strong>Type:</strong> {$meeting_type}</p>";
                            if (!empty($location)) {
                                $email_html .= "<p style='margin: 5px 0;'><strong>Location:</strong> {$location}</p>";
                            }
                            if (!empty($meeting_link)) {
                                $email_html .= "<p style='margin: 5px 0;'><strong>Link:</strong> <a href='{$meeting_link}'>{$meeting_link}</a></p>";
                                if (!empty($meeting_password)) {
                                    $email_html .= "<p style='margin: 5px 0;'><strong>Password:</strong> {$meeting_password}</p>";
                                }
                            }
                            $email_html .= "
                                    </div>
                                    <p style='color: #334155;'>Please log in to your dashboard to RSVP for this meeting.</p>
                                    <p style='color: #64748b; font-size: 12px; margin-top: 30px;'>This is an automated message from Connect Amravati.</p>
                                </div>
                            </div>";
                            
                            try {
                                send_smtp_email(
                                    $uRow['email'],
                                    "Meeting Invitation: " . $title,
                                    $email_html,
                                    SMTP_USER,
                                    SMTP_FROM_NAME,
                                    SMTP_HOST,
                                    SMTP_PORT,
                                    SMTP_USER,
                                    SMTP_PASS,
                                    SMTP_SECURE
                                );
                            } catch (Exception $e) {
                                // Log or ignore email failure to not break creation
                                error_log("Failed to send meeting email to {$uRow['email']}: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            logAction($conn, $userId, 'Meeting', 'Create', $meeting_id, null, json_encode(['title' => $title, 'date' => $meeting_date]));
            echo json_encode(['status' => 'success', 'message' => 'Meeting scheduled successfully', 'id' => $meeting_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepared statement failed: ' . $conn->error]);
    }
}
elseif ($action === 'list_events') {
    // Return event calendar items formatted for Calendar interface
    $events = [];
    
    // Filter events visible to user based on role level and target
    $roleLevelFilter = '';
    $userLevel = match($sRole) {
        'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
        'SDO', 'Tehsildar', 'BDO' => 2,
        'Talathi', 'Gramsevak' => 3,
        default => 3
    };
    
    $whereClause = "WHERE m.audience_type = 'All' 
                    OR m.created_by = $userId 
                    OR (m.audience_type = 'L1' AND $userLevel == 1)
                    OR (m.audience_type = 'L2' AND $userLevel == 2)
                    OR (m.audience_type = 'L3' AND $userLevel == 3)
                    OR m.meeting_id IN (SELECT meeting_id FROM meeting_participants WHERE user_id = $userId)";
                    
    $res = $conn->query("
        SELECT m.*, mp.rsvp_status 
        FROM meetings m 
        LEFT JOIN meeting_participants mp ON m.meeting_id = mp.meeting_id AND mp.user_id = $userId 
        $whereClause
    ");
    
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            // Determine Color Code for Calendar events based on status
            $color = '#10B981'; // Green (default upcoming)
            if ($row['status'] === 'Starting Soon') $color = '#F59E0B'; // Orange
            elseif ($row['status'] === 'Live') $color = '#2563EB'; // Blue
            elseif ($row['status'] === 'Completed') $color = '#6B7280'; // Gray
            elseif ($row['status'] === 'Cancelled') $color = '#EF4444'; // Red
            elseif ($row['status'] === 'Missed') $color = '#EF4444'; // Red
            
            // Format time
            $start = $row['meeting_date'] . 'T' . $row['meeting_time'];
            $end = date('Y-m-d\TH:i:s', strtotime($start . ' + ' . $row['duration'] . ' minutes'));
            
            $events[] = [
                'id' => $row['meeting_id'],
                'title' => $row['title'],
                'start' => $start,
                'end' => $end,
                'color' => $color,
                'extendedProps' => [
                    'description' => $row['description'],
                    'agenda' => $row['agenda'],
                    'meeting_type' => $row['meeting_type'],
                    'meeting_platform' => $row['meeting_platform'],
                    'meeting_link' => $row['meeting_link'],
                    'meeting_password' => $row['meeting_password'],
                    'status' => $row['status'],
                    'duration' => $row['duration'],
                    'location' => $row['location'],
                    'rsvp_status' => $row['rsvp_status'] ?? 'Pending',
                    'created_by' => $row['created_by']
                ]
            ];
        }
    }
    echo json_encode($events);
}
elseif ($action === 'cancel') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $meeting_id = (int)$_POST['meeting_id'];
    $old_data = $conn->query("SELECT * FROM meetings WHERE meeting_id = $meeting_id")->fetch_assoc();
    
    $stmt = $conn->prepare("UPDATE meetings SET status = 'Cancelled' WHERE meeting_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $meeting_id);
        if ($stmt->execute()) {
            $stmt->close();
            logAction($conn, $userId, 'Meeting', 'Cancel', $meeting_id, json_encode($old_data), 'Cancelled');
            
            // Notify attendees
            $res = $conn->query("SELECT user_id FROM meeting_participants WHERE meeting_id = $meeting_id");
            if ($res) {
                while ($r = $res->fetch_assoc()) {
                    createNotification($conn, 'Meeting', 'Cancelled: ' . $old_data['title'], "The meeting scheduled for " . $old_data['meeting_date'] . " has been cancelled.", $meeting_id, $userId, (int)$r['user_id']);
                }
            }
            echo json_encode(['status' => 'success', 'message' => 'Meeting cancelled successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed']);
        }
    }
}
elseif ($action === 'submit_rsvp') {
    $meeting_id = (int)($_POST['meeting_id'] ?? 0);
    $rsvp_status = $_POST['rsvp_status'] ?? 'Pending';
    $rsvp_reason = $_POST['rsvp_reason'] ?? '';
    
    $stmt = $conn->prepare("UPDATE meeting_participants SET rsvp_status = ?, rsvp_reason = ? WHERE meeting_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ssii", $rsvp_status, $rsvp_reason, $meeting_id, $userId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'DB error']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed']);
    }
}
elseif ($action === 'get_rsvps') {
    $meeting_id = (int)($_POST['meeting_id'] ?? 0);
    
    // Check if creator
    $mRes = $conn->query("SELECT created_by FROM meetings WHERE meeting_id = $meeting_id");
    if ($mRes && $mRow = $mRes->fetch_assoc()) {
        if ($mRow['created_by'] != $userId && !$isL1) {
            echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
            exit;
        }
    }
    
    $stats = ['Joined' => 0, 'Not Joining' => 0, 'Pending' => 0];
    $participants = [];
    
    $query = "
        SELECT mp.rsvp_status, mp.rsvp_reason, u.full_name, d.department_name, r.role_name
        FROM meeting_participants mp
        JOIN users u ON mp.user_id = u.user_id
        LEFT JOIN departments d ON u.department_id = d.department_id
        LEFT JOIN roles r ON u.role_id = r.role_id
        WHERE mp.meeting_id = $meeting_id
    ";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $status = $row['rsvp_status'];
            if (isset($stats[$status])) $stats[$status]++;
            $participants[] = $row;
        }
    }
    
    echo json_encode(['status' => 'success', 'stats' => $stats, 'participants' => $participants]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
