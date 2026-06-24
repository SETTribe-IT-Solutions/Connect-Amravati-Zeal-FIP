<?php
/**
 * User Profile Update Page
 * Amravati Connect - Government Workflow Platform
 */

session_start();
require_once 'include/dbConfig.php';

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

// Bilingual translations dictionary
$translations = [
    'en' => [
        'title' => 'Profile Update — Amravati Connect',
        'heading' => 'User Profile Update',
        'subheading' => 'View and update your official account contact information.',
        'label_fullname' => 'Full Name',
        'label_email' => 'Email Address',
        'label_mobile' => 'Mobile Number',
        'label_employee_code' => 'Employee Code',
        'label_designation' => 'Designation',
        'btn_save' => 'Save Changes',
        'msg_success' => 'Profile updated successfully.',
        'msg_error' => 'Failed to update profile. Please try again.',
        'back_to_dashboard' => 'Back to Dashboard',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
        'menu_notifications' => 'Notification Center',
        'menu_analytics' => 'Analytics & Data',
        'menu_reports' => 'Reports & Analytics',
        'menu_users' => 'User Management',
        'menu_logout' => 'Logout',
        'menu_main_modules' => 'Main Modules'
    ],
    'mr' => [
        'title' => 'प्रोफाइल अपडेट — अमरावती कनेक्ट',
        'heading' => 'वापरकर्ता प्रोफाइल अपडेट',
        'subheading' => 'तुमच्या अधिकृत खात्याची संपर्क माहिती पहा आणि अद्ययावत करा.',
        'label_fullname' => 'पूर्ण नाव',
        'label_email' => 'ईमेल पत्ता',
        'label_mobile' => 'मोबाईल नंबर',
        'label_employee_code' => 'कर्मचारी कोड',
        'label_designation' => 'पदनाम',
        'btn_save' => 'बदल जतन करा',
        'msg_success' => 'प्रोफाइल यशस्वीरित्या अद्यतनित केले गेले.',
        'msg_error' => 'प्रोफाइल अद्यतनित करण्यात अपयश. कृपया पुन्हा प्रयत्न करा.',
        'back_to_dashboard' => 'डॅशबोर्डवर परत जा',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_analytics' => 'विश्लेषण आणि डेटा',
        'menu_reports' => 'अहवाल आणि विश्लेषण',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'menu_logout' => 'लॉगआउट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स'
    ]
];
$t = $translations[$lang];

$user_id = $_SESSION['user_id'] ?? 1;
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    
    if (empty($full_name)) {
        $error_msg = $t['msg_error'];
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, mobile = ?, updated_at = NOW() WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param('sssi', $full_name, $email, $mobile, $user_id);
            if ($stmt->execute()) {
                $_SESSION['full_name'] = $full_name;
                $success_msg = $t['msg_success'];
            } else {
                $error_msg = $t['msg_error'];
            }
            $stmt->close();
        }
    }
}

// Fetch user data
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
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('acTheme') || localStorage.getItem('theme');
            const sessionTheme = '<?= $_SESSION['pref_theme'] ?? '' ?>';
            const isDark = stored === 'dark' || (sessionTheme === 'dark') || (!stored && !sessionTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
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
                        navy: { 600: '#152b4a', 700: '#0f1f38', 900: '#0a1424' }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); }
        .dark .glass-panel { background: rgba(15,23,42,0.7); border: 1px solid rgba(255,255,255,0.05); }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200">

<!-- SIDEBAR -->
<aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col z-20">
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
    </div>
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
            <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_dashboard']) ?>
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_task_alloc']) ?>
            </a>
            <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_notifications']) ?>
            </a>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
            <a href="overdue_report.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_reports']) ?>
            </a>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="user_creation.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_users']) ?>
            </a>
        </nav>
    </div>
</aside>

<!-- MAIN WRAPPER -->
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10">
        <div class="flex items-center flex-1">
            <nav class="flex items-center text-sm">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white">Profile</span>
            </nav>
        </div>
        <div class="flex items-center space-x-4">
            <a href="profile_update.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="flex items-center text-sm border px-3 py-1.5 rounded-md text-slate-700 dark:text-slate-300">
                <i data-lucide="languages" class="w-4 h-4 mr-2"></i>
                <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
            </a>
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 rounded-full hover:bg-slate-100">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            
            <!-- Profile dropdown container -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?></span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= $lang==='en'?'User Profile Update':'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= $lang==='en'?'Settings':'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordReset.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100">
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
        <div class="max-w-2xl mx-auto bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50">
                <h1 class="text-xl font-bold"><?= htmlspecialchars($t['heading']) ?></h1>
                <p class="text-xs text-slate-500"><?= htmlspecialchars($t['subheading']) ?></p>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <?php if ($success_msg): ?>
                    <div class="p-4 bg-green-50 text-green-700 rounded-xl border border-green-200 text-sm"><?= $success_msg ?></div>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <div class="p-4 bg-red-50 text-red-700 rounded-xl border border-red-200 text-sm"><?= $error_msg ?></div>
                <?php endif; ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1"><?= htmlspecialchars($t['label_fullname']) ?></label>
                    <input type="text" name="full_name" required value="<?= htmlspecialchars($user_data['full_name'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1"><?= htmlspecialchars($t['label_email']) ?></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1"><?= htmlspecialchars($t['label_mobile']) ?></label>
                    <input type="text" name="mobile" value="<?= htmlspecialchars($user_data['mobile'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-900 dark:border-slate-700">
                </div>
                
                <div class="grid grid-cols-2 gap-4 pt-4 border-t dark:border-slate-700">
                    <div>
                        <span class="block text-[10px] text-slate-400 font-semibold uppercase"><?= htmlspecialchars($t['label_employee_code']) ?></span>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($user_data['employee_code'] ?? '—') ?></span>
                    </div>
                    <div>
                        <span class="block text-[10px] text-slate-400 font-semibold uppercase"><?= htmlspecialchars($t['label_designation']) ?></span>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?= htmlspecialchars($user_data['designation'] ?? '—') ?></span>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between">
                    <button type="submit" class="bg-navy-600 hover:bg-navy-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm"><?= htmlspecialchars($t['btn_save']) ?></button>
                    <a href="dashboard.php?lang=<?= $lang ?>" class="text-sm text-navy-600 dark:text-blue-400 font-medium hover:underline"><?= htmlspecialchars($t['back_to_dashboard']) ?></a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    lucide.createIcons();
    
    // Theme
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
    
    // Dropdown
    const dropdownBtn = document.getElementById('profileDropdownBtn');
    const dropdownMenu = document.getElementById('profileDropdownMenu');
    if (dropdownBtn && dropdownMenu) {
        dropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => dropdownMenu.classList.add('hidden'));
    }
</script>
</body>
</html>
