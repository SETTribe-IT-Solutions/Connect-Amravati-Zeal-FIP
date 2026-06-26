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
            
            // Manage custom selection or targeted user notification triggers
            if ($audience_type === 'Custom' && isset($_POST['custom_users']) && is_array($_POST['custom_users'])) {
                foreach ($_POST['custom_users'] as $partUserId) {
                    $pUserId = (int)$partUserId;
                    $conn->query("INSERT INTO meeting_participants (meeting_id, user_id) VALUES ($meeting_id, $pUserId)");
                    createNotification($conn, 'Meeting', 'New Meeting: ' . $title, "You have been invited to a meeting on " . $meeting_date . " at " . $meeting_time, $meeting_id, $userId, $pUserId);
                }
            } else {
                // Bulk notify targeted levels
                $roleLevelFilter = '';
                if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                
                $usersRes = $conn->query("SELECT user_id FROM users " . $roleLevelFilter);
                if ($usersRes) {
                    while ($row = $usersRes->fetch_assoc()) {
                        $targetUserId = (int)$row['user_id'];
                        createNotification($conn, 'Meeting', 'New Meeting: ' . $title, "A new meeting has been scheduled: " . $title . " on " . $meeting_date, $meeting_id, $userId, $targetUserId);
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
    
    $whereClause = "WHERE audience_type = 'All' 
                    OR created_by = $userId 
                    OR (audience_type = 'L1' AND $userLevel == 1)
                    OR (audience_type = 'L2' AND $userLevel == 2)
                    OR (audience_type = 'L3' AND $userLevel == 3)
                    OR meeting_id IN (SELECT meeting_id FROM meeting_participants WHERE user_id = $userId)";
                    
    $res = $conn->query("SELECT * FROM meetings $whereClause");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            // Determine Color Code for Calendar events based on status
            // Green = Upcoming, Orange = Starting Soon, Blue = Live, Gray = Completed, Red = Missed / Cancelled
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
                    'location' => $row['location']
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
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
