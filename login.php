<?php
/**
 * login.php - Standard MySQLi Login Functionality for Connect Amravati Portal
 * Tailored to match phpMyAdmin schema definition exactly with error reporting enabled.
 */

// Enable error reporting for debugging development issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set secure session cookie parameters before starting the session
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start standard session handling for role-based tracking
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database Configuration File Inclusion
include("include\dbConfig.php");

// Establish the MySQLi procedural database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("System Connection Failure. Please try again later.");
}
mysqli_set_charset($conn, "utf8mb4");

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title' => 'Connect Amravati - Secure Login',
        'heading' => 'Collector Office, Amravati',
        'subheading' => 'Online Communication & Task Allocation Portal',
        'label_username' => 'Employee Code / Email Address',
        'label_password' => 'Password',
        'btn_login' => 'Secure Login',
        'err_invalid' => 'Invalid Employee Code/Email or Password.',
        'err_inactive' => 'Your account has been deactivated. Contact the System Administrator.',
        'forgot_pwd' => 'Forgot Password?'
    ],
    'mr' => [
        'title' => 'कनेक्ट अमरावती - सुरक्षित लॉगिन',
        'heading' => 'जिल्हाधिकारी कार्यालय, अमरावती',
        'subheading' => 'ऑनलाइन संप्रेषण आणि टास्क वाटप पोर्टल',
        'label_username' => 'कर्मचारी कोड / ईमेल पत्ता',
        'label_password' => 'पासवर्ड',
        'btn_login' => 'सुरक्षित लॉगिन',
        'err_invalid' => 'चुकीचा कर्मचारी कोड/ईमेल किंवा पासवर्ड.',
        'err_inactive' => 'तुमचे खाते निष्क्रिय केले आहे. सिस्टम प्रशासकाशी संपर्क साधा.',
        'forgot_pwd' => 'पासवर्ड विसरलात?'
    ]
];
$t = $translations[$lang];

$error_message = '';

// Form Processing Block
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = $_POST['password'] ?? '';
    $csrf_token_input = $_POST['csrf_token'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $device_info = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);

    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token_input)) {
        $error_message = "Invalid request. Please try again.";
        log_login_attempt($conn, null, $ip_address, $device_info, 'Failed - CSRF Token Mismatch');
    } else {
        // Rate Limiting: Check for > 5 failed attempts in the last 15 minutes from this IP
        $rate_limit_sql = "SELECT COUNT(*) as attempt_count FROM login_history 
                           WHERE ip_address = ? AND status LIKE 'Failed%' 
                           AND login_time >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        $rl_stmt = mysqli_prepare($conn, $rate_limit_sql);
        mysqli_stmt_bind_param($rl_stmt, "s", $ip_address);
        mysqli_stmt_execute($rl_stmt);
        $rl_result = mysqli_stmt_get_result($rl_stmt);
        $rl_row = mysqli_fetch_assoc($rl_result);
        mysqli_stmt_close($rl_stmt);

        if ($rl_row && $rl_row['attempt_count'] >= 5) {
            $error_message = "Too many failed attempts. Please try again after 15 minutes.";
            log_login_attempt($conn, null, $ip_address, $device_info, 'Failed - Rate Limited');
        } elseif ($username_input === '' || $password_input === '') {
            $error_message = $t['err_invalid'];
        } else {
      
        // SQL Statement joining users and roles based on structure constraints
        $sql = "SELECT u.user_id, u.employee_code, u.full_name, u.email, u.password_hash, u.status, 
                       r.role_id, r.role_name, r.can_allocate_task, u.department_id, 
                       u.district_id, u.taluka_id, u.village_id
                FROM users u
                JOIN roles r ON u.role_id = r.role_id
                WHERE u.employee_code = ? OR u.email = ?
                LIMIT 1";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username_input, $username_input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Metadata processing targets for audit tracking
        $user_id_for_log = $user ? $user['user_id'] : null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $device_info = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);
        
        if ($user) {
            // Validating matching 'Active' case state from enum configuration
            if ($user['status'] !== 'Active') {
                $error_message = $t['err_inactive'];
                log_login_attempt($conn, $user_id_for_log, $ip_address, $device_info, 'Failed - Account Inactive');
            } 
            // Matching dynamic crypt verification strings
            elseif (password_verify($password_input, $user['password_hash'])) {
                
                log_login_attempt($conn, $user_id_for_log, $ip_address, $device_info, 'Success');
                
                // Prevent Session Fixation
                session_regenerate_id(true);
                
                // Populate global session maps
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['employee_code'] = $user['employee_code'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role_name'] = $user['role_name'];
                $_SESSION['can_allocate_task'] = $user['can_allocate_task']; // Used to restrict UI buttons
                $_SESSION['district_id'] = $user['district_id'];
                $_SESSION['taluka_id'] = $user['taluka_id'];
                $_SESSION['village_id'] = $user['village_id'];
                
                header("Location: dashboard.php");
                exit;
            } else {
                $error_message = $t['err_invalid'];
                log_login_attempt($conn, $user_id_for_log, $ip_address, $device_info, 'Failed - Wrong Password');
            }
        } else {
            $error_message = $t['err_invalid'];
            log_login_attempt($conn, null, $ip_address, $device_info, 'Failed - User Not Found');
        }
        }
    }
}

