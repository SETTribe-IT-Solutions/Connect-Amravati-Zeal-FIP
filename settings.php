<?php
/**
 * Settings Page
 * Amravati Connect - Government Workflow Platform
 */

session_start();
require_once 'include/dbConfig.php';

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

// Bilingual translations dictionary
$translations = [
    'en' => [
        'title' => 'Settings — Amravati Connect',
        'heading' => 'Account Settings & Preferences',
        'subheading' => 'Manage your system theme, preferred language, and notification alerts.',
        'label_theme' => 'Appearance Theme',
        'label_theme_desc' => 'Switch between light and dark visual aesthetics.',
        'label_lang' => 'Default Language',
        'label_lang_desc' => 'Preferred interface language for the dashboard.',
        'label_notif' => 'System Notifications',
        'label_notif_desc' => 'Enable or disable sound alerts and instant popups.',
        'btn_save' => 'Save Preferences',
        'msg_success' => 'Preferences updated successfully.',
        'back_to_dashboard' => 'Back to Dashboard',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
        'menu_notifications' => 'Notification Center',
        'menu_analytics' => 'Analytics & Data',
        'menu_reports' => 'Reports & Analytics',
        'menu_users' => 'User Management',
        'menu_logout' => 'Logout',
        'menu_main_modules' => 'Main Modules',
        'opt_light' => 'Light Theme',
        'opt_dark' => 'Dark Theme',
        'opt_enabled' => 'Enabled',
        'opt_disabled' => 'Disabled',
        'label_email_notif' => 'Email Notifications',
        'label_email_notif_desc' => 'Frequency of email reports and updates.',
        'opt_daily' => 'Daily Summary',
        'opt_immediate' => 'Immediate',
        'label_2fa' => 'Two-Factor Authentication',
        'label_2fa_desc' => 'Add an extra layer of security to your account.',
        'label_pwd' => 'Account Password',
        'label_pwd_desc' => 'Update your login password regularly.',
        'btn_change_pwd' => 'Change Password',
        'label_data' => 'Data Export',
        'label_data_desc' => 'Download all your personal account data.',
        'btn_export_data' => 'Export My Data'
    ],
    'mr' => [
        'title' => 'सेटिंग्ज — अमरावती कनेक्ट',
        'heading' => 'खाते सेटिंग्ज आणि प्राधान्ये',
        'subheading' => 'तुमची सिस्टम थीम, पसंतीची भाषा आणि सूचना सूचना व्यवस्थापित करा.',
        'label_theme' => 'देखावा थीम (Theme)',
        'label_theme_desc' => 'प्रकाश (Light) आणि गडद (Dark) देखावा बदलण्यासाठी निवडा.',
        'label_lang' => 'पसंतीची भाषा',
        'label_lang_desc' => 'डॅशबोर्डसाठी पसंतीची भाषा.',
        'label_notif' => 'प्रणाली सूचना (System Notifications)',
        'label_notif_desc' => 'ध्वनी इशारे आणि झटपट पॉपअप सक्षम किंवा अक्षम करा.',
        'btn_save' => 'प्राधान्ये जतन करा',
        'msg_success' => 'प्राधान्ये यशस्वीरित्या अद्यतनित केली गेली.',
        'back_to_dashboard' => 'डॅशबोर्डवर परत जा',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_analytics' => 'विश्लेषण आणि डेटा',
        'menu_reports' => 'अहवाल आणि विश्लेषण',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'menu_logout' => 'लॉगआउट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'opt_light' => 'प्रकाश देखावा (Light Theme)',
        'opt_dark' => 'गडद देखावा (Dark Theme)',
        'opt_enabled' => 'सक्षम (Enabled)',
        'opt_disabled' => 'अक्षम (Disabled)',
        'label_email_notif' => 'ईमेल सूचना (Email Notifications)',
        'label_email_notif_desc' => 'ईमेल अहवाल आणि अद्यतनांची वारंवारता.',
        'opt_daily' => 'दैनिक सारांश (Daily Summary)',
        'opt_immediate' => 'तात्काळ (Immediate)',
        'label_2fa' => 'दोन-घटक प्रमाणीकरण (2FA)',
        'label_2fa_desc' => 'तुमच्या खात्यामध्ये सुरक्षिततेचा अतिरिक्त स्तर जोडा.',
        'label_pwd' => 'खाते पासवर्ड (Account Password)',
        'label_pwd_desc' => 'तुमचा लॉगिन पासवर्ड नियमितपणे अपडेट करा.',
        'btn_change_pwd' => 'पासवर्ड बदला (Change Password)',
        'label_data' => 'डेटा निर्यात (Data Export)',
        'label_data_desc' => 'तुमचा सर्व वैयक्तिक खाते डेटा डाउनलोड करा.',
        'btn_export_data' => 'माझा डेटा निर्यात करा'
    ]
];
$t = $translations[$lang];

