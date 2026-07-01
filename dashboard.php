<?php
// Suppress error output to browser — errors go to PHP log only
ini_set('display_errors', '0');
ini_set('log_errors', '1');
/**
 * =============================================================
 *  dashboard.php  |  Amravati Connect – Role-Based Dashboard
 * =============================================================
 *  Extends / mirrors the design of blank_wrushabh.php.
 *
 *  Level 1 → System Administrator, Collector,
 *             Additional Collector, Deputy Collector
 *  Level 2 → SDO, Tehsildar, BDO
 *  Level 3 → Talathi, Gramsevak
 *
 *  Access:
 *    L1 = District + Taluka + Village
 *    L2 = Taluka  + Village
 *    L3 = Village only
 * =============================================================
 */

session_start();

// Attempt DB connection – disable strict exceptions so a remote-server
// 'max_connections_per_hour' error does NOT produce a fatal crash.
mysqli_report(MYSQLI_REPORT_OFF);
$conn = null;
try {
    require_once 'include/dbConfig.php';
    // Re-enable strict mode only after a successful connection
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
} catch (Throwable $dbEx) {
    error_log('Dashboard DB connection failed: ' . $dbEx->getMessage());
    // $conn stays null – dashboard will render with zero/mock values
}

// Fetch real-time dashboard statistics from the database
$totalActiveTasks = 0;
$pendingTasks = 0;
$completedTasks = 0;
$overdueTasks = 0;
$completionLabels = [];
$completionCounts = [];

// === NEW: Dynamic KPI, Line Chart & Pie Chart data ===
$dynamicTotalTasks = 0;          // MAX(task_id)
$dynamicStatusCounts = [];       // [{status=>'Pending', total=>N}, ...]
$dynamicCompletionLabels = [];   // ['2026-06-18', '2026-06-19', ...]
$dynamicCompletionCounts = [];   // [3, 5, ...]
$dynamicPieLabels = [];          // ['Pending','Completed', ...]
$dynamicPieCounts = [];          // [12, 8, ...]
$dynamicPiePercentages = [];     // [45.5, 30.2, ...]
$dynamicGrandTotal = 0;

if ($conn instanceof mysqli && !$conn->connect_error) {
    try {
        // 1. Total Active Tasks (legacy)
        $res = $conn->query("SELECT COUNT(*) AS total_active FROM tasks WHERE status IN ('Active','Pending','In Progress','Completed','Overdue','Escalated')");
        if ($res && $row = $res->fetch_assoc()) {
            $totalActiveTasks = (int)$row['total_active'];
        }

        // 2. Pending Tasks (legacy)
        $res = $conn->query("SELECT COUNT(*) AS pending_tasks FROM tasks WHERE status='Pending'");
        if ($res && $row = $res->fetch_assoc()) {
            $pendingTasks = (int)$row['pending_tasks'];
        }

        // 3. Completed Tasks (legacy)
        $res = $conn->query("SELECT COUNT(*) AS completed_tasks FROM tasks WHERE status='Completed'");
        if ($res && $row = $res->fetch_assoc()) {
            $completedTasks = (int)$row['completed_tasks'];
        }

        // 4. Escalated / Overdue Tasks (legacy)
        $res = $conn->query("SELECT COUNT(*) AS overdue_tasks FROM tasks WHERE status IN ('Overdue','Escalated')");
        if ($res && $row = $res->fetch_assoc()) {
            $overdueTasks = (int)$row['overdue_tasks'];
        }

        // Legacy Task Completion Trend (kept for existing ApexCharts)
        $q = "SELECT DATE(completion_date) AS completion_day, COUNT(*) AS completed_count
              FROM tasks
              WHERE status='Completed' AND completion_date IS NOT NULL
              GROUP BY DATE(completion_date)
              ORDER BY completion_day ASC";
        $resTrend = $conn->query($q);
        if ($resTrend) {
            while ($row = $resTrend->fetch_assoc()) {
                $completionLabels[] = $row['completion_day'];
                $completionCounts[] = (int)$row['completed_count'];
            }
        }

        // === NEW QUERY 1: Total Tasks = MAX(task_id) ===
        $res = $conn->query("SELECT MAX(task_id) AS total_tasks FROM tasks");
        if ($res && $row = $res->fetch_assoc()) {
            $dynamicTotalTasks = (int)$row['total_tasks'];
        }

        // === NEW QUERY 2: Status-wise KPI cards (dynamic) ===
        $res = $conn->query("SELECT status, COUNT(*) AS total FROM tasks GROUP BY status ORDER BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $dynamicStatusCounts[] = $row;
            }
        }

        // === NEW QUERY 3: Task Completion Trend (Date Wise) for Chart.js ===
        $res = $conn->query("SELECT DATE(completion_date) AS completed_date, COUNT(*) AS total_completed FROM tasks WHERE completion_date IS NOT NULL GROUP BY DATE(completion_date) ORDER BY completed_date ASC");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $dynamicCompletionLabels[] = $row['completed_date'];
                $dynamicCompletionCounts[] = (int)$row['total_completed'];
            }
        }

        // === NEW QUERY 4: Task Status Distribution for Pie Chart ===
        $res = $conn->query("SELECT status, COUNT(*) AS total FROM tasks GROUP BY status ORDER BY status");
        if ($res) {
            // First pass: collect all data and compute grand total
            $pieData = [];
            while ($row = $res->fetch_assoc()) {
                $pieData[] = $row;
                $dynamicGrandTotal += (int)$row['total'];
            }
            // Second pass: compute percentages
            foreach ($pieData as $pd) {
                $dynamicPieLabels[] = $pd['status'];
                $dynamicPieCounts[] = (int)$pd['total'];
                $dynamicPiePercentages[] = $dynamicGrandTotal > 0
                    ? round(((int)$pd['total'] / $dynamicGrandTotal) * 100, 1)
                    : 0;
            }
        }

    } catch (Exception $e) {
        error_log("Real-time dashboard card stats or trend query error: " . $e->getMessage());
    }
}

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title' => 'Dashboard — Amravati Connect | Government Workflow Platform',
        'desc' => 'Role-based executive dashboard for Amravati District. District, Taluka and Village level task insights.',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
        'menu_announcements' => 'Announcements',
        'menu_announcement_center' => 'Announcement Center',
        'menu_notifications' => 'Notification Center',
        'menu_appreciation' => 'Appreciation',
        'menu_analytics' => 'Analytics & Data',
        'menu_reports' => 'Reports & Analytics',
        'menu_gis' => 'Performance Report',
        'menu_docs' => 'Document Management',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'menu_hierarchy' => 'Location Hierarchy',
        'menu_audit' => 'Audit Logs',
        'menu_settings' => 'Settings',
        'menu_logout' => 'Logout',
        'btn_ask_ai' => 'Ask Amravati AI',
        'page_title' => 'Executive Dashboard',
        'page_subtitle' => 'Real-time overview of Amravati District operations and task hierarchy.',
        'badge_level' => 'Level',
        'btn_export' => 'Export Report',
        'btn_allocate' => 'Allocate Task',
        
        'heading_district' => 'District Level Dashboard',
        'desc_district' => 'District-wide summary & taluka performance',
        'heading_taluka' => 'Taluka Level Dashboard',
        'desc_taluka' => 'Sub-divisional summary & village breakdown',
        'heading_village' => 'Village Level Dashboard',
        'desc_village' => 'Field officer task assignments — Talathi & Gramsevak',
        
        'kpi_active' => 'Total Active Tasks',
        'kpi_pending' => 'Pending Approvals',
        'kpi_completed' => 'Tasks Completed',
        'kpi_overdue' => 'Escalated / Overdue',
        
        'chart_trend' => 'Task Completion Trend (Date Wise)',
        'chart_taluka' => 'Taluka Performance',
        'chart_village' => 'Village Performance',
        'chart_distribution' => 'Task Status Distribution',
        
        'table_title' => 'Hierarchical Task Allocation Pipeline',
        'table_details' => 'Task Details',
        'table_assigned' => 'Assigned To',
        'table_priority' => 'Priority',
        'table_due' => 'Due Date',
        'table_status' => 'Status',
        'table_actions' => 'Actions',
        'all_talukas' => 'All Talukas',
        
        // Additional translation keys
        'role_administrator' => 'System Administrator',
        'role_collector' => 'District Collector',
        'role_additional_collector' => 'Additional Collector',
        'role_deputy_collector' => 'Deputy Collector',
        'role_sdo' => 'Sub-Divisional Officer',
        'role_tehsildar' => 'Tehsildar',
        'role_bdo' => 'Block Development Officer',
        'role_talathi' => 'Talathi',
        'role_gramsevak' => 'Gramsevak',
        
        'search_placeholder' => "Search tasks, officers, or circulars (Press '/')",
        'table_taluka_office' => 'Taluka / Office',
        'table_total' => 'Total',
        'table_completed' => 'Completed',
        'table_pending' => 'Pending',
        'table_overdue' => 'Overdue',
        'table_rate' => 'Rate',
        'table_progress' => 'Progress',
        'table_village' => 'Village',
        'table_village_summary' => 'Village-wise Performance Summary',
        'view_all' => 'View All',
        'showing_results' => 'Showing <span class="font-medium text-slate-900 dark:text-white">1</span> to <span class="font-medium text-slate-900 dark:text-white">%1$d</span> of <span class="font-medium text-slate-900 dark:text-white">%2$s</span> results',
        
        'chart_monthly_trend' => 'Monthly Task Completion Trend',
        'chart_assigned_tasks' => 'Assigned Tasks',
        'chart_completed_tasks' => 'Completed Tasks',
        'chart_pending' => 'Pending',
        'chart_overdue' => 'Overdue',
        'chart_in_progress' => 'In Progress',
        'chart_completion_rate' => 'Completion Rate %',
        
        'filter_all_statuses' => 'All Statuses',
        'status_completed' => 'Completed',
        'status_pending' => 'Pending',
        'status_in_progress' => 'In Progress',
        'status_overdue' => 'Overdue',
        
        'priority_high' => 'High',
        'priority_medium' => 'Medium',
        'priority_low' => 'Low',
        
        'kpi_needs_attention' => 'Needs attention',
        'kpi_urgent' => 'Urgent',
        'kpi_pending_tasks' => 'Pending Tasks',
        'kpi_overdue_tasks' => 'Overdue Tasks'
    ],
    'mr' => [
        'title' => 'डॅशबोर्ड — अमरावती कनेक्ट | शासकीय कार्यप्रवाह प्लॅटफॉर्म',
        'desc' => 'अमरावती जिल्ह्यासाठी भूमिका-आधारित कार्यकारी डॅशबोर्ड. जिल्हा, तालुका आणि गाव पातळीवरील कार्य अंतर्दृष्टी.',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_announcements' => 'घोषणा',
        'menu_announcement_center' => 'घोषणा केंद्र',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_appreciation' => 'कौतुक',
        'menu_analytics' => 'विश्लेषण आणि डेटा',
        'menu_reports' => 'अहवाल आणि विश्लेषण',
        'menu_gis' => 'जीआयएस नकाशा',
        'menu_docs' => 'दस्तऐवज व्यवस्थापन',
        'menu_admin' => 'प्रशासन',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'menu_hierarchy' => 'स्थान उतरंड',
        'menu_audit' => 'ऑडिट लॉग्स',
        'menu_settings' => 'सेटिंग्ज',
        'menu_logout' => 'लॉगआउट',
        'btn_ask_ai' => 'अमरावती एआय विचारा',
        'page_title' => 'जिल्हा कार्यकारी डॅशबोर्ड',
        'page_subtitle' => 'अमरावती जिल्हा ऑपरेशन्स आणि कार्य उतरंडीचे थेट विहंगावलोकन.',
        'badge_level' => 'स्तर',
        'btn_export' => 'अहवाल निर्यात करा',
        'btn_allocate' => 'कार्य वाटप करा',
        
        'heading_district' => 'जिल्हास्तरीय डॅशबोर्ड',
        'desc_district' => 'जिल्हास्तरीय सारांश आणि तालुका कामगिरी',
        'heading_taluka' => 'तालुकास्तरीय डॅशबोर्ड',
        'desc_taluka' => 'उपविभागीय सारांश आणि गावाची विभागणी',
        'heading_village' => 'गावस्तरीय डॅशबोर्ड',
        'desc_village' => 'क्षेत्रीय अधिकार्‍यांसाठी कार्य सूची आणि पडताळणी पाईपलाईन',
        
        'kpi_active' => 'एकूण सक्रिय कार्ये',
        'kpi_pending' => 'प्रलंबित मंजुरी',
        'kpi_completed' => 'पूर्ण झालेली कार्ये',
        'kpi_overdue' => 'गंभीर / थकीत',
        
        'chart_trend' => 'कार्य पूर्णतेचा कल (दिनांकानुसार)',
        'chart_taluka' => 'तालुका कामगिरी',
        'chart_village' => 'गावाची कामगिरी',
        'chart_distribution' => 'कार्य स्थिती वितरण',
        
        'table_title' => 'श्रेणीबद्ध कार्य वाटप पाईपलाईन',
        'table_details' => 'कार्याचा तपशील',
        'table_assigned' => 'नियुक्त अधिकारी',
        'table_priority' => 'प्राधान्यक्रम',
        'table_due' => 'नियत तारीख',
        'table_status' => 'स्थिती',
        'table_actions' => 'कृती',
        'all_talukas' => 'सर्व तालुके',
        
        // Additional translation keys
        'role_administrator' => 'सिस्टम प्रशासक',
        'role_collector' => 'जिल्हाधिकारी',
        'role_additional_collector' => 'अपर जिल्हाधिकारी',
        'role_deputy_collector' => 'उपजिल्हाधिकारी',
        'role_sdo' => 'उपविभागीय अधिकारी (SDO)',
        'role_tehsildar' => 'तहसीलदार',
        'role_bdo' => 'गट विकास अधिकारी (BDO)',
        'role_talathi' => 'तलाठी',
        'role_gramsevak' => 'ग्रामसेवक',
        
        'search_placeholder' => "कार्ये, अधिकारी किंवा परिपत्रके शोधा (दाबा '/')",
        'table_taluka_office' => 'तालुका / कार्यालय',
        'table_total' => 'एकूण',
        'table_completed' => 'पूर्ण',
        'table_pending' => 'प्रलंबित',
        'table_overdue' => 'थकीत',
        'table_rate' => 'दर',
        'table_progress' => 'प्रगती',
        'table_village' => 'गाव',
        'table_village_summary' => 'गावस्तरीय कामगिरीचा सारांश',
        'view_all' => 'सर्व पहा',
        'showing_results' => 'एकूण <span class="font-medium text-slate-900 dark:text-white">%2$s</span> पैकी <span class="font-medium text-slate-900 dark:text-white">१</span> ते <span class="font-medium text-slate-900 dark:text-white">%1$d</span> निकाल दर्शवित आहे',
        
        'chart_monthly_trend' => 'मासिक कार्य पूर्णतेचा कल',
        'chart_assigned_tasks' => 'सोपवलेली कार्ये',
        'chart_completed_tasks' => 'पूर्ण झालेली कार्ये',
        'chart_pending' => 'प्रलंबित',
        'chart_overdue' => 'थकीत',
        'chart_in_progress' => 'प्रगतीपथावर',
        'chart_completion_rate' => 'पूर्णता दर %',
        
        'filter_all_statuses' => 'सर्व स्थिती',
        'status_completed' => 'पूर्ण',
        'status_pending' => 'प्रलंबित',
        'status_in_progress' => 'प्रगतीपथावर',
        'status_overdue' => 'थकीत',
        
        'priority_high' => 'उच्च',
        'priority_medium' => 'मध्यम',
        'priority_low' => 'कमी',
        
        'kpi_needs_attention' => 'लक्ष देणे आवश्यक',
        'kpi_urgent' => 'तातडीचे',
        'kpi_pending_tasks' => 'प्रलंबित कार्ये',
        'kpi_overdue_tasks' => 'थकीत कार्ये'
    ]
];
$t = $translations[$lang];

