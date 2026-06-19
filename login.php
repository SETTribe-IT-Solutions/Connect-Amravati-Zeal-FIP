<?php
/**
 * login.php - Standard MySQLi Login Functionality for Connect Amravati Portal
 * Tailored to match phpMyAdmin schema definition exactly with error reporting enabled.
 */

// Enable error reporting for debugging development issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start standard session handling for role-based tracking
session_start();

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
    
    if ($username_input === '' || $password_input === '') {
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
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-container { background: #ffffff; width: 100%; max-width: 420px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 8px; border-top: 5px solid #0056b3; }
        .header-area { text-align: center; margin-bottom: 25px; }
        .header-area h2 { margin: 5px 0; color: #333; font-size: 22px; }
        .header-area p { margin: 0; color: #666; font-size: 14px; }
        .lang-switch { display: flex; justify-content: flex-end; margin-bottom: 15px; font-size: 13px; }
        .lang-switch a { color: #0056b3; text-decoration: none; font-weight: bold; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #495057; font-size: 14px; }
        .form-control { width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ced4da; border-radius: 4px; font-size: 15px; }
        .form-control:focus { border-color: #80bdff; outline: 0; box-shadow: 0 0 0 0.2rem rgba(0,86,179,0.25); }
        .btn-submit { background-color: #0056b3; color: white; width: 100%; border: none; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background-color: #004085; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 4px; margin-bottom: 20px; font-size: 14px; }
        .footer-links { margin-top: 20px; text-align: center; font-size: 13px; }
        .footer-links a { color: #6c757d; text-decoration: none; }
        .footer-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="lang-switch">
        <?php if ($lang === 'en'): ?>
            <a href="login.php?lang=mr">मराठी (MR)</a>
        <?php else: ?>
            <a href="login.php?lang=en">English (EN)</a>
        <?php endif; ?>
    </div>

    <div class="header-area">
        <h2><?php echo htmlspecialchars($t['heading']); ?></h2>
        <p><?php echo htmlspecialchars($t['subheading']); ?></p>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert-danger">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form action="login.php?lang=<?php echo $lang; ?>" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="username"><?php echo htmlspecialchars($t['label_username']); ?></label>
            <input type="text" id="username" name="username" class="form-control" required autofocus placeholder="e.g. EMP12345 or user@domain.gov.in">
        </div>

        <div class="form-group">
            <label for="password"><?php echo htmlspecialchars($t['label_password']); ?></label>
            <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-submit"><?php echo htmlspecialchars($t['btn_login']); ?></button>
    </form>

    <div class="footer-links">
        <a href="passwordReset.php"><?php echo htmlspecialchars($t['forgot_pwd']); ?></a>
    </div>
</div>

</body>
</html>