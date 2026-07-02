<?php
// include/sidebar.php
// Common sidebar navigation for government UI

$lang = $lang ?? 'en';
$activePage = $activePage ?? 'dashboard';

// Determine role level and authorization status
$sRole_sidebar = $_SESSION['user_role'] ?? 'Collector';
$level_sidebar = 3;
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $lvl_q = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? LIMIT 1");
    if ($lvl_q) {
        $lvl_q->bind_param("s", $sRole_sidebar);
        $lvl_q->execute();
        $lvl_res = $lvl_q->get_result();
        if ($lvl_row = $lvl_res->fetch_assoc()) {
            $level_sidebar = (int)$lvl_row['role_level'];
        }
        $lvl_q->close();
    }
} else {
    $sidebar_map = [
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
    $level_sidebar = $sidebar_map[$sRole_sidebar] ?? 3;
}

$isCollectorOrAdmin = in_array($sRole_sidebar, ['Collector', 'System Administrator', 'Administrator']);

// Simple translation fallback for sidebar if $t is not fully populated
$brand_name = $t['brand_name'] ?? 'AMRAVATI CONNECT';
$menu_main = $t['menu_main_modules'] ?? 'Main Modules';
?>
<!-- Mobile Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

<aside id="sidebar"
       class="w-64 glass-panel border-r border-slate-200 dark:border-slate-800
              flex flex-col transition-transform duration-300 z-40 flex-shrink-0 
              fixed inset-y-0 left-0 transform -translate-x-full lg:relative lg:translate-x-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
              
    <!-- Background Gradient Accent for Sidebar -->
    <div class="absolute inset-0 bg-gradient-to-b from-navy-50/50 to-transparent dark:from-navy-900/20 pointer-events-none z-[-1]"></div>

    <!-- Logo / Brand (Government Formal Style) -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 bg-gradient-formal text-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('assets/images/pattern.svg')] opacity-10 mix-blend-overlay"></div>
        <div class="w-8 h-8 rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center mr-3 shadow-lg relative z-10">
            <!-- Replace with actual Ashoka Chakra or Govt Emblem if available -->
            <i data-lucide="landmark" class="text-white w-4 h-4 drop-shadow-md"></i>
        </div>
        <span class="font-formal font-bold text-lg text-white tracking-wide uppercase relative z-10 drop-shadow-sm"><?= htmlspecialchars($brand_name) ?></span>
    </div>

    <!-- Navigation -->
    <div class="flex-1 overflow-y-auto py-4 scrollbar-thin">
        <nav class="space-y-1 px-3 relative z-10">
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 mt-2">
                <?= htmlspecialchars($menu_main) ?>
            </p>
            
            <a href="dashboard.php?lang=<?= $lang ?>"
               class="nav-item <?= $activePage === 'dashboard' ? 'nav-active' : '' ?>">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 <?= $activePage === 'dashboard' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_dashboard'] ?? 'Executive Dashboard') ?>
            </a>
            
            <a href="announcements.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'announcements' ? 'nav-active' : '' ?>">
                <i data-lucide="megaphone" class="w-5 h-5 mr-3 <?= $activePage === 'announcements' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_announcement_center'] ?? 'Announcement Center') ?>
            </a>
            
            <?php if ($level_sidebar <= 2): ?>
            <a href="create_task.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'create_task' ? 'nav-active' : '' ?>">
                <i data-lucide="network" class="w-5 h-5 mr-3 <?= $activePage === 'create_task' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_task_alloc'] ?? 'Task Allocation') ?>
            </a>
            <?php endif; ?>
            
            <a href="notifications.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'notifications' ? 'nav-active' : '' ?>">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 <?= $activePage === 'notifications' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_notifications'] ?? 'Notifications') ?>
            </a>
            
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 mt-6">
                <?= htmlspecialchars($t['menu_analytics'] ?? 'Analytics & Data') ?>
            </p>
            
            <a href="reports.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'reports' ? 'nav-active' : '' ?>">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 <?= $activePage === 'reports' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_reports'] ?? 'Reports & Analytics') ?>
            </a>

            <!-- Previously Hidden / Missing Links -->
            <a href="appreciations.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'appreciations' ? 'nav-active' : '' ?>">
                <i data-lucide="award" class="w-5 h-5 mr-3 <?= $activePage === 'appreciations' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_appreciation'] ?? 'Appreciation') ?>
            </a>
            <a href="graph.php?lang=<?= $lang ?>"
               class="nav-item <?= $activePage === 'graph' ? 'nav-active' : '' ?>">
                <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3 <?= $activePage === 'graph' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_gis'] ?? 'Performance Report') ?>
            </a>
            <a href="documents.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'documents' ? 'nav-active' : '' ?>">
                <i data-lucide="file-text" class="w-5 h-5 mr-3 <?= $activePage === 'documents' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_docs'] ?? 'Document Management') ?>
            </a>

            <!-- Administration (Now Visible to All) -->
            <p class="px-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2 mt-6">
                <?= htmlspecialchars($t['menu_admin'] ?? 'Administration') ?>
            </p>
            
            <?php if ($isCollectorOrAdmin): ?>
            <a href="user_creation.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'users' ? 'nav-active' : '' ?>">
                <i data-lucide="users" class="w-5 h-5 mr-3 <?= $activePage === 'users' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_users'] ?? 'User Management') ?>
            </a>
            <?php endif; ?>

            <a href="location_hierarchy.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'hierarchy' ? 'nav-active' : '' ?>">
                <i data-lucide="network" class="w-5 h-5 mr-3 <?= $activePage === 'hierarchy' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_hierarchy'] ?? 'Location Hierarchy') ?>
            </a>
            <a href="audit_logs.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'audit' ? 'nav-active' : '' ?>">
                <i data-lucide="clipboard-list" class="w-5 h-5 mr-3 <?= $activePage === 'audit' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_audit'] ?? 'Audit Logs') ?>
            </a>
            
            <a href="settings.php?lang=<?= $lang ?>" 
               class="nav-item <?= $activePage === 'settings' ? 'nav-active' : '' ?>">
                <i data-lucide="settings" class="w-5 h-5 mr-3 <?= $activePage === 'settings' ? '' : 'text-slate-400 dark:text-slate-500' ?>"></i>
                <?= htmlspecialchars($t['menu_settings'] ?? 'Settings') ?>
            </a>
            
            <a href="logout.php" class="nav-item mt-4 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 text-red-500 dark:text-red-400"></i>
                <?= htmlspecialchars($t['menu_logout'] ?? 'Logout') ?>
            </a>
        </nav>
    </div>
    
    <!-- User Profile Snippet in Sidebar -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-900/50 backdrop-blur-md relative z-10">
        <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-gradient-formal flex items-center justify-center text-white font-bold flex-shrink-0 shadow-official relative overflow-hidden">
                <span class="relative z-10"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></span>
            </div>
            <div class="ml-3 overflow-hidden">
                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                    <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?>
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate font-medium">
                    <?= htmlspecialchars($_SESSION['user_role'] ?? 'Role') ?>
                </p>
            </div>
        </div>
    </div>
</aside>
