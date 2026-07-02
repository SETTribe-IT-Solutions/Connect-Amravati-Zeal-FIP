<?php
/**
 * api/search.php
 * Global Live Search API — Tasks, Officers, Circulars/Announcements
 * Returns JSON results for the dashboard search bar
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../include/dbConfig.php';

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode(['status' => 'ok', 'results' => []]);
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$userRole  = $_SESSION['user_role'] ?? 'Gramsevak';
$talukaId  = (int)($_SESSION['taluka_id']  ?? $_SESSION['user_taluka_id']  ?? 0);
$villageId = (int)($_SESSION['village_id'] ?? $_SESSION['user_village_id'] ?? 0);

// Role level
$roleLevelMap = [
    'Administrator' => 1, 'System Administrator' => 1,
    'Collector' => 1, 'Additional Collector' => 1, 'Deputy Collector' => 1,
    'SDO' => 2, 'Tehsildar' => 2, 'BDO' => 2,
    'Talathi' => 3, 'Gramsevak' => 3,
];
$userLevel = $roleLevelMap[$userRole] ?? 3;

$like = '%' . $q . '%';
$results = [];

try {
    /* ────────────────────────────────────────────────
       1. TASKS
    ──────────────────────────────────────────────── */
    $taskSql = "SELECT t.task_id, t.task_no, t.task_title AS title, t.status, t.priority, t.due_date,
                       MAX(u.full_name) AS assigned_to_name
                FROM tasks t
                LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
                LEFT JOIN users u ON ta.assigned_to_user = u.user_id
                WHERE (t.task_title LIKE ? OR t.task_id LIKE ? OR t.task_no LIKE ?)";

    $taskParams = [$like, $like, $like];
    $taskTypes  = 'sss';

    // Scope by user level — L1 sees all, L2/L3 see tasks assigned to them OR in their area
    if ($userLevel === 2 && $talukaId > 0) {
        // L2: tasks in their taluka, OR directly assigned to them, OR created by them
        $taskSql .= " AND (
            t.taluka_id = ?
            OR t.assigned_user_id = ?
            OR t.created_by = ?
            OR ta.assigned_to_user = ?
        )";
        $taskParams = array_merge($taskParams, [$talukaId, $userId, $userId, $userId]);
        $taskTypes .= 'iiii';
    } elseif ($userLevel === 3) {
        // L3: tasks assigned to them, OR in their village, OR created by them
        $taskSql .= " AND (
            t.assigned_user_id = ?
            OR t.created_by = ?
            OR ta.assigned_to_user = ?";
        $taskParams = array_merge($taskParams, [$userId, $userId, $userId]);
        $taskTypes .= 'iii';
        if ($villageId > 0) {
            $taskSql .= " OR t.village_id = ?";
            $taskParams[] = $villageId;
            $taskTypes .= 'i';
        }
        $taskSql .= ")";
    }

    $taskSql .= " GROUP BY t.task_id ORDER BY t.task_id DESC LIMIT 5";

    $stmt = $conn->prepare($taskSql);
    if ($stmt) {
        $stmt->bind_param($taskTypes, ...$taskParams);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $taskTitle = ($row['task_no'] ? $row['task_no'] . ' - ' : '') . $row['title'];
            $results[] = [
                'type'     => 'task',
                'icon'     => 'check-square',
                'id'       => $row['task_id'],
                'title'    => $taskTitle,
                'subtitle' => ($row['assigned_to_name'] ? 'Assigned to ' . $row['assigned_to_name'] : 'Unassigned')
                              . ($row['due_date'] ? ' · Due ' . date('d M Y', strtotime($row['due_date'])) : ''),
                'badge'    => $row['status'],
                'url'      => 'task_tracking.php?task_id=' . $row['task_id'],
            ];
        }
        $stmt->close();
    }

    /* ────────────────────────────────────────────────
       2. OFFICERS / USERS (Available to all logged-in roles)
    ──────────────────────────────────────────────── */
    $officerSql = "SELECT u.user_id, u.full_name, u.designation, u.employee_code, u.email, u.mobile, u.status,
                          r.role_name, d.department_name, t.taluka_name, v.village_name
                   FROM users u
                   LEFT JOIN roles r ON u.role_id = r.role_id
                   LEFT JOIN departments d ON u.department_id = d.department_id
                   LEFT JOIN talukas t ON u.taluka_id = t.taluka_id
                   LEFT JOIN villages v ON u.village_id = v.village_id
                   WHERE u.status = 'Active'
                     AND (u.full_name LIKE ? OR u.employee_code LIKE ? OR u.designation LIKE ?)";

    $officerParams = [$like, $like, $like];
    $officerTypes  = 'sss';

    if ($userLevel === 2 && $talukaId > 0) {
        $officerSql   .= " AND u.taluka_id = ?";
        $officerParams[] = $talukaId;
        $officerTypes .= 'i';
    }

    $officerSql .= " ORDER BY u.full_name ASC LIMIT 4";

    $stmt = $conn->prepare($officerSql);
    if ($stmt) {
        $stmt->bind_param($officerTypes, ...$officerParams);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = [
                'type'     => 'officer',
                'icon'     => 'user',
                'id'       => $row['user_id'],
                'title'    => $row['full_name'],
                'subtitle' => ($row['designation'] ?? $row['role_name'] ?? '') . ($row['department_name'] ? ' · ' . $row['department_name'] : ''),
                'badge'    => $row['employee_code'] ?? '',
                'url'      => 'user_creation.php?action=edit&id=' . $row['user_id'],
                'details'  => [
                    'full_name'     => $row['full_name'],
                    'designation'   => $row['designation'] ?? $row['role_name'] ?? 'Officer',
                    'employee_code' => $row['employee_code'] ?? 'N/A',
                    'email'         => $row['email'] ?? 'N/A',
                    'mobile'        => $row['mobile'] ?? 'N/A',
                    'department'    => $row['department_name'] ?? 'N/A',
                    'taluka'        => $row['taluka_name'] ?? 'N/A',
                    'village'       => $row['village_name'] ?? 'N/A',
                    'status'        => $row['status'] ?? 'Active'
                ]
            ];
        }
        $stmt->close();
    }

    /* ────────────────────────────────────────────────
       3. CIRCULARS / ANNOUNCEMENTS
    ──────────────────────────────────────────────── */
    $circularSql = "SELECT a.announcement_id, a.title, a.category, a.status, a.priority, a.created_at, a.description, a.attachment
                    FROM announcements a
                    WHERE a.status != 'Archived'
                      AND (a.title LIKE ? OR a.category LIKE ?)
                    ORDER BY a.announcement_id DESC
                    LIMIT 4";

    $stmt = $conn->prepare($circularSql);
    if ($stmt) {
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $results[] = [
                'type'     => 'circular',
                'icon'     => 'megaphone',
                'id'       => $row['announcement_id'],
                'title'    => $row['title'],
                'subtitle' => ($row['category'] ?? 'General') . ' · ' . ($row['status'] ?? 'Published'),
                'badge'    => $row['priority'] ?? '',
                'url'      => 'announcements.php',
                'details'  => [
                    'title'        => $row['title'],
                    'category'     => $row['category'] ?? 'General',
                    'priority'     => $row['priority'] ?? 'Medium',
                    'publish_date' => $row['created_at'] ? date('Y-m-d', strtotime($row['created_at'])) : 'N/A',
                    'description'  => $row['description'] ?? '',
                    'attachment'   => $row['attachment'] ?? ''
                ]
            ];
        }
        $stmt->close();
    }

    echo json_encode(['status' => 'ok', 'results' => $results, 'query' => $q]);

} catch (Throwable $e) {
    error_log('search.php error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Search failed', 'results' => []]);
}
