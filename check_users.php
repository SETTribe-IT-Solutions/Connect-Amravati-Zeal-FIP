<?php
require_once 'include/dbConfig.php';

$result = $conn->query("SELECT * FROM users LIMIT 10");
if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "Email/Username: " . ($row['email'] ?? $row['username'] ?? 'N/A') . " | Password Hash: " . ($row['password'] ?? 'N/A') . "\n";
        }
    } else {
        echo "0 results in users table";
    }
} else {
    echo "Error querying users table: " . $conn->error;
}
?>
