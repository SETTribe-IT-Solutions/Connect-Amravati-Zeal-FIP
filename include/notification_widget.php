<?php
/**
 * =============================================================
 *  include/notification_widget.php  |  Reusable Notification Bell
 * =============================================================
 *  Include this file ONCE in any page that already has:
 *    - Lucide icons loaded (<script src="https://unpkg.com/lucide@latest">)
 *    - The page must have $lang set ('en' or 'mr')
 *
 *  It injects:
 *    1.  The notification bell + dropdown HTML
 *    2.  A self-contained <script> block (polling, mark-read, chime, etc.)
 *
 *  Usage:
 *    Inside a header <div>, simply:
 *      <?php include 'include/notification_widget.php'; ?>
 *
 *  Requirements:
 *    - api/get_notifications.php must exist
 *    - api/mark_notification_read.php must exist
 *    - api/task_notification_actions.php must exist
 * =============================================================
 */

// Make sure $lang is available
if (!isset($lang)) {
    $lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
}
?>

<!-- ── Notification Bell Widget ─────────────────────────────── -->
<div class="relative" id="notifWidgetWrap">
    <button id="notificationBtn" class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
        <i data-lucide="bell" class="w-5 h-5"></i>
        <span id="unreadCountBadge" style="display:none;" class="absolute top-0 right-0 flex items-center justify-center h-4 w-4 text-[10px] font-bold text-white rounded-full bg-orange-500 ring-2 ring-white dark:ring-slate-900">0</span>
    </button>
    <!-- Dropdown -->
    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 z-50">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-t-lg">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">
                <?= $lang === 'en' ? 'Notification Center' : 'सूचना केंद्र' ?>
            </h3>
            <button onclick="markAllAsRead()" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">
                <?= $lang === 'en' ? 'Mark all as read' : 'सर्व वाचलेले म्हणून चिन्हांकित करा' ?>
            </button>
        </div>
        <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
            <!-- Populated via AJAX -->
        </div>
        <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-lg">
            <a href="notifications.php?lang=<?= $lang ?>" class="block w-full text-center px-4 py-3 text-xs font-medium text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                <?= $lang === 'en' ? 'View All Notifications' : 'सर्व सूचना पहा' ?>
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    // ── Notification JS (self-contained, isolated from page scripts) ──
    const notifBtn      = document.getElementById('notificationBtn');
    const notifDropdown = document.getElementById('notificationDropdown');
    const notifBadge    = document.getElementById('unreadCountBadge');
    const notifList     = document.getElementById('notificationList');

    if (!notifBtn || !notifDropdown) return;

    notifBtn.addEventListener('click', () => notifDropdown.classList.toggle('hidden'));

    document.addEventListener('click', (e) => {
        if (!notifBtn.contains(e.target) && !notifDropdown.contains(e.target)) {
            notifDropdown.classList.add('hidden');
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
                        notifBadge.style.display = 'flex';
                        notifBadge.innerText = data.unread_count > 99 ? '99+' : data.unread_count;
                    } else {
                        notifBadge.style.display = 'none';
                    }
                    notifList.innerHTML = '';
                    if (data.notifications.length === 0) {
                        notifList.innerHTML = '<div class="px-4 py-6 text-center text-sm text-slate-500">No new notifications</div>';
                    } else {
                        data.notifications.forEach(n => {
                            const isUnread = n.is_read == 0;
                            const readBgClass = isUnread
                                ? 'bg-blue-50/30 dark:bg-slate-800/80 border-l-4 border-blue-500 font-medium'
                                : 'bg-transparent border-l-4 border-transparent opacity-75 hover:opacity-100';
                            const titleWeight = isUnread
                                ? 'font-bold text-slate-900 dark:text-white'
                                : 'font-medium text-slate-700 dark:text-slate-300';
                            const dotIndicator = isUnread
                                ? '<span class="absolute top-4 right-4 w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_5px_rgba(59,130,246,0.6)]"></span>'
                                : '';

                            let actionsHtml = '';
                            if (n.actions && n.actions.length > 0) {
                                actionsHtml += '<div class="mt-2 flex flex-wrap gap-1.5" onclick="event.stopPropagation();">';
                                n.actions.forEach(act => {
                                    if (act.action === 'accept') {
                                        actionsHtml += `<button onclick="acceptTask(${n.task_id}, ${n.id})" class="px-2 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-[10px] font-bold transition-colors">Accept</button>`;
                                    } else if (act.action === 'reject') {
                                        actionsHtml += `<button onclick="openRejectTaskModal(${n.task_id})" class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-[10px] font-bold transition-colors">Reject</button>`;
                                    } else if (act.action === 'verify_rejection') {
                                        actionsHtml += `<button onclick="openReviewRejectionModal(${n.task_id})" class="px-2 py-1 bg-blue-700 hover:bg-blue-800 text-white rounded text-[10px] font-bold transition-colors">Verify Rejection</button>`;
                                    } else if (act.action === 'verify_completion') {
                                        actionsHtml += `<button onclick="verifyCompletion(${n.task_id}, ${n.id})" class="px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded text-[10px] font-bold transition-colors">Verify Completion</button>`;
                                    }
                                });
                                actionsHtml += '</div>';
                            }

                            const item = document.createElement('div');
                            item.className = `relative px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-all duration-200 ${readBgClass}`;
                            item.innerHTML = `
                                ${dotIndicator}
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mt-0.5 shadow-sm ${n.badge_color || 'bg-blue-100 text-blue-600'}">
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
                            notifList.appendChild(item);
                        });
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    }
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Global functions (accessible from action buttons)
    window.acceptTask = function(taskId, notifId) {
        fetch('api/task_notification_actions.php?action=accept&task_id=' + taskId)
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    if (notifId) markAsRead(notifId);
                    fetchNotifications();
                }
            });
    };

    window.verifyCompletion = function(taskId, notifId) {
        fetch(`api/task_notification_actions.php?action=verify&task_id=${taskId}`)
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    if (notifId) markAsRead(notifId);
                    fetchNotifications();
                }
            });
    };

    window.openRejectTaskModal = function(taskId) {
        const modal = document.getElementById('rejectTaskModal');
        if (modal) {
            document.getElementById('rejectTaskId').value = taskId;
            modal.classList.remove('hidden');
        } else {
            alert('Rejection modal not available on this page. Visit the dashboard to reject tasks.');
        }
    };

    window.openReviewRejectionModal = function(taskId) {
        const modal = document.getElementById('reviewRejectionModal');
        if (modal) {
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
                        modal.classList.remove('hidden');
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    } else {
                        alert(res.message);
                    }
                });
        } else {
            alert('Review modal not available on this page. Visit the dashboard to review rejections.');
        }
    };

    window.markAsRead = function(id) {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ notification_id: id })
        }).then(() => fetchNotifications());
    };

    window.markAllAsRead = function() {
        fetch('api/mark_notification_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mark_all: true })
        }).then(() => fetchNotifications());
    };

    // Start polling
    setInterval(fetchNotifications, 5000);
    fetchNotifications();
})();
</script>
