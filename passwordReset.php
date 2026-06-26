<?php
/**
 * Secure Password Reset Feature Example
 * Connect-Amravati-Zeal-FIP
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title_forgot' => 'Forgot Password - Connect Amravati',
        'title_reset' => 'Reset Password - Connect Amravati',
        'title_success' => 'Reset Successful - Connect Amravati',
        'heading_forgot' => 'Forgot Password',
        'desc_forgot' => 'Enter your registered email address to receive reset instructions.',
        'heading_reset' => 'Reset Password',
        'desc_reset' => 'Create a strong password to secure your account.',
        'heading_success' => 'Reset Successful',
        'desc_success' => 'Your password has been updated. You can now log in with your new password.',
        'label_email' => 'Registered Email',
        'label_new_pwd' => 'New Password',
        'label_confirm_pwd' => 'Confirm Password',
        'pwd_hint' => 'Minimum 8 characters, include uppercase, lowercase, number, and special character.',
        'btn_send_link' => 'Send Reset Link',
        'btn_reset_pwd' => 'Reset Password',
        'btn_go_to_login' => 'Go to Login',
        'err_pwd_len' => 'Password must be at least 8 characters.',
        'err_pwd_match' => 'Passwords do not match.',
        'err_invalid_email' => 'Please enter a valid email address.',
        'err_no_account' => 'No active account found with that email address. Please check and try again.',
        'err_invalid_link' => 'Invalid or expired password reset link. Please request a new one.',
        'msg_success_reset' => 'Password reset successfully! You can now log in with your new password.',
        'msg_link_sent' => 'A password reset link has been sent to your email address.',
        'msg_local_link' => 'SMTP not configured. Link generated for local testing.'
    ],
    'mr' => [
        'title_forgot' => 'पासवर्ड विसरलात - कनेक्ट अमरावती',
        'title_reset' => 'पासवर्ड रीसेट करा - कनेक्ट अमरावती',
        'title_success' => 'रीसेट यशस्वी - कनेक्ट अमरावती',
        'heading_forgot' => 'पासवर्ड विसरलात',
        'desc_forgot' => 'रीसेट सूचना प्राप्त करण्यासाठी तुमचा नोंदणीकृत ईमेल पत्ता प्रविष्ट करा.',
        'heading_reset' => 'पासवर्ड रीसेट करा',
        'desc_reset' => 'तुमचे खाते सुरक्षित करण्यासाठी एक मजबूत पासवर्ड तयार करा.',
        'heading_success' => 'रीसेट यशस्वी',
        'desc_success' => 'तुमचा पासवर्ड अपडेट केला गेला आहे. आता तुम्ही तुमच्या नवीन पासवर्डने लॉग इन करू शकता.',
        'label_email' => 'नोंदणीकृत ईमेल',
        'label_new_pwd' => 'नवीन पासवर्ड',
        'label_confirm_pwd' => 'पासवर्डची पुष्टी करा',
        'pwd_hint' => 'किमान ८ अक्षरे, अप्परकेस, लोअरकेस, संख्या आणि विशेष वर्ण समाविष्ट करा.',
        'btn_send_link' => 'रीसेट लिंक पाठवा',
        'btn_reset_pwd' => 'पासवर्ड रीसेट करा',
        'btn_go_to_login' => 'लॉगिन वर जा',
        'err_pwd_len' => 'पासवर्ड किमान ८ अक्षरांचा असावा.',
        'err_pwd_match' => 'पासवर्ड जुळत नाहीत.',
        'err_invalid_email' => 'कृपया एक वैध ईमेल पत्ता प्रविष्ट करा.',
        'err_no_account' => 'त्या ईमेल पत्त्यासह कोणतेही सक्रिय खाते आढळले नाही. कृपया तपासा आणि पुन्हा प्रयत्न करा.',
        'err_invalid_link' => 'अवैध किंवा कालबाह्य झालेली पासवर्ड रीसेट लिंक. कृपया नवीन विनंती करा.',
        'msg_success_reset' => 'पासवर्ड यशस्वीरित्या रीसेट झाला! आता तुम्ही तुमच्या नवीन पासवर्डने लॉग इन करू शकता.',
        'msg_link_sent' => 'तुमच्या ईमेल पत्त्यावर पासवर्ड रीसेट लिंक पाठवली गेली आहे.',
        'msg_local_link' => 'SMTP कॉन्फिगर केलेले नाही. स्थानिक चाचणीसाठी लिंक व्युत्पन्न केली आहे.'
    ]
];
$t = $translations[$lang];

$temp_token = isset($_GET['token']) ? trim($_GET['token']) : '';
$lang_switch_url = 'passwordReset.php?lang=' . ($lang === 'en' ? 'mr' : 'en');
if (!empty($temp_token)) {
    $lang_switch_url .= '&token=' . urlencode($temp_token);
}

// ==========================================
// SMTP CONFIGURATION FOR REAL EMAIL DISPATCH
// ==========================================
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');
define('SMTP_USER', 'shree.chaugule@gmail.com');
define('SMTP_PASS', 'mvnp mtcg goft cnps');
define('SMTP_FROM_NAME', 'Connect Amravati Admin');

// Helper function to send email via SMTP socket connection
function send_smtp_email($to, $subject, $message_html, $from_email, $from_name, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure = 'ssl') {
    $newline = "\r\n";
    $timeout = 15;
    
    $host_prefix = '';
    if (strtolower($smtp_secure) === 'ssl' || $smtp_port == 465) {
        $host_prefix = 'ssl://';
    }
    
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);
    
    $socket = @stream_socket_client($host_prefix . $smtp_host . ':' . $smtp_port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
    }
    
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '220') {
        throw new Exception("SMTP greeting failed: " . $response);
    }
    
    fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?: 'localhost') . $newline);
    $response = "";
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (substr($line, 3, 1) == " ") break;
    }
    
    if (strtolower($smtp_secure) === 'tls' && empty($host_prefix)) {
        fwrite($socket, "STARTTLS" . $newline);
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '220') {
            throw new Exception("STARTTLS command failed: " . $response);
        }
        
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception("Failed to enable TLS encryption on socket.");
        }
        
        fwrite($socket, "EHLO " . ($_SERVER['SERVER_NAME'] ?: 'localhost') . $newline);
        $response = "";
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) == " ") break;
        }
    }
    
    if (!empty($smtp_user) && !empty($smtp_pass)) {
        fwrite($socket, "AUTH LOGIN" . $newline);
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '334') {
            throw new Exception("AUTH LOGIN rejected: " . $response);
        }
        
        fwrite($socket, base64_encode($smtp_user) . $newline);
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '334') {
            throw new Exception("SMTP username rejected: " . $response);
        }
        
        fwrite($socket, base64_encode($smtp_pass) . $newline);
        $response = fgets($socket, 512);
        if (substr($response, 0, 3) != '235') {
            throw new Exception("SMTP password rejected. Please verify your App Password.");
        }
    }
    
    fwrite($socket, "MAIL FROM:<" . $from_email . ">" . $newline);
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        throw new Exception("MAIL FROM rejected: " . $response);
    }
    
    fwrite($socket, "RCPT TO:<" . $to . ">" . $newline);
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        throw new Exception("RCPT TO rejected: " . $response);
    }
    
    fwrite($socket, "DATA" . $newline);
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '354') {
        throw new Exception("DATA initiation rejected: " . $response);
    }
    
    $headers = "MIME-Version: 1.0" . $newline;
    $headers .= "Content-Type: text/html; charset=UTF-8" . $newline;
    $headers .= "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <" . $from_email . ">" . $newline;
    $headers .= "To: <" . $to . ">" . $newline;
    $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=" . $newline;
    $headers .= "Date: " . date('r') . $newline;
    $headers .= "Message-ID: <" . time() . "-" . md5($to) . "@" . ($_SERVER['SERVER_NAME'] ?: 'localhost') . ">" . $newline;
    
    fwrite($socket, $headers . $newline . $message_html . $newline . "." . $newline);
    $response = fgets($socket, 512);
    if (substr($response, 0, 3) != '250') {
        throw new Exception("DATA content rejected: " . $response);
    }
    
    fwrite($socket, "QUIT" . $newline);
    fclose($socket);
    return true;
}

// Helper function for building HTML reset email
function get_reset_email_html($reset_link, $full_name = 'User') {
    $safe_name = htmlspecialchars($full_name);
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Reset Your Password</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
            .container { max-width: 500px; background: #ffffff; padding: 40px; border-radius: 12px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
            h2 { color: #333; margin-top: 0; text-align: center; }
            p { color: #666; line-height: 1.6; font-size: 15px; }
            .btn-container { text-align: center; margin: 25px 0; }
            .btn { display: inline-block; background-color: #2563eb; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; font-size: 15px; }
            .btn:hover { background-color: #1d4ed8; }
            .footer { margin-top: 30px; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; text-align: center; }
            .link-text { word-break: break-all; font-size: 13px; color: #2563eb; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <h2>Reset Your Password</h2>
            <p>Hello, <strong>' . $safe_name . '</strong>,</p>
            <p>You requested a password reset for your Connect Amravati account. Please click the button below to choose a new password. This link is valid for <strong>1 hour</strong>.</p>
            <div class="btn-container">
                <a href="' . htmlspecialchars($reset_link) . '" class="btn" target="_blank">Reset Password</a>
            </div>
            <p style="margin-top: 25px; font-size: 13px; color: #777;">If you cannot click the button, copy and paste this link in your browser:</p>
            <p><a href="' . htmlspecialchars($reset_link) . '" class="link-text">' . htmlspecialchars($reset_link) . '</a></p>
            <div class="footer">
                If you did not make this request, you can safely ignore this email. Your password will not be changed.
            </div>
        </div>
    </body>
    </html>
    ';
}


// Database configuration — use shared config
require_once __DIR__ . '/include/dbConfig.php';

// ==========================================
// STATELESS TOKEN SECRET (change to a long random string in production)
// ==========================================
define('TOKEN_SECRET', 'ConnectAmravati_ZEAL_FIP_2026_SecretKey!@#$');
define('TOKEN_EXPIRY_SECONDS', 3600); // 1 hour

/**
 * Generate a stateless signed reset token.
 * Format: base64url(user_id . '.' . timestamp) . '.' . HMAC signature
 * No extra DB columns required — invalidates automatically when password changes.
 */
