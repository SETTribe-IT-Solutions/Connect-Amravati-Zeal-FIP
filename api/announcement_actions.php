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
$sName  = $_SESSION['user_name'] ?? 'Employee';

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
function createNotification($conn, $type, $title, $message, $announcement_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, announcement_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $announcement_id, $sender_id, $receiver_id);
        $stmt->execute();
        $stmt->close();
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Check role permission
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

// Allow 'list' action for all logged-in users, but restrict other write actions to L1 only
if ($action !== 'list') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions']);
        exit;
    }
}

if ($action === 'list') {
    $announcements = [];
    // If not L1, only fetch announcements that are Published and match their role level/audience
    if (!$isL1) {
        $level = match($sRole) {
            'SDO', 'Tehsildar', 'BDO' => 'L2',
            'Talathi', 'Gramsevak' => 'L3',
            default => 'L3'
        };
        // Show announcements matching audience 'All', their level, or if specifically targeted in announcement_recipients
        $query = "SELECT DISTINCT a.* FROM announcements a 
                  LEFT JOIN announcement_recipients ar ON a.announcement_id = ar.announcement_id 
                  WHERE a.status = 'Published' AND (a.audience_type = 'All' OR a.audience_type = ? OR ar.user_id = ?)";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("si", $level, $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $announcements[] = $row;
            }
            $stmt->close();
        }
    } else {
        // L1 can view all announcements (including Drafts and Scheduled)
        $res = $conn->query("SELECT * FROM announcements WHERE status != 'Archived' ORDER BY announcement_id DESC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $announcements[] = $row;
            }
        }
    }
    echo json_encode(['status' => 'success', 'announcements' => $announcements]);
    exit;
}

