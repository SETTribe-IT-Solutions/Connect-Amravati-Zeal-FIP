<?php
/**
 * Database Configuration File
 *
 * Tries remote DB first, falls back to local XAMPP MySQL automatically.
 * Never throws — always sets $conn to a working connection or null.
 */

// ── Remote credentials ────────────────────────────────────────
define('DB_HOST',      '82.25.121.144');
define('DB_USER',      'u196817721_districCNTZEAL');
define('DB_PASS',      'districtCNTDB@2026');
define('DB_NAME',      'u196817721_districtCNTDB');

// ── Local XAMPP credentials (fallback) ────────────────────────
define('DB_HOST_LOCAL', '127.0.0.1');
define('DB_USER_LOCAL', 'root');
define('DB_PASS_LOCAL', '');
define('DB_NAME_LOCAL', 'u196817721_districtcntdb');

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
?>
