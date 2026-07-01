<?php
require_once 'include/dbConfig.php';
require_once 'include/mailer.php';

$testEmail = 'shree.chaugule@gmail.com';
try {
    send_smtp_email(
        $testEmail,
        'SMTP Test from Connect Amravati',
        '<h3>Testing email dispatcher</h3><p>If you get this, SMTP is working perfectly.</p>',
        SMTP_USER,
        SMTP_FROM_NAME,
        SMTP_HOST,
        SMTP_PORT,
        SMTP_USER,
        SMTP_PASS,
        SMTP_SECURE,
        5
    );
    echo json_encode(['status' => 'success', 'message' => 'Email sent successfully']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