/* ─── Role → Level map ─────────────────────────────────────── */
const ROLE_LEVEL_MAP = [
    'Administrator'        => 1,
    'System Administrator' => 1,
    'Collector'            => 1,
    'Additional Collector' => 1,
    'Deputy Collector'     => 1,
    'SDO'                  => 2,
    'Tehsildar'            => 2,
    'BDO'                  => 2,
    'Talathi'              => 3,
    'Gramsevak'            => 3,
];

/* ─── Authentication Guard ─────────────────────────────────── */
if (empty($_SESSION['user_id']) && !isset($_GET['role'])) {
    header('Location: login.php');
    exit;
}

/* ─── DEV: ?role=Collector in URL switches the demo role ───── */
if (isset($_GET['role']) && array_key_exists($_GET['role'], ROLE_LEVEL_MAP)) {
    $_SESSION['user_role']       = $_GET['role'];
    $_SESSION['user_name']       = 'Demo – ' . $_GET['role'];
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

/* ─── Map login session keys to dashboard variables ────────────────── */
if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'];
    $_SESSION['user_village_id'] = $_SESSION['village_id'];
}

/* ─── Session defaults (only used for ?role= dev override) ─── */
if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role']       = 'Collector';
    $_SESSION['user_name']       = 'Hon. Collector';
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

$sRole      = $_SESSION['user_role'];
$sName      = $_SESSION['user_name'];
$sTalukaId  = (int) ($_SESSION['user_taluka_id']  ?? 1);
$sVillageId = (int) ($_SESSION['user_village_id'] ?? 1);

/* ============================================================
   HELPER FUNCTIONS
   ============================================================ */

/**
 * getDashboardLevel()  –  Returns 1 | 2 | 3 for the given role.
 */
function getDashboardLevel(string $role, mysqli $conn): int {
    try {
        $stmt = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return (int)$row['role_level'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log('getDashboardLevel DB error: ' . $e->getMessage());
    }
    return ROLE_LEVEL_MAP[$role] ?? 3;
}

/**
 * getMonthlyTrend()  –  Fetches dynamic 6-month assigned vs completed counts.
 */
function getMonthlyTrend(mysqli $conn, string $scopeType, int $scopeId, string $lang = 'en'): array {
    $categories = [];
    $assigned = [];
    $completed = [];
    
    $monthsMap = [
        'en' => [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Aug', 9=>'Sep', 10=>'Oct', 11=>'Nov', 12=>'Dec'],
        'mr' => [1=>'जाने', 2=>'फेब्रु', 3=>'मार्च', 4=>'एप्रिल', 5=>'मे', 6=>'जून', 7=>'जुलै', 8=>'ऑगस्ट', 9=>'सप्टें', 10=>'ऑक्टो', 11=>'नोव्हें', 12=>'डिसें']
    ];
    
    $data = [];
    $start = new DateTime();
    $start->modify('-5 months');
    
    $sinceDate = $start->format('Y-m-01 00:00:00');
    
    for ($i = 0; $i < 6; $i++) {
        $key = $start->format('Y-n');
        $mNum = (int)$start->format('n');
        $label = ($monthsMap[$lang][$mNum] ?? $start->format('M')) . ' ' . $start->format('y');
        $data[$key] = [
            'label' => $label,
            'assigned' => 0,
            'completed' => 0
        ];
        $start->modify('+1 month');
    }
    
    $query = "SELECT YEAR(t.created_at) as y, MONTH(t.created_at) as m, 
                     COUNT(DISTINCT t.task_id) as assigned_count,
                     COUNT(DISTINCT CASE WHEN t.status = 'Completed' THEN t.task_id END) as completed_count
              FROM tasks t
              LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
              LEFT JOIN users u ON ta.assigned_to_user = u.user_id
              WHERE t.created_at >= ?";
              
    $params = [$sinceDate];
    $types = "s";
    
    if ($scopeType === 'taluka') {
        $query .= " AND (t.taluka_id = ? OR u.taluka_id = ?)";
        $params[] = $scopeId;
        $params[] = $scopeId;
        $types .= "ii";
    } elseif ($scopeType === 'village') {
        $query .= " AND (t.village_id = ? OR u.village_id = ?)";
        $params[] = $scopeId;
        $params[] = $scopeId;
        $types .= "ii";
    }
    
    $query .= " GROUP BY YEAR(t.created_at), MONTH(t.created_at)";
    
    try {
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $key = $row['y'] . '-' . $row['m'];
                if (isset($data[$key])) {
                    $data[$key]['assigned'] = (int)$row['assigned_count'];
                    $data[$key]['completed'] = (int)$row['completed_count'];
                }
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("getMonthlyTrend error: " . $e->getMessage());
    }
    
    foreach ($data as $d) {
        $categories[] = $d['label'];
        $assigned[] = $d['assigned'];
        $completed[] = $d['completed'];
    }
    
    return [
        'categories' => $categories,
        'assigned' => $assigned,
        'completed' => $completed
    ];
}

/**
 * getDistrictStats()  –  District-wide KPIs + taluka breakdown.
 */
function getDistrictStats(mysqli $conn): array {
    $out = ['total'=>0,'active'=>0,'pending'=>0,'completed'=>0,'overdue'=>0,'talukas'=>[]];
    try {
        /* ── District KPIs (single-pass conditional aggregation) ── */
        $r = $conn->query("
            SELECT
              COUNT(*)                                                     AS total,
              COUNT(CASE WHEN status IN ('Pending','Assigned','In Progress','Active') THEN 1 END) AS active,
              COUNT(CASE WHEN status  = 'Pending'   THEN 1 END)           AS pending,
              COUNT(CASE WHEN status  = 'Completed' THEN 1 END)           AS completed,
              COUNT(CASE WHEN (status <> 'Completed' AND due_date < CURDATE()) OR status = 'Escalated' THEN 1 END) AS overdue
            FROM tasks
        ")->fetch_assoc();
        if ($r) {
            $out['total']     = (int)$r['total'];
            $out['active']    = (int)$r['active'];
            $out['pending']   = (int)$r['pending'];
            $out['completed'] = (int)$r['completed'];
            $out['overdue']   = (int)$r['overdue'];
        }

        /* ── Taluka-wise breakdown ─────────────────────────────── */
        $res = $conn->query("
            SELECT
              COALESCE(tk.taluka_name, 'Unknown')                          AS taluka,
              COUNT(DISTINCT t.task_id)                                    AS total,
              COUNT(DISTINCT CASE WHEN t.status = 'Completed' THEN t.task_id END) AS completed,
              COUNT(DISTINCT CASE WHEN t.status = 'Pending'   THEN t.task_id END) AS pending,
              COUNT(DISTINCT CASE WHEN t.due_date < CURDATE()
                         AND t.status != 'Completed' THEN t.task_id END)   AS overdue,
              ROUND(COUNT(DISTINCT CASE WHEN t.status='Completed' THEN t.task_id END)
                    / NULLIF(COUNT(DISTINCT t.task_id),0)*100, 1)          AS rate
            FROM tasks t
            LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
            LEFT JOIN users u ON ta.assigned_to_user = u.user_id
            LEFT JOIN talukas tk ON COALESCE(t.taluka_id, u.taluka_id) = tk.taluka_id
            GROUP BY tk.taluka_name, COALESCE(t.taluka_id, u.taluka_id)
            ORDER BY rate DESC
            LIMIT 10
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) $out['talukas'][] = $row;
        }

     } catch (mysqli_sql_exception $e) {
        error_log('getDistrictStats: ' . $e->getMessage());
        $out = _mockDistrict();
    }
    return $out;
}

/**
 * getTalukaStats()  –  KPIs + village rows scoped to one taluka.
 */
function getTalukaStats(mysqli $conn, int $talukaId): array {
    $out = ['total'=>0,'active'=>0,'pending'=>0,'completed'=>0,'overdue'=>0,'villages'=>[]];
    try {
        /* ── Taluka KPIs ──────────────────────────────────────── */
        $st = $conn->prepare("
            SELECT
              COUNT(DISTINCT t.task_id)                                    AS total,
              COUNT(DISTINCT CASE WHEN t.status != 'Completed' THEN t.task_id END) AS active,
              COUNT(DISTINCT CASE WHEN t.status  = 'Pending'   THEN t.task_id END) AS pending,
              COUNT(DISTINCT CASE WHEN t.status  = 'Completed' THEN t.task_id END) AS completed,
              COUNT(DISTINCT CASE WHEN t.due_date < CURDATE()
                         AND t.status  != 'Completed' THEN t.task_id END)  AS overdue
            FROM tasks t
            LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
            LEFT JOIN users u ON ta.assigned_to_user = u.user_id
            WHERE (t.taluka_id = ? OR u.taluka_id = ?)
        ");
        $st->bind_param('ii', $talukaId, $talukaId);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            foreach (['total','active','pending','completed','overdue'] as $k)
                $out[$k] = (int)$r[$k];
        }
        $st->close();

        /* ── Village breakdown ─────────────────────────────────── */
        $st = $conn->prepare("
            SELECT
              COALESCE(v.village_name, 'Unknown')                          AS village,
              COUNT(DISTINCT t.task_id)                                    AS total,
              COUNT(DISTINCT CASE WHEN t.status='Completed' THEN t.task_id END) AS completed,
              COUNT(DISTINCT CASE WHEN t.status='Pending'   THEN t.task_id END) AS pending,
              COUNT(DISTINCT CASE WHEN t.due_date<CURDATE()
                         AND t.status!='Completed' THEN t.task_id END)     AS overdue
            FROM tasks t
            LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
            LEFT JOIN users u ON ta.assigned_to_user = u.user_id
            LEFT JOIN villages v ON COALESCE(t.village_id, u.village_id) = v.village_id
            WHERE (t.taluka_id = ? OR u.taluka_id = ?)
            GROUP BY v.village_name, COALESCE(t.village_id, u.village_id)
            ORDER BY total DESC LIMIT 10
        ");
        $st->bind_param('ii', $talukaId, $talukaId);
        $st->execute();
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) $out['villages'][] = $row;
        $st->close();

    } catch (mysqli_sql_exception $e) {
        error_log('getTalukaStats: ' . $e->getMessage());
        $out = _mockTaluka();
    }
    return $out;
}

/**
 * getVillageStats()  –  KPIs + task list for a field officer.
 */
function getVillageStats(mysqli $conn, int $villageId): array {
    $out = ['total'=>0,'active'=>0,'pending'=>0,'completed'=>0,'overdue'=>0,'tasks'=>[]];
    try {
        /* ── Village KPIs ─────────────────────────────────────── */
        $st = $conn->prepare("
            SELECT
              COUNT(DISTINCT t.task_id)                                    AS total,
              COUNT(DISTINCT CASE WHEN t.status != 'Completed' THEN t.task_id END) AS active,
              COUNT(DISTINCT CASE WHEN t.status  = 'Pending'   THEN t.task_id END) AS pending,
              COUNT(DISTINCT CASE WHEN t.status  = 'Completed' THEN t.task_id END) AS completed,
              COUNT(DISTINCT CASE WHEN t.due_date < CURDATE()
                         AND t.status  != 'Completed' THEN t.task_id END)  AS overdue
            FROM tasks t
            LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
            LEFT JOIN users u ON ta.assigned_to_user = u.user_id
            WHERE (t.village_id = ? OR u.village_id = ?)
        ");
        $st->bind_param('ii', $villageId, $villageId);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            foreach (['total','active','pending','completed','overdue'] as $k)
                $out[$k] = (int)$r[$k];
        }
        $st->close();

        /* ── Task list ──────────────────────────────────────────── */
        $st = $conn->prepare("
            SELECT DISTINCT t.task_id, t.task_title AS title, t.status, t.due_date, t.priority, u.full_name AS assigned_to_name
            FROM tasks t
            LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
            LEFT JOIN users u ON ta.assigned_to_user = u.user_id
            WHERE (t.village_id = ? OR u.village_id = ?)
            ORDER BY FIELD(t.status,'Overdue','Pending','In Progress','Completed'),
                     t.due_date ASC LIMIT 20
        ");
        $st->bind_param('ii', $villageId, $villageId);
        $st->execute();
        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) $out['tasks'][] = $row;
        $st->close();

    } catch (mysqli_sql_exception $e) {
        error_log('getVillageStats: ' . $e->getMessage());
        $out = _mockVillage();
    }
    return $out;
}

function getPriorityDistribution(mysqli $conn, string $scopeType, int $scopeId): array {
    $out = ['Critical' => 0, 'High' => 0, 'Medium' => 0, 'Low' => 0];
    $query = "SELECT t.priority, COUNT(DISTINCT t.task_id) as count 
              FROM tasks t
              LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
              LEFT JOIN users u ON ta.assigned_to_user = u.user_id";
    $params = [];
    $types = "";
    if ($scopeType === 'taluka') {
        $query .= " WHERE (t.taluka_id = ? OR u.taluka_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    } elseif ($scopeType === 'village') {
        $query .= " WHERE (t.village_id = ? OR u.village_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    }
    $query .= " GROUP BY t.priority";
    try {
        if ($types) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query($query);
        }
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $p = $row['priority'];
                if (isset($out[$p])) {
                    $out[$p] = (int)$row['count'];
                }
            }
        }
        if (isset($stmt)) $stmt->close();
    } catch (Exception $e) {
        error_log('getPriorityDistribution error: ' . $e->getMessage());
    }
    return $out;
}

