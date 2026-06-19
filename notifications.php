<?php
session_start();
require_once 'include/dbConfig.php';

$userId = $_SESSION['user_id'] ?? 1;

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
function time_elapsed_string_full($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array('y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second');
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Center - Amravati Connect</title>
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
                        navy: { 50: '#eef2f6', 600: '#152b4a', 700: '#0f1f38' },
                        govgreen: { 500: '#2e7d32' },
                        saffron: { 500: '#f57c00' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
        .dark .glass-panel { background: rgba(15, 23, 42, 0.7); }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
                <i data-lucide="landmark" class="text-white w-5 h-5"></i>
            </div>
            <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-3">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Main Modules</p>
                <a href="blank_wrushabh.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i> Executive Dashboard
                </a>
                <a href="notifications.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-navy-50 text-navy-700 dark:bg-slate-800 dark:text-white">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i> Notification Center
                </a>
            </nav>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- GLOBAL HEADER -->
        <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 focus:outline-none hidden md:block" id="sidebarToggle">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
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
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Notifications</h3>
                            <button onclick="markAllAsRead()" class="text-xs text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 font-medium">Mark all as read</button>
                        </div>
                        <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                            <!-- Populated via AJAX -->
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-lg">
                            <a href="notifications.php" class="block w-full text-center px-4 py-3 text-xs font-medium text-slate-500 hover:text-navy-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-3 border-l border-slate-200 dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm">C</div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Notification Center</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Manage your alerts, reminders, and system notifications.</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button onclick="markAllAsRead()" class="inline-flex items-center px-4 py-2 border border-slate-300 shadow-sm text-sm font-medium rounded-md text-slate-700 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                        <i data-lucide="check-check" class="w-4 h-4 mr-2"></i> Mark All as Read
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6 flex space-x-4">
                <form method="GET" class="flex space-x-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" class="block w-40 pl-3 pr-10 py-2 text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md focus:ring-navy-500">
                            <option value="All" <?= $statusFilter == 'All' ? 'selected' : '' ?>>All Notifications</option>
                            <option value="Unread" <?= $statusFilter == 'Unread' ? 'selected' : '' ?>>Unread Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Type</label>
                        <select name="type" class="block w-40 pl-3 pr-10 py-2 text-sm border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md focus:ring-navy-500">
                            <option value="All" <?= $typeFilter == 'All' ? 'selected' : '' ?>>All Types</option>
                            <option value="Task Allocated" <?= $typeFilter == 'Task Allocated' ? 'selected' : '' ?>>Task Allocated</option>
                            <option value="Reminder" <?= $typeFilter == 'Reminder' ? 'selected' : '' ?>>Reminders</option>
                            <option value="Announcement" <?= $typeFilter == 'Announcement' ? 'selected' : '' ?>>Announcements</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-600 hover:bg-navy-700">Filter</button>
                </form>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-2/5">Notification</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/5">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/6">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-1/6">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <?php if (empty($notificationsList)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-500">No notifications found based on the current filters.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($notificationsList as $notif): 
                                    $isRead = ($notif['status'] ?? '') !== 'Unread';
                                    $rowClass = $isRead ? 'bg-white dark:bg-slate-800' : 'bg-slate-50 dark:bg-slate-800/80 font-medium';
                                    
                                    $priority = $notif['task_priority'] ?? 'Medium';
                                    $pColor = 'text-blue-600 bg-blue-100';
                                    if ($priority == 'Critical') $pColor = 'text-red-600 bg-red-100';
                                    if ($priority == 'High') $pColor = 'text-orange-600 bg-orange-100';
                                    if ($priority == 'Low') $pColor = 'text-green-600 bg-green-100';
                                ?>
                                <tr class="<?= $rowClass ?> hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                                    <td class="px-6 py-4 align-top">
                                        <div class="text-sm text-slate-900 dark:text-white font-semibold"><?= htmlspecialchars($notif['title']) ?></div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($notif['message']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-300 align-top">
                                        <?= htmlspecialchars($notif['notification_type']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full <?= $pColor ?>">
                                            <?= htmlspecialchars($priority) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400 align-top">
                                        <?= time_elapsed_string_full($notif['created_at']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                        <div class="flex justify-end items-center space-x-3">
                                            <?php if (!$isRead): ?>
                                                <button onclick="markAsReadPage(<?= $notif['notification_id'] ?>)" class="text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 transition-colors flex items-center" title="Mark as read">
                                                    <i data-lucide="check" class="w-4 h-4 mr-1"></i> <span class="text-xs">Read</span>
                                                </button>
                                            <?php endif; ?>
                                            <button class="text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
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

    <script>
        lucide.createIcons();

        // Dark Mode Logic
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        themeToggle.addEventListener('click', () => { htmlElement.classList.toggle('dark'); });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
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
                                const readBgClass = isUnread ? 'bg-blue-50/30 dark:bg-slate-800/80 border-l-4 border-blue-500' : 'bg-transparent border-l-4 border-transparent opacity-75 hover:opacity-100';
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
    </script>
</body>
</html>
