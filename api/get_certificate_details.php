<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

$query = "
    SELECT a.*, 
           recip.full_name AS recipient_name, r_recip.role_name AS recipient_role,
           sender.full_name AS sender_name, r_sender.role_name AS sender_role
    FROM appreciations a
    LEFT JOIN users recip ON a.recipient_id = recip.user_id
    LEFT JOIN roles r_recip ON recip.role_id = r_recip.role_id
    LEFT JOIN users sender ON a.sender_id = sender.user_id
    LEFT JOIN roles r_sender ON sender.role_id = r_sender.role_id
    WHERE a.appreciation_id = $id
    LIMIT 1
";

$res = $conn->query($query);
$cert = $res ? $res->fetch_assoc() : null;

if ($cert) {
    echo json_encode(['status' => 'success', 'certificate' => $cert]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Certificate not found']);
}
