<?php
/**
 * NotificationEngine.php
 * Core engine for creating, storing, and dispatching notifications via Email and SMS.
 */

class NotificationEngine {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    /**
     * Create a notification and dispatch it
     */
    public function sendNotification($data) {
        // Defaults
        $type = $data['type'] ?? 'System';
        $priority = $data['priority'] ?? 'Medium';
        $title = $data['title'];
        $message = $data['message'];
        $taskId = $data['task_id'] ?? null;
        $announcementId = $data['announcement_id'] ?? null;
        $senderId = $data['sender_id'] ?? null;
        $receiverId = $data['receiver_id'];
        
        $sendEmail = $data['send_email'] ?? false;
        $sendSms = $data['send_sms'] ?? false;
        $receiverEmail = $data['receiver_email'] ?? null;
        $receiverMobile = $data['receiver_mobile'] ?? null;

        // 1. Insert into Database
        $stmt = $this->conn->prepare("INSERT INTO notifications (notification_type, notification_priority, title, message, task_id, announcement_id, sender_id, receiver_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssiiii", $type, $priority, $title, $message, $taskId, $announcementId, $senderId, $receiverId);
        
        if ($stmt->execute()) {
            $notificationId = $stmt->insert_id;

            // 2. Dispatch Email
            if ($sendEmail && $receiverEmail) {
                $this->dispatchEmail($notificationId, $receiverEmail, $title, $message);
            }

            // 3. Dispatch SMS
            if ($sendSms && $receiverMobile) {
                $this->dispatchSMS($notificationId, $receiverMobile, $message);
            }

            return $notificationId;
        }
        
        return false;
    }

    private function dispatchEmail($notificationId, $email, $subject, $body) {
        // Here you would integrate PHPMailer. For now, we stub it and use native mail() as a fallback
        // To use PHPMailer: 
        // require 'vendor/autoload.php';
        // $mail = new PHPMailer\PHPMailer\PHPMailer();
        
        $headers = "From: Amravati Connect <noreply@amravati.gov.in>\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $htmlBody = "
            <h2>{$subject}</h2>
            <p>{$body}</p>
            <hr>
            <p><small>This is an automated message from Amravati Connect.</small></p>
        ";

        $status = 'Failed';
        $errorMsg = '';

        try {
            if (mail($email, "[AMRAVATI CONNECT] " . $subject, $htmlBody, $headers)) {
                $status = 'Sent';
            } else {
                $errorMsg = 'mail() function failed';
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }

        // Log Email
        $stmt = $this->conn->prepare("INSERT INTO email_logs (notification_id, receiver_email, subject, status, error_message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $notificationId, $email, $subject, $status, $errorMsg);
        $stmt->execute();
        
        if ($status === 'Sent') {
            $this->conn->query("UPDATE notifications SET email_sent = 1 WHERE notification_id = $notificationId");
        }
    }

    private function dispatchSMS($notificationId, $mobile, $message) {
        // Stub for Fast2SMS / MSG91 API integration
        /*
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2?authorization=YOUR_API_KEY&route=v3&sender_id=TXTIND&message=".urlencode($message)."&language=english&flash=0&numbers=".$mobile,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        */
        
        $status = 'Sent'; // Assuming success for the stub
        $errorMsg = null;

        // Log SMS
        $stmt = $this->conn->prepare("INSERT INTO sms_logs (notification_id, mobile_no, message, status, error_message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $notificationId, $mobile, $message, $status, $errorMsg);
        $stmt->execute();

        if ($status === 'Sent') {
            $this->conn->query("UPDATE notifications SET sms_sent = 1 WHERE notification_id = $notificationId");
        }
    }
}
?>
