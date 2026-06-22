<?php
session_start();
require_once 'include/dbConfig.php';

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        'title' => 'Notification Center - Amravati Connect',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_notifications' => 'Notification Center',
        'menu_task_alloc' => 'Task Allocation',
        'menu_announcements' => 'Announcements',
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
        'page_title' => 'Notification Center',
        'page_subtitle' => 'Manage your alerts, reminders, and system notifications.',
        'btn_mark_all_read' => 'Mark All as Read',
        'label_status' => 'Status',
        'label_type' => 'Type',
        'opt_all_notifications' => 'All Notifications',
        'opt_unread_only' => 'Unread Only',
        'opt_all_types' => 'All Types',
        'opt_task_allocated' => 'Task Allocated',
        'opt_reminders' => 'Reminders',
        'opt_announcements' => 'Announcements',
        'btn_filter' => 'Filter',
        'col_notification' => 'Notification',
        'col_type' => 'Type',
        'col_priority' => 'Priority',
        'col_date' => 'Date',
        'col_actions' => 'Actions',
        'no_notifications' => 'No notifications found based on the current filters.',
        'btn_read' => 'Read',
        'role_administrator' => 'System Administrator',
        'role_collector' => 'District Collector',
        'role_additional_collector' => 'Additional Collector',
        'role_deputy_collector' => 'Deputy Collector',
        'role_sdo' => 'Sub-Divisional Officer',
        'role_tehsildar' => 'Tehsildar',
        'role_bdo' => 'Block Development Officer',
        'role_talathi' => 'Talathi',
        'role_gramsevak' => 'Gramsevak',
        'badge_level' => 'Level',
        'priority_high' => 'High',
        'priority_medium' => 'Medium',
        'priority_low' => 'Low'
    ],
    'mr' => [
        'title' => 'सूचना केंद्र - अमरावती कनेक्ट',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_announcements' => 'घोषणा',
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
        'page_title' => 'सूचना केंद्र',
        'page_subtitle' => 'तुमच्या सूचना, स्मरणपत्रे आणि प्रणालीच्या सूचना व्यवस्थापित करा.',
        'btn_mark_all_read' => 'सर्व वाचलेले म्हणून चिन्हांकित करा',
        'label_status' => 'स्थिती',
        'label_type' => 'प्रकार',
        'opt_all_notifications' => 'सर्व सूचना',
        'opt_unread_only' => 'फक्त न वाचलेल्या',
        'opt_all_types' => 'सर्व प्रकार',
        'opt_task_allocated' => 'कार्य वाटप केलेले',
        'opt_reminders' => 'स्मरणपत्रे',
        'opt_announcements' => 'घोषणा',
        'btn_filter' => 'फिल्टर करा',
        'col_notification' => 'सूचना',
        'col_type' => 'प्रकार',
        'col_priority' => 'प्राधान्य',
        'col_date' => 'तारीख',
        'col_actions' => 'कृती',
        'no_notifications' => 'सध्याच्या फिल्टरवर आधारित कोणत्याही सूचना आढळल्या नाहीत.',
        'btn_read' => 'वाचले',
        'role_administrator' => 'सिस्टम प्रशासक',
        'role_collector' => 'जिल्हाधिकारी',
        'role_additional_collector' => 'अपर जिल्हाधिकारी',
        'role_deputy_collector' => 'उपजिल्हाधिकारी',
        'role_sdo' => 'उपविभागीय अधिकारी (SDO)',
        'role_tehsildar' => 'तहसीलदार',
        'role_bdo' => 'गट विकास अधिकारी (BDO)',
        'role_talathi' => 'तलाठी',
        'role_gramsevak' => 'ग्रामसेवक',
        'badge_level' => 'स्तर',
        'priority_high' => 'उच्च',
        'priority_medium' => 'मध्यम',
        'priority_low' => 'कमी'
    ]
];
$t = $translations[$lang];

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

$level = getDashboardLevel($sRole, $conn);

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

$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

// Handle Filters
$statusFilter = $_GET['status'] ?? 'All';
$typeFilter = $_GET['type'] ?? 'All';

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if ($statusFilter === 'Unread') {
    $whereClause .= " AND n.status = 'Unread'";
} else if ($statusFilter === 'Read') {
    $whereClause .= " AND n.status = 'Read'";
}

if ($typeFilter !== 'All') {
    $whereClause .= " AND n.notification_type = ?";
    $params[] = $typeFilter;
    $types .= "s";
}

$query = "SELECT n.*, t.priority AS task_priority FROM notifications n LEFT JOIN tasks t ON n.task_id = t.task_id $whereClause ORDER BY n.created_at DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$notificationsList = $result->fetch_all(MYSQLI_ASSOC);

