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

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8mb4");

?>
