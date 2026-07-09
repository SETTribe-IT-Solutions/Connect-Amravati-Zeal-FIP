<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../include/dbConfig.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';
$sName  = $_SESSION['user_name'] ?? 'Employee';

$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

// Helper: Audit Logging
function logAuditAction($conn, $userId, $task_id, $action, $remarks) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $module = 'Task';
    
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) VALUES (?, ?, ?, ?, NULL, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ississs", $userId, $module, $action, $task_id, $remarks, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper: Create notification
function createTaskNotification($conn, $type, $title, $message, $task_id, $sender_id, $receiver_id) {
    $stmt = $conn->prepare("INSERT INTO notifications (notification_type, title, message, task_id, sender_id, receiver_id, status) VALUES (?, ?, ?, ?, ?, ?, 'Unread')");
    if ($stmt) {
        $stmt->bind_param("sssiii", $type, $title, $message, $task_id, $sender_id, $receiver_id);
        $stmt->execute();
        $notification_id = $stmt->insert_id;
        $stmt->close();
        
        // Log notification delivery
        $channel = 'System';
        $delivery_status = 'Sent';
        $log_sql = "INSERT INTO notification_delivery_logs (notification_id, channel, delivery_status, delivery_time) VALUES ($notification_id, '$channel', '$delivery_status', NOW())";
        $conn->query($log_sql);
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$taskId = (int)($_POST['task_id'] ?? $_GET['task_id'] ?? 0);

if ($taskId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid task ID']);
    exit;
}

// Fetch task info
$taskRes = $conn->query("SELECT * FROM tasks WHERE task_id = $taskId");
$task = $taskRes ? $taskRes->fetch_assoc() : null;

if (!$task) {
    echo json_encode(['status' => 'error', 'message' => 'Task not found']);
    exit;
}

$taskTitle = $task['task_title'];
$creatorId = (int)$task['created_by'];
$assignedUserId = (int)$task['assigned_user_id'];
$oldStatus = $task['status'];

if ($action === 'accept') {
    // Check if task is assigned to this user
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    // Update task status to Accepted
    $conn->query("UPDATE tasks SET status = 'Accepted' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Accepted' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Status History
    $stmt = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Accepted', ?, NOW(), 'Task accepted by employee')");
    if ($stmt) {
        $stmt->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmt->execute();
        $stmt->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Accepted', "$sName has accepted the task: $taskTitle");
    
    // Send Notification to creator
    createTaskNotification($conn, 'Task', 'Task Accepted', "$sName has accepted the assigned task: $taskTitle", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task accepted successfully']);
    exit;
}
elseif ($action === 'reject') {
    // Check if task is assigned to this user
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    $reason = trim($_POST['reason'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    
    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Rejection reason is mandatory']);
        exit;
    }
    if (empty($remarks)) {
        echo json_encode(['status' => 'error', 'message' => 'Detailed remarks are mandatory']);
        exit;
    }
    
    if (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Mandatory proof upload is missing']);
        exit;
    }
    
    // Handle proof file upload
    $upload_dir = '../uploads/tasks/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES['proof_file']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'];
    
    if (!in_array($file_ext, $allowed_exts)) {
        echo json_encode(['status' => 'error', 'message' => 'Unsupported file format. Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP']);
        exit;
    }
    
    $new_filename = 'REJ_PROOF_' . uniqid() . '.' . $file_ext;
    $db_file_path = 'uploads/tasks/' . $new_filename;
    
    if (!move_uploaded_file($_FILES['proof_file']['tmp_name'], $upload_dir . $new_filename)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save proof upload file']);
        exit;
    }
    
    // Insert rejection proofs record
    $stmt = $conn->prepare("INSERT INTO task_rejection_proofs (task_id, user_id, rejection_reason, remarks, file_path) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iisss", $taskId, $userId, $reason, $remarks, $db_file_path);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update task status to Pending Verification
    $conn->query("UPDATE tasks SET status = 'Pending Verification' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Pending Verification' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Save document to task_documents as well for backward compatibility
    $stmtDoc = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
    if ($stmtDoc) {
        $orig_name = $_FILES['proof_file']['name'];
        $stmtDoc->bind_param("isss", $taskId, $orig_name, $db_file_path, $userId);
        $stmtDoc->execute();
        $stmtDoc->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Pending Verification', ?, NOW(), ?)");
    if ($stmtHist) {
        $hist_remarks = "Rejected. Reason: $reason. Remarks: $remarks";
        $stmtHist->bind_param("isis", $taskId, $oldStatus, $userId, $hist_remarks);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Save to task_remarks as well
    $stmtRem = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Pending Verification')");
    if ($stmtRem) {
        $stmtRem->bind_param("iis", $taskId, $userId, $remarks);
        $stmtRem->execute();
        $stmtRem->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Rejected', "$sName has rejected the task: $taskTitle. Reason: $reason");
    
    // Send Notification to creator
    createTaskNotification($conn, 'Task', 'Task Rejected - Verification Required', "$sName has rejected Task: $taskTitle and submitted proof for verification.", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Rejection submitted for verification successfully']);
    exit;
}
elseif ($action === 'approve_rejection') {
    // Only L1 can verify and approve rejections
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only L1 Officers/Admins can approve rejections.']);
        exit;
    }
    
    // Update task status to Rejected Approved (Rejected Approved)
    $conn->query("UPDATE tasks SET status = 'Approved Rejection' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Approved Rejection' WHERE task_id = $taskId");
    
    // Verification Logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Approve Rejection', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Approved Rejection', ?, NOW(), 'Rejection approved by manager')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Verification Approved', "Task rejection approved by manager: $taskTitle");
    
    // Send Notification to employee
    createTaskNotification($conn, 'Task', 'Task Rejection Approved', "Your rejection request for Task: $taskTitle has been approved.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task rejection approved successfully']);
    exit;
}
elseif ($action === 'reject_rejection') {
    // Only L1 can verify and deny rejections
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only L1 Officers/Admins can deny rejections.']);
        exit;
    }
    
    // Update task status to Reassigned
    $conn->query("UPDATE tasks SET status = 'Reassigned' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Reassigned' WHERE task_id = $taskId");
    
    // Verification Logs
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Deny Rejection', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Reassigned', ?, NOW(), 'Rejection denied. Task reassigned.')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Verification Denied', "Task rejection denied by manager: $taskTitle");
    
    // Send Notification to employee
    createTaskNotification($conn, 'Task', 'Task Rejection Denied', "Your rejection request for Task: $taskTitle has been denied. Please complete the assigned task.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task rejection denied. Task reassigned to employee.']);
    exit;
}
elseif ($action === 'complete') {
    if ($assignedUserId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'This task is not assigned to you']);
        exit;
    }
    
    $achievements = trim($_POST['achievements'] ?? '');
    if (empty($achievements)) {
        echo json_encode(['status' => 'error', 'message' => 'Achievement details are mandatory']);
        exit;
    }
    
    $attachment_path = null;
    if (isset($_FILES['complete_file']) && $_FILES['complete_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/tasks/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['complete_file']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip'];
        if (in_array($file_ext, $allowed_exts)) {
            $new_filename = 'CMP_' . uniqid() . '.' . $file_ext;
            $attachment_path = 'uploads/tasks/' . $new_filename;
            move_uploaded_file($_FILES['complete_file']['tmp_name'], $upload_dir . $new_filename);
            
            // Insert doc
            $stmtDoc = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
            if ($stmtDoc) {
                $orig_name = $_FILES['complete_file']['name'];
                $stmtDoc->bind_param("isss", $taskId, $orig_name, $attachment_path, $userId);
                $stmtDoc->execute();
                $stmtDoc->close();
            }
        }
    }
    
    $conn->query("UPDATE tasks SET status = 'Completed', completion_date = NOW() WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Completed' WHERE task_id = $taskId AND assigned_to_user = $userId");
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Completed', ?, NOW(), ?)");
    if ($stmtHist) {
        $stmtHist->bind_param("isis", $taskId, $oldStatus, $userId, $achievements);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Task Remarks
    $stmtRem = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Completed')");
    if ($stmtRem) {
        $stmtRem->bind_param("iis", $taskId, $userId, $achievements);
        $stmtRem->execute();
        $stmtRem->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Completed', "$sName has completed task: $taskTitle");
    
    // Notification to creator
    createTaskNotification($conn, 'Task', 'Task Completed', "$sName has completed Task: $taskTitle.", $taskId, $userId, $creatorId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task marked as completed successfully']);
    exit;
}
elseif ($action === 'verify') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $conn->query("UPDATE tasks SET status = 'Verified' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'Verified' WHERE task_id = $taskId");
    
    // Status History
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Verified', ?, NOW(), 'Task marked as verified by manager')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Task Verified', "Task verified by manager: $taskTitle");
    
    // Notification to employee
    createTaskNotification($conn, 'Task', 'Task Verified', "Your completed Task: $taskTitle has been verified.", $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Task marked as verified successfully']);
    exit;
}
elseif ($action === 'request_clarification') {
    if (!$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        exit;
    }
    
    $msg = trim($_POST['message'] ?? 'Clarification requested for your task rejection.');
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO task_verification_logs (task_id, user_id, action, ip_address) VALUES (?, ?, 'Request Clarification', ?)");
    if ($stmt) {
        $stmt->bind_param("iis", $taskId, $userId, $ip);
        $stmt->execute();
        $stmt->close();
    }
    
    // Audit log
    logAuditAction($conn, $userId, $taskId, 'Clarification Request', "Clarification requested on task: $taskTitle");
    
    // Notification to employee
    createTaskNotification($conn, 'Task', 'Clarification Request', $msg, $taskId, $userId, $assignedUserId);
    
    echo json_encode(['status' => 'success', 'message' => 'Clarification request sent to employee']);
    exit;
}
elseif ($action === 'get_rejection_details') {
    // Return details from task_rejection_proofs
    $rejRes = $conn->query("SELECT r.*, u.full_name FROM task_rejection_proofs r LEFT JOIN users u ON r.user_id = u.user_id WHERE r.task_id = $taskId ORDER BY r.uploaded_at DESC LIMIT 1");
    $rej = $rejRes ? $rejRes->fetch_assoc() : null;
    
    if ($rej) {
        echo json_encode(['status' => 'success', 'rejection' => $rej]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Rejection details not found']);
    }
    exit;
}
elseif ($action === 'take_action') {
    // 1. Fetch assigned user email and details
    $emailRes = $conn->query("
        SELECT t.task_title, u.email, u.full_name, u.user_id as assignee_id 
        FROM tasks t 
        JOIN task_assignments ta ON t.task_id = ta.task_id 
        JOIN users u ON ta.assigned_to_user = u.user_id 
        WHERE t.task_id = $taskId LIMIT 1
    ");
    $taskData = $emailRes ? $emailRes->fetch_assoc() : null;

    if ($taskData && !empty($taskData['email'])) {
        require_once __DIR__ . '/../include/mailer.php';
        
        $assigneeId = (int)$taskData['assignee_id'];

        // Check how many overdue tasks the user has in the current quarter
        $countRes = $conn->query("
            SELECT COUNT(*) as overdue_count 
            FROM tasks t 
            JOIN task_assignments ta ON t.task_id = ta.task_id 
            WHERE ta.assigned_to_user = $assigneeId 
            AND t.status = 'Overdue' 
            AND QUARTER(t.created_at) = QUARTER(CURDATE()) 
            AND YEAR(t.created_at) = YEAR(CURDATE())
        ");
        $countData = $countRes ? $countRes->fetch_assoc() : ['overdue_count' => 0];
        $overdueCount = (int)$countData['overdue_count'];

        $to = $taskData['email'];
        
        if ($overdueCount > 5) {
            $subject = "TERMINATION NOTICE: Critical Overdue Limit Reached";
            $email_html = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ef4444; border-radius: 8px; background-color: #fef2f2;'>
                    <h2 style='color: #991b1b; text-align: center; text-transform: uppercase;'>Official Termination Notice</h2>
                    <p>Dear {$taskData['full_name']},</p>
                    <p>Our records indicate that you have accumulated <strong>$overdueCount overdue tasks</strong> in the current quarter, which exceeds the acceptable threshold.</p>
                    <p>Specifically, administrative action was taken today regarding the following overdue task:</p>
                    <div style='background-color: #ffffff; padding: 15px; border-left: 4px solid #ef4444; margin: 20px 0;'>
                        <p style='margin: 0; font-weight: bold;'>Task Title: {$taskData['task_title']}</p>
                        <p style='margin: 5px 0 0 0; color: #b91c1c;'>Status: Overdue</p>
                    </div>
                    <p style='color: #7f1d1d; font-weight: bold;'>As a result of chronic negligence, you are hereby issued a 10-day suspension/termination notice.</p>
                    <p>Please contact HR and your supervisor immediately to discuss this matter.</p>
                    <br>
                    <p>Sincerely,</p>
                    <p><strong>Administration</strong><br>Connect Amravati</p>
                </div>
            </body>
            </html>
            ";
            $messageResponse = "Termination email dispatched successfully. Candidate has > 5 overdue tasks ($overdueCount).";
        } else {
            $subject = "STRICT WARNING: Action Taken on Overdue Task '{$taskData['task_title']}'";
            $email_html = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f59e0b; border-radius: 8px; background-color: #fffbeb;'>
                    <h2 style='color: #b45309;'>Strict Warning: Action Taken on Overdue Task</h2>
                    <p>Dear {$taskData['full_name']},</p>
                    <p>This is a strict official warning. Administrative action has been taken regarding your assigned task because it has missed its deadline:</p>
                    <div style='background-color: #ffffff; padding: 15px; border-left: 4px solid #f59e0b; margin: 20px 0;'>
                        <p style='margin: 0; font-weight: bold;'>Task Title: {$taskData['task_title']}</p>
                        <p style='margin: 5px 0 0 0; color: #b45309;'>Status: Overdue</p>
                    </div>
                    <p>You currently have <strong>$overdueCount overdue task(s)</strong> this quarter. Be advised that accumulating more than 5 overdue tasks in a quarter will result in an automatic 10-day suspension/termination.</p>
                    <p>Please log in to the system immediately to complete this task and prevent further escalation.</p>
                    <br>
                    <p>Sincerely,</p>
                    <p><strong>Administration</strong><br>Connect Amravati</p>
                </div>
            </body>
            </html>
            ";
            $messageResponse = "Strict warning email dispatched successfully. Candidate has $overdueCount overdue tasks.";
        }
        
        try {
            if (SMTP_ENABLED) {
                send_smtp_email(
                    $to, $subject, $email_html, 
                    SMTP_USER, SMTP_FROM_NAME, 
                    SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE
                );
            }
            
            // Log audit
            logAuditAction($conn, $userId, $taskId, 'Action Taken', "Sent overdue action email to {$to} (Quarter Count: $overdueCount)");
            
            // Add notification
            $notifTitle = $overdueCount > 5 ? 'Termination Notice' : 'Strict Warning';
            $notifMsg = $overdueCount > 5 ? "You have been issued a 10-day suspension notice due to $overdueCount overdue tasks." : "A strict warning has been issued for your overdue task: {$taskData['task_title']}";
            createTaskNotification($conn, 'Task', $notifTitle, $notifMsg, $taskId, $userId, $assigneeId);

            echo json_encode(['status' => 'success', 'message' => $messageResponse]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Action recorded, but email failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Candidate email not found or task unassigned.']);
    }
    exit;
}
elseif ($action === 'reassign_task') {
    // Check authorization: is creator, current assignee, or L1 admin
    if ($assignedUserId !== $userId && $creatorId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied. Only the task creator, assignee, or administrator can transfer this task.']);
        exit;
    }
    
    // Support single and multiple assignees
    $newAssigneeIds = [];
    if (isset($_POST['new_assignee_ids']) && is_array($_POST['new_assignee_ids'])) {
        foreach ($_POST['new_assignee_ids'] as $uid) {
            $val = (int)$uid;
            if ($val > 0) $newAssigneeIds[] = $val;
        }
    } elseif (!empty($_POST['new_assignee_id'])) {
        $newAssigneeIds[] = (int)$_POST['new_assignee_id'];
    }

    if (empty($newAssigneeIds)) {
        echo json_encode(['status' => 'error', 'message' => 'At least one new assignee is required.']);
        exit;
    }

    // Fetch original task info
    $statusRes = $conn->query("SELECT * FROM tasks WHERE task_id = $taskId LIMIT 1");
    if ($statusRes && $statusRes->num_rows > 0) {
        $origTask = $statusRes->fetch_assoc();
        $oldStatus = $origTask['status'];
        $taskTitle = $origTask['task_title'];

        // We process the first assignee: update the original task
        $firstAssigneeId = array_shift($newAssigneeIds);
        
        // Lookup first assignee's department, role, location data
        $first_dept_id = $origTask['department_id'];
        $first_role_id = $origTask['assigned_role_id'];
        $first_district = $origTask['district_id'];
        $first_taluka   = $origTask['taluka_id'];
        $first_village  = $origTask['village_id'];

        $u_res = $conn->query("SELECT department_id, role_id, district_id, taluka_id, village_id FROM users WHERE user_id = $firstAssigneeId AND status = 'Active' LIMIT 1");
        if ($u_res && $u_row = $u_res->fetch_assoc()) {
            $first_dept_id = !empty($u_row['department_id']) ? (int)$u_row['department_id'] : $first_dept_id;
            $first_role_id = !empty($u_row['role_id'])       ? (int)$u_row['role_id']       : $first_role_id;
            $first_district = !empty($u_row['district_id'])   ? (int)$u_row['district_id']   : null;
            $first_taluka   = !empty($u_row['taluka_id'])     ? (int)$u_row['taluka_id']     : null;
            $first_village  = !empty($u_row['village_id'])    ? (int)$u_row['village_id']    : null;
        }

        $f_dept_sql = $first_dept_id ? (int)$first_dept_id : 'NULL';
        $f_role_sql = $first_role_id ? (int)$first_role_id : 'NULL';
        $f_dist_sql = $first_district ? (int)$first_district : 'NULL';
        $f_tal_sql  = $first_taluka ? (int)$first_taluka : 'NULL';
        $f_vil_sql  = $first_village ? (int)$first_village : 'NULL';

        // Update the tasks table for the first assignee
        $conn->query("UPDATE tasks SET assigned_user_id = $firstAssigneeId, department_id = $f_dept_sql, assigned_role_id = $f_role_sql, district_id = $f_dist_sql, taluka_id = $f_tal_sql, village_id = $f_vil_sql, status = 'Reassigned', updated_at = NOW() WHERE task_id = $taskId");
        
        // Update task_assignments table
        $conn->query("UPDATE task_assignments SET assigned_to_user = $firstAssigneeId, assigned_to_role = $f_role_sql, status = 'Reassigned' WHERE task_id = $taskId");
        
        // Add to task_status_history
        $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Reassigned', ?, NOW(), 'Task transferred to new member.')");
        if ($stmtHist) {
            $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
            $stmtHist->execute();
            $stmtHist->close();
        }

        // Notify new assignee
        createTaskNotification($conn, 'Task', 'Task Reassigned', "You have been reassigned a new task: $taskTitle.", $taskId, $userId, $firstAssigneeId);

        // Audit Log
        logAuditAction($conn, $userId, $taskId, 'Task Reassigned', "Task reassigned to user ID $firstAssigneeId");

        // Loop over any remaining assignee IDs and create duplicate tasks for them!
        foreach ($newAssigneeIds as $nextAssigneeId) {
            $next_dept_id = $origTask['department_id'];
            $next_role_id = $origTask['assigned_role_id'];
            $next_district = $origTask['district_id'];
            $next_taluka   = $origTask['taluka_id'];
            $next_village  = $origTask['village_id'];

            $u_res2 = $conn->query("SELECT department_id, role_id, district_id, taluka_id, village_id FROM users WHERE user_id = $nextAssigneeId AND status = 'Active' LIMIT 1");
            if ($u_res2 && $u_row2 = $u_res2->fetch_assoc()) {
                $next_dept_id = !empty($u_row2['department_id']) ? (int)$u_row2['department_id'] : $next_dept_id;
                $next_role_id = !empty($u_row2['role_id'])       ? (int)$u_row2['role_id']       : $next_role_id;
                $next_district = !empty($u_row2['district_id'])   ? (int)$u_row2['district_id']   : null;
                $next_taluka   = !empty($u_row2['taluka_id'])     ? (int)$u_row2['taluka_id']     : null;
                $next_village  = !empty($u_row2['village_id'])    ? (int)$u_row2['village_id']    : null;
            }

            // Build SQL literals for duplication
            $d_dept_sql  = $next_dept_id ? (int)$next_dept_id : 'NULL';
            $d_role_sql  = $next_role_id ? (int)$next_role_id : 'NULL';
            $d_dist_sql  = $next_district ? (int)$next_district : 'NULL';
            $d_tal_sql   = $next_taluka ? (int)$next_taluka : 'NULL';
            $d_vil_sql   = $next_village ? (int)$next_village : 'NULL';

            $t_title_esc = $conn->real_escape_string($origTask['task_title']);
            $t_desc_esc  = $conn->real_escape_string($origTask['task_description']);
            $t_pri_esc   = $conn->real_escape_string($origTask['priority']);
            $t_cat_sql   = $origTask['task_category'] ? "'" . $conn->real_escape_string($origTask['task_category']) . "'" : 'NULL';
            $t_due_sql   = $origTask['due_date'] ? "'" . $origTask['due_date'] . "'" : 'NULL';
            $t_rem_sql   = $origTask['remarks'] ? "'" . $conn->real_escape_string($origTask['remarks']) . "'" : 'NULL';
            
            $tmp_no = 'TASK_TMP_' . time() . '_' . rand(100, 999);

            $ins_sql = "INSERT INTO tasks
                            (task_no, task_title, task_description, priority, task_category,
                             department_id, created_by, assigned_role_id, assigned_user_id,
                             district_id, taluka_id, village_id,
                             due_date, status, remarks)
                        VALUES
                            ('" . $conn->real_escape_string($tmp_no) . "',
                             '$t_title_esc', '$t_desc_esc', '$t_pri_esc', $t_cat_sql,
                             $d_dept_sql, {$origTask['created_by']}, $d_role_sql, $nextAssigneeId,
                             $d_dist_sql, $d_tal_sql, $d_vil_sql,
                             $t_due_sql, 'Reassigned', $t_rem_sql)";

            if ($conn->query($ins_sql)) {
                $new_tid = $conn->insert_id;

                // Sequentially pad task_no
                $seq_res = $conn->query("SELECT COUNT(*) AS cnt FROM tasks WHERE task_id <= $new_tid");
                $seq_row = $seq_res ? $seq_res->fetch_assoc() : null;
                $seq_num = (int)($seq_row['cnt'] ?? $new_tid);
                $new_no_str = 'TASK_' . str_pad($seq_num, 3, '0', STR_PAD_LEFT);

                $conn->query("UPDATE tasks SET task_no = '" . $conn->real_escape_string($new_no_str) . "' WHERE task_id = $new_tid");

                // Duplicate attachments/remarks
                $doc_res = $conn->query("SELECT * FROM task_documents WHERE task_id = $taskId");
                if ($doc_res) {
                    while ($doc_row = $doc_res->fetch_assoc()) {
                        $f_name_esc = $conn->real_escape_string($doc_row['file_name']);
                        $f_path_esc = $conn->real_escape_string($doc_row['file_path']);
                        $conn->query("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES ($new_tid, '$f_name_esc', '$f_path_esc', {$doc_row['uploaded_by']})");
                    }
                }
                
                $rem_res = $conn->query("SELECT * FROM task_remarks WHERE task_id = $taskId");
                if ($rem_res) {
                    while ($rem_row = $rem_res->fetch_assoc()) {
                        $rem_esc = $conn->real_escape_string($rem_row['remark_text']);
                        $conn->query("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark, created_at) VALUES ($new_tid, {$rem_row['user_id']}, '$rem_esc', 'Pending', NOW())");
                    }
                }

                // Insert activity log
                $act_desc = $conn->real_escape_string("Task duplicated via transfer reassignment.");
                $conn->query("INSERT INTO task_activity_logs (task_id, user_id, activity_type, description, activity_time) VALUES ($new_tid, $userId, 'Task Created', '$act_desc', NOW())");

                // Insert task assignments
                $conn->query("INSERT INTO task_assignments (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status) VALUES ($new_tid, $userId, $nextAssigneeId, $d_role_sql, NOW(), 'Reassigned')");

                // Notify new assignee
                createTaskNotification($conn, 'Task', 'Task Reassigned', "You have been assigned a transferred task: $taskTitle.", $new_tid, $userId, $nextAssigneeId);

                // Audit Log
                logAuditAction($conn, $userId, $new_tid, 'Task Reassigned', "Task created via transfer to user ID $nextAssigneeId");
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Task transferred successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Task not found.']);
    }
    exit;
}

elseif ($action === 'hold_task') {
    if ($assignedUserId !== $userId && $creatorId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
        exit;
    }
    
    $conn->query("UPDATE tasks SET status = 'On Hold' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'On Hold' WHERE task_id = $taskId");
    
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'On Hold', ?, NOW(), 'Task put on hold.')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    $receiverId = ($userId === $assignedUserId) ? $creatorId : $assignedUserId;
    createTaskNotification($conn, 'Task', 'Task Put On Hold', "The task '$taskTitle' has been put on hold.", $taskId, $userId, $receiverId);
    logAuditAction($conn, $userId, $taskId, 'Task Put On Hold', "Task put on hold by user ID $userId");
    
    echo json_encode(['status' => 'success', 'message' => 'Task put on hold successfully.']);
    exit;
}
elseif ($action === 'resume_task') {
    if ($assignedUserId !== $userId && $creatorId !== $userId && !$isL1) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied.']);
        exit;
    }
    
    $conn->query("UPDATE tasks SET status = 'In Progress' WHERE task_id = $taskId");
    $conn->query("UPDATE task_assignments SET status = 'In Progress' WHERE task_id = $taskId");
    
    $stmtHist = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'In Progress', ?, NOW(), 'Task resumed.')");
    if ($stmtHist) {
        $stmtHist->bind_param("isi", $taskId, $oldStatus, $userId);
        $stmtHist->execute();
        $stmtHist->close();
    }
    
    $receiverId = ($userId === $assignedUserId) ? $creatorId : $assignedUserId;
    createTaskNotification($conn, 'Task', 'Task Resumed', "The task '$taskTitle' has been resumed.", $taskId, $userId, $receiverId);
    logAuditAction($conn, $userId, $taskId, 'Task Resumed', "Task resumed by user ID $userId");
    
    echo json_encode(['status' => 'success', 'message' => 'Task resumed successfully.']);
    exit;
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    exit;
}
?>
