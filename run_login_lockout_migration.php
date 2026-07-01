<?php
/**
 * Login Lockout DB Migration Runner
 * Run this ONCE via browser: http://localhost/Connect-Amravati-Zeal-FIP/run_login_lockout_migration.php
 * Compatible with all MySQL versions (no IF NOT EXISTS in ALTER TABLE).
 * Delete or restrict this file after use.
 */
include_once('include/dbConfig.php');

echo "<pre style='font-family:monospace;font-size:15px;padding:28px;background:#1e1e1e;color:#d4d4d4;line-height:1.8;'>";
echo "=== Login Lockout Migration ===\n\n";

// ── Helper: run ALTER only if column doesn't exist ──────────────────────────
function add_column_if_missing($conn, $table, $column, $definition) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows === 0) {
        if ($conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition")) {
            return "✅  Added column `$column` to `$table`";
        } else {
            return "❌  Failed to add `$column`: " . $conn->error;
        }
    } else {
        return "⏭️  Column `$column` already exists — skipped";
    }
}

// ── Step 1: Add failed_login_attempts ────────────────────────────────────────
echo add_column_if_missing($conn, 'users', 'failed_login_attempts', 'INT NOT NULL DEFAULT 0') . "\n";

// ── Step 2: Add locked_until ─────────────────────────────────────────────────
echo add_column_if_missing($conn, 'users', 'locked_until', 'DATETIME NULL DEFAULT NULL') . "\n";

// ── Step 3: Create login_history table if missing ────────────────────────────
$create_sql = "CREATE TABLE IF NOT EXISTS `login_history` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($create_sql)) {
    echo "✅  Table `login_history` ready\n";
} else {
    echo "❌  login_history table: " . $conn->error . "\n";
}

// ── Verify columns exist ─────────────────────────────────────────────────────
echo "\n--- Verification ---\n";
$verify = $conn->query("SHOW COLUMNS FROM `users` WHERE Field IN ('failed_login_attempts','locked_until')");
if ($verify) {
    $found = [];
    while ($row = $verify->fetch_assoc()) { $found[] = $row['Field']; }
    foreach (['failed_login_attempts', 'locked_until'] as $col) {
        echo (in_array($col, $found) ? "✅" : "❌") . "  users.$col\n";
    }
}

echo "\n=== Migration Complete ===\n";
echo "\n<span style='color:#f87171;'>⚠  Delete or restrict this file after running: run_login_lockout_migration.php</span>";
echo "</pre>";
?>
