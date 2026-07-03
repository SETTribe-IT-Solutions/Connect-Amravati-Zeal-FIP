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
                if (!sidebar) return;
                if (window.innerWidth >= 1024) {
                    // Desktop toggle: Toggle lg:hidden to hide sidebar in grid columns
                    sidebar.classList.toggle('lg:hidden');
                } else {
                    // Mobile toggle: Slide in/out and toggle overlay background
                    sidebar.classList.toggle('-translate-x-full');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('hidden');
                    }
                }
            };

            const sidebarToggleBtns = document.querySelectorAll('#sidebarToggle, .sidebar-toggle');
            sidebarToggleBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleSidebar();
                });
            });
            // Premium Profile Dropdown UI Redesign & Toggle logic
            const oldProfileBtn = document.getElementById('profileDropdownBtn');
            const profileDropdownMenu = document.getElementById('profileDropdownMenu');
            if (oldProfileBtn && profileDropdownMenu) {
                // Clone the button and replace it to remove any clashing local page event listeners
                const profileDropdownBtn = oldProfileBtn.cloneNode(true);
                oldProfileBtn.parentNode.replaceChild(profileDropdownBtn, oldProfileBtn);

                // Get name, role & initials from the button to render in the new dropdown header
                const nameEl = profileDropdownBtn.querySelector('span.font-semibold') || profileDropdownBtn.querySelector('span:first-child');
                const roleEl = profileDropdownBtn.querySelector('span.text-xs') || profileDropdownBtn.querySelector('span:last-child');
                
                const userName = nameEl ? nameEl.innerText.trim() : 'User';
                const userRole = roleEl ? roleEl.innerText.trim() : 'Officer';
                
                const currentLang = new URLSearchParams(window.location.search).get('lang') || 'en';
                
                // Redesign profileDropdownMenu HTML dynamically to make it consistent & attractive
                profileDropdownMenu.className = "hidden absolute right-0 mt-2 w-56 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg z-50 transition-all duration-150 ease-out origin-top-right";
                
                const titleLogged = currentLang === 'en' ? 'Logged In As' : 'लॉग इन';
                const labelProfile = currentLang === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट';
                const labelSettings = currentLang === 'en' ? 'Settings' : 'सेटिंग्ज';
                const labelPassword = currentLang === 'en' ? 'Password Change' : 'पासवर्ड बदला';
                const labelLogout = currentLang === 'en' ? 'Logout' : 'लॉगआउट';
                
                profileDropdownMenu.innerHTML = `
                    <div class="px-4 py-3 border-b border-slate-150 dark:border-slate-800">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mb-0.5">${titleLogged}</p>
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">${userName}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate mt-0.5 font-semibold">${userRole}</p>
                    </div>
                    <div class="p-1.5 space-y-0.5">
                        <a href="profile_update.php?lang=${currentLang}" class="flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" style="text-decoration: none;">
                            <i data-lucide="user" class="w-4 h-4 mr-2.5 text-navy-500 dark:text-blue-400"></i>${labelProfile}
                        </a>
                        <a href="settings.php?lang=${currentLang}" class="flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" style="text-decoration: none;">
                            <i data-lucide="settings" class="w-4 h-4 mr-2.5 text-slate-550 dark:text-slate-400"></i>${labelSettings}
                        </a>
                        <a href="passwordChange.php?lang=${currentLang}" class="flex items-center px-3.5 py-2 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" style="text-decoration: none;">
                            <i data-lucide="key" class="w-4 h-4 mr-2.5 text-saffron-500 dark:text-saffron-450"></i>${labelPassword}
                        </a>
                        <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                        <a href="logout.php" class="flex items-center px-3.5 py-2 text-xs font-bold rounded-xl text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors" style="text-decoration: none;">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2.5 text-red-550"></i>${labelLogout}
                        </a>
                    </div>
                `;

                // Add click toggle behavior
                profileDropdownBtn.addEventListener('click', (e) => {
                    const isClosed = profileDropdownMenu.classList.contains('hidden');
                    
                    // Close notification dropdown if open
                    if (notificationDropdown) {
                        notificationDropdown.classList.add('hidden');
                        if (notificationBtn) notificationBtn.classList.remove('bg-white/20');
                    }
                    
                    if (isClosed) {
                        profileDropdownMenu.classList.remove('hidden');
                        profileDropdownBtn.classList.add('bg-white/20');
                    } else {
                        profileDropdownMenu.classList.add('hidden');
                        profileDropdownBtn.classList.remove('bg-white/20');
                    }
                    e.stopPropagation();
                });
                
                document.addEventListener('click', (e) => {
                    if (!profileDropdownBtn.contains(e.target) && !profileDropdownMenu.contains(e.target)) {
                        profileDropdownMenu.classList.add('hidden');
                        profileDropdownBtn.classList.remove('bg-white/20');
                    }
                });
                
                // Re-init lucide icons inside the dropdown
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }

            // Notification dropdown toggle
            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationBtn && notificationDropdown && !notificationBtn.dataset.initialized) {
                notificationBtn.dataset.initialized = 'true';
                notificationBtn.addEventListener('click', (e) => {
                    const isClosed = notificationDropdown.classList.contains('hidden');
                    
                    // Close profile dropdown if open
                    const profileDropdownMenu = document.getElementById('profileDropdownMenu');
                    const profileDropdownBtn = document.getElementById('profileDropdownBtn');
                    if (profileDropdownMenu) {
                        profileDropdownMenu.classList.add('hidden');
                        if (profileDropdownBtn) profileDropdownBtn.classList.remove('bg-white/20');
                    }
                    
                    if (isClosed) {
                        notificationDropdown.classList.remove('hidden');
                        notificationBtn.classList.add('bg-white/20');
                    } else {
                        notificationDropdown.classList.add('hidden');
                        notificationBtn.classList.remove('bg-white/20');
                    }
                    e.stopPropagation();
                });
                document.addEventListener('click', (e) => {
                    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.classList.add('hidden');
                        notificationBtn.classList.remove('bg-white/20');
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

            // Dynamically inject visual page footer to the body for CSS Grid rendering
            const isAuthPage = window.location.pathname.indexOf('login.php') !== -1 || window.location.pathname.indexOf('passwordReset.php') !== -1 || window.location.pathname.indexOf('logout.php') !== -1;
            if (!isAuthPage && !document.querySelector('footer.custom-page-footer')) {
                const footer = document.createElement('footer');
                footer.className = 'custom-page-footer py-6 border-t border-navy-800 text-slate-400 text-xs flex flex-col lg:flex-row items-center justify-between px-8 gap-4 select-none relative overflow-hidden';
                
                const currentLang = new URLSearchParams(window.location.search).get('lang') || 'en';
                const adminLabel = currentLang === 'en' ? 'District Administration, Amravati' : 'जिल्हा प्रशासन, अमरावती';
                const officeLabel = currentLang === 'en' ? 'Collector Office, Amravati' : 'जिल्हाधिकारी कार्यालय, अमरावती';
                const addrLabel = currentLang === 'en' ? 'Collector Office, Amravati, MH - 444601' : 'जिल्हाधिकारी कार्यालय, अमरावती, MH - ४४४६०१';
                const privacyLabel = currentLang === 'en' ? 'Privacy Policy' : 'गोपनीयता धोरण';
                const termsLabel = currentLang === 'en' ? 'Terms of Use' : 'वापरण्याच्या अटी';
                const accessLabel = currentLang === 'en' ? 'Accessibility' : 'प्रवेशयोग्यता';

                footer.innerHTML = `
                    <!-- Background pattern -->
                    <div class="absolute inset-0 bg-[url('assets/images/gov_bg.png')] opacity-10 bg-cover bg-center mix-blend-overlay"></div>
                    
                    <!-- Left side: emblem and text -->
                    <div class="flex items-center space-x-3.5 relative z-10">
                        <img src="assets/images/maharashtra_seal.jpg" alt="Seal of Maharashtra" class="h-12 w-auto" style="filter: invert(1); mix-blend-mode: screen;">
                        <div class="flex flex-col text-left">
                            <span class="font-bold text-white leading-tight">${officeLabel}</span>
                            <span class="text-[10px] text-slate-400 font-medium">${adminLabel}</span>
                        </div>
                    </div>

                    <!-- Middle side: contact info columns -->
                    <div class="flex flex-col sm:flex-row items-center sm:space-x-8 gap-2.5 relative z-10 text-slate-350">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="phone" class="w-4 h-4 text-amber-500"></i>
                            <span class="font-semibold text-xs text-white">0721-2661001</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="mail" class="w-4 h-4 text-blue-400"></i>
                            <span class="font-semibold text-xs text-white">collector.amravati@maharashtra.gov.in</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400"></i>
                            <span class="font-semibold text-xs text-white">${addrLabel}</span>
                        </div>
                    </div>

                    <!-- Right side: Copyright & Policy Links -->
                    <div class="flex flex-col items-center lg:items-end gap-1.5 relative z-10 text-slate-400 font-medium">
                        <span>© 2026 AMRAVATI CONNECT. All rights reserved.</span>
                        <div class="flex items-center space-x-3 text-[10px] text-slate-500">
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${privacyLabel}</a>
                            <span>|</span>
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${termsLabel}</a>
                            <span>|</span>
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${accessLabel}</a>
                        </div>
                    </div>
                `;
                
                const mainEl = document.querySelector('main');
                if (mainEl) {
                    mainEl.appendChild(footer);
                } else {
                    document.body.appendChild(footer);
                }
                
                // Re-init icons in footer
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        })();
    </script>

    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Session Inactivity Auto-Logout (3 minutes) -->
    <script>
    (() => {
        'use strict';

        const TIMEOUT_MS       = 3 * 60 * 1000;   // 3 minutes total
        const WARNING_AT_MS    = 2.5 * 60 * 1000;  // Show warning at 2:30
        const COUNTDOWN_SEC    = 30;                // 30-second countdown in warning
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

        console.log('[Session Guard] Inactivity auto-logout initialized (3 min timeout)');
    })();
    </script>
    <?php endif; ?>

</body>
</html>
