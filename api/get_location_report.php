<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$talukaId = isset($_GET['taluka_id']) ? (int)$_GET['taluka_id'] : 0;
$villageId = isset($_GET['village_id']) ? (int)$_GET['village_id'] : 0;

$locationName = "District";

/* ─── Build WHERE clause ──────────────────────────────────────────────────
   Tasks may be linked to a location in two ways:
     1. Directly via tasks.taluka_id / tasks.village_id  (newer schema)
     2. Via the assigned user's taluka_id / village_id   (older schema)
   We merge both to avoid missing any tasks.
   ────────────────────────────────────────────────────────────────────── */
$joinClause  = "LEFT JOIN task_assignments ta2 ON t.task_id = ta2.task_id
                LEFT JOIN users u2 ON ta2.assigned_to_user = u2.user_id";
$whereClause = "WHERE 1=1";

if ($villageId > 0) {
    $whereClause = "WHERE (t.village_id = $villageId OR u2.village_id = $villageId)";
    $resName = $conn->query("SELECT village_name FROM villages WHERE village_id = $villageId LIMIT 1");
    if ($resName && $row = $resName->fetch_assoc()) {
        $locationName = $row['village_name'];
    }
} elseif ($talukaId > 0) {
    $whereClause = "WHERE (t.taluka_id = $talukaId OR u2.taluka_id = $talukaId)";
    $resName = $conn->query("SELECT taluka_name FROM talukas WHERE taluka_id = $talukaId LIMIT 1");
    if ($resName && $row = $resName->fetch_assoc()) {
        $locationName = $row['taluka_name'];
    }
}

// 1. Total & Status counts  ──────────────────────────────────────────────
$statusCounts = [
    'Pending'     => 0,
    'In Progress' => 0,
    'Completed'   => 0,
    'Rejected'    => 0,
    'On Hold'     => 0,
];
$totalTasks = 0;

$sqlStats = "SELECT t.status, COUNT(DISTINCT t.task_id) as qty
             FROM tasks t
             $joinClause
             $whereClause
             GROUP BY t.status";

$resStats = $conn->query($sqlStats);
if ($resStats) {
    while ($row = $resStats->fetch_assoc()) {
        $st  = $row['status'];
        $qty = (int)$row['qty'];
        $totalTasks += $qty;

        if (in_array($st, ['Completed', 'Verified'])) {
            $statusCounts['Completed'] += $qty;
        } elseif (in_array($st, ['In Progress', 'Accepted', 'Reassigned', 'Assigned'])) {
            $statusCounts['In Progress'] += $qty;
        } elseif ($st === 'On Hold') {
            $statusCounts['On Hold'] += $qty;
        } elseif (in_array($st, ['Rejected', 'Approved Rejection', 'Denied'])) {
            $statusCounts['Rejected'] += $qty;
        } else {
            // Pending, Overdue, Escalated, Pending Verification, and any unknown status
            $statusCounts['Pending'] += $qty;
        }
    }
}

// 2. Category breakdown ──────────────────────────────────────────────────
$categoryCounts = [];
$sqlCats = "SELECT t.task_category, COUNT(DISTINCT t.task_id) as qty
            FROM tasks t
            $joinClause
            $whereClause
            GROUP BY t.task_category";

$resCats = $conn->query($sqlCats);
if ($resCats) {
    while ($row = $resCats->fetch_assoc()) {
        $cat = $row['task_category'] ?: 'General';
        $categoryCounts[$cat] = (int)$row['qty'];
    }
}

// 3. Task list: title, creator, assignee, status ─────────────────────────
$tasksList = [];
$sqlTasks = "SELECT DISTINCT t.task_id, t.task_title, t.task_no, t.status, t.due_date, t.task_category, t.created_at,
               creator.full_name        AS creator_name,
               creator_role.role_name   AS creator_role,
               assignee.full_name       AS assignee_name,
               assignee_role.role_name  AS assignee_role
             FROM tasks t
             $joinClause
             LEFT JOIN users creator          ON t.created_by = creator.user_id
             LEFT JOIN roles creator_role     ON creator.role_id = creator_role.role_id
             LEFT JOIN users assignee         ON t.assigned_user_id = assignee.user_id
             LEFT JOIN roles assignee_role    ON assignee.role_id = assignee_role.role_id
             $whereClause
             ORDER BY t.created_at DESC
             LIMIT 100";

$resTasks = $conn->query($sqlTasks);
if ($resTasks) {
    while ($row = $resTasks->fetch_assoc()) {
        $tasksList[] = $row;
    }
}

echo json_encode([
    'status'          => 'success',
    'location_name'   => $locationName,
    'total_tasks'     => $totalTasks,
    'status_counts'   => $statusCounts,
    'category_counts' => $categoryCounts,
    'tasks'           => $tasksList,
], JSON_UNESCAPED_UNICODE);
