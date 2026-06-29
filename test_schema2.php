<?php
require 'include/dbConfig.php';
$conn->query("ALTER TABLE meeting_participants ADD COLUMN rsvp_status ENUM('Pending', 'Joined', 'Not Joining') DEFAULT 'Pending'");
$conn->query("ALTER TABLE meeting_participants ADD COLUMN rsvp_reason TEXT NULL");
echo "DB Updated successfully!";
?>