$user_id = $_SESSION['user_id'] ?? 1;
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save in cookies or session for demonstration
    $_SESSION['pref_theme'] = $_POST['theme'] ?? 'light';
    $_SESSION['pref_notif'] = $_POST['notifications'] ?? 'enabled';
    $_SESSION['pref_email'] = $_POST['email_notifications'] ?? 'daily';
    $_SESSION['pref_2fa'] = isset($_POST['two_factor']) ? true : false;
    
    $new_lang = $_POST['lang'] ?? 'en';
    $success_msg = $t['msg_success'];
    
    if ($new_lang !== $lang) {
        header("Location: settings.php?lang=$new_lang");
        exit();
    }
}

$pref_theme = $_SESSION['pref_theme'] ?? 'light';
$pref_notif = $_SESSION['pref_notif'] ?? 'enabled';
$pref_email = $_SESSION['pref_email'] ?? 'daily';
$pref_2fa   = $_SESSION['pref_2fa'] ?? false;

// Fetch user data for initials
$user_data = [];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$sName = $_SESSION['full_name'] ?? $user_data['full_name'] ?? 'Government Employee';
$sRole = $_SESSION['role_name'] ?? 'Officer';

$parts = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'O', 0, 1) . substr($parts[1] ?? '', 0, 1));
?>
<?php
$pageTitle = $t['title'];
$pageDesc = $t['page_subtitle'] ?? 'Platform configuration and preferences.';
$extraHead = <<<'EOT'
    <style>
        .glass-panel { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .dark .glass-panel { background: rgba(15,23,42,0.7); border: 1px solid rgba(255,255,255,0.05); }
    </style>
EOT;
include 'include/header.php';
$activePage = 'settings';
include 'include/sidebar.php';
?>

<!-- MAIN WRAPPER -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10">
        <div class="flex items-center flex-1">
            <nav class="flex items-center text-sm">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white">Settings</span>
            </nav>
        </div>
        <div class="flex items-center space-x-4">
            <a href="settings.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="flex items-center text-sm border px-3 py-1.5 rounded-md text-slate-700 dark:text-slate-300">
                <i data-lucide="languages" class="w-4 h-4 mr-2"></i>
                <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
            </a>
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            
            <!-- Notifications -->
            <?php include 'include/notification_widget.php'; ?>
            
            <!-- Profile dropdown container -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($sRole) ?>
                            <?= ' (' . htmlspecialchars($headerLocationDisplay) . ')' ?>
                        </span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 text-left">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= $lang==='en'?'User Profile Update':'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= $lang==='en'?'Settings':'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordChange.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i><?= $lang==='en'?'Password Change':'पासवर्ड बदला' ?>
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
        <div class="max-w-2xl mx-auto glass-panel rounded-2xl shadow-official border border-slate-200/50 dark:border-slate-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <h1 class="text-xl font-bold"><?= htmlspecialchars($t['heading']) ?></h1>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($t['subheading']) ?></p>
            </div>
            
            <form method="POST" class="p-6 space-y-6">
                <?php if ($success_msg): ?>
                    <div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-200 text-sm"><?= $success_msg ?></div>
                <?php endif; ?>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_theme']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_theme_desc']) ?></span>
                    </div>
                    <select name="theme" class="px-3 py-1.5 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700 text-sm">
                        <option value="light" <?= $pref_theme==='light'?'selected':'' ?>><?= htmlspecialchars($t['opt_light']) ?></option>
                        <option value="dark" <?= $pref_theme==='dark'?'selected':'' ?>><?= htmlspecialchars($t['opt_dark']) ?></option>
                    </select>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_lang']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_lang_desc']) ?></span>
                    </div>
                    <select name="lang" class="px-3 py-1.5 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700 text-sm">
                        <option value="en" <?= $lang==='en'?'selected':'' ?>>English (EN)</option>
                        <option value="mr" <?= $lang==='mr'?'selected':'' ?>>मराठी (MR)</option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_notif']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_notif_desc']) ?></span>
                    </div>
                    <select name="notifications" class="px-3 py-1.5 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700 text-sm">
                        <option value="enabled" <?= $pref_notif==='enabled'?'selected':'' ?>><?= htmlspecialchars($t['opt_enabled']) ?></option>
                        <option value="disabled" <?= $pref_notif==='disabled'?'selected':'' ?>><?= htmlspecialchars($t['opt_disabled']) ?></option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_email_notif']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_email_notif_desc']) ?></span>
                    </div>
                    <select name="email_notifications" class="px-3 py-1.5 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700 text-sm">
                        <option value="daily" <?= $pref_email==='daily'?'selected':'' ?>><?= htmlspecialchars($t['opt_daily']) ?></option>
                        <option value="immediate" <?= $pref_email==='immediate'?'selected':'' ?>><?= htmlspecialchars($t['opt_immediate']) ?></option>
                        <option value="disabled" <?= $pref_email==='disabled'?'selected':'' ?>><?= htmlspecialchars($t['opt_disabled']) ?></option>
                    </select>
                </div>

                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_2fa']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_2fa_desc']) ?></span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="two_factor" value="1" <?= $pref_2fa ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-navy-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_pwd']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_pwd_desc']) ?></span>
                    </div>
                    <button type="button" onclick="Swal.fire({icon: 'info', title: '<?= htmlspecialchars($t['btn_change_pwd']) ?>', text: 'This feature is coming soon.', confirmButtonColor: '#0069cd'})" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors">
                        <?= htmlspecialchars($t['btn_change_pwd']) ?>
                    </button>
                </div>

                <div class="flex items-center justify-between pt-4 border-t dark:border-slate-700">
                    <div>
                        <label class="block text-sm font-semibold"><?= htmlspecialchars($t['label_data']) ?></label>
                        <span class="text-xs text-slate-400"><?= htmlspecialchars($t['label_data_desc']) ?></span>
                    </div>
                    <button type="button" onclick="Swal.fire({icon: 'info', title: '<?= htmlspecialchars($t['btn_export_data']) ?>', text: 'Your data archive is being prepared. We will notify you when it is ready to download.', confirmButtonColor: '#0069cd'})" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 transition-colors">
                        <i data-lucide="download" class="w-4 h-4 inline-block mr-1"></i><?= htmlspecialchars($t['btn_export_data']) ?>
                    </button>
                </div>

                <div class="pt-6 flex items-center justify-between border-t dark:border-slate-700">
                    <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm"><?= htmlspecialchars($t['btn_save']) ?></button>
                    <a href="dashboard.php?lang=<?= $lang ?>" class="text-sm text-navy-600 dark:text-blue-400 font-medium hover:underline"><?= htmlspecialchars($t['back_to_dashboard']) ?></a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();
    
    const htmlEl = document.documentElement;
    const themeSelect = document.querySelector('select[name="theme"]');
    
    function applyTheme(isDark) {
        if (isDark) {
            htmlEl.classList.add('dark');
            if (themeSelect) themeSelect.value = 'dark';
        } else {
            htmlEl.classList.remove('dark');
            if (themeSelect) themeSelect.value = 'light';
        }
        localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }

    // Set initial dropdown value
    if (themeSelect) {
        themeSelect.value = htmlEl.classList.contains('dark') ? 'dark' : 'light';
        
        // Listen for changes on select dropdown
        themeSelect.addEventListener('change', (e) => {
            applyTheme(e.target.value === 'dark');
        });
    }

    // Theme Switch Button
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = !htmlEl.classList.contains('dark');
            applyTheme(isDark);
        });
    }
    

</script>
<?php include 'include/footer.php'; ?>
