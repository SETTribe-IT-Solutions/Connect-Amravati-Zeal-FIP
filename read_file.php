<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['file'])) {
    echo file_get_contents($_GET['file']);
}
?>
