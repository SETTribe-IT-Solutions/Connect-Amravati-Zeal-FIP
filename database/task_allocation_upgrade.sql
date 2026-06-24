-- Expand tasks status enum to include all workflow transitions
ALTER TABLE `tasks` MODIFY COLUMN `status` ENUM('Pending', 'In Progress', 'Transferred', 'Completed', 'Overdue', 'Rejected', 'Escalated', 'Accepted', 'Verified', 'Pending Verification', 'Approved Rejection', 'Denied', 'Reassigned') DEFAULT 'Pending';

-- Create task rejection proofs table for mandatory uploads
CREATE TABLE IF NOT EXISTS `task_rejection_proofs` (
  `proof_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rejection_reason` varchar(255) NOT NULL,
  `remarks` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`proof_id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create verification logs table to audit rejection reviews
CREATE TABLE IF NOT EXISTS `task_verification_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`task_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