function getTaskAgeing(mysqli $conn, string $scopeType, int $scopeId): array {
    $out = ['< 5 Days' => 0, '5-10 Days' => 0, '11-30 Days' => 0, '> 30 Days' => 0];
    $query = "SELECT DATEDIFF(CURDATE(), t.created_at) as age_days
              FROM tasks t
              LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
              LEFT JOIN users u ON ta.assigned_to_user = u.user_id
              WHERE t.status != 'Completed'";
    $params = [];
    $types = "";
    if ($scopeType === 'taluka') {
        $query .= " AND (t.taluka_id = ? OR u.taluka_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    } elseif ($scopeType === 'village') {
        $query .= " AND (t.village_id = ? OR u.village_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    }
    try {
        if ($types) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query($query);
        }
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $days = (int)$row['age_days'];
                if ($days < 5) $out['< 5 Days']++;
                elseif ($days <= 10) $out['5-10 Days']++;
                elseif ($days <= 30) $out['11-30 Days']++;
                else $out['> 30 Days']++;
            }
        }
        if (isset($stmt)) $stmt->close();
    } catch (Exception $e) {
        error_log('getTaskAgeing error: ' . $e->getMessage());
    }
    return $out;
}

function getRejectionAnalysis(mysqli $conn, string $scopeType, int $scopeId): array {
    $out = [];
    $query = "SELECT rp.rejection_reason, COUNT(*) as count 
              FROM task_rejection_proofs rp
              JOIN tasks t ON rp.task_id = t.task_id
              LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
              LEFT JOIN users u ON ta.assigned_to_user = u.user_id";
    $params = [];
    $types = "";
    if ($scopeType === 'taluka') {
        $query .= " WHERE (t.taluka_id = ? OR u.taluka_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    } elseif ($scopeType === 'village') {
        $query .= " WHERE (t.village_id = ? OR u.village_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    }
    $query .= " GROUP BY rp.rejection_reason ORDER BY count DESC LIMIT 5";
    try {
        if ($types) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query($query);
        }
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $out[$row['rejection_reason']] = (int)$row['count'];
            }
        }
        if (isset($stmt)) $stmt->close();
    } catch (Exception $e) {
        error_log('getRejectionAnalysis error: ' . $e->getMessage());
    }
    if (empty($out)) {
        $out = [
            'Overlapping Priorities' => 0,
            'Resource Unavailability' => 0,
            'Outside Area of Responsibility' => 0,
            'Technical Insufficiency' => 0
        ];
    }
    return $out;
}

function getUserPerformance(mysqli $conn, string $scopeType, int $scopeId): array {
    $out = [];
    $query = "SELECT u.full_name, u.employee_code,
                     COUNT(DISTINCT t.task_id) as total_tasks,
                     COUNT(DISTINCT CASE WHEN t.status = 'Completed' THEN t.task_id END) as completed_tasks
              FROM users u
              JOIN task_assignments ta ON u.user_id = ta.assigned_to_user
              JOIN tasks t ON ta.task_id = t.task_id";
    $params = [];
    $types = "";
    if ($scopeType === 'taluka') {
        $query .= " WHERE (t.taluka_id = ? OR u.taluka_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    } elseif ($scopeType === 'village') {
        $query .= " WHERE (t.village_id = ? OR u.village_id = ?)";
        $params = [$scopeId, $scopeId];
        $types = "ii";
    }
    $query .= " GROUP BY u.user_id ORDER BY total_tasks DESC LIMIT 5";
    try {
        if ($types) {
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
        } else {
            $res = $conn->query($query);
        }
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $total = (int)$row['total_tasks'];
                $completed = (int)$row['completed_tasks'];
                $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                $out[] = [
                    'name' => $row['full_name'] ?: $row['employee_code'],
                    'total' => $total,
                    'completed' => $completed,
                    'rate' => $rate
                ];
            }
        }
        if (isset($stmt)) $stmt->close();
    } catch (Exception $e) {
        error_log('getUserPerformance error: ' . $e->getMessage());
    }
    return $out;
}

/* ============================================================
   MOCK DATA  –  fallback when DB is unreachable (dev preview)
   ============================================================ */

function _mockDistrict(): array {
    return [
        'total'=>1240,'active'=>847,'pending'=>312,'completed'=>393,'overdue'=>89,
        'talukas'=> [
            ['taluka'=>'Amravati',        'total'=>310,'completed'=>285,'pending'=>18,'overdue'=>7, 'rate'=>91.9],
            ['taluka'=>'Achalpur',        'total'=>225,'completed'=>191,'pending'=>24,'overdue'=>10,'rate'=>84.9],
            ['taluka'=>'Chandur Railway', 'total'=>178,'completed'=>139,'pending'=>29,'overdue'=>10,'rate'=>78.1],
            ['taluka'=>'Daryapur',        'total'=>196,'completed'=>172,'pending'=>15,'overdue'=>9, 'rate'=>87.8],
            ['taluka'=>'Nandgaon Kh.',    'total'=>142,'completed'=>101,'pending'=>25,'overdue'=>16,'rate'=>71.1],
            ['taluka'=>'Warud',           'total'=>189,'completed'=>156,'pending'=>21,'overdue'=>12,'rate'=>82.5],
        ],
    ];
}

function _mockTaluka(): array {
    return [
        'total'=>310,'active'=>215,'pending'=>78,'completed'=>95,'overdue'=>22,
        'villages'=> [
            ['village'=>'Paratwada',   'total'=>45,'completed'=>40,'pending'=>3,'overdue'=>2],
            ['village'=>'Morshi',      'total'=>38,'completed'=>31,'pending'=>5,'overdue'=>2],
            ['village'=>'Nandapur',    'total'=>29,'completed'=>22,'pending'=>5,'overdue'=>2],
            ['village'=>'Chandurbazar','total'=>33,'completed'=>27,'pending'=>4,'overdue'=>2],
            ['village'=>'Wagholi',     'total'=>24,'completed'=>18,'pending'=>4,'overdue'=>2],
            ['village'=>'Dhamangaon',  'total'=>41,'completed'=>35,'pending'=>4,'overdue'=>2],
        ],
    ];
}

function _mockVillage(): array {
    return [
        'total'=>45,'active'=>28,'pending'=>12,'completed'=>17,'overdue'=>6,
        'tasks'=> [
            ['task_id'=>'TSK-8941','title'=>'Crop Damage Assessment',      'status'=>'In Progress','due_date'=>'2026-06-20','priority'=>'High',  'assigned_to_name'=>'Anil Patil'],
            ['task_id'=>'TSK-8902','title'=>'E-KYC Verification Camp',     'status'=>'Pending',    'due_date'=>'2026-06-24','priority'=>'Medium','assigned_to_name'=>'Sunita More'],
            ['task_id'=>'TSK-8850','title'=>'7/12 Record Update',          'status'=>'Completed',  'due_date'=>'2026-06-15','priority'=>'Low',   'assigned_to_name'=>'Rajesh Kolhe'],
            ['task_id'=>'TSK-8831','title'=>'Village Pond Water Survey',   'status'=>'Overdue',    'due_date'=>'2026-06-10','priority'=>'High',  'assigned_to_name'=>'Meena Shinde'],
            ['task_id'=>'TSK-8820','title'=>'PM Awas Beneficiary Listing', 'status'=>'Pending',    'due_date'=>'2026-06-28','priority'=>'High',  'assigned_to_name'=>'Anil Patil'],
            ['task_id'=>'TSK-8800','title'=>'Street Light Repair Report',  'status'=>'Completed',  'due_date'=>'2026-06-12','priority'=>'Low',   'assigned_to_name'=>'Sunita More'],
        ],
    ];
}

/* ============================================================
   RESOLVE CURRENT USER
   ============================================================ */

$dbAvailable = ($conn instanceof mysqli && !$conn->connect_error);

$level   = $dbAvailable ? getDashboardLevel($sRole, $conn) : (ROLE_LEVEL_MAP[$sRole] ?? 3);
$showL1  = ($level === 1);
$showL2  = ($level <= 2);
$showL3  = true;

$distData = ($showL1 && $dbAvailable) ? getDistrictStats($conn)           : _mockDistrict();
$talData  = ($showL2 && $dbAvailable) ? getTalukaStats($conn, $sTalukaId) : _mockTaluka();
$vilData  = $dbAvailable              ? getVillageStats($conn, $sVillageId) : _mockVillage();

// Replace hardcoded / mock counts with the real-time values from database
$distData['active']    = $totalActiveTasks;
$distData['pending']   = $pendingTasks;
$distData['completed'] = $completedTasks;
$distData['overdue']   = $overdueTasks;

$talData['active']     = $totalActiveTasks;
$talData['pending']    = $pendingTasks;
$talData['completed']  = $completedTasks;
$talData['overdue']    = $overdueTasks;

$vilData['active']     = $totalActiveTasks;
$vilData['pending']    = $pendingTasks;
$vilData['completed']  = $completedTasks;
$vilData['overdue']    = $overdueTasks;

$distTrend  = ($showL1 && $dbAvailable) ? getMonthlyTrend($conn, 'district', 0, $lang)          : ['categories'=>[],'assigned'=>[],'completed'=>[]];
$talTrend   = ($showL2 && $dbAvailable) ? getMonthlyTrend($conn, 'taluka', $sTalukaId, $lang)    : ['categories'=>[],'assigned'=>[],'completed'=>[]];
$vilTrend   = $dbAvailable              ? getMonthlyTrend($conn, 'village', $sVillageId, $lang)   : ['categories'=>[],'assigned'=>[],'completed'=>[]];

$distPriority   = ($showL1 && $dbAvailable) ? getPriorityDistribution($conn, 'district', 0)          : ['Critical'=>1,'High'=>3,'Medium'=>5,'Low'=>4];
$distAgeing     = ($showL1 && $dbAvailable) ? getTaskAgeing($conn, 'district', 0)                    : ['< 5 Days'=>2,'5-10 Days'=>4,'11-30 Days'=>3,'> 30 Days'=>1];
$distRejections = ($showL1 && $dbAvailable) ? getRejectionAnalysis($conn, 'district', 0)             : ['Overlapping Priorities'=>2,'Resource Unavailability'=>1];
$distPerform    = ($showL1 && $dbAvailable) ? getUserPerformance($conn, 'district', 0)               : [];

$talPriority   = ($showL2 && $dbAvailable) ? getPriorityDistribution($conn, 'taluka', $sTalukaId)   : ['Critical'=>1,'High'=>2,'Medium'=>3,'Low'=>2];
$talAgeing     = ($showL2 && $dbAvailable) ? getTaskAgeing($conn, 'taluka', $sTalukaId)              : ['< 5 Days'=>1,'5-10 Days'=>2,'11-30 Days'=>1,'> 30 Days'=>0];
$talRejections = ($showL2 && $dbAvailable) ? getRejectionAnalysis($conn, 'taluka', $sTalukaId)       : ['Overlapping Priorities'=>1];
$talPerform    = ($showL2 && $dbAvailable) ? getUserPerformance($conn, 'taluka', $sTalukaId)         : [];

