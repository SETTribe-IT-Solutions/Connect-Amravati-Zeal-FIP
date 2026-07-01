<?php
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$talukaId = isset($_GET['taluka_id']) ? (int)$_GET['taluka_id'] : 0;
$villageId = isset($_GET['village_id']) ? (int)$_GET['village_id'] : 0;

$where = "WHERE 1=1";
$locationName = "District";

if ($villageId > 0) {
    $where = "WHERE t.village_id = $villageId";
    $resName = $conn->query("SELECT village_name FROM villages WHERE village_id = $villageId LIMIT 1");
    if ($resName && $row = $resName->fetch_assoc()) {
        $locationName = $row['village_name'];
    }
} elseif ($talukaId > 0) {
    $where = "WHERE t.taluka_id = $talukaId";
    $resName = $conn->query("SELECT taluka_name FROM talukas WHERE taluka_id = $talukaId LIMIT 1");
    if ($resName && $row = $resName->fetch_assoc()) {
        $locationName = $row['taluka_name'];
    }
}

// 1. Total & Status count
$statusCounts = ['Pending' => 0, 'In Progress' => 0, 'Completed' => 0, 'Rejected' => 0, 'On Hold' => 0];
$resStats = $conn->query("SELECT status, COUNT(*) as qty FROM tasks t $where GROUP BY status");
$totalTasks = 0;
if ($resStats) {
    while ($row = $resStats->fetch_assoc()) {
        $st = $row['status'];
        if (array_key_exists($st, $statusCounts)) {
            $statusCounts[$st] = (int)$row['qty'];
        }
        $totalTasks += (int)$row['qty'];
    }
}

// 2. Category breakdown
$categoryCounts = [];
$resCats = $conn->query("SELECT t.task_category, COUNT(*) as qty FROM tasks t $where GROUP BY t.task_category");
if ($resCats) {
    while ($row = $resCats->fetch_assoc()) {
        $cat = $row['task_category'] ? $row['task_category'] : 'General';
        $categoryCounts[$cat] = (int)$row['qty'];
    }
}

// 3. Task list: title, who allocated to whom, status
$tasksList = [];
$resTasks = $conn->query("
    SELECT t.task_id, t.task_title, t.task_no, t.status, t.due_date, t.task_category,
           creator.full_name AS creator_name, creator_role.role_name AS creator_role,
           assignee.full_name AS assignee_name, assignee_role.role_name AS assignee_role
    FROM tasks t
    LEFT JOIN users creator ON t.created_by = creator.user_id
    LEFT JOIN roles creator_role ON creator.role_id = creator_role.role_id
    LEFT JOIN users assignee ON t.assigned_user_id = assignee.user_id
    LEFT JOIN roles assignee_role ON assignee.role_id = assignee_role.role_id
    $where
    ORDER BY t.created_at DESC
");
if ($resTasks) {
    while ($row = $resTasks->fetch_assoc()) {
        $tasksList[] = $row;
    }
}

echo json_encode([
    'status' => 'success',
    'location_name' => $locationName,
    'total_tasks' => $totalTasks,
    'status_counts' => $statusCounts,
    'category_counts' => $categoryCounts,
    'tasks' => $tasksList
]);
