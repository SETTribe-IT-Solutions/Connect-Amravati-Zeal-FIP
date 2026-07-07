<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Balaji Chaugule';
$_SESSION['user_role'] = 'System Administrator';
$_SESSION['district_id'] = 1;
$_SESSION['last_activity'] = time();
header('Location: dashboard.php');
?>