$vilPriority   = $dbAvailable ? getPriorityDistribution($conn, 'village', $sVillageId) : ['Critical'=>0,'High'=>1,'Medium'=>2,'Low'=>1];
$vilAgeing     = $dbAvailable ? getTaskAgeing($conn, 'village', $sVillageId)             : ['< 5 Days'=>1,'5-10 Days'=>1,'11-30 Days'=>0,'> 30 Days'=>0];

/* Friendly role label */
$roleKey = match($sRole) {
    'Administrator', 'System Administrator' => 'role_administrator',
    'Collector' => 'role_collector',
    'Additional Collector' => 'role_additional_collector',
    'Deputy Collector' => 'role_deputy_collector',
    'SDO' => 'role_sdo',
    'Tehsildar' => 'role_tehsildar',
    'BDO' => 'role_bdo',
    'Talathi' => 'role_talathi',
    'Gramsevak' => 'role_gramsevak',
    default => '',
};
$roleLabel = $roleKey ? $t[$roleKey] : $sRole;

/* Avatar initials */
$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

/* Safe positive value for "In Progress" in charts */
function inProgress(int $active, int $pending): int {
    return max(0, $active - $pending);
}

/* Status CSS classes */
function statusCss(string $s): string {
    return match($s) {
        'Completed'   => 'bg-green-100  text-green-800  border-green-200  dark:bg-green-900/30  dark:text-green-400  dark:border-green-800',
        'Pending'     => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800',
        'In Progress' => 'bg-blue-100   text-blue-800   border-blue-200   dark:bg-blue-900/30   dark:text-blue-400   dark:border-blue-800',
        'Overdue'     => 'bg-red-100    text-red-700    border-red-200    dark:bg-red-900/30    dark:text-red-400    dark:border-red-800',
        default       => 'bg-slate-100  text-slate-700  border-slate-200  dark:bg-slate-700     dark:text-slate-300  dark:border-slate-600',
    };
}
function dotCss(string $s): string {
    return match($s) {
        'Completed'   => 'bg-green-500',
        'Pending'     => 'bg-yellow-400',
        'In Progress' => 'bg-blue-500',
        'Overdue'     => 'bg-red-500',
        default       => 'bg-slate-400',
    };
}
function priorityCss(string $p): string {
    return match($p) {
        'High'   => 'text-red-600    dark:text-red-400',
        'Medium' => 'text-orange-500 dark:text-orange-400',
        default  => 'text-slate-400  dark:text-slate-500',
    };
}

close_db_connection();
?>
<?php
$pageTitle = $t['title'] ?? 'Amravati Connect Dashboard';
$pageDesc = $t['desc'] ?? 'Dashboard';
$extraHead = '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>';
include 'include/header.php';
$activePage = 'dashboard';
include 'include/sidebar.php';
?>