if ($action === 'create' || $action === 'edit') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';
    $audience_type = $_POST['audience_type'] ?? 'All';
    $publish_now = $_POST['publish_now'] ?? '0'; // 1 = Now, 2 = Schedule, 0 = Draft
    $publish_date = $_POST['publish_date'] ?? null;
    $expiry_date = $_POST['expiry_date'] ?? null;
    
    if (empty($title) || empty($description)) {
        echo json_encode(['status' => 'error', 'message' => 'Title and description are required']);
        exit;
    }
    
    // Status resolution based on role and options
    if ($publish_now == '1') {
        if (!$isCollector) {
            $status = 'Draft'; // L1 can only save drafts or submit, Collector publishes
        } else {
            $status = 'Published';
            $publish_date = date('Y-m-d');
        }
    } elseif ($publish_now == '2') {
        if (!$isCollector) {
            $status = 'Draft';
        } else {
            $status = 'Scheduled';
        }
    } else {
        $status = 'Draft';
    }
    
    // File upload
    $attachment = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            $err_msg = 'File upload failed: ';
            switch ($_FILES['attachment']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $err_msg .= 'The uploaded file exceeds the maximum allowed upload size (' . ini_get('upload_max_filesize') . ').';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $err_msg .= 'The file was only partially uploaded.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $err_msg .= 'Missing a temporary folder on the server.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $err_msg .= 'Failed to write file to disk.';
                    break;
                default:
                    $err_msg .= 'Unknown upload error.';
            }
            echo json_encode(['status' => 'error', 'message' => $err_msg]);
            exit;
        }

        $upload_dir = '../uploads/announcements/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'mp3', 'wav', 'ogg', 'm4a', 'mp4', 'webm', 'avi', 'mov', 'mkv'];
        if (!in_array($file_ext, $allowed_exts)) {
            echo json_encode(['status' => 'error', 'message' => 'Unsupported attachment type. Allowed types: PDF, Word, Excel, Images, Audio, and Video.']);
            exit;
        }

        // Limit size to 50MB
        $max_size = 50 * 1024 * 1024;
        if ($_FILES['attachment']['size'] > $max_size) {
            echo json_encode(['status' => 'error', 'message' => 'File size exceeds the maximum limit of 50MB.']);
            exit;
        }

        // MIME Type validation
        if (function_exists('mime_content_type')) {
            $file_mime = mime_content_type($_FILES['attachment']['tmp_name']);
            $allowed_mimes = [
                // Documents
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                // Images
                'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                // Audio
                'audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/x-m4a', 'audio/aac', 'audio/mp4', 'audio/webm',
                // Video
                'video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/msvideo', 'video/x-msvideo', 'video/quicktime', 'video/x-matroska', 'video/x-ms-wmv'
            ];
            $is_valid_mime = in_array($file_mime, $allowed_mimes) || 
                             strpos($file_mime, 'image/') === 0 || 
                             strpos($file_mime, 'audio/') === 0 || 
                             strpos($file_mime, 'video/') === 0;

            if (!$is_valid_mime) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid file content (MIME type mismatch).']);
                exit;
            }
        }

        $new_filename = 'ANNC_' . uniqid() . '.' . $file_ext;
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $new_filename)) {
            $attachment = 'uploads/announcements/' . $new_filename;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save the uploaded file.']);
            exit;
        }
    }
    
    if ($action === 'create') {
        $stmt = $conn->prepare("INSERT INTO announcements (title, category, description, priority, audience_type, attachment, publish_date, expiry_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssssssi", $title, $category, $description, $priority, $audience_type, $attachment, $publish_date, $expiry_date, $status, $userId);
            if ($stmt->execute()) {
                $announcement_id = $stmt->insert_id;
                $stmt->close();
                
                // Track selected custom users if Custom targeting is selected
                if ($audience_type === 'Custom' && isset($_POST['custom_users']) && is_array($_POST['custom_users'])) {
                    foreach ($_POST['custom_users'] as $customUserId) {
                        $cUserId = (int)$customUserId;
                        $conn->query("INSERT INTO announcement_recipients (announcement_id, user_id, is_read) VALUES ($announcement_id, $cUserId, 0)");
                        
                        if ($status === 'Published') {
                            createNotification($conn, 'Announcement', 'New Announcement: ' . $title, "New announcement has been published: " . substr($description, 0, 100) . "...", $announcement_id, $userId, $cUserId);
                        }
                    }
                } elseif ($status === 'Published') {
                    // Populate announcements recipients table for targeted roles
                    $roleLevelFilter = '';
                    if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                    elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                    elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                    
                    $usersQuery = "SELECT user_id FROM users " . $roleLevelFilter;
                    $usersResult = $conn->query($usersQuery);
                    if ($usersResult) {
                        while ($row = $usersResult->fetch_assoc()) {
                            $targetUserId = (int)$row['user_id'];
                            $conn->query("INSERT INTO announcement_recipients (announcement_id, user_id, is_read) VALUES ($announcement_id, $targetUserId, 0)");
                            createNotification($conn, 'Announcement', 'New Announcement: ' . $title, "New announcement has been published: " . substr($description, 0, 100) . "...", $announcement_id, $userId, $targetUserId);
                        }
                    }
                }
                
                logAction($conn, $userId, 'Announcement', 'Create', $announcement_id, null, json_encode(['title' => $title, 'status' => $status]));
                echo json_encode(['status' => 'success', 'message' => 'Announcement created successfully', 'id' => $announcement_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database execution failed: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Prepared statement failed: ' . $conn->error]);
        }
    } else {
        // Edit announcement
        $announcement_id = (int)$_POST['announcement_id'];
        if (!$isCollector) {
            // L1 cannot edit published announcements or someone else's drafts
            $chk = $conn->query("SELECT created_by, status FROM announcements WHERE announcement_id = $announcement_id")->fetch_assoc();
            if ($chk['created_by'] != $userId || $chk['status'] === 'Published') {
                echo json_encode(['status' => 'error', 'message' => 'You do not have permission to edit this announcement']);
                exit;
            }
        }
        
        // Retrieve old values for audit logging
        $old_data = $conn->query("SELECT * FROM announcements WHERE announcement_id = $announcement_id")->fetch_assoc();
        
        $attachment_sql = "";
        if ($attachment !== null) {
            $attachment_sql = ", attachment = '" . $conn->real_escape_string($attachment) . "'";
        }
        
        $stmt = $conn->prepare("UPDATE announcements SET title=?, category=?, description=?, priority=?, audience_type=?, publish_date=?, expiry_date=?, status=?, updated_by=? $attachment_sql WHERE announcement_id=?");
        if ($stmt) {
            $stmt->bind_param("ssssssssii", $title, $category, $description, $priority, $audience_type, $publish_date, $expiry_date, $status, $userId, $announcement_id);
            if ($stmt->execute()) {
                $stmt->close();
                
                // Clear old recipients
                $conn->query("DELETE FROM announcement_recipients WHERE announcement_id = $announcement_id");
                
                // Insert new ones
                if ($audience_type === 'Custom' && isset($_POST['custom_users']) && is_array($_POST['custom_users'])) {
                    foreach ($_POST['custom_users'] as $customUserId) {
                        $cUserId = (int)$customUserId;
                        $conn->query("INSERT INTO announcement_recipients (announcement_id, user_id, is_read) VALUES ($announcement_id, $cUserId, 0)");
                        if ($status === 'Published') {
                            createNotification($conn, 'Announcement', 'New Announcement: ' . $title, "New announcement has been published: " . substr($description, 0, 100) . "...", $announcement_id, $userId, $cUserId);
                        }
                    }
                } elseif ($status === 'Published') {
                    $roleLevelFilter = '';
                    if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                    elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                    elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                    
                    $usersQuery = "SELECT user_id FROM users " . $roleLevelFilter;
                    $usersResult = $conn->query($usersQuery);
                    if ($usersResult) {
                        while ($row = $usersResult->fetch_assoc()) {
                            $targetUserId = (int)$row['user_id'];
                            $conn->query("INSERT INTO announcement_recipients (announcement_id, user_id, is_read) VALUES ($announcement_id, $targetUserId, 0)");
                            createNotification($conn, 'Announcement', 'New Announcement: ' . $title, "New announcement has been published: " . substr($description, 0, 100) . "...", $announcement_id, $userId, $targetUserId);
                        }
                    }
                }
                
                logAction($conn, $userId, 'Announcement', 'Edit', $announcement_id, json_encode($old_data), json_encode(['title' => $title, 'status' => $status]));
                echo json_encode(['status' => 'success', 'message' => 'Announcement updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database execution failed: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Prepared statement failed: ' . $conn->error]);
        }
    }
}
elseif ($action === 'delete') {
    $announcement_id = (int)$_POST['announcement_id'];
    
    // Check permission
    if (!$isCollector) {
        $chk = $conn->query("SELECT created_by FROM announcements WHERE announcement_id = $announcement_id")->fetch_assoc();
        if ($chk['created_by'] != $userId) {
            echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions to delete']);
            exit;
        }
    }
    
    $old_data = $conn->query("SELECT * FROM announcements WHERE announcement_id = $announcement_id")->fetch_assoc();
    
    // Perform soft delete by moving status to Archived (or hard delete if needed, let's archive to maintain database audit constraints)
    $stmt = $conn->prepare("UPDATE announcements SET status = 'Archived' WHERE announcement_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $announcement_id);
        if ($stmt->execute()) {
            $stmt->close();
            logAction($conn, $userId, 'Announcement', 'Delete', $announcement_id, json_encode($old_data), 'Archived');
            echo json_encode(['status' => 'success', 'message' => 'Announcement deleted (archived) successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed']);
        }
    }
}
elseif ($action === 'get_users') {
    // Get users list for Custom employee selection, grouped by role levels
    $users = [];
    $res = $conn->query("SELECT u.user_id, u.full_name, u.designation, r.role_name, r.role_level FROM users u LEFT JOIN roles r ON u.role_id = r.role_id WHERE u.status = 'Active' ORDER BY r.role_level, u.full_name");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $users[] = $row;
        }
    }
    echo json_encode(['status' => 'success', 'users' => $users]);
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
