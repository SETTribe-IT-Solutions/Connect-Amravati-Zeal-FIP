<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

// Check auth
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$userId = (int)$userId;
$sRole  = $_SESSION['user_role'] ?? 'L3';
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

try {
    // 1. Get Unread Count from notifications (scoped to logged-in user)
    $countQuery = "SELECT COUNT(*) as unread_count 
                   FROM notifications 
                   WHERE status = 'Unread' AND receiver_id = $userId";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result();
    $unreadCount = $countResult->fetch_assoc()['unread_count'];
    $stmtCount->close();

    // 2. Get Latest 100 Notifications
    $notifQuery = "SELECT n.notification_id, n.title, n.message, n.notification_type, n.status as n_status, 
                          n.created_at as notif_created_at, n.task_id, ndl.delivery_time, 
                          t.task_title, t.task_description, t.priority AS task_priority, 
                          t.due_date AS task_due_date, t.status AS task_status, 
                          t.assigned_user_id, u.full_name AS sender_name
                   FROM notifications n
                   LEFT JOIN notification_delivery_logs ndl ON n.notification_id = ndl.notification_id
                   LEFT JOIN tasks t ON n.task_id = t.task_id
                   LEFT JOIN users u ON n.sender_id = u.user_id
                   WHERE n.receiver_id = $userId
                   ORDER BY COALESCE(ndl.delivery_time, n.created_at) DESC LIMIT 100";
    $stmtNotif = $conn->prepare($notifQuery);
    $stmtNotif->execute();
    $notifResult = $stmtNotif->get_result();
    
    $notifications = [];
    while ($row = $notifResult->fetch_assoc()) {
        $priority = $row['task_priority'] ?? 'Medium';
        
        // Color indicators
        $badgeColor = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        if ($priority == 'Critical') $badgeColor = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        if ($priority == 'High') $badgeColor = 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
        if ($priority == 'Low') $badgeColor = 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';

        // Actions mapping based on roles, assigned user, and task status
        $actions = [];
        $tId = (int)$row['task_id'];
        $tStatus = $row['task_status'];
        $assigneeId = (int)$row['assigned_user_id'];
        
        if ($row['notification_type'] === 'Task' && $tId > 0) {
            if ($tStatus === 'Pending' || $tStatus === 'Reassigned') {
                if ($assigneeId === $userId) {
                    $actions = [
                        ['label' => 'Accept', 'action' => 'accept', 'style' => 'bg-govgreen-500 text-white hover:bg-govgreen-600'],
                        ['label' => 'Reject', 'action' => 'reject', 'style' => 'bg-red-500 text-white hover:bg-red-650']
                    ];
                }
            } elseif ($tStatus === 'Pending Verification') {
                if ($isL1) {
                    $actions = [
                        ['label' => 'Verify Rejection', 'action' => 'verify_rejection', 'style' => 'bg-blue-500 text-white hover:bg-blue-600']
                    ];
                }
            } elseif ($tStatus === 'Completed') {
                if ($isL1) {
                    $actions = [
                        ['label' => 'Verify Completion', 'action' => 'verify_completion', 'style' => 'bg-purple-500 text-white hover:bg-purple-650']
                    ];
                }
            }
        }

        $notifications[] = [
            'id' => $row['notification_id'],
            'task_id' => $tId,
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['notification_type'],
            'priority' => $priority,
            'is_read' => ($row['n_status'] !== 'Unread') ? 1 : 0,
            'time_elapsed' => time_elapsed_string($row['delivery_time'] ?? $row['notif_created_at']),
            'badge_color' => $badgeColor,
            'sender_name' => $row['sender_name'] ?? 'System',
            'task_due_date' => $row['task_due_date'],
            'task_status' => $tStatus,
            'actions' => $actions
        ];
    }
    $stmtNotif->close();

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
    if (!$datetime) return 'just now';
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
