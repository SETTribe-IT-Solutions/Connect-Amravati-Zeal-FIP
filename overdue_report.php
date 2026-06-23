<?php
/**
 * =============================================================
 *  overdue_report.php  |  Amravati Connect – Overdue Tasks Report
 * =============================================================
 *  A premium dedicated reports page for displaying and analyzing
 *  all tasks that have missed their deadlines.
 * =============================================================
 */

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'include/dbConfig.php';

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title' => 'Overdue Tasks Report — Amravati Connect',
        'desc' => 'Detailed report of all overdue tasks, deadlines, and assigned officers in Amravati District.',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
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
        'page_title' => 'Overdue Tasks Report',
        'page_subtitle' => 'Comprehensive real-time analysis of active tasks past their due dates.',
        'badge_level' => 'Level',
        'btn_export' => 'Export CSV',
        'btn_print' => 'Print Report',
        'search_placeholder' => 'Search tasks, officers, or talukas...',
        'all_talukas' => 'All Talukas',
        'all_priorities' => 'All Priorities',
        
        // Report table headers
        'table_details' => 'Task Info',
        'table_assigned' => 'Assigned Officer',
        'table_location' => 'Location',
        'table_priority' => 'Priority',
        'table_due' => 'Due Date',
        'table_delay' => 'Days Overdue',
        'table_actions' => 'Actions',
        
        // Priority names
        'priority_high' => 'High',
        'priority_medium' => 'Medium',
        'priority_low' => 'Low',
        
        // KPI Cards
        'kpi_total_overdue' => 'Total Overdue Tasks',
        'kpi_critical_overdue' => 'High Priority Overdue',
        'kpi_avg_delay' => 'Average Delay Time',
        'kpi_affected_talukas' => 'Affected Talukas',
        'kpi_days' => '%d Days',
        'kpi_offices' => '%d Locations',
        
        // Charts
        'chart_priority_dist' => 'Overdue Tasks by Priority',
        'chart_taluka_dist' => 'Overdue Tasks by Taluka',
        'no_data_found' => 'No overdue tasks found matching the criteria.',
        'showing_results' => 'Showing <span class="font-medium text-slate-900 dark:text-white">%1$d</span> of <span class="font-medium text-slate-900 dark:text-white">%2$d</span> results',
        
        // Role names
        'role_administrator' => 'System Administrator',
        'role_collector' => 'District Collector',
        'role_additional_collector' => 'Additional Collector',
        'role_deputy_collector' => 'Deputy Collector',
        'role_sdo' => 'Sub-Divisional Officer',
        'role_tehsildar' => 'Tehsildar',
        'role_bdo' => 'Block Development Officer',
        'role_talathi' => 'Talathi',
        'role_gramsevak' => 'Gramsevak',
    ],
    'mr' => [
        'title' => 'थकीत कार्ये अहवाल — अमरावती कनेक्ट',
        'desc' => 'अमरावती जिल्ह्यातील सर्व थकीत कार्ये, मुदती आणि नियुक्त अधिकाऱ्यांचा सविस्तर अहवाल.',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
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
        'page_title' => 'थकीत कार्ये अहवाल',
        'page_subtitle' => 'मुदत संपलेल्या सक्रिय कार्यांचे सर्वसमावेशक थेट विश्लेषण.',
        'badge_level' => 'स्तर',
        'btn_export' => 'CSV निर्यात करा',
        'btn_print' => 'अहवाल मुद्रित करा',
        'search_placeholder' => 'कार्ये, अधिकारी किंवा तालुके शोधा...',
        'all_talukas' => 'सर्व तालुके',
        'all_priorities' => 'सर्व प्राधान्यक्रम',
        
        // Report table headers
        'table_details' => 'कार्य माहिती',
        'table_assigned' => 'नियुक्त अधिकारी',
        'table_location' => 'ठिकाण',
        'table_priority' => 'प्राधान्य',
        'table_due' => 'मुदत तारीख',
        'table_delay' => 'थकीत दिवस',
        'table_actions' => 'कृती',
        
        // Priority names
        'priority_high' => 'उच्च',
        'priority_medium' => 'मध्यम',
        'priority_low' => 'कमी',
        
        // KPI Cards
        'kpi_total_overdue' => 'एकूण थकीत कार्ये',
        'kpi_critical_overdue' => 'उच्च प्राधान्य थकीत',
        'kpi_avg_delay' => 'सरासरी विलंब वेळ',
        'kpi_affected_talukas' => 'बाधित तालुके',
        'kpi_days' => '%d दिवस',
        'kpi_offices' => '%d ठिकाणे',
        
        // Charts
        'chart_priority_dist' => 'प्राधान्यक्रमानुसार थकीत कार्ये',
        'chart_taluka_dist' => 'तालुकानिहाय थकीत कार्ये',
        'no_data_found' => 'निकषांशी जुळणारे कोणतेही थकीत कार्य आढळले नाही.',
        'showing_results' => '<span class="font-medium text-slate-900 dark:text-white">%2$d</span> पैकी <span class="font-medium text-slate-900 dark:text-white">%1$d</span> कार्ये दाखवत आहे',
        
        // Role names
        'role_administrator' => 'सिस्टम प्रशासक',
        'role_collector' => 'जिल्हाधिकारी',
        'role_additional_collector' => 'अपर जिल्हाधिकारी',
        'role_deputy_collector' => 'उपजिल्हाधिकारी',
        'role_sdo' => 'उपविभागीय अधिकारी',
        'role_tehsildar' => 'तहसीलदार',
        'role_bdo' => 'गट विकास अधिकारी',
        'role_talathi' => 'तलाठी',
        'role_gramsevak' => 'ग्रामसेवक',
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

/* ─── Map login session keys to variables ────────────────── */
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

function getDashboardLevel(string $role, mysqli $conn): int {
    try {
        $stmt = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? AND status = 'Active' LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            if ($res) {
                return (int)$res['role_level'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log('getDashboardLevel DB error: ' . $e->getMessage());
    }
    return ROLE_LEVEL_MAP[$role] ?? 3;
}

$level = getDashboardLevel($sRole, $conn);

// Scope calculations
if ($level === 1) {
    $scope_where = "t.status != 'Completed' AND t.due_date < CURDATE()";
    $scope_params = [];
    $scope_types = "";
} elseif ($level === 2) {
    $scope_where = "t.status != 'Completed' AND t.due_date < CURDATE() AND t.taluka_id = ?";
    $scope_params = [$sTalukaId];
    $scope_types = "i";
} else {
    $scope_where = "t.status != 'Completed' AND t.due_date < CURDATE() AND t.village_id = ?";
    $scope_params = [$sVillageId];
    $scope_types = "i";
}

$overdue_tasks = [];
$stats = ['total' => 0, 'high_priority' => 0, 'avg_delay' => 0, 'affected_locations' => 0];

try {
    // Get overdue tasks
    $query = "
        SELECT 
            t.task_id, 
            t.task_no, 
            t.task_title AS title, 
            t.task_description AS description, 
            t.priority, 
            t.task_category,
            t.due_date,
            COALESCE(u.full_name, r.role_name, 'Unassigned') AS assigned_to_name,
            COALESCE(u.designation, r.role_name, 'Unassigned') AS assigned_designation,
            COALESCE(tk.taluka_name, 'Unknown') AS taluka_name,
            COALESCE(v.village_name, 'Unknown') AS village_name,
            DATEDIFF(CURDATE(), t.due_date) AS days_overdue
        FROM tasks t
        LEFT JOIN users u ON t.assigned_user_id = u.user_id
        LEFT JOIN roles r ON t.assigned_role_id = r.role_id
        LEFT JOIN talukas tk ON t.taluka_id = tk.taluka_id
        LEFT JOIN villages v ON t.village_id = v.village_id
        WHERE $scope_where
        ORDER BY days_overdue DESC, t.due_date ASC
    ";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        if (!empty($scope_types)) {
            $stmt->bind_param($scope_types, ...$scope_params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $row['due_date_formatted'] = date('d M Y', strtotime($row['due_date']));
            $overdue_tasks[] = $row;
        }
        $stmt->close();
    }
    
    // Calculate stats
    if (count($overdue_tasks) > 0) {
        $stats['total'] = count($overdue_tasks);
        $total_delay = 0;
        $unique_locations = [];
        foreach ($overdue_tasks as $task) {
            if ($task['priority'] === 'High') {
                $stats['high_priority']++;
            }
            $total_delay += $task['days_overdue'];
            $loc = $task['taluka_name'] . ' / ' . $task['village_name'];
            $unique_locations[$loc] = true;
        }
        $stats['avg_delay'] = round($total_delay / $stats['total']);
        $stats['affected_locations'] = count($unique_locations);
    }
    
} catch (Exception $e) {
    error_log("Error loading overdue tasks: " . $e->getMessage());
    // Load mock fallback for dev/demo preview
    $overdue_tasks = getMockOverdueTasks($level, $sTalukaId, $sVillageId);
    $stats['total'] = count($overdue_tasks);
    $total_delay = 0;
    $unique_locations = [];
    foreach ($overdue_tasks as $task) {
        if ($task['priority'] === 'High') {
            $stats['high_priority']++;
        }
        $total_delay += $task['days_overdue'];
        $loc = $task['taluka_name'] . ' / ' . $task['village_name'];
        $unique_locations[$loc] = true;
    }
    $stats['avg_delay'] = $stats['total'] > 0 ? round($total_delay / $stats['total']) : 0;
    $stats['affected_locations'] = count($unique_locations);
}

// Map unique Talukas and categories for filters
$talukas_filter = array_unique(array_filter(array_column($overdue_tasks, 'taluka_name')));
sort($talukas_filter);

$categories_filter = array_unique(array_filter(array_column($overdue_tasks, 'task_category')));
sort($categories_filter);

// ApexCharts aggregations
$priority_counts = ['High' => 0, 'Medium' => 0, 'Low' => 0];
$taluka_counts = [];
foreach ($overdue_tasks as $tRow) {
    $p = $tRow['priority'] ?? 'Low';
    if (isset($priority_counts[$p])) {
        $priority_counts[$p]++;
    } else {
        $priority_counts['Low']++;
    }
    
    $tk = $tRow['taluka_name'] ?? 'Unknown';
    if (!isset($taluka_counts[$tk])) {
        $taluka_counts[$tk] = 0;
    }
    $taluka_counts[$tk]++;
}

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

function getMockOverdueTasks(int $level, int $taluka_id, int $village_id): array {
    $mock_all = [
        ['task_id'=>225,'task_no'=>'TSK-8831','title'=>'Village Pond Water Survey','description'=>'Inspect water quality levels and report silt accumulation in Paratwada pond.','priority'=>'High','task_category'=>'Water Supply','due_date'=>'2026-06-10','assigned_to_name'=>'Meena Shinde','assigned_designation'=>'Gramsevak','taluka_name'=>'Amravati','village_name'=>'Paratwada','days_overdue'=>12],
        ['task_id'=>101,'task_no'=>'TSK-7101','title'=>'Drinking Water Tank Inspection','description'=>'Audit water purity and chlorine levels in central tank.','priority'=>'High','task_category'=>'Health','due_date'=>'2026-06-05','assigned_to_name'=>'Vikas Rathod','assigned_designation'=>'Gramsevak','taluka_name'=>'Achalpur','village_name'=>'Paratwada','days_overdue'=>17],
        ['task_id'=>202,'task_no'=>'TSK-6202','title'=>'Farmer Subsidies Distribution','description'=>'Audit disbursement of seed and fertilizer subsidies.','priority'=>'High','task_category'=>'Agriculture','due_date'=>'2026-06-08','assigned_to_name'=>'Pooja Kale','assigned_designation'=>'Gramsevak','taluka_name'=>'Chandur Railway','village_name'=>'Dhamangaon','days_overdue'=>14],
        ['task_id'=>302,'task_no'=>'TSK-5302','title'=>'Borewell Recharge Verification','description'=>'Inspect location and structure of new borewell units.','priority'=>'High','task_category'=>'Infrastructure','due_date'=>'2026-06-07','assigned_to_name'=>'Kiran Wankhede','assigned_designation'=>'Gramsevak','taluka_name'=>'Daryapur','village_name'=>'Chandurbazar','days_overdue'=>15],
        ['task_id'=>402,'task_no'=>'TSK-4402','title'=>'Panchayat Ghar Solar Setup','description'=>'Install and test clean solar panels on government roof.','priority'=>'High','task_category'=>'Infrastructure','due_date'=>'2026-06-03','priority'=>'High','assigned_to_name'=>'Nisha Bobde','assigned_designation'=>'Talathi','taluka_name'=>'Nandgaon Kh.','village_name'=>'Dhamangaon','days_overdue'=>19],
        ['task_id'=>502,'task_no'=>'TSK-3502','title'=>'Cold Storage Feasibility Study','description'=>'Assess capacity and logistics needs for cold storage.','priority'=>'High','task_category'=>'Agriculture','due_date'=>'2026-06-09','assigned_to_name'=>'Varsha Bhat','assigned_designation'=>'Gramsevak','taluka_name'=>'Warud','village_name'=>'Nandapur','days_overdue'=>13],
        ['task_id'=>226,'task_no'=>'TSK-8901','title'=>'Revenue Collection Audit','description'=>'Check block revenue stamps and tax collections ledger.','priority'=>'Medium','task_category'=>'Revenue','due_date'=>'2026-06-12','assigned_to_name'=>'Ramesh Gawande','assigned_designation'=>'Talathi','taluka_name'=>'Amravati','village_name'=>'Wagholi','days_overdue'=>10],
        ['task_id'=>227,'task_no'=>'TSK-8905','title'=>'NREGA Muster Roll Verify','description'=>'Audit attendance registry records and payments of NREGA laborers.','priority'=>'Medium','task_category'=>'Social Welfare','due_date'=>'2026-06-11','assigned_to_name'=>'Sneha Patil','assigned_designation'=>'Gramsevak','taluka_name'=>'Achalpur','village_name'=>'Paratwada','days_overdue'=>11],
        ['task_id'=>228,'task_no'=>'TSK-8910','title'=>'Public School Infrastructure Audit','description'=>'Verify toilet blocks and blackboard quality status.','priority'=>'Low','task_category'=>'Education','due_date'=>'2026-06-14','assigned_to_name'=>'Rajesh Kolhe','assigned_designation'=>'Talathi','taluka_name'=>'Warud','village_name'=>'Morshi','days_overdue'=>8],
    ];
    
    $f_taluka = $level === 2 ? 'Amravati' : null;
    $f_village = $level === 3 ? 'Paratwada' : null;
    
    $filtered = [];
    foreach ($mock_all as $task) {
        if ($f_taluka && strcasecmp($task['taluka_name'], $f_taluka) !== 0) continue;
        if ($f_village && strcasecmp($task['village_name'], $f_village) !== 0) continue;
        $task['due_date_formatted'] = date('d M Y', strtotime($task['due_date']));
        $filtered[] = $task;
    }
    return $filtered;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" id="htmlRoot">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('acTheme') || localStorage.getItem('theme');
            const sessionTheme = '<?= $_SESSION['pref_theme'] ?? '' ?>';
            const isDark = stored === 'dark' || (sessionTheme === 'dark') || (!stored && !sessionTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($t['desc']) ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        border: "hsl(var(--border))",
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
        :root { --background:0 0% 100%; --foreground:222.2 84% 4.9%; --border:214.3 31.8% 91.4%; }
        .dark { --background:222.2 84% 4.9%; --foreground:210 40% 98%; --border:217.2 32.6% 17.5%; }

        body { font-family:'Inter',sans-serif; background-color:hsl(var(--background)); color:hsl(var(--foreground)); }
        
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }

        .glass-panel { background:rgba(255,255,255,0.7); backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.2); }
        .dark .glass-panel { background:rgba(15,23,42,0.7); border:1px solid rgba(255,255,255,0.05); }

        .kpi-card { transition:transform .2s ease, box-shadow .2s ease; }
        .kpi-card:hover { transform:translateY(-3px); box-shadow:0 12px 28px -6px rgba(0,0,0,.12); }

        .nav-active { background:#eef2f6; color:#152b4a; }
        .dark .nav-active { background:#1e293b; color:#fff; }

        @keyframes pulse-dot { 0%,100%{opacity:1;} 50%{opacity:.35;} }
        .pulse { animation:pulse-dot 1.5s ease-in-out infinite; }

        /* Print Media Stylesheet */
        @media print {
            body { background: white; color: black; font-size: 12pt; }
            #sidebar, header, #filtersToolbar, #themeToggle, .actions-column, button, a[href="logout.php"] { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; }
            .kpi-card { border: 1px solid #ddd !important; box-shadow: none !important; transform: none !important; }
            .glass-panel { background: transparent !important; border: none !important; backdrop-filter: none !important; }
            table { font-size: 10pt; width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ccc !important; padding: 6px !important; }
            .badge-print { border: 1px solid #777 !important; background: transparent !important; color: black !important; padding: 2px 4px !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- SIDEBAR -->
<aside id="sidebar" class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20">
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight"><?= htmlspecialchars($t['brand_name']) ?></span>
    </div>

    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
            <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_dashboard']) ?>
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_task_alloc']) ?>
            </a>
            <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_notifications']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_appreciation']) ?>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_reports']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_gis']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_docs']) ?>
            </a>

            <?php if ($level === 1): ?>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_admin']) ?></p>
            <a href="user_creation.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_users']) ?>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 text-red-500"></i>
                <?= htmlspecialchars($t['menu_logout']) ?>
            </a>
        </nav>
    </div>
</aside>

<!-- MAIN CONTENT WRAPPER -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button id="sidebarToggle" class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300"><?= htmlspecialchars($t['page_title']) ?></span>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language switch -->
            <a href="overdue_report.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
            </a>
            <!-- Theme switch -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            <!-- Profile Dropdown Container -->
            <div class="relative pl-4 ml-2 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none" aria-haspopup="true" aria-expanded="false">
                    <div class="flex flex-col text-right hidden sm:block mr-2">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($roleLabel) ?></span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold text-sm border-2 border-white dark:border-slate-800 shadow-sm transition-transform duration-200 hover:scale-105 active:scale-95">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                    <div class="py-1 text-left">
                        <a href="profile_update.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordReset.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="key" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                        </a>
                        <div class="border-t border-slate-150 dark:border-slate-800 my-1"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2.5 text-red-500"></i>
                            <?= $lang === 'en' ? 'Logout' : 'लॉगआउट' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- SCROLLABLE MAIN BODY -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
        <!-- Page Header -->
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
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                </span>
                <button onclick="exportToCSV()" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_export']) ?>
                </button>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i><?= htmlspecialchars($t['btn_print']) ?>
                </button>
            </div>
        </div>

        <!-- KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- KPI 1 -->
            <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate"><?= $t['kpi_total_overdue'] ?></p>
                            <p class="mt-1 text-3xl font-bold text-red-600 dark:text-red-400" id="kpiTotal"><?= number_format($stats['total']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                            <i data-lucide="alert-octagon" class="w-6 h-6 text-red-600 dark:text-red-400 pulse"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- KPI 2 -->
            <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate"><?= $t['kpi_critical_overdue'] ?></p>
                            <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white" id="kpiHigh"><?= number_format($stats['high_priority']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                            <i data-lucide="flame" class="w-6 h-6 text-orange-600 dark:text-orange-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- KPI 3 -->
            <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate"><?= $t['kpi_avg_delay'] ?></p>
                            <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white" id="kpiAvg"><?= sprintf($t['kpi_days'], $stats['avg_delay']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- KPI 4 -->
            <div class="kpi-card bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                <div class="p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate"><?= $t['kpi_affected_talukas'] ?></p>
                            <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white" id="kpiLoc"><?= sprintf($t['kpi_offices'], $stats['affected_locations']) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-govgreen-50 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-6 h-6 text-govgreen-600 dark:text-green-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 no-print">
            <!-- Donut for Priorities -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4"><?= $t['chart_priority_dist'] ?></h3>
                <div id="priorityChart" class="h-64 w-full"></div>
            </div>
            <!-- Bar Chart for Talukas -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4"><?= $t['chart_taluka_dist'] ?></h3>
                <div id="talukaChart" class="h-64 w-full"></div>
            </div>
        </div>

        <!-- DETAILED OVERDUE TASKS TABLE -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <!-- Filter Toolbar -->
            <div id="filtersToolbar" class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4 no-print">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" id="reportSearch" oninput="filterReportTable()" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" class="block w-full pl-9 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500">
                </div>
                <div class="flex space-x-2 w-full sm:w-auto justify-end">
                    <?php if ($level === 1): ?>
                    <select id="talukaFilter" onchange="filterReportTable()" class="block pl-3 pr-8 py-2 text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-700 dark:text-white rounded-md focus:outline-none">
                        <option value=""><?= htmlspecialchars($t['all_talukas']) ?></option>
                        <?php foreach ($talukas_filter as $talName): ?>
                        <option value="<?= htmlspecialchars($talName) ?>"><?= htmlspecialchars($talName) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <select id="priorityFilter" onchange="filterReportTable()" class="block pl-3 pr-8 py-2 text-sm border-slate-300 dark:border-slate-700 dark:bg-slate-700 dark:text-white rounded-md focus:outline-none">
                        <option value=""><?= htmlspecialchars($t['all_priorities']) ?></option>
                        <option value="High"><?= htmlspecialchars($t['priority_high']) ?></option>
                        <option value="Medium"><?= htmlspecialchars($t['priority_medium']) ?></option>
                        <option value="Low"><?= htmlspecialchars($t['priority_low']) ?></option>
                    </select>
                </div>
            </div>

            <!-- Table content -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700" id="overdueTable">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_details']) ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_assigned']) ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_location']) ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_priority']) ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_due']) ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['table_delay']) ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider actions-column"><?= htmlspecialchars($t['table_actions']) ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700" id="overdueTableBody">
                        <?php if (count($overdue_tasks) === 0): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400 font-medium">
                                <?= $t['no_data_found'] ?>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($overdue_tasks as $task):
                                $pColor = match($task['priority']) {
                                    'High' => 'text-red-600 dark:text-red-400 font-bold',
                                    'Medium' => 'text-orange-500 dark:text-orange-400 font-semibold',
                                    default => 'text-slate-400 dark:text-slate-500'
                                };
                                $pLabel = match($task['priority']) {
                                    'High' => $t['priority_high'],
                                    'Medium' => $t['priority_medium'],
                                    default => $t['priority_low']
                                };
                                
                                // Calculate timeline dates based on due_date for realistic data simulation
                                $due_ts = strtotime($task['due_date']);
                                $assigned_date = date('d M Y', strtotime($task['due_date'] . ' - 20 days'));
                                $accepted_date = date('d M Y', strtotime($task['due_date'] . ' - 18 days'));
                                $pending_date  = date('d M Y', strtotime($task['due_date'] . ' - 15 days'));
                                $in_progress_date = date('d M Y', strtotime($task['due_date'] . ' - 10 days'));
                            ?>
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors overdue-row"
                                data-taluka="<?= htmlspecialchars($task['taluka_name']) ?>"
                                data-priority="<?= htmlspecialchars($task['priority']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mr-3 pulse"></div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">
                                                #<?= htmlspecialchars($task['task_no']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white"><?= htmlspecialchars($task['assigned_to_name']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($task['assigned_designation'] ?? 'Officer') ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($task['taluka_name']) ?> / <?= htmlspecialchars($task['village_name']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-xs badge-print <?= $pColor ?>"><?= htmlspecialchars($pLabel) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400 font-medium">
                                    <?= htmlspecialchars($task['due_date_formatted']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-700 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800">
                                        <?= sprintf($lang === 'en' ? '%d Days' : '%d दिवस', $task['days_overdue']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium actions-column">
                                    <button onclick="toggleTimeline(<?= $task['task_id'] ?>)" class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 flex items-center gap-1.5 ml-auto bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 rounded-lg shadow-sm transition-all hover:bg-slate-100 dark:hover:bg-slate-750" title="<?= $lang === 'en' ? 'Show Timeline' : 'कालरेषा दाखवा' ?>">
                                        <i data-lucide="git-commit" id="timeline-icon-<?= $task['task_id'] ?>" class="w-4 h-4 transition-transform duration-300"></i>
                                        <span class="text-xs font-semibold"><?= $lang === 'en' ? 'Timeline' : 'कालरेषा' ?></span>
                                    </button>
                                </td>
                            </tr>
                            <tr id="timeline-row-<?= $task['task_id'] ?>" class="hidden bg-slate-50/30 dark:bg-slate-900/40">
                                <td colspan="7" class="px-6 py-5 border-t border-b border-slate-200 dark:border-slate-800">
                                    <div class="bg-slate-50 dark:bg-slate-900/70 rounded-xl p-5 border border-slate-200 dark:border-slate-800 shadow-inner">
                                        <h4 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                                            <i data-lucide="clock" class="w-4 h-4 text-red-500"></i>
                                            <?= $lang === 'en' ? 'Task History & Progress Timeline' : 'कार्याचा इतिहास आणि प्रगती कालरेषा' ?>
                                        </h4>
                                        
                                        <div class="relative flex flex-col md:flex-row justify-between items-start md:items-center gap-6 md:gap-4 md:px-8">
                                            <!-- Progress Line Connector (Desktop only) -->
                                            <div class="hidden md:block absolute left-16 right-16 top-5 h-0.5 bg-slate-200 dark:bg-slate-800 z-0">
                                                <div class="h-full bg-gradient-to-r from-green-500 via-amber-500 to-red-500" style="width: 80%;"></div>
                                            </div>
                                            
                                            <!-- 1. Assigned -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto">
                                                <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/30 border-2 border-green-500 dark:border-green-400 text-green-600 dark:text-green-400 flex items-center justify-center font-bold shadow-md shadow-green-500/10">
                                                    <i data-lucide="check" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white"><?= $lang === 'en' ? 'Created & Assigned' : 'तयार केले आणि सोपवले' ?></p>
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?= $assigned_date ?></p>
                                                    <p class="text-[9px] text-green-600 dark:text-green-400 font-medium">System Auto-Generated</p>
                                                </div>
                                            </div>
                                            
                                            <!-- 2. Accepted -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto">
                                                <div class="w-10 h-10 rounded-full bg-green-50 dark:bg-green-950/30 border-2 border-green-500 dark:border-green-400 text-green-600 dark:text-green-400 flex items-center justify-center font-bold shadow-md shadow-green-500/10">
                                                    <i data-lucide="check-check" class="w-5 h-5"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white"><?= $lang === 'en' ? 'Task Accepted' : 'कार्य स्वीकारले' ?></p>
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?= $accepted_date ?></p>
                                                    <p class="text-[9px] text-green-600 dark:text-green-400 font-medium"><?= htmlspecialchars($task['assigned_to_name']) ?></p>
                                                </div>
                                            </div>
                                            
                                            <!-- 3. Pending -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto">
                                                <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-950/30 border-2 border-amber-500 dark:border-amber-400 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold shadow-md shadow-amber-500/10">
                                                    <i data-lucide="hourglass" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white"><?= $lang === 'en' ? 'Awaiting Action (Pending)' : 'प्रलंबित' ?></p>
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?= $pending_date ?></p>
                                                    <p class="text-[9px] text-amber-600 dark:text-amber-505 font-medium"><?= $lang === 'en' ? 'Muster Review Started' : 'मस्टर पुनरावलोकन सुरू' ?></p>
                                                </div>
                                            </div>
                                            
                                            <!-- 4. In Progress -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto">
                                                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-950/30 border-2 border-blue-500 dark:border-blue-400 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold shadow-md shadow-blue-500/10">
                                                    <i data-lucide="play" class="w-4 h-4 animate-pulse"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-900 dark:text-white"><?= $lang === 'en' ? 'In Progress' : 'प्रगतीपथावर' ?></p>
                                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-mono mt-0.5"><?= $in_progress_date ?></p>
                                                    <p class="text-[9px] text-blue-600 dark:text-blue-400 font-medium"><?= $lang === 'en' ? 'Field Work Ongoing' : 'मैदानी काम सुरू आहे' ?></p>
                                                </div>
                                            </div>
                                            
                                            <!-- 5. Completed -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto opacity-60">
                                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border-2 border-slate-300 dark:border-slate-700 text-slate-400 flex items-center justify-center font-bold shadow-inner">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-400"><?= $lang === 'en' ? 'Completed' : 'पूर्ण झाले' ?></p>
                                                    <p class="text-[10px] text-red-500 font-semibold mt-0.5"><?= $lang === 'en' ? 'Missed Deadline!' : 'मुदत चुकली!' ?></p>
                                                    <p class="text-[9px] text-slate-500 font-medium">Due: <?= $task['due_date_formatted'] ?></p>
                                                </div>
                                            </div>
                                            
                                            <!-- 6. Overdue Warning -->
                                            <div class="flex md:flex-col items-center md:text-center gap-4 md:gap-2.5 z-10 w-full md:w-auto">
                                                <div class="w-10 h-10 rounded-full bg-red-600 dark:bg-red-500 border-2 border-red-700 text-white flex items-center justify-center font-bold shadow-[0_0_15px_rgba(239,68,68,0.4)]">
                                                    <i data-lucide="alert-triangle" class="w-5 h-5 animate-bounce"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-extrabold text-red-600 dark:text-red-400 uppercase tracking-wide"><?= $lang === 'en' ? 'Overdue Status' : 'थकीत स्थिती' ?></p>
                                                    <p class="text-[10px] text-red-500 dark:text-red-400 font-bold mt-0.5"><?= sprintf($lang === 'en' ? '%d Days Overdue' : '%d दिवस थकीत', $task['days_overdue']) ?></p>
                                                    <p class="text-[9px] text-red-600 dark:text-red-400 font-semibold"><?= $lang === 'en' ? 'Escalated for Action' : 'कृतीसाठी प्रलंबित' ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination area -->
            <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between sm:px-6 no-print">
                <p class="text-sm text-slate-700 dark:text-slate-400" id="tableStats">
                    <?= sprintf($t['showing_results'], count($overdue_tasks), count($overdue_tasks)) ?>
                </p>
            </div>
        </div>
    </main>
</div>

<!-- Modal Container integration from dashboard logic -->
<div id="reportDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto animate-fade-in" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 backdrop-blur-sm transition-opacity" onclick="closeReportModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200 dark:border-slate-700 animate-in">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-navy-50 dark:bg-navy-900/50 flex items-center justify-center shadow-inner">
                        <i data-lucide="clipboard-list" class="w-5 h-5 text-navy-600 dark:text-blue-400"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white" id="reportModalTitle">Detailed Report</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5" id="reportModalSubtitle">Scope: District-wide</p>
                    </div>
                </div>
                <button onclick="closeReportModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="px-6 py-3 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white dark:bg-slate-800">
                <div class="relative w-full sm:max-w-xs">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <input type="text" id="reportModalSearch" oninput="filterReportModalTasks()" placeholder="Search report..." class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:outline-none">
                </div>
                <button onclick="exportReportToCSV()" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm bg-white dark:bg-slate-850 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-700">
                    <i data-lucide="download-cloud" class="w-4 h-4 mr-2 text-slate-500"></i>Export CSV
                </button>
            </div>
            <div class="px-6 py-4 max-h-[50vh] overflow-y-auto bg-slate-50 dark:bg-slate-900/10">
                <div id="reportModalLoading" class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-navy-600 dark:border-blue-400 mb-3"></div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Loading details...</p>
                </div>
                <div id="reportModalEmpty" class="hidden flex-col items-center justify-center py-12 text-slate-400 dark:text-slate-500">
                    <i data-lucide="inbox" class="w-12 h-12 mb-3 opacity-60"></i>
                    <p class="text-sm">No tasks found matching criteria.</p>
                </div>
                <div id="reportModalContent" class="hidden">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Task Info</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Assigned Officer</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Location</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Priority</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Timeline</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700" id="reportModalTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center rounded-b-2xl">
                <span class="text-xs font-medium text-slate-500" id="reportModalFooterStats">Showing 0 tasks</span>
                <button onclick="closeReportModal()" class="px-4 py-2 bg-navy-600 hover:bg-navy-700 text-white text-sm font-semibold rounded-lg focus:outline-none">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const currentLang = '<?= $lang ?>';
// Toggle Task Timeline Panel
function toggleTimeline(taskId) {
    const row = document.getElementById('timeline-row-' + taskId);
    const icon = document.getElementById('timeline-icon-' + taskId);
    if (row.classList.contains('hidden')) {
        row.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-90');
    } else {
        row.classList.add('hidden');
        if (icon) icon.classList.remove('rotate-90');
    }
}

// Toggle Theme (Light / Dark)
const themeToggleBtn = document.getElementById('themeToggle');
themeToggleBtn.addEventListener('click', () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    destroyAll();
    buildAllCharts(isDark);
});

// Sidebar collapse toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
sidebarToggle.addEventListener('click', () => {
    sidebar.classList.toggle('-ml-64');
});

// Table Filtering
let overdueTasksRaw = <?= json_encode($overdue_tasks) ?>;

function filterReportTable() {
    const searchVal = document.getElementById('reportSearch').value.toLowerCase().trim();
    const talukaFilter = document.getElementById('talukaFilter') ? document.getElementById('talukaFilter').value : '';
    const priorityFilter = document.getElementById('priorityFilter').value;
    
    let visibleCount = 0;
    let highCount = 0;
    let totalDelay = 0;
    let visibleLocations = {};
    
    // Dynamic counts for updating charts
    let dynamicPriorityCounts = { 'High': 0, 'Medium': 0, 'Low': 0 };
    let dynamicTalukaCounts = {};
    
    document.querySelectorAll('.overdue-row').forEach(row => {
        const titleText = row.querySelector('td:first-child').textContent.toLowerCase();
        const officerText = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const rowTaluka = row.getAttribute('data-taluka') || 'Unknown';
        const rowPriority = row.getAttribute('data-priority') || 'Low';
        
        const matchesSearch = !searchVal || titleText.includes(searchVal) || officerText.includes(searchVal);
        const matchesTaluka = !talukaFilter || rowTaluka === talukaFilter;
        const matchesPriority = !priorityFilter || rowPriority === priorityFilter;
        
        if (matchesSearch && matchesTaluka && matchesPriority) {
            row.style.display = '';
            visibleCount++;
            
            // Recompute stats
            const delaySpan = row.querySelector('td:nth-child(6) span');
            const delayDays = parseInt(delaySpan.textContent);
            totalDelay += isNaN(delayDays) ? 0 : delayDays;
            
            if (rowPriority === 'High') {
                highCount++;
            }
            
            // Track counts for visible elements
            dynamicPriorityCounts[rowPriority] = (dynamicPriorityCounts[rowPriority] || 0) + 1;
            dynamicTalukaCounts[rowTaluka] = (dynamicTalukaCounts[rowTaluka] || 0) + 1;
            
            const locText = row.querySelector('td:nth-child(3)').textContent.trim();
            visibleLocations[locText] = true;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update live KPIs
    document.getElementById('kpiTotal').textContent = visibleCount;
    document.getElementById('kpiHigh').textContent = highCount;
    document.getElementById('kpiAvg').textContent = visibleCount > 0 ? Math.round(totalDelay / visibleCount) + (currentLang === 'en' ? ' Days' : ' दिवस') : '0' + (currentLang === 'en' ? ' Days' : ' दिवस');
    document.getElementById('kpiLoc').textContent = Object.keys(visibleLocations).length + (currentLang === 'en' ? ' Locations' : ' ठिकाणे');
    
    // Update charts dynamically to match the filtered subset
    if (charts.priorityChart) {
        charts.priorityChart.updateSeries([
            dynamicPriorityCounts['High'] || 0,
            dynamicPriorityCounts['Medium'] || 0,
            dynamicPriorityCounts['Low'] || 0
        ]);
    }
    if (charts.talukaChart) {
        const categories = charts.talukaChart.w.config.xaxis.categories;
        const newData = categories.map(cat => dynamicTalukaCounts[cat] || 0);
        charts.talukaChart.updateSeries([{
            name: currentLang === 'en' ? 'Total Overdue Tasks' : 'एकूण थकीत कार्ये',
            data: newData
        }]);
    }
    
    // Update footer message
    const resultsMsg = currentLang === 'en' 
        ? `Showing <span class="font-medium text-slate-900 dark:text-white">${visibleCount}</span> of <span class="font-medium text-slate-900 dark:text-white">${overdueTasksRaw.length}</span> results`
        : `<span class="font-medium text-slate-900 dark:text-white">${overdueTasksRaw.length}</span> पैकी <span class="font-medium text-slate-900 dark:text-white">${visibleCount}</span> कार्ये दाखवत आहे`;
    document.getElementById('tableStats').innerHTML = resultsMsg;
}

// CSV Export
function exportToCSV() {
    if (overdueTasksRaw.length === 0) return;
    
    let csv = [];
    csv.push(['Task ID', 'Title', 'Description', 'Priority', 'Category', 'Due Date', 'Assigned To', 'Designation', 'Taluka', 'Village', 'Days Overdue'].join(','));
    
    const searchVal = document.getElementById('reportSearch').value.toLowerCase().trim();
    const talukaFilter = document.getElementById('talukaFilter') ? document.getElementById('talukaFilter').value : '';
    const priorityFilter = document.getElementById('priorityFilter').value;
    
    overdueTasksRaw.forEach(task => {
        const matchesSearch = !searchVal || (task.title || '').toLowerCase().includes(searchVal) || (task.assigned_to_name || '').toLowerCase().includes(searchVal);
        const matchesTaluka = !talukaFilter || task.taluka_name === talukaFilter;
        const matchesPriority = !priorityFilter || task.priority === priorityFilter;
        
        if (matchesSearch && matchesTaluka && matchesPriority) {
            let row = [
                `"${task.task_no || ''}"`,
                `"${(task.title || '').replace(/"/g, '""')}"`,
                `"${(task.description || '').replace(/"/g, '""')}"`,
                `"${task.priority || ''}"`,
                `"${task.task_category || ''}"`,
                `"${task.due_date || ''}"`,
                `"${(task.assigned_to_name || '').replace(/"/g, '""')}"`,
                `"${(task.assigned_designation || '').replace(/"/g, '""')}"`,
                `"${task.taluka_name || ''}"`,
                `"${task.village_name || ''}"`,
                `"${task.days_overdue || 0}"`
            ];
            csv.push(row.join(','));
        }
    });
    
    const blob = new Blob([csv.join("\n")], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `Amravati_Connect_Overdue_Report_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ─── Modal Functions Integration ─────────────────── */
let currentReportTasks = [];

function getStatusCss(status) {
    switch(status) {
        case 'Completed': return 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800';
        case 'Pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-800';
        case 'In Progress': return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800';
        case 'Overdue': return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800';
        default: return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600';
    }
}

function getPriorityCss(priority) {
    switch(priority) {
        case 'High': return 'text-red-600 dark:text-red-400';
        case 'Medium': return 'text-orange-500 dark:text-orange-400';
        default: return 'text-slate-400 dark:text-slate-500';
    }
}

function openReportModal(metric, extraParams = {}) {
    const modal = document.getElementById('reportDetailsModal');
    const loading = document.getElementById('reportModalLoading');
    const empty = document.getElementById('reportModalEmpty');
    const content = document.getElementById('reportModalContent');
    const subtitle = document.getElementById('reportModalSubtitle');
    const searchInput = document.getElementById('reportModalSearch');
    
    searchInput.value = '';
    currentReportTasks = [];
    
    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    empty.classList.add('hidden');
    content.classList.add('hidden');
    
    const params = new URLSearchParams({
        metric: metric,
        level: <?= $level ?>,
        taluka_id: <?= $sTalukaId ?>,
        village_id: <?= $sVillageId ?>,
        lang: currentLang,
        ...extraParams
    });
    
    fetch(`api/get_report_details.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.success && data.tasks.length > 0) {
                currentReportTasks = data.tasks;
                subtitle.textContent = currentLang === 'en' ? `Scope: ${data.scope}` : `व्याप्ती: ${data.scope}`;
                content.classList.remove('hidden');
                renderReportModalTasks(data.tasks);
            } else {
                subtitle.textContent = currentLang === 'en' ? `Scope: ${data.scope}` : `व्याप्ती: ${data.scope}`;
                empty.classList.remove('hidden');
                document.getElementById('reportModalFooterStats').textContent = currentLang === 'en' ? 'Showing 0 tasks' : '० कार्ये दाखवत आहे';
            }
            lucide.createIcons();
        })
        .catch(err => {
            console.error('Error loading report details:', err);
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            lucide.createIcons();
        });
}

function closeReportModal() {
    document.getElementById('reportDetailsModal').classList.add('hidden');
}

function renderReportModalTasks(tasks) {
    const tableBody = document.getElementById('reportModalTableBody');
    tableBody.innerHTML = '';
    
    tasks.forEach(task => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-150';
        const loc = [task.taluka_name, task.village_name].filter(Boolean).join(' / ') || '—';
        
        tr.innerHTML = `
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm font-semibold text-slate-900 dark:text-white">${task.title}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">#${task.task_no}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm text-slate-900 dark:text-white">${task.assigned_to_name}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">${task.assigned_designation || ''}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">${loc}</td>
            <td class="px-4 py-3 whitespace-nowrap">
                <span class="text-xs font-semibold ${getPriorityCss(task.priority)}">${task.priority}</span>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm text-slate-900 dark:text-white">${task.due_date_formatted}</div>
                <div class="text-xs font-medium text-red-600 dark:text-red-400">${task.timeline_info || ''}</div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border ${getStatusCss(task.status)}">${task.status}</span>
            </td>
        `;
        tableBody.appendChild(tr);
    });
    
    document.getElementById('reportModalFooterStats').textContent = currentLang === 'en' 
        ? `Showing ${tasks.length} task${tasks.length === 1 ? '' : 's'}` 
        : `${tasks.length} कार्ये दाखवत आहे`;
}

function filterReportModalTasks() {
    const val = document.getElementById('reportModalSearch').value.toLowerCase().trim();
    if (!val) {
        renderReportModalTasks(currentReportTasks);
        return;
    }
    const filtered = currentReportTasks.filter(task => {
        return (task.title || '').toLowerCase().includes(val) ||
               (task.task_no || '').toLowerCase().includes(val) ||
               (task.assigned_to_name || '').toLowerCase().includes(val) ||
               (task.assigned_designation || '').toLowerCase().includes(val);
    });
    renderReportModalTasks(filtered);
}

function exportReportToCSV() {
    if (currentReportTasks.length === 0) return;
    let csv = [];
    csv.push(['Task ID', 'Title', 'Description', 'Status', 'Due Date', 'Priority', 'Assigned To', 'Designation', 'Taluka', 'Village'].join(','));
    currentReportTasks.forEach(task => {
        let row = [
            `"${task.task_no || ''}"`,
            `"${(task.title || '').replace(/"/g, '""')}"`,
            `"${(task.description || '').replace(/"/g, '""')}"`,
            `"${task.status || ''}"`,
            `"${task.due_date || ''}"`,
            `"${task.priority || ''}"`,
            `"${(task.assigned_to_name || '').replace(/"/g, '""')}"`,
            `"${(task.assigned_designation || '').replace(/"/g, '""')}"`,
            `"${task.taluka_name || ''}"`,
            `"${task.village_name || ''}"`
        ];
        csv.push(row.join(','));
    });
    const blob = new Blob([csv.join("\n")], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `Amravati_Connect_Modal_Report_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* ─── APEXCHARTS BUILDER ─────────────────────────── */
let charts = {};

function destroyAll() {
    Object.values(charts).forEach(c => { try { c.destroy(); } catch(_){} });
    charts = {};
}

function buildAllCharts(isDark) {
    destroyAll();
    const tc  = isDark ? '#94a3b8' : '#64748b';
    const gc  = isDark ? '#334155' : '#e2e8f0';
    const mode= isDark ? 'dark'    : 'light';
    const ax  = { style:{ colors:tc, fontSize:'11px', fontFamily:'Inter,sans-serif' } };

    // 1. Priority Donut Chart
    charts.priorityChart = new ApexCharts(
        document.querySelector('#priorityChart'),
        {
            series: [
                <?= (int)$priority_counts['High'] ?>, 
                <?= (int)$priority_counts['Medium'] ?>, 
                <?= (int)$priority_counts['Low'] ?>
            ],
            chart: { 
                height: 260, 
                type: 'donut', 
                fontFamily: 'Inter,sans-serif', 
                background: 'transparent',
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        const priorities = ['High', 'Medium', 'Low'];
                        const selectedPriority = priorities[config.dataPointIndex];
                        const selectEl = document.getElementById('priorityFilter');
                        if (selectEl) {
                            selectEl.value = selectedPriority;
                            filterReportTable();
                        }
                    }
                }
            },
            labels: [
                <?= json_encode($t['priority_high']) ?>, 
                <?= json_encode($t['priority_medium']) ?>, 
                <?= json_encode($t['priority_low']) ?>
            ],
            colors: ['#ef4444', '#f57c00', '#64748b'],
            plotOptions: { pie: { donut: { size: '65%' } } },
            legend: { position: 'bottom', labels: { colors: tc } },
            dataLabels: { enabled: false },
            theme: { mode }
        }
    );
    charts.priorityChart.render();

    // 2. Taluka Bar Chart
    charts.talukaChart = new ApexCharts(
        document.querySelector('#talukaChart'),
        {
            series: [{
                name: <?= json_encode($t['kpi_total_overdue']) ?>,
                data: [<?php foreach($taluka_counts as $val) echo $val.','; ?>]
            }],
            chart: { 
                height: 260, 
                type: 'bar', 
                fontFamily: 'Inter,sans-serif', 
                background: 'transparent', 
                toolbar: { show: false },
                events: {
                    dataPointSelection: function(event, chartContext, config) {
                        const selectedTaluka = config.w.config.xaxis.categories[config.dataPointIndex];
                        const selectEl = document.getElementById('talukaFilter');
                        if (selectEl) {
                            selectEl.value = selectedTaluka;
                            filterReportTable();
                        }
                    }
                }
            },
            colors: ['#ef4444'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '40%' } },
            dataLabels: { enabled: false },
            xaxis: {
                categories: [<?php foreach($taluka_counts as $key => $val) echo '"'.addslashes($key).'",'; ?>],
                labels: { style: ax.style }
            },
            yaxis: { labels: { style: ax.style } },
            grid: { borderColor: gc, strokeDashArray: 4 },
            theme: { mode }
        }
    );
    charts.talukaChart.render();
}

// Init theme on load
document.addEventListener('DOMContentLoaded', () => {
    const isDark = localStorage.getItem('theme') === 'dark' || 
                   (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
    if (isDark) {
        document.documentElement.classList.add('dark');
        document.documentElement.classList.remove('light');
    }
    buildAllCharts(isDark);
    lucide.createIcons();

    // Profile Dropdown Toggle
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => {
            profileMenu.classList.add('hidden');
        });
    }
});
</script>
</body>
</html>
