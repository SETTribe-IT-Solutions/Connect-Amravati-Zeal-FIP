<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$recipientId = isset($_GET['recipient_id']) ? (int)$_GET['recipient_id'] : 0;
if ($recipientId <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch only tasks that are assigned to the recipient user
$tasks = [];
$res = $conn->query("
    SELECT task_id, task_title, task_no, status
    FROM tasks
    WHERE assigned_user_id = $recipientId
    ORDER BY created_at DESC
    LIMIT 100
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $tasks[] = [
            'task_id'    => $row['task_id'],
            'task_title' => $row['task_title'],
            'task_no'    => $row['task_no'] ?: '#' . $row['task_id'],
            'status'     => $row['status'],
        ];
    }
}

echo json_encode($tasks);
