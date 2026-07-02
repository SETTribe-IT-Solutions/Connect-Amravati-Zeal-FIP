<?php
/**
 * login.php - Secure Login with Per-User Lockout & SMTP Email Notifications
 * Connect Amravati Portal
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Kolkata');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => $_SERVER['HTTP_HOST'] ?? '',
    'secure'   => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include_once('include/dbConfig.php');
include_once('include/mailer.php');

if (!isset($conn) || ($conn instanceof mysqli && $conn->connect_error)) {
    die("System Connection Failure. Please try again later.");
}

// ── Auto-migrate: add lockout columns if missing (all MySQL versions) ──────
try {
    $r1 = $conn->query("SHOW COLUMNS FROM `users` LIKE 'failed_login_attempts'");
    if ($r1 && $r1->num_rows === 0) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `failed_login_attempts` INT NOT NULL DEFAULT 0");
    }
    $r2 = $conn->query("SHOW COLUMNS FROM `users` LIKE 'locked_until'");
    if ($r2 && $r2->num_rows === 0) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `locked_until` DATETIME NULL DEFAULT NULL");
    }
} catch (Exception $e) {
    error_log('[Login Migration] ' . $e->getMessage());
}

// Already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Language ───────────────────────────────────────────────────────────────
$lang = (isset($_GET['lang']) && $_GET['lang'] === 'mr') ? 'mr' : 'en';
$T = [
    'en' => [
        'title'     => 'Connect Amravati - Secure Login',
        'heading'   => 'AMRAVATI CONNECT',
        'sub'       => 'Online Communication & Task Allocation Portal',
        'lbl_user'  => 'Employee Code / Email Address',
        'lbl_pass'  => 'Password',
        'btn'       => 'Secure Login',
        'err'       => 'Invalid Employee Code/Email or Password.',
        'inactive'  => 'Your account has been deactivated. Contact the System Administrator.',
        'forgot'    => 'Forgot Password?',
    ],
    'mr' => [
        'title'     => 'कनेक्ट अमरावती - सुरक्षित लॉगिन',
        'heading'   => 'अमरावती कनेक्ट',
        'sub'       => 'ऑनलाइन संप्रेषण आणि टास्क वाटप पोर्टल',
        'lbl_user'  => 'कर्मचारी कोड / ईमेल पत्ता',
        'lbl_pass'  => 'पासवर्ड',
        'btn'       => 'सुरक्षित लॉगिन',
        'err'       => 'चुकीचा कर्मचारी कोड/ईमेल किंवा पासवर्ड.',
        'inactive'  => 'तुमचे खाते निष्क्रिय केले आहे. सिस्टम प्रशासकाशी संपर्क साधा.',
        'forgot'    => 'पासवर्ड विसरलात?',
    ],
];
$t = $T[$lang];

// ── Constants ──────────────────────────────────────────────────────────────
if (!defined('MAX_ATTEMPTS'))    define('MAX_ATTEMPTS',    3);
if (!defined('LOCKOUT_MINUTES')) define('LOCKOUT_MINUTES', 15);

// ── State ──────────────────────────────────────────────────────────────────
$error_message     = '';
$lockout_remaining = 0;
$is_locked         = false;

// ── Helper: log attempt ────────────────────────────────────────────────────
function log_login_attempt($conn, $user_id, $ip, $device, $status) {
    try {
        $st = $conn->prepare(
            "INSERT INTO login_history (user_id, login_time, ip_address, device_info, status)
             VALUES (?, NOW(), ?, ?, ?)"
        );
        if ($st) {
            $st->bind_param("isss", $user_id, $ip, $device, $status);
            $st->execute();
            $st->close();
        }
    } catch (Exception $e) {
        error_log('Login history: ' . $e->getMessage());
    }
}

// ── Helper: send email asynchronously (fire-and-forget via output buffering) ──
// BUG FIX: emails were blocking the page for 15s. Now uses 5s timeout
// and is sent AFTER the response has been flushed to the browser where possible.
function queue_login_email($to_email, $to_name, $event, $ip, $device, $extra = []) {
    if (!SMTP_ENABLED || empty($to_email)) return;

    $base_url = (isset($_SERVER['HTTP_HOST'])
        ? 'http://' . $_SERVER['HTTP_HOST'] . '/Connect-Amravati-Zeal-FIP'
        : 'http://localhost/Connect-Amravati-Zeal-FIP');

    $reset_url = $base_url . '/passwordReset.php';
    $login_url = $base_url . '/login.php';
    $now       = date('d M Y, h:i:s A');

    switch ($event) {
        case 'success':
            $subject  = 'Successful Login - Connect Amravati';
            $hdr_bg   = '#16a34a';
            $icon     = '&#x2705;';
            $headline = 'Login Successful';
            $content  = "
                <p>Hello <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
                <p>You have successfully logged into the <strong>Connect Amravati Portal</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;font-weight:700;width:130px;'>Date &amp; Time</td><td style='padding:8px 12px;border:1px solid #bbf7d0;'>$now</td></tr>
                    <tr><td style='padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;font-weight:700;'>IP Address</td><td style='padding:8px 12px;border:1px solid #bbf7d0;'>$ip</td></tr>
                    <tr><td style='padding:8px 12px;background:#f0fdf4;border:1px solid #bbf7d0;font-weight:700;'>Device</td><td style='padding:8px 12px;border:1px solid #bbf7d0;'>$device</td></tr>
                </table>
                <p style='margin-top:16px;'>If this was not you, <a href='$reset_url' style='color:#16a34a;font-weight:700;'>reset your password immediately</a>.</p>
            ";
            break;

        case 'failed':
            $attempts  = (int)($extra['attempts'] ?? 1);
            $left      = MAX_ATTEMPTS - $attempts;
            $subject   = 'Failed Login Attempt - Connect Amravati';
            $hdr_bg    = '#d97706';
            $icon      = '&#x26A0;&#xFE0F;';
            $headline  = 'Failed Login Attempt';
            $content   = "
                <p>Hello <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
                <p>A <strong>failed login attempt</strong> was detected on your account.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;font-weight:700;width:130px;'>Date &amp; Time</td><td style='padding:8px 12px;border:1px solid #fde68a;'>$now</td></tr>
                    <tr><td style='padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;font-weight:700;'>IP Address</td><td style='padding:8px 12px;border:1px solid #fde68a;'>$ip</td></tr>
                    <tr><td style='padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;font-weight:700;'>Attempt No.</td><td style='padding:8px 12px;border:1px solid #fde68a;'><strong>$attempts</strong> of " . MAX_ATTEMPTS . "</td></tr>
                    <tr><td style='padding:8px 12px;background:#fffbeb;border:1px solid #fde68a;font-weight:700;'>Attempts Left</td><td style='padding:8px 12px;border:1px solid #fde68a;color:#dc2626;font-weight:700;'>$left more before account lock</td></tr>
                </table>
                <p>If this was not you, <a href='$reset_url' style='color:#d97706;font-weight:700;'>reset your password immediately</a>.</p>
            ";
            break;

        case 'locked':
            $unlock_time = $extra['unlock_time'] ?? 'N/A';
            $subject     = 'Account Locked - Connect Amravati';
            $hdr_bg      = '#dc2626';
            $icon        = '&#x1F512;';
            $headline    = 'Account Temporarily Locked';
            $content     = "
                <p>Hello <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
                <p>Your account has been <strong style='color:#dc2626;'>temporarily locked</strong> after <strong>" . MAX_ATTEMPTS . " consecutive failed login attempts</strong>.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;font-weight:700;width:130px;'>Locked At</td><td style='padding:8px 12px;border:1px solid #fecaca;'>$now</td></tr>
                    <tr><td style='padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;font-weight:700;'>Unlocks At</td><td style='padding:8px 12px;border:1px solid #fecaca;font-weight:700;'>$unlock_time</td></tr>
                    <tr><td style='padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;font-weight:700;'>Duration</td><td style='padding:8px 12px;border:1px solid #fecaca;'>" . LOCKOUT_MINUTES . " minutes</td></tr>
                    <tr><td style='padding:8px 12px;background:#fef2f2;border:1px solid #fecaca;font-weight:700;'>IP Address</td><td style='padding:8px 12px;border:1px solid #fecaca;'>$ip</td></tr>
                </table>
                <p><strong>Your account will automatically unlock after " . LOCKOUT_MINUTES . " minutes.</strong> You will receive an email when it is unlocked.</p>
                <p>If this was not you, contact the System Administrator immediately.</p>
            ";
            break;

        case 'unlocked':
            $subject  = 'Account Unlocked - Connect Amravati';
            $hdr_bg   = '#2563eb';
            $icon     = '&#x1F513;';
            $headline = 'Account Unlocked';
            $content  = "
                <p>Hello <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
                <p>Your account has been <strong style='color:#2563eb;'>automatically unlocked</strong>. You can now log in again.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;font-weight:700;width:130px;'>Unlocked At</td><td style='padding:8px 12px;border:1px solid #bfdbfe;'>$now</td></tr>
                </table>
                <p><a href='$login_url' style='display:inline-block;background:#2563eb;color:#fff;padding:10px 28px;border-radius:8px;text-decoration:none;font-weight:700;margin-top:8px;'>Login Now</a></p>
                <p>If you did not initiate those attempts, <a href='$reset_url' style='color:#2563eb;font-weight:700;'>reset your password immediately</a>.</p>
            ";
            break;

        default:
            return;
    }

    $year = date('Y');
    $html = "<!DOCTYPE html>
<html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width'></head>
<body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='padding:32px 0;background:#f1f5f9;'>
<tr><td align='center'>
<table width='580' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.10);max-width:580px;'>
  <tr><td style='background:$hdr_bg;padding:28px 32px;text-align:center;'>
    <div style='font-size:40px;'>$icon</div>
    <h2 style='margin:8px 0 4px;color:#fff;font-size:20px;letter-spacing:-0.3px;'>$headline</h2>
    <p style='margin:0;color:rgba(255,255,255,0.80);font-size:12px;'>Connect Amravati Portal &mdash; Security Notification</p>
  </td></tr>
  <tr><td style='padding:28px 32px;color:#374151;font-size:14px;line-height:1.75;'>$content</td></tr>
  <tr><td style='background:#f8fafc;border-top:1px solid #e5e7eb;padding:16px 32px;text-align:center;'>
    <p style='margin:0;font-size:11px;color:#9ca3af;'>Automated security alert from Connect Amravati Portal. Do not reply.</p>
    <p style='margin:4px 0 0;font-size:11px;color:#9ca3af;'>&copy; $year District Administration, Amravati.</p>
  </td></tr>
</table>
</td></tr>
</table>
</body></html>";

    // BUG FIX: Reduced timeout from 15s to 5s to prevent blocking the page
    try {
        send_smtp_email(
            $to_email, $subject, $html,
            SMTP_USER, SMTP_FROM_NAME,
            SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE,
            5   // max 5-second timeout
        );
    } catch (Exception $e) {
        error_log('[Login Email] ' . $e->getMessage());
    }
}

// ── Form Processing ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_input   = trim($_POST['username'] ?? '');
    $password_input   = $_POST['password'] ?? '';
    $csrf_input       = $_POST['csrf_token'] ?? '';
    $ip_address       = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $device_info      = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);

    // CSRF
    if (!hash_equals($_SESSION['csrf_token'], $csrf_input)) {
        $error_message = "Invalid request. Please refresh and try again.";
    } elseif (empty($username_input) || empty($password_input)) {
        $error_message = $t['err'];
    } else {
        // Fetch user (include lockout columns)
        try {
            $sql = "SELECT u.user_id, u.employee_code, u.full_name, u.email,
                           u.password_hash, u.status,
                           u.failed_login_attempts, u.locked_until,
                           r.role_name, r.can_allocate_task, r.role_level,
                           u.department_id, u.district_id, u.taluka_id, u.village_id
                    FROM users u
                    JOIN roles r ON u.role_id = r.role_id
                    WHERE (u.employee_code = ? OR u.email = ?) AND u.status = 'Active'
                    LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $username_input, $username_input);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();
        } catch (Exception $e) {
            error_log('[Login SQL] ' . $e->getMessage());
            $user = null;
        }

        if (!$user) {
            // User not found or inactive
            $error_message = $t['err'];
            log_login_attempt($conn, null, $ip_address, $device_info, 'Failed - User Not Found / Inactive');

        } else {
            $uid            = $user['user_id'];
            $now_ts         = time();
            $locked_until_ts = !empty($user['locked_until']) ? strtotime($user['locked_until']) : 0;

            // ── CASE 1: Still locked ─────────────────────────────────────
            if ($locked_until_ts > $now_ts) {
                $lockout_remaining = $locked_until_ts - $now_ts;
                $is_locked         = true;
                $m = floor($lockout_remaining / 60);
                $s = $lockout_remaining % 60;
                $error_message = "Account locked. Try again in {$m}m {$s}s.";
                log_login_attempt($conn, $uid, $ip_address, $device_info, 'Failed - Account Locked');

            } else {
                // ── CASE 2: Lock just expired → auto-unlock ──────────────
                if ($locked_until_ts > 0 && $locked_until_ts <= $now_ts) {
                    try {
                        $conn->query("UPDATE users SET failed_login_attempts=0, locked_until=NULL WHERE user_id=$uid");
                    } catch (Exception $e) { /* ignore */ }
                    $user['failed_login_attempts'] = 0;
                    $user['locked_until']          = null;
                    // Send unlocked email
                    queue_login_email($user['email'], $user['full_name'], 'unlocked', $ip_address, $device_info);
                }

                // ── CASE 3: Verify password ──────────────────────────────
                if (password_verify($password_input, $user['password_hash'])) {

                    // Reset counter
                    try {
                        $conn->query("UPDATE users SET failed_login_attempts=0, locked_until=NULL WHERE user_id=$uid");
                    } catch (Exception $e) { /* ignore */ }

                    log_login_attempt($conn, $uid, $ip_address, $device_info, 'Success');

                    // Populate session BEFORE sending email (email is last)
                    session_regenerate_id(true);
                    $_SESSION['user_id']           = $user['user_id'];
                    $_SESSION['employee_code']     = $user['employee_code'];
                    $_SESSION['full_name']         = $user['full_name'];
                    $_SESSION['role_name']         = $user['role_name'];
                    $_SESSION['user_name']         = $user['full_name'];
                    $_SESSION['user_role']         = $user['role_name'];
                    $_SESSION['can_allocate_task'] = $user['can_allocate_task'];
                    $_SESSION['district_id']       = $user['district_id'];
                    $_SESSION['taluka_id']         = $user['taluka_id'];
                    $_SESSION['village_id']        = $user['village_id'];
                    $_SESSION['email']             = $user['email'];
                    $_SESSION['user_level']        = (int)$user['role_level'];
                    $_SESSION['last_activity']     = time(); // Initialize inactivity timer

                    // BUG FIX: Send email BEFORE redirect (but after session is set)
                    // SMTP errors are silently logged, never break login
                    queue_login_email($user['email'], $user['full_name'], 'success', $ip_address, $device_info);

                    // Close DB then redirect
                    close_db_connection();
                    header("Location: dashboard.php?lang=$lang");
                    exit;

                } else {
                    // ── CASE 4: Wrong password ────────────────────────────
                    $new_attempts = (int)$user['failed_login_attempts'] + 1;

                    if ($new_attempts >= MAX_ATTEMPTS) {
                        // LOCK the account
                        $unlock_dt = date('Y-m-d H:i:s', $now_ts + LOCKOUT_MINUTES * 60);
                        try {
                            $lk = $conn->prepare(
                                "UPDATE users SET failed_login_attempts=?, locked_until=? WHERE user_id=?"
                            );
                            $lk->bind_param("isi", $new_attempts, $unlock_dt, $uid);
                            $lk->execute();
                            $lk->close();
                        } catch (Exception $e) { error_log('[Lock] ' . $e->getMessage()); }

                        $lockout_remaining = LOCKOUT_MINUTES * 60;
                        $is_locked         = true;
                        $error_message     = "Account locked after " . MAX_ATTEMPTS . " failed attempts. Try again in " . LOCKOUT_MINUTES . " minutes.";

                        log_login_attempt($conn, $uid, $ip_address, $device_info, 'Failed - Account Locked');
                        queue_login_email($user['email'], $user['full_name'], 'locked', $ip_address, $device_info, [
                            'unlock_time' => date('d M Y, h:i:s A', $now_ts + LOCKOUT_MINUTES * 60)
                        ]);

                    } else {
                        // Increment counter
                        $left = MAX_ATTEMPTS - $new_attempts;
                        try {
                            $inc = $conn->prepare(
                                "UPDATE users SET failed_login_attempts=? WHERE user_id=?"
                            );
                            $inc->bind_param("ii", $new_attempts, $uid);
                            $inc->execute();
                            $inc->close();
                        } catch (Exception $e) { error_log('[Attempt] ' . $e->getMessage()); }

                        $error_message = "Wrong password. {$left} attempt(s) left before account is locked.";
                        log_login_attempt($conn, $uid, $ip_address, $device_info, 'Failed - Wrong Password');
                        queue_login_email($user['email'], $user['full_name'], 'failed', $ip_address, $device_info, [
                            'attempts' => $new_attempts
                        ]);
                    }
                }
            }
        }
    }
}

