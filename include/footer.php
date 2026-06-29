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

            // Dark Mode Toggle Logic (if a button exists with id 'themeToggle')
            const themeToggle = document.getElementById('themeToggle');
            const htmlEl = document.documentElement;

            if (themeToggle) {
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

            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if(notificationBtn && notificationDropdown) {
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
</body>
</html>