<!-- ════════════════════════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- ── HEADER ─────────────────────────────────────────── -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800
                   flex items-center justify-between px-6 z-10 sticky top-0">

        <div class="flex items-center flex-1">
            <button id="sidebarToggle"
                    class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400
                           dark:hover:text-slate-200 focus:outline-none block lg:hidden">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <!-- Search -->
            <div class="w-32 sm:max-w-md sm:w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                </div>
                <input id="globalSearch" type="text"
                       placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>"
                       class="block w-full pl-8 sm:pl-10 pr-3 py-2 border border-slate-300
                              dark:border-slate-700 rounded-md leading-5
                              bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100
                              placeholder-slate-500 focus:outline-none
                              focus:ring-1 focus:ring-navy-500 focus:border-navy-500
                              text-xs sm:text-sm transition-colors">
                <div class="hidden sm:flex absolute inset-y-0 right-0 pr-3 items-center pointer-events-none">
                    <span class="text-slate-400 text-xs border border-slate-300
                                 dark:border-slate-700 rounded px-1.5 py-0.5">⌘K</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Language -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'dashboard.php?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                      hover:bg-slate-100 dark:hover:bg-slate-800 px-2 sm:px-3 py-1.5 rounded-md
                      transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 sm:mr-2 text-slate-500"></i>
                <span class="hidden sm:inline"><?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?></span>
            </a>
            <!-- Theme -->
            <button id="themeToggle"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400
                           dark:hover:text-slate-200 rounded-full
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun"  class="w-5 h-5 hidden dark:block"></i>
            </button>
            <!-- Notifications -->
            <div class="relative">
                <button id="notificationBtn" class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="unreadCountBadge" style="display:none;" class="absolute top-0 right-0 flex items-center justify-center h-4 w-4 text-[10px] font-bold text-white rounded-full bg-saffron-500 ring-2 ring-white dark:ring-slate-900">0</span>
                </button>
                <!-- Dropdown -->
                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 z-50">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-t-lg">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($t['menu_notifications'] ?? 'Notifications') ?></h3>
                        <button onclick="markAllAsRead()" class="text-xs text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 font-medium">
                            <?= $lang === 'en' ? 'Mark all as read' : 'सर्व वाचलेले म्हणून चिन्हांकित करा' ?>
                        </button>
                    </div>
                    <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                        <!-- Populated via AJAX -->
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-lg">
                        <a href="notifications.php?lang=<?= $lang ?>" class="block w-full text-center px-4 py-3 text-xs font-medium text-slate-500 hover:text-navy-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                            <?= $lang === 'en' ? 'View All Notifications' : 'सर्व सूचना पहा' ?>
                        </a>
                    </div>
                </div>
            </div>
                        <!-- Profile dropdown container -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName ?? 'User') ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($sRole ?? $roleLabel ?? 'Officer') ?>
                            <?= ' (' . htmlspecialchars($headerLocationDisplay) . ')' ?>
                        </span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                        <?= htmlspecialchars($initials ?? 'U') ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordChange.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i><?= ($lang ?? 'en') === 'en' ? 'Logout' : 'लॉगआउट' ?>
                        </a>
                    </div>
                </div>
            </div></div>
    </header>

    <!-- ── MAIN SCROLL AREA ───────────────────────────────── -->
    <main id="dashboard-report-container" class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- Page Header (same layout as blank_wrushabh.php) -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                    <?= htmlspecialchars($t['page_title']) ?>
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    <?= htmlspecialchars($t['page_subtitle']) ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3 flex-wrap gap-y-2 no-print">
                <!-- Access level badge -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                             <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                </span>
                <button onclick="exportDashboardPDF()" class="btn-modern bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_export']) ?>
                </button>
                <button onclick="window.location.href='create_task.php?lang=<?= $lang ?>'" class="btn-modern btn-primary shadow-official">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_allocate']) ?>
                </button>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════
             SECTION A — DISTRICT  (Level 1 only)
        ══════════════════════════════════════════════════ -->
        <?php if ($showL1): ?>
        <div class="mb-10">
            <!-- Section toggle header -->
            <button onclick="toggleSec('dist')"
                    class="group w-full flex items-center justify-between mb-6 text-left">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg
                                 bg-navy-600 shadow-lg shadow-navy-600/30">
                        <i data-lucide="building-2" class="w-4 h-4 text-white"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['heading_district']) ?>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($t['desc_district']) ?>
                        </p>
                    </div>
                    <span class="badge-l1 text-xs font-semibold px-2.5 py-0.5 rounded-full">Level 1</span>
                </div>
                <i data-lucide="chevron-down"
                   id="chev-dist"
                   class="chevron open w-5 h-5 text-slate-400 group-hover:text-slate-600
                          dark:group-hover:text-slate-300"></i>
            </button>

            <div id="sec-dist" class="sec-body">

                <!-- KPI Cards — 4 per row matching blank_wrushabh.php -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <?php
                    // 1. Total Tasks KPI
                    $dkpi = [
                        ['Total Tasks', $dynamicTotalTasks, 'hash', 'blue', '', '', false, 'all']
                    ];

                    // 2. Status-wise KPIs
                    $statusStyles = [
                        'Completed'   => ['icon' => 'check-circle', 'color' => 'green'],
                        'Pending'     => ['icon' => 'clock', 'color' => 'orange'],
                        'In Progress' => ['icon' => 'activity', 'color' => 'blue'],
                        'Overdue'     => ['icon' => 'alert-octagon', 'color' => 'red'],
                        'Escalated'   => ['icon' => 'alert-triangle', 'color' => 'red'],
                        'Assigned'    => ['icon' => 'user-check', 'color' => 'indigo'],
                        'Rejected'    => ['icon' => 'x-circle', 'color' => 'red'],
                    ];

                    // Explicitly add Overdue Tasks (uses $overdueTasks calculated earlier)
                    $dkpi[] = [
                        'Total Overdue Tasks',
                        $overdueTasks ?? 0,
                        'alert-octagon',
                        'red',
                        '', '', false,
                        'Overdue'
                    ];

                    foreach ($dynamicStatusCounts as $row) {
                        $st = $row['status'];
                        if ($st === 'Overdue' || $st === 'Escalated') continue; // Handled by consolidated Overdue card

                        $val = (int)$row['total'];
                        $style = $statusStyles[$st] ?? ['icon' => 'layers', 'color' => 'slate'];
                        
                        $dkpi[] = [
                            'Total ' . $st . ' Tasks',
                            $val,
                            $style['icon'],
                            $style['color'],
                            '', '', false,
                            $st
                        ];
                    }

                    foreach ($dkpi as [$label,$val,$icon,$clr,$trendIcon,$trendTxt,$trendUp,$filterStatus]):
                        $linkUrl = "task_tracking.php?lang={$lang}&filter_status=" . urlencode($filterStatus);
                    ?>
                    <a href="<?= htmlspecialchars($linkUrl) ?>" class="block transition-transform hover:scale-105 cursor-pointer" style="text-decoration: none;">
                        <div class="kpi-card bg-gradient-to-br from-<?= $clr ?>-50 to-white dark:from-<?= $clr ?>-900/40 dark:to-slate-800 overflow-hidden shadow-sm
                                    rounded-xl border-l-4 border-l-<?= $clr ?>-500 border-t border-r border-b border-slate-200 dark:border-slate-700 h-full">
                            <div class="p-5 relative">
                                <i data-lucide="<?= $icon ?>" class="absolute right-0 bottom-0 w-24 h-24 text-<?= $clr ?>-500 opacity-5 transform translate-x-4 translate-y-4 pointer-events-none"></i>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">
                                            <?= htmlspecialchars($label) ?>
                                        </p>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-3xl font-bold
                                               <?= $clr==='red' ? 'text-red-600 dark:text-red-400'
                                                               : 'text-slate-900 dark:text-white' ?>">
                                                <?php echo number_format($val); ?>
                                            </p>
                                            <?php if ($trendTxt): ?>
                                            <p class="ml-2 flex items-baseline text-sm font-semibold
                                               <?= $trendUp ? 'text-govgreen-600 dark:text-green-400'
                                                            : 'text-red-600 dark:text-red-400' ?>">
                                                <i data-lucide="<?= $trendIcon ?>" class="w-3 h-3 mr-1"></i>
                                                <?= $trendTxt ?>
                                            </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 bg-<?= $clr ?>-50 dark:bg-<?= $clr ?>-900/30
                                                rounded-full flex items-center justify-center">
                                        <i data-lucide="<?= $icon ?>"
                                           class="w-6 h-6 text-<?= $clr ?>-600 dark:text-<?= $clr ?>-400 <?= $clr==='red'?'pulse':'' ?>"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Charts — matching blank_wrushabh.php (Trend and Taluka Bar in same row) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Line / Area Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_monthly_trend']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-dist-trend" class="h-72 w-full"></div>
                    </div>
                    
                    <!-- Top Performing Offices — bar chart -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_taluka']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-dist-bar" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Secondary Charts Grid (Status, Priority, Ageing) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Donut -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_distribution']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-dist-donut" class="h-72 w-full"></div>
                    </div>

                    <!-- Priority Distribution -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Task Priority-wise Analysis' : 'प्राधान्यक्रमानुसार कार्य विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-dist-priority" class="h-72 w-full"></div>
                    </div>

                    <!-- Task Ageing -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Task Ageing Analysis' : 'कार्य वयोमान विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-dist-ageing" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Third Tier Charts (Rejections, Performance) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Rejection Analysis -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Task Rejection Reasons Analysis' : 'कार्य नाकारण्याच्या कारणांचे विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-dist-rejections" class="h-72 w-full"></div>
                    </div>
                    <!-- User Performance -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Top Officer Performance (Task Completion)' : 'वरिष्ठ अधिकारी कामगिरी (कार्य पूर्णता)' ?>
                            </h2>
                        </div>
                        <div id="chart-dist-performance" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Taluka-wise performance table (matches blank_wrushabh.php table) -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['table_title']) ?>
                        </h2>
                        <div class="flex space-x-2">
                            <select id="talukaTableFilter" onchange="filterTalukaRows()" class="block pl-3 pr-8 py-2 text-sm border-slate-300
                                          dark:border-slate-600 dark:bg-slate-700 dark:text-white
                                          rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option value=""><?= htmlspecialchars($t['all_talukas']) ?></option>
                                <?php foreach ($distData['talukas'] as $tRow): ?>
                                <option value="<?= htmlspecialchars($tRow['taluka']) ?>"><?= htmlspecialchars($tRow['taluka']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="talukaPerformanceTable">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <?php foreach ([
                                        $t['table_taluka_office'],
                                        $t['table_total'],
                                        $t['table_completed'],
                                        $t['table_pending'],
                                        $t['table_overdue'],
                                        $t['table_rate'],
                                        $t['table_progress']
                                    ] as $h): ?>
                                    <th class="px-6 py-3 text-left text-xs font-semibold
                                               text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        <?= $h ?>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <?php foreach ($distData['talukas'] as $idx => $tRow):
                                    $dotColors = ['bg-red-500','bg-saffron-500','bg-blue-500','bg-govgreen-500','bg-purple-500','bg-teal-500'];
                                    $dot = $dotColors[$idx % count($dotColors)];
                                ?>
                                <tr class="table-row-modern border-b border-slate-100 dark:border-slate-700/50 taluka-row" data-taluka="<?= htmlspecialchars($tRow['taluka']) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 rounded-full <?= $dot ?> mr-3"></div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($tRow['taluka']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                        <?= number_format($tRow['total']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-govgreen-600 dark:text-green-400">
                                        <?= number_format($tRow['completed']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-saffron-600 dark:text-yellow-400">
                                        <?= number_format($tRow['pending']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                        <?= number_format($tRow['overdue']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">
                                        <?= number_format($tRow['rate'], 1) ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap w-36">
                                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                            <div class="bg-navy-600 h-1.5 rounded-full transition-all duration-700"
                                                 style="width:<?= min(100,(float)$tRow['rate']) ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination (matches blank_wrushabh.php) -->
                    <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200
                                dark:border-slate-700 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-700 dark:text-slate-400">
                                <?= sprintf($t['showing_results'], count($distData['talukas']), number_format($distData['total'])) ?>
                            </p>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <a href="#" onclick="Swal.fire({icon: 'info', title: 'Pagination', text: 'More data is coming soon.', confirmButtonColor: '#0069cd'}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-l-md
                                    border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                                    text-sm font-medium text-slate-500 dark:text-slate-300
                                    hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                                <a href="#" aria-current="page"
                                   class="z-10 bg-navy-50 dark:bg-navy-900 border-navy-500 dark:border-navy-400
                                          text-navy-600 dark:text-blue-400
                                          relative inline-flex items-center px-4 py-2 border text-sm font-medium">1</a>
                                <a href="#" class="bg-white dark:bg-slate-700 border-slate-300
                                    dark:border-slate-600 text-slate-500 dark:text-slate-300
                                    hover:bg-slate-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">2</a>
                                <a href="#" onclick="Swal.fire({icon: 'info', title: 'Pagination', text: 'More data is coming soon.', confirmButtonColor: '#0069cd'}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-r-md
                                    border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                                    text-sm font-medium text-slate-500 dark:text-slate-300
                                    hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

            </div><!-- /sec-dist -->
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════
             SECTION B — TALUKA  (Level 1 & 2)
        ══════════════════════════════════════════════════ -->
        <?php if ($showL2): ?>
        <div class="mb-10">
            <button onclick="toggleSec('tal')"
                    class="group w-full flex items-center justify-between mb-6 text-left">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg
                                 bg-saffron-500 shadow-lg shadow-orange-400/30">
                        <i data-lucide="map-pin" class="w-4 h-4 text-white"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['heading_taluka']) ?>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($t['desc_taluka']) ?>
                        </p>
                    </div>
                    <span class="badge-l2 text-xs font-semibold px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($t['badge_level']) ?> 2</span>
                </div>
                <i data-lucide="chevron-down" id="chev-tal"
                   class="chevron open w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300"></i>
            </button>

            <div id="sec-tal" class="sec-body">

                <!-- Taluka KPI Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <?php
                    $tkpi = [
                        [$t['kpi_active'],   $talData['active'],    'layers',       'blue',   'trending-up',   '+9%',  true, 'Active'],
                        [$t['kpi_pending'],    $talData['pending'],   'clock',        'orange', 'trending-down', '-2%',  false, 'Pending'],
                        [$t['kpi_completed'],      $talData['completed'], 'check-circle', 'green',  'trending-up',   '+18%', true, 'Completed'],
                        [$t['kpi_overdue'],  $talData['overdue'],   'alert-octagon','red',    'alert-triangle', $lang === 'en' ? '5 Req' : '५ कृती आवश्यक', false, 'Overdue'],
                    ];
                    foreach ($tkpi as [$label,$val,$icon,$clr,$tIcon,$tTxt,$tUp,$filterStatus]):
                        $linkUrl = "task_tracking.php?lang={$lang}&filter_status=" . urlencode($filterStatus);
                    ?>
                    <a href="<?= htmlspecialchars($linkUrl) ?>" class="block transition-transform hover:scale-105 cursor-pointer" style="text-decoration: none;">
                        <div class="kpi-card bg-gradient-to-br from-<?= $clr ?>-50 to-white dark:from-<?= $clr ?>-900/40 dark:to-slate-800 overflow-hidden shadow-sm
                                    rounded-xl border-l-4 border-l-<?= $clr ?>-500 border-t border-r border-b border-slate-200 dark:border-slate-700 h-full">
                            <div class="p-5 relative">
                                <i data-lucide="<?= $icon ?>" class="absolute right-0 bottom-0 w-24 h-24 text-<?= $clr ?>-500 opacity-5 transform translate-x-4 translate-y-4 pointer-events-none"></i>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">
                                            <?= $label ?>
                                        </p>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-3xl font-bold
                                               <?= $clr==='red' ? 'text-red-600 dark:text-red-400'
                                                               : 'text-slate-900 dark:text-white' ?>">
                                                <?php echo number_format($val); ?>
                                            </p>
                                            <p class="ml-2 flex items-baseline text-sm font-semibold
                                               <?= $tUp ? 'text-govgreen-600 dark:text-green-400'
                                                         : 'text-red-600 dark:text-red-400' ?>">
                                                <i data-lucide="<?= $tIcon ?>" class="w-3 h-3 mr-1"></i>
                                                <?= $tTxt ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 bg-<?= $clr ?>-50 dark:bg-<?= $clr ?>-900/30
                                                rounded-full flex items-center justify-center">
                                        <i data-lucide="<?= $icon ?>"
                                            class="w-6 h-6 text-<?= $clr ?>-600 dark:text-<?= $clr ?>-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Taluka Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_monthly_trend']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-tal-trend" class="h-72 w-full"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_distribution']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-tal-donut" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Village Performance Bar -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['chart_village']) ?>
                        </h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div id="chart-tal-bar" class="h-60 w-full"></div>
                </div>

                <!-- New Taluka Graphs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Priority Distribution -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Taluka Priority-wise Analysis' : 'तालुका प्राधान्यक्रमानुसार कार्य विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-tal-priority" class="h-72 w-full"></div>
                    </div>
                    <!-- Task Ageing -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Taluka Task Ageing Analysis' : 'तालुका कार्य प्रलंबित कालावधी विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-tal-ageing" class="h-72 w-full"></div>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Rejection Analysis -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Taluka Task Rejection reasons' : 'तालुका कार्य नाकारण्याची कारणे' ?>
                            </h2>
                        </div>
                        <div id="chart-tal-rejections" class="h-72 w-full"></div>
                    </div>
                    <!-- User Performance -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Taluka Officer Performance' : 'तालुका अधिकारी कामगिरी' ?>
                            </h2>
                        </div>
                        <div id="chart-tal-performance" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Village Summary Table -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['table_village_summary']) ?>
                        </h2>
                        <button class="text-sm text-navy-600 dark:text-blue-400 hover:underline font-medium">
                            <?= htmlspecialchars($t['view_all']) ?>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <?php foreach ([
                                        $t['table_village'],
                                        $t['table_total'],
                                        $t['table_completed'],
                                        $t['table_pending'],
                                        $t['table_overdue'],
                                        $t['table_progress']
                                    ] as $h): ?>
                                    <th class="px-6 py-3 text-left text-xs font-semibold
                                               text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= $h ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <?php foreach ($talData['villages'] as $v):
                                    $vRate = $v['total'] > 0 ? round($v['completed']/$v['total']*100,1) : 0;
                                ?>
                                <tr class="table-row-modern border-b border-slate-100 dark:border-slate-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                               text-slate-900 dark:text-white">
                                        <?= htmlspecialchars($v['village']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                        <?= number_format($v['total']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-govgreen-600 dark:text-green-400">
                                        <?= number_format($v['completed']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-saffron-600 dark:text-yellow-400">
                                        <?= number_format($v['pending']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                        <?= number_format($v['overdue']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap w-40">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                                <div class="bg-saffron-500 h-1.5 rounded-full"
                                                     style="width:<?= min(100,$vRate) ?>%"></div>
                                            </div>
                                            <span class="text-xs text-slate-500 w-9 text-right"><?= $vRate ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- /sec-tal -->
        </div>
        <?php endif; ?>

        <!-- ══════════════════════════════════════════════════
             SECTION C — VILLAGE  (All Levels)
        ══════════════════════════════════════════════════ -->
        <div class="mb-12">
            <button onclick="toggleSec('vil')"
                    class="group w-full flex items-center justify-between mb-6 text-left">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg
                                 bg-govgreen-500 shadow-lg shadow-green-500/30">
                        <i data-lucide="home" class="w-4 h-4 text-white"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['heading_village']) ?>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($t['desc_village']) ?>
                        </p>
                    </div>
                    <span class="badge-l3 text-xs font-semibold px-2.5 py-0.5 rounded-full"><?= htmlspecialchars($t['badge_level']) ?> 3</span>
                </div>
                <i data-lucide="chevron-down" id="chev-vil"
                   class="chevron open w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300"></i>
            </button>

            <div id="sec-vil" class="sec-body">

                <!-- Village KPI Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <?php
                    $vkpi = [
                        [$t['kpi_active'],  $vilData['active'],    'layers',       'blue',   'trending-up',   '+5%',    true, 'Active'],
                        [$t['kpi_pending_tasks'],       $vilData['pending'],   'clock',        'orange', 'alert-triangle', $t['kpi_needs_attention'], false, 'Pending'],
                        [$t['kpi_completed'],     $vilData['completed'], 'check-circle', 'green',  'trending-up',   '+15%',   true, 'Completed'],
                        [$t['kpi_overdue_tasks'],       $vilData['overdue'],   'alert-octagon','red',    'alert-triangle', $t['kpi_urgent'], false, 'Overdue'],
                    ];
                    foreach ($vkpi as [$label,$val,$icon,$clr,$tIcon,$tTxt,$tUp,$filterStatus]):
                        $linkUrl = "task_tracking.php?lang={$lang}&filter_status=" . urlencode($filterStatus);
                    ?>
                    <a href="<?= htmlspecialchars($linkUrl) ?>" class="block transition-transform hover:scale-105 cursor-pointer" style="text-decoration: none;">
                        <div class="kpi-card bg-gradient-to-br from-<?= $clr ?>-50 to-white dark:from-<?= $clr ?>-900/40 dark:to-slate-800 overflow-hidden shadow-sm
                                    rounded-xl border-l-4 border-l-<?= $clr ?>-500 border-t border-r border-b border-slate-200 dark:border-slate-700 h-full">
                            <div class="p-5 relative">
                                <i data-lucide="<?= $icon ?>" class="absolute right-0 bottom-0 w-24 h-24 text-<?= $clr ?>-500 opacity-5 transform translate-x-4 translate-y-4 pointer-events-none"></i>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">
                                            <?= $label ?>
                                        </p>
                                        <div class="mt-1 flex items-baseline">
                                            <p class="text-3xl font-bold
                                               <?= $clr==='red' ? 'text-red-600 dark:text-red-400'
                                                               : 'text-slate-900 dark:text-white' ?>">
                                                <?php echo number_format($val); ?>
                                            </p>
                                            <p class="ml-2 flex items-baseline text-sm font-semibold
                                               <?= $tUp ? 'text-govgreen-600 dark:text-green-400'
                                                         : 'text-red-600 dark:text-red-400' ?>">
                                                <i data-lucide="<?= $tIcon ?>" class="w-3 h-3 mr-1"></i>
                                                <?= $tTxt ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="w-12 h-12 bg-<?= $clr ?>-50 dark:bg-<?= $clr ?>-900/30
                                                rounded-full flex items-center justify-center">
                                        <i data-lucide="<?= $icon ?>"
                                           class="w-6 h-6 text-<?= $clr ?>-600 dark:text-<?= $clr ?>-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Village Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_monthly_trend']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-vil-trend" class="h-72 w-full"></div>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_distribution']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-vil-donut" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- New Village Graphs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <!-- Priority Distribution -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Village Priority-wise Analysis' : 'ग्राम प्राधान्यक्रमानुसार कार्य विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-vil-priority" class="h-72 w-full"></div>
                    </div>
                    <!-- Task Ageing -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= $lang === 'en' ? 'Village Task Ageing Analysis' : 'ग्राम कार्य प्रलंबित कालावधी विश्लेषण' ?>
                            </h2>
                        </div>
                        <div id="chart-vil-ageing" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Task Allocation Pipeline Table (matches blank_wrushabh.php style) -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['table_title']) ?>
                        </h2>
                        <div class="flex space-x-2">
                            <select id="statusFilter" onchange="filterRows()"
                                    class="block pl-3 pr-8 py-2 text-sm border-slate-300
                                           dark:border-slate-600 dark:bg-slate-700 dark:text-white
                                           rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option value=""><?= htmlspecialchars($t['filter_all_statuses']) ?></option>
                                <option value="Completed"><?= htmlspecialchars($t['status_completed']) ?></option>
                                <option value="Pending"><?= htmlspecialchars($t['status_pending']) ?></option>
                                <option value="In Progress"><?= htmlspecialchars($t['status_in_progress']) ?></option>
                                <option value="Overdue"><?= htmlspecialchars($t['status_overdue']) ?></option>
                            </select>
                            <button onclick="Swal.fire({icon: 'info', title: 'Filter', text: 'Advanced filtering is coming soon.', confirmButtonColor: '#0069cd'}); return false;" class="p-2 border border-slate-300 dark:border-slate-600 rounded-md
                                          text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="taskTable">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_details']) ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_assigned']) ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_priority']) ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_due']) ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_status']) ?></th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_actions']) ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <?php foreach ($vilData['tasks'] as $task):
                                    $sc  = statusCss($task['status']);
                                    $dc  = dotCss($task['status']);
                                    
                                    $priorityKey = match($task['priority'] ?? 'Low') {
                                        'High' => 'priority_high',
                                        'Medium' => 'priority_medium',
                                        'Low' => 'priority_low',
                                        default => 'priority_low',
                                    };
                                    $displayPriority = $t[$priorityKey];
                                    
                                    $statusKey = match($task['status']) {
                                        'Completed' => 'status_completed',
                                        'Pending' => 'status_pending',
                                        'In Progress' => 'status_in_progress',
                                        'Overdue' => 'status_overdue',
                                        default => '',
                                    };
                                    $displayStatus = $statusKey ? $t[$statusKey] : $task['status'];
                                    
                                    $pc  = priorityCss($task['priority'] ?? 'Low');
                                    $due = !empty($task['due_date'])
                                         ? date('d M Y', strtotime($task['due_date'])) : '—';
                                    $overdue = $task['status'] === 'Overdue';
                                    $name = $task['assigned_to_name'] ?? '—';
                                    $ini  = strtoupper(substr($name, 0, 1));
                                ?>
                                <tr class="table-row-modern border-b border-slate-100 dark:border-slate-700/50 task-row"
                                    data-status="<?= htmlspecialchars($task['status']) ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 rounded-full mr-3
                                                        <?= $dc ?> <?= $overdue?'pulse':'' ?>"></div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($task['title']) ?>
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    #<?= htmlspecialchars($task['task_id']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600
                                                        flex items-center justify-center text-xs font-bold
                                                        text-slate-600 dark:text-white mr-3">
                                                <?= htmlspecialchars($ini) ?>
                                            </div>
                                            <div>
                                                <div class="text-sm text-slate-900 dark:text-white">
                                                    <?= htmlspecialchars($name) ?>
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    <?= htmlspecialchars($roleLabel) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-semibold <?= $pc ?>">
                                            <?= htmlspecialchars($displayPriority) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm <?= $overdue
                                            ? 'text-red-600 dark:text-red-400 font-medium'
                                            : 'text-slate-900 dark:text-slate-300' ?>">
                                            <?= $due ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5
                                                     font-semibold rounded-full border <?= $sc ?>">
                                            <?= htmlspecialchars($displayStatus) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick="openTrackModal(<?= $task['task_id'] ?>); return false;" class="text-navy-600 dark:text-blue-400 hover:text-navy-900
                                                       dark:hover:text-blue-300 mr-3" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="openTrackModal(<?= $task['task_id'] ?>); return false;" class="text-slate-400 hover:text-slate-600
                                                       dark:hover:text-slate-200" title="More">
                                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200
                                dark:border-slate-700 flex items-center justify-between sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <p class="text-sm text-slate-700 dark:text-slate-400">
                                <?= sprintf($t['showing_results'], count($vilData['tasks']), number_format($vilData['total'])) ?>
                            </p>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <a href="#" onclick="Swal.fire({icon: 'info', title: 'Pagination', text: 'More data is coming soon.', confirmButtonColor: '#0069cd'}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-l-md border
                                    border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm
                                    font-medium text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                                <a href="#" aria-current="page"
                                   class="z-10 bg-navy-50 dark:bg-navy-900 border-navy-500 dark:border-navy-400
                                          text-navy-600 dark:text-blue-400 relative inline-flex items-center
                                          px-4 py-2 border text-sm font-medium">1</a>
                                <a href="#" class="bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600
                                    text-slate-500 dark:text-slate-300 hover:bg-slate-50 relative inline-flex
                                    items-center px-4 py-2 border text-sm font-medium">2</a>
                                <a href="#" onclick="Swal.fire({icon: 'info', title: 'Pagination', text: 'More data is coming soon.', confirmButtonColor: '#0069cd'}); return false;" class="relative inline-flex items-center px-2 py-2 rounded-r-md border
                                    border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm
                                    font-medium text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>

            </div><!-- /sec-vil -->
        </div>

    </main>
</div><!-- /main wrapper -->

<!-- AI Chatbot FAB Removed -->

<!-- ════════════════════════════════════════════════════════════
     SCRIPTS — Initialise Icons, Dark Mode, Sidebar & Charts
════════════════════════════════════════════════════════════ -->
<script>
/* ── Icons ──────────────────────────────────────────────────── */
lucide.createIcons();

/* ── Dark Mode ──────────────────────────────────────────────── */
const html  = document.getElementById('htmlRoot');
const btn   = document.getElementById('themeToggle');

function applyTheme(dark) {
    dark ? html.classList.add('dark') : html.classList.remove('dark');
    localStorage.setItem('acTheme', dark ? 'dark' : 'light');
    /* Only build charts if ApexCharts is already loaded */
    if (typeof ApexCharts !== 'undefined') {
        buildAllCharts(dark);
    }
}

const stored = localStorage.getItem('acTheme');
const prefersDark = stored ? stored === 'dark'
                           : window.matchMedia('(prefers-color-scheme:dark)').matches;

/* Apply dark/light class immediately (no FOUC) */
prefersDark ? html.classList.add('dark') : html.classList.remove('dark');

/* Build charts only after ALL resources (incl. ApexCharts CDN) are loaded */
window.addEventListener('load', function() {
    function tryBuild(attempts) {
        if (typeof ApexCharts !== 'undefined') {
            buildAllCharts(prefersDark);
        } else if (attempts > 0) {
            setTimeout(() => tryBuild(attempts - 1), 300);
        } else {
            document.querySelectorAll('[id^="chart-"]').forEach(el => {
                el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;font-family:Inter,sans-serif;padding:20px;text-align:center;">⚠ Could not load ApexCharts — check internet connection</div>';
            });
        }
    }
    tryBuild(20);
});



/* ── Sidebar Toggle ─────────────────────────────────────────── */
const sidebar = document.getElementById('sidebar');
document.getElementById('sidebarToggle').addEventListener('click', () => {
    const gone = sidebar.classList.contains('-translate-x-full') || sidebar.style.display === 'none';
    if (gone) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.style.display = 'flex';
    } else {
        sidebar.classList.add('-translate-x-full');
        setTimeout(() => sidebar.style.display = 'none', 300);
    }
});

