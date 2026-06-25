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

// Database Connection (uses remote+local fallback from dbConfig.php)
include_once("include/dbConfig.php");

if (!isset($conn) || $conn->connect_error) {
    die("System Connection Failure. Please try again later.");
}

// If already logged in, redirect to dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title' => 'Connect Amravati - Secure Login',
        'heading' => 'AMRAVATI CONNECT',
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
        'heading' => 'अमरावती कनेक्ट',
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
        $rl_stmt = $conn->prepare($rate_limit_sql);
        if ($rl_stmt) {
            $rl_stmt->bind_param("s", $ip_address);
            $rl_stmt->execute();
            $rl_result = $rl_stmt->get_result();
            $rl_row = $rl_result->fetch_assoc();
            $rl_stmt->close();
        } else {
            $rl_row = null;
        }

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
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username_input, $username_input);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Metadata for audit tracking
        $user_id_for_log = $user ? $user['user_id'] : null;
        $ip_address  = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
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
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role_name'];
                $_SESSION['can_allocate_task'] = $user['can_allocate_task']; // Used to restrict UI buttons
                $_SESSION['district_id'] = $user['district_id'];
                $_SESSION['taluka_id'] = $user['taluka_id'];
                $_SESSION['village_id'] = $user['village_id'];
                
                header("Location: dashboard.php?lang=" . $lang);
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
    try {
        $log_sql = "INSERT INTO login_history (user_id, login_time, ip_address, device_info, status) 
                    VALUES (?, NOW(), ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param("isss", $user_id, $ip, $device, $status);
            $log_stmt->execute();
            $log_stmt->close();
        }
    } catch (Exception $e) {
        // Silently fail if login_history table doesn't exist yet
        error_log('Login history error: ' . $e->getMessage());
    }
}

// Close the database connection explicitly at the end of PHP processing
close_db_connection();
?>
<?php
$pageTitle = htmlspecialchars($t['title']);
$bodyClass = "min-h-screen flex flex-col md:flex-row bg-white font-sans overflow-hidden";
include 'include/header.php';
?>
    <!-- National Tricolor Bar at the absolute top -->
    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#FF9933] via-white to-[#138808] z-50"></div>

    <!-- Left Sidebar (Realistic Background) -->
    <div class="hidden md:flex md:w-1/2 lg:w-3/5 bg-navy-900 relative items-center justify-center overflow-hidden">
        <!-- Background Image -->
        <div class="absolute inset-0 bg-cover bg-center opacity-70 mix-blend-overlay" style="background-image: url('assets/images/gov_bg.png');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-navy-900/90 via-navy-800/50 to-transparent"></div>
        
        <div class="relative z-10 p-12 text-white max-w-2xl text-center">
            <div class="mx-auto w-20 h-20 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center mb-6 shadow-xl">
                <i data-lucide="landmark" class="w-10 h-10 text-saffron-400"></i>
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold mb-4 tracking-tight text-white drop-shadow-md font-formal uppercase">Welcome to <br>AMRAVATI CONNECT</h1>
            <p class="text-lg text-slate-200 font-medium leading-relaxed max-w-lg mx-auto">Official Online Communication & Task Allocation Portal for District Administration, Amravati.</p>
        </div>
    </div>

    <!-- Right Side (Login Area) -->
    <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col justify-between min-h-screen px-8 py-10 lg:px-12 xl:px-16 relative bg-slate-50">
        
        <!-- Header -->
        <header class="flex justify-between items-center mb-8 mt-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-govgreen-500 to-govgreen-600 rounded-xl flex items-center justify-center shadow-official">
                    <i data-lucide="landmark" class="w-5 h-5 text-white"></i>
                </div>
                <span class="font-bold text-xl text-slate-800 tracking-tight font-formal uppercase">Collector Office</span>
            </div>
        </header>

        <main class="flex-1 flex flex-col justify-center">
            <div class="mb-8">
                <span class="inline-block py-1 px-3 rounded-full bg-navy-50 text-navy-700 text-xs font-bold uppercase tracking-wider mb-3 border border-navy-100">Welcome</span>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight mb-2 font-formal uppercase"><?php echo htmlspecialchars($t['heading']); ?></h2>
                <p class="text-sm text-slate-500 font-medium"><?php echo htmlspecialchars($t['subheading']); ?></p>
            </div>

            <!-- Language Toggle -->
            <div class="flex mb-6">
                <?php if ($lang === 'en'): ?>
                    <a href="login.php?lang=mr" class="inline-flex items-center text-xs font-semibold text-navy-600 hover:text-navy-800 transition-colors bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
                        <i data-lucide="globe" class="w-3 h-3 mr-1.5"></i> Switch to मराठी (MR)
                    </a>
                <?php else: ?>
                    <a href="login.php?lang=en" class="inline-flex items-center text-xs font-semibold text-navy-600 hover:text-navy-800 transition-colors bg-white px-3 py-1.5 rounded-full border border-slate-200 shadow-sm">
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
                            class="input-modern w-full pl-11 pr-4">
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
                            pattern="(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&#^()_+=\-]{6,}" minlength="6"
                            title="Password must be at least 6 characters, contain 1 uppercase letter, 1 number, and 1 special character."
                            class="input-modern w-full pl-11 pr-12">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer" onclick="togglePassword()">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5 text-slate-400 hover:text-navy-600 transition-colors"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-official text-sm font-bold text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 transition-all duration-200 transform hover:-translate-y-0.5 mt-4 uppercase tracking-wider">
                    <i data-lucide="log-in" class="w-4 h-4 mr-2"></i>
                    <?php echo htmlspecialchars($t['btn_login']); ?>
                </button>
            </form>
        </main>

        <!-- Footer -->
        <footer class="mt-8 pt-6 border-t border-slate-200 text-center">
            <p class="text-xs text-slate-500 font-medium flex items-center justify-center">
                &copy; <?php echo date('Y'); ?> AMRAVATI CONNECT. All rights reserved.
            </p>
        </footer>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
<?php include 'include/footer.php'; ?>