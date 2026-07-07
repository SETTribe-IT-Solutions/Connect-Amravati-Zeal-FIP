<?php
$files = [
    'user_creation.php',
    'reports.php',
    'notifications.php',
    'graph.php',
    'dashboard.php',
    'create_task.php',
    'blank_wrushabh.php',
    'announcements.php',
    'task_tracking.php'
];

$replacement = <<<'HTML'
            <!-- Profile dropdown container -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName ?? 'User') ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole ?? $roleLabel ?? 'Officer') ?></span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
                        <?= htmlspecialchars($initials ?? 'U') ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 text-left">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordChange.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i><?= ($lang ?? 'en') === 'en' ? 'Logout' : 'लॉगआउट' ?>
                        </a>
                    </div>
                </div>
            </div>
HTML;

$js_html = <<<'HTML'

<script>
    // Profile Dropdown Toggle
    document.addEventListener('DOMContentLoaded', () => {
        const profileDropdownBtn = document.getElementById('profileDropdownBtn');
        const profileDropdownMenu = document.getElementById('profileDropdownMenu');
        if (profileDropdownBtn && profileDropdownMenu) {
            profileDropdownBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                profileDropdownMenu.classList.toggle('hidden');
            });
            document.addEventListener('click', () => {
                profileDropdownMenu.classList.add('hidden');
            });
            profileDropdownMenu.addEventListener('click', (e) => e.stopPropagation());
        }
    });
</script>
HTML;

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);

    // Skip if already has dropdown
    if (strpos($content, 'id="profileDropdownBtn"') !== false) {
        echo "Skipping $file, already has dropdown.<br>\n";
        continue;
    }

    $pattern = '/(?:<!-- Profile -->\s*)?<div class="flex items-center (?:space-x-3|gap-3) border-l border-slate-200.*?<\/div>\s*<\/div>\s*(?=<\/div>\s*<\/header>)/s';
    
    if (preg_match($pattern, $content, $matches)) {
        // Output matched block to a log for verification
        file_put_contents('apply_dropdown_log.txt', "MATCH IN $file:\n" . $matches[0] . "\n\n-----------------\n\n", FILE_APPEND);
        
        $replaced = preg_replace($pattern, $replacement, $content, 1, $count);
        if ($count > 0) {
            // Append JS before </body>
            if (strpos($replaced, '</body>') !== false && strpos($replaced, 'Profile Dropdown Toggle') === false) {
                $replaced = str_replace('</body>', $js_html . "\n</body>", $replaced);
            }
            file_put_contents($file, $replaced);
            echo "Updated $file using pattern.<br>\n";
        }
    } else {
        echo "Could not match header for $file.<br>\n";
    }
}
?>
