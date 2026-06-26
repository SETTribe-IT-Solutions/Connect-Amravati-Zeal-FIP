<?php
/**
 * cron/reminder_engine.php
 * Run this script hourly or daily via cron:
 * 0 * * * * php /path/to/cron/reminder_engine.php
 */

require_once dirname(__DIR__) . '/include/dbConfig.php';
require_once dirname(__DIR__) . '/include/NotificationEngine.php';

$notificationEngine = new NotificationEngine($conn);

echo "Starting Reminder Engine...\n";

// Get Tasks that are not completed and their assigned users
$query = "SELECT t.task_id, t.task_title, t.due_date, DATEDIFF(t.due_date, CURDATE()) as days_left, 
                 COALESCE(ta.assigned_to_user, t.assigned_user_id) as user_id, u.email, u.mobile
          FROM tasks t
          LEFT JOIN task_assignments ta ON t.task_id = ta.task_id AND ta.status != 'Completed'
          LEFT JOIN users u ON COALESCE(ta.assigned_to_user, t.assigned_user_id) = u.user_id
          WHERE t.status != 'Completed' AND t.due_date IS NOT NULL AND COALESCE(ta.assigned_to_user, t.assigned_user_id) IS NOT NULL";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daysLeft = (int)$row['days_left'];
        $taskId = $row['task_id'];
        $userId = $row['user_id'];
        $title = $row['task_title'];
        $email = $row['email'];
        $mobile = $row['mobile'];

        // Define alert conditions
        if ($daysLeft < 0) {
            // Overdue
            createAlert($notificationEngine, 'Overdue Reminder', "Task '$title' is overdue by " . abs($daysLeft) . " day(s).", 'Critical', $taskId, $userId, $email, $mobile);
        } else if ($daysLeft === 0) {
            // Due Today
            createAlert($notificationEngine, 'Due Today', "Task '$title' is due today.", 'High', $taskId, $userId, $email, $mobile);
        } else if ($daysLeft === 1) {
            // 1 Day Reminder
            createAlert($notificationEngine, 'Due Tomorrow', "Task '$title' is due tomorrow.", 'High', $taskId, $userId, $email, $mobile);
        } else if ($daysLeft === 3) {
            // 3 Day Reminder
            createAlert($notificationEngine, 'Upcoming Deadline', "Task '$title' is due in 3 days.", 'Medium', $taskId, $userId, $email, $mobile);
        } else if ($daysLeft === 7) {
            // 7 Day Reminder
            createAlert($notificationEngine, 'Upcoming Deadline', "Task '$title' is due in 7 days.", 'Low', $taskId, $userId, $email, $mobile);
        }
    }
}

echo "Reminder Engine completed.\n";

function createAlert($engine, $type, $message, $priority, $taskId, $userId, $email, $mobile) {
    global $conn;
    
    // Check if we already sent this specific alert today to avoid spamming
    $checkQuery = "SELECT notification_id FROM notifications WHERE task_id = ? AND receiver_id = ? AND title = ? AND DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("iis", $taskId, $userId, $type);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        $engine->sendNotification([
            'type' => 'Reminder',
            'priority' => $priority,
            'title' => $type,
            'message' => $message,
            'task_id' => $taskId,
            'receiver_id' => $userId,
            'send_email' => !empty($email),
            'send_sms' => !empty($mobile),
            'receiver_email' => $email,
            'receiver_mobile' => $mobile
        ]);
        echo "Sent: $type for Task $taskId to User $userId\n";
    }
    $stmt->close();
}
?>
