-- =====================================================================
-- Login Lockout Upgrade
-- Adds per-user failed attempt tracking and lockout support to `users`
-- Run once. Both ALTER statements are idempotent (IF NOT EXISTS-safe).
-- =====================================================================

-- Add failed login attempt counter (resets on success or after lock expiry)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `failed_login_attempts` INT NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `locked_until` DATETIME NULL DEFAULT NULL;

-- Ensure the login_history table exists (created here as a fallback)
CREATE TABLE IF NOT EXISTS `login_history` (
    `id`          INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`     INT(11)      NULL,
    `login_time`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ip_address`  VARCHAR(45)  NOT NULL DEFAULT '',
    `device_info` VARCHAR(255) NOT NULL DEFAULT '',
    `status`      VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`id`),
    INDEX `idx_user_id`    (`user_id`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
