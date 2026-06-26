<?php
// include/footer.php
// Common footer and scripts for government UI
?>
    <!-- Common Scripts for Lucide Icons and Theme Toggling -->
    <script>
        // Initialize Icons
        lucide.createIcons();

        // Dark Mode Toggle Logic (if a button exists with id 'themeToggle')
        const themeToggle = document.getElementById('themeToggle');
        const htmlEl = document.documentElement;

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                htmlEl.classList.toggle('dark');
                // Re-render icons if needed or adjust charts
            });
        }

        // Sidebar Toggle Logic (if a button exists with id 'sidebarToggle')
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (sidebar && sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                if (sidebar.style.display === 'none') {
                    sidebar.style.display = 'flex';
                } else {
                    sidebar.style.display = 'none';
                }
            });
        }
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
    </script>
</body>
</html>
