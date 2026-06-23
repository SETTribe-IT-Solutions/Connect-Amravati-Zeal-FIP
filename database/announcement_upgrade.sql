-- Alter announcements table to add category, priority, audience_type, created_at, updated_at, updated_by
ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title`;
ALTER TABLE `announcements` ADD COLUMN `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium' AFTER `description`;
ALTER TABLE `announcements` ADD COLUMN `audience_type` ENUM('All', 'L1', 'L2', 'L3', 'Custom') NOT NULL DEFAULT 'All' AFTER `priority`;
ALTER TABLE `announcements` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() AFTER `expiry_date`;
ALTER TABLE `announcements` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP() AFTER `created_at`;
ALTER TABLE `announcements` ADD COLUMN `updated_by` INT(11) NULL AFTER `created_by`;

-- Alter notifications table to add meeting_id, document_id, message_id
ALTER TABLE `notifications` ADD COLUMN `meeting_id` INT(11) NULL DEFAULT NULL AFTER `certificate_id`;
ALTER TABLE `notifications` ADD COLUMN `document_id` INT(11) NULL DEFAULT NULL AFTER `meeting_id`;
ALTER TABLE `notifications` ADD COLUMN `message_id` INT(11) NULL DEFAULT NULL AFTER `document_id`;

-- Alter audit_logs to add browser_details
ALTER TABLE `audit_logs` ADD COLUMN `browser_details` VARCHAR(255) NULL DEFAULT NULL AFTER `new_value`;

-- New tables
CREATE TABLE IF NOT EXISTS `meetings` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `meeting_participants` (
  `participant_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `meeting_attendance` (
  `attendance_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `join_time` DATETIME NOT NULL,
  `exit_time` DATETIME NULL,
  `duration` INT(11) COMMENT 'Duration in seconds',
  `status` ENUM('Present', 'Late', 'Absent') DEFAULT 'Absent',
  UNIQUE KEY `unique_meeting_user` (`meeting_id`, `user_id`),
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `meeting_reminders` (
  `reminder_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `meeting_id` INT(11) NOT NULL,
  `reminder_type` ENUM('24h', '1h', '15m', 'live') NOT NULL,
  `sent_status` TINYINT(1) DEFAULT 0,
  `sent_at` TIMESTAMP NULL,
  FOREIGN KEY (`meeting_id`) REFERENCES `meetings`(`meeting_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confidential_documents` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `confidential_document_audience` (
  `id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  FOREIGN KEY (`document_id`) REFERENCES `confidential_documents`(`document_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `document_access_logs` (
  `log_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `action_type` ENUM('View', 'Download') NOT NULL,
  `access_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `device_info` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`document_id`) REFERENCES `confidential_documents`(`document_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `shared_messages` (
  `message_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `subject` VARCHAR(255) NOT NULL,
  `message_body` TEXT NOT NULL,
  `sender_id` INT(11) NOT NULL,
  `attachment_path` VARCHAR(255) NULL,
  `parent_message_id` INT(11) DEFAULT NULL,
  `is_forwarded` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `message_recipients` (
  `recipient_id` INT(11) AUTO_INCREMENT PRIMARY KEY,
  `message_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` DATETIME NULL,
  `is_delivered` TINYINT(1) DEFAULT 0,
  `delivered_at` DATETIME NULL,
  FOREIGN KEY (`message_id`) REFERENCES `shared_messages`(`message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
