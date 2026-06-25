<?php

/**
 * Database Configuration File
 * 
 * This file establishes a MySQLi connection to the database.
 * Include this file wherever a database connection is needed.
 */

// Database credentials
define('DB_HOST', '103.160.107.18');
define('DB_USER', 'nmrmlatur_districCNTZEAL');
define('DB_PASS', 'districtCNTDB@2026');
define('DB_NAME', 'nmrmlatur_districtCNTDB');

// Disable persistent connections at runtime level for MySQLi
ini_set('mysqli.allow_persistent', '0');

// Create MySQLi connection with graceful error handling
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
} catch (mysqli_sql_exception $e) {
    // Check if the hourly connection limit is exceeded
    if (strpos($e->getMessage(), 'max_connections_per_hour') !== false) {
        die("<h3>Database Connection Error</h3><p>The database has temporarily reached its hourly request limit. This limit will reset shortly. Please try again in a few minutes.</p>");
    }
    // General connection error
    die("<h3>Database Connection Error</h3><p>Unable to connect to the database. Please try again later.</p>");
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
function close_db_connection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        @$conn->close();
        $conn = null;
    }
}

?>
