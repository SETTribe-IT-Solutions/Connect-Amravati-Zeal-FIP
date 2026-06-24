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
        'menu_gis' => 'GIS Map View',
        'menu_docs' => 'Document Management',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'menu_hierarchy' => 'Location Hierarchy',
        'menu_audit' => 'Audit Logs',
        'menu_settings' => 'Settings',
        'menu_logout' => 'Logout',
        'btn_ask_ai' => 'Ask Amravati AI',
        'page_title' => 'District Executive Dashboard',
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

/* ─── Session defaults (dev preview) ───────────────────────── */
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

$distTrend  = $showL1 ? getMonthlyTrend($conn, 'district', 0, $lang) : null;
$talTrend   = $showL2 ? getMonthlyTrend($conn, 'taluka', $sTalukaId, $lang) : null;
$vilTrend   = getMonthlyTrend($conn, 'village', $sVillageId, $lang);

$distPriority   = $showL1 ? getPriorityDistribution($conn, 'district', 0) : ['Critical'=>1, 'High'=>3, 'Medium'=>5, 'Low'=>4];
$distAgeing     = $showL1 ? getTaskAgeing($conn, 'district', 0) : ['< 5 Days'=>2, '5-10 Days'=>4, '11-30 Days'=>3, '> 30 Days'=>1];
$distRejections = $showL1 ? getRejectionAnalysis($conn, 'district', 0) : ['Overlapping Priorities'=>2, 'Resource Unavailability'=>1];
$distPerform    = $showL1 ? getUserPerformance($conn, 'district', 0) : [];

$talPriority   = $showL2 ? getPriorityDistribution($conn, 'taluka', $sTalukaId) : ['Critical'=>1, 'High'=>2, 'Medium'=>3, 'Low'=>2];
$talAgeing     = $showL2 ? getTaskAgeing($conn, 'taluka', $sTalukaId) : ['< 5 Days'=>1, '5-10 Days'=>2, '11-30 Days'=>1, '> 30 Days'=>0];
$talRejections = $showL2 ? getRejectionAnalysis($conn, 'taluka', $sTalukaId) : ['Overlapping Priorities'=>1];
$talPerform    = $showL2 ? getUserPerformance($conn, 'taluka', $sTalukaId) : [];