/* ── Keyboard: '/' opens search ────────────────────────────── */
document.addEventListener('keydown', e => {
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
        e.preventDefault();
        document.getElementById('globalSearch').focus();
    }
});

/* ── Collapsible Sections ──────────────────────────────────── */
function toggleSec(id) {
    const body  = document.getElementById('sec-' + id);
    const chev  = document.getElementById('chev-' + id);
    body.classList.toggle('closed');
    chev.classList.toggle('open');
}

/* ── Taluka Table Filter ─────────────────────────────────────── */
function filterTalukaRows() {
    const filterSelect = document.getElementById('talukaTableFilter');
    const selectedTaluka = filterSelect ? filterSelect.value : '';
    document.querySelectorAll('#talukaPerformanceTable .taluka-row').forEach(row => {
        if (!selectedTaluka || row.dataset.taluka === selectedTaluka) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

/* ── Status Filter ─────────────────────────────────────────── */
function filterRows() {
    const statusVal = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';
    const searchInput = document.getElementById('globalSearch');
    const searchVal = searchInput ? searchInput.value.toLowerCase() : '';
    
    document.querySelectorAll('#taskTable .task-row').forEach(r => {
        const statusMatch = (!statusVal || r.dataset.status === statusVal);
        const textMatch = (!searchVal || r.textContent.toLowerCase().includes(searchVal));
        r.style.display = (statusMatch && textMatch) ? '' : 'none';
    });
}

const globalSearchEl = document.getElementById('globalSearch');
if (globalSearchEl) {
    globalSearchEl.addEventListener('input', filterRows);
    globalSearchEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            Swal.fire({
                icon: 'info',
                title: 'Coming Soon',
                text: 'Global Search across all modules is currently under development.',
                confirmButtonColor: '#0069cd'
            });
        }
    });
}

/* ════════════════════════════════════════════════════════════
   APEXCHARTS  —  Data pre-serialized from PHP
════════════════════════════════════════════════════════════ */

/* ── All DB data from PHP ────────────────────────────────── */
const CHART_DATA = {
<?php if ($showL1): ?>
    distTrend: {
        cats: <?= json_encode($distTrend['categories']) ?>,
        assigned: <?= json_encode($distTrend['assigned']) ?>,
        completed: <?= json_encode($distTrend['completed']) ?>
    },
    distStatus: [<?= inProgress($distData['active'],$distData['pending']) ?>, <?= $distData['pending'] ?>, <?= $distData['completed'] ?>, <?= $distData['overdue'] ?>],
    distTalukaNames: <?= json_encode(array_column($distData['talukas'], 'taluka')) ?>,
    distTalukaRates: <?= json_encode(array_map('floatval', array_column($distData['talukas'], 'rate'))) ?>,
    distPriority: [<?= (int)($distPriority['Critical']??0) ?>, <?= (int)($distPriority['High']??0) ?>, <?= (int)($distPriority['Medium']??0) ?>, <?= (int)($distPriority['Low']??0) ?>],
    distAgeing: [<?= (int)($distAgeing['< 5 Days']??0) ?>, <?= (int)($distAgeing['5-10 Days']??0) ?>, <?= (int)($distAgeing['11-30 Days']??0) ?>, <?= (int)($distAgeing['> 30 Days']??0) ?>],
    distRejLabels: <?= json_encode(array_sum(array_values($distRejections)) > 0 ? array_keys($distRejections) : ['No Rejections']) ?>,
    distRejValues: <?= json_encode(array_sum(array_values($distRejections)) > 0 ? array_values($distRejections) : [0]) ?>,
    distPerfNames: <?= json_encode(array_column($distPerform, 'name')) ?>,
    distPerfRates: <?= json_encode(array_map('floatval', array_column($distPerform, 'rate'))) ?>,
<?php else: ?>
    distTrend:null, distStatus:null, distTalukaNames:[], distTalukaRates:[],
    distPriority:[0,0,0,0], distAgeing:[0,0,0,0],
    distRejLabels:[], distRejValues:[], distPerfNames:[], distPerfRates:[],
<?php endif; ?>
<?php if ($showL2): ?>
    talTrend: {
        cats: <?= json_encode($talTrend['categories']) ?>,
        assigned: <?= json_encode($talTrend['assigned']) ?>,
        completed: <?= json_encode($talTrend['completed']) ?>
    },
    talStatus: [<?= inProgress($talData['active'],$talData['pending']) ?>, <?= $talData['pending'] ?>, <?= $talData['completed'] ?>, <?= $talData['overdue'] ?>],
    talVilNames: <?= json_encode(array_column($talData['villages'], 'village')) ?>,
    talVilCompleted: <?= json_encode(array_map('intval', array_column($talData['villages'], 'completed'))) ?>,
    talVilPending: <?= json_encode(array_map('intval', array_column($talData['villages'], 'pending'))) ?>,
    talVilOverdue: <?= json_encode(array_map('intval', array_column($talData['villages'], 'overdue'))) ?>,
    talPriority: [<?= (int)($talPriority['Critical']??0) ?>, <?= (int)($talPriority['High']??0) ?>, <?= (int)($talPriority['Medium']??0) ?>, <?= (int)($talPriority['Low']??0) ?>],
    talAgeing: [<?= (int)($talAgeing['< 5 Days']??0) ?>, <?= (int)($talAgeing['5-10 Days']??0) ?>, <?= (int)($talAgeing['11-30 Days']??0) ?>, <?= (int)($talAgeing['> 30 Days']??0) ?>],
    talRejLabels: <?= json_encode(array_sum(array_values($talRejections)) > 0 ? array_keys($talRejections) : ['No Rejections']) ?>,
    talRejValues: <?= json_encode(array_sum(array_values($talRejections)) > 0 ? array_values($talRejections) : [0]) ?>,
    talPerfNames: <?= json_encode(array_column($talPerform, 'name')) ?>,
    talPerfRates: <?= json_encode(array_map('floatval', array_column($talPerform, 'rate'))) ?>,
<?php else: ?>
    talTrend:null, talStatus:null, talVilNames:[], talVilCompleted:[], talVilPending:[], talVilOverdue:[],
    talPriority:[0,0,0,0], talAgeing:[0,0,0,0],
    talRejLabels:[], talRejValues:[], talPerfNames:[], talPerfRates:[],
<?php endif; ?>
    vilTrend: {
        cats: <?= json_encode($vilTrend['categories']) ?>,
        assigned: <?= json_encode($vilTrend['assigned']) ?>,
        completed: <?= json_encode($vilTrend['completed']) ?>
    },
    vilStatus: [<?= inProgress($vilData['active'],$vilData['pending']) ?>, <?= $vilData['pending'] ?>, <?= $vilData['completed'] ?>, <?= $vilData['overdue'] ?>],
    vilPriority: [<?= (int)($vilPriority['Critical']??0) ?>, <?= (int)($vilPriority['High']??0) ?>, <?= (int)($vilPriority['Medium']??0) ?>, <?= (int)($vilPriority['Low']??0) ?>],
    vilAgeing: [<?= (int)($vilAgeing['< 5 Days']??0) ?>, <?= (int)($vilAgeing['5-10 Days']??0) ?>, <?= (int)($vilAgeing['11-30 Days']??0) ?>, <?= (int)($vilAgeing['> 30 Days']??0) ?>],
    showL1: <?= $showL1 ? 'true' : 'false' ?>,
    showL2: <?= $showL2 ? 'true' : 'false' ?>
};

