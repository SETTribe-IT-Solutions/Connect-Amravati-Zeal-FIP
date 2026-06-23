<?php

/**
 * Database Configuration File
 * 
 * This file establishes a MySQLi connection to the database.
 * Include this file wherever a database connection is needed.
 */

// Database credentials
define('DB_HOST', '82.25.121.144');
define('DB_USER', 'u196817721_districCNTZEAL');
define('DB_PASS', 'districtCNTDB@2026');
define('DB_NAME', 'u196817721_districtCNTDB');

// Create MySQLi connection with fallback
try {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception($conn->connect_error);
    }
} catch (Exception $e) {
    // Fallback to local database on XAMPP
    try {
        $conn = @new mysqli('127.0.0.1', 'root', '', 'u196817721_districtcntdb');
        if ($conn->connect_error) {
            throw new Exception($conn->connect_error);
        }
    } catch (Exception $ex) {
        die("Connection failed. Remote error: " . $e->getMessage() . " | Local error: " . $ex->getMessage());
    }
}

// Set character set to UTF-8
if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset("utf8mb4");
}


?>
