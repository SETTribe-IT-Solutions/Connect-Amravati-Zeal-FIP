<?php
/**
 * api/get_report_details.php
 * Fetches detailed list of tasks based on selected metric and scope (district, taluka, village).
 */

session_start();
require_once '../include/dbConfig.php';

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

// Translations for report modal headers/labels
$notif_translations = [
    'en' => [
        'modal_title' => 'Detailed Report: %s Tasks',
        'district_scope' => 'District-wide',
        'taluka_scope' => 'Taluka: %s',
        'village_scope' => 'Village: %s',
        'no_tasks' => 'No matching tasks found.',
        'days_overdue' => '%d days overdue',
        'days_left' => '%d days remaining',
        'today' => 'Due today',
    ],
    'mr' => [
        'modal_title' => 'तपशीलवार अहवाल: %s कार्ये',
        'district_scope' => 'जिल्हा-स्तरीय',
        'taluka_scope' => 'तालुका: %s',
        'village_scope' => 'गाव: %s',
        'no_tasks' => 'कोणतेही जुळणारे कार्य आढळले नाही.',
        'days_overdue' => '%d दिवस थकीत',
        'days_left' => '%d दिवस शिल्लक',
        'today' => 'आज मुदत आहे',
    ]
];
$t = $notif_translations[$lang];

// Parameters
$metric = $_GET['metric'] ?? 'all'; // active, pending, completed, overdue, total
$level = isset($_GET['level']) ? (int)$_GET['level'] : 1;
$taluka_id = isset($_GET['taluka_id']) ? (int)$_GET['taluka_id'] : null;
$village_id = isset($_GET['village_id']) ? (int)$_GET['village_id'] : null;
$taluka_name = $_GET['taluka_name'] ?? null;
$village_name = $_GET['village_name'] ?? null;

$tasks = [];
$scope_text = $t['district_scope'];
$used_mock = false;

