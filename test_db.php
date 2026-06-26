<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Status Diagnostic</h2>";

// 1. Check configured credentials
require_once 'include/dbConfig.php';

echo "<p>Connected successfully!</p>";
echo "<p>Active Host Info: " . $conn->host_info . "</p>";

// 2. Query tables and row counts
$tables = ['users', 'tasks', 'task_assignments', 'roles', 'talukas', 'villages', 'notifications', 'notification_delivery_logs'];
foreach ($tables as $t) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM `$t`");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Table <strong>$t</strong>: " . $row['cnt'] . " rows<br>";
    } else {
        echo "Table <strong>$t</strong> error: " . $conn->error . "<br>";
    }
}

// 3. Print notifications schema
echo "<h3>Notifications Table Structure:</h3>";
$res = $conn->query("DESCRIBE `notifications`");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
} else {
    echo "Error describing notifications: " . $conn->error;
}

// 4. Print first 5 users to verify data content
echo "<h3>Sample Users:</h3>";
$res = $conn->query("SELECT user_id, full_name, employee_code, email FROM users LIMIT 5");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . " | EmpCode: " . $row['employee_code'] . " | Name: " . $row['full_name'] . " | Email: " . $row['email'] . "<br>";
    }
} else {
    echo "Error fetching users: " . $conn->error;
}
?>