function generate_reset_token($user_id, $email, $password_hash) {
    $timestamp = time();
    $payload   = $user_id . '.' . $timestamp;
    $payload_b64 = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $data_to_sign = $payload_b64 . '.' . $email . '.' . $password_hash;
    $signature = hash_hmac('sha256', $data_to_sign, TOKEN_SECRET);
    return $payload_b64 . '.' . $signature;
}

/**
 * Verify a stateless reset token.
 * Returns user row on success, null on failure/expiry.
 */
function verify_reset_token($token, $conn) {
    $parts = explode('.', $token);
    if (count($parts) !== 2) return null;

    [$payload_b64, $received_sig] = $parts;

    $payload = base64_decode(strtr($payload_b64, '-_', '+/'));
    if ($payload === false || substr_count($payload, '.') !== 1) return null;

    [$user_id, $timestamp] = explode('.', $payload, 2);
    $user_id   = (int) $user_id;
    $timestamp = (int) $timestamp;

    // Check expiry
    if (time() - $timestamp > TOKEN_EXPIRY_SECONDS) return null;

    // Fetch user from DB using actual column names
    $stmt = $conn->prepare(
        "SELECT user_id, email, full_name, password_hash, status
         FROM users
         WHERE user_id = ? AND status = 'Active'"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if (!$user) return null;

    // Re-compute expected signature
    $data_to_sign = $payload_b64 . '.' . $user['email'] . '.' . $user['password_hash'];
    $expected_sig = hash_hmac('sha256', $data_to_sign, TOKEN_SECRET);

    // Constant-time comparison to prevent timing attacks
    if (!hash_equals($expected_sig, $received_sig)) return null;

    return $user;
}

$message = "";
$simulated_email = null;

// Determine current mode
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$mode  = !empty($token) ? 'reset' : 'request';

// Verify token if in reset mode
$valid_user = null;
if ($mode === 'reset') {
    $valid_user = verify_reset_token($token, $conn);
    if (!$valid_user) {
        $message = "<div class='error'>" . $t['err_invalid_link'] . "</div>";
        $mode = 'request';
    }
}

// Process Form Submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ACTION: Reset Password
    if (isset($_POST['password']) && $mode === 'reset' && $valid_user) {
        $password        = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            $message = "<div class='error'>" . $t['err_pwd_len'] . "</div>";
        } elseif ($password !== $confirmPassword) {
            $message = "<div class='error'>" . $t['err_pwd_match'] . "</div>";
        } else {
            // Hash and update using correct column: password_hash, primary key: user_id
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $user_id        = $valid_user['user_id'];

            $stmt = $conn->prepare(
                "UPDATE users
                 SET password_hash = ?, updated_at = NOW()
                 WHERE user_id = ? AND status = 'Active'"
            );
            $stmt->bind_param('si', $hashedPassword, $user_id);
            $stmt->execute();
            $stmt->close();

            $message   = "<div class='success'>" . $t['msg_success_reset'] . "</div>";
            $valid_user = null;
            $mode       = 'success';
            $token      = '';
        }
    }

    // ACTION: Request Reset Link
    elseif (isset($_POST['email']) && $mode === 'request') {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "<div class='error'>" . $t['err_invalid_email'] . "</div>";
        } else {
            // Lookup using actual columns: email, user_id, full_name, password_hash, status
            $stmt = $conn->prepare(
                "SELECT user_id, email, full_name, password_hash
                 FROM users
                 WHERE email = ? AND status = 'Active'
                 LIMIT 1"
            );
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user   = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                // Generate stateless token (no DB columns needed)
                $plain_token = generate_reset_token($user['user_id'], $user['email'], $user['password_hash']);
                
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                $host = $_SERVER['HTTP_HOST'];
                $dir = dirname($_SERVER['SCRIPT_NAME']);
                $dir = ($dir === '\\' || $dir === '/') ? '' : $dir;
                $reset_link = $protocol . "://" . $host . $dir . "/passwordReset.php?token=" . $plain_token . "&lang=" . $lang;
                
                $smtp_configured = (SMTP_PASS !== 'YOUR_APP_PASSWORD_OR_SMTP_PASSWORD' && !empty(SMTP_PASS));
                
                if (SMTP_ENABLED && $smtp_configured) {
                    try {
                        $subject    = "🔑 Reset Your Password - Connect Amravati";
                        $email_html = get_reset_email_html($reset_link, $user['full_name']);
                        
                        send_smtp_email(
                            $email,
                            $subject,
                            $email_html,
                            SMTP_USER,
                            SMTP_FROM_NAME,
                            SMTP_HOST,
                            SMTP_PORT,
                            SMTP_USER,
                            SMTP_PASS,
                            SMTP_SECURE
                        );
                        
                        $message = "<div class='success'>" . $t['msg_link_sent'] . "</div>";
                    } catch (Exception $e) {
                        $message = "<div class='error'>SMTP Mailer Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                        $simulated_email = [
                            'to' => $email,
                            'subject' => "🔑 Reset Your Password",
                            'link' => $reset_link,
                            'expiry' => '1 Hour'
                        ];
                    }
                } else {
                    $message = "<div class='success'>" . $t['msg_local_link'] . "</div>";
                    $simulated_email = [
                        'to' => $email,
                        'subject' => "🔑 Reset Your Password",
                        'link' => $reset_link,
                        'expiry' => '1 Hour'
                    ];
                }
            } else {
                $message = "<div class='error'>" . $t['err_no_account'] . "</div>";
            }
        }
    }
}
close_db_connection();
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php 
    if ($mode === 'success') echo htmlspecialchars($t['title_success']);
    elseif ($mode === 'reset' && $valid_user) echo htmlspecialchars($t['title_reset']);
    else echo htmlspecialchars($t['title_forgot']);