/* Labels */
const LBL = {
    assigned: <?= json_encode($t['chart_assigned_tasks']) ?>,
    completed: <?= json_encode($t['chart_completed_tasks']) ?>,
    inProgress: <?= json_encode($t['chart_in_progress']) ?>,
    pending: <?= json_encode($t['chart_pending']) ?>,
    overdue: <?= json_encode($t['chart_overdue']) ?>,
    rate: <?= json_encode($lang==='en' ? 'Completion Rate %' : 'पूर्णता दर %') ?>,
    vilCompleted: <?= json_encode($t['status_completed']) ?>,
    vilPending: <?= json_encode($t['status_pending']) ?>,
    vilOverdue: <?= json_encode($t['status_overdue']) ?>
};

/* ── Chart registries ───────────────────────────────────── */
let charts = {};
let chartJsInstances = {};
function destroyAll() {
    Object.values(charts).forEach(c => { try { c.destroy(); } catch(_){} });
    charts = {};
    Object.values(chartJsInstances).forEach(c => { try { c.destroy(); } catch(_){} });
    chartJsInstances = {};
}

/* ── Chart option builders ───────────────────────────────── */
function _ax(tc) { return { style:{ colors:tc, fontSize:'11px', fontFamily:'Inter,sans-serif' } }; }

function buildArea(el, series, cats, colors, isDark) {
    const tc = isDark ? '#94a3b8' : '#64748b';
    const gc = isDark ? '#1e3a5f33' : '#e2e8f0';
    return new ApexCharts(el, {
        series, colors,
        chart:{ height:288, type:'area', fontFamily:'Inter,sans-serif', toolbar:{show:false},
                background: isDark ? '#1e293b' : '#ffffff',
                animations:{enabled:true,easing:'easeinout',speed:900} },
        dataLabels:{enabled:false},
        stroke:{curve:'smooth',width:2.5},
        fill:{type:'gradient',gradient:{shadeIntensity:1,opacityFrom:0.35,opacityTo:0.02,stops:[0,100]}},
        xaxis:{categories:cats, labels:_ax(tc), axisBorder:{show:false}, axisTicks:{show:false}},
        yaxis:{labels:_ax(tc), min:0},
        grid:{borderColor:gc, strokeDashArray:4, padding:{left:8,right:8}},
        legend:{position:'top',horizontalAlign:'right',fontFamily:'Inter,sans-serif',fontSize:'12px',
                labels:{colors:tc}},
        tooltip:{theme:isDark?'dark':'light',shared:true,intersect:false},
        noData:{text:'No data yet',style:{color:tc,fontSize:'13px',fontFamily:'Inter,sans-serif'}},
        theme:{mode:isDark?'dark':'light'}
    });
}

function buildDonut(el, series, labels, colors, isDark) {
    const tc = isDark ? '#94a3b8' : '#64748b';
    const total = series.reduce((a,b)=>a+b,0);
    const safeSeries = total === 0 ? [1] : series;
    const safeLabels = total === 0 ? ['No Data'] : labels;
    const safeColors = total === 0 ? ['#94a3b8'] : colors;
    return new ApexCharts(el, {
        series: safeSeries, labels: safeLabels, colors: safeColors,
        chart:{ height:288, type:'donut', fontFamily:'Inter,sans-serif',
                background: isDark ? '#1e293b' : '#ffffff',
                animations:{enabled:true,easing:'easeinout',speed:900} },
        dataLabels:{
            enabled: total > 0,
            formatter: val => val.toFixed(1)+'%',
            style:{fontSize:'11px',fontFamily:'Inter,sans-serif',fontWeight:'600'},
            dropShadow:{enabled:false}
        },
        plotOptions:{pie:{donut:{size:'65%',
            labels:{show: total>0,
                total:{show:true, label:'Total', color:tc,
                       fontSize:'12px', fontFamily:'Inter,sans-serif',
                       formatter:()=>total.toLocaleString()},
                value:{fontSize:'20px',fontWeight:'700',fontFamily:'Inter,sans-serif',
                       color:isDark?'#f1f5f9':'#0f172a'}
            }
        }}},
        legend:{position:'bottom',fontFamily:'Inter,sans-serif',fontSize:'12px',
                labels:{colors:tc}, markers:{width:8,height:8,radius:8}},
        tooltip:{theme:isDark?'dark':'light',
                 y:{formatter:v=>total===0?'No data':v.toLocaleString()+' tasks'}},
        theme:{mode:isDark?'dark':'light'}
    });
}

function buildHBar(el, data, cats, color, isDark) {
    const tc = isDark ? '#94a3b8' : '#64748b';
    const gc = isDark ? '#1e3a5f33' : '#e2e8f0';
    const h  = Math.max(240, cats.length * 44 + 60);
    return new ApexCharts(el, {
        series:[{name:LBL.rate, data}],
        chart:{height:h, type:'bar', fontFamily:'Inter,sans-serif', toolbar:{show:false},
               background: isDark?'#1e293b':'#ffffff',
               animations:{enabled:true,easing:'easeinout',speed:900}},
        colors:[color],
        plotOptions:{bar:{borderRadius:6,horizontal:true,barHeight:'55%',
            dataLabels:{position:'right'}}},
        dataLabels:{enabled:true,
            textAnchor: 'start',
            formatter:v=>v>0?v.toFixed(1)+'%':'',
            style:{fontSize:'11px',fontFamily:'Inter,sans-serif',colors:[isDark?'#94a3b8':'#475569']},
            offsetX:25},
        xaxis:{categories:cats, max:100, labels:_ax(tc)},
        yaxis:{labels:{..._ax(tc), maxWidth:160}},
        grid:{borderColor:gc,strokeDashArray:4,xaxis:{lines:{show:true}},yaxis:{lines:{show:false}}},
        tooltip:{theme:isDark?'dark':'light',y:{formatter:v=>v.toFixed(1)+'%'}},
        noData:{text:'No data yet',style:{color:tc,fontSize:'13px',fontFamily:'Inter,sans-serif'}},
        theme:{mode:isDark?'dark':'light'}
    });
}

function buildStackedBar(el, seriesArr, cats, colors, isDark) {
    const tc = isDark ? '#94a3b8' : '#64748b';
    const gc = isDark ? '#1e3a5f33' : '#e2e8f0';
    return new ApexCharts(el, {
        series: seriesArr, colors,
        chart:{height:240, type:'bar', stacked:true, fontFamily:'Inter,sans-serif',
               toolbar:{show:false}, background:isDark?'#1e293b':'#ffffff',
               animations:{enabled:true,easing:'easeinout',speed:900}},
        plotOptions:{bar:{borderRadius:4,columnWidth:'55%',
            borderRadiusApplication:'end',borderRadiusWhenStacked:'last'}},
        dataLabels:{enabled:false},
        xaxis:{categories:cats, labels:{..._ax(tc),rotate:-35,trim:true,maxHeight:60}},
        yaxis:{labels:_ax(tc)},
        grid:{borderColor:gc,strokeDashArray:4},
        legend:{position:'top',fontFamily:'Inter,sans-serif',fontSize:'12px',labels:{colors:tc},
                markers:{width:8,height:8,radius:8}},
        tooltip:{theme:isDark?'dark':'light',shared:true,intersect:false},
        noData:{text:'No data yet',style:{color:tc,fontSize:'13px',fontFamily:'Inter,sans-serif'}},
        theme:{mode:isDark?'dark':'light'}
    });
}

/* ── Main render function ────────────────────────────────── */
function buildAllCharts(isDark) {
    destroyAll();
    if (typeof ApexCharts === 'undefined') {
        document.querySelectorAll('[id^="chart-"]').forEach(el => {
            el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;font-family:Inter,sans-serif;padding:20px;text-align:center">⚠ Charts loading... refresh if this persists</div>';
        });
        return;
    }
    try {
        const d = CHART_DATA;
        const pL = ['Critical','High','Medium','Low'];
        const pC = ['#ef4444','#f97316','#eab308','#3b82f6'];
        const aL = ['< 5 Days','5–10 Days','11–30 Days','> 30 Days'];
        const aC = ['#10b981','#3b82f6','#f59e0b','#ef4444'];
        const sL = [LBL.inProgress, LBL.pending, LBL.completed, LBL.overdue];
        const sC = ['#3b82f6','#f59e0b','#10b981','#ef4444'];

        /* ── District ─────────────────────────────────── */
        if (d.showL1) {
            const el = id => document.querySelector('#chart-dist-'+id);

            charts.dTrend = buildArea(el('trend'),
                [{name:LBL.assigned, data:d.distTrend.assigned},{name:LBL.completed, data:d.distTrend.completed}],
                d.distTrend.cats, ['#3b82f6','#10b981'], isDark);
            charts.dTrend.render();

            charts.dDonut = buildDonut(el('donut'), d.distStatus, sL, sC, isDark);
            charts.dDonut.render();

            charts.dBar = buildHBar(el('bar'), d.distTalukaRates, d.distTalukaNames, '#1a365d', isDark);
            charts.dBar.render();

            charts.dPriority = buildDonut(el('priority'), d.distPriority, pL, pC, isDark);
            charts.dPriority.render();

            charts.dAgeing = buildDonut(el('ageing'), d.distAgeing, aL, aC, isDark);
            charts.dAgeing.render();

            charts.dRejections = buildDonut(el('rejections'), d.distRejValues, d.distRejLabels,
                ['#ef4444','#f97316','#3b82f6','#10b981','#a855f7'], isDark);
            charts.dRejections.render();

            charts.dPerformance = buildHBar(el('performance'), d.distPerfRates, d.distPerfNames, '#10b981', isDark);
            charts.dPerformance.render();
        }

        /* ── Taluka ─────────────────────────────────── */
        if (d.showL2) {
            const el = id => document.querySelector('#chart-tal-'+id);

            charts.tTrend = buildArea(el('trend'),
                [{name:LBL.assigned, data:d.talTrend.assigned},{name:LBL.completed, data:d.talTrend.completed}],
                d.talTrend.cats, ['#f59e0b','#10b981'], isDark);
            charts.tTrend.render();

            charts.tDonut = buildDonut(el('donut'), d.talStatus, sL, sC, isDark);
            charts.tDonut.render();

            charts.tBar = buildStackedBar(el('bar'),
                [{name:LBL.vilCompleted,data:d.talVilCompleted},{name:LBL.vilPending,data:d.talVilPending},{name:LBL.vilOverdue,data:d.talVilOverdue}],
                d.talVilNames, ['#10b981','#f59e0b','#ef4444'], isDark);
            charts.tBar.render();

            charts.tPriority = buildDonut(el('priority'), d.talPriority, pL, pC, isDark);
            charts.tPriority.render();

            charts.tAgeing = buildDonut(el('ageing'), d.talAgeing, aL, aC, isDark);
            charts.tAgeing.render();

            charts.tRejections = buildDonut(el('rejections'), d.talRejValues, d.talRejLabels,
                ['#ef4444','#f97316','#3b82f6','#10b981','#a855f7'], isDark);
            charts.tRejections.render();

            charts.tPerformance = buildHBar(el('performance'), d.talPerfRates, d.talPerfNames, '#10b981', isDark);
            charts.tPerformance.render();
        }

        /* ── Village ─────────────────────────────────── */
        {
            const el = id => document.querySelector('#chart-vil-'+id);

            charts.vTrend = buildArea(el('trend'),
                [{name:LBL.assigned, data:d.vilTrend.assigned},{name:LBL.completed, data:d.vilTrend.completed}],
                d.vilTrend.cats, ['#10b981','#f59e0b'], isDark);
            charts.vTrend.render();

            charts.vDonut = buildDonut(el('donut'), d.vilStatus, sL, sC, isDark);
            charts.vDonut.render();

            charts.vPriority = buildDonut(el('priority'), d.vilPriority, pL, pC, isDark);
            charts.vPriority.render();

            charts.vAgeing = buildDonut(el('ageing'), d.vilAgeing, aL, aC, isDark);
            charts.vAgeing.render();
        }
    } catch(chartErr) {
        console.error('ApexCharts render error:', chartErr);
        document.querySelectorAll('[id^="chart-"]').forEach(el => {
            if (!el.querySelector('svg')) {
                el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;font-family:Inter,sans-serif;">⚠ Chart rendering failed</div>';
            }
        });
    }
}












/* ── Export Dashboard Data ─────────────────────────────── */
function exportDashboardData() {
    window.location.href = 'api/export_data.php?type=csv&scope=<?= $showL1 ? "district" : ($showL2 ? "taluka" : "village") ?>&lang=<?= $lang ?>';
}

