<?php
/**
 * logout.php - Securely logs out the user by destroying the session and sending email notification
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'include/dbConfig.php';
require_once 'include/mailer.php';

if (isset($_SESSION['email'])) {
    $to_email = $_SESSION['email'];
    $to_name = $_SESSION['full_name'] ?? 'Employee';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $device = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);
    $now = date('d M Y, h:i:s A');
    $subject = 'Logout Notification - Connect Amravati';
    $year = date('Y');
    
    $email_html = "
    <!DOCTYPE html>
    <html><head><meta charset='UTF-8'></head>
    <body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='padding:32px 0;background:#f1f5f9;'>
    <tr><td align='center'>
    <table width='580' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.10);max-width:580px;'>
      <tr><td style='background:#475569;padding:28px 32px;text-align:center;'>
        <div style='font-size:40px;'>&#x1F6AA;</div>
        <h2 style='margin:8px 0 4px;color:#fff;font-size:20px;'>Logout Successful</h2>
        <p style='margin:0;color:rgba(255,255,255,0.80);font-size:12px;'>Connect Amravati Portal &mdash; Security Notification</p>
      </td></tr>
      <tr><td style='padding:28px 32px;color:#374151;font-size:14px;line-height:1.75;'>
        <p>Hello <strong>" . htmlspecialchars($to_name) . "</strong>,</p>
        <p>You have successfully logged out of the <strong>Connect Amravati Portal</strong>.</p>
        <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
            <tr><td style='padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;width:130px;'>Date &amp; Time</td><td style='padding:8px 12px;border:1px solid #e2e8f0;'>$now</td></tr>
            <tr><td style='padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;'>IP Address</td><td style='padding:8px 12px;border:1px solid #e2e8f0;'>$ip</td></tr>
            <tr><td style='padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;font-weight:700;'>Device</td><td style='padding:8px 12px;border:1px solid #e2e8f0;'>$device</td></tr>
        </table>
      </td></tr>
      <tr><td style='background:#f8fafc;border-top:1px solid #e5e7eb;padding:16px 32px;text-align:center;'>
        <p style='margin:0;font-size:11px;color:#9ca3af;'>Automated security alert from Connect Amravati Portal. Do not reply.</p>
        <p style='margin:4px 0 0;font-size:11px;color:#9ca3af;'>&copy; $year District Administration, Amravati.</p>
      </td></tr>
    </table>
    </td></tr>
    </table>
    </body></html>
    ";
    
    if (SMTP_ENABLED) {
        try {
            send_smtp_email(
                $to_email, $subject, $email_html,
                SMTP_USER, SMTP_FROM_NAME,
                SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE,
                5
            );
        } catch (Exception $e) {
            error_log('[Logout Email] ' . $e->getMessage());
        }
    }
}

// Clear all session variables
$_SESSION = [];

// Destroy session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Prevent caching — stop browser back-button from showing protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect to login page (preserve inactivity reason if present)
$redirect = 'login.php';
if (isset($_GET['reason']) && $_GET['reason'] === 'inactivity') {
    $redirect .= '?reason=inactivity';
}
header("Location: " . $redirect);
exit;
