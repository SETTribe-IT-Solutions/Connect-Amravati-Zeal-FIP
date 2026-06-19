<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

// Assuming user is logged in and we have their ID. Mocking for now if not set.
$userId = $_SESSION['user_id'] ?? 1;

try {
    // 1. Get Unread Count from notifications
    $countQuery = "SELECT COUNT(*) as unread_count 
                   FROM notifications n
                   LEFT JOIN notification_delivery_logs ndl ON n.notification_id = ndl.notification_id
                   WHERE n.status = 'Unread'"; // Temporarily removing receiver_id filter to show all data for testing
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $unreadCount = $countResult->fetch_assoc()['unread_count'];

    // 2. Get Latest 10 Notifications
    $notifQuery = "SELECT n.notification_id, n.title, n.message, n.notification_type, n.status as n_status, n.created_at as notif_created_at, ndl.delivery_time, t.priority AS task_priority
                   FROM notifications n
                   LEFT JOIN notification_delivery_logs ndl ON n.notification_id = ndl.notification_id
                   LEFT JOIN tasks t ON n.task_id = t.task_id
                   ORDER BY COALESCE(ndl.delivery_time, n.created_at) DESC LIMIT 10";
    $stmtNotif = $conn->prepare($notifQuery);
    $stmtNotif->execute();
    $notifResult = $stmtNotif->get_result();
    
    $notifications = [];
    while ($row = $notifResult->fetch_assoc()) {
        // Map priority to color (default to Medium since notifications table has no priority column)
        $priority = $row['task_priority'] ?? 'Medium';
        $badgeColor = 'bg-blue-100 text-blue-800';
        if ($priority == 'Critical') $badgeColor = 'bg-red-100 text-red-800';
        if ($priority == 'High') $badgeColor = 'bg-orange-100 text-orange-800';
        if ($priority == 'Medium') $badgeColor = 'bg-blue-100 text-blue-800';
        if ($priority == 'Low') $badgeColor = 'bg-green-100 text-green-800';

        $notifications[] = [
            'id' => $row['notification_id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'priority' => $priority,
            'is_read' => ($row['n_status'] !== 'Unread') ? 1 : 0,
            'time_elapsed' => time_elapsed_string($row['delivery_time'] ?? $row['notif_created_at']),
            'badge_color' => $badgeColor
        ];
    }

    echo json_encode([
        'status' => 'success',
        'unread_count' => $unreadCount,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

// Helper function
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day',
        'h' => 'hr', 'i' => 'min', 's' => 'sec',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
