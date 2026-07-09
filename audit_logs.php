<?php
session_start();
require_once 'include/dbConfig.php';

// Auth Check
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';
$sName  = $_SESSION['user_name'] ?? 'User';

// Determine Level
$level = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        'title'         => 'System Audit Logs - Connect Amravati',
        'page_title'    => 'System Audit Logs',
        'page_subtitle' => 'Audit trail of administrative actions, logins, status modifications, and task lifecycles.',
    ],
    'mr' => [
        'title'         => 'सिस्टम ऑडिट लॉग - अमरावती कनेक्ट',
        'page_title'    => 'सिस्टम ऑडिट लॉग',
        'page_subtitle' => 'प्रशासकीय कृती, लॉगिन, स्थिती बदल आणि कार्य जीवनचक्र यांचा ऑडिट मागोवा.',
    ]
];
$t = $translations[$lang];

// Fetch Module Filter Options
$modules = [];
$resMod = $conn->query("SELECT DISTINCT module_name FROM audit_logs WHERE module_name IS NOT NULL AND module_name != '' ORDER BY module_name ASC");
if ($resMod) {
    while ($row = $resMod->fetch_assoc()) $modules[] = $row['module_name'];
}

// Filters
$filterModule = $_GET['module'] ?? 'All';
$searchQuery  = $_GET['search'] ?? '';

$where = "WHERE 1=1";
if ($filterModule !== 'All') {
    $modEsc = $conn->real_escape_string($filterModule);
    $where .= " AND al.module_name = '$modEsc'";
}
if (!empty($searchQuery)) {
    $searchEsc = $conn->real_escape_string($searchQuery);
    $where .= " AND (al.action_name LIKE '%$searchEsc%' OR al.old_value LIKE '%$searchEsc%' OR al.new_value LIKE '%$searchEsc%' OR u.full_name LIKE '%$searchEsc%')";
}

// Fetch Logs
$auditLogs = [];
$logQuery = "
    SELECT al.*, u.full_name, r.role_name
    FROM audit_logs al
    LEFT JOIN users u ON al.user_id = u.user_id
    LEFT JOIN roles r ON u.role_id = r.role_id
    $where
    ORDER BY al.created_at DESC
    LIMIT 200
";
$resLogs = $conn->query($logQuery);
if ($resLogs) {
    while ($row = $resLogs->fetch_assoc()) $auditLogs[] = $row;
}