try {
    // Determine where filter
    $where_clauses = ["1=1"];
    $params = [];
    $types = "";

    // Geography scope
    if (!empty($village_name) && $village_name !== 'Unknown') {
        $where_clauses[] = "v.village_name = ?";
        $types .= "s";
        $params[] = $village_name;
        $scope_text = sprintf($t['village_scope'], $village_name);
    } elseif (!empty($taluka_name) && $taluka_name !== 'Unknown') {
        $where_clauses[] = "tk.taluka_name = ?";
        $types .= "s";
        $params[] = $taluka_name;
        $scope_text = sprintf($t['taluka_scope'], $taluka_name);
    } else {
        if ($level === 3 && $village_id) {
            $where_clauses[] = "t.village_id = ?";
            $types .= "i";
            $params[] = $village_id;
            // Try fetching village name for scope text
            $st_v = $conn->prepare("SELECT village_name FROM villages WHERE village_id = ? LIMIT 1");
            if ($st_v) {
                $st_v->bind_param("i", $village_id);
                $st_v->execute();
                $res_v = $st_v->get_result()->fetch_assoc();
                if ($res_v) $scope_text = sprintf($t['village_scope'], $res_v['village_name']);
                $st_v->close();
            }
        } elseif ($level === 2 && $taluka_id) {
            $where_clauses[] = "t.taluka_id = ?";
            $types .= "i";
            $params[] = $taluka_id;
            // Try fetching taluka name for scope text
            $st_t = $conn->prepare("SELECT taluka_name FROM talukas WHERE taluka_id = ? LIMIT 1");
            if ($st_t) {
                $st_t->bind_param("i", $taluka_id);
                $st_t->execute();
                $res_t = $st_t->get_result()->fetch_assoc();
                if ($res_t) $scope_text = sprintf($t['taluka_scope'], $res_t['taluka_name']);
                $st_t->close();
            }
        } elseif ($taluka_id) {
            $where_clauses[] = "t.taluka_id = ?";
            $types .= "i";
            $params[] = $taluka_id;
        }
    }

    // Metric filter
    switch ($metric) {
        case 'active':
            $where_clauses[] = "t.status != 'Completed'";
            break;
        case 'pending':
            $where_clauses[] = "t.status = 'Pending' AND (t.due_date >= CURDATE() OR t.due_date IS NULL)";
            break;
        case 'completed':
            $where_clauses[] = "t.status = 'Completed'";
            break;
        case 'overdue':
            $where_clauses[] = "t.due_date < CURDATE() AND t.status != 'Completed'";
            break;
        case 'total':
        default:
            break;
    }

    $where_sql = implode(" AND ", $where_clauses);

    $query = "
        SELECT
            t.task_id,
            t.task_no,
            t.task_title AS title,
            t.task_description AS description,
            CASE WHEN t.due_date < CURDATE() AND t.status != 'Completed' THEN 'Overdue' ELSE t.status END AS status,
            t.due_date,
            t.priority,
            t.task_category,
            COALESCE(u.full_name, r.role_name, 'Unassigned') AS assigned_to_name,
            COALESCE(u.designation, r.role_name, 'Unassigned') AS assigned_designation,
            tk.taluka_name,
            v.village_name
        FROM tasks t
        LEFT JOIN users u ON t.assigned_user_id = u.user_id
        LEFT JOIN roles r ON t.assigned_role_id = r.role_id
        LEFT JOIN talukas tk ON t.taluka_id = tk.taluka_id
        LEFT JOIN villages v ON t.village_id = v.village_id
        WHERE $where_sql
        ORDER BY FIELD(CASE WHEN t.due_date < CURDATE() AND t.status != 'Completed' THEN 'Overdue' ELSE t.status END,'Overdue','Pending','In Progress','Completed'),
                 t.due_date ASC
    ";

    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($types)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $timeline_info = '';
            if (!empty($row['due_date'])) {
                $due_ts = strtotime($row['due_date']);
                $today_ts = strtotime(date('Y-m-d'));
                $diff_days = round(($due_ts - $today_ts) / 86400);
                
                if ($row['status'] === 'Completed') {
                    $timeline_info = '';
                } elseif ($diff_days < 0) {
                    $timeline_info = sprintf($t['days_overdue'], abs($diff_days));
                } elseif ($diff_days > 0) {
                    $timeline_info = sprintf($t['days_left'], $diff_days);
                } else {
                    $timeline_info = $t['today'];
                }
            }
            $row['timeline_info'] = $timeline_info;
            $row['due_date_formatted'] = !empty($row['due_date']) ? date('d M Y', strtotime($row['due_date'])) : '—';
            $tasks[] = $row;
        }
        $stmt->close();
    } else {
        throw new Exception("SQL prepare failed");
    }

} catch (Exception $e) {
    // Graceful fallback to Mock Data if DB fails or tables are missing
    $used_mock = true;
    $tasks = getMockTasksFallback($metric, $taluka_name, $village_name, $level, $taluka_id, $village_id, $t);
    
    // Set scope text for mock data
    if (!empty($village_name)) {
        $scope_text = sprintf($t['village_scope'], $village_name);
    } elseif (!empty($taluka_name)) {
        $scope_text = sprintf($t['taluka_scope'], $taluka_name);
    } elseif ($level === 3) {
        $scope_text = sprintf($t['village_scope'], 'Paratwada');
    } elseif ($level === 2) {
        $scope_text = sprintf($t['taluka_scope'], 'Amravati');
    } else {
        $scope_text = $t['district_scope'];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'scope' => $scope_text,
    'metric' => $metric,
    'tasks' => $tasks,
    'is_mock' => $used_mock
]);

/**
 * Generates mock tasks matching filters when DB is offline
 */
