<?php
require_once 'include/dbConfig.php';

$res = $conn->query("SELECT email, full_name, failed_login_attempts, locked_until FROM users LIMIT 10");
$rows = [];
while($row = $res->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
