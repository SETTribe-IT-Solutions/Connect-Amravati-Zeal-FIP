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

// Get Tasks that are not completed
$query = "SELECT task_id, task_title, assigned_user_id, due_date, DATEDIFF(due_date, CURDATE()) as days_left 
          FROM tasks 
          WHERE status != 'Completed' AND due_date IS NOT NULL";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $daysLeft = (int)$row['days_left'];
        $taskId = $row['task_id'];
        $userId = $row['assigned_user_id'];
        $title = $row['task_title'];

        // Define alert conditions
        if ($daysLeft < 0) {
            // Overdue
            createAlert($notificationEngine, 'Overdue Reminder', "Task '$title' is overdue by " . abs($daysLeft) . " day(s).", 'Critical', $taskId, $userId);
        } else if ($daysLeft === 0) {
            // Due Today
            createAlert($notificationEngine, 'Due Today', "Task '$title' is due today.", 'High', $taskId, $userId);
        } else if ($daysLeft === 1) {
            // 1 Day Reminder
            createAlert($notificationEngine, 'Due Tomorrow', "Task '$title' is due tomorrow.", 'High', $taskId, $userId);
        } else if ($daysLeft === 3) {
            // 3 Day Reminder
            createAlert($notificationEngine, 'Upcoming Deadline', "Task '$title' is due in 3 days.", 'Medium', $taskId, $userId);
        } else if ($daysLeft === 7) {
            // 7 Day Reminder
            createAlert($notificationEngine, 'Upcoming Deadline', "Task '$title' is due in 7 days.", 'Low', $taskId, $userId);
        }
    }
}

echo "Reminder Engine completed.\n";

function createAlert($engine, $type, $message, $priority, $taskId, $userId) {
    global $conn;
    
    // Check if we already sent this specific alert today to avoid spamming
    // This simple check prevents duplicate alerts on the same day for the same task
    $checkQuery = "SELECT notification_id FROM notifications WHERE task_id = ? AND title = ? AND DATE(created_at) = CURDATE()";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("is", $taskId, $type);
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
            'send_email' => true, // Configurable based on user settings
            'send_sms' => false,
            // To be fetched from DB:
            // 'receiver_email' => 'user@example.com',
            // 'receiver_mobile' => '9876543210'
        ]);
        echo "Sent: $type for Task $taskId\n";
    }
}
?>
