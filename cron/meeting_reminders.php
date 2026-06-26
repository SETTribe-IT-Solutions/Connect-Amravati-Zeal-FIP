<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection
require_once __DIR__ . '/../include/dbConfig.php';

echo "Starting meeting reminders cron job at " . date('Y-m-d H:i:s') . "\n";

// Fetch pending reminders
$remindersQuery = "
    SELECT mr.reminder_id, mr.meeting_id, mr.reminder_type, 
           m.title, m.meeting_date, m.meeting_time, m.audience_type, m.created_by, m.status AS meeting_status
    FROM meeting_reminders mr
    JOIN meetings m ON mr.meeting_id = m.meeting_id
    WHERE mr.sent_status = 0 AND m.status NOT IN ('Completed', 'Cancelled')
";

$result = $conn->query($remindersQuery);
if (!$result) {
    echo "Error querying reminders: " . $conn->error . "\n";
    exit;
}

$sentCount = 0;

while ($row = $result->fetch_assoc()) {
    $reminderId = (int)$row['reminder_id'];
    $meetingId = (int)$row['meeting_id'];
    $reminderType = $row['reminder_type'];
    $meetingTitle = $row['title'];
    $creatorId = (int)$row['created_by'];
    $audienceType = $row['audience_type'];
    
    // Scheduled start timestamp
    $schedStr = $row['meeting_date'] . ' ' . $row['meeting_time'];
    $schedTs = strtotime($schedStr);
    $nowTs = time();
    $diffSecs = $schedTs - $nowTs; // Positive means in the future, negative means past
    
    $shouldSend = false;
    $msgText = "";
    
    if ($reminderType === '24h') {
        // Send if meeting starts within 24 hours (86400s)
        if ($diffSecs > 0 && $diffSecs <= 86400) {
            $shouldSend = true;
            $msgText = "Meeting '" . $meetingTitle . "' starts tomorrow at " . $row['meeting_time'] . ".";
        }
    }
    elseif ($reminderType === '1h') {
        // Send if meeting starts within 1 hour (3600s)
        if ($diffSecs > 0 && $diffSecs <= 3600) {
            $shouldSend = true;
            $msgText = "Meeting '" . $meetingTitle . "' starts in 1 hour.";
        }
    }
    elseif ($reminderType === '15m') {
        // Send if meeting starts within 15 minutes (900s)
        if ($diffSecs > 0 && $diffSecs <= 900) {
            $shouldSend = true;
            $msgText = "Meeting '" . $meetingTitle . "' starts in 15 minutes.";
            
            // Auto update meeting status to 'Starting Soon'
            $conn->query("UPDATE meetings SET status = 'Starting Soon' WHERE meeting_id = $meetingId AND status = 'Scheduled'");
        }
    }
    elseif ($reminderType === 'live') {
        // Send if meeting start time has arrived (diff <= 0) and it hasn't started more than 30 mins ago
        if ($diffSecs <= 0 && $diffSecs >= -1800) {
            $shouldSend = true;
            $msgText = "Meeting '" . $meetingTitle . "' is now live! Join immediately.";
            
            // Auto update meeting status to 'Live'
            $conn->query("UPDATE meetings SET status = 'Live' WHERE meeting_id = $meetingId AND status IN ('Scheduled', 'Starting Soon')");
        }
    }
    
    if ($shouldSend) {
        // Fetch recipients including emails
        $recipients = [];
        // We now have meeting_participants for all audience types, but let's query users directly to get emails.
        $partRes = $conn->query("
            SELECT u.user_id, u.email, u.full_name, mp.rsvp_status 
            FROM meeting_participants mp
            JOIN users u ON mp.user_id = u.user_id
            WHERE mp.meeting_id = $meetingId
        ");
        
        if ($partRes && $partRes->num_rows > 0) {
            while ($pr = $partRes->fetch_assoc()) {
                $recipients[] = $pr;
            }
        } else {
            // Fallback for older meetings without meeting_participants populated
            $roleLevelFilter = '';
            if ($audienceType === 'L1') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 1)';
            elseif ($audienceType === 'L2') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 2)';
            elseif ($audienceType === 'L3') $roleLevelFilter = 'WHERE role_id IN (SELECT role_id FROM roles WHERE role_level = 3)';
            
            $usersRes = $conn->query("SELECT user_id, email, full_name FROM users " . $roleLevelFilter);
            if ($usersRes) {
                while ($ur = $usersRes->fetch_assoc()) {
                    $ur['rsvp_status'] = 'Pending';
                    $recipients[] = $ur;
                }
            }
        }
        
        require_once __DIR__ . '/../include/mailer.php';
        
        // Dispatch notifications and emails
        foreach ($recipients as $rec) {
            $recId = (int)$rec['user_id'];
            $titleEsc = $conn->real_escape_string("Meeting Alert: " . $meetingTitle);
            $msgEsc = $conn->real_escape_string($msgText);
            
            $conn->query("
                INSERT INTO notifications (notification_type, title, message, meeting_id, sender_id, receiver_id, status)
                VALUES ('Meeting', '$titleEsc', '$msgEsc', $meetingId, $creatorId, $recId, 'Unread')
            ");
            
            // Send email for 1h reminder if they haven't explicitly said "Not Joining"
            if ($reminderType === '1h' && !empty($rec['email']) && $rec['rsvp_status'] !== 'Not Joining') {
                $email_html = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8fafc; border-radius: 8px;'>
                    <div style='background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);'>
                        <h2 style='color: #0f172a; margin-top: 0;'>Meeting Reminder: {$meetingTitle}</h2>
                        <p style='color: #334155;'>Hello {$rec['full_name']},</p>
                        <p style='color: #334155;'>This is a reminder that your meeting will start in 1 hour.</p>
                        <p style='color: #64748b; font-size: 12px; margin-top: 30px;'>This is an automated message from Connect Amravati.</p>
                    </div>
                </div>";
                try {
                    send_smtp_email(
                        $rec['email'],
                        "Reminder: " . $meetingTitle . " starts in 1 hour",
                        $email_html,
                        SMTP_USER,
                        SMTP_FROM_NAME,
                        SMTP_HOST,
                        SMTP_PORT,
                        SMTP_USER,
                        SMTP_PASS,
                        SMTP_SECURE
                    );
                } catch (Exception $e) {
                    error_log("Failed to send reminder email: " . $e->getMessage());
                }
            }
        }
        
        // Update reminder state to sent
        $conn->query("UPDATE meeting_reminders SET sent_status = 1, sent_at = NOW() WHERE reminder_id = $reminderId");
        $sentCount++;
        echo "Dispatched $reminderType reminder for meeting ID: $meetingId\n";
    }
}

echo "Reminders cron job completed. Total reminders processed & sent: $sentCount\n";
?>
