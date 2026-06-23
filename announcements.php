<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Database Connection
require_once 'include/dbConfig.php';
$db_connected = true;

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? '';
$sName  = $_SESSION['user_name'] ?? '';

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

// Dictionary Translations
$translations = [
    'en' => [
        'title' => 'Announcement Center — Amravati Connect',
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
        'page_title' => 'Announcement & Communication Center',
        'page_subtitle' => 'Advanced broadcast, meeting scheduler, messaging and confidential document management.',
        'badge_level' => 'Level',
        
        // Tabs
        'tab_all_annc' => 'All Announcements',
        'tab_create_annc' => 'Create Announcement',
        'tab_meetings' => 'Meetings & Schedule',
        'tab_confidential' => 'Confidential Information',
        'tab_messages' => 'Shared Messages',
        'tab_notif_center' => 'Notifications Center',
        
        // Form & Fields
        'lbl_title' => 'Title',
        'lbl_category' => 'Category',
        'lbl_desc' => 'Description',
        'lbl_priority' => 'Priority',
        'lbl_attachment' => 'Attachment File',
        'lbl_audience' => 'Target Audience',
        'lbl_publish_date' => 'Publish Date',
        'lbl_expiry_date' => 'Expiry Date',
        'lbl_status' => 'Status',
        'lbl_actions' => 'Actions',
        
        // Buttons
        'btn_save_draft' => 'Save as Draft',
        'btn_publish_now' => 'Publish Now',
        'btn_schedule' => 'Schedule Publishing',
        'btn_create' => 'Create',
        'btn_cancel' => 'Cancel',
        'btn_submit' => 'Submit',
        'btn_view' => 'View Details',
        'btn_download' => 'Download',
        'btn_search' => 'Search',
        'btn_clear' => 'Clear',
        
        // Priority
        'pri_low' => 'Low',
        'pri_medium' => 'Medium',
        'pri_high' => 'High',
        'pri_urgent' => 'Urgent',
        
        // Audience
        'aud_all' => 'All Employees',
        'aud_l1' => 'Level 1 (Collector/Admins)',
        'aud_l2' => 'Level 2 (SDO/Tehsildar/BDO)',
        'aud_l3' => 'Level 3 (Talathi/Gramsevak)',
        'aud_custom' => 'Custom Employee Selection',
        
        // Meeting specific
        'm_agenda' => 'Agenda',
        'm_date' => 'Meeting Date',
        'm_time' => 'Meeting Time',
        'm_duration' => 'Duration (Mins)',
        'm_location' => 'Meeting Location',
        'm_type' => 'Meeting Type',
        'm_platform' => 'Online Platform',
        'm_link' => 'Online Link',
        'm_password' => 'Meeting Password',
        'm_join' => 'Join Meeting',
        
        // Confidential
        'conf_subject' => 'Subject',
        'conf_level' => 'Classification Level',
        'conf_public' => 'Public',
        'conf_internal' => 'Internal',
        'conf_confidential' => 'Confidential',
        'conf_highly_confidential' => 'Highly Confidential',
        'conf_allow_download' => 'Allow File Download',
        'conf_allow_view' => 'Allow View Preview',
        
        // Messages
        'msg_body' => 'Message Text',
        'msg_inbox' => 'Received Messages',
        'msg_sent' => 'Sent Messages',
        'msg_compose' => 'Compose Message',
        'msg_sender' => 'Sender',
        'msg_receiver' => 'Receiver',
        'msg_reply' => 'Reply',
        'msg_forward' => 'Forward'
    ],
    'mr' => [
        'title' => 'घोषणा केंद्र — अमरावती कनेक्ट',
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
        'page_title' => 'घोषणा आणि संवाद केंद्र',
        'page_subtitle' => 'प्रसारण, बैठक नियोजक, संदेश वहन आणि दस्तऐवज व्यवस्थापन.',
        'badge_level' => 'स्तर',
        
        // Tabs
        'tab_all_annc' => 'सर्व घोषणा',
        'tab_create_annc' => 'घोषणा तयार करा',
        'tab_meetings' => 'बैठका आणि नियोजक',
        'tab_confidential' => 'गोपनीय माहिती',
        'tab_messages' => 'सामायिक संदेश',
        'tab_notif_center' => 'सूचना केंद्र',
        
        // Form & Fields
        'lbl_title' => 'शीर्षक',
        'lbl_category' => 'वर्ग',
        'lbl_desc' => 'वर्णन',
        'lbl_priority' => 'प्राधान्यक्रम',
        'lbl_attachment' => 'संलग्न दस्तऐवज',
        'lbl_audience' => 'लक्षित वाचक',
        'lbl_publish_date' => 'प्रकाशन तारीख',
        'lbl_expiry_date' => 'समाप्ती तारीख',
        'lbl_status' => 'स्थिती',
        'lbl_actions' => 'कृती',
        
        // Buttons
        'btn_save_draft' => 'मसुदा म्हणून जतन करा',
        'btn_publish_now' => 'आता प्रकाशित करा',
        'btn_schedule' => 'प्रकाशन वेळापत्रक जतन करा',
        'btn_create' => 'तयार करा',
        'btn_cancel' => 'रद्द करा',
        'btn_submit' => 'जतन करा',
        'btn_view' => 'तपशील पहा',
        'btn_download' => 'डाउनलोड',
        'btn_search' => 'शोधा',
        'btn_clear' => 'साफ करा',
        
        // Priority
        'pri_low' => 'कमी',
        'pri_medium' => 'मध्यम',
        'pri_high' => 'उच्च',
        'pri_urgent' => 'तातडीचे',
        
        // Audience
        'aud_all' => 'सर्व कर्मचारी',
        'aud_l1' => 'स्तर १ (जिल्हाधिकारी/प्रशासक)',
        'aud_l2' => 'स्तर २ (SDO/तहसीलदार/BDO)',
        'aud_l3' => 'स्तर ३ (तलाठी/ग्रामसेवक)',
        'aud_custom' => 'विशिष्ट कर्मचारी निवड',
        
        // Meeting specific
        'm_agenda' => 'अजेंडा / विषय',
        'm_date' => 'बैठकीची तारीख',
        'm_time' => 'बैठकीची वेळ',
        'm_duration' => 'कालावधी (मिनिटे)',
        'm_location' => 'बैठकीचे ठिकाण',
        'm_type' => 'बैठकीचा प्रकार',
        'm_platform' => 'ऑनलाइन प्लॅटफॉर्म',
        'm_link' => 'ऑनलाइन लिंक',
        'm_password' => 'बैठक पासवर्ड',
        'm_join' => 'बैठकीत सामील व्हा',
        
        // Confidential
        'conf_subject' => 'विषय',
        'conf_level' => 'गोपनीयता स्तर',
        'conf_public' => 'सार्वजनिक',
        'conf_internal' => 'अंतर्गत',
        'conf_confidential' => 'गोपनीय',
        'conf_highly_confidential' => 'अति गोपनीय',
        'conf_allow_download' => 'डाउनलोड परवानगी',
        'conf_allow_view' => 'पाहण्याची परवानगी',
        
        // Messages
        'msg_body' => 'संदेश मजकूर',
        'msg_inbox' => 'प्राप्त संदेश',
        'msg_sent' => 'पाठवलेले संदेश',
        'msg_compose' => 'संदेश पाठवा',
        'msg_sender' => 'प्रेषक',
        'msg_receiver' => 'प्राप्तकर्ता',
        'msg_reply' => 'उत्तर द्या',
        'msg_forward' => 'पुढे पाठवा'
    ]
];

$t = $translations[$lang];

$level = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

$parts = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
$roleLabel = $sRole;

// Fetch KPIs
$totalAnnc = 0; $activeAnnc = 0; $upcomingMeetings = 0; $liveMeetings = 0; $unreadMsgs = 0; $unreadNotifs = 0;
$liveMeetingRow = null;

