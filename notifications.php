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
        'menu_announcement_center' => 'Announcement Center',
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
        'no_notifications' => 'No notifications found.',
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
        'priority_low' => 'Low',
        'btn_ask_ai' => 'Ask Amravati AI'
    ],
    'mr' => [
        'title' => 'सूचना केंद्र - अमरावती कनेक्ट',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_announcements' => 'घोषणा',
        'menu_announcement_center' => 'घोषणा केंद्र',
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
        'no_notifications' => 'कोणत्याही सूचना आढळल्या नाहीत.',
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
        'priority_low' => 'कमी',
        'btn_ask_ai' => 'अमरावती एआय विचारा'
    ]
];
$t = $translations[$lang];

/* ─── Session defaults (dev preview) ───────────────────────── */
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id']         = 1;
    $_SESSION['user_role']       = 'Collector';
    $_SESSION['user_name']       = 'Hon. Collector';
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

$userId     = (int)$_SESSION['user_id'];
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
$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

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
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        navy: {
                            50: '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1E3A8A',
                            600: '#2563EB',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        },
                        govgreen: {
                            50: '#edf7ed',
                            100: '#cce8cc',
                            500: '#10B981',
                            600: '#059669'
                        },
                        saffron: {
                            50: '#fff3e0',
                            100: '#ffe0b2',
                            500: '#F59E0B',
                            600: '#d97706'
                        }
                    }
                }
            }
        }
    </script>

    <style>
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
        
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .badge-l1 { background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; }
        .badge-l2 { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .badge-l3 { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .dark .badge-l1 { background:#1e3a8a33; color:#93c5fd; border-color:#1e40af; }
        .dark .badge-l2 { background:#92400e33; color:#fcd34d; border-color:#b45309; }
        .dark .badge-l3 { background:#065f4633; color:#6ee7b7; border-color:#047857; }

        .nav-active { background:#eef2f6; color:#1e3a8a; font-weight: 600; }
        .dark .nav-active { background:#1e293b; color:#fff; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
    <aside id="sidebar" class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20">
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded bg-navy-500 flex items-center justify-center mr-3">
                <i data-lucide="landmark" class="text-white w-5 h-5"></i>
            </div>
            <span class="font-bold text-lg text-navy-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['brand_name']) ?></span>
        </div>

        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-3">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
                <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_dashboard']) ?>
                </a>
                <a href="announcements.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="megaphone" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_announcement_center']) ?>
                </a>
                <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_task_alloc']) ?>
                </a>
                <a href="notifications.php?lang=<?= $lang ?>" class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-navy-500 dark:text-blue-400"></i>
                    <?= htmlspecialchars($t['menu_notifications']) ?>
                </a>
                
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
                <a href="reports.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_reports']) ?>
                </a>
            </nav>
        </div>
        
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-navy-500 to-navy-600 hover:from-navy-600 hover:to-navy-700 focus:outline-none">
                <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
                <?= htmlspecialchars($t['btn_ask_ai']) ?>
            </button>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- HEADER -->
        <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Lang Switch -->
                <a href="notifications.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                    <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                    <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
                </a>
                
                <!-- Theme Toggle -->
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                    <i data-lucide="sun"  class="w-5 h-5 hidden dark:block"></i>
                </button>

                <!-- Notification Bell -->
                <?php include 'include/notification_widget.php'; ?>
                
                <!-- Profile -->
                <div class="flex items-center space-x-3 border-l border-slate-200 dark:border-slate-700 pl-4">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($roleLabel) ?></span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-500 flex items-center justify-center text-white font-bold text-sm border shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN SCROLLABLE BODY -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
            <!-- Page Title -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold badge-l<?= $level ?>">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                    </span>
                    <button onclick="markAllAsRead()" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white text-sm font-semibold rounded-lg shadow-md transition-colors flex items-center">
                        <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> <?= htmlspecialchars($t['btn_mark_all_read']) ?>
                    </button>
                </div>
            </div>

            <!-- TABS NAVIGATION -->
            <div class="border-b border-slate-200 dark:border-slate-700 mb-6 overflow-x-auto">
                <nav class="flex space-x-6 min-w-max">
                    <button onclick="switchCenterTab('all')" id="cbtn-all" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-navy-500 text-navy-600 font-bold dark:border-blue-400 dark:text-blue-400">All Alerts</button>
                    <button onclick="switchCenterTab('task')" id="cbtn-task" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">Task Notifications</button>
                    <button onclick="switchCenterTab('meeting')" id="cbtn-meeting" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">Meeting Notifications</button>
                    <button onclick="switchCenterTab('annc')" id="cbtn-annc" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">Announcement Notifications</button>
                    <?php if ($isL1): ?>
                    <button onclick="switchCenterTab('verify_req')" id="cbtn-verify_req" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">Verification Requests</button>
                    <button onclick="switchCenterTab('approve_req')" id="cbtn-approve_req" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">Approval Requests</button>
                    <?php endif; ?>
                    <button onclick="switchCenterTab('alerts')" id="cbtn-alerts" class="center-tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">System Alerts</button>
                </nav>
            </div>

            <!-- FILTERS -->
            <div class="flex flex-wrap gap-2 mb-6 items-center">
                <span class="text-xs font-bold text-slate-400 uppercase mr-2">Filters:</span>
                <button onclick="setStatusFilter('all')" id="fbtn-all" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-navy-500 text-white">All</button>
                <button onclick="setStatusFilter('unread')" id="fbtn-unread" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Unread</button>
                <button onclick="setStatusFilter('read')" id="fbtn-read" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Read</button>
                <button onclick="setStatusFilter('accepted')" id="fbtn-accepted" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Accepted</button>
                <button onclick="setStatusFilter('rejected')" id="fbtn-rejected" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Rejected</button>
                <button onclick="setStatusFilter('pending')" id="fbtn-pending" class="f-btn px-4 py-1.5 rounded-full text-xs font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">Pending</button>
            </div>

            <!-- NOTIFICATIONS LIST GRID -->
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-12">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Alert Notification</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Module</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Priority</th>
                                <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Workflow Actions</th>
                            </tr>
                        </thead>
                        <tbody id="centerNotificationsListBody" class="divide-y divide-slate-200 dark:divide-slate-700">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: REJECT TASK -->
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

    <!-- MODAL: REJECTION REVIEW VERIFICATION -->
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

    <!-- MODAL: REQUEST CLARIFICATION -->
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

    <!-- Scripts -->
    <script>
        // Init Lucide
        lucide.createIcons();

        // Constants
        const USER_ID = <?= $userId ?>;
        const USER_NAME = "<?= htmlspecialchars($sName) ?>";
        const ROLE_NAME = "<?= htmlspecialchars($sRole) ?>";
        const IS_L1 = <?= $isL1 ? 'true' : 'false' ?>;

        let activeCenterTab = 'all';
        let activeStatusFilter = 'all';
        let loadedNotificationsList = [];
        let lastUnreadCount = 0;

        // Document Load
        window.addEventListener('DOMContentLoaded', () => {
            loadCenterNotifications();
            fetchNotifications(); // load header bell
        });

        // Theme Switcher Logic
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        function applyTheme(dark) {
            if (dark) {
                htmlElement.classList.add('dark');
                htmlElement.classList.remove('light');
            } else {
                htmlElement.classList.remove('dark');
                htmlElement.classList.add('light');
            }
            localStorage.setItem('acTheme', dark ? 'dark' : 'light');
        }

        const stored = localStorage.getItem('acTheme');
        const prefersDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark);

        themeToggle.addEventListener('click', () => {
            applyTheme(!htmlElement.classList.contains('dark'));
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                const gone = sidebar.classList.contains('-translate-x-full') || sidebar.style.display === 'none';
                if (gone) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.style.display = 'flex';
                } else {
                    sidebar.classList.add('-translate-x-full');
                    setTimeout(() => sidebar.style.display = 'none', 300);
                }
            });
        }

        // Dropdown toggle
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

        // HTML5 Audio chime alert (no assets needed)
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

        // Header Bell Fetch Notifications
        function fetchNotifications() {
            fetch('api/get_notifications.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Play chime if unread count increases
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
                                    if (isUnread) {
                                        markAsRead(n.id);
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

        function markAsRead(id) {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: id })
            }).then(() => {
                fetchNotifications();
                loadCenterNotifications();
            });
        }

        function markAllAsRead() {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mark_all: true })
            }).then(() => {
                fetchNotifications();
                loadCenterNotifications();
            });
        }

        // ==============================================================
        // CORE NOTIFICATION CENTER TABS & FILTERS
        // ==============================================================
        function switchCenterTab(tabId) {
            document.querySelectorAll('.center-tab-btn').forEach(btn => {
                btn.classList.remove('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
                btn.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
            });
            const activeBtn = document.getElementById('cbtn-' + tabId);
            if (activeBtn) {
                activeBtn.classList.add('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
                activeBtn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
            }
            activeCenterTab = tabId;
            renderCenterNotifications();
        }

        function setStatusFilter(filterVal) {
            document.querySelectorAll('.f-btn').forEach(btn => {
                btn.classList.remove('bg-navy-500', 'text-white');
                btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'border', 'border-slate-200', 'dark:border-slate-700');
            });
            const activeBtn = document.getElementById('fbtn-' + filterVal);
            if (activeBtn) {
                activeBtn.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-300', 'border', 'border-slate-200', 'dark:border-slate-700');
                activeBtn.classList.add('bg-navy-500', 'text-white');
            }
            activeStatusFilter = filterVal;
            renderCenterNotifications();
        }

        function loadCenterNotifications() {
            fetch('api/get_notifications.php')
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadedNotificationsList = data.notifications || [];
                        renderCenterNotifications();
                    }
                });
        }

        function renderCenterNotifications() {
            const body = document.getElementById('centerNotificationsListBody');
            body.innerHTML = '';
            
            // Filter list based on center tab and status filter
            let list = [...loadedNotificationsList];
            
            // 1. Tab Categories
            if (activeCenterTab === 'task') {
                list = list.filter(n => n.type === 'Task');
            } else if (activeCenterTab === 'meeting') {
                list = list.filter(n => n.type === 'Meeting');
            } else if (activeCenterTab === 'annc') {
                list = list.filter(n => n.type === 'Announcement');
            } else if (activeCenterTab === 'verify_req') {
                list = list.filter(n => n.type === 'Task' && n.task_status === 'Pending Verification');
            } else if (activeCenterTab === 'approve_req') {
                list = list.filter(n => n.type === 'Task' && ['Completed', 'Pending Verification'].includes(n.task_status));
            } else if (activeCenterTab === 'alerts') {
                list = list.filter(n => ['Alert', 'System', 'Clarification Request'].includes(n.type) || !['Task', 'Meeting', 'Announcement'].includes(n.type));
            }

            // 2. Status Filters
            if (activeStatusFilter === 'unread') {
                list = list.filter(n => n.is_read == 0);
            } else if (activeStatusFilter === 'read') {
                list = list.filter(n => n.is_read == 1);
            } else if (activeStatusFilter === 'accepted') {
                list = list.filter(n => n.task_status === 'Accepted');
            } else if (activeStatusFilter === 'rejected') {
                list = list.filter(n => ['Rejected', 'Pending Verification', 'Approved Rejection'].includes(n.task_status));
            } else if (activeStatusFilter === 'pending') {
                list = list.filter(n => ['Pending', 'Assigned', 'Reassigned'].includes(n.task_status));
            }

            if (list.length === 0) {
                body.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">No matching notifications in this view.</td></tr>`;
                return;
            }

            list.forEach(n => {
                const tr = document.createElement('tr');
                const readStyle = n.is_read == 0 ? 'bg-blue-50/10 dark:bg-slate-800/40 font-semibold border-l-4 border-blue-500' : 'border-l-4 border-transparent';
                tr.className = `hover:bg-slate-100/50 dark:hover:bg-slate-700/30 transition-colors ${readStyle}`;

                // Action Buttons Generator
                let actionsHtml = '';
                if (n.actions && n.actions.length > 0) {
                    n.actions.forEach(act => {
                        if (act.action === 'accept') {
                            actionsHtml += `<button onclick="acceptTask(${n.task_id}, ${n.id})" class="px-3 py-1.5 rounded text-xs font-bold bg-govgreen-500 text-white hover:bg-govgreen-600 transition-colors mr-2">Accept</button>`;
                        } else if (act.action === 'reject') {
                            actionsHtml += `<button onclick="openRejectTaskModal(${n.task_id})" class="px-3 py-1.5 rounded text-xs font-bold bg-red-500 text-white hover:bg-red-650 transition-colors mr-2">Reject</button>`;
                        } else if (act.action === 'verify_rejection') {
                            actionsHtml += `<button onclick="openReviewRejectionModal(${n.task_id})" class="px-3 py-1.5 rounded text-xs font-bold bg-navy-500 text-white hover:bg-navy-600 transition-colors mr-2">Verify Rejection</button>`;
                        } else if (act.action === 'verify_completion') {
                            actionsHtml += `<button onclick="verifyCompletion(${n.task_id}, ${n.id})" class="px-3 py-1.5 rounded text-xs font-bold bg-purple-500 text-white hover:bg-purple-650 transition-colors mr-2">Verify Completion</button>`;
                        }
                    });
                }
                
                // Read/Unread toggler
                if (n.is_read == 0) {
                    actionsHtml += `<button onclick="markAsRead(${n.id})" class="px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-500 rounded text-xs font-semibold hover:bg-slate-100" title="Mark as Read">Read</button>`;
                }

                tr.innerHTML = `
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">${n.title}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">${n.message}</div>
                        <div class="text-[10px] text-slate-400 mt-1">Sender: <span class="font-semibold text-slate-500">${n.sender_name}</span></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-500 dark:text-slate-350 uppercase">${n.type}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full ${n.badge_color}">${n.priority}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">${n.time_elapsed}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">${actionsHtml}</td>
                `;
                body.appendChild(tr);
            });
            lucide.createIcons();
        }

        // ==============================================================
        // TASK WORKFLOW WORK ACTIONS
        // ==============================================================
        function acceptTask(taskId, notifId) {
            fetch('api/task_notification_actions.php?action=accept&task_id=' + taskId)
                .then(r => r.json())
                .then(res => {
                    alert(res.message);
                    if (res.status === 'success') {
                        if (notifId) markAsRead(notifId);
                        loadCenterNotifications();
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
                    loadCenterNotifications();
                }
            })
            .catch(() => alert('Network error submitting rejection. Ensure remarks and file upload size matches.'));
        }

        function openReviewRejectionModal(taskId) {
            document.getElementById('reviewTaskId').value = taskId;
            
            // Load rejection details via API
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
                        loadCenterNotifications();
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
                    loadCenterNotifications();
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
                        loadCenterNotifications();
                    }
                });
        }

        // Poll notifications every 5 seconds for live dashboard updates
        setInterval(() => {
            fetchNotifications();
            loadCenterNotifications();
        }, 5000);
    </script>
</body>
</html>
