<?php
session_start();
require_once 'include/dbConfig.php';

// Fetch the first active user (preferably Collector or Admin)
$res = $conn->query("
    SELECT u.*, r.role_name, r.role_level
    FROM users u
    LEFT JOIN roles r ON u.role_id = r.role_id
    WHERE u.status = 'Active'
    ORDER BY r.role_level ASC, u.user_id ASC
    LIMIT 1
");

if ($res && $user = $res->fetch_assoc()) {
    session_regenerate_id(true);
    $_SESSION['user_id']           = $user['user_id'];
    $_SESSION['employee_code']     = $user['employee_code'];
    $_SESSION['full_name']         = $user['full_name'];
    $_SESSION['role_name']         = $user['role_name'];
    $_SESSION['user_name']         = $user['full_name'];
    $_SESSION['user_role']         = $user['role_name'];
    $_SESSION['can_allocate_task'] = $user['can_allocate_task'] ?? 1;
    $_SESSION['district_id']       = $user['district_id'] ?? 1;
    $_SESSION['taluka_id']         = $user['taluka_id'];
    $_SESSION['village_id']        = $user['village_id'];
    $_SESSION['email']             = $user['email'];
    $_SESSION['user_level']        = (int)$user['role_level'];
    $_SESSION['last_activity']     = time();
    
    header("Location: dashboard.php");
    exit;
} else {
    echo "No active user found in database.";
}
?>
