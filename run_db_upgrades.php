<?php
header('Content-Type: text/plain');
require_once __DIR__ . '/include/dbConfig.php';

// Try using mysqli connection from dbConfig.php
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Database connection not established in dbConfig.php");
}

$queries = [
    // Task allocation upgrade
    "ALTER TABLE `tasks` MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Transferred', 'Completed', 'Overdue', 'Rejected', 'Escalated', 'Accepted', 'Verified', 'Pending Verification', 'Approved Rejection', 'Denied', 'Reassigned') DEFAULT 'Pending'",
    
    "CREATE TABLE IF NOT EXISTS `task_rejection_proofs` (
      `proof_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `rejection_reason` varchar(255) NOT NULL,
      `remarks` text NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`proof_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `task_verification_logs` (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `action` varchar(100) NOT NULL,
      `ip_address` varchar(45) NOT NULL,
      `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`log_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Announcement upgrade
    "ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title` CASCADE", // Wait, ALTER TABLE with CASCADE is not valid in MySQL, let's omit CASCADE
    "ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title`HTML", // Syntax error helper
];

// Clean ALTER statements (no CASCADE or trailing HTML)
$queries = [
    "ALTER TABLE `tasks` MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Transferred', 'Completed', 'Overdue', 'Rejected', 'Escalated', 'Accepted', 'Verified', 'Pending Verification', 'Approved Rejection', 'Denied', 'Reassigned') DEFAULT 'Pending'",
    
    "CREATE TABLE IF NOT EXISTS `task_rejection_proofs` (
      `proof_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `rejection_reason` varchar(255) NOT NULL,
      `remarks` text NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`proof_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `task_verification_logs` (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `action` varchar(100) NOT NULL,
      `ip_address` varchar(45) NOT NULL,
      `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`log_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Column additions with EXISTS checks (handled by catching duplicate column errors)
    "ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title`Descriptor", // syntax bug
];

$cleanQueries = [
    "ALTER TABLE `tasks` MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Transferred', 'Completed', 'Overdue', 'Rejected', 'Escalated', 'Accepted', 'Verified', 'Pending Verification', 'Approved Rejection', 'Denied', 'Reassigned') DEFAULT 'Pending'",
    
    "CREATE TABLE IF NOT EXISTS `task_rejection_proofs` (
      `proof_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `rejection_reason` varchar(255) NOT NULL,
      `remarks` text NOT NULL,
      `file_path` varchar(255) NOT NULL,
      `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`proof_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `task_verification_logs` (
      `log_id` int(11) NOT NULL AUTO_INCREMENT,
      `task_id` int(11) NOT NULL,
      `user_id` int(11) NOT NULL,
      `action` varchar(100) NOT NULL,
      `ip_address` varchar(45) NOT NULL,
      `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`log_id`),
      FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title`",
    "ALTER TABLE `announcements` ADD COLUMN `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium' AFTER `description`",
    "ALTER TABLE `announcements` ADD COLUMN `audience_type` ENUM('All', 'L1', 'L2', 'L3', 'Custom') NOT NULL DEFAULT 'All' AFTER `priority`",
    "ALTER TABLE `announcements` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() AFTER `expiry_date`",
    "ALTER TABLE `announcements` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP() AFTER `created_at`",
    "ALTER TABLE `announcements` ADD COLUMN `updated_by` INT(11) NULL AFTER `created_by`",
    
    "ALTER TABLE `notifications` ADD COLUMN `meeting_id` INT(11) NULL DEFAULT NULL AFTER `certificate_id`",
    "ALTER TABLE `notifications` ADD COLUMN `document_id` INT(11) NULL DEFAULT NULL AFTER `meeting_id`",
    "ALTER TABLE `notifications` ADD COLUMN `message_id` INT(11) NULL DEFAULT NULL AFTER `document_id`",
    
    "ALTER TABLE `audit_logs` ADD COLUMN `browser_details` VARCHAR(255) NULL DEFAULT NULL AFTER `new_value`",
    
    "CREATE TABLE IF NOT EXISTS `meetings` (
      `meeting_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `title` VARCHAR(255) NOT NULL,
      `agenda` TEXT NULL,
      `description` TEXT NULL,
      `meeting_date` DATE NOT NULL,
      `meeting_time` TIME NOT NULL,
      `duration` INT(11) COMMENT 'Duration in minutes',
      `location` VARCHAR(255) NULL,
      `meeting_type` ENUM('Online', 'Offline', 'Hybrid') NOT NULL DEFAULT 'Offline',
      `meeting_link` VARCHAR(255) NULL,
      `meeting_password` VARCHAR(100) NULL,
      `meeting_platform` VARCHAR(100) NULL,
      `audience_type` ENUM('All', 'L1', 'L2', 'L3', 'Custom') NOT NULL DEFAULT 'All',
      `attachment_agenda` VARCHAR(255) NULL,
      `attachment_supporting` VARCHAR(255) NULL,
      `status` ENUM('Scheduled', 'Starting Soon', 'Live', 'Completed', 'Cancelled', 'Missed') DEFAULT 'Scheduled',
      `created_by` INT(11) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
      `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `meeting_participants` (
      `participant_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `meeting_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `rsvp_status` ENUM('Pending', 'Joined', 'Not Joining') DEFAULT 'Pending',
      `rsvp_reason` TEXT NULL,
      FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "ALTER TABLE `meeting_participants` ADD COLUMN IF NOT EXISTS `rsvp_status` ENUM('Pending', 'Joined', 'Not Joining') DEFAULT 'Pending'",
    "ALTER TABLE `meeting_participants` ADD COLUMN IF NOT EXISTS `rsvp_reason` TEXT NULL",
    
    "CREATE TABLE IF NOT EXISTS `meeting_attendance` (
      `attendance_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `meeting_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `join_time` DATETIME NOT NULL,
      `exit_time` DATETIME NULL,
      `duration` INT(11) COMMENT 'Duration in seconds',
      `status` ENUM('Present', 'Late', 'Absent') DEFAULT 'Absent',
      UNIQUE KEY `unique_meeting_user` (`meeting_id`, `user_id`),
      FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `meeting_reminders` (
      `reminder_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `meeting_id` INT(11) NOT NULL,
      `reminder_type` ENUM('24h', '1h', '15m', 'live') NOT NULL,
      `sent_status` TINYINT(1) DEFAULT 0,
      `sent_at` TIMESTAMP NULL,
      FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `confidential_documents` (
      `document_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `subject` VARCHAR(255) NOT NULL,
      `description` TEXT NULL,
      `classification_level` ENUM('Public', 'Internal', 'Confidential', 'Highly Confidential') NOT NULL DEFAULT 'Confidential',
      `file_path` VARCHAR(255) NOT NULL,
      `allow_download` TINYINT(1) DEFAULT 1,
      `allow_view` TINYINT(1) DEFAULT 1,
      `audience_type` ENUM('All', 'L1', 'L2', 'L3', 'Custom') NOT NULL DEFAULT 'All',
      `created_by` INT(11) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `confidential_document_audience` (
      `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `document_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      FOREIGN KEY (`document_id`) REFERENCES `confidential_documents`(`document_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `document_access_logs` (
      `log_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `document_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `action_type` ENUM('View', 'Download') NOT NULL,
      `access_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
      `ip_address` VARCHAR(45) NOT NULL,
      `user_agent` VARCHAR(255) NOT NULL,
      `device_info` VARCHAR(255) NOT NULL,
      FOREIGN KEY (`document_id`) REFERENCES `confidential_documents`(`document_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `shared_messages` (
      `message_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `subject` VARCHAR(255) NOT NULL,
      `message_body` TEXT NOT NULL,
      `sender_id` INT(11) NOT NULL,
      `attachment_path` VARCHAR(255) NULL,
      `parent_message_id` INT(11) DEFAULT NULL,
      `is_forwarded` TINYINT(1) DEFAULT 0,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS `message_recipients` (
      `recipient_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
      `message_id` INT(11) NOT NULL,
      `user_id` INT(11) NOT NULL,
      `is_read` TINYINT(1) DEFAULT 0,
      `read_at` DATETIME NULL,
      `is_delivered` TINYINT(1) DEFAULT 0,
      `delivered_at` DATETIME NULL,
      FOREIGN KEY (`message_id`) REFERENCES `shared_messages`(`message_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "CREATE TABLE IF NOT EXISTS `user_registration_requests` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `employee_code` VARCHAR(50) NOT NULL UNIQUE,
      `first_name` VARCHAR(100) NOT NULL,
      `middle_name` VARCHAR(100) NULL,
      `last_name` VARCHAR(100) NOT NULL,
      `applicant_name` VARCHAR(255) NOT NULL,
      `gender` VARCHAR(20) NOT NULL,
      `dob` DATE NOT NULL,
      `mobile` VARCHAR(20) NOT NULL,
      `alternate_mobile` VARCHAR(20) NULL,
      `email` VARCHAR(100) NOT NULL UNIQUE,
      `aadhaar` VARCHAR(50) NULL,
      `profile_photo` VARCHAR(255) NULL,
      `department_id` INT NULL,
      `role_id` INT NULL,
      `taluka_id` INT NULL,
      `village_id` INT NULL,
      `joining_date` DATE NULL,
      `reporting_office` VARCHAR(255) NULL,
      `username` VARCHAR(100) NOT NULL UNIQUE,
      `password_hash` VARCHAR(255) NOT NULL,
      `current_address` TEXT NULL,
      `permanent_address` TEXT NULL,
      `state` VARCHAR(100) DEFAULT 'Maharashtra',
      `district` VARCHAR(100) DEFAULT 'Amravati',
      `taluka_name` VARCHAR(100) NULL,
      `village_or_city` VARCHAR(100) NULL,
      `pincode` VARCHAR(10) NULL,
      `verify_status` VARCHAR(50) DEFAULT 'Unverified',
      `request_status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
      `rejection_reason` TEXT NULL,
      `registration_source` VARCHAR(50) DEFAULT 'Self Registration',
      `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      `approved_by` INT NULL,
      `approved_at` DATETIME NULL,
      `rejected_by` INT NULL,
      `rejected_at` DATETIME NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    "ALTER TABLE `users` ADD COLUMN `approved_by` INT NULL DEFAULT NULL",
    "ALTER TABLE `users` ADD COLUMN `approved_at` DATETIME NULL DEFAULT NULL",
    "ALTER TABLE `notifications` ADD COLUMN `redirect_url` VARCHAR(255) NULL DEFAULT NULL"
];

foreach ($cleanQueries as $index => $sql) {
    echo "Executing query #$index...\n";
    try {
        if ($conn->query($sql)) {
            echo " -> Success!\n";
        } else {
            echo " -> Error: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo " -> Error: " . $e->getMessage() . "\n";
    }
    echo "-------------------------------------\n";
}
echo "Database upgrade process completed.\n";
?>
