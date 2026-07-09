<?php
// include/footer.php
// Common footer and scripts for government UI
?>
    <!-- ═══════════════════════════════════════════════════════
         GLOBAL SEARCH MODULE — Active on ALL pages
         Handles: live AJAX search, validation, SweetAlert,
         keyboard nav, task-specific validation
    ═══════════════════════════════════════════════════════ -->
    <script>
    (function () {
        'use strict';

        // Only init if the header search box is present on this page
        const input      = document.getElementById('globalSearch');
        const dropdown   = document.getElementById('searchDropdown');
        const spinner    = document.getElementById('searchSpinner');
        const searchIcon = document.getElementById('searchIcon');
        const clearBtn   = document.getElementById('searchClearBtn');
        const sdEmpty    = document.getElementById('sdEmpty');
        const sdResultCnt= document.getElementById('sdResultCount');

        if (!input || !dropdown) return; // page doesn't have the search box

        let debounceTimer    = null;
        let currentFocusIdx  = -1;
        let allItems         = [];

        /* ── Status badge colours ────────────────────────── */
        function badgeClass(badge, type) {
            if (type === 'task') {
                const map = {
                    'Completed':   'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    'Pending':     'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'In Progress': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    'Overdue':     'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    'Escalated':   'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                };
                return map[badge] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
            }
            if (type === 'circular') {
                const map = {
                    'High':   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    'Medium': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                    'Low':    'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                };
                return map[badge] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
            }
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400';
        }

        /* ── Highlight matching text ─────────────────────── */
        function highlight(text, q) {
            if (!q) return escHtml(text);
            const escaped = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escHtml(text).replace(new RegExp('(' + escaped + ')', 'gi'),
                '<mark class="bg-yellow-200 dark:bg-yellow-800/60 rounded px-0.5">$1</mark>');
        }
        function escHtml(s) {
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        /* ── Build one result row ───────────────────────── */
        function buildItem(r, q) {
            const li = document.createElement('li');
            li.className = 'search-result-item group flex items-start gap-3 px-4 py-2.5 cursor-pointer ' +
                           'hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors outline-none';
            li.setAttribute('tabindex', '-1');
            li.dataset.url = r.url;

            const iconMap = { task: 'check-square', officer: 'user', circular: 'megaphone' };
            const icon = iconMap[r.type] || 'search';

            const typeColorMap = {
                task:    'bg-navy-50 dark:bg-navy-900/40 text-navy-600 dark:text-blue-400',
                officer: 'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400',
                circular:'bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
            };

            li.innerHTML = `
                <span class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center ${typeColorMap[r.type] || ''}">
                    <i data-lucide="${icon}" class="w-4 h-4"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 dark:text-white truncate">${highlight(r.title, q)}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">${escHtml(r.subtitle)}</p>
                </div>
                ${r.badge ? `<span class="flex-shrink-0 self-center text-[10px] font-semibold px-1.5 py-0.5 rounded-full ${badgeClass(r.badge, r.type)}">${escHtml(r.badge)}</span>` : ''}
            `;

            li.addEventListener('click', () => {
                if (r.type === 'officer' && typeof openOfficerDetailsModal === 'function' && r.details) {
                    closeDropdown();
                    openOfficerDetailsModal(r.details);
                } else if (r.type === 'circular' && typeof openCircularDetailsModal === 'function' && r.details) {
                    closeDropdown();
                    openCircularDetailsModal(r.details);
                } else {
                    navigate(r.url);
                }
            });
            li.addEventListener('keydown', handleItemKeydown);
            return li;
        }

        /* ── Render all results ─────────────────────────── */
        function renderResults(results, q) {
            const tasks    = results.filter(r => r.type === 'task');
            const officers = results.filter(r => r.type === 'officer');
            const circs    = results.filter(r => r.type === 'circular');

            const sdTasks    = document.getElementById('sdTasks');
            const sdOfficers = document.getElementById('sdOfficers');
            const sdCircs    = document.getElementById('sdCirculars');
            const listT      = document.getElementById('sdTaskList');
            const listO      = document.getElementById('sdOfficerList');
            const listC      = document.getElementById('sdCircularList');

            if (listT) listT.innerHTML = '';
            if (listO) listO.innerHTML = '';
            if (listC) listC.innerHTML = '';
            allItems = [];

            if (tasks.length && sdTasks && listT) {
                sdTasks.classList.remove('hidden');
                tasks.forEach(r => { const li = buildItem(r, q); listT.appendChild(li); allItems.push(li); });
            } else if (sdTasks) { sdTasks.classList.add('hidden'); }

            if (officers.length && sdOfficers && listO) {
                sdOfficers.classList.remove('hidden');
                officers.forEach(r => { const li = buildItem(r, q); listO.appendChild(li); allItems.push(li); });
            } else if (sdOfficers) { sdOfficers.classList.add('hidden'); }

            if (circs.length && sdCircs && listC) {
                sdCircs.classList.remove('hidden');
                circs.forEach(r => { const li = buildItem(r, q); listC.appendChild(li); allItems.push(li); });
            } else if (sdCircs) { sdCircs.classList.add('hidden'); }

            const total = results.length;
            if (sdEmpty) sdEmpty.classList.toggle('hidden', total > 0);
            if (sdResultCnt) sdResultCnt.textContent = total > 0 ? total + ' result' + (total !== 1 ? 's' : '') : '';

            if (typeof lucide !== 'undefined') lucide.createIcons();
            currentFocusIdx = -1;
        }

        /* ── Navigate to URL ────────────────────────────── */
        function navigate(url) {
            if (url) window.location.href = url;
        }

        /* ── Open / close dropdown ──────────────────────── */
        function openDropdown()  { dropdown.classList.remove('hidden'); }
        function closeDropdown() { dropdown.classList.add('hidden'); currentFocusIdx = -1; }

        /* ── Show/hide spinner ──────────────────────────── */
        function setLoading(on) {
            if (spinner)    spinner.classList.toggle('hidden', !on);
            if (searchIcon) searchIcon.classList.toggle('hidden', on);
        }

        /* ── Keyboard navigation inside dropdown ────────── */
        function handleItemKeydown(e) {
            if (e.key === 'Enter')    { e.preventDefault(); e.currentTarget.click(); }
            if (e.key === 'ArrowDown'){ e.preventDefault(); focusItem(currentFocusIdx + 1); }
            if (e.key === 'ArrowUp')  { e.preventDefault(); focusItem(currentFocusIdx - 1); }
            if (e.key === 'Escape')   { e.preventDefault(); closeDropdown(); input.focus(); }
        }

        function focusItem(idx) {
            if (!allItems.length) return;
            currentFocusIdx = Math.max(0, Math.min(idx, allItems.length - 1));
            allItems[currentFocusIdx].focus();
        }

        /* ── Input Validation ───────────────────────────── */
        function validateSearchInput(q) {
            if (!q || q.trim().length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Search Input Required',
                        text: 'Please enter a search term before searching.',
                        confirmButtonColor: '#0054a4',
                        confirmButtonText: 'OK'
                    });
                }
                return false;
            }
            if (q.trim().length < 2) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Search Too Short',
                        text: 'Please enter at least 2 characters to search.',
                        confirmButtonColor: '#0054a4',
                        confirmButtonText: 'OK'
                    });
                }
                return false;
            }
            return true;
        }

        /* ── Show No-Results SweetAlert ─────────────────── */
        function showNoResultsAlert(q) {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                icon: 'info',
                title: 'No Records Found',
                html: `<p>No results found for <strong>"${escHtml(q)}"</strong>.</p>
                       <p class="text-sm text-slate-500 mt-1">Please enter correct data and try again.</p>`,
                confirmButtonColor: '#0054a4',
                confirmButtonText: 'OK'
            });
        }

        /* ── Fetch from API ─────────────────────────────── */
        function doSearch(q, showAlertOnEmpty) {
            if (q.length < 2) { closeDropdown(); setLoading(false); return; }
            setLoading(true);
            fetch('api/search.php?q=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(data => {
                    setLoading(false);
                    if (data.status === 'ok') {
                        renderResults(data.results, q);
                        if (data.results.length > 0) {
                            openDropdown();
                        } else {
                            closeDropdown();
                            if (showAlertOnEmpty) {
                                showNoResultsAlert(q);
                            }
                        }
                    } else if (data.status === 'not_found') {
                        // Explicit not_found from API — task ID searched doesn't exist
                        closeDropdown();
                        if (showAlertOnEmpty && typeof Swal !== 'undefined') {
                            const totalTasks = data.total_tasks || 0;
                            const searchedId = data.searched_id || '';
                            Swal.fire({
                                icon: 'warning',
                                title: 'Task Not Found',
                                html: `<p>Task <strong>#${escHtml(String(searchedId))}</strong> does not exist.</p>
                                       ${totalTasks > 0 ? `<p class="text-sm text-slate-500 mt-2">Only <strong>${totalTasks}</strong> task${totalTasks !== 1 ? 's' : ''} exist in the system (Task #1 to #${totalTasks}).</p>` : ''}
                                       <p class="text-sm text-slate-500 mt-1">Please enter a valid task number and try again.</p>`,
                                confirmButtonColor: '#0054a4',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                })
                .catch(() => { setLoading(false); });
        }

        /* ── Wire up input events ───────────────────────── */
        input.addEventListener('input', function () {
            const q = this.value.trim();
            if (clearBtn) clearBtn.classList.toggle('hidden', q.length === 0);

            if (typeof filterRows === 'function') {
                filterRows();
            }

            clearTimeout(debounceTimer);
            if (q.length < 2) { closeDropdown(); setLoading(false); return; }
            debounceTimer = setTimeout(() => doSearch(q, false), 280);
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown' && !dropdown.classList.contains('hidden')) {
                e.preventDefault(); focusItem(0);
            }
            if (e.key === 'Escape') { 
                closeDropdown(); 
                this.value = ''; 
                if (clearBtn) clearBtn.classList.add('hidden'); 
                if (typeof filterRows === 'function') {
                    filterRows();
                }
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = this.value.trim();
                if (!dropdown.classList.contains('hidden') && allItems.length) {
                    allItems[0].click();
                } else {
                    // Validate, then search with SweetAlert on no results
                    if (!validateSearchInput(q)) return;
                    clearTimeout(debounceTimer);
                    doSearch(q, true);
                }
            }
        });

        /* ── Clear button ───────────────────────────────── */
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                clearBtn.classList.add('hidden');
                closeDropdown();
                if (typeof filterRows === 'function') {
                    filterRows();
                }
                input.focus();
            });
        }

        /* ── Close on outside click ─────────────────────── */
        document.addEventListener('click', function (e) {
            const wrapper = document.getElementById('searchWrapper');
            if (wrapper && !wrapper.contains(e.target)) closeDropdown();
        });

        /* ── Keyboard shortcut '/' to focus search ──────── */
        document.addEventListener('keydown', function (e) {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                input.focus();
                input.select();
            }
        });

    })();
    </script>

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
                profileDropdownMenu.className = "hidden absolute right-0 top-full mt-2 w-56 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg z-50 transition-all duration-150 ease-out origin-top-right text-left";
                
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
<<<<<<< HEAD
<<<<<<< HEAD
                footer.className = 'custom-page-footer border-t border-navy-800 text-slate-400 text-xs select-none relative overflow-hidden';
                footer.style.cssText = 'padding: 10px 24px; min-height: 0;';
=======
                footer.className = 'custom-page-footer py-3.5 border-t border-navy-800 text-slate-400 text-xs flex flex-col lg:flex-row items-center justify-between px-8 gap-4 select-none relative overflow-hidden';
>>>>>>> b1f55568d96b16976fcff78a4f39436bc283bace
=======
                footer.className = 'custom-page-footer py-3.5 border-t border-navy-800 text-slate-400 text-xs flex flex-col lg:flex-row items-center justify-between px-8 gap-4 select-none relative overflow-hidden';
>>>>>>> b1f55568d96b16976fcff78a4f39436bc283bace
                
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
                    
<<<<<<< HEAD
                    <!-- Single compact row -->
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; position:relative; z-index:10; flex-wrap:wrap;">
                        
                        <!-- Left: Emblem + Office name -->
                        <div style="display:flex; align-items:center; gap:10px; flex-shrink:0;">
                            <img src="assets/images/maharashtra_seal.jpg" alt="Seal of Maharashtra" style="height:32px; width:auto; filter:invert(1); mix-blend-mode:screen;">
                            <div style="display:flex; flex-direction:column; line-height:1.3;">
                                <span style="font-weight:700; color:#fff; font-size:11px; white-space:nowrap;">${officeLabel}</span>
                                <span style="font-size:9px; color:#94a3b8;">${adminLabel}</span>
                            </div>
=======
                    <!-- Left side: emblem and text -->
                    <div class="flex items-center space-x-3 relative z-10">
                        <img src="assets/images/maharashtra_seal.jpg" alt="Seal of Maharashtra" class="h-9 w-auto" style="filter: invert(1); mix-blend-mode: screen;">
                        <div class="flex flex-col text-left">
                            <span class="font-semibold text-xs text-white leading-tight">${officeLabel}</span>
                            <span class="text-[9px] text-slate-450 font-medium">${adminLabel}</span>
<<<<<<< HEAD
>>>>>>> b1f55568d96b16976fcff78a4f39436bc283bace
=======
>>>>>>> b1f55568d96b16976fcff78a4f39436bc283bace
                        </div>

<<<<<<< HEAD
                        <!-- Center: Contact info in one row -->
                        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap; justify-content:center;">
                            <div style="display:flex; align-items:center; gap:5px;">
                                <i data-lucide="phone" style="width:12px; height:12px; color:#f59e0b; flex-shrink:0;"></i>
                                <span style="color:#fff; font-size:11px; font-weight:600; white-space:nowrap;">0721-2661001</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:5px;">
                                <i data-lucide="mail" style="width:12px; height:12px; color:#60a5fa; flex-shrink:0;"></i>
                                <span style="color:#fff; font-size:11px; font-weight:600; white-space:nowrap;">collector.amravati@maharashtra.gov.in</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:5px;">
                                <i data-lucide="map-pin" style="width:12px; height:12px; color:#34d399; flex-shrink:0;"></i>
                                <span style="color:#fff; font-size:11px; font-weight:600; white-space:nowrap;">${addrLabel}</span>
                            </div>
                        </div>

                        <!-- Right: Copyright & Links -->
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:3px; flex-shrink:0;">
                            <span style="color:#cbd5e1; font-size:10px; white-space:nowrap;">© 2026 AMRAVATI CONNECT. All rights reserved.</span>
                            <div style="display:flex; align-items:center; gap:8px; font-size:10px; color:#64748b;">
                                <a href="#" style="color:#64748b; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#64748b'">${privacyLabel}</a>
                                <span>|</span>
                                <a href="#" style="color:#64748b; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#64748b'">${termsLabel}</a>
                                <span>|</span>
                                <a href="#" style="color:#64748b; text-decoration:none; transition:color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#64748b'">${accessLabel}</a>
                            </div>
=======
                    <!-- Middle side: contact info columns -->
                    <div class="flex flex-col sm:flex-row items-center sm:space-x-6 gap-2.5 relative z-10 text-slate-350 text-[11px]">
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-amber-500"></i>
                            <a href="tel:07212661001" class="font-semibold text-white hover:text-amber-400 transition-colors" style="text-decoration: none;">0721-2661001</a>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-blue-400"></i>
                            <a href="mailto:collector.amravati@maharashtra.gov.in" class="font-semibold text-white hover:text-blue-300 transition-colors" style="text-decoration: none;">collector.amravati@maharashtra.gov.in</a>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <a href="https://maps.google.com/?q=Collector+Office,+Amravati" target="_blank" class="font-semibold text-white hover:text-emerald-350 transition-colors" style="text-decoration: none;">${addrLabel}</a>
                        </div>
                    </div>

                    <!-- Right side: Copyright & Policy Links -->
                    <div class="flex flex-col items-center lg:items-end gap-1 relative z-10 text-slate-400 font-medium text-[11px]">
                        <span>© 2026 AMRAVATI CONNECT. All rights reserved.</span>
                        <div class="flex items-center space-x-3 text-[10px] text-slate-500">
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${privacyLabel}</a>
                            <span>|</span>
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${termsLabel}</a>
                            <span>|</span>
                            <a href="#" class="hover:text-white transition-colors" style="text-decoration: none;">${accessLabel}</a>
>>>>>>> b1f55568d96b16976fcff78a4f39436bc283bace
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
