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
$meetingId = (int)($_POST['meeting_id'] ?? $_GET['meeting_id'] ?? 0);
$action = $_POST['action'] ?? $_GET['action'] ?? ''; // 'join' or 'exit'

if ($meetingId <= 0 || !in_array($action, ['join', 'exit'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

// Fetch meeting details to compare scheduled start time
$meetingRes = $conn->query("SELECT * FROM meetings WHERE meeting_id = $meetingId");
$meeting = $meetingRes ? $meetingRes->fetch_assoc() : null;

if (!$meeting) {
    echo json_encode(['status' => 'error', 'message' => 'Meeting not found']);
    exit;
}

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

if ($action === 'join') {
    $join_time = date('Y-m-d H:i:s');
    
    // Compute scheduled start timestamp
    $sched_start_str = $meeting['meeting_date'] . ' ' . $meeting['meeting_time'];
    $sched_start_ts = strtotime($sched_start_str);
    $join_ts = strtotime($join_time);
    
    // If user joins within 10 minutes (600s) of start time, mark Present. Otherwise Late.
    $diff_seconds = $join_ts - $sched_start_ts;
    $status = ($diff_seconds <= 600) ? 'Present' : 'Late';
    
    // Check if attendance record already exists
    $checkRes = $conn->query("SELECT * FROM meeting_attendance WHERE meeting_id = $meetingId AND user_id = $userId");
    $exists = ($checkRes && $checkRes->num_rows > 0);
    
    if (!$exists) {
        $stmt = $conn->prepare("INSERT INTO meeting_attendance (meeting_id, user_id, join_time, status) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iiss", $meetingId, $userId, $join_time, $status);
            $stmt->execute();
            $stmt->close();
            
            logAction($conn, $userId, 'Meeting Attendance', 'Join', $meetingId, null, json_encode(['join_time' => $join_time, 'status' => $status]));
        }
    } else {
        // Update join time if re-joining
        $stmt = $conn->prepare("UPDATE meeting_attendance SET join_time = ?, status = ? WHERE meeting_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("ssii", $join_time, $status, $meetingId, $userId);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    echo json_encode(['status' => 'success', 'attendance_status' => $status]);
    exit;
}
elseif ($action === 'exit') {
    $exit_time = date('Y-m-d H:i:s');
    
    // Fetch join time
    $attRes = $conn->query("SELECT join_time FROM meeting_attendance WHERE meeting_id = $meetingId AND user_id = $userId");
    $att = $attRes ? $attRes->fetch_assoc() : null;
    
    if ($att) {
        $join_ts = strtotime($att['join_time']);
        $exit_ts = strtotime($exit_time);
        $duration = max(0, $exit_ts - $join_ts); // duration in seconds
        
        $stmt = $conn->prepare("UPDATE meeting_attendance SET exit_time = ?, duration = ? WHERE meeting_id = ? AND user_id = ?");
        if ($stmt) {
            $stmt->bind_param("siii", $exit_time, $duration, $meetingId, $userId);
            $stmt->execute();
            $stmt->close();
            
            logAction($conn, $userId, 'Meeting Attendance', 'Exit', $meetingId, null, json_encode(['exit_time' => $exit_time, 'duration' => $duration]));
        }
    }
    
    echo json_encode(['status' => 'success']);
    exit;
}
?>