// Stats
$totalLogs   = count($auditLogs);
$totalUsers  = count(array_unique(array_column($auditLogs, 'user_id')));
$totalModules = count(array_unique(array_filter(array_column($auditLogs, 'module_name'))));
$todayLogs   = count(array_filter($auditLogs, fn($l) => date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')));

// Module color map
function moduleColor(string $mod): array {
    $map = [
        'Task'          => ['bg' => 'bg-indigo-100 dark:bg-indigo-950/50', 'text' => 'text-indigo-700 dark:text-indigo-300', 'dot' => 'bg-indigo-500', 'icon' => 'clipboard-list'],
        'Login'         => ['bg' => 'bg-emerald-100 dark:bg-emerald-950/50', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'icon' => 'log-in'],
        'User'          => ['bg' => 'bg-blue-100 dark:bg-blue-950/50', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'icon' => 'user'],
        'Notification'  => ['bg' => 'bg-amber-100 dark:bg-amber-950/50', 'text' => 'text-amber-700 dark:text-amber-300', 'dot' => 'bg-amber-500', 'icon' => 'bell'],
        'Appreciation'  => ['bg' => 'bg-pink-100 dark:bg-pink-950/50', 'text' => 'text-pink-700 dark:text-pink-300', 'dot' => 'bg-pink-500', 'icon' => 'award'],
        'Report'        => ['bg' => 'bg-violet-100 dark:bg-violet-950/50', 'text' => 'text-violet-700 dark:text-violet-300', 'dot' => 'bg-violet-500', 'icon' => 'bar-chart-2'],
        'Document'      => ['bg' => 'bg-cyan-100 dark:bg-cyan-950/50', 'text' => 'text-cyan-700 dark:text-cyan-300', 'dot' => 'bg-cyan-500', 'icon' => 'file-text'],
    ];
    foreach ($map as $key => $val) {
        if (stripos($mod, $key) !== false) return $val;
    }
    return ['bg' => 'bg-slate-100 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'dot' => 'bg-slate-400', 'icon' => 'activity'];
}

include 'include/header.php';
$activePage = 'audit';
include 'include/sidebar.php';
?>
<style>
.audit-timeline-item {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.4s ease, transform 0.4s ease;
}
.audit-timeline-item.visible {
    opacity: 1;
    transform: translateY(0);
}
.payload-toggle { cursor: pointer; }
.payload-body { display: none; }
.payload-body.open { display: block; }
.stat-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 32px -8px rgba(0,0,0,0.12);
}
.timeline-line {
    background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 50%, #3b82f6 100%);
}
.glow-dot {
    box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
}
</style>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button id="sidebarToggle" class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none block lg:hidden">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex items-center space-x-4">
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            <?php include 'include/notification_widget.php'; ?>
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?> (<?= htmlspecialchars($headerLocationDisplay) ?>)</span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
                        <?= strtoupper(substr($sName, 0, 1)) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 z-50 text-left">
                    <div class="py-1">
                        <a href="profile_update.php" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i>Update Profile
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-650 hover:bg-red-50 dark:hover:bg-red-900/10">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8 space-y-8">

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg">
                        <i data-lucide="shield-check" class="w-5 h-5 text-white"></i>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 ml-13 pl-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold badge-l<?= $level ?>">
                <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                <?= htmlspecialchars($sRole) ?> (L<?= $level ?>)
            </span>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card bg-gradient-to-br from-indigo-600 to-indigo-700 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-indigo-200 text-xs font-bold uppercase tracking-wider">Total Events</span>
                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-3xl font-black"><?= $totalLogs ?></div>
                <div class="text-indigo-200 text-xs mt-1">Last 200 records</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-emerald-100 text-xs font-bold uppercase tracking-wider">Today's Activity</span>
                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                        <i data-lucide="calendar-check" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-3xl font-black"><?= $todayLogs ?></div>
                <div class="text-emerald-100 text-xs mt-1"><?= date('d M Y') ?></div>
            </div>
            <div class="stat-card bg-gradient-to-br from-violet-500 to-purple-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-violet-100 text-xs font-bold uppercase tracking-wider">Active Users</span>
                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-3xl font-black"><?= $totalUsers ?></div>
                <div class="text-violet-100 text-xs mt-1">Distinct operators</div>
            </div>
            <div class="stat-card bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-amber-100 text-xs font-bold uppercase tracking-wider">Modules</span>
                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-3xl font-black"><?= $totalModules ?></div>
                <div class="text-amber-100 text-xs mt-1">System modules tracked</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white dark:bg-slate-950 border border-slate-200/70 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
            <form action="audit_logs.php" method="GET" class="flex flex-col md:flex-row gap-3 items-center" id="auditSearchForm" onsubmit="return validateAuditSearch(event)">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="w-4 h-4 text-navy-500 dark:text-blue-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="text" name="search" id="auditSearchInput" value="<?= htmlspecialchars($searchQuery) ?>"
                        placeholder="Search by name, action, or values... (Press Enter)"
                        autocomplete="off"
                        class="w-full pl-10 pr-4 py-2.5 border-2 border-slate-200 dark:border-slate-800 dark:bg-slate-900 dark:text-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
                </div>
                <div class="w-full md:w-56">
                    <select name="module" onchange="this.form.submit()"
                        class="w-full border border-slate-200 dark:border-slate-800 dark:bg-slate-900 dark:text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="All">All Modules</option>
                        <?php foreach ($modules as $mod): ?>
                            <option value="<?= htmlspecialchars($mod) ?>" <?= $filterModule === $mod ? 'selected' : '' ?>><?= htmlspecialchars($mod) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                    <i data-lucide="search" class="w-4 h-4"></i> Search
                </button>
                <?php if ($filterModule !== 'All' || !empty($searchQuery)): ?>
                    <a href="audit_logs.php?lang=<?= $lang ?>" class="px-4 py-2.5 text-slate-500 hover:text-red-500 dark:text-slate-400 text-sm flex items-center gap-1.5 transition-colors">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>
        <?php if (!empty($searchQuery) && empty($auditLogs)): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'No Records Found',
                    html: '<p>No audit records found for <strong>"<?= htmlspecialchars(addslashes($searchQuery)) ?>"</strong>.</p><p class="text-sm text-slate-500 mt-1">Please enter correct data and try again.</p>',
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK'
                });
            }
        });
        </script>
        <?php endif; ?>
        <script>
        function validateAuditSearch(e) {
            const input = document.getElementById('auditSearchInput');
            if (input && input.value.trim().length > 0 && input.value.trim().length < 2) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Search Too Short',
                        text: 'Please enter at least 2 characters to search.',
                        confirmButtonColor: '#4f46e5',
                        confirmButtonText: 'OK'
                    });
                }
                return false;
            }
            return true;
        }
        </script>


        <!-- Timeline Activity Feed -->
        <?php if (empty($auditLogs)): ?>
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <i data-lucide="inbox" class="w-16 h-16 mb-4 opacity-30"></i>
                <p class="text-lg font-semibold">No audit records found</p>
                <p class="text-sm mt-1">Try adjusting your filters or search terms</p>
            </div>
        <?php else: ?>
            <?php
            $groupedLogs = [];
            foreach ($auditLogs as $log) {
                $dateKey = date('d M Y', strtotime($log['created_at']));
                $groupedLogs[$dateKey][] = $log;
            }
            ?>
            <div class="space-y-8">
                <?php foreach ($groupedLogs as $dateLabel => $dayLogs): ?>
                    <!-- Date Group Header -->
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-1.5 rounded-full text-xs font-bold shadow-md">
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                                <?= $dateLabel === date('d M Y') ? '📅 Today — ' . $dateLabel : $dateLabel ?>
                            </div>
                            <div class="flex-1 h-px bg-slate-200 dark:bg-slate-800"></div>
                            <span class="text-xs text-slate-400 font-semibold"><?= count($dayLogs) ?> events</span>
                        </div>

                        <!-- Timeline -->
                        <div class="relative pl-8">
                            <!-- Vertical Line -->
                            <div class="absolute left-3.5 top-0 bottom-0 w-0.5 timeline-line rounded-full opacity-25"></div>

                            <div class="space-y-3">
                                <?php foreach ($dayLogs as $idx => $log):
                                    $mc = moduleColor($log['module_name'] ?? '');
                                    $initials = $log['full_name']
                                        ? strtoupper(substr(trim($log['full_name']), 0, 1))
                                        : '⚙';
                                    $payloadId = 'payload_' . $log['audit_id'];
                                    $hasPayload = !empty($log['old_value']) || !empty($log['new_value']);
                                ?>
                                <div class="audit-timeline-item" data-delay="<?= $idx * 40 ?>">
                                    <!-- Timeline dot -->
                                    <div class="absolute -left-0 w-7 h-7 rounded-full <?= $mc['dot'] ?> glow-dot border-2 border-white dark:border-slate-900 flex items-center justify-center shadow-sm" style="margin-top: 12px;">
                                        <i data-lucide="<?= $mc['icon'] ?>" class="w-3.5 h-3.5 text-white"></i>
                                    </div>

                                    <!-- Card -->
                                    <div class="ml-4 bg-white dark:bg-slate-950 border border-slate-200/70 dark:border-slate-800/70 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-800/60 transition-all duration-200">
                                        <div class="p-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">

                                                <!-- Left: User + Action -->
                                                <div class="flex items-start gap-3 flex-1 min-w-0">
                                                    <!-- Avatar -->
                                                    <div class="w-9 h-9 rounded-xl <?= $mc['bg'] ?> flex items-center justify-center font-black text-sm <?= $mc['text'] ?> flex-shrink-0">
                                                        <?= $initials ?>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                                            <span class="font-bold text-slate-900 dark:text-white text-sm">
                                                                <?= htmlspecialchars($log['full_name'] ?: 'System Process') ?>
                                                            </span>
                                                            <?php if ($log['role_name']): ?>
                                                                <span class="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full font-semibold">
                                                                    <?= htmlspecialchars($log['role_name']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                                                            <?= htmlspecialchars($log['action_name'] ?? 'Performed action') ?>
                                                        </p>
                                                    </div>
                                                </div>

                                                <!-- Right: Module badge + Time -->
                                                <div class="flex flex-col items-end gap-1.5 flex-shrink-0">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wider <?= $mc['bg'] ?> <?= $mc['text'] ?>">
                                                        <i data-lucide="<?= $mc['icon'] ?>" class="w-3 h-3"></i>
                                                        <?= htmlspecialchars($log['module_name'] ?? 'System') ?>
                                                    </span>
                                                    <span class="text-[11px] text-slate-400 flex items-center gap-1">
                                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                                        <?= date('h:i A', strtotime($log['created_at'])) ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Meta Row: IP + Browser -->
                                            <div class="flex flex-wrap items-center gap-4 mt-3 pt-3 border-t border-slate-100 dark:border-slate-800/60">
                                                <span class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                                    <i data-lucide="wifi" class="w-3 h-3 text-indigo-400"></i>
                                                    <span class="font-semibold text-slate-600 dark:text-slate-300"><?= htmlspecialchars($log['ip_address'] ?: 'N/A') ?></span>
                                                </span>
                                                <?php if ($log['browser_details']): ?>
                                                    <span class="flex items-center gap-1.5 text-[11px] text-slate-400 truncate max-w-xs" title="<?= htmlspecialchars($log['browser_details']) ?>">
                                                        <i data-lucide="monitor" class="w-3 h-3 flex-shrink-0"></i>
                                                        <?= htmlspecialchars(mb_strimwidth($log['browser_details'], 0, 70, '…')) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <!-- Payload toggle button -->
                                                <?php if ($hasPayload): ?>
                                                    <button class="payload-toggle ml-auto flex items-center gap-1.5 text-[11px] text-indigo-600 dark:text-indigo-400 font-bold hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors" data-target="<?= $payloadId ?>">
                                                        <i data-lucide="code-2" class="w-3 h-3"></i>
                                                        View Payload
                                                        <i data-lucide="chevron-down" class="w-3 h-3 payload-chevron transition-transform duration-200"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Payload Expandable -->
                                            <?php if ($hasPayload): ?>
                                                <div id="<?= $payloadId ?>" class="payload-body mt-3 rounded-xl overflow-hidden border border-slate-100 dark:border-slate-800">
                                                    <?php if (!empty($log['old_value'])): ?>
                                                        <div class="bg-red-50 dark:bg-red-950/20 px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                                            <div class="flex items-center gap-2 mb-1.5">
                                                                <span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span>
                                                                <span class="text-[10px] font-bold text-red-500 uppercase tracking-wider">Previous Value</span>
                                                            </div>
                                                            <pre class="text-[11px] text-red-700 dark:text-red-300 font-mono whitespace-pre-wrap break-all leading-relaxed"><?= htmlspecialchars($log['old_value']) ?></pre>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($log['new_value'])): ?>
                                                        <div class="bg-emerald-50 dark:bg-emerald-950/20 px-4 py-3">
                                                            <div class="flex items-center gap-2 mb-1.5">
                                                                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                                                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">New Value</span>
                                                            </div>
                                                            <pre class="text-[11px] text-emerald-700 dark:text-emerald-300 font-mono whitespace-pre-wrap break-all leading-relaxed"><?= htmlspecialchars($log['new_value']) ?></pre>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- End of Feed -->
            <div class="flex items-center justify-center gap-3 py-6">
                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
                <span class="text-xs text-slate-400 font-semibold px-3">End of audit trail · <?= $totalLogs ?> records shown</span>
                <div class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></div>
            </div>

        <?php endif; ?>
    </main>
</div>

<script>
    lucide.createIcons();

    // Profile dropdown
    const profileBtn  = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', e => { e.stopPropagation(); profileMenu.classList.toggle('hidden'); });
        document.addEventListener('click', () => profileMenu.classList.add('hidden'));
    }

    // Animate timeline items on scroll
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.dataset.delay || 0);
                setTimeout(() => entry.target.classList.add('visible'), delay);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });

    document.querySelectorAll('.audit-timeline-item').forEach(el => observer.observe(el));

    // Payload toggle — use event delegation so Lucide SVG replacement doesn't break listeners
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.payload-toggle');
        if (!btn) return;
        const targetId = btn.dataset.target;
        const body = document.getElementById(targetId);
        const chevron = btn.querySelector('.payload-chevron');
        if (body) {
            body.classList.toggle('open');
            if (chevron) chevron.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : '';
        }
    });
</script>
<?php include 'include/footer.php'; ?>