function exportDashboardPDF() {
    if (typeof html2pdf === 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Fallback Printing',
            text: 'PDF generation library is offline. Opening browser print dialog instead.',
            confirmButtonColor: '#0069cd'
        }).then(() => {
            window.print();
        });
        return;
    }
    
    Swal.fire({
        title: 'Generating Report...',
        text: 'Please wait while we render the charts and generate your PDF.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const element = document.getElementById('dashboard-report-container');
    
    // Add print styles temporary to hide elements with no-print
    const style = document.createElement('style');
    style.id = 'pdf-export-temp-style';
    style.innerHTML = `
        .no-print, 
        #sidebar, 
        header, 
        .btn-modern,
        #themeToggle,
        #notificationBtn,
        #sidebarToggle {
            display: none !important;
        }
        /* Ensure background colors are preserved and charts print correctly */
        #dashboard-report-container {
            padding: 20px !important;
            background: #f8fafc !important;
            color: #0f172a !important;
        }
        .dark #dashboard-report-container {
            background: #0f172a !important;
            color: #f1f5f9 !important;
        }
        /* Page break adjustments for cards and sections */
        .sec-body {
            page-break-inside: auto !important;
        }
        .kpi-card, .glass-panel {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
    `;
    document.head.appendChild(style);

    const filename = 'Connect_Amravati_Executive_Report_' + new Date().toISOString().slice(0, 10) + '.pdf';
    
    const opt = {
        margin:       [0.4, 0.4, 0.4, 0.4],
        filename:     filename,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 2, 
            useCORS: true,
            logging: false,
            letterRendering: true
        },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        style.remove();
        Swal.close();
    }).catch(err => {
        console.error('PDF export failed:', err);
        style.remove();
        Swal.fire({
            icon: 'error',
            title: 'Export Failed',
            text: 'There was an issue generating the PDF. Opening standard print window instead.'
        }).then(() => {
            window.print();
        });
    });
}
</script>

<!-- Notification System (isolated script block) -->
<script>
// Notification Bell Logic
const notificationBtn = document.getElementById('notificationBtn');
const notificationDropdown = document.getElementById('notificationDropdown');
const unreadCountBadge = document.getElementById('unreadCountBadge');
const notificationList = document.getElementById('notificationList');



let lastUnreadCount = 0;

function playChime() {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.frequency.value = 587.33; // D5 tone
        gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
        osc.start();
        setTimeout(() => {
            osc.frequency.value = 880; // A5 tone
            setTimeout(() => {
                osc.stop();
                audioCtx.close();
            }, 100);
        }, 120);
    } catch (e) {
        console.error('AudioContext error:', e);
    }
}

function fetchNotifications() {
    fetch('api/get_notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.unread_count > lastUnreadCount) {
                    playChime();
                }
                lastUnreadCount = data.unread_count;

                if (data.unread_count > 0) {
                    unreadCountBadge.style.display = 'flex';
                    unreadCountBadge.innerText = data.unread_count > 99 ? '99+' : data.unread_count;
                } else {
                    unreadCountBadge.style.display = 'none';
                }
                notificationList.innerHTML = '';
                if (data.notifications.length === 0) {
                    notificationList.innerHTML = `<div class="px-4 py-6 text-center text-sm text-slate-500">No new notifications</div>`;
                } else {
                    data.notifications.forEach(n => {
                        const isUnread = n.is_read == 0;
                        const readBgClass = isUnread ? 'bg-blue-50/30 dark:bg-slate-800/80 border-l-4 border-blue-500 font-medium' : 'bg-transparent border-l-4 border-transparent opacity-75 hover:opacity-100';
                        const titleWeight = isUnread ? 'font-bold text-slate-900 dark:text-white' : 'font-medium text-slate-700 dark:text-slate-300';
                        const dotIndicator = isUnread ? `<span class="absolute top-4 right-4 w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_5px_rgba(59,130,246,0.6)]"></span>` : '';
                        
                        let actionsHtml = '';
                        if (n.actions && n.actions.length > 0) {
                            actionsHtml += `<div class="mt-2 flex flex-wrap gap-1.5" onclick="event.stopPropagation();">`;
                            n.actions.forEach(act => {
                                if (act.action === 'accept') {
                                    actionsHtml += `<button onclick="acceptTask(${n.task_id}, ${n.id})" class="px-2 py-1 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded text-[10px] font-bold transition-colors">Accept</button>`;
                                } else if (act.action === 'reject') {
                                    actionsHtml += `<button onclick="openRejectTaskModal(${n.task_id})" class="px-2 py-1 bg-red-500 hover:bg-red-650 text-white rounded text-[10px] font-bold transition-colors">Reject</button>`;
                                } else if (act.action === 'verify_rejection') {
                                    actionsHtml += `<button onclick="openReviewRejectionModal(${n.task_id})" class="px-2 py-1 bg-navy-500 hover:bg-navy-600 text-white rounded text-[10px] font-bold transition-colors">Verify Rejection</button>`;
                                } else if (act.action === 'verify_completion') {
                                    actionsHtml += `<button onclick="verifyCompletion(${n.task_id}, ${n.id})" class="px-2 py-1 bg-purple-500 hover:bg-purple-650 text-white rounded text-[10px] font-bold transition-colors">Verify Completion</button>`;
                                }
                            });
                            actionsHtml += `</div>`;
                        }

                        const item = document.createElement('div');
                        item.className = `relative px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-all duration-200 ${readBgClass}`;
                        item.innerHTML = `
                            ${dotIndicator}
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mt-0.5 shadow-sm ${n.badge_color}">
                                    <i data-lucide="bell" class="w-4 h-4"></i>
                                </div>
                                <div class="ml-3 flex-1 pr-6">
                                    <p class="text-sm ${titleWeight}">${n.title}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">${n.message}</p>
                                    ${actionsHtml}
                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1.5 font-medium flex items-center">
                                        <i data-lucide="clock" class="w-3 h-3 mr-1 opacity-70"></i> ${n.time_elapsed}
                                    </p>
                                </div>
                            </div>
                        `;
                        item.onclick = () => {
                            const currentLang = new URLSearchParams(window.location.search).get('lang') || 'en';
                            const targetUrl = 'notifications.php?lang=' + currentLang;
                            if (isUnread) {
                                fetch('api/mark_notification_read.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ notification_id: n.id })
                                }).then(() => {
                                    window.location.href = targetUrl;
                                });
                            } else {
                                window.location.href = targetUrl;
                            }
                        };
                        notificationList.appendChild(item);
                    });
                    lucide.createIcons();
                }
            }
        })
        .catch(err => console.error('Error fetching notifications:', err));
}

function acceptTask(taskId, notifId) {
    fetch('api/task_notification_actions.php?action=accept&task_id=' + taskId)
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                if (notifId) markAsRead(notifId);
                fetchNotifications();
            }
        });
}

function verifyCompletion(taskId, notifId) {
    fetch(`api/task_notification_actions.php?action=verify&task_id=${taskId}`)
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                if (notifId) markAsRead(notifId);
                fetchNotifications();
            }
        });
}

function openRejectTaskModal(taskId) {
    document.getElementById('rejectTaskId').value = taskId;
    document.getElementById('rejectTaskModal').classList.remove('hidden');
}

function closeRejectTaskModal() {
    document.getElementById('rejectTaskModal').classList.add('hidden');
    document.getElementById('rejectTaskForm').reset();
}

function submitRejectTask(e) {
    e.preventDefault();
    const form = document.getElementById('rejectTaskForm');
    const fd = new FormData(form);
    fd.append('action', 'reject');

    fetch('api/task_notification_actions.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === 'success') {
            closeRejectTaskModal();
            fetchNotifications();
        }
    })
    .catch(() => alert('Network error submitting rejection. Ensure remarks and file upload size matches.'));
}

function openReviewRejectionModal(taskId) {
    document.getElementById('reviewTaskId').value = taskId;
    
    fetch(`api/task_notification_actions.php?action=get_rejection_details&task_id=${taskId}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const rej = res.rejection;
                document.getElementById('reviewEmployeeName').innerText = rej.full_name;
                document.getElementById('reviewTaskTitle').innerText = `ID: ${taskId}`;
                document.getElementById('reviewReason').innerText = rej.rejection_reason;
                document.getElementById('reviewRemarks').innerText = rej.remarks;
                
                const fileName = rej.file_path.split('/').pop();
                document.getElementById('reviewProofName').innerText = fileName;
                document.getElementById('reviewProofDownload').href = rej.file_path;
                
                document.getElementById('reviewRejectionModal').classList.remove('hidden');
                lucide.createIcons();
            } else {
                alert(res.message);
            }
        });
}

function closeReviewRejectionModal() {
    document.getElementById('reviewRejectionModal').classList.add('hidden');
}

function submitRejectionReview(actionName) {
    const taskId = document.getElementById('reviewTaskId').value;
    fetch(`api/task_notification_actions.php?action=${actionName}&task_id=${taskId}`)
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                closeReviewRejectionModal();
                fetchNotifications();
            }
        });
}

function openClarificationModal() {
    document.getElementById('clarificationModal').classList.remove('hidden');
}

function closeClarificationModal() {
    document.getElementById('clarificationModal').classList.add('hidden');
    document.getElementById('clarificationMessage').value = '';
}

function submitClarification(e) {
    e.preventDefault();
    const taskId = document.getElementById('reviewTaskId').value;
    const message = document.getElementById('clarificationMessage').value;

    const fd = new FormData();
    fd.append('action', 'request_clarification');
    fd.append('task_id', taskId);
    fd.append('message', message);

    fetch('api/task_notification_actions.php', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.status === 'success') {
            closeClarificationModal();
            closeReviewRejectionModal();
            fetchNotifications();
        }
    });
}

function markAsRead(id) {
    fetch('api/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notification_id: id })
    }).then(() => fetchNotifications());
}

function markAllAsRead() {
    fetch('api/mark_notification_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mark_all: true })
    }).then(() => fetchNotifications());
}

setInterval(fetchNotifications, 5000);
fetchNotifications();
</script>

<!-- MODALS FOR TASK WORKFLOW ACTIONS -->
<div id="rejectTaskModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-red-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Task Rejection Submission</h3>
            <button onclick="closeRejectTaskModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form id="rejectTaskForm" onsubmit="submitRejectTask(event)" class="p-6 space-y-4">
            <input type="hidden" id="rejectTaskId" name="task_id">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Rejection Reason *</label>
                <select name="reason" required class="input-modern">
                    <option value="">-- Choose Rejection Reason --</option>
                    <option value="Overlapping Priorities">Overlapping Priorities</option>
                    <option value="Resource Unavailability">Resource Unavailability</option>
                    <option value="Outside Area of Responsibility">Outside Area of Responsibility</option>
                    <option value="Technical Insufficiency">Technical Insufficiency</option>
                    <option value="Health / Leave Period">Health / Leave Period</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Detailed Remarks *</label>
                <textarea name="remarks" rows="4" required placeholder="Explain in detail why you are rejecting this task assignment..." class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-red-500"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Proof Upload (Mandatory Document) *</label>
                <input type="file" name="proof_file" required class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 border border-slate-300 dark:border-slate-600 rounded-lg p-1 dark:bg-slate-700">
                <p class="text-[10px] text-slate-400 mt-1">Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP. Max file size: 10MB.</p>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeRejectTaskModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">Submit Rejection</button>
            </div>
        </form>
    </div>
</div>

<div id="reviewRejectionModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-navy-500 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Rejection Verification Review</h3>
            <button onclick="closeReviewRejectionModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="reviewTaskId">
            
            <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg">
                <div>
                    <span class="block text-xs text-slate-400 font-bold uppercase">Employee</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-white" id="reviewEmployeeName">Employee Name</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-400 font-bold uppercase">Task Name</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-white" id="reviewTaskTitle">Task Name</span>
                </div>
            </div>

            <div>
                <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Rejection Reason</span>
                <span class="text-sm font-semibold text-red-650 dark:text-red-400 bg-red-50 dark:bg-red-950/20 px-3 py-1 rounded-md" id="reviewReason">Reason Description</span>
            </div>

            <div>
                <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Detailed Remarks</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/30 p-3 rounded-lg border border-slate-200 dark:border-slate-700 whitespace-pre-wrap" id="reviewRemarks">Detailed description remarks...</p>
            </div>

            <div class="border border-slate-200 dark:border-slate-700 p-4 rounded-xl flex justify-between items-center bg-slate-50 dark:bg-slate-900/30">
                <div class="flex items-center">
                    <i data-lucide="paperclip" class="w-5 h-5 text-slate-400 mr-2"></i>
                    <span class="text-sm font-semibold" id="reviewProofName">proof_file.pdf</span>
                </div>
                <a href="#" id="reviewProofDownload" target="_blank" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-xs font-bold transition-colors">Download Proof</a>
            </div>

            <div class="flex flex-wrap gap-2 justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="openClarificationModal()" class="px-3.5 py-2 bg-saffron-500 hover:bg-saffron-600 text-white rounded-lg text-xs font-semibold transition-colors">Request Clarification</button>
                <button type="button" onclick="submitRejectionReview('reject_rejection')" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors">Deny Rejection (Reassign)</button>
                <button type="button" onclick="submitRejectionReview('approve_rejection')" class="px-3.5 py-2 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded-lg text-xs font-semibold transition-colors">Approve Rejection</button>
            </div>
        </div>
    </div>
</div>

<div id="clarificationModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-saffron-500 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Clarification Message</h3>
            <button onclick="closeClarificationModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form onsubmit="submitClarification(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Enter your request or clarification notes *</label>
                <textarea id="clarificationMessage" required rows="4" placeholder="Please clarify the dates or provide further context..." class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-saffron-500"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeClarificationModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-saffron-500 hover:bg-saffron-600 text-white rounded-lg text-sm font-semibold transition-colors">Send Request</button>
            </div>
        </form>
    </div>
</div>
<?php include 'include/tracking_modal.php'; ?>
<?php include 'include/tracking_modal.php'; ?>
<?php include 'include/footer.php'; ?>
