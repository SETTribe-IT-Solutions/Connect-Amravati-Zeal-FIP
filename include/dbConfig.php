<?php
/**
 * Database Configuration File
 *
 * Tries remote DB first, falls back to local XAMPP MySQL automatically.
 * Never throws — always sets $conn to a working connection or null.
 */

// ── Remote credentials ────────────────────────────────────────
define('DB_HOST',      'p:103.160.107.18');
define('DB_USER',      'nmrmlatur_districCNTZEAL');
define('DB_PASS',      'districtCNTDB@2026');
define('DB_NAME',      'nmrmlatur_districtCNTDB');

// ── Local credentials (XAMPP fallback) ───────────────────────
define('DB_HOST_LOCAL', 'localhost');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');
define('DB_NAME_LOCAL', 'nmrmlatur_districtCNTDB');

$conn = null;

// Suppress connect errors so we can handle them manually
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Try remote server first
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_errno) {
    // Remote failed — log it silently and try local
    error_log('[dbConfig] Remote DB failed (' . $conn->connect_error . ') — falling back to local DB');
    $conn = null;

    // 2. Try local XAMPP MySQL
    $conn = @new mysqli(DB_HOST_LOCAL, DB_USER_LOCAL, DB_PASS_LOCAL, DB_NAME_LOCAL);

    if ($conn->connect_errno) {
        error_log('[dbConfig] Local DB also failed: ' . $conn->connect_error);
        $conn = null; // Both failed — callers must handle null $conn gracefully
    } else {
        $conn->set_charset('utf8mb4');
        error_log('[dbConfig] Connected to LOCAL database successfully');
    }
} else {
    $conn->set_charset('utf8mb4');
    error_log('[dbConfig] Connected to REMOTE database successfully');
}

// Auto-patch missing columns to fix graph data and functionality silently
if ($conn instanceof mysqli) {
    @$conn->query("ALTER TABLE `tasks` ADD COLUMN `completion_date` DATETIME NULL AFTER `due_date`");
    @$conn->query("ALTER TABLE `announcements` ADD COLUMN `category` VARCHAR(100) NULL AFTER `title`");
}

// Re-enable strict mode only after successful connection
if ($conn instanceof mysqli) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

/**
 * Explicitly close the database connection.
 */
function close_db_connection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        @$conn->close();
        $conn = null;
    }
}

// Register shutdown to auto-close the connection at script end
register_shutdown_function('close_db_connection');
// Clear all tasks from the database
function clear_all_tasks() {
    global $conn;
    if ($conn instanceof mysqli) {
        $conn->query("DELETE FROM `tasks`");
        $conn->query("ALTER TABLE `tasks` AUTO_INCREMENT = 1");
    } else {
        error_log('[dbConfig] No DB connection to clear tasks');
    }
}
?>
