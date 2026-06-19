<?php
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
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'include/dbConfig.php';

/* ─── Role → Level map ─────────────────────────────────────── */
const ROLE_LEVEL_MAP = [
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
function getDashboardLevel(string $role): int {
    return ROLE_LEVEL_MAP[$role] ?? 3;
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
              COUNT(CASE WHEN status != 'Completed' THEN 1 END)           AS active,
              COUNT(CASE WHEN status  = 'Pending'   THEN 1 END)           AS pending,
              COUNT(CASE WHEN status  = 'Completed' THEN 1 END)           AS completed,
              COUNT(CASE WHEN due_date < CURDATE()
                         AND status  != 'Completed' THEN 1 END)           AS overdue
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
        // TODO: Join with `locations` table using location_id FK once schema is confirmed.
        $res = $conn->query("
            SELECT
              COALESCE(location_name,'Unknown')                            AS taluka,
              COUNT(*)                                                     AS total,
              COUNT(CASE WHEN status = 'Completed' THEN 1 END)            AS completed,
              COUNT(CASE WHEN status = 'Pending'   THEN 1 END)            AS pending,
              COUNT(CASE WHEN due_date < CURDATE()
                         AND status != 'Completed' THEN 1 END)            AS overdue,
              ROUND(COUNT(CASE WHEN status='Completed' THEN 1 END)
                    / NULLIF(COUNT(*),0)*100, 1)                           AS rate
            FROM tasks
            GROUP BY location_name
            ORDER BY rate DESC
            LIMIT 10
        ");
        while ($row = $res->fetch_assoc()) $out['talukas'][] = $row;

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
        // TODO: Replace `taluka_id` with actual FK column name from schema.
        $st = $conn->prepare("
            SELECT
              COUNT(*)                                                     AS total,
              COUNT(CASE WHEN status != 'Completed' THEN 1 END)           AS active,
              COUNT(CASE WHEN status  = 'Pending'   THEN 1 END)           AS pending,
              COUNT(CASE WHEN status  = 'Completed' THEN 1 END)           AS completed,
              COUNT(CASE WHEN due_date < CURDATE()
                         AND status  != 'Completed' THEN 1 END)           AS overdue
            FROM tasks WHERE taluka_id = ?
        ");
        $st->bind_param('i', $talukaId);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            foreach (['total','active','pending','completed','overdue'] as $k)
                $out[$k] = (int)$r[$k];
        }
        $st->close();

        /* ── Village breakdown ─────────────────────────────────── */
        // TODO: Join `villages` table for proper village names.
        $st = $conn->prepare("
            SELECT
              COALESCE(village_name,'Unknown')                             AS village,
              COUNT(*)                                                     AS total,
              COUNT(CASE WHEN status='Completed' THEN 1 END)              AS completed,
              COUNT(CASE WHEN status='Pending'   THEN 1 END)              AS pending,
              COUNT(CASE WHEN due_date<CURDATE()
                         AND status!='Completed' THEN 1 END)              AS overdue
            FROM tasks WHERE taluka_id=? GROUP BY village_name
            ORDER BY total DESC LIMIT 10
        ");
        $st->bind_param('i', $talukaId);
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
        // TODO: Replace `assigned_village_id` with actual column once confirmed.
        $st = $conn->prepare("
            SELECT
              COUNT(*)                                                     AS total,
              COUNT(CASE WHEN status != 'Completed' THEN 1 END)           AS active,
              COUNT(CASE WHEN status  = 'Pending'   THEN 1 END)           AS pending,
              COUNT(CASE WHEN status  = 'Completed' THEN 1 END)           AS completed,
              COUNT(CASE WHEN due_date < CURDATE()
                         AND status  != 'Completed' THEN 1 END)           AS overdue
            FROM tasks WHERE assigned_village_id = ?
        ");
        $st->bind_param('i', $villageId);
        $st->execute();
        if ($r = $st->get_result()->fetch_assoc()) {
            foreach (['total','active','pending','completed','overdue'] as $k)
                $out[$k] = (int)$r[$k];
        }
        $st->close();

        /* ── Task list ──────────────────────────────────────────── */
        // TODO: JOIN users table for assigned_to_name once FK is available.
        $st = $conn->prepare("
            SELECT task_id, title, status, due_date, priority, assigned_to_name
            FROM tasks WHERE assigned_village_id = ?
            ORDER BY FIELD(status,'Overdue','Pending','In Progress','Completed'),
                     due_date ASC LIMIT 20
        ");
        $st->bind_param('i', $villageId);
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

$level      = getDashboardLevel($sRole);
$showL1     = ($level === 1);
$showL2     = ($level <= 2);
$showL3     = true;

$distData   = $showL1 ? getDistrictStats($conn)             : _mockDistrict();
$talData    = $showL2 ? getTalukaStats($conn, $sTalukaId)   : _mockTaluka();
$vilData    =           getVillageStats($conn, $sVillageId);

/* Friendly role label */
$roleLabel = [
    'System Administrator'=>'System Administrator','Collector'=>'District Collector',
    'Additional Collector'=>'Additional Collector','Deputy Collector'=>'Deputy Collector',
    'SDO'=>'Sub-Divisional Officer','Tehsildar'=>'Tehsildar',
    'BDO'=>'Block Development Officer','Talathi'=>'Talathi','Gramsevak'=>'Gramsevak',
][$sRole] ?? $sRole;

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
?>
<!DOCTYPE html>
<html lang="en" class="light" id="htmlRoot">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Amravati Connect | Government Workflow Platform</title>
    <meta name="description"
          content="Role-based executive dashboard for Amravati District. District, Taluka and Village level task insights.">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

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
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Main Modules</p>
            <a href="dashboard.php"
               class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                Executive Dashboard
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network"   class="w-5 h-5 mr-3 text-slate-400"></i>Task Allocation
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>Announcements
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award"     class="w-5 h-5 mr-3 text-slate-400"></i>Appreciation
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Analytics &amp; Data</p>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart"   class="w-5 h-5 mr-3 text-slate-400"></i>Reports &amp; Analytics
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map"         class="w-5 h-5 mr-3 text-slate-400"></i>GIS Map View
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>Document Management
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users"        class="w-5 h-5 mr-3 text-slate-400"></i>User Management
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map-pin"      class="w-5 h-5 mr-3 text-slate-400"></i>Location Hierarchy
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i>Audit Logs
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="settings"     class="w-5 h-5 mr-3 text-slate-400"></i>Settings
            </a>
        </nav>
    </div>

    <!-- Footer CTA -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent
                       rounded-md shadow-sm text-sm font-medium text-white
                       bg-gradient-to-r from-navy-600 to-navy-500
                       hover:from-navy-700 hover:to-navy-600 focus:outline-none transition-all">
            <i data-lucide="bot" class="w-4 h-4 mr-2"></i>Ask Amravati AI
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
                       placeholder="Search tasks, officers, or circulars (Press '/')"
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
            <button class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                           hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md
                           transition-colors border border-slate-200 dark:border-slate-700">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>EN / MR
            </button>
            <!-- Theme -->
            <button id="themeToggle"
                    class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400
                           dark:hover:text-slate-200 rounded-full
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun"  class="w-5 h-5 hidden dark:block"></i>
            </button>
            <!-- Notifications -->
            <button class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400
                           dark:hover:text-slate-200 rounded-full
                           hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full
                             bg-saffron-500 ring-2 ring-white dark:ring-slate-900"></span>
            </button>
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
                    District Executive Dashboard
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Real-time overview of Amravati District operations and task hierarchy.
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3 flex-wrap gap-y-2">
                <!-- Access level badge -->
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                             <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    Level <?= $level ?> &middot; <?= htmlspecialchars($sRole) ?>
                </span>
                <button class="inline-flex items-center px-4 py-2 border border-slate-300
                               dark:border-slate-600 shadow-sm text-sm font-medium rounded-md
                               text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800
                               hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i>Export Report
                </button>
                <button class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm
                               text-sm font-medium rounded-md text-white
                               bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>Allocate Task
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
                            District Level Dashboard
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            District-wide summary &amp; taluka performance
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
                    $dkpi = [
                        ['Total Active Tasks',    $distData['active'],    'layers',       'blue',   'trending-up',   '+12%', true],
                        ['Pending Approvals',     $distData['pending'],   'clock',        'orange', 'trending-down', '-4%',  false],
                        ['Tasks Completed',       $distData['completed'], 'check-circle', 'green',  'trending-up',   '+24%', true],
                        ['Escalated / Overdue',   $distData['overdue'],   'alert-octagon','red',    'alert-triangle','12 Action Req', false],
                    ];
                    foreach ($dkpi as [$label,$val,$icon,$clr,$trendIcon,$trendTxt,$trendUp]):
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
                                            <?= number_format($val) ?>
                                        </p>
                                        <p class="ml-2 flex items-baseline text-sm font-semibold
                                           <?= $trendUp ? 'text-govgreen-600 dark:text-green-400'
                                                        : 'text-red-600 dark:text-red-400' ?>">
                                            <i data-lucide="<?= $trendIcon ?>" class="w-3 h-3 mr-1"></i>
                                            <?= $trendTxt ?>
                                        </p>
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
                                Task Completion Trend (District Wide)
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-dist-trend" class="h-72 w-full"></div>
                    </div>
                    <!-- Donut -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                                border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                                Task Status Distribution
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-dist-donut" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Top Performing Offices — bar chart -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Top Performing Talukas
                        </h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div id="chart-dist-bar" class="h-60 w-full"></div>
                </div>

                <!-- Taluka-wise performance table (matches blank_wrushabh.php table) -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Taluka-wise Performance Pipeline
                        </h2>
                        <div class="flex space-x-2">
                            <select class="block pl-3 pr-8 py-2 text-sm border-slate-300
                                          dark:border-slate-600 dark:bg-slate-700 dark:text-white
                                          rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option>All Talukas</option>
                                <?php foreach ($distData['talukas'] as $t): ?>
                                <option><?= htmlspecialchars($t['taluka']) ?></option>
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
                                    <?php foreach (['Taluka / Office','Total','Completed','Pending','Overdue','Rate','Progress'] as $h): ?>
                                    <th class="px-6 py-3 text-left text-xs font-semibold
                                               text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        <?= $h ?>
                                    </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <?php foreach ($distData['talukas'] as $idx => $t):
                                    $dotColors = ['bg-red-500','bg-saffron-500','bg-blue-500','bg-govgreen-500','bg-purple-500','bg-teal-500'];
                                    $dot = $dotColors[$idx % count($dotColors)];
                                ?>
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-2 h-2 rounded-full <?= $dot ?> mr-3"></div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">
                                                <?= htmlspecialchars($t['taluka']) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 dark:text-slate-300">
                                        <?= number_format($t['total']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-govgreen-600 dark:text-green-400">
                                        <?= number_format($t['completed']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-saffron-600 dark:text-yellow-400">
                                        <?= number_format($t['pending']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 dark:text-red-400">
                                        <?= number_format($t['overdue']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-900 dark:text-white">
                                        <?= number_format($t['rate'], 1) ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap w-36">
                                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                                            <div class="bg-navy-600 h-1.5 rounded-full transition-all duration-700"
                                                 style="width:<?= min(100,(float)$t['rate']) ?>%"></div>
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
                                Showing <span class="font-medium text-slate-900 dark:text-white">1</span>
                                to <span class="font-medium text-slate-900 dark:text-white"><?= count($distData['talukas']) ?></span>
                                of <span class="font-medium text-slate-900 dark:text-white"><?= number_format($distData['total']) ?></span> results
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
                            Taluka Level Dashboard
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Sub-divisional summary &amp; village breakdown
                        </p>
                    </div>
                    <span class="badge-l2 text-xs font-semibold px-2.5 py-0.5 rounded-full">Level 2</span>
                </div>
                <i data-lucide="chevron-down" id="chev-tal"
                   class="chevron open w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300"></i>
            </button>

            <div id="sec-tal" class="sec-body">

                <!-- Taluka KPI Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <?php
                    $tkpi = [
                        ['Total Active Tasks',   $talData['active'],    'layers',       'blue',   'trending-up',   '+9%',  true],
                        ['Pending Approvals',    $talData['pending'],   'clock',        'orange', 'trending-down', '-2%',  false],
                        ['Tasks Completed',      $talData['completed'], 'check-circle', 'green',  'trending-up',   '+18%', true],
                        ['Overdue / Escalated',  $talData['overdue'],   'alert-octagon','red',    'alert-triangle','5 Req',false],
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
                                            <?= number_format($val) ?>
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
                                Monthly Task Completion Trend
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
                                Task Status Distribution
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
                            Village Performance
                        </h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i data-lucide="more-vertical" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div id="chart-tal-bar" class="h-60 w-full"></div>
                </div>

                <!-- Village Summary Table -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Village-wise Performance Summary
                        </h2>
                        <button class="text-sm text-navy-600 dark:text-blue-400 hover:underline font-medium">
                            View All
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <?php foreach (['Village','Total','Completed','Pending','Overdue','Progress'] as $h): ?>
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
                            Village Level Dashboard
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Field officer task assignments — Talathi &amp; Gramsevak
                        </p>
                    </div>
                    <span class="badge-l3 text-xs font-semibold px-2.5 py-0.5 rounded-full">Level 3</span>
                </div>
                <i data-lucide="chevron-down" id="chev-vil"
                   class="chevron open w-5 h-5 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300"></i>
            </button>

            <div id="sec-vil" class="sec-body">

                <!-- Village KPI Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <?php
                    $vkpi = [
                        ['Total Active Tasks',  $vilData['active'],    'layers',       'blue',   'trending-up',   '+5%',    true],
                        ['Pending Tasks',       $vilData['pending'],   'clock',        'orange', 'alert-triangle','Needs attention',false],
                        ['Tasks Completed',     $vilData['completed'], 'check-circle', 'green',  'trending-up',   '+15%',   true],
                        ['Overdue Tasks',       $vilData['overdue'],   'alert-octagon','red',    'alert-triangle','Urgent', false],
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
                                            <?= number_format($val) ?>
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
                                Monthly Task Completion Trend
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
                                Task Status Distribution
                            </h2>
                            <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                <i data-lucide="more-vertical" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <div id="chart-vil-donut" class="h-72 w-full"></div>
                    </div>
                </div>

                <!-- Task Allocation Pipeline Table (matches blank_wrushabh.php style) -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm
                            border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700
                                flex flex-col sm:flex-row justify-between items-center gap-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            Hierarchical Task Allocation Pipeline
                        </h2>
                        <div class="flex space-x-2">
                            <select id="statusFilter" onchange="filterRows()"
                                    class="block pl-3 pr-8 py-2 text-sm border-slate-300
                                           dark:border-slate-600 dark:bg-slate-700 dark:text-white
                                           rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option value="">All Statuses</option>
                                <option>Completed</option>
                                <option>Pending</option>
                                <option>In Progress</option>
                                <option>Overdue</option>
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
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task Details</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <?php foreach ($vilData['tasks'] as $task):
                                    $sc  = statusCss($task['status']);
                                    $dc  = dotCss($task['status']);
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
                                                    <?= htmlspecialchars($sRole) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-xs font-semibold <?= $pc ?>">
                                            <?= htmlspecialchars($task['priority'] ?? 'Low') ?>
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
                                            <?= htmlspecialchars($task['status']) ?>
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
                                Showing <span class="font-medium text-slate-900 dark:text-white">1</span>
                                to <span class="font-medium text-slate-900 dark:text-white">
                                    <?= count($vilData['tasks']) ?>
                                </span>
                                of <span class="font-medium text-slate-900 dark:text-white">
                                    <?= number_format($vilData['total']) ?>
                                </span> results
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

function destroyAll() {
    Object.values(charts).forEach(c => { try { c.destroy(); } catch(_){} });
    charts = {};
}

function buildAllCharts(isDark) {
    destroyAll();
    const tc  = isDark ? '#94a3b8' : '#64748b';   // text
    const gc  = isDark ? '#334155' : '#e2e8f0';   // grid
    const mode= isDark ? 'dark'    : 'light';
    const ax  = { style:{ colors:tc, fontSize:'11px', fontFamily:'Inter,sans-serif' } };

    /* ── Shared builders ─────────────────────────────────── */
    function areaOpts(series, cats, colors) {
        return {
            series, colors,
            chart:{ height:288, type:'area', fontFamily:'Inter,sans-serif',
                    toolbar:{show:false}, background:'transparent' },
            dataLabels:{ enabled:false },
            stroke:{ curve:'smooth', width:2 },
            fill:{ type:'gradient', gradient:{ opacityFrom:0.22, opacityTo:0.02 } },
            xaxis:{ categories:cats, labels:ax, axisBorder:{show:false}, axisTicks:{show:false} },
            yaxis:{ labels:ax },
            grid:{ borderColor:gc, strokeDashArray:4 },
            legend:{ position:'top', horizontalAlign:'right',
                     fontFamily:'Inter,sans-serif', fontSize:'12px' },
            theme:{ mode }
        };
    }

    function donutOpts(series, labels, colors) {
        return {
            series, labels, colors,
            chart:{ height:288, type:'donut', fontFamily:'Inter,sans-serif', background:'transparent' },
            dataLabels:{ enabled:false },
            plotOptions:{ pie:{ donut:{ size:'70%',
                labels:{ show:true, total:{
                    show:true, label:'Total',
                    style:{ fontSize:'13px', fontFamily:'Inter,sans-serif', color:tc }
                }}
            }}},
            legend:{ position:'bottom', fontFamily:'Inter,sans-serif', fontSize:'12px' },
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
    <?php if ($showL1): ?>
    /* District */
    charts.dTrend = new ApexCharts(
        document.querySelector('#chart-dist-trend'),
        areaOpts([
            { name:'Assigned Tasks',  data:[310,400,280,510,420,609,500] },
            { name:'Completed Tasks', data:[250,320,240,480,390,580,490] }
        ], ['Jan','Feb','Mar','Apr','May','Jun','Jul'], ['#1a365d','#2e7d32'])
    );
    charts.dTrend.render();

    charts.dDonut = new ApexCharts(
        document.querySelector('#chart-dist-donut'),
        donutOpts(
            [<?= inProgress($distData['active'],$distData['pending']) ?>,
             <?= $distData['pending'] ?>,
             <?= $distData['completed'] ?>,
             <?= $distData['overdue'] ?>],
            ['In Progress','Pending','Completed','Overdue'],
            ['#3b82f6','#f57c00','#2e7d32','#ef4444']
        )
    );
    charts.dDonut.render();

    charts.dBar = new ApexCharts(
        document.querySelector('#chart-dist-bar'),
        hbarOpts(
            [{ name:'Completion Rate %', data:[
                <?php foreach($distData['talukas'] as $t) echo $t['rate'].','; ?>
            ]}],
            [<?php foreach($distData['talukas'] as $t) echo '"'.addslashes($t['taluka']).'",'; ?>],
            '#1a365d'
        )
    );
    charts.dBar.render();
    <?php endif; ?>

    <?php if ($showL2): ?>
    /* Taluka */
    charts.tTrend = new ApexCharts(
        document.querySelector('#chart-tal-trend'),
        areaOpts([
            { name:'Assigned Tasks',  data:[80,110,75,140,110,180,155] },
            { name:'Completed Tasks', data:[60, 95,60,125, 95,165,140] }
        ], ['Jan','Feb','Mar','Apr','May','Jun','Jul'], ['#f57c00','#2e7d32'])
    );
    charts.tTrend.render();

    charts.tDonut = new ApexCharts(
        document.querySelector('#chart-tal-donut'),
        donutOpts(
            [<?= inProgress($talData['active'],$talData['pending']) ?>,
             <?= $talData['pending'] ?>,
             <?= $talData['completed'] ?>,
             <?= $talData['overdue'] ?>],
            ['In Progress','Pending','Completed','Overdue'],
            ['#3b82f6','#f57c00','#2e7d32','#ef4444']
        )
    );
    charts.tDonut.render();

    charts.tBar = new ApexCharts(
        document.querySelector('#chart-tal-bar'),
        {
            series:[
                { name:'Completed', data:[<?php foreach($talData['villages'] as $v) echo $v['completed'].','; ?>] },
                { name:'Pending',   data:[<?php foreach($talData['villages'] as $v) echo $v['pending'].','; ?>] },
                { name:'Overdue',   data:[<?php foreach($talData['villages'] as $v) echo $v['overdue'].','; ?>] }
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
    <?php endif; ?>

    /* Village */
    charts.vTrend = new ApexCharts(
        document.querySelector('#chart-vil-trend'),
        areaOpts([
            { name:'Assigned Tasks',  data:[8,12,9,14,11,18,15] },
            { name:'Completed Tasks', data:[6,10,7,12,10,16,14] }
        ], ['Jan','Feb','Mar','Apr','May','Jun','Jul'], ['#2e7d32','#f57c00'])
    );
    charts.vTrend.render();

    charts.vDonut = new ApexCharts(
        document.querySelector('#chart-vil-donut'),
        donutOpts(
            [<?= inProgress($vilData['active'],$vilData['pending']) ?>,
             <?= $vilData['pending'] ?>,
             <?= $vilData['completed'] ?>,
             <?= $vilData['overdue'] ?>],
            ['In Progress','Pending','Completed','Overdue'],
            ['#3b82f6','#f57c00','#2e7d32','#ef4444']
        )
    );
    charts.vDonut.render();
}
</script>
</body>
</html>
