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
function createNotification($conn, $type, $title, $message, $document_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, document_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $document_id, $sender_id, $receiver_id);
        $stmt->execute();
        $stmt->close();
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Check permission
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

if ($action === 'upload') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient permissions to upload confidential documents']);
        exit;
    }
    
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $classification_level = $_POST['classification_level'] ?? 'Confidential';
    $allow_download = isset($_POST['allow_download']) ? (int)$_POST['allow_download'] : 1;
    $allow_view = isset($_POST['allow_view']) ? (int)$_POST['allow_view'] : 1;
    $audience_type = $_POST['audience_type'] ?? 'All';
    
    if (empty($subject) || !isset($_FILES['document_file'])) {
        echo json_encode(['status' => 'error', 'message' => 'Subject and document file are required']);
        exit;
    }
    
    // File upload
    $file_path = null;
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/confidential/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'CONF_' . uniqid() . '.' . $file_ext;
            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $upload_dir . $new_filename)) {
                $file_path = 'uploads/confidential/' . $new_filename;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Unsupported file format']);
            exit;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'File upload error']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO confidential_documents (subject, description, classification_level, file_path, allow_download, allow_view, audience_type, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssssiiii", $subject, $description, $classification_level, $file_path, $allow_download, $allow_view, $audience_type, $userId);
        if ($stmt->execute()) {
            $document_id = $stmt->insert_id;
            $stmt->close();
            
            // Log upload action
            logAction($conn, $userId, 'Confidential Document', 'Upload', $document_id, null, json_encode(['subject' => $subject, 'level' => $classification_level]));
            
            // Custom recipients mapping
            if ($audience_type === 'Custom' && isset($_POST['custom_users']) && is_array($_POST['custom_users'])) {
                foreach ($_POST['custom_users'] as $audUserId) {
                    $aUserId = (int)$audUserId;
                    $conn->query("INSERT INTO confidential_document_audience (document_id, user_id) VALUES ($document_id, $aUserId)");
                    createNotification($conn, 'ConfidentialDocument', 'Confidential Document Assigned: ' . $subject, "A classified document has been shared with you: " . $subject, $document_id, $userId, $aUserId);
                }
            } else {
                // Bulk notify targeted roles
                $roleLevelFilter = '';
                if ($audience_type === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
                elseif ($audience_type === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
                elseif ($audience_type === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
                
                $usersRes = $conn->query("SELECT user_id FROM users " . $roleLevelFilter);
                if ($usersRes) {
                    while ($row = $usersRes->fetch_assoc()) {
                        $targetUserId = (int)$row['user_id'];
                        createNotification($conn, 'ConfidentialDocument', 'Confidential Document: ' . $subject, "A classified document is available: " . $subject, $document_id, $userId, $targetUserId);
                    }
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Confidential document uploaded successfully', 'id' => $document_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database execution failed: ' . $stmt->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Prepared statement failed: ' . $conn->error]);
    }
}
elseif ($action === 'log_access') {
    $document_id = (int)($_POST['document_id'] ?? $_GET['document_id'] ?? 0);
    $action_type = $_POST['action_type'] ?? $_GET['action_type'] ?? 'View'; // 'View' or 'Download'
    
    if ($document_id <= 0 || !in_array($action_type, ['View', 'Download'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
        exit;
    }
    
    // Verify document exists and user is authorized to access it
    $docRes = $conn->query("SELECT * FROM confidential_documents WHERE document_id = $document_id");
    $doc = $docRes ? $docRes->fetch_assoc() : null;
    
    if (!$doc) {
        echo json_encode(['status' => 'error', 'message' => 'Document not found']);
        exit;
    }
    
    // Check permission parameters
    if ($action_type === 'View' && $doc['allow_view'] == 0 && !$isCollector) {
        echo json_encode(['status' => 'error', 'message' => 'Viewing permission is disabled for this document']);
        exit;
    }
    if ($action_type === 'Download' && $doc['allow_download'] == 0 && !$isCollector) {
        echo json_encode(['status' => 'error', 'message' => 'Downloading permission is disabled for this document']);
        exit;
    }
    
    // Authorization rules
    $userLevel = match($sRole) {
        'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
        'SDO', 'Tehsildar', 'BDO' => 2,
        'Talathi', 'Gramsevak' => 3,
        default => 3
    };
    
    $authorized = ($isCollector 
        || $doc['created_by'] == $userId
        || $doc['audience_type'] === 'All'
        || ($doc['audience_type'] === 'L1' && $userLevel = 1)
        || ($doc['audience_type'] === 'L2' && $userLevel = 2)
        || ($doc['audience_type'] === 'L3' && $userLevel = 3)
        || $conn->query("SELECT id FROM confidential_document_audience WHERE document_id = $document_id AND user_id = $userId")->num_rows > 0
    );
    
    if (!$authorized) {
        echo json_encode(['status' => 'error', 'message' => 'You are not authorized to access this document']);
        exit;
    }
    
    // Log access
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Simple device detector
    $device = "Desktop / PC";
    if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
        $device = "Mobile Device";
    } elseif (preg_match('/ipad|playbook|silk/i', $userAgent)) {
        $device = "Tablet Device";
    }
    
    $stmt = $conn->prepare("INSERT INTO document_access_logs (document_id, user_id, action_type, ip_address, user_agent, device_info) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iissss", $document_id, $userId, $action_type, $ip, $userAgent, $device);
        $stmt->execute();
        $stmt->close();
        
        logAction($conn, $userId, 'Confidential Access', $action_type, $document_id, null, json_encode(['ip' => $ip, 'device' => $device]));
    }
    
    echo json_encode(['status' => 'success', 'file_path' => $doc['file_path'], 'allow_download' => $doc['allow_download'], 'allow_view' => $doc['allow_view']]);
    exit;
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
