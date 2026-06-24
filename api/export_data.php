<?php
/**
 * =============================================================
 *  api/export_data.php  |  Task Data Export Endpoint
 * =============================================================
 *  GET params:
 *    type  = csv | excel          (output format)
 *    scope = district | taluka | village   (data scope)
 *    lang  = en | mr              (language)
 * =============================================================
 */

session_start();
require_once __DIR__ . '/../include/dbConfig.php';

$type  = $_GET['type']  ?? 'csv';
$scope = $_GET['scope'] ?? 'district';
$lang  = $_GET['lang']  ?? 'en';

// ─── Headers ────────────────────────────────────────────────
$headers_en = ['Task ID', 'Task Title', 'Description', 'Priority', 'Status', 'Created By', 'Assigned To', 'Taluka', 'Village', 'Created Date', 'Due Date'];
$headers_mr = ['कार्य क्रमांक', 'कार्य शीर्षक', 'वर्णन', 'प्राधान्य', 'स्थिती', 'तयार केले', 'नियुक्त', 'तालुका', 'गाव', 'तयार दिनांक', 'देय दिनांक'];
$headers = ($lang === 'mr') ? $headers_mr : $headers_en;

// ─── Query ──────────────────────────────────────────────────
$params = [];
$paramTypes = '';
$conditions = [];

$sql = "
    SELECT 
        t.task_id,
        t.task_title,
        COALESCE(t.description, '') AS description,
        COALESCE(t.priority, 'Normal') AS priority,
        COALESCE(t.status, 'Pending') AS status,
        COALESCE(creator.full_name, creator.employee_code, 'N/A') AS created_by,
        GROUP_CONCAT(DISTINCT COALESCE(assignee.full_name, assignee.employee_code, 'Unassigned') SEPARATOR ', ') AS assigned_to,
        COALESCE(tk.taluka_name, 'N/A') AS taluka_name,
        COALESCE(v.village_name, 'N/A') AS village_name,
        DATE_FORMAT(t.created_at, '%Y-%m-%d') AS created_date,
        COALESCE(DATE_FORMAT(t.due_date, '%Y-%m-%d'), 'N/A') AS due_date
    FROM tasks t
    LEFT JOIN users creator ON t.created_by = creator.user_id
    LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
    LEFT JOIN users assignee ON ta.assigned_to_user = assignee.user_id
    LEFT JOIN talukas tk ON COALESCE(t.taluka_id, creator.taluka_id) = tk.taluka_id
    LEFT JOIN villages v ON COALESCE(t.village_id, creator.village_id) = v.village_id
";

if ($scope === 'reports') {
    if (empty($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
    }
    $userId = (int)$_SESSION['user_id'];
    $tab = $_GET['tab'] ?? 'assigned';
    $search = $_GET['search'] ?? '';
    $filterStatus = $_GET['status'] ?? 'All';
    $filterPriority = $_GET['priority'] ?? 'All';

    if ($tab === 'allocated') {
        $conditions[] = "(t.created_by = ? OR t.task_id IN (SELECT ta2.task_id FROM task_assignments ta2 WHERE ta2.assigned_from_user = ?))";
    } else {
        $conditions[] = "(t.assigned_user_id = ? OR t.task_id IN (SELECT ta2.task_id FROM task_assignments ta2 WHERE ta2.assigned_to_user = ?))";
    }
    $params[] = $userId;
    $params[] = $userId;
    $paramTypes .= 'ii';

    if (!empty($search)) {
        $conditions[] = "(t.task_title LIKE ? OR t.task_no LIKE ? OR t.description LIKE ?)";
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $paramTypes .= 'sss';
    }

    if ($filterStatus !== 'All') {
        if ($filterStatus === 'Overdue') {
            $conditions[] = "t.due_date < CURDATE() AND t.status != 'Completed'";
        } else {
            $conditions[] = "t.status = ?";
            $params[] = $filterStatus;
            $paramTypes .= 's';
        }
    }

    if ($filterPriority !== 'All') {
        $conditions[] = "t.priority = ?";
        $params[] = $filterPriority;
        $paramTypes .= 's';
    }
} else {
    if ($scope === 'taluka' && isset($_GET['taluka_id'])) {
        $conditions[] = "(t.taluka_id = ? OR creator.taluka_id = ?)";
        $params[] = (int) $_GET['taluka_id'];
        $params[] = (int) $_GET['taluka_id'];
        $paramTypes .= 'ii';
    } elseif ($scope === 'village' && isset($_GET['village_id'])) {
        $conditions[] = "(t.village_id = ? OR creator.village_id = ?)";
        $params[] = (int) $_GET['village_id'];
        $params[] = (int) $_GET['village_id'];
        $paramTypes .= 'ii';
    }
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY t.task_id ORDER BY t.created_at DESC";

$rows = [];
try {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($paramTypes, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
} catch (Exception $e) {
    error_log('export_data.php error: ' . $e->getMessage());
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Export failed: ' . htmlspecialchars($e->getMessage());
    exit;
}

// ─── Output ─────────────────────────────────────────────────
$filename = 'amravati_connect_tasks_' . $scope . '_' . date('Y-m-d');

if ($type === 'excel') {
    // Tab-separated values with .xls extension (opens universally in Excel)
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
    echo implode("\t", $headers) . "\n";
    foreach ($rows as $row) {
        echo implode("\t", array_values($row)) . "\n";
    }
} else {
    // CSV (default)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, array_values($row));
    }
    fclose($out);
}

exit;
?>
