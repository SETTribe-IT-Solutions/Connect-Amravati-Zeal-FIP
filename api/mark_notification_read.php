<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

$userId = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $notificationId = $data['notification_id'] ?? null;
    $markAll = $data['mark_all'] ?? false;

    try {
        if ($markAll) {
            $stmt = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE status = 'Unread'");
            $stmt->execute();
        } else if ($notificationId) {
            $stmt = $conn->prepare("UPDATE notifications SET status = 'Read' WHERE notification_id = ?");
            $stmt->bind_param("i", $notificationId);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            exit;
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
