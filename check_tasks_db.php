<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'include/dbConfig.php';

$tables = ['tasks', 'task_assignments', 'task_documents', 'task_remarks', 'task_status_history'];

foreach ($tables as $t) {
    echo "=== Table: $t ===\n";
    $res = $conn->query("DESCRIBE `$t`");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "  " . $row['Field'] . " - " . $row['Type'] . " - " . $row['Null'] . " - " . $row['Key'] . " - " . $row['Default'] . "\n";
        }
    } else {
        echo "Error or table does not exist: " . $conn->error . "\n";
    }
    echo "\n";
}
?>
