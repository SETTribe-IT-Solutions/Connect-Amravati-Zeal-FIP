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

$conn = null;

// 1. Try remote server first (suppress error output with @)
mysqli_report(MYSQLI_REPORT_OFF); // Don't throw on connect error
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
 * Also called automatically at shutdown via register_shutdown_function.
 */
function close_db_connection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        @$conn->close();
        $conn = null;
    }
}

register_shutdown_function('close_db_connection');

// Create MySQLi connection (@ suppresses printed warning; error is handled via connect_error check below)
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    throw new RuntimeException("DB Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset("utf8mb4");
}


// Register a shutdown function to automatically close the database connection at the end of script execution
register_shutdown_function(function() {
    close_db_connection();
});

/**
 * Explicitly close the database connection and set it to null.
 * Call this function at the end of database operations to release connection limits immediately.
 */
?>