?></title>

<style>
.lang-switch {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 15px;
    font-size: 13px;
}
.lang-switch a {
    color: #2563eb;
    text-decoration: none;
    font-weight: bold;
}
.lang-switch a:hover {
    text-decoration: underline;
}

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f4f6f9;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    padding: 20px;
}

.container{
    background:#fff;
    width:400px;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
    box-sizing: border-box;
}

h2{
    margin-bottom:10px;
    color:#333;
}

p{
    color:#666;
    font-size:14px;
    margin-bottom:25px;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

.password-hint{
    font-size:12px;
    color:#777;
    margin-top:5px;
}

button{
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
    font-weight: bold;
}

button:hover{
    background:#1d4ed8;
}

.btn-link {
    display: block;
    text-align: center;
    width: 100%;
    padding: 12px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    box-sizing: border-box;
}

.btn-link:hover {
    background: #1d4ed8;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    font-size: 14px;
    line-height: 1.4;
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
    font-size: 14px;
    line-height: 1.4;
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #2563eb;
    text-decoration: none;
}
.back-link:hover {
    text-decoration: underline;
}

.email-simulator {
    width: 400px;
    margin-top: 20px;
    background: #fff;
    border: 1px dashed #2563eb;
    border-radius: 12px;
    padding: 25px;
    font-size: 13px;
    color: #333;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    box-sizing: border-box;
}
.email-header {
    font-weight: bold;
    border-bottom: 1px solid #eee;
    padding-bottom: 8px;
    margin-bottom: 12px;
    display: flex;
    justify-content: space-between;
}
.email-body {
    margin-top: 10px;
    line-height: 1.5;
}
.btn-reset-email {
    display: inline-block;
    background: #2563eb;
    color: white !important;
    text-decoration: none;
    padding: 10px 18px;
    border-radius: 6px;
    font-weight: 600;
    margin: 10px 0;
    text-align: center;
}
.btn-reset-email:hover {
    background: #1d4ed8;
}

@media(max-width:480px){
    .container, .email-simulator{
        width:100%;
    }
}
</style>
</head>

<body>

<div class="container">
    <div class="lang-switch">
        <?php if ($lang === 'en'): ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>">मराठी (MR)</a>
        <?php else: ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>">English (EN)</a>
        <?php endif; ?>
    </div>

    <?php if ($mode === 'success'): ?>
        <!-- SUCCESS MODE -->
        <h2><?php echo htmlspecialchars($t['heading_success']); ?></h2>
        <p><?php echo htmlspecialchars($t['desc_success']); ?></p>

        <?php echo $message; ?>

        <a href="login.php?lang=<?php echo $lang; ?>" class="btn-link"><?php echo htmlspecialchars($t['btn_go_to_login']); ?></a>

    <?php elseif ($mode === 'reset' && $valid_user): ?>
        <!-- RESET PASSWORD MODE -->
        <h2><?php echo htmlspecialchars($t['heading_reset']); ?></h2>
        <p><?php echo htmlspecialchars($t['desc_reset']); ?></p>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label><?php echo htmlspecialchars($t['label_new_pwd']); ?></label>
                <input type="password" name="password" required>
                <div class="password-hint">
                    <?php echo htmlspecialchars($t['pwd_hint']); ?>
                </div>
            </div>

            <div class="form-group">
                <label><?php echo htmlspecialchars($t['label_confirm_pwd']); ?></label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit"><?php echo htmlspecialchars($t['btn_reset_pwd']); ?></button>
        </form>
        
    <?php else: ?>
        <!-- REQUEST RESET LINK MODE -->
        <h2><?php echo htmlspecialchars($t['heading_forgot']); ?></h2>
        <p><?php echo htmlspecialchars($t['desc_forgot']); ?></p>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label><?php echo htmlspecialchars($t['label_email']); ?></label>
                <input type="email" name="email" required placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <button type="submit"><?php echo htmlspecialchars($t['btn_send_link']); ?></button>
        </form>
    <?php endif; ?>

</div>

<!-- Simulated email box for local debugging -->
<?php if ($simulated_email): ?>
    <div class="email-simulator">
        <div class="email-header">
            <span>Local Mail Dispatcher 📬</span>
            <span style="color: #2563eb; font-size: 11px;">Developer Mode</span>
        </div>
        <div><strong>To:</strong> <?php echo htmlspecialchars($simulated_email['to']); ?></div>
        <div><strong>Subject:</strong> <?php echo htmlspecialchars($simulated_email['subject']); ?></div>
        <div class="email-body">
            <p>Hello,</p>
            <p>You requested a password reset. To choose a new password, click the button below:</p>
            <div style="text-align: center;">
                <a href="<?php echo htmlspecialchars($simulated_email['link']); ?>" class="btn-reset-email">Reset Password</a>
            </div>
            <p style="margin-top: 15px; font-size: 11px; word-break: break-all; color: #777;">
                Direct Link: <a href="<?php echo htmlspecialchars($simulated_email['link']); ?>" style="color: #2563eb;"><?php echo htmlspecialchars($simulated_email['link']); ?></a>
            </p>
        </div>
    </div>
<?php endif; ?>

</body>
</html>