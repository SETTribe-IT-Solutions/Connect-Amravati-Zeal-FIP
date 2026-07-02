<?php
// include/footer.php
// Common footer and scripts for government UI
?>
    <!-- Common Scripts for Lucide Icons and Theme Toggling -->
    <script>
        (() => {
            // Initialize Icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Dark Mode Toggle Logic
            const themeToggle = document.getElementById('themeToggle');
            const htmlEl = document.documentElement;

            if (themeToggle && !themeToggle.dataset.initialized) {
                themeToggle.dataset.initialized = 'true';
                themeToggle.addEventListener('click', () => {
                    const isDark = htmlEl.classList.toggle('dark');
                    localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
                    localStorage.setItem('theme', isDark ? 'dark' : 'light');
                    if (isDark) {
                        htmlEl.classList.remove('light');
                    } else {
                        htmlEl.classList.add('light');
                    }
                    if (typeof destroyAll === 'function') {
                        destroyAll();
                    }
                    if (typeof buildAllCharts === 'function') {
                        buildAllCharts(isDark);
                    }
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            }

            // Sidebar Toggle Logic
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            window.toggleSidebar = function() {
                if (sidebar) {
                    sidebar.classList.toggle('-translate-x-full');
                }
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('hidden');
                }
            };

            const sidebarToggleBtns = document.querySelectorAll('#sidebarToggle');
            sidebarToggleBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleSidebar();
                });
            });
            // Dropdown Toggle Logic
            const profileDropdownBtn = document.getElementById('profileDropdownBtn');
            const profileDropdownMenu = document.getElementById('profileDropdownMenu');
            if(profileDropdownBtn && profileDropdownMenu) {
                profileDropdownBtn.addEventListener('click', (e) => {
                    profileDropdownMenu.classList.toggle('hidden');
                    e.stopPropagation();
                });
                document.addEventListener('click', (e) => {
                    if (!profileDropdownBtn.contains(e.target) && !profileDropdownMenu.contains(e.target)) {
                        profileDropdownMenu.classList.add('hidden');
                    }
                });
            }

            // Notification dropdown toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationBtn && notificationDropdown && !notificationBtn.dataset.initialized) {
                notificationBtn.dataset.initialized = 'true';
                notificationBtn.addEventListener('click', (e) => {
                    notificationDropdown.classList.toggle('hidden');
                    e.stopPropagation();
                });
                document.addEventListener('click', (e) => {
                    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.classList.add('hidden');
                    }
                });
            }

            // Universal Language Toggle Interceptor
            document.addEventListener('click', (e) => {
                const toggleBtn = e.target.closest('a, button');
                if (toggleBtn) {
                    const icon = toggleBtn.querySelector('i[data-lucide="languages"]');
                    if (icon || (e.target.tagName.toLowerCase() === 'i' && e.target.getAttribute('data-lucide') === 'languages')) {
                        e.preventDefault();
                        e.stopPropagation();
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentLang = urlParams.get('lang') || 'en';
                        urlParams.set('lang', currentLang === 'en' ? 'mr' : 'en');
                        window.location.search = urlParams.toString();
                    }
                }
            });
        })();
    </script>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Session Inactivity Auto-Logout (3 minutes) -->
    <script>
    (() => {
        'use strict';

        const TIMEOUT_MS       = 10 * 60 * 1000;   // 10 minutes total
        const WARNING_AT_MS    = 9 * 60 * 1000;    // Show warning at 9:00
        const COUNTDOWN_SEC    = 60;                // 60-second countdown in warning
        const LOGOUT_URL       = 'logout.php?reason=inactivity';
        const TRACKED_EVENTS   = ['mousemove', 'mousedown', 'click', 'keydown', 'scroll', 'touchstart', 'touchmove'];

        let inactivityTimer    = null;
        let warningTimer       = null;
        let countdownInterval  = null;
        let warningShown       = false;
        let lastActivity       = Date.now();

        // ── Reset all timers on user activity ─────────────────────────
        function resetTimers() {
            lastActivity = Date.now();

            // If warning popup is visible, close it and reset
            if (warningShown && typeof Swal !== 'undefined') {
                Swal.close();
                warningShown = false;
            }

            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);

            // Set the warning timer (fires at 2:30)
            warningTimer = setTimeout(showWarning, WARNING_AT_MS);

            // Set the hard logout timer (fires at 3:00)
            inactivityTimer = setTimeout(forceLogout, TIMEOUT_MS);
        }

        // ── Show SweetAlert2 warning with countdown ───────────────────
        function showWarning() {
            if (warningShown) return;
            warningShown = true;

            let secondsLeft = COUNTDOWN_SEC;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Session Expiring!',
                    html: buildCountdownHTML(secondsLeft),
                    showConfirmButton: true,
                    confirmButtonText: '<i class="lucide-mouse-pointer-click" style="width:16px;height:16px;margin-right:6px;vertical-align:middle;"></i> Stay Logged In',
                    confirmButtonColor: '#0054a4',
                    allowOutsideClick: true,
                    allowEscapeKey: true,
                    customClass: {
                        popup: 'rounded-xl shadow-2xl',
                        title: 'text-lg font-semibold',
                    },
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        countdownInterval = setInterval(() => {
                            secondsLeft--;
                            if (content) {
                                content.innerHTML = buildCountdownHTML(secondsLeft);
                            }
                            if (secondsLeft <= 0) {
                                clearInterval(countdownInterval);
                                Swal.close();
                                forceLogout();
                            }
                        }, 1000);
                    },
                    willClose: () => {
                        clearInterval(countdownInterval);
                    }
                }).then((result) => {
                    if (result.isConfirmed || result.isDismissed) {
                        warningShown = false;
                        resetTimers();
                    }
                });
            } else {
                // Fallback if SweetAlert2 is not loaded
                countdownInterval = setInterval(() => {
                    secondsLeft--;
                    if (secondsLeft <= 0) {
                        clearInterval(countdownInterval);
                        forceLogout();
                    }
                }, 1000);
            }
        }

        // ── Build the countdown HTML ──────────────────────────────────
        function buildCountdownHTML(seconds) {
            const barPercent = (seconds / COUNTDOWN_SEC) * 100;
            const barColor = seconds > 15 ? '#f59e0b' : seconds > 5 ? '#ef6c00' : '#ef4444';
            return `
                <div style="text-align:center;padding:8px 0;">
                    <p style="color:#64748b;font-size:14px;margin-bottom:12px;">
                        You will be logged out due to inactivity in
                    </p>
                    <div style="font-size:42px;font-weight:800;color:${barColor};font-family:'Inter',sans-serif;letter-spacing:2px;margin-bottom:14px;">
                        ${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}
                    </div>
                    <div style="background:#e2e8f0;border-radius:999px;height:6px;overflow:hidden;margin:0 20px;">
                        <div style="height:100%;width:${barPercent}%;background:${barColor};border-radius:999px;transition:width 1s linear,background 0.5s ease;"></div>
                    </div>
                    <p style="color:#94a3b8;font-size:12px;margin-top:10px;">
                        Move your mouse or press any key to stay logged in
                    </p>
                </div>
            `;
        }

        // ── Force logout redirect ─────────────────────────────────────
        function forceLogout() {
            clearTimeout(inactivityTimer);
            clearTimeout(warningTimer);
            clearInterval(countdownInterval);
            window.location.href = LOGOUT_URL;
        }

        // ── Throttle helper (don't fire on every mousemove pixel) ─────
        function throttle(fn, delay) {
            let last = 0;
            return function(...args) {
                const now = Date.now();
                if (now - last >= delay) {
                    last = now;
                    fn.apply(this, args);
                }
            };
        }

        // ── Attach event listeners ────────────────────────────────────
        const throttledReset = throttle(resetTimers, 1000); // max 1 reset/sec

        TRACKED_EVENTS.forEach(event => {
            document.addEventListener(event, throttledReset, { passive: true });
        });

        // ── Sync across tabs (if user is active in another tab) ───────
        window.addEventListener('storage', (e) => {
            if (e.key === 'ac_last_activity') {
                resetTimers();
            }
        });

        // Broadcast activity to other tabs
        const originalReset = resetTimers;
        const broadcastReset = function() {
            originalReset();
            try {
                localStorage.setItem('ac_last_activity', Date.now().toString());
            } catch(e) { /* localStorage may be full or disabled */ }
        };

        // Re-attach with broadcast
        TRACKED_EVENTS.forEach(event => {
            document.removeEventListener(event, throttledReset);
        });
        const throttledBroadcastReset = throttle(broadcastReset, 1000);
        TRACKED_EVENTS.forEach(event => {
            document.addEventListener(event, throttledBroadcastReset, { passive: true });
        });

        // ── Initialize timers on page load ────────────────────────────
        resetTimers();

        // Also handle visibility change — reset when user comes back
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                const elapsed = Date.now() - lastActivity;
                if (elapsed >= TIMEOUT_MS) {
                    forceLogout();
                } else if (elapsed >= WARNING_AT_MS) {
                    showWarning();
                }
            }
        });

        console.log('[Session Guard] Inactivity auto-logout initialized (10 min timeout)');
    })();
    </script>
    <?php endif; ?>

</body>
</html>
