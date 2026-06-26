<?php
require_once 'include/dbConfig.php';
$result = $conn->query("SHOW COLUMNS FROM task_tracking");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