function getMockTasksFallback(string $metric, ?string $taluka_name, ?string $village_name, int $level, ?int $taluka_id, ?int $village_id, array $t): array {
    // Master mock tasks mimicking the structure and details
    $all_mock = [
        // Amravati (Taluka 1)
        ['task_id'=>'TSK-8941','task_no'=>'TSK-8941','title'=>'Crop Damage Assessment','description'=>'Survey crop damage in local farms due to recent heavy rainfall.','status'=>'In Progress','due_date'=>'2026-06-28','priority'=>'High','assigned_to_name'=>'Anil Patil','assigned_designation'=>'Talathi','taluka_name'=>'Amravati','village_name'=>'Nandapur'],
        ['task_id'=>'TSK-8902','task_no'=>'TSK-8902','title'=>'E-KYC Verification Camp','description'=>'Organize UIDAI E-KYC verification camp for senior citizens in Chandurbazar.','status'=>'Pending','due_date'=>'2026-06-24','priority'=>'Medium','assigned_to_name'=>'Sunita More','assigned_designation'=>'Gramsevak','taluka_name'=>'Amravati','village_name'=>'Chandurbazar'],
        ['task_id'=>'TSK-8850','task_no'=>'TSK-8850','title'=>'7/12 Record Update','description'=>'Update land tenancy records in Wagholi village register.','status'=>'Completed','due_date'=>'2026-06-15','priority'=>'Low','assigned_to_name'=>'Rajesh Kolhe','assigned_designation'=>'Talathi','taluka_name'=>'Amravati','village_name'=>'Wagholi'],
        ['task_id'=>'TSK-8831','task_no'=>'TSK-8831','title'=>'Village Pond Water Survey','description'=>'Inspect water quality levels and report silt accumulation in Paratwada pond.','status'=>'Overdue','due_date'=>'2026-06-10','priority'=>'High','assigned_to_name'=>'Meena Shinde','assigned_designation'=>'Gramsevak','taluka_name'=>'Amravati','village_name'=>'Paratwada'],
        ['task_id'=>'TSK-8820','task_no'=>'TSK-8820','title'=>'PM Awas Beneficiary Listing','description'=>'Prepare the final eligibility list for PM Awas Yojana beneficiaries in Morshi.','status'=>'Pending','due_date'=>'2026-06-28','priority'=>'High','assigned_to_name'=>'Anil Patil','assigned_designation'=>'Talathi','taluka_name'=>'Amravati','village_name'=>'Morshi'],
        ['task_id'=>'TSK-8800','task_no'=>'TSK-8800','title'=>'Street Light Repair Report','description'=>'Audit solar street lights and catalog repair actions in Dhamangaon.','status'=>'Completed','due_date'=>'2026-06-12','priority'=>'Low','assigned_to_name'=>'Sunita More','assigned_designation'=>'Gramsevak','taluka_name'=>'Amravati','village_name'=>'Dhamangaon'],
        // Achalpur (Taluka 2)
        ['task_id'=>'TSK-7101','task_no'=>'TSK-7101','title'=>'Drinking Water Tank Inspection','description'=>'Audit water purity and chlorine levels in central tank.','status'=>'Overdue','due_date'=>'2026-06-05','priority'=>'High','assigned_to_name'=>'Vikas Rathod','assigned_designation'=>'Gramsevak','taluka_name'=>'Achalpur','village_name'=>'Paratwada'],
        ['task_id'=>'TSK-7102','task_no'=>'TSK-7102','title'=>'Anganwadi Health Audit','description'=>'Check growth records and supply of nutritional packets.','status'=>'In Progress','due_date'=>'2026-06-21','priority'=>'Medium','assigned_to_name'=>'Sneha Patil','assigned_designation'=>'Gramsevak','taluka_name'=>'Achalpur','village_name'=>'Paratwada'],
        ['task_id'=>'TSK-7103','task_no'=>'TSK-7103','title'=>'Gram Panchayat Fund Verification','description'=>'Verify expenditure ledger accounts for rural roads project.','status'=>'Completed','due_date'=>'2026-06-01','priority'=>'Low','assigned_to_name'=>'Sanjay Deshmukh','assigned_designation'=>'Tehsildar','taluka_name'=>'Achalpur','village_name'=>'Morshi'],
        // Chandur Railway (Taluka 3)
        ['task_id'=>'TSK-6201','task_no'=>'TSK-6201','title'=>'Soil Testing Campaign','description'=>'Collect soil samples across selected fields for testing.','status'=>'Pending','due_date'=>'2026-06-25','priority'=>'Medium','assigned_to_name'=>'Ramesh Gawande','assigned_designation'=>'Talathi','taluka_name'=>'Chandur Railway','village_name'=>'Wagholi'],
        ['task_id'=>'TSK-6202','task_no'=>'TSK-6202','title'=>'Farmer Subsidies Distribution','description'=>'Audit disbursement of seed and fertilizer subsidies.','status'=>'Overdue','due_date'=>'2026-06-08','priority'=>'High','assigned_to_name'=>'Pooja Kale','assigned_designation'=>'Gramsevak','taluka_name'=>'Chandur Railway','village_name'=>'Dhamangaon'],
        // Daryapur (Taluka 4)
        ['task_id'=>'TSK-5301','task_no'=>'TSK-5301','title'=>'Silt Removal Monitoring','description'=>'Oversee excavation of silt from village canal systems.','status'=>'Completed','due_date'=>'2026-06-10','priority'=>'Medium','assigned_to_name'=>'Harish Tayade','assigned_designation'=>'Talathi','taluka_name'=>'Daryapur','village_name'=>'Nandapur'],
        ['task_id'=>'TSK-5302','task_no'=>'TSK-5302','title'=>'Borewell Recharge Verification','description'=>'Inspect location and structure of new borewell units.','status'=>'Overdue','due_date'=>'2026-06-07','priority'=>'High','assigned_to_name'=>'Kiran Wankhede','assigned_designation'=>'Gramsevak','taluka_name'=>'Daryapur','village_name'=>'Chandurbazar'],
        // Nandgaon Kh. (Taluka 5)
        ['task_id'=>'TSK-4401','task_no'=>'TSK-4401','title'=>'Rural Road Repairs Survey','description'=>'Map out stretches of asphalt requiring patch repair.','status'=>'In Progress','due_date'=>'2026-06-27','priority'=>'High','assigned_to_name'=>'Amol Chore','assigned_designation'=>'Gramsevak','taluka_name'=>'Nandgaon Kh.','village_name'=>'Wagholi'],
        ['task_id'=>'TSK-4402','task_no'=>'TSK-4402','title'=>'Panchayat Ghar Solar Setup','description'=>'Install and test clean solar panels on government roof.','status'=>'Overdue','due_date'=>'2026-06-03','priority'=>'High','assigned_to_name'=>'Nisha Bobde','assigned_designation'=>'Talathi','taluka_name'=>'Nandgaon Kh.','village_name'=>'Dhamangaon'],
        // Warud (Taluka 6)
        ['task_id'=>'TSK-3501','task_no'=>'TSK-3501','title'=>'Orange Plantation Inspection','description'=>'Inspect orange orchids for disease outbreaks.','status'=>'Completed','due_date'=>'2026-06-14','priority'=>'Low','assigned_to_name'=>'Pravin Raut','assigned_designation'=>'Talathi','taluka_name'=>'Warud','village_name'=>'Morshi'],
        ['task_id'=>'TSK-3502','task_no'=>'TSK-3502','title'=>'Cold Storage Feasibility Study','description'=>'Assess capacity and logistics needs for cold storage.','status'=>'Overdue','due_date'=>'2026-06-09','priority'=>'High','assigned_to_name'=>'Varsha Bhat','assigned_designation'=>'Gramsevak','taluka_name'=>'Warud','village_name'=>'Nandapur'],
    ];

    // Determine geographical filtering defaults based on level if no names were specified
    $f_taluka = $taluka_name;
    $f_village = $village_name;

    if (empty($f_taluka) && empty($f_village)) {
        if ($level === 3) {
            $f_village = 'Paratwada';
            $f_taluka = 'Amravati';
        } elseif ($level === 2) {
            $f_taluka = 'Amravati';
        }
    }

    $filtered = [];
    foreach ($all_mock as $task) {
        // Taluka match
        if (!empty($f_taluka) && strcasecmp($task['taluka_name'], $f_taluka) !== 0) {
            continue;
        }
        // Village match
        if (!empty($f_village) && strcasecmp($task['village_name'], $f_village) !== 0) {
            continue;
        }

        // Metric match
        if ($metric === 'active' && $task['status'] === 'Completed') {
            continue;
        }
        if ($metric === 'pending' && $task['status'] !== 'Pending') {
            continue;
        }
        if ($metric === 'completed' && $task['status'] !== 'Completed') {
            continue;
        }
        if ($metric === 'overdue' && $task['status'] !== 'Overdue') {
            continue;
        }

        // Calculate timeline info
        $timeline_info = '';
        if (!empty($task['due_date'])) {
            $due_ts = strtotime($task['due_date']);
            $today_ts = strtotime(date('Y-m-d'));
            $diff_days = round(($due_ts - $today_ts) / 86400);
            
            if ($task['status'] === 'Completed') {
                $timeline_info = '';
            } elseif ($diff_days < 0) {
                $timeline_info = sprintf($t['days_overdue'], abs($diff_days));
            } elseif ($diff_days > 0) {
                $timeline_info = sprintf($t['days_left'], $diff_days);
            } else {
                $timeline_info = $t['today'];
            }
        }
        $task['timeline_info'] = $timeline_info;
        $task['due_date_formatted'] = !empty($task['due_date']) ? date('d M Y', strtotime($task['due_date'])) : '—';

        $filtered[] = $task;
    }
    return $filtered;
}
