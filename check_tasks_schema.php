<?php
require_once 'include/dbConfig.php';

$res = $conn->query("SELECT COUNT(*) FROM tasks");
if ($res) {
    $row = $res->fetch_row();
    echo "Total tasks in DB: " . $row[0] . "\n";
} else {
    echo "Error tasks count: " . $conn->error . "\n";
}

$res = $conn->query("SELECT * FROM tasks LIMIT 5");
if ($res && $res->num_rows > 0) {
    echo "\nSample Tasks:\n";
    while($row = $res->fetch_assoc()){
        echo "ID: " . $row['task_id'] . " | No: " . $row['task_no'] . " | Title: " . $row['task_title'] . " | Assigned User ID: " . $row['assigned_user_id'] . " | Created By: " . $row['created_by'] . " | Status: " . $row['status'] . "\n";
    }
}

$res = $conn->query("SELECT COUNT(*) FROM task_assignments");
if ($res) {
    $row = $res->fetch_row();
    echo "\nTotal task_assignments in DB: " . $row[0] . "\n";
} else {
    echo "Error task_assignments count: " . $conn->error . "\n";
}

$res = $conn->query("SELECT * FROM task_assignments LIMIT 5");
if ($res && $res->num_rows > 0) {
    echo "\nSample task_assignments:\n";
    while($row = $res->fetch_assoc()){
        echo "ID: " . $row['assignment_id'] . " | Task ID: " . $row['task_id'] . " | From: " . $row['assigned_from_user'] . " | To: " . $row['assigned_to_user'] . " | Status: " . $row['status'] . "\n";
    }
}
?>