/**
 * Audit History writer using native mysqli tracking logic
 */
function log_login_attempt($conn, $user_id, $ip, $device, $status) {
    $log_sql = "INSERT INTO login_history (user_id, login_time, ip_address, device_info, status) 
                VALUES (?, NOW(), ?, ?, ?)";
    $log_stmt = mysqli_prepare($conn, $log_sql);
    if ($log_stmt) {
        mysqli_stmt_bind_param($log_stmt, "isss", $user_id, $ip, $device, $status);
        mysqli_stmt_execute($log_stmt);
        mysqli_stmt_close($log_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($t['title']); ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind Config for Design System -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        navy: {
                            50: '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1a365d',
                            600: '#152b4a',
                            700: '#0f1f38',
                            900: '#0a1424'
                        },
                        govgreen: {
                            50: '#edf7ed',
                            100: '#cce8cc',
                            500: '#2e7d32',
                            600: '#256428'
                        },
                        saffron: {
                            50: '#fff3e0',
                            100: '#ffe0b2',
                            500: '#f57c00',
                            600: '#e65100'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Optional Animations */
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-navy-50 font-sans relative overflow-hidden">

    <!-- Decorative Background Elements -->
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-navy-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-saffron-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    
    <!-- National Tricolor Bar at the absolute top -->
    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-[#FF9933] via-white to-[#138808]"></div>

    <div class="w-full max-w-md px-6 py-8 relative z-10">
        
        <!-- Login Card -->
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl shadow-xl overflow-hidden border border-white/50">
            
            <div class="p-8">
                <!-- Header / Logo Area -->
                <div class="text-center mb-8">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-navy-500 to-navy-700 rounded-2xl shadow-lg flex items-center justify-center mb-5 transform rotate-3 hover:rotate-0 transition-transform duration-300">
                        <i data-lucide="landmark" class="w-8 h-8 text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight"><?php echo htmlspecialchars($t['heading']); ?></h2>
                    <p class="text-sm text-slate-500 mt-2 font-medium"><?php echo htmlspecialchars($t['subheading']); ?></p>
                </div>

                <!-- Language Toggle -->
                <div class="flex justify-center mb-6">
                    <?php if ($lang === 'en'): ?>
                        <a href="login.php?lang=mr" class="inline-flex items-center text-xs font-semibold text-navy-600 hover:text-navy-800 transition-colors bg-navy-50/80 px-3 py-1.5 rounded-full border border-navy-100">
                            <i data-lucide="globe" class="w-3 h-3 mr-1.5"></i> Switch to मराठी (MR)
                        </a>
                    <?php else: ?>
                        <a href="login.php?lang=en" class="inline-flex items-center text-xs font-semibold text-navy-600 hover:text-navy-800 transition-colors bg-navy-50/80 px-3 py-1.5 rounded-full border border-navy-100">
                            <i data-lucide="globe" class="w-3 h-3 mr-1.5"></i> Switch to English (EN)
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start mb-6 shadow-sm animate-pulse">
                        <i data-lucide="alert-circle" class="w-5 h-5 mr-2 shrink-0 mt-0.5"></i>
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php?lang=<?php echo $lang; ?>" method="POST" autocomplete="off" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <div>
                        <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5"><?php echo htmlspecialchars($t['label_username']); ?></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                            </div>
                            <input type="text" id="username" name="username" required autofocus placeholder="e.g. EMP12345 or user@domain.gov.in"
                                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-transparent transition-all text-slate-900 placeholder-slate-400 shadow-sm hover:border-slate-300">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($t['label_password']); ?></label>
                            <a href="passwordReset.php" class="text-xs font-medium text-navy-600 hover:text-navy-800 transition-colors"><?php echo htmlspecialchars($t['forgot_pwd']); ?></a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                            </div>
                            <input type="password" id="password" name="password" required placeholder="••••••••"
                                class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-transparent transition-all text-slate-900 placeholder-slate-400 shadow-sm hover:border-slate-300">
                        </div>
                    </div>

                    <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-govgreen-600 hover:bg-govgreen-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-govgreen-500 transition-all duration-200 transform hover:-translate-y-0.5 mt-4">
                        <i data-lucide="log-in" class="w-4 h-4 mr-2"></i>
                        <?php echo htmlspecialchars($t['btn_login']); ?>
                    </button>
                    
                </form>

            </div>
            
            <div class="bg-slate-50/50 px-8 py-4 border-t border-slate-100 text-center">
                <p class="text-xs text-slate-500 font-medium">&copy; <?php echo date('Y'); ?> Connect Amravati. All rights reserved.</p>
            </div>

        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>