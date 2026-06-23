<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
require_once __DIR__ . '/../include/dbConfig.php';

echo "Starting scheduled announcements publishing cron job at " . date('Y-m-d H:i:s') . "\n";

// Fetch scheduled announcements that need publishing
$anncQuery = "
    SELECT * FROM announcements 
    WHERE status = 'Scheduled' AND (publish_date IS NULL OR publish_date <= CURDATE())
";

$result = $conn->query($anncQuery);
if (!$result) {
    echo "Error querying announcements: " . $conn->error . "\n";
    exit;
}

$publishCount = 0;

while ($row = $result->fetch_assoc()) {
    $anncId = (int)$row['announcement_id'];
    $title = $row['title'];
    $description = $row['description'];
    $audienceType = $row['audience_type'];
    $creatorId = (int)$row['created_by'];
    
    // Update announcement status
    $conn->query("UPDATE announcements SET status = 'Published' WHERE announcement_id = $anncId");
    
    // Fetch target recipients
    $recipients = [];
    if ($audienceType === 'Custom') {
        // Find if custom recipients exist in announcement_recipients (normally L1 creates these in draft mode)
        // If Custom, they might be in a separate table, but we stored custom recipients directly in announcement_recipients.
        // Wait, if it was scheduled, the custom recipients might have been deleted or empty. But we shouldn't delete if it is custom.
        // Actually, let's see: for Custom, we already inserted them into announcement_recipients when the announcement was created/edited.
        // So for Custom, we do NOT delete them! Let's check:
        // "Delete only if NOT Custom"
        // Let's modify the delete query to:
        // IF audience_type != 'Custom', clear recipients.
    }
    
    if ($audienceType !== 'Custom') {
        $conn->query("DELETE FROM announcement_recipients WHERE announcement_id = $anncId");
        
        $roleLevelFilter = '';
        if ($audienceType === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
        elseif ($audienceType === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
        elseif ($audienceType === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
        
        $usersRes = $conn->query("SELECT user_id FROM users " . $roleLevelFilter);
        if ($usersRes) {
            while ($ur = $usersRes->fetch_assoc()) {
                $targetUserId = (int)$ur['user_id'];
                $conn->query("INSERT INTO announcement_recipients (announcement_id, user_id, is_read) VALUES ($anncId, $targetUserId, 0)");
                $recipients[] = $targetUserId;
            }
        }
    } else {
        // Custom: select from announcement_recipients that already exist
        $recRes = $conn->query("SELECT user_id FROM announcement_recipients WHERE announcement_id = $anncId");
        if ($recRes) {
            while ($rr = $recRes->fetch_assoc()) {
                $recipients[] = (int)$rr['user_id'];
            }
        }
    }
    
    // Send notifications to all recipients
    foreach ($recipients as $recId) {
        $titleEsc = $conn->real_escape_string("New Announcement: " . $title);
        $msgEsc = $conn->real_escape_string("A new announcement has been published: " . substr($description, 0, 100) . "...");
        
        $conn->query("
            INSERT INTO notifications (notification_type, title, message, announcement_id, sender_id, receiver_id, status)
            VALUES ('Announcement', '$titleEsc', '$msgEsc', $anncId, $creatorId, $recId, 'Unread')
        ");
    }
    
    // Log audit action
    $ip = '127.0.0.1';
    $userAgent = 'System Cron Job';
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) VALUES (?, 'Announcement', 'Publish Scheduled', ?, 'Scheduled', 'Published', ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iisss", $creatorId, $anncId, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
    
    $publishCount++;
    echo "Published scheduled announcement ID: $anncId ($title)\n";
}

echo "Publish scheduled announcements cron completed. Total published: $publishCount\n";
?>