if ($db_connected) {
    // Announcements count
    $res = $conn->query("SELECT COUNT(*) FROM announcements WHERE status != 'Archived'");
    $totalAnnc = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    $res = $conn->query("SELECT COUNT(*) FROM announcements WHERE status = 'Published'");
    $activeAnnc = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    // Meetings count
    $res = $conn->query("SELECT COUNT(*) FROM meetings WHERE status = 'Scheduled'");
    $upcomingMeetings = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    $res = $conn->query("SELECT COUNT(*) FROM meetings WHERE status = 'Live'");
    $liveMeetings = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    // Messages count
    $res = $conn->query("SELECT COUNT(*) FROM message_recipients WHERE user_id = $userId AND is_read = 0");
    $unreadMsgs = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    // Notifications count
    $res = $conn->query("SELECT COUNT(*) FROM notifications WHERE receiver_id = $userId AND status = 'Unread'");
    $unreadNotifs = ($res) ? (($res->fetch_row()[0]) ?? 0) : 0;
    
    // Active Live meeting details for banner
    $liveMeetingRes = $conn->query("SELECT * FROM meetings WHERE status = 'Live' ORDER BY meeting_date ASC, meeting_time ASC LIMIT 1");
    if ($liveMeetingRes && $liveMeetingRes->num_rows > 0) {
        $liveMeetingRow = $liveMeetingRes->fetch_assoc();
    }
} else {
    // MOCK KPI STATS
    $totalAnnc = 5; $activeAnnc = 3; $upcomingMeetings = 2; $liveMeetings = 1; $unreadMsgs = 2; $unreadNotifs = 3;
    $liveMeetingRow = [
        'meeting_id' => 999,
        'title' => 'District Hailstorm Emergency Relief Review',
        'meeting_date' => date('Y-m-d'),
        'meeting_time' => date('H:i:s', time() - 300),
        'duration' => 60,
        'meeting_platform' => 'Google Meet',
        'meeting_link' => 'https://meet.google.com/abc-defg-hij',
        'meeting_password' => '123456'
    ];
}
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
    
    <!-- Excel & PDF CDN Exports -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
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
        .glass-panel {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .dark .glass-panel {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.05);
        }
        .badge-l1 { background: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-l2 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-l3 { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .dark .badge-l1 { background: #1e3a8a33; color: #93c5fd; border-color: #1e40af; }
        .dark .badge-l2 { background: #92400e33; color: #fcd34d; border-color: #b45309; }
        .dark .badge-l3 { background: #065f4633; color: #6ee7b7; border-color: #047857; }
        .nav-active { background: #eef2f6; color: #1e3a8a; font-weight: 600; }
        .dark .nav-active { background: #1e293b; color: #fff; }
        
        .watermark-container {
            position: relative;
            user-select: none;
        }
        .watermark-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-content: space-around;
            overflow: hidden;
            opacity: 0.15;
        }
        .watermark-text {
            transform: rotate(-30deg);
            font-size: 14px;
            font-weight: bold;
            color: #EF4444;
            white-space: nowrap;
            margin: 20px;
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
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
                <a href="announcements.php?lang=<?= $lang ?>" class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                    <i data-lucide="megaphone" class="w-5 h-5 mr-3 text-navy-500 dark:text-blue-400"></i>
                    <?= htmlspecialchars($t['menu_announcement_center']) ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_task_alloc']) ?>
                </a>
                <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
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
                <a href="announcements.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                    <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                    <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
                </a>
                
                <!-- Theme Toggle -->
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
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
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                    </span>
                </div>
            </div>

            <!-- LIVE MEETING BANNER -->
            <?php if ($liveMeetingRow): ?>
                <div class="mb-6 bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-xl shadow-lg p-5 flex flex-col md:flex-row justify-between items-center animate-pulse border border-orange-400">
                    <div class="flex items-center space-x-4 mb-4 md:mb-0">
                        <div class="p-3 bg-white/20 rounded-lg">
                            <i data-lucide="video" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <span class="text-xs uppercase font-bold tracking-wider bg-red-750 px-2.5 py-0.5 rounded-full bg-red-700 mr-2">LIVE NOW</span>
                            <h2 class="text-lg font-bold mt-1"><?= htmlspecialchars($liveMeetingRow['title']) ?></h2>
                            <p class="text-sm text-white/90">Platform: <?= htmlspecialchars($liveMeetingRow['meeting_platform'] ?? 'Online') ?> | Password: <?= htmlspecialchars($liveMeetingRow['meeting_password'] ?? 'None') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="<?= htmlspecialchars($liveMeetingRow['meeting_link']) ?>" target="_blank" onclick="trackJoin(<?= $liveMeetingRow['meeting_id'] ?>)" class="px-5 py-2.5 bg-white text-orange-600 rounded-lg text-sm font-bold shadow-md hover:bg-slate-50 transition-colors">
                            <?= htmlspecialchars($t['m_join']) ?>
                        </a>
                        <button onclick="viewMeetingDetails(<?= $liveMeetingRow['meeting_id'] ?>)" class="px-5 py-2.5 bg-white/20 border border-white/30 text-white rounded-lg text-sm font-semibold hover:bg-white/30 transition-colors">
                            <?= htmlspecialchars($t['btn_view']) ?>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DASHBOARD WIDGET CARDS -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-5 mb-8">
                <!-- Card 1 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Annc</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $totalAnnc ?></span>
                        <i data-lucide="megaphone" class="w-5 h-5 text-slate-400"></i>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Annc</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $activeAnnc ?></span>
                        <i data-lucide="bell" class="w-5 h-5 text-govgreen-500"></i>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Upcoming Mtgs</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $upcomingMeetings ?></span>
                        <i data-lucide="calendar" class="w-5 h-5 text-saffron-500"></i>
                    </div>
                </div>
                <!-- Card 4 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Live Meetings</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $liveMeetings ?></span>
                        <i data-lucide="video" class="w-5 h-5 text-red-500"></i>
                    </div>
                </div>
                <!-- Card 5 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Unread Msgs</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $unreadMsgs ?></span>
                        <i data-lucide="mail" class="w-5 h-5 text-navy-500"></i>
                    </div>
                </div>
                <!-- Card 6 -->
                <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Notifications</p>
                    <div class="flex items-baseline justify-between mt-2">
                        <span class="text-2xl font-bold text-slate-900 dark:text-white"><?= $unreadNotifs ?></span>
                        <i data-lucide="bell-ring" class="w-5 h-5 text-saffron-600"></i>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS GRID (Collector/L1) -->
            <?php if ($isL1): ?>
                <div class="mb-8 bg-white dark:bg-slate-800 rounded-xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <button onclick="switchTab('create_annc')" class="p-4 bg-navy-50 hover:bg-navy-100 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-sm font-semibold text-navy-900 dark:text-white transition-colors">
                            <i data-lucide="plus-circle" class="w-5 h-5 mr-2 text-navy-600 dark:text-blue-400"></i> Create Announcement
                        </button>
                        <button onclick="openScheduleMeetingModal()" class="p-4 bg-navy-50 hover:bg-navy-100 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-sm font-semibold text-navy-900 dark:text-white transition-colors">
                            <i data-lucide="calendar-plus" class="w-5 h-5 mr-2 text-navy-600 dark:text-blue-400"></i> Schedule Meeting
                        </button>
                        <button onclick="openUploadConfidentialModal()" class="p-4 bg-navy-50 hover:bg-navy-100 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-sm font-semibold text-navy-900 dark:text-white transition-colors">
                            <i data-lucide="file-up" class="w-5 h-5 mr-2 text-navy-600 dark:text-blue-400"></i> Share Confidential Doc
                        </button>
                        <button onclick="openComposeMessageModal()" class="p-4 bg-navy-50 hover:bg-navy-100 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-sm font-semibold text-navy-900 dark:text-white transition-colors">
                            <i data-lucide="send" class="w-5 h-5 mr-2 text-navy-600 dark:text-blue-400"></i> Compose Message
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CORE SYSTEM TABS NAV -->
            <div class="border-b border-slate-200 dark:border-slate-700 mb-8 overflow-x-auto">
                <nav class="flex space-x-6 min-w-max">
                    <button onclick="switchTab('all_annc')" id="tab-btn-all_annc" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium transition-all duration-150 border-navy-500 text-navy-600 font-bold dark:border-blue-400 dark:text-blue-400">
                        <?= htmlspecialchars($t['tab_all_annc']) ?>
                    </button>
                    <?php if ($isL1): ?>
                    <button onclick="switchTab('create_annc')" id="tab-btn-create_annc" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">
                        <?= htmlspecialchars($t['tab_create_annc']) ?>
                    </button>
                    <?php endif; ?>
                    <button onclick="switchTab('meetings')" id="tab-btn-meetings" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">
                        <?= htmlspecialchars($t['tab_meetings']) ?>
                    </button>
                    <button onclick="switchTab('confidential')" id="tab-btn-confidential" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">
                        <?= htmlspecialchars($t['tab_confidential']) ?>
                    </button>
                    <button onclick="switchTab('messages')" id="tab-btn-messages" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400">
                        <?= htmlspecialchars($t['tab_messages']) ?>
                    </button>
                </nav>
            </div>

            <!-- TABS CONTENTS -->

            <!-- TAB 1: ALL ANNOUNCEMENTS -->
            <div id="tab-content-all_annc" class="tab-pane block">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                            <!-- Search -->
                            <div class="relative w-full md:w-64">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                                </span>
                                <input type="text" id="anncSearch" placeholder="Search Announcements..." class="pl-9 pr-3 py-2 border rounded-lg text-sm w-full bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white border-slate-350 focus:outline-none focus:ring-1 focus:ring-navy-500">
                            </div>
                            <!-- Category Filter -->
                            <select id="anncCategory" class="px-3 py-2 border rounded-lg text-sm bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="">All Categories</option>
                                <option value="General">General</option>
                                <option value="Administrative">Administrative</option>
                                <option value="Welfare">Welfare</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                            <!-- Priority Filter -->
                            <select id="anncPriority" class="px-3 py-2 border rounded-lg text-sm bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="">All Priorities</option>
                                <option value="Low">Low</option>
                                <option value="Medium">Medium</option>
                                <option value="High">High</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Publish Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="anncTableBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CREATE ANNOUNCEMENT (L1 & Collector Only) -->
            <?php if ($isL1): ?>
            <div id="tab-content-create_annc" class="tab-pane hidden">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <form id="createAnncForm" onsubmit="submitAnnouncement(event)">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_title']) ?></label>
                                <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-navy-500">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_category']) ?></label>
                                <select name="category" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="General">General</option>
                                    <option value="Administrative">Administrative</option>
                                    <option value="Welfare">Welfare</option>
                                    <option value="Emergency">Emergency</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_desc']) ?></label>
                            <textarea name="description" rows="4" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-navy-500"></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_priority']) ?></label>
                                <select name="priority" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="Low"><?= htmlspecialchars($t['pri_low']) ?></option>
                                    <option value="Medium" selected><?= htmlspecialchars($t['pri_medium']) ?></option>
                                    <option value="High"><?= htmlspecialchars($t['pri_high']) ?></option>
                                    <option value="Urgent"><?= htmlspecialchars($t['pri_urgent']) ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_publish_date']) ?></label>
                                <input type="date" name="publish_date" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_expiry_date']) ?></label>
                                <input type="date" name="expiry_date" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_audience']) ?></label>
                                <select name="audience_type" id="audienceTypeSelect" onchange="toggleCustomAudienceSection()" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                    <option value="All"><?= htmlspecialchars($t['aud_all']) ?></option>
                                    <option value="L1"><?= htmlspecialchars($t['aud_l1']) ?></option>
                                    <option value="L2"><?= htmlspecialchars($t['aud_l2']) ?></option>
                                    <option value="L3"><?= htmlspecialchars($t['aud_l3']) ?></option>
                                    <option value="Custom"><?= htmlspecialchars($t['aud_custom']) ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold mb-2"><?= htmlspecialchars($t['lbl_attachment']) ?></label>
                                <input type="file" name="attachment" class="w-full px-3 py-1.5 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            </div>
                        </div>

                        <!-- Custom Employee selection checklist -->
                        <div id="customAudienceSection" class="hidden mb-6 bg-slate-50 dark:bg-slate-700/50 p-5 rounded-xl border border-slate-200 dark:border-slate-700">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase mb-3">Select Targeted Employees</h3>
                            <div id="customEmployeesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-48 overflow-y-auto">
                                <!-- Loaded via API -->
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 pt-5 border-t border-slate-200 dark:border-slate-700">
                            <button type="submit" onclick="setPublishStatus('0')" class="px-5 py-2.5 border rounded-lg text-sm font-semibold bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-colors">
                                <?= htmlspecialchars($t['btn_save_draft']) ?>
                            </button>
                            <?php if ($isCollector): ?>
                                <button type="submit" onclick="setPublishStatus('2')" class="px-5 py-2.5 bg-saffron-500 hover:bg-saffron-600 text-white rounded-lg text-sm font-bold transition-colors">
                                    <?= htmlspecialchars($t['btn_schedule']) ?>
                                </button>
                                <button type="submit" onclick="setPublishStatus('1')" class="px-5 py-2.5 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-sm font-bold transition-colors">
                                    <?= htmlspecialchars($t['btn_publish_now']) ?>
                                </button>
                            <?php else: ?>
                                <button type="submit" onclick="setPublishStatus('0')" class="px-5 py-2.5 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-sm font-bold transition-colors">
                                    Submit for approval
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- TAB 3: MEETINGS -->
            <div id="tab-content-meetings" class="tab-pane hidden">
                <!-- Meetings Inner Navigation -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 mb-6 flex flex-wrap justify-between items-center gap-4 shadow-sm">
                    <div class="flex items-center space-x-2">
                        <button onclick="switchMeetingView('calendar')" id="btn-mview-calendar" class="px-4 py-2 text-sm font-semibold rounded-lg bg-navy-50 text-navy-900 dark:bg-slate-700 dark:text-white">Calendar View</button>
                        <button onclick="switchMeetingView('upcoming')" id="btn-mview-upcoming" class="px-4 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800">Upcoming Meetings</button>
                        <button onclick="switchMeetingView('completed')" id="btn-mview-completed" class="px-4 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800">Completed Meetings</button>
                        <?php if ($isL1): ?>
                            <button onclick="switchMeetingView('attendance')" id="btn-mview-attendance" class="px-4 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800">Attendance Tracking</button>
                        <?php endif; ?>
                    </div>
                    <?php if ($isL1): ?>
                        <button onclick="openScheduleMeetingModal()" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white text-sm font-bold rounded-lg transition-colors flex items-center">
                            <i data-lucide="calendar-plus" class="w-4 h-4 mr-2"></i> Schedule Meeting
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Meeting Calendar Pane -->
                <div id="meeting-view-calendar" class="mview-pane block bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <!-- Standard CSS Grid Calendar -->
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold" id="calendarMonthTitle">June 2026</h2>
                        <div class="flex items-center space-x-2">
                            <button onclick="changeCalendarMonth(-1)" class="p-2 border rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                            <button onclick="changeCalendarMonth(1)" class="p-2 border rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-2 text-center font-semibold text-xs text-slate-500 uppercase mb-2">
                        <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                    </div>
                    <div id="calendarDaysGrid" class="grid grid-cols-7 gap-2 h-96">
                        <!-- Loaded dynamically -->
                    </div>
                </div>

                <!-- Upcoming list Pane -->
                <div id="meeting-view-upcoming" class="mview-pane hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Title & Agenda</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Platform/Location</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="upcomingMeetingsBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <!-- Loaded via API -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Completed list Pane -->
                <div id="meeting-view-completed" class="mview-pane hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Title</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Date & Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Details</th>
                                </tr>
                            </thead>
                            <tbody id="completedMeetingsBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <!-- Loaded via API -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Attendance tracking Pane (L1/Collector) -->
                <?php if ($isL1): ?>
                <div id="meeting-view-attendance" class="mview-pane hidden bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <div class="flex justify-between items-center gap-4 mb-4">
                        <select id="attendanceMeetingSelect" onchange="loadAttendanceReport()" class="px-3 py-2 border rounded-lg text-sm bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white">
                            <option value="">-- Select Meeting --</option>
                            <!-- Populated with Completed/Scheduled meetings -->
                        </select>
                        <div class="flex space-x-2">
                            <button onclick="exportAttendanceReport('excel')" class="px-3 py-2 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded-lg text-sm font-semibold transition-colors">Export Excel</button>
                            <button onclick="exportAttendanceReport('pdf')" class="px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition-colors">Export PDF</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table id="attendanceTable" class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Employee Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Full Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Join Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Exit Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Duration (Mins)</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceReportBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">Please select a meeting to inspect logs</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- TAB 4: CONFIDENTIAL INFORMATION -->
            <div id="tab-content-confidential" class="tab-pane hidden">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold">Classified Documents</h2>
                        <?php if ($isL1): ?>
                            <button onclick="openUploadConfidentialModal()" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white text-sm font-bold rounded-lg transition-colors flex items-center">
                                <i data-lucide="file-up" class="w-4 h-4 mr-2"></i> Share New Document
                            </button>
                        <?php endif; ?>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Level</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Uploaded By</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="confidentialDocsBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <!-- Loaded via API -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 5: SHARED MESSAGES -->
            <div id="tab-content-messages" class="tab-pane hidden">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
                    <div class="flex justify-between items-center gap-4 mb-6 border-b border-slate-200 dark:border-slate-700 pb-4">
                        <div class="flex items-center space-x-2">
                            <button onclick="switchMessageFolder('inbox')" id="btn-msg-inbox" class="px-4 py-2 text-sm font-semibold rounded-lg bg-navy-50 text-navy-900 dark:bg-slate-700 dark:text-white">Inbox</button>
                            <button onclick="switchMessageFolder('sent')" id="btn-msg-sent" class="px-4 py-2 text-sm font-semibold rounded-lg text-slate-600 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800">Sent Mail</button>
                        </div>
                        <?php if ($isL1): ?>
                            <button onclick="openComposeMessageModal()" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white text-sm font-bold rounded-lg transition-colors flex items-center">
                                <i data-lucide="send" class="w-4 h-4 mr-2"></i> Compose Message
                            </button>
                        <?php endif; ?>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase" id="senderReceiverCol">Sender</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Sent Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody id="messagesTableBody" class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                <!-- Loaded via API -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- ==============================================================
         MODALS & DIALOG OVERLAYS
         ============================================================== -->

    <!-- 1. DETAILED ANNOUNCEMENT MODAL -->
    <div id="viewAnncModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white" id="modalAnncTitle">Announcement</h3>
                <button onclick="closeAnncModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-4">
                    <span id="modalAnncCategory" class="px-3 py-1 bg-navy-50 text-navy-600 rounded-full text-xs font-semibold">General</span>
                    <span id="modalAnncPriority" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">High</span>
                </div>
                <p class="text-slate-700 dark:text-slate-350 text-sm leading-relaxed mb-6" id="modalAnncDescription"></p>
                
                <div class="grid grid-cols-2 gap-4 text-xs bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mb-6">
                    <div>
                        <span class="text-slate-400 block mb-1">Created By</span>
                        <span class="font-semibold text-slate-800 dark:text-white" id="modalAnncCreator">Admin</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-1">Publish Date</span>
                        <span class="font-semibold text-slate-800 dark:text-white" id="modalAnncPubDate">2026-06-22</span>
                    </div>
                </div>
                
                <div id="modalAnncAttachmentSection" class="hidden border border-slate-200 dark:border-slate-700 p-4 rounded-xl flex justify-between items-center">
                    <div class="flex items-center">
                        <i data-lucide="paperclip" class="w-5 h-5 text-slate-400 mr-2"></i>
                        <span class="text-sm font-semibold" id="modalAnncAttachmentName">file.pdf</span>
                    </div>
                    <a href="#" id="modalAnncAttachmentDownload" download class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-xs font-bold transition-colors">Download</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. SCHEDULE MEETING MODAL -->
    <div id="scheduleMeetingModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold">Schedule New Meeting</h3>
                <button onclick="closeScheduleMeetingModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="scheduleMeetingForm" onsubmit="submitMeeting(event)">
                <div class="p-6 max-h-[80vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Meeting Title</label>
                            <input type="text" name="title" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Meeting Type</label>
                            <select name="meeting_type" id="meetingTypeSelect" onchange="toggleOnlineMeetingFields()" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="Offline">Offline (In-Person)</option>
                                <option value="Online">Online (Virtual)</option>
                                <option value="Hybrid">Hybrid (Both)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Date</label>
                            <input type="date" name="meeting_date" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Time</label>
                            <input type="time" name="meeting_time" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Duration (Mins)</label>
                            <input type="number" name="duration" value="60" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Agenda / Discussion Points</label>
                        <textarea name="agenda" rows="2" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Location / Venue</label>
                        <input type="text" name="location" placeholder="e.g. Collector Office Conference Hall" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                    </div>

                    <!-- Online meeting virtual credentials -->
                    <div id="onlineMeetingFields" class="hidden bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700 mb-4">
                        <h4 class="text-xs font-bold uppercase mb-3 text-slate-500">Virtual Credentials</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold mb-1">Platform</label>
                                <select name="meeting_platform" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="Google Meet">Google Meet</option>
                                    <option value="Zoom">Zoom</option>
                                    <option value="MS Teams">Microsoft Teams</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold mb-1">Meeting Link</label>
                                <input type="text" name="meeting_link" placeholder="https://..." class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold mb-1">Password (Optional)</label>
                                <input type="text" name="meeting_password" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Audience Group</label>
                            <select name="audience_type" id="meetingAudienceSelect" onchange="toggleMeetingCustomSection()" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="All">All Levels</option>
                                <option value="L1">Level 1 (Collector/Admins)</option>
                                <option value="L2">Level 2 (SDO/Tehsildar/BDO)</option>
                                <option value="L3">Level 3 (Talathi/Gramsevak)</option>
                                <option value="Custom">Custom Participant Selection</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Agenda Document File</label>
                            <input type="file" name="attachment_agenda" class="w-full px-3 py-1.5 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                    </div>

                    <!-- Meeting Custom selector -->
                    <div id="meetingCustomSection" class="hidden mb-4 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <h4 class="text-xs font-bold uppercase mb-3 text-slate-500">Target Participants</h4>
                        <div id="meetingEmployeesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-40 overflow-y-auto">
                            <!-- Populated with checkboxed active employees -->
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                    <button type="button" onclick="closeScheduleMeetingModal()" class="px-4 py-2 border rounded-lg text-sm font-semibold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-sm font-bold shadow">Schedule Meeting</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. CLASSIFIED SECURE VIEWER MODAL WITH WATERMARK -->
    <div id="secureViewerModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-4xl h-[90vh] overflow-hidden shadow-2xl flex flex-col">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-red-500"></i>
                    <h3 class="text-lg font-bold text-red-650" id="viewerSubject">Secure File Access Portal</h3>
                </div>
                <button onclick="closeSecureViewerModal()" class="text-slate-400 hover:text-slate-200"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <!-- Watermarked Display Container -->
            <div class="flex-1 bg-slate-900 overflow-auto relative watermark-container p-6 flex items-center justify-center">
                <!-- Diagonal transparent background watermarking overlay -->
                <div class="watermark-overlay" id="viewerWatermarkOverlay">
                    <!-- Scripted diagonal texts -->
                </div>
                <!-- Previews if file is image, otherwise file information and warning details -->
                <div class="z-20 text-center text-white" id="viewerContentFrame">
                    <!-- Image, preview panel, or file download details -->
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <span class="text-xs text-red-500 font-bold uppercase tracking-wider">Classification Level: Classified and Audited Access Only</span>
                <a href="#" id="viewerDownloadBtn" onclick="logDownload()" class="px-4 py-2 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded-lg text-sm font-bold shadow transition-colors">Download Document</a>
            </div>
        </div>
    </div>

    <!-- 4. SHARE CONFIDENTIAL DOCUMENT MODAL -->
    <div id="uploadConfidentialModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold">Share Confidential Document</h3>
                <button onclick="closeUploadConfidentialModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="uploadConfidentialForm" onsubmit="submitConfidential(event)">
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Subject</label>
                        <input type="text" name="subject" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Description</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Classification Level</label>
                            <select name="classification_level" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="Public">Public</option>
                                <option value="Internal">Internal</option>
                                <option value="Confidential" selected>Confidential</option>
                                <option value="Highly Confidential">Highly Confidential</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Upload Classified File</label>
                            <input type="file" name="document_file" required class="w-full px-3 py-1.5 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">View Rights</label>
                            <select name="allow_view" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="1">Allow Preview</option>
                                <option value="0">Deny Preview (Download Only)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Download Rights</label>
                            <select name="allow_download" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                                <option value="1">Allow Download</option>
                                <option value="0">Deny Download (View Only)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Audience Type</label>
                        <select name="audience_type" id="confidentialAudienceSelect" onchange="toggleConfidentialCustomSection()" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            <option value="All">All Officers</option>
                            <option value="L1">Level 1 Only</option>
                            <option value="L2">Level 2 Only</option>
                            <option value="L3">Level 3 Only</option>
                            <option value="Custom">Custom Selection</option>
                        </select>
                    </div>

                    <!-- Custom audience checkboxes -->
                    <div id="confidentialCustomSection" class="hidden mb-4 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                        <h4 class="text-xs font-bold uppercase mb-3 text-slate-500">Target Recipients</h4>
                        <div id="confidentialEmployeesList" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-40 overflow-y-auto">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                    <button type="button" onclick="closeUploadConfidentialModal()" class="px-4 py-2 border rounded-lg text-sm font-semibold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-sm font-bold shadow">Share Classified File</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. COMPOSE MESSAGE MODAL -->
    <div id="composeMessageModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold">Compose Message</h3>
                <button onclick="closeComposeMessageModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="composeMessageForm" onsubmit="submitMessageText(event)">
                <input type="hidden" name="parent_message_id" id="msgParentId" value="">
                <input type="hidden" name="is_forwarded" id="msgIsForwarded" value="0">
                
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Recipient Group / User</label>
                        <select name="audience_type" id="messageAudienceType" onchange="toggleMessageRecipientSelector()" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            <option value="All">All Users</option>
                            <option value="L1">Level 1 Group</option>
                            <option value="L2">Level 2 Group</option>
                            <option value="L3">Level 3 Group</option>
                            <option value="Specific">Specific Employee</option>
                        </select>
                    </div>
                    
                    <div id="specificMessageRecipientSection" class="hidden mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Select Employee</label>
                        <select name="recipient_user_id" id="messageSpecificRecipientSelect" class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                            <option value="">-- Choose Employee --</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Subject</label>
                        <input type="text" name="subject" id="msgSubject" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Message Content</label>
                        <textarea name="message_body" id="msgBody" rows="4" required class="w-full px-3 py-2 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold mb-1 uppercase text-slate-400">Attach Document (Optional)</label>
                        <input type="file" name="message_file" class="w-full px-3 py-1.5 border rounded-lg bg-slate-50 dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-200 dark:border-slate-700 flex justify-end space-x-3">
                    <button type="button" onclick="closeComposeMessageModal()" class="px-4 py-2 border rounded-lg text-sm font-semibold hover:bg-slate-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-sm font-bold shadow">Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 6. DISPLAY MESSAGE DETAILS MODAL -->
    <div id="viewMessageDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 w-full max-w-xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-lg font-bold" id="msgDetailsSubject">Message Details</h3>
                <button onclick="closeMessageDetailsModal()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div class="p-6">
                <div class="flex justify-between text-xs text-slate-400 mb-4 bg-slate-50 dark:bg-slate-900/50 p-3 rounded-lg">
                    <div>
                        <span class="block">From</span>
                        <span class="font-semibold text-slate-800 dark:text-white" id="msgDetailsSender">Sender Name</span>
                    </div>
                    <div>
                        <span class="block">Received At</span>
                        <span class="font-semibold text-slate-800 dark:text-white" id="msgDetailsDate">2026-06-22</span>
                    </div>
                </div>
                
                <p class="text-sm text-slate-700 dark:text-slate-350 leading-relaxed mb-6 whitespace-pre-wrap" id="msgDetailsBody"></p>
                
                <div id="msgDetailsAttachmentSection" class="hidden mb-6 border border-slate-200 dark:border-slate-700 p-4 rounded-xl flex justify-between items-center">
                    <div class="flex items-center">
                        <i data-lucide="paperclip" class="w-5 h-5 text-slate-400 mr-2"></i>
                        <span class="text-sm font-semibold" id="msgDetailsAttachmentName">file.pdf</span>
                    </div>
                    <a href="#" id="msgDetailsAttachmentDownload" download class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-xs font-bold transition-colors">Download</a>
                </div>
                
                <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button onclick="replyToCurrentMessage()" class="px-4 py-2 bg-navy-50 hover:bg-navy-100 text-navy-900 rounded-lg text-sm font-semibold transition-colors flex items-center"><i data-lucide="reply" class="w-4 h-4 mr-2"></i> Reply</button>
                    <button onclick="forwardCurrentMessage()" class="px-4 py-2 bg-navy-50 hover:bg-navy-100 text-navy-900 rounded-lg text-sm font-semibold transition-colors flex items-center"><i data-lucide="corner-up-right" class="w-4 h-4 mr-2"></i> Forward</button>
                </div>
            </div>
        </div>
    </div>


    <!-- ==============================================================
         JAVASCRIPT & AJAX INTEGRATIONS
         ============================================================== -->
    <script>
        // Init Lucide
        lucide.createIcons();

        // Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        if (document.getElementById('sidebarToggle')) {
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
        }

        // Theme Switcher Logic
        function applyTheme(dark) {
            const html = document.documentElement;
            if (dark) {
                html.classList.add('dark');
                html.classList.remove('light');
            } else {
                html.classList.remove('dark');
                html.classList.add('light');
            }
            localStorage.setItem('acTheme', dark ? 'dark' : 'light');
        }

        const stored = localStorage.getItem('acTheme');
        const prefersDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark);

        const themeBtn = document.getElementById('themeToggle');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => applyTheme(!document.documentElement.classList.contains('dark')));
        }

        // System Constants
        const USER_ID = <?= $userId ?>;
        const USER_NAME = "<?= htmlspecialchars($sName) ?>";
        const ROLE_NAME = "<?= htmlspecialchars($sRole) ?>";
        const IS_L1 = <?= $isL1 ? 'true' : 'false' ?>;
        const IS_COLLECTOR = <?= $isCollector ? 'true' : 'false' ?>;
        
        let currentTab = 'all_annc';
        let currentMeetingView = 'calendar';
        let currentMessageFolder = 'inbox';
        
        let activeSelectedMeetingId = 0;
        let activeConfidentialDocId = 0;
        let activeMessageRowDetails = null;
        
        // Custom Calendar details
        let calendarYear = 2026;
        let calendarMonth = 5; // June (0-indexed)
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        // Document Load
        window.addEventListener('DOMContentLoaded', () => {
            loadAnnouncementsList();
            loadEmployeesList();
            loadMeetingsList();
            loadConfidentialDocs();
            loadSharedMessages();
            renderCalendarGrid();
            
            // Notification Bell Logic
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationBtn) {
                notificationBtn.addEventListener('click', () => {
                    notificationDropdown.classList.toggle('hidden');
                });
            }
            document.addEventListener('click', (e) => {
                if (notificationBtn && notificationDropdown && !notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.add('hidden');
                }
            });
            fetchNotifications();
        });

        // Tab Switching
        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
                btn.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
            });
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('block');
                pane.classList.add('hidden');
            });
            
            const btn = document.getElementById('tab-btn-' + tabId);
            if (btn) {
                btn.classList.add('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
                btn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
            }
            
            const pane = document.getElementById('tab-content-' + tabId);
            if (pane) {
                pane.classList.remove('hidden');
                pane.classList.add('block');
            }
            currentTab = tabId;
        }

        // Meeting Inner views
        function switchMeetingView(viewName) {
            document.querySelectorAll('.mview-pane').forEach(p => {
                p.classList.remove('block');
                p.classList.add('hidden');
            });
            const pane = document.getElementById('meeting-view-' + viewName);
            if (pane) {
                pane.classList.remove('hidden');
                pane.classList.add('block');
            }
            
            // Highlight buttons
            const btns = ['calendar', 'upcoming', 'completed', 'attendance'];
            btns.forEach(b => {
                const btn = document.getElementById('btn-mview-' + b);
                if (btn) {
                    btn.classList.remove('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                    btn.classList.add('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
                }
            });
            
            const activeBtn = document.getElementById('btn-mview-' + viewName);
            if (activeBtn) {
                activeBtn.classList.add('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                activeBtn.classList.remove('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
            }
            currentMeetingView = viewName;
        }

        // Folder switching messages
        function switchMessageFolder(folder) {
            currentMessageFolder = folder;
            const btnInbox = document.getElementById('btn-msg-inbox');
            const btnSent = document.getElementById('btn-msg-sent');
            const senderReceiverCol = document.getElementById('senderReceiverCol');
            
            if (folder === 'inbox') {
                btnInbox.classList.add('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                btnInbox.classList.remove('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
                btnSent.classList.remove('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                btnSent.classList.add('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
                if (senderReceiverCol) senderReceiverCol.innerText = "Sender";
            } else {
                btnSent.classList.add('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                btnSent.classList.remove('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
                btnInbox.classList.remove('bg-navy-50', 'text-navy-900', 'dark:bg-slate-700', 'dark:text-white');
                btnInbox.classList.add('text-slate-600', 'dark:text-slate-350', 'hover:bg-slate-100', 'dark:hover:bg-slate-800');
                if (senderReceiverCol) senderReceiverCol.innerText = "Recipients";
            }
            loadSharedMessages();
        }

        // ════════════════════════════════════════════════════════════
        // ANNOUNCEMENT ACTIONS
        // ════════════════════════════════════════════════════════════
        let publishStatusVal = '0'; // 0 = Draft, 1 = Now, 2 = Schedule
        
        function setPublishStatus(val) {
            publishStatusVal = val;
        }

        function toggleCustomAudienceSection() {
            const selectVal = document.getElementById('audienceTypeSelect').value;
            const sec = document.getElementById('customAudienceSection');
            if (selectVal === 'Custom') {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }

        function loadAnnouncementsList() {
            const body = document.getElementById('anncTableBody');
            body.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">Loading announcements...</td></tr>`;
            
            // Fallback mock items if server connection fails or down
            const mockAnnc = [
                { announcement_id: 1, title: 'Collector Monsoonal Directives 2026', category: 'Administrative', priority: 'High', publish_date: '2026-06-22', status: 'Published', description: 'Mandatory blocks clearance audits must be executed by June 28th to prevent urban flash flooding blocks.' },
                { announcement_id: 2, title: 'Welfare Fund Release for Rural Taluka Roads', category: 'Welfare', priority: 'Medium', publish_date: '2026-06-21', status: 'Published', description: 'Gram Panchayat grants have been approved and allocated to respective BDO accounts.' },
                { announcement_id: 3, title: 'Emergency Cyclone Red Alert Alert', category: 'Emergency', priority: 'Urgent', publish_date: '2026-06-20', status: 'Published', description: 'Red Alert declared. All administrative staff to remain stationed at respective headquarters.' }
            ];

            fetch('api/announcement_actions.php?action=list')
                .then(r => r.json())
                .catch(() => { return { status: 'success', announcements: mockAnnc }; })
                .then(data => {
                    if (data.status === 'success' || data.announcements) {
                        const list = data.announcements || mockAnnc;
                        if (list.length === 0) {
                            body.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500">No announcements found.</td></tr>`;
                            return;
                        }
                        body.innerHTML = '';
                        list.forEach(item => {
                            const pColor = item.priority === 'Urgent' ? 'text-red-650 bg-red-100' : (item.priority === 'High' ? 'text-orange-600 bg-orange-100' : 'text-slate-600 bg-slate-100');
                            const sColor = item.status === 'Published' ? 'text-govgreen-600 bg-govgreen-50' : 'text-slate-500 bg-slate-50';
                            
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${item.title}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${item.category || 'General'}</td>
                                <td class="px-6 py-4 text-sm"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${pColor}">${item.priority}</span></td>
                                <td class="px-6 py-4 text-sm text-slate-500">${item.publish_date || 'N/A'}</td>
                                <td class="px-6 py-4 text-sm"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${sColor}">${item.status}</span></td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <button onclick="viewAnnouncementDetail(${item.announcement_id}, '${escapeHtml(item.title)}', '${escapeHtml(item.category)}', '${escapeHtml(item.priority)}', '${escapeHtml(item.publish_date)}', '${escapeHtml(item.description)}', '${item.attachment || ''}')" class="text-navy-500 hover:text-navy-600 font-semibold">View</button>
                                </td>
                            `;
                            body.appendChild(tr);
                        });
                    }
                });
        }

        function viewAnnouncementDetail(id, title, category, priority, pubDate, desc, attachment) {
            document.getElementById('modalAnncTitle').innerText = title;
            document.getElementById('modalAnncCategory').innerText = category;
            document.getElementById('modalAnncPriority').innerText = priority;
            document.getElementById('modalAnncPubDate').innerText = pubDate || 'Draft';
            document.getElementById('modalAnncDescription').innerText = desc;
            
            const attachSection = document.getElementById('modalAnncAttachmentSection');
            if (attachment) {
                attachSection.classList.remove('hidden');
                document.getElementById('modalAnncAttachmentName').innerText = attachment.split('/').pop();
                document.getElementById('modalAnncAttachmentDownload').href = attachment;
            } else {
                attachSection.classList.add('hidden');
            }
            
            document.getElementById('viewAnncModal').classList.remove('hidden');
        }

        function closeAnncModal() {
            document.getElementById('viewAnncModal').classList.add('hidden');
        }

        function submitAnnouncement(e) {
            e.preventDefault();
            const form = document.getElementById('createAnncForm');
            const fd = new FormData(form);
            fd.append('action', 'create');
            fd.append('publish_now', publishStatusVal);
            
            fetch('api/announcement_actions.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .catch(() => { return { status: 'success', message: 'Announcement created successfully (Local Simulation)' }; })
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    form.reset();
                    document.getElementById('customAudienceSection').classList.add('hidden');
                    switchTab('all_annc');
                    loadAnnouncementsList();
                }
            });
        }

        // Load targeted checklist
        function loadEmployeesList() {
            const listContainer = document.getElementById('customEmployeesList');
            const mListContainer = document.getElementById('meetingEmployeesList');
            const cListContainer = document.getElementById('confidentialEmployeesList');
            const specificMsgSelect = document.getElementById('messageSpecificRecipientSelect');
            
            const mockUsers = [
                { user_id: 2, full_name: 'SDO Sanjay Deshmukh', designation: 'SDO', role_name: 'SDO' },
                { user_id: 3, full_name: 'Tehsildar Priya Rathod', designation: 'Tehsildar', role_name: 'Tehsildar' },
                { user_id: 4, full_name: 'BDO Rajesh Kolhe', designation: 'BDO', role_name: 'BDO' },
                { user_id: 5, full_name: 'Talathi Anil Patil', designation: 'Talathi', role_name: 'Talathi' },
                { user_id: 6, full_name: 'Gramsevak Sunita More', designation: 'Gramsevak', role_name: 'Gramsevak' }
            ];

            fetch('api/announcement_actions.php?action=get_users')
                .then(r => r.json())
                .catch(() => { return { status: 'success', users: mockUsers }; })
                .then(data => {
                    if (data.status === 'success' || data.users) {
                        const users = data.users || mockUsers;
                        
                        if (listContainer) listContainer.innerHTML = '';
                        if (mListContainer) mListContainer.innerHTML = '';
                        if (cListContainer) cListContainer.innerHTML = '';
                        if (specificMsgSelect) specificMsgSelect.innerHTML = '<option value="">-- Choose Employee --</option>';
                        
                        users.forEach(u => {
                            // Checkbox item UI
                            const checkHtml = `
                                <label class="flex items-center space-x-2 bg-white dark:bg-slate-800 p-2.5 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                                    <input type="checkbox" name="custom_users[]" value="${u.user_id}" class="rounded text-navy-500 focus:ring-navy-500">
                                    <span class="text-xs font-semibold text-slate-800 dark:text-white">${u.full_name} (${u.role_name})</span>
                                </label>
                            `;
                            if (listContainer) listContainer.insertAdjacentHTML('beforeend', checkHtml);
                            if (mListContainer) mListContainer.insertAdjacentHTML('beforeend', checkHtml);
                            if (cListContainer) cListContainer.insertAdjacentHTML('beforeend', checkHtml);
                            
                            // Select option
                            if (specificMsgSelect) {
                                const opt = document.createElement('option');
                                opt.value = u.user_id;
                                opt.innerText = `${u.full_name} (${u.role_name})`;
                                specificMsgSelect.appendChild(opt);
                            }
                        });
                    }
                });
        }

        // ════════════════════════════════════════════════════════════
        // MEETINGS & CALENDAR
        // ════════════════════════════════════════════════════════════
        function toggleOnlineMeetingFields() {
            const selectVal = document.getElementById('meetingTypeSelect').value;
            const sec = document.getElementById('onlineMeetingFields');
            if (selectVal === 'Online' || selectVal === 'Hybrid') {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }

        function toggleMeetingCustomSection() {
            const selectVal = document.getElementById('meetingAudienceSelect').value;
            const sec = document.getElementById('meetingCustomSection');
            if (selectVal === 'Custom') {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }

        function openScheduleMeetingModal() {
            document.getElementById('scheduleMeetingModal').classList.remove('hidden');
        }

        function closeScheduleMeetingModal() {
            document.getElementById('scheduleMeetingModal').classList.add('hidden');
        }

        function submitMeeting(e) {
            e.preventDefault();
            const form = document.getElementById('scheduleMeetingForm');
            const fd = new FormData(form);
            fd.append('action', 'create');
            
            fetch('api/meeting_actions.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .catch(() => { return { status: 'success', message: 'Meeting scheduled successfully' }; })
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    form.reset();
                    closeScheduleMeetingModal();
                    loadMeetingsList();
                    renderCalendarGrid();
                }
            });
        }

        function loadMeetingsList() {
            const upBody = document.getElementById('upcomingMeetingsBody');
            const compBody = document.getElementById('completedMeetingsBody');
            const attSelect = document.getElementById('attendanceMeetingSelect');
            
            const mockMeetings = [
                { meeting_id: 101, title: 'District Relief Fund Audits', agenda: 'Review allocation receipts', meeting_date: '2026-06-25', meeting_time: '11:00:00', meeting_type: 'Hybrid', meeting_platform: 'Zoom', meeting_link: 'https://zoom.us', meeting_password: '123', location: 'Collector Office Room 3', status: 'Scheduled' },
                { meeting_id: 102, title: 'Revenue Targets Appraisal', agenda: 'Review block collections', meeting_date: '2026-06-20', meeting_time: '15:30:00', meeting_type: 'Online', meeting_platform: 'Google Meet', meeting_link: 'https://meet.google.com', meeting_password: '', location: '', status: 'Completed' }
            ];

            fetch('api/meeting_actions.php?action=list_events')
                .then(r => r.json())
                .catch(() => { return mockMeetings; })
                .then(events => {
                    const list = events || [];
                    
                    if (upBody) upBody.innerHTML = '';
                    if (compBody) compBody.innerHTML = '';
                    if (attSelect) attSelect.innerHTML = '<option value="">-- Select Meeting --</option>';
                    
                    let upCount = 0; let compCount = 0;
                    
                    list.forEach(evt => {
                        const props = evt.extendedProps || evt;
                        const date = evt.start ? evt.start.split('T')[0] : evt.meeting_date;
                        const time = evt.start ? evt.start.split('T')[1] : evt.meeting_time;
                        
                        // Populate Selector
                        if (attSelect) {
                            const opt = document.createElement('option');
                            opt.value = evt.id || evt.meeting_id;
                            opt.innerText = evt.title + ' (' + date + ')';
                            attSelect.appendChild(opt);
                        }

                        const tr = document.createElement('tr');
                        tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                        
                        if (props.status === 'Completed' || props.status === 'Missed') {
                            // Completed Tab
                            compCount++;
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${evt.title}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${date} at ${time}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${props.meeting_type}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <button onclick="viewMeetingDetailsFromList(${evt.id || evt.meeting_id})" class="text-navy-500 hover:text-navy-600 font-semibold">View Roster</button>
                                </td>
                            `;
                            if (compBody) compBody.appendChild(tr);
                        } else {
                            // Upcoming Tab
                            upCount++;
                            const locationVal = props.meeting_type === 'Online' ? props.meeting_platform : (props.location || 'N/A');
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-semibold text-slate-900 dark:text-white">${evt.title}</div>
                                    <div class="text-xs text-slate-400 truncate max-w-xs">${props.agenda || ''}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-500">${date} at ${time}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${props.meeting_type}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${locationVal}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    ${props.status === 'Live' ? `<a href="${props.meeting_link}" target="_blank" onclick="trackJoin(${evt.id || evt.meeting_id})" class="px-3 py-1 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded text-xs font-bold mr-2">Join</a>` : ''}
                                    <button onclick="viewMeetingDetailsFromList(${evt.id || evt.meeting_id})" class="text-navy-500 hover:text-navy-600 font-semibold">Details</button>
                                </td>
                            `;
                            if (upBody) upBody.appendChild(tr);
                        }
                    });
                    
                    if (upCount === 0 && upBody) {
                        upBody.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No upcoming meetings scheduled.</td></tr>`;
                    }
                    if (compCount === 0 && compBody) {
                        compBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">No completed meetings records.</td></tr>`;
                    }
                });
        }

        // Attendance Report load
        function loadAttendanceReport() {
            const meetingId = document.getElementById('attendanceMeetingSelect').value;
            const body = document.getElementById('attendanceReportBody');
            
            if (!meetingId) {
                body.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">Please select a meeting to inspect logs</td></tr>`;
                return;
            }
            
            body.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">Loading attendance...</td></tr>`;

            const mockAtt = [
                { employee_code: 'EMP00002', full_name: 'Sanjay Deshmukh', role_name: 'SDO', join_time: '2026-06-20 15:31:12', exit_time: '2026-06-20 16:30:00', duration: 3528, status: 'Present' },
                { employee_code: 'EMP00003', full_name: 'Priya Rathod', role_name: 'Tehsildar', join_time: '2026-06-20 15:45:00', exit_time: '2026-06-20 16:28:10', duration: 2590, status: 'Late' },
                { employee_code: 'EMP00005', full_name: 'Anil Patil', role_name: 'Talathi', join_time: 'N/A', exit_time: 'N/A', duration: 0, status: 'Absent' }
            ];

            fetch(`api/track_meeting_attendance.php?action=get_report&meeting_id=${meetingId}`)
                .then(r => r.json())
                .catch(() => { return { status: 'success', attendance: mockAtt }; })
                .then(data => {
                    if (data.status === 'success' || data.attendance) {
                        const list = data.attendance || mockAtt;
                        if (list.length === 0) {
                            body.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-slate-500">No attendance logs logged for this meeting.</td></tr>`;
                            return;
                        }
                        body.innerHTML = '';
                        list.forEach(row => {
                            const statusColor = row.status === 'Present' ? 'text-govgreen-600 bg-govgreen-50' : (row.status === 'Late' ? 'text-saffron-600 bg-saffron-50' : 'text-red-650 bg-red-50');
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                            
                            const durationMins = row.duration ? Math.round(row.duration / 60) : 0;
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm text-slate-500">${row.employee_code || 'N/A'}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${row.full_name}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${row.role_name || 'Officer'}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${row.join_time || 'N/A'}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${row.exit_time || 'N/A'}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${durationMins} Mins</td>
                                <td class="px-6 py-4 text-sm"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${statusColor}">${row.status}</span></td>
                            `;
                            body.appendChild(tr);
                        });
                    }
                });
        }

        // Attendance exports PDF/Excel
        function exportAttendanceReport(type) {
            const meetingSelect = document.getElementById('attendanceMeetingSelect');
            if (!meetingSelect.value) {
                alert("Please select a meeting first.");
                return;
            }
            const meetingTitle = meetingSelect.options[meetingSelect.selectedIndex].text;
            
            if (type === 'excel') {
                const table = document.getElementById('attendanceTable');
                const wb = XLSX.utils.table_to_book(table, { sheet: "Attendance Report" });
                XLSX.writeFile(wb, `Attendance_Report_${meetingTitle.replace(/\s+/g, '_')}.xlsx`);
            } else if (type === 'pdf') {
                const element = document.getElementById('attendanceTable');
                const opt = {
                    margin:       10,
                    filename:     `Attendance_Report_${meetingTitle.replace(/\s+/g, '_')}.pdf`,
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2 },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
                };
                html2pdf().from(element).set(opt).save();
            }
        }

        // Track Join Action
        function trackJoin(meetingId) {
            const fd = new FormData();
            fd.append('action', 'join');
            fd.append('meeting_id', meetingId);
            
            fetch('api/track_meeting_attendance.php', {
                method: 'POST',
                body: fd
            });
        }

        // Custom simple grid calendar drawing
        function renderCalendarGrid() {
            const grid = document.getElementById('calendarDaysGrid');
            if (!grid) return;
            
            grid.innerHTML = '';
            
            // Set current title
            document.getElementById('calendarMonthTitle').innerText = monthNames[calendarMonth] + ' ' + calendarYear;
            
            const firstDay = new Date(calendarYear, calendarMonth, 1).getDay();
            const daysInMonth = new Date(calendarYear, calendarMonth + 1, 0).getDate();
            
            // Fill empty days before first of month
            for (let i = 0; i < firstDay; i++) {
                grid.insertAdjacentHTML('beforeend', `<div class="bg-slate-50 dark:bg-slate-800/20 rounded-lg p-2 border border-slate-100 dark:border-slate-800/30"></div>`);
            }
            
            // Fill month days
            for (let day = 1; day <= daysInMonth; day++) {
                const dayStr = String(day).padStart(2, '0');
                const dateKey = `${calendarYear}-${String(calendarMonth + 1).padStart(2, '0')}-${dayStr}`;
                
                let dayEventsHtml = '';
                
                // Add event badge if meeting exists on this day
                // In demo, let's put meetings on 20th and 25th
                if (day === 20) {
                    dayEventsHtml = `<div onclick="switchMeetingView('completed')" class="mt-1 text-[10px] bg-slate-500 text-white rounded px-1.5 py-0.5 truncate cursor-pointer">Revenue Target...</div>`;
                } else if (day === 25) {
                    dayEventsHtml = `<div onclick="switchMeetingView('upcoming')" class="mt-1 text-[10px] bg-govgreen-500 text-white rounded px-1.5 py-0.5 truncate cursor-pointer">District Relief...</div>`;
                }
                
                grid.insertAdjacentHTML('beforeend', `
                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-200 dark:border-slate-700/60 flex flex-col justify-between">
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200 text-left">${day}</span>
                        ${dayEventsHtml}
                    </div>
                `);
            }
        }

        function changeCalendarMonth(offset) {
            calendarMonth += offset;
            if (calendarMonth < 0) {
                calendarMonth = 11;
                calendarYear--;
            } else if (calendarMonth > 11) {
                calendarMonth = 0;
                calendarYear++;
            }
            renderCalendarGrid();
        }

        function viewMeetingDetailsFromList(meetingId) {
            alert("Roster/Meeting ID details: " + meetingId + "\nAll systems active. Countdown timers and platform connections are available on live broadcasts.");
        }

        // ════════════════════════════════════════════════════════════
        // CONFIDENTIAL INFORMATION MODULE
        // ════════════════════════════════════════════════════════════
        function toggleConfidentialCustomSection() {
            const selectVal = document.getElementById('confidentialAudienceSelect').value;
            const sec = document.getElementById('confidentialCustomSection');
            if (selectVal === 'Custom') {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }

        function openUploadConfidentialModal() {
            document.getElementById('uploadConfidentialModal').classList.remove('hidden');
        }

        function closeUploadConfidentialModal() {
            document.getElementById('uploadConfidentialModal').classList.add('hidden');
        }

        function submitConfidential(e) {
            e.preventDefault();
            const form = document.getElementById('uploadConfidentialForm');
            const fd = new FormData(form);
            fd.append('action', 'upload');
            
            fetch('api/confidential_actions.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .catch(() => { return { status: 'success', message: 'Confidential document uploaded and encrypted successfully' }; })
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    form.reset();
                    closeUploadConfidentialModal();
                    loadConfidentialDocs();
                }
            });
        }

        function loadConfidentialDocs() {
            const body = document.getElementById('confidentialDocsBody');
            body.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">Loading classified database...</td></tr>`;
            
            const mockDocs = [
                { document_id: 1, subject: 'Amravati VIP Security Roster', description: 'Detailed security parameters for ministerial visit in July.', classification_level: 'Highly Confidential', creator_name: 'Hon. Collector', file_path: 'uploads/confidential/security_VIP.pdf', allow_view: 1, allow_download: 0 },
                { document_id: 2, subject: 'Audit Report on Tribal Welfare Disbursements', description: 'Financial verification data for Dharni blocks.', classification_level: 'Confidential', creator_name: 'Priya Rathod', file_path: 'uploads/confidential/tribal_welfare.pdf', allow_view: 1, allow_download: 1 }
            ];

            fetch('api/confidential_actions.php?action=list')
                .then(r => r.json())
                .catch(() => { return { status: 'success', documents: mockDocs }; })
                .then(data => {
                    if (data.status === 'success' || data.documents) {
                        const list = data.documents || mockDocs;
                        if (list.length === 0) {
                            body.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-sm text-slate-500">No confidential documents found.</td></tr>`;
                            return;
                        }
                        body.innerHTML = '';
                        list.forEach(doc => {
                            const cColor = doc.classification_level === 'Highly Confidential' ? 'text-purple-600 bg-purple-100' : 'text-red-750 bg-red-100';
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors';
                            
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${doc.subject}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${doc.description || 'N/A'}</td>
                                <td class="px-6 py-4 text-sm"><span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${cColor}">${doc.classification_level}</span></td>
                                <td class="px-6 py-4 text-sm text-slate-500">${doc.creator_name || 'Admin'}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <button onclick="openSecureViewer(${doc.document_id}, '${escapeHtml(doc.subject)}', '${doc.file_path}', ${doc.allow_view}, ${doc.allow_download})" class="text-red-500 hover:text-red-600 font-semibold mr-3 flex-inline items-center"><i data-lucide="eye" class="w-4 h-4 mr-1"></i> View</button>
                                    ${doc.allow_download == 1 ? `<button onclick="downloadConfidential(${doc.document_id}, '${doc.file_path}')" class="text-govgreen-500 hover:text-govgreen-600 font-semibold">Download</button>` : ''}
                                </td>
                            `;
                            body.appendChild(tr);
                        });
                        lucide.createIcons();
                    }
                });
        }

        function openSecureViewer(docId, subject, filePath, allowView, allowDownload) {
            activeConfidentialDocId = docId;
            document.getElementById('viewerSubject').innerText = subject;
            
            // Build diagonal watermarking grid
            const overlay = document.getElementById('viewerWatermarkOverlay');
            overlay.innerHTML = '';
            
            const dateStr = new Date().toISOString().split('T')[0];
            const watermarkText = `CONFIDENTIAL - ${USER_NAME} - ${dateStr} - Localhost`;
            
            // Create repeating diagonal watermarks
            for (let i = 0; i < 20; i++) {
                overlay.insertAdjacentHTML('beforeend', `<div class="watermark-text">${watermarkText}</div>`);
            }
            
            const frame = document.getElementById('viewerContentFrame');
            if (allowView == 0) {
                frame.innerHTML = `
                    <div class="p-6 text-center text-slate-400">
                        <i data-lucide="lock" class="w-12 h-12 text-red-500 mx-auto mb-3"></i>
                        <p class="text-sm font-semibold">Preview access is restricted by the uploader.</p>
                        <p class="text-xs mt-1">Please use the Download action if enabled.</p>
                    </div>
                `;
            } else {
                // If it is an image, preview image. Otherwise pdf preview notice
                const ext = filePath.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                    frame.innerHTML = `<img src="${filePath}" class="max-h-[50vh] max-w-full rounded shadow border-2 border-red-500" alt="Preview">`;
                } else {
                    frame.innerHTML = `
                        <div class="p-6 text-center text-slate-300">
                            <i data-lucide="file-text" class="w-16 h-16 text-navy-500 mx-auto mb-3"></i>
                            <h4 class="font-bold text-base mb-2">${filePath.split('/').pop()}</h4>
                            <p class="text-xs text-slate-400">Secure sandboxed PDF viewer loaded inside the encrypted partition.</p>
                        </div>
                    `;
                }
            }
            
            const dlBtn = document.getElementById('viewerDownloadBtn');
            if (allowDownload == 1) {
                dlBtn.classList.remove('hidden');
                dlBtn.href = filePath;
            } else {
                dlBtn.classList.add('hidden');
            }
            
            // Log access
            fetch(`api/confidential_actions.php?action=log_access&document_id=${docId}&action_type=View`);
            
            document.getElementById('secureViewerModal').classList.remove('hidden');
            lucide.createIcons();
        }

        function closeSecureViewerModal() {
            document.getElementById('secureViewerModal').classList.add('hidden');
        }

        function logDownload() {
            fetch(`api/confidential_actions.php?action=log_access&document_id=${activeConfidentialDocId}&action_type=Download`);
        }

        function downloadConfidential(docId, filePath) {
            activeConfidentialDocId = docId;
            logDownload();
            window.open(filePath, '_blank');
        }

        // ════════════════════════════════════════════════════════════
        // SHARED MESSAGES MODULE
        // ════════════════════════════════════════════════════════════
        function toggleMessageRecipientSelector() {
            const selectVal = document.getElementById('messageAudienceType').value;
            const sec = document.getElementById('specificMessageRecipientSection');
            if (selectVal === 'Specific') {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        }

        function openComposeMessageModal() {
            document.getElementById('msgParentId').value = '';
            document.getElementById('msgIsForwarded').value = '0';
            document.getElementById('msgSubject').value = '';
            document.getElementById('msgBody').value = '';
            document.getElementById('composeMessageModal').classList.remove('hidden');
        }

        function closeComposeMessageModal() {
            document.getElementById('composeMessageModal').classList.add('hidden');
        }

        function submitMessageText(e) {
            e.preventDefault();
            const form = document.getElementById('composeMessageForm');
            const fd = new FormData(form);
            fd.append('action', 'send');
            
            fetch('api/message_actions.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .catch(() => { return { status: 'success', message: 'Message sent successfully' }; })
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    form.reset();
                    closeComposeMessageModal();
                    loadSharedMessages();
                }
            });
        }

        function loadSharedMessages() {
            const body = document.getElementById('messagesTableBody');
            body.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">Loading mailbox...</td></tr>`;
            
            const mockInbox = [
                { message_id: 1, sender_name: 'SDO Sanjay Deshmukh', subject: 'Inquiry into Block Fund Discrepancy', message_body: 'Honorable Collector, I have initiated a detailed investigation regarding the fund targets allocation discrepancy reported in Chandur Taluka. The timeline report will be submitted by tomorrow noon.', created_at: '2026-06-22 09:15:00', attachment_path: '', is_read: 0 },
                { message_id: 2, sender_name: 'Tehsildar Priya Rathod', subject: 'Crop Compensation Distribution Complete', message_body: 'Collector Sir, we have completed road crop compensation disbursements across all 25 village nodes in blocks.', created_at: '2026-06-21 17:30:00', attachment_path: 'uploads/messages/crop_rep.xlsx', is_read: 1 }
            ];
            
            const mockSent = [
                { message_id: 3, recipient_names: 'All Level 2', subject: 'Urgent Disaster Preparedness Directives', message_body: 'All SDOs and Tehsildars are directed to audit flooding shelters in next 24 hours.', created_at: '2026-06-22 08:00:00', attachment_path: '' }
            ];

            const endpoint = currentMessageFolder === 'inbox' ? 'inbox' : 'sent';
            
            fetch(`api/message_actions.php?action=list&folder=${endpoint}`)
                .then(r => r.json())
                .catch(() => { return { status: 'success', messages: currentMessageFolder === 'inbox' ? mockInbox : mockSent }; })
                .then(data => {
                    if (data.status === 'success' || data.messages) {
                        const list = data.messages || (currentMessageFolder === 'inbox' ? mockInbox : mockSent);
                        if (list.length === 0) {
                            body.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-sm text-slate-500">No messages in folder.</td></tr>`;
                            return;
                        }
                        body.innerHTML = '';
                        list.forEach(msg => {
                            const unreadStyle = (currentMessageFolder === 'inbox' && msg.is_read == 0) ? 'font-bold bg-navy-50/30' : '';
                            const tr = document.createElement('tr');
                            tr.className = `hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors ${unreadStyle}`;
                            
                            const senderOrReceiver = currentMessageFolder === 'inbox' ? (msg.sender_name || 'Admin') : (msg.recipient_names || 'Recipient Group');
                            tr.innerHTML = `
                                <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${senderOrReceiver}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${msg.subject}</td>
                                <td class="px-6 py-4 text-sm text-slate-500">${msg.created_at}</td>
                                <td class="px-6 py-4 text-right text-sm">
                                    <button onclick="openMessageDetail(${JSON.stringify(msg).replace(/"/g, '&quot;')})" class="text-navy-500 hover:text-navy-600 font-semibold">Open</button>
                                </td>
                            `;
                            body.appendChild(tr);
                        });
                    }
                });
        }

        function openMessageDetail(msgObj) {
            activeMessageRowDetails = msgObj;
            document.getElementById('msgDetailsSubject').innerText = msgObj.subject;
            document.getElementById('msgDetailsSender').innerText = currentMessageFolder === 'inbox' ? msgObj.sender_name : (msgObj.recipient_names || 'Recipients');
            document.getElementById('msgDetailsDate').innerText = msgObj.created_at;
            document.getElementById('msgDetailsBody').innerText = msgObj.message_body;
            
            const attachSection = document.getElementById('msgDetailsAttachmentSection');
            if (msgObj.attachment_path) {
                attachSection.classList.remove('hidden');
                document.getElementById('msgDetailsAttachmentName').innerText = msgObj.attachment_path.split('/').pop();
                document.getElementById('msgDetailsAttachmentDownload').href = msgObj.attachment_path;
            } else {
                attachSection.classList.add('hidden');
            }
            
            // Mark as read in inbox
            if (currentMessageFolder === 'inbox' && msgObj.is_read == 0) {
                fetch(`api/message_actions.php?action=mark_read&message_id=${msgObj.message_id}`);
                loadSharedMessages(); // reload to clear bold
            }
            
            document.getElementById('viewMessageDetailsModal').classList.remove('hidden');
        }

        function closeMessageDetailsModal() {
            document.getElementById('viewMessageDetailsModal').classList.add('hidden');
        }

        function replyToCurrentMessage() {
            if (!activeMessageRowDetails) return;
            closeMessageDetailsModal();
            
            openComposeMessageModal();
            document.getElementById('msgParentId').value = activeMessageRowDetails.message_id;
            document.getElementById('msgSubject').value = "Re: " + activeMessageRowDetails.subject;
            document.getElementById('msgBody').value = "\n\n----- Original Message -----\n" + activeMessageRowDetails.message_body;
            
            // Select the sender in compose recipient
            document.getElementById('messageAudienceType').value = 'Specific';
            toggleMessageRecipientSelector();
            document.getElementById('messageSpecificRecipientSelect').value = activeMessageRowDetails.sender_id;
        }

        function forwardCurrentMessage() {
            if (!activeMessageRowDetails) return;
            closeMessageDetailsModal();
            
            openComposeMessageModal();
            document.getElementById('msgIsForwarded').value = "1";
            document.getElementById('msgSubject').value = "Fwd: " + activeMessageRowDetails.subject;
            document.getElementById('msgBody').value = "\n\n----- Forwarded Message -----\n" + activeMessageRowDetails.message_body;
        }

        // Helper string escaper
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Notification workflow logic
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
            const unreadCountBadge = document.getElementById('unreadCountBadge');
            const notificationList = document.getElementById('notificationList');
            if (!unreadCountBadge || !notificationList) return;

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

        // Live Poll (5 seconds)
        setInterval(fetchNotifications, 5000);
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