$vilPriority   = getPriorityDistribution($conn, 'village', $sVillageId);
$vilAgeing     = getTaskAgeing($conn, 'village', $sVillageId);

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
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <meta name="description"
          content="<?= htmlspecialchars($t['desc']) ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- ApexCharts with CDN fallback -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        if (typeof ApexCharts === 'undefined') {
            document.write('<scr' + 'ipt src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.49.0/apexcharts.min.js"><\/scr' + 'ipt>');
        }
    </script>

    <!-- Tailwind config — identical to blank_wrushabh.php ─── -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        border:     "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        navy: {
                            50:'#eef2f6', 100:'#d9e2ec',
                            500:'#1a365d', 600:'#152b4a',
                            700:'#0f1f38', 900:'#0a1424'
                        },
                        govgreen: { 50:'#edf7ed', 100:'#cce8cc', 500:'#2e7d32', 600:'#256428' },
                        saffron:  { 50:'#fff3e0', 100:'#ffe0b2', 500:'#f57c00', 600:'#e65100' }
                    }
                }
            }
        }
    </script>

    <style>
        /* CSS vars ─ identical to blank_wrushabh.php */
        :root { --background:0 0% 100%; --foreground:222.2 84% 4.9%; --border:214.3 31.8% 91.4%; }
        .dark { --background:222.2 84% 4.9%; --foreground:210 40% 98%; --border:217.2 32.6% 17.5%; }

        body { font-family:'Inter',sans-serif; background-color:hsl(var(--background)); color:hsl(var(--foreground)); }

        /* Scrollbar */
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }

        /* Glass panel */
        .glass-panel { background:rgba(255,255,255,0.7); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2); }
        .dark .glass-panel { background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.05); }

        /* KPI card hover */
        .kpi-card { transition:transform .2s ease, box-shadow .2s ease; }
        .kpi-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px -6px rgba(0,0,0,.12); }

        /* Collapsible sections */
        .sec-body {
            overflow:hidden;
            transition:max-height .4s cubic-bezier(.4,0,.2,1), opacity .3s ease;
            max-height:9999px; opacity:1;
        }
        .sec-body.closed { max-height:0; opacity:0; }
        .chevron { transition:transform .3s ease; }
        .chevron.open { transform:rotate(-180deg); }

        /* Level badges */
        .badge-l1 { background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; }
        .badge-l2 { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .badge-l3 { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .dark .badge-l1 { background:#1e3a8a33; color:#93c5fd; border-color:#1e40af; }
        .dark .badge-l2 { background:#92400e33; color:#fcd34d; border-color:#b45309; }
        .dark .badge-l3 { background:#065f4633; color:#6ee7b7; border-color:#047857; }

        /* Active nav */
        .nav-active { background:#eef2f6; color:#152b4a; }
        .dark .nav-active { background:#1e293b; color:#fff; }

        /* Pulse for overdue dot */
        @keyframes pulse-dot { 0%,100%{opacity:1;} 50%{opacity:.35;} }
        .pulse { animation:pulse-dot 1.5s ease-in-out infinite; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- ════════════════════════════════════════════════════════════
     SIDEBAR  ─  matches blank_wrushabh.php exactly
════════════════════════════════════════════════════════════ -->
<aside id="sidebar"
       class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800
              flex flex-col transition-all duration-300 z-20">

    <!-- Logo / Brand -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight"><?= htmlspecialchars($t['brand_name']) ?></span>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
            <a href="dashboard.php?lang=<?= $lang ?>"
               class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                <?= htmlspecialchars($t['menu_dashboard']) ?>
            </a>
            <a href="announcements.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="megaphone" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_announcement_center'] ?? 'Announcement Center') ?>
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network"   class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_task_alloc']) ?>
            </a>
            <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_notifications']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award"     class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_appreciation']) ?>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
            <a href="reports.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart"   class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_reports']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map"         class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_gis']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_docs']) ?>
            </a>

            <?php if ($level === 1): ?>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_admin']) ?></p>
            <a href="user_creation.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users"        class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_users']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map-pin"      class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_hierarchy']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_audit']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="settings"     class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_settings']) ?>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 text-red-500"></i><?= htmlspecialchars($t['menu_logout']) ?>
            </a>
        </nav>
    </div>

    <!-- Footer CTA -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent
                       rounded-md shadow-sm text-sm font-medium text-white
                       bg-gradient-to-r from-navy-600 to-navy-500
                       hover:from-navy-700 hover:to-navy-600 focus:outline-none transition-all">
            <i data-lucide="bot" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_ask_ai']) ?>
        </button>
    </div>
</aside>

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
                           dark:hover:text-slate-200 focus:outline-none hidden md:block">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <!-- Search -->
            <div class="max-w-md w-full relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                </div>
                <input id="globalSearch" type="text"
                       placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>"
                       class="block w-full pl-10 pr-3 py-2 border border-slate-300
                              dark:border-slate-700 rounded-md leading-5
                              bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100
                              placeholder-slate-500 focus:outline-none
                              focus:ring-1 focus:ring-navy-500 focus:border-navy-500
                              sm:text-sm transition-colors">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-slate-400 text-xs border border-slate-300
                                 dark:border-slate-700 rounded px-1.5 py-0.5">⌘K</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'dashboard.php?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                      hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md
                      transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                <?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?>
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
            <!-- Profile -->
            <div class="flex items-center space-x-3 border-l border-slate-200
                        dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                <div class="flex flex-col text-right hidden sm:block">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white">
                        <?= htmlspecialchars($sName) ?>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        <?= htmlspecialchars($roleLabel) ?>
                    </span>
                </div>
                <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center
                            text-white font-bold text-sm border-2 border-white dark:border-slate-800 shadow-sm">
                    <?= htmlspecialchars($initials) ?>
                </div>
            </div>
        </div>
    </header>

    <!-- ── MAIN SCROLL AREA ───────────────────────────────── -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

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
            <div class="mt-4 md:mt-0 flex items-center space-x-3 flex-wrap gap-y-2">
                <!-- Access level badge -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                             <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                </span>
                <button onclick="exportDashboardData()" class="inline-flex items-center px-4 py-2 border border-slate-300
                               dark:border-slate-600 shadow-sm text-sm font-medium rounded-md
                               text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800
                               hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_export']) ?>
                </button>
                <button onclick="window.location.href='create_task.php?lang=<?= $lang ?>'" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm
                               text-sm font-medium rounded-md text-white
                               bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
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
                        ['Total Tasks', $dynamicTotalTasks, 'hash', 'blue', '', '', false]
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

                    foreach ($dynamicStatusCounts as $row) {
                        $st = $row['status'];
                        $val = (int)$row['total'];
                        $style = $statusStyles[$st] ?? ['icon' => 'layers', 'color' => 'slate'];
                        
                        $dkpi[] = [
                            'Total ' . $st . ' Tasks',
                            $val,
                            $style['icon'],
                            $style['color'],
                            '', '', false // No trend data for dynamic statuses
                        ];
                    }

                    foreach ($dkpi as [$label,$val,$icon,$clr,$trendIcon,$trendTxt,$trendUp]):
                    ?>
                    <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm
                                rounded-xl border border-slate-200 dark:border-slate-700">
                        <div class="p-5">
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
                    <?php endforeach; ?>
                </div>

                <!-- Charts — same 3-col grid as blank_wrushabh.php -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    <!-- Line / Area Chart -->
                    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                <?= htmlspecialchars($t['chart_trend']) ?>
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div class="h-72 w-full relative">
                            <canvas id="chartjs-line-trend"></canvas>
                        </div>
                    </div>
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
                        <div class="h-72 w-full relative">
                            <canvas id="chartjs-pie-status"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Performing Offices — bar chart -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($t['chart_taluka']) ?>
                        </h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div id="chart-dist-bar" class="h-60 w-full"></div>
                </div>

                <!-- New District Graphs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
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
                                <?= $lang === 'en' ? 'Task Ageing Analysis (Open Tasks)' : 'कार्य प्रलंबित कालावधी विश्लेषण (सक्रिय कार्ये)' ?>
                            </h2>
                        </div>
                        <div id="chart-dist-ageing" class="h-72 w-full"></div>
                    </div>
                </div>
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
                            <select class="block pl-3 pr-8 py-2 text-sm border-slate-300
                                          dark:border-slate-600 dark:bg-slate-700 dark:text-white
                                          rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option><?= htmlspecialchars($t['all_talukas']) ?></option>
                                <?php foreach ($distData['talukas'] as $tRow): ?>
                                <option><?= htmlspecialchars($tRow['taluka']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="p-2 border border-slate-300 dark:border-slate-600 rounded-md
                                          text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">
                                <i data-lucide="filter" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
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
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
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
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md
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
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md
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
                        [$t['kpi_active'],   $talData['active'],    'layers',       'blue',   'trending-up',   '+9%',  true],
                        [$t['kpi_pending'],    $talData['pending'],   'clock',        'orange', 'trending-down', '-2%',  false],
                        [$t['kpi_completed'],      $talData['completed'], 'check-circle', 'green',  'trending-up',   '+18%', true],
                        [$t['kpi_overdue'],  $talData['overdue'],   'alert-octagon','red',    'alert-triangle', $lang === 'en' ? '5 Req' : '५ कृती आवश्यक', false],
                    ];
                    foreach ($tkpi as [$label,$val,$icon,$clr,$tIcon,$tTxt,$tUp]):
                    ?>
                    <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm
                                rounded-xl border border-slate-200 dark:border-slate-700">
                        <div class="p-5">
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
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
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
                        [$t['kpi_active'],  $vilData['active'],    'layers',       'blue',   'trending-up',   '+5%',    true],
                        [$t['kpi_pending_tasks'],       $vilData['pending'],   'clock',        'orange', 'alert-triangle', $t['kpi_needs_attention'], false],
                        [$t['kpi_completed'],     $vilData['completed'], 'check-circle', 'green',  'trending-up',   '+15%',   true],
                        [$t['kpi_overdue_tasks'],       $vilData['overdue'],   'alert-octagon','red',    'alert-triangle', $t['kpi_urgent'], false],
                    ];
                    foreach ($vkpi as [$label,$val,$icon,$clr,$tIcon,$tTxt,$tUp]):
                    ?>
                    <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm
                                rounded-xl border border-slate-200 dark:border-slate-700">
                        <div class="p-5">
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
                            <button class="p-2 border border-slate-300 dark:border-slate-600 rounded-md
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
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors task-row"
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
                                        <button class="text-navy-600 dark:text-blue-400 hover:text-navy-900
                                                       dark:hover:text-blue-300 mr-3" title="View">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>
                                        <button class="text-slate-400 hover:text-slate-600
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
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border
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
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border
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

<!-- AI Chatbot FAB -->
<div class="fixed bottom-6 right-6 z-50">
    <button class="w-14 h-14 bg-gradient-to-r from-navy-600 to-navy-500 rounded-full shadow-lg
                   flex items-center justify-center text-white hover:scale-105 transition-transform
                   shadow-navy-500/30" title="Ask Amravati AI">
        <i data-lucide="message-square-text" class="w-6 h-6"></i>
    </button>
</div>

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
    buildAllCharts(dark);
}

