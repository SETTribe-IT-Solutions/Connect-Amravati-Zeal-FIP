<?php
/**
 * Shared Mailer Module for Connect Amravati
 * Provides centralized SMTP configuration and a reusable socket-based email sender.
 */

// ==========================================
// SMTP CONFIGURATION FOR REAL EMAIL DISPATCH
// ==========================================
if (!defined('SMTP_ENABLED')) {
    define('SMTP_ENABLED', true);
    define('SMTP_HOST', 'smtp.gmail.com');
    define('SMTP_PORT', 465);
    define('SMTP_SECURE', 'ssl');
    define('SMTP_USER', 'shree.chaugule@gmail.com');
    define('SMTP_PASS', 'mvnp mtcg goft cnps');
    define('SMTP_FROM_NAME', 'Connect Amravati Admin');
}

/**
 * Helper function to send email via SMTP socket connection.
 * Avoids the need for external libraries like PHPMailer if simple sending is required.
 */
if (!function_exists('send_smtp_email')) {
    // BUG FIX: Added $timeout param (default 5s). Was hardcoded 15s, blocking login page.
    function send_smtp_email($to, $subject, $message_html, $from_email, $from_name, $smtp_host, $smtp_port, $smtp_user, $smtp_pass, $smtp_secure = 'ssl', $timeout = 5) {
        $newline = "\r\n";
        
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
}
?>