// Helper function
function time_elapsed_string_full($datetime, $lang = 'en', $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    
    $string = [
        'y' => ['year', 'years', 'वर्ष', 'वर्षांपूर्वी'],
        'm' => ['month', 'months', 'महिने', 'महिन्यांपूर्वी'],
        'w' => ['week', 'weeks', 'आठवडे', 'आठवड्यांपूर्वी'],
        'd' => ['day', 'days', 'दिवस', 'दिवसांपूर्वी'],
        'h' => ['hour', 'hours', 'तास', 'तासांपूर्वी'],
        'i' => ['minute', 'minutes', 'मिनिटे', 'मिनिटांपूर्वी'],
        's' => ['second', 'seconds', 'सेकंद', 'सेकंदांपूर्वी']
    ];
    
    $parts = [];
    foreach ($string as $k => $v) {
        if ($diff->$k) {
            $count = $diff->$k;
            if ($lang === 'en') {
                $parts[] = $count . ' ' . ($count > 1 ? $v[1] : $v[0]);
            } else {
                $parts[] = $count . ' ' . $v[2];
            }
        }
    }
    
    if (!$full) $parts = array_slice($parts, 0, 1);
    
    if (empty($parts)) {
        return $lang === 'en' ? 'just now' : 'आता लगेच';
    }
    
    if ($lang === 'en') {
        return implode(', ', $parts) . ' ago';
    } else {
        return implode(', ', $parts) . ' पूर्वी';
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
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
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind Config for Design System -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        border: "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        navy: {
                            50: '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1a365d',
                            600: '#152b4a',
                            700: '#0f1f38',
                            900: '#0a1424'
                        },
                        govgreen: {
                            50: '#edf7ed',
                            100: '#cce8cc',
                            500: '#2e7d32',
                            600: '#256428'
                        },
                        saffron: {
                            50: '#fff3e0',
                            100: '#ffe0b2',
                            500: '#f57c00',
                            600: '#e65100'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Base styles and ShadCN-like variables */
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --border: 214.3 31.8% 91.4%;
        }
        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

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
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
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
                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_dashboard']) ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="network"   class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_task_alloc']) ?>
                </a>
                <a href="notifications.php?lang=<?= $lang ?>"
                   class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                    <?= htmlspecialchars($t['menu_notifications']) ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="award"     class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($t['menu_appreciation']) ?>
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
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
                <a href="user_creation.php?lang=<?= $lang ?>"
                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_users']) ?>
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
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- GLOBAL HEADER -->
        <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <?php
                $queryParams = $_GET;
                $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
                $lang_switch_url = 'notifications.php?' . http_build_query($queryParams);
                ?>
                <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
                   class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                          hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md
                          transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                    <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                    <?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?>
                </a>
                
                <!-- Theme Switcher -->
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
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

                <!-- Profile Dropdown Container -->
                <div class="relative pl-4 ml-2 border-l border-slate-200 dark:border-slate-700">
                    <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none" aria-haspopup="true" aria-expanded="false">
                        <div class="flex flex-col text-right hidden sm:block mr-2">
                            <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                            <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($roleLabel) ?></span>
                        </div>
                        <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm transition-transform duration-200 hover:scale-105 active:scale-95">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                        <div class="py-1 text-left font-normal">
                            <a href="profile_update.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors" style="text-decoration: none;">
                                <i data-lucide="user" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                                <?= $lang === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                            </a>
                            <a href="settings.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors" style="text-decoration: none;">
                                <i data-lucide="settings" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                                <?= $lang === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                            </a>
                            <a href="passwordReset.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors" style="text-decoration: none;">
                                <i data-lucide="key" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                                <?= $lang === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                            </a>
                            <div class="border-t border-slate-150 dark:border-slate-800 my-1"></div>
                            <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors" style="text-decoration: none;">
                                <i data-lucide="log-out" class="w-4 h-4 mr-2.5 text-red-500"></i>
                                <?= $lang === 'en' ? 'Logout' : 'लॉगआउट' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3 items-center">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold badge-l<?= $level ?> mr-2">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        <?= htmlspecialchars($t['badge_level'] ?? 'Level') ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                    </span>
                    <button onclick="markAllAsRead()" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 focus:outline-none transition-colors">
                        <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> <?= htmlspecialchars($t['btn_mark_all_read']) ?>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6">
                <form method="GET" action="notifications.php" class="flex flex-wrap gap-4 items-end">
                    <input type="hidden" name="lang" value="<?= $lang ?>">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1"><?= htmlspecialchars($t['label_status']) ?></label>
                        <select name="status" class="block w-40 pl-3 pr-10 py-2 text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md focus:ring-navy-500">
                            <option value="All" <?= $statusFilter == 'All' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_all_notifications']) ?></option>
                            <option value="Unread" <?= $statusFilter == 'Unread' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_unread_only']) ?></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1"><?= htmlspecialchars($t['label_type']) ?></label>
                        <select name="type" class="block w-48 pl-3 pr-10 py-2 text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md focus:ring-navy-500">
                            <option value="All" <?= $typeFilter == 'All' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_all_types']) ?></option>
                            <option value="Task Allocated" <?= $typeFilter == 'Task Allocated' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_task_allocated']) ?></option>
                            <option value="Reminder" <?= $typeFilter == 'Reminder' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_reminders']) ?></option>
                            <option value="Announcement" <?= $typeFilter == 'Announcement' ? 'selected' : '' ?>><?= htmlspecialchars($t['opt_announcements']) ?></option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-600 hover:bg-navy-700 transition-colors"><?= htmlspecialchars($t['btn_filter']) ?></button>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-2/5"><?= htmlspecialchars($t['col_notification']) ?></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/5"><?= htmlspecialchars($t['col_type']) ?></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/6"><?= htmlspecialchars($t['col_priority']) ?></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/6"><?= htmlspecialchars($t['col_date']) ?></th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-24"><?= htmlspecialchars($t['col_actions']) ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <?php if (empty($notificationsList)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['no_notifications']) ?></td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($notificationsList as $notif): 
                                    $isRead = ($notif['status'] ?? '') !== 'Unread';
                                    $rowClass = $isRead ? 'bg-white dark:bg-slate-800' : 'bg-blue-50/20 dark:bg-slate-800/80 font-semibold';
                                    
                                    $priority = $notif['task_priority'] ?? 'Medium';
                                    $pColor = 'text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400';
                                    if ($priority == 'Critical') $pColor = 'text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400';
                                    if ($priority == 'High') $pColor = 'text-orange-600 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400';
                                    if ($priority == 'Low') $pColor = 'text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400';

                                    $priorityKey = match($priority) {
                                        'High', 'Critical' => 'priority_high',
                                        'Medium' => 'priority_medium',
                                        'Low' => 'priority_low',
                                        default => 'priority_medium'
                                    };
                                    $priorityLabel = $t[$priorityKey] ?? $priority;

                                    $dbNotifType = $notif['notification_type'];
                                    $translatedType = match($dbNotifType) {
                                        'Task Allocated' => $t['opt_task_allocated'],
                                        'Reminder' => $t['opt_reminders'],
                                        'Announcement' => $t['opt_announcements'],
                                        default => $dbNotifType
                                    };
                                ?>
                                <tr class="<?= $rowClass ?> hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <td class="px-6 py-4 align-top">
                                        <div class="text-sm text-slate-900 dark:text-white font-semibold"><?= htmlspecialchars($notif['title']) ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($notif['message']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 align-top">
                                        <?= htmlspecialchars($translatedType) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $pColor ?>">
                                            <?= htmlspecialchars($priorityLabel) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 align-top">
                                        <?= time_elapsed_string_full($notif['created_at'], $lang) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                        <div class="flex justify-end items-center space-x-3">
                                            <?php if (!$isRead): ?>
                                                <button onclick="markAsReadPage(<?= $notif['notification_id'] ?>)" class="text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 transition-colors flex items-center" title="Mark as read">
                                                    <i data-lucide="check" class="w-4 h-4 mr-1"></i> <span class="text-xs"><?= htmlspecialchars($t['btn_read']) ?></span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script>
        lucide.createIcons();

        // Dark Mode Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        function updateTheme(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        }

        themeToggle.addEventListener('click', () => {
            const isDark = !htmlElement.classList.contains('dark');
            updateTheme(isDark);
            localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full') || sidebar.style.display === 'none') {
                sidebar.classList.remove('-translate-x-full');
                sidebar.style.display = 'flex';
            } else {
                sidebar.classList.add('-translate-x-full');
                setTimeout(() => sidebar.style.display = 'none', 300);
            }
        });

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

        function fetchNotifications() {
            fetch('api/get_notifications.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
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

        function markAsRead(id) {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: id })
            }).then(() => fetchNotifications());
        }

        function markAsReadPage(id) {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: id })
            }).then(() => location.reload());
        }

        function markAllAsRead() {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mark_all: true })
            }).then(() => location.reload());
        }

        setInterval(fetchNotifications, 30000);
        fetchNotifications();

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
    </script>
</body>
</html>