close_db_connection();
?>
<?php
$pageTitle = htmlspecialchars($t['title']);
$bodyClass = "min-h-screen flex flex-col md:flex-row bg-white font-sans overflow-hidden";
include 'include/header.php';
?>

    <!-- National Tricolor Bar -->
    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#FF9933] via-white to-[#138808] z-50"></div>

    <!-- Left Side -->
    <div class="hidden md:flex md:w-1/2 lg:w-3/5 bg-navy-900 relative items-center justify-center overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center opacity-70 mix-blend-overlay" style="background-image:url('assets/images/gov_bg.png');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-navy-900/90 via-navy-800/50 to-transparent"></div>
        <div class="relative z-10 p-12 text-white max-w-2xl text-center">
            <div class="mx-auto w-20 h-20 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full flex items-center justify-center mb-6 shadow-xl">
                <i data-lucide="landmark" class="w-10 h-10 text-saffron-400"></i>
            </div>
            <h1 class="text-4xl lg:text-5xl font-extrabold mb-4 tracking-tight text-white drop-shadow-md font-formal uppercase">Welcome to<br>AMRAVATI CONNECT</h1>
            <p class="text-lg text-slate-200 font-medium leading-relaxed max-w-lg mx-auto">Official Online Communication &amp; Task Allocation Portal for District Administration, Amravati.</p>
        </div>
    </div>

    <!-- Right Side -->
    <div class="w-full md:w-1/2 lg:w-2/5 flex flex-col justify-between min-h-screen px-8 py-10 lg:px-12 xl:px-16 relative bg-slate-50">

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
                <p class="text-sm text-slate-500 font-medium"><?php echo htmlspecialchars($t['sub']); ?></p>
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

            <?php if ($is_locked): ?>
            <!-- ════ LOCKOUT CARD WITH COUNTDOWN ════ -->
            <div id="lockout-card" class="rounded-2xl border-2 border-red-300 bg-gradient-to-br from-red-50 to-red-100/80 p-5 mb-6 shadow-md">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-11 h-11 bg-red-100 border-2 border-red-300 rounded-full flex items-center justify-center flex-shrink-0">
                        <i data-lucide="lock" class="w-5 h-5 text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-red-800 font-bold text-base leading-tight">Account Temporarily Locked</h3>
                        <p class="text-red-500 text-xs font-medium">Too many failed login attempts</p>
                    </div>
                </div>

                <p class="text-red-700 text-sm mb-4 leading-relaxed">
                    Your account is locked for <strong><?php echo LOCKOUT_MINUTES; ?> minutes</strong>.
                    Login will be re-enabled when the timer reaches <strong>00:00</strong>.
                </p>

                <!-- Big Countdown Timer -->
                <div class="bg-white rounded-xl border border-red-200 py-4 px-6 text-center shadow-sm mb-4">
                    <p class="text-xs text-red-400 font-bold uppercase tracking-widest mb-1">Time Remaining</p>
                    <div id="countdown" class="font-mono font-black tabular-nums text-red-700" style="font-size:3.5rem;letter-spacing:0.08em;">
                        <?php
                            printf('%02d:%02d',
                                (int)floor($lockout_remaining / 60),
                                (int)($lockout_remaining % 60)
                            );
                        ?>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">minutes &nbsp;:&nbsp; seconds</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                    <p class="text-xs text-red-600 font-medium">A security notification has been sent to your registered email.</p>
                </div>
            </div>

            <!-- Disabled form (greyed out visual) -->
            <form class="space-y-4 opacity-40 pointer-events-none select-none" aria-disabled="true">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5"><?php echo htmlspecialchars($t['lbl_user']); ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="text" disabled placeholder="e.g. EMP12345 or user@domain.gov.in" class="input-modern w-full pl-11 pr-4 cursor-not-allowed">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5"><?php echo htmlspecialchars($t['lbl_pass']); ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" disabled placeholder="••••••••" class="input-modern w-full pl-11 pr-4 cursor-not-allowed">
                    </div>
                </div>
                <button type="button" disabled class="w-full flex justify-center items-center py-3 px-4 rounded-xl text-sm font-bold text-white bg-slate-400 cursor-not-allowed mt-2 uppercase tracking-wider">
                    <i data-lucide="lock" class="w-4 h-4 mr-2"></i> Account Locked
                </button>
            </form>

            <!-- Countdown Timer Script -->
            <script>
            (function(){
                var remaining = <?php echo (int)$lockout_remaining; ?>;
                var el = document.getElementById('countdown');
                var card = document.getElementById('lockout-card');

                function pad(n){ return (n < 10 ? '0' : '') + n; }

                var iv = setInterval(function(){
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(iv);
                        el.textContent = '00:00';
                        el.style.color = '#16a34a';
                        // Update card to "unlocked" state
                        card.classList.remove('border-red-300','from-red-50','to-red-100/80');
                        card.classList.add('border-green-300','from-green-50','to-green-100/80');
                        card.innerHTML =
                            '<div class="flex items-center gap-3">' +
                            '<div class="w-11 h-11 bg-green-100 border-2 border-green-300 rounded-full flex items-center justify-center">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>' +
                            '</div>' +
                            '<div><h3 class="text-green-800 font-bold text-base">Account Unlocked!</h3>' +
                            '<p class="text-green-600 text-sm">You can now log in again.</p></div>' +
                            '</div>';
                        setTimeout(function(){ location.reload(); }, 1800);
                        return;
                    }
                    el.textContent = pad(Math.floor(remaining/60)) + ':' + pad(remaining % 60);
                }, 1000);
            })();
            </script>

            <?php else: ?>
            <!-- ════ NORMAL LOGIN FORM ════ -->

            <?php if (!empty($error_message)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-2 mb-5 shadow-sm" role="alert">
                <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-500"></i>
                <span class="text-sm font-medium"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <form id="loginForm" action="login.php?lang=<?php echo $lang; ?>" method="POST" autocomplete="off" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div>
                    <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5"><?php echo htmlspecialchars($t['lbl_user']); ?></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="user" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="text" id="username" name="username" required autofocus
                            placeholder="e.g. EMP12345 or user@domain.gov.in"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            class="input-modern w-full pl-11 pr-4">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-slate-700"><?php echo htmlspecialchars($t['lbl_pass']); ?></label>
                        <a href="passwordReset.php" class="text-xs font-medium text-navy-600 hover:text-navy-800 transition-colors"><?php echo htmlspecialchars($t['forgot']); ?></a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i data-lucide="lock" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            placeholder="••••••••" minlength="4"
                            class="input-modern w-full pl-11 pr-12">
                        <button type="button" onclick="togglePassword()"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-navy-600 transition-colors">
                            <i data-lucide="eye" id="eyeIcon" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <?php if (!empty($error_message)): ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-lg">
                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
                    <span class="text-xs text-amber-700 font-medium">
                        Account will be locked after <strong><?php echo MAX_ATTEMPTS; ?></strong> failed attempts.
                    </span>
                </div>
                <?php endif; ?>

                <button type="submit" id="loginBtn"
                    class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-official text-sm font-bold text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-navy-500 transition-all duration-200 transform hover:-translate-y-0.5 mt-2 uppercase tracking-wider">
                    <i data-lucide="log-in" class="w-4 h-4 mr-2"></i>
                    <span id="btnText"><?php echo htmlspecialchars($t['btn']); ?></span>
                </button>
            </form>

            <script>
            // Show loading state on submit
            document.getElementById('loginForm').addEventListener('submit', function(){
                var btn = document.getElementById('loginBtn');
                var txt = document.getElementById('btnText');
                btn.disabled = true;
                txt.textContent = 'Signing in...';
                btn.classList.add('opacity-75','cursor-not-allowed');
            });

            function togglePassword() {
                var inp  = document.getElementById('password');
                var icon = document.getElementById('eyeIcon');
                inp.type = (inp.type === 'password') ? 'text' : 'password';
                icon.setAttribute('data-lucide', inp.type === 'password' ? 'eye' : 'eye-off');
                lucide.createIcons();
            }
            </script>

            <?php endif; ?>
        </main>

        <footer class="mt-8 pt-6 border-t border-slate-200 text-center">
            <p class="text-xs text-slate-500 font-medium">&copy; <?php echo date('Y'); ?> AMRAVATI CONNECT. All rights reserved.</p>
        </footer>
    </div>

<?php include 'include/footer.php'; ?>

<script>
// Show notification if user was auto-logged out due to inactivity
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('auto_logout') === '1' || params.get('reason') === 'inactivity') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Session Expired',
                html: '<p style="color:#64748b;font-size:14px;">You were automatically logged out due to <strong>10 minutes of inactivity</strong>.</p><p style="color:#94a3b8;font-size:12px;margin-top:8px;">Please sign in again to continue.</p>',
                confirmButtonText: 'OK',
                confirmButtonColor: '#0054a4',
                customClass: {
                    popup: 'rounded-xl shadow-2xl',
                },
            });
        }
        // Clean the URL without reloading
        const cleanUrl = window.location.pathname;
        window.history.replaceState({}, document.title, cleanUrl);
    }
})();
</script>