const stored = localStorage.getItem('acTheme');
const prefersDark = stored ? stored === 'dark'
                           : window.matchMedia('(prefers-color-scheme:dark)').matches;
applyTheme(prefersDark);

btn.addEventListener('click', () => applyTheme(!html.classList.contains('dark')));

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

/* ── Status Filter ─────────────────────────────────────────── */
function filterRows() {
    const val = document.getElementById('statusFilter').value;
    document.querySelectorAll('#taskTable .task-row').forEach(r => {
        r.style.display = (!val || r.dataset.status === val) ? '' : 'none';
    });
}

/* ════════════════════════════════════════════════════════════
   APEXCHARTS
════════════════════════════════════════════════════════════ */
let charts = {};
let chartJsInstances = {};

function destroyAll() {
    Object.values(charts).forEach(c => { try { c.destroy(); } catch(_){} });
    charts = {};
    Object.values(chartJsInstances).forEach(c => { try { c.destroy(); } catch(_){} });
    chartJsInstances = {};
}

function buildAllCharts(isDark) {
    destroyAll();
    if (typeof ApexCharts === 'undefined') {
        document.querySelectorAll('[id^="chart-"]').forEach(el => {
            el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;font-family:Inter,sans-serif;"><span>⚠ Charts unavailable — check internet connection</span></div>';
        });
        return;
    }
    try {
    const tc  = isDark ? '#94a3b8' : '#64748b';   // text
    const gc  = isDark ? '#334155' : '#e2e8f0';   // grid
    const mode= isDark ? 'dark'    : 'light';
    const ax  = { style:{ colors:tc, fontSize:'11px', fontFamily:'Inter,sans-serif' } };

    /* ── Shared builders ─────────────────────────────────── */
    function lineOpts(series, cats, colors) {
        return {
            series, colors,
            chart:{ height:288, type:'line', fontFamily:'Inter,sans-serif',
                    toolbar:{show:false}, background:'transparent' },
            dataLabels:{ enabled:false },
            stroke:{ curve:'smooth', width:2 },
            markers:{ size: 4 },
            tooltip:{ enabled: true, theme: mode },
            xaxis:{ title:{ text: 'Completion Date', style: { color: tc, fontSize: '12px', fontFamily: 'Inter,sans-serif' } }, categories:cats, labels:ax, axisBorder:{show:false}, axisTicks:{show:false} },
            yaxis:{ title:{ text: 'Completed Task Count', style: { color: tc, fontSize: '12px', fontFamily: 'Inter,sans-serif' } }, labels:ax },
            grid:{ borderColor:gc, strokeDashArray:4 },
            legend:{ position:'top', horizontalAlign:'right',
                     fontFamily:'Inter,sans-serif', fontSize:'12px' },
            theme:{ mode }
        };
    }

    function pieOpts(series, labels, colors) {
        return {
            series, labels, colors,
            chart:{ height:288, type:'pie', fontFamily:'Inter,sans-serif', background:'transparent' },
            dataLabels:{ enabled:true },
            legend:{ position:'bottom', show:true, fontFamily:'Inter,sans-serif', fontSize:'12px' },
            theme:{ mode }
        };
    }

    function hbarOpts(series, cats, color) {
        return {
            series,
            chart:{ height:240, type:'bar', fontFamily:'Inter,sans-serif',
                    toolbar:{show:false}, background:'transparent' },
            colors:[color],
            plotOptions:{ bar:{ borderRadius:4, horizontal:true, barHeight:'60%' } },
            dataLabels:{ enabled:true, formatter:v=>v+'%',
                         style:{ fontSize:'11px', fontFamily:'Inter,sans-serif' } },
            xaxis:{ categories:cats, max:100, labels:ax },
            yaxis:{ labels:ax },
            grid:{ borderColor:gc, strokeDashArray:4 },
            theme:{ mode }
        };
    }

    /* ── PHP data injected as JS ─────────────────────────── */
    var completionLabels = <?php echo json_encode($completionLabels); ?>;
    var completionCounts = <?php echo json_encode($completionCounts); ?>;
    var statusSeries = [
        <?php echo $totalActiveTasks; ?>,
        <?php echo $pendingTasks; ?>,
        <?php echo $completedTasks; ?>,
        <?php echo $overdueTasks; ?>
    ];

    <?php if ($showL1): ?>
    /* District - Dynamic Chart.js Charts */
    const dynamicCompletionLabels = <?php echo json_encode($dynamicCompletionLabels); ?>;
    const dynamicCompletionCounts = <?php echo json_encode($dynamicCompletionCounts); ?>;
    const dynamicPieLabels = <?php echo json_encode($dynamicPieLabels); ?>;
    const dynamicPieCounts = <?php echo json_encode($dynamicPieCounts); ?>;
    const dynamicPiePercentages = <?php echo json_encode($dynamicPiePercentages); ?>;

    const ctxLine = document.getElementById('chartjs-line-trend');
    if (ctxLine) {
        chartJsInstances.dTrend = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: dynamicCompletionLabels,
                datasets: [{
                    label: 'Completed Tasks',
                    data: dynamicCompletionCounts,
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46, 125, 50, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, labels: { color: tc } }
                },
                scales: {
                    x: { ticks: { color: tc }, grid: { display: false } },
                    y: { ticks: { color: tc, stepSize: 1 }, grid: { color: gc, borderDash: [4, 4] }, beginAtZero: true }
                }
            }
        });
    }

    const ctxPie = document.getElementById('chartjs-pie-status');
    if (ctxPie) {
        const palette = ['#3b82f6', '#2e7d32', '#f57c00', '#ef4444', '#8b5cf6', '#06b6d4', '#eab308'];
        const pieColors = dynamicPieLabels.map((_, i) => palette[i % palette.length]);

        chartJsInstances.dDonut = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: dynamicPieLabels,
                datasets: [{
                    data: dynamicPieCounts,
                    backgroundColor: pieColors,
                    borderWidth: 1,
                    borderColor: isDark ? '#1e293b' : '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: tc } },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const val = context.raw;
                                const idx = context.dataIndex;
                                const perc = dynamicPiePercentages[idx];
                                return `${label}: ${val} (${perc}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    charts.dBar = new ApexCharts(
        document.querySelector('#chart-dist-bar'),
        hbarOpts(
            [{ name:<?= json_encode($t['chart_completion_rate']) ?>, data:[
                <?php foreach($distData['talukas'] as $tRow) echo $tRow['rate'].','; ?>
            ]}],
            [<?php foreach($distData['talukas'] as $tRow) echo '"'.addslashes($tRow['taluka']).'",'; ?>],
            '#1a365d'
        )
    );
    charts.dBar.render();

    charts.dPriority = new ApexCharts(
        document.querySelector('#chart-dist-priority'),
        pieOpts(
            [<?= (int)($distPriority['Critical'] ?? 0) ?>, <?= (int)($distPriority['High'] ?? 0) ?>, <?= (int)($distPriority['Medium'] ?? 0) ?>, <?= (int)($distPriority['Low'] ?? 0) ?>],
            ['Critical', 'High', 'Medium', 'Low'],
            ['#ef4444', '#f97316', '#eab308', '#3b82f6']
        )
    );
    charts.dPriority.render();

    charts.dAgeing = new ApexCharts(
        document.querySelector('#chart-dist-ageing'),
        pieOpts(
            [<?= (int)($distAgeing['< 5 Days'] ?? 0) ?>, <?= (int)($distAgeing['5-10 Days'] ?? 0) ?>, <?= (int)($distAgeing['11-30 Days'] ?? 0) ?>, <?= (int)($distAgeing['> 30 Days'] ?? 0) ?>],
            ['< 5 Days', '5-10 Days', '11-30 Days', '> 30 Days'],
            ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        )
    );
    charts.dAgeing.render();

    charts.dRejections = new ApexCharts(
        document.querySelector('#chart-dist-rejections'),
        pieOpts(
            <?= json_encode(array_values($distRejections)) ?>,
            <?= json_encode(array_keys($distRejections)) ?>,
            ['#ef4444', '#f97316', '#3b82f6', '#10b981', '#a855f7']
        )
    );
    charts.dRejections.render();

    charts.dPerformance = new ApexCharts(
        document.querySelector('#chart-dist-performance'),
        hbarOpts(
            [{ name: 'Completion Rate %', data: [<?php foreach($distPerform as $p) echo $p['rate'].','; ?>] }],
            [<?php foreach($distPerform as $p) echo '"'.addslashes($p['name']).'",'; ?>],
            '#10b981'
        )
    );
    charts.dPerformance.render();
    <?php endif; ?>

    <?php if ($showL2): ?>
    /* Taluka */
    charts.tTrend = new ApexCharts(
        document.querySelector('#chart-tal-trend'),
        lineOpts([
            { name:<?= json_encode($t['chart_completed_tasks']) ?>, data: completionCounts }
        ], completionLabels, ['#2e7d32'])
    );
    charts.tTrend.render();

    charts.tDonut = new ApexCharts(
        document.querySelector('#chart-tal-donut'),
        pieOpts(
            statusSeries,
            ['Active', 'Pending', 'Completed', 'Overdue'],
            ['#3b82f6','#f57c00','#2e7d32','#ef4444']
        )
    );
    charts.tDonut.render();

    charts.tBar = new ApexCharts(
        document.querySelector('#chart-tal-bar'),
        {
            series:[
                { name:<?= json_encode($t['status_completed']) ?>, data:[<?php foreach($talData['villages'] as $v) echo $v['completed'].','; ?>] },
                { name:<?= json_encode($t['status_pending']) ?>,   data:[<?php foreach($talData['villages'] as $v) echo $v['pending'].','; ?>] },
                { name:<?= json_encode($t['status_overdue']) ?>,   data:[<?php foreach($talData['villages'] as $v) echo $v['overdue'].','; ?>] }
            ],
            chart:{ height:240, type:'bar', stacked:true, fontFamily:'Inter,sans-serif',
                    toolbar:{show:false}, background:'transparent' },
            colors:['#2e7d32','#f57c00','#ef4444'],
            plotOptions:{ bar:{ borderRadius:3, columnWidth:'55%' } },
            dataLabels:{ enabled:false },
            xaxis:{ categories:[<?php foreach($talData['villages'] as $v) echo '"'.addslashes($v['village']).'",'; ?>],
                    labels:{...ax, rotate:-30} },
            yaxis:{ labels:ax },
            grid:{ borderColor:gc, strokeDashArray:4 },
            legend:{ position:'top', fontFamily:'Inter,sans-serif', fontSize:'12px' },
            theme:{ mode }
        }
    );
    charts.tBar.render();

    charts.tPriority = new ApexCharts(
        document.querySelector('#chart-tal-priority'),
        pieOpts(
            [<?= (int)($talPriority['Critical'] ?? 0) ?>, <?= (int)($talPriority['High'] ?? 0) ?>, <?= (int)($talPriority['Medium'] ?? 0) ?>, <?= (int)($talPriority['Low'] ?? 0) ?>],
            ['Critical', 'High', 'Medium', 'Low'],
            ['#ef4444', '#f97316', '#eab308', '#3b82f6']
        )
    );
    charts.tPriority.render();

    charts.tAgeing = new ApexCharts(
        document.querySelector('#chart-tal-ageing'),
        pieOpts(
            [<?= (int)($talAgeing['< 5 Days'] ?? 0) ?>, <?= (int)($talAgeing['5-10 Days'] ?? 0) ?>, <?= (int)($talAgeing['11-30 Days'] ?? 0) ?>, <?= (int)($talAgeing['> 30 Days'] ?? 0) ?>],
            ['< 5 Days', '5-10 Days', '11-30 Days', '> 30 Days'],
            ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        )
    );
    charts.tAgeing.render();

    charts.tRejections = new ApexCharts(
        document.querySelector('#chart-tal-rejections'),
        pieOpts(
            <?= json_encode(array_values($talRejections)) ?>,
            <?= json_encode(array_keys($talRejections)) ?>,
            ['#ef4444', '#f97316', '#3b82f6', '#10b981', '#a855f7']
        )
    );
    charts.tRejections.render();

    charts.tPerformance = new ApexCharts(
        document.querySelector('#chart-tal-performance'),
        hbarOpts(
            [{ name: 'Completion Rate %', data: [<?php foreach($talPerform as $p) echo $p['rate'].','; ?>] }],
            [<?php foreach($talPerform as $p) echo '"'.addslashes($p['name']).'",'; ?>],
            '#10b981'
        )
    );
    charts.tPerformance.render();
    <?php endif; ?>

    /* Village */
    charts.vTrend = new ApexCharts(
        document.querySelector('#chart-vil-trend'),
        lineOpts([
            { name:<?= json_encode($t['chart_completed_tasks']) ?>, data: completionCounts }
        ], completionLabels, ['#2e7d32'])
    );
    charts.vTrend.render();

    charts.vDonut = new ApexCharts(
        document.querySelector('#chart-vil-donut'),
        pieOpts(
            statusSeries,
            ['Active', 'Pending', 'Completed', 'Overdue'],
            ['#3b82f6','#f57c00','#2e7d32','#ef4444']
        )
    );
    charts.vDonut.render();

    charts.vPriority = new ApexCharts(
        document.querySelector('#chart-vil-priority'),
        pieOpts(
            [<?= (int)($vilPriority['Critical'] ?? 0) ?>, <?= (int)($vilPriority['High'] ?? 0) ?>, <?= (int)($vilPriority['Medium'] ?? 0) ?>, <?= (int)($vilPriority['Low'] ?? 0) ?>],
            ['Critical', 'High', 'Medium', 'Low'],
            ['#ef4444', '#f97316', '#eab308', '#3b82f6']
        )
    );
    charts.vPriority.render();

    charts.vAgeing = new ApexCharts(
        document.querySelector('#chart-vil-ageing'),
        pieOpts(
            [<?= (int)($vilAgeing['< 5 Days'] ?? 0) ?>, <?= (int)($vilAgeing['5-10 Days'] ?? 0) ?>, <?= (int)($vilAgeing['11-30 Days'] ?? 0) ?>, <?= (int)($vilAgeing['> 30 Days'] ?? 0) ?>],
            ['< 5 Days', '5-10 Days', '11-30 Days', '> 30 Days'],
            ['#10b981', '#3b82f6', '#f59e0b', '#ef4444']
        )
    );
    charts.vAgeing.render();
} catch(chartErr) {
    console.error('ApexCharts render error:', chartErr);
    document.querySelectorAll('[id^="chart-"]').forEach(el => {
        if (!el.querySelector('svg')) {
            el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;"><span>⚠ Chart rendering failed</span></div>';
        }
    });
}
}

/* ── Export Dashboard Data ─────────────────────────────── */
function exportDashboardData() {
    window.location.href = 'api/export_data.php?type=csv&scope=<?= $showL1 ? "district" : ($showL2 ? "taluka" : "village") ?>&lang=<?= $lang ?>';
}
</script>

<!-- Notification System (isolated script block) -->
<script>
// Notification Bell Logic
const notificationBtn = document.getElementById('notificationBtn');
const notificationDropdown = document.getElementById('notificationDropdown');
const unreadCountBadge = document.getElementById('unreadCountBadge');
const notificationList = document.getElementById('notificationList');

notificationBtn.addEventListener('click', () => {
    notificationDropdown.classList.toggle('hidden');
});

document.addEventListener('click', (e) => {
    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
        notificationDropdown.classList.add('hidden');
    }
});

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
                            if (isUnread) markAsRead(n.id);
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
                <select name="reason" required class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-red-500">
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
</body>
</html>
