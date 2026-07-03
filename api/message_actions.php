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
function createNotification($conn, $type, $title, $message, $message_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, message_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $message_id, $sender_id, $receiver_id);
        $stmt->execute();
        $stmt->close();
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Check permission: Collector and L1 can Send Messages
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

if ($action === 'send') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions to send messages']);
        exit;
    }
    
    $subject = trim($_POST['subject'] ?? '');
    $message_body = trim($_POST['message_body'] ?? '');
    $audience_type = $_POST['audience_type'] ?? 'All'; // All, L1, L2, L3, Specific
    $recipient_user_id = (int)($_POST['recipient_user_id'] ?? 0);
    $parent_message_id = isset($_POST['parent_message_id']) ? (int)$_POST['parent_message_id'] : null;
    $is_forwarded = isset($_POST['is_forwarded']) ? (int)$_POST['is_forwarded'] : 0;
    
    if (empty($subject) || empty($message_body)) {
        echo json_encode(['status' => 'error', 'message' => 'Subject and message body are required']);
        exit;
    }
    
    // File upload
    $attachment_path = null;
    if (isset($_FILES['message_file']) && $_FILES['message_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/messages/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['message_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'MSG_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['message_file']['tmp_name'], $upload_dir . $new_filename)) {
                $attachment_path = 'uploads/messages/' . $new_filename;
            }
        }
    }
    
    // Insert into shared_messages
    $stmt = $conn->prepare("INSERT INTO shared_messages (subject, message_body, sender_id, attachment_path, parent_message_id, is_forwarded) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssisii", $subject, $message_body, $userId, $attachment_path, $parent_message_id, $is_forwarded);
        if ($stmt->execute()) {
            $message_id = $stmt->insert_id;
            $stmt->close();
            
            // Build receivers list
            $receivers = [];
            if ($audience_type === 'Specific' && $recipient_user_id > 0) {
                $receivers[] = $recipient_user_id;
            } else {
                $roleLevelFilter = '';
                if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                
                $res = $conn->query("SELECT user_id FROM users " . $roleLevelFilter);
                if ($res) {
                    while ($r = $res->fetch_assoc()) {
                        $receivers[] = (int)$r['user_id'];
                    }
                }
            }
            
            // Insert into message_recipients & notify
            foreach ($receivers as $recUserId) {
                // Skip sender
                if ($recUserId === $userId) continue;
                
                $conn->query("INSERT INTO message_recipients (message_id, user_id, is_delivered, delivered_at) VALUES ($message_id, $recUserId, 1, NOW())");
                createNotification($conn, 'Message', 'New Message: ' . $subject, "You have received a new message from " . $_SESSION['user_name'], $message_id, $userId, $recUserId);
            }
            
            logAction($conn, $userId, 'Shared Message', 'Send', $message_id, null, json_encode(['subject' => $subject, 'receivers_count' => count($receivers)]));
            echo json_encode(['status' => 'success', 'message' => 'Message sent successfully', 'id' => $message_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepared statement failed: ' . $conn->error]);
    }
}
elseif ($action === 'mark_read') {
    $message_id = (int)($_POST['message_id'] ?? $_GET['message_id'] ?? 0);
    if ($message_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE message_recipients SET is_read = 1, read_at = NOW() WHERE message_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $message_id, $userId);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}
elseif ($action === 'list') {
    $folder = $_POST['folder'] ?? $_GET['folder'] ?? 'inbox';
    $messages = [];
    
    if ($folder === 'inbox') {
        $q = "SELECT sm.*, mr.is_read, u.full_name AS sender_name 
              FROM shared_messages sm 
              JOIN message_recipients mr ON sm.message_id = mr.message_id 
              LEFT JOIN users u ON sm.sender_id = u.user_id 
              WHERE mr.user_id = $userId 
              ORDER BY sm.created_at DESC";
        $res = $conn->query($q);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $messages[] = [
                    'message_id' => (int)$row['message_id'],
                    'subject' => $row['subject'],
                    'message_body' => $row['message_body'],
                    'sender_name' => $row['sender_name'] ?: 'System',
                    'attachment_path' => $row['attachment_path'],
                    'parent_message_id' => $row['parent_message_id'] ? (int)$row['parent_message_id'] : null,
                    'is_forwarded' => (int)$row['is_forwarded'],
                    'created_at' => $row['created_at'],
                    'is_read' => (int)$row['is_read']
                ];
            }
        }
    } else {
        $q = "SELECT sm.*, GROUP_CONCAT(u.full_name SEPARATOR ', ') AS recipient_names 
              FROM shared_messages sm 
              LEFT JOIN message_recipients mr ON sm.message_id = mr.message_id 
              LEFT JOIN users u ON mr.user_id = u.user_id 
              WHERE sm.sender_id = $userId 
              GROUP BY sm.message_id 
              ORDER BY sm.created_at DESC";
        $res = $conn->query($q);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $messages[] = [
                    'message_id' => (int)$row['message_id'],
                    'subject' => $row['subject'],
                    'message_body' => $row['message_body'],
                    'recipient_names' => $row['recipient_names'] ?: 'All Employees',
                    'attachment_path' => $row['attachment_path'],
                    'parent_message_id' => $row['parent_message_id'] ? (int)$row['parent_message_id'] : null,
                    'is_forwarded' => (int)$row['is_forwarded'],
                    'created_at' => $row['created_at']
                ];
            }
        }
    }
    
    echo json_encode(['status' => 'success', 'messages' => $messages]);
    exit;
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
