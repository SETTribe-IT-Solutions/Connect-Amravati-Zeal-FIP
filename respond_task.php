<?php
/**
 * Respond Task Page
 * Amravati Connect - Government Workflow Platform
 *
 * Allows assigned employees to Accept, Acknowledge, or Reject a task.
 * Rejection requires a mandatory text reason and a PDF/JPEG file attachment.
 */

session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
require_once 'include/dbConfig.php';

// Bilingual translations dictionary
$translations = [
    'en' => [
        'title' => 'Task Action & Detail — Amravati Connect',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
        'menu_notifications' => 'Notification Center',
        'menu_appreciation' => 'Appreciation',
        'menu_analytics' => 'Analytics & Data',
        'menu_reports' => 'Reports & Analytics',
        'menu_gis' => 'GIS Map View',
        'menu_docs' => 'Document Management',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'menu_logout' => 'Logout',
        
        // Page headings & labels
        'task_details' => 'Task Details & Response',
        'task_info' => 'View task details and submit your action response.',
        'task_no' => 'Task Number',
        'task_title' => 'Task Title',
        'description' => 'Description',
        'priority' => 'Priority Level',
        'category' => 'Category',
        'due_date' => 'Due Date & Time',
        'created_by' => 'Created By',
        'assigned_to' => 'Assigned To',
        'target' => 'Target / Milestone',
        'status' => 'Current Status',
        'attachments' => 'Attachments',
        'no_attachments' => 'No attachments uploaded for this task.',
        'action_panel' => 'Respond to Assigned Task',
        'action_desc' => 'Choose an action below to update the task status.',
        'btn_accept' => 'Accept Task',
        'btn_acknowledge' => 'Acknowledge Task',
        'btn_reject' => 'Reject Task',
        'label_reject_reason' => 'Reason for Rejection',
        'placeholder_reject_reason' => 'Please explain why you are rejecting this task...',
        'label_reject_attachment' => 'Upload Mandatory Rejection Document',
        'desc_reject_attachment' => 'PDF or JPEG format only (max 20MB)',
        'btn_submit_response' => 'Submit Response',
        
        // Actions details
        'action_accept_desc' => 'Confirm you are accepting the task and will begin work.',
        'action_acknowledge_desc' => 'Acknowledge that you have received the task assignment.',
        'action_reject_desc' => 'Reject the task assignment (rejection reason & document mandatory).',
        'rejection_detail' => 'Rejection Details',
        
        // Alerts
        'msg_already_responded' => 'You have already responded to this task.',
        'msg_not_assignee' => 'Only the assigned employee has rights to accept, acknowledge, or reject this task.',
        'msg_response_success' => 'Your response has been registered successfully.',
        'error_rejection_reason' => 'Reason for rejection is required.',
        'error_rejection_file' => 'A valid PDF or JPEG attachment is mandatory when rejecting a task.',
        'back_to_dashboard' => 'Back to Dashboard',
        'view_attachment' => 'View Attachment',
        'status_pending' => 'Pending Action',
        'status_accepted' => 'Accepted',
        'status_acknowledged' => 'Acknowledged',
        'status_rejected' => 'Rejected',
        'status_completed' => 'Completed',
        'status_overdue' => 'Overdue',
        'status_in_progress' => 'In Progress',
        
        // Dev Fallback
        'msg_task_not_found' => 'Task not found or database connection issue.',
    ],
    'mr' => [
        'title' => 'कामाची कृती आणि तपशील — अमरावती कनेक्ट',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_appreciation' => 'कौतुक',
        'menu_analytics' => 'विश्लेषण आणि डेटा',
        'menu_reports' => 'अहवाल आणि विश्लेषण',
        'menu_gis' => 'जीआयएस नकाशा',
        'menu_docs' => 'दस्तऐवज व्यवस्थापन',
        'menu_admin' => 'प्रशासन',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'menu_logout' => 'लॉगआउट',
        
        // Page headings & labels
        'task_details' => 'कामाचा तपशील आणि प्रतिसाद',
        'task_info' => 'कामाचा तपशील पहा आणि तुमचा प्रतिसाद सादर करा.',
        'task_no' => 'काम क्रमांक (Task Number)',
        'task_title' => 'कामाचे शीर्षक',
        'description' => 'वर्णन',
        'priority' => 'प्राधान्य स्तर',
        'category' => 'वर्ग (Category)',
        'due_date' => 'अंतिम तारीख आणि वेळ',
        'created_by' => 'द्वारे तयार केले',
        'assigned_to' => 'नियुक्त कर्मचारी',
        'target' => 'उद्दिष्ट / टप्पा',
        'status' => 'सध्याची स्थिती',
        'attachments' => 'जोडलेली कागदपत्रे (Attachments)',
        'no_attachments' => 'या कामासाठी कोणतीही दस्तऐवज जोडलेली नाहीत.',
        'action_panel' => 'नियुक्त कामाला प्रतिसाद द्या',
        'action_desc' => 'कामाची स्थिती अपडेट करण्यासाठी खालीलपैकी एक पर्याय निवडा.',
        'btn_accept' => 'काम स्वीकारा (Accept)',
        'btn_acknowledge' => 'कामाची पोहोच द्या (Acknowledge)',
        'btn_reject' => 'काम नाकारा (Reject)',
        'label_reject_reason' => 'काम नाकारण्याचे कारण',
        'placeholder_reject_reason' => 'कृपया काम नाकारण्याचे कारण स्पष्ट करा...',
        'label_reject_attachment' => 'नाकारण्याचा बंधनकारक दस्तऐवज अपलोड करा',
        'desc_reject_attachment' => 'फक्त PDF किंवा JPEG फॉरमॅट (कमाल २० MB)',
        'btn_submit_response' => 'प्रतिसाद सादर करा',
        
        // Actions details
        'action_accept_desc' => 'तुम्ही काम स्वीकारत आहात आणि काम सुरू कराल याची पुष्टी करा.',
        'action_acknowledge_desc' => 'तुम्हाला कामाचे वाटप प्राप्त झाले आहे याची पोहोच द्या.',
        'action_reject_desc' => 'कामाचे वाटप नाकारा (कारण आणि दस्तऐवज अपलोड करणे बंधनकारक).',
        'rejection_detail' => 'नाकारल्याचा तपशील',
        
        // Alerts
        'msg_already_responded' => 'तुम्ही यापूर्वीच या कामाला प्रतिसाद दिला आहे.',
        'msg_not_assignee' => 'फक्त नियुक्त कर्मचाऱ्यालाच काम स्वीकारण्याचा, पोहोच देण्याचा किंवा नाकारण्याचा अधिकार आहे.',
        'msg_response_success' => 'तुमचा प्रतिसाद यशस्वीरित्या नोंदवला गेला आहे.',
        'error_rejection_reason' => 'काम नाकारण्याचे कारण देणे बंधनकारक आहे.',
        'error_rejection_file' => 'काम नाकारताना वैध PDF किंवा JPEG दस्तऐवज जोडणे बंधनकारक आहे.',
        'back_to_dashboard' => 'डॅशबोर्डवर परत जा',
        'view_attachment' => 'दस्तऐवज पहा',
        'status_pending' => 'प्रलंबित कारवाई',
        'status_accepted' => 'स्वीकारले (Accepted)',
        'status_acknowledged' => 'पोहोच दिली (Acknowledged)',
        'status_rejected' => 'नाकारले (Rejected)',
        'status_completed' => 'पूर्ण झाले (Completed)',
        'status_overdue' => 'थकीत (Overdue)',
        'status_in_progress' => 'प्रगतीपथावर',
        
        // Dev Fallback
        'msg_task_not_found' => 'काम आढळले नाही किंवा डेटाबेस कनेक्शनची समस्या आहे.',
    ]
];
$tr = $translations[$lang];

$success_msg = '';
$error_msg = '';

if (isset($_SESSION['msg'])) {
    $success_msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// ═══════════════════════════════════════════════════════════════════
// HELPER: Create notification + delivery log for one assigned user
// ═══════════════════════════════════════════════════════════════════
function createTaskNotification(
    mysqli $conn,
    int    $task_id,
    string $task_title,
    string $due_date,
    int    $receiver_id,
    int    $sender_id,
    string $notif_type = 'Task Assigned'
): array {
    $title   = $conn->real_escape_string('Task Status Updated: ' . $task_title);
    $message = $conn->real_escape_string(
        'Task update: "' . $task_title . '" status changed to ' . $notif_type . '.'
    );
    $notif_type_safe = $conn->real_escape_string($notif_type);

    $notif_sql = "INSERT INTO notifications
                      (notification_type, title, message, task_id,
                       sender_id, receiver_id, status)
                  VALUES
                      ('$notif_type_safe', '$title', '$message', $task_id,
                       $sender_id, $receiver_id, 'Unread')";

    $notification_id = null;
    $insert_ok       = false;
    $error_msg       = '';

    if ($conn->query($notif_sql)) {
        $notification_id = $conn->insert_id;
        $insert_ok       = true;
    } else {
        $error_msg = $conn->error;
    }

    if ($notification_id !== null) {
        $log_notif_id    = $notification_id;
        $delivery_status = $conn->real_escape_string('Sent');
        $channel         = $conn->real_escape_string('System');

        $log_sql = "INSERT INTO notification_delivery_logs
                        (notification_id, channel, delivery_status, delivery_time)
                    VALUES
                        ($log_notif_id, '$channel', '$delivery_status', NOW())";
        $conn->query($log_sql);
    }
    return [
        'ok'              => $insert_ok,
        'notification_id' => $notification_id,
        'error'           => $error_msg,
    ];
}

$user_id = $_SESSION['user_id'] ?? 1;

// ─── Handle Response Form Submission (Assignee Action) ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_response_action'])) {
    $action = trim($_POST['task_response_action']);
    $resp_task_id = (int)$_POST['response_task_id'];
    
    // Check if task assignment exists for this user
    $assign_res = $conn->query("
        SELECT ta.*, t.task_title, t.due_date 
        FROM task_assignments ta
        JOIN tasks t ON ta.task_id = t.task_id
        WHERE ta.task_id = $resp_task_id AND ta.assigned_to_user = $user_id
        LIMIT 1
    ");
    
    if ($assign_res && $assign_res->num_rows > 0) {
        $assignment = $assign_res->fetch_assoc();
        $task_title = $assignment['task_title'];
        $due_date = $assignment['due_date'];
        
        if ($action === 'accept') {
            $status = 'Accepted';
            $conn->query("UPDATE tasks SET status = '$status' WHERE task_id = $resp_task_id");
            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
            
            // Log to task_tracking
            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', 'Task accepted by assignee', $user_id, NOW())");
            
            // Notify the creator
            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'Accepted');
            
            $_SESSION['msg'] = $tr['msg_response_success'];
            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
            exit();
            
        } elseif ($action === 'acknowledge') {
            $status = 'Acknowledged';
            $conn->query("UPDATE tasks SET status = '$status' WHERE task_id = $resp_task_id");
            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
            
            // Log to task_tracking
            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', 'Task acknowledged by assignee', $user_id, NOW())");
            
            // Notify the creator
            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'Acknowledged');
            
            $_SESSION['msg'] = $tr['msg_response_success'];
            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
            exit();
            
        } elseif ($action === 'in_progress') {
            $status = 'In Progress';
            $conn->query("UPDATE tasks SET status = '$status' WHERE task_id = $resp_task_id");
            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
            
            // Log to task_tracking
            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', 'Task marked as In Progress', $user_id, NOW())");
            
            // Notify the creator
            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'In Progress');
            
            $_SESSION['msg'] = $tr['msg_response_success'];
            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
            exit();
            
        } elseif ($action === 'complete') {
            $status = 'Completed';
            $conn->query("UPDATE tasks SET status = '$status' WHERE task_id = $resp_task_id");
            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
            
            // Log to task_tracking
            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', 'Task completed', $user_id, NOW())");
            
            // Notify the creator
            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'Completed');
            
            $_SESSION['msg'] = $tr['msg_response_success'];
            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
            exit();
            
        } elseif ($action === 'pending') {
            $status = 'Pending';
            $conn->query("UPDATE tasks SET status = '$status' WHERE task_id = $resp_task_id");
            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
            
            // Log to task_tracking
            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', 'Task reverted to Pending', $user_id, NOW())");
            
            // Notify the creator
            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'Pending');
            
            $_SESSION['msg'] = $tr['msg_response_success'];
            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
            exit();
            
        } elseif ($action === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? '');
            if (empty($reason)) {
                $error_msg = $tr['error_rejection_reason'];
            } else {
                if (!isset($_FILES['rejection_attachment']) || $_FILES['rejection_attachment']['error'] !== UPLOAD_ERR_OK) {
                    $error_msg = $tr['error_rejection_file'];
                } else {
                    $tmp_name = $_FILES['rejection_attachment']['tmp_name'];
                    $orig_name = $_FILES['rejection_attachment']['name'];
                    $file_size = $_FILES['rejection_attachment']['size'];
                    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                    $file_mime = mime_content_type($tmp_name);
                    
                    $allowed_exts = ['pdf', 'jpg', 'jpeg'];
                    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/jpg'];
                    
                    if (!in_array($ext, $allowed_exts) || !in_array($file_mime, $allowed_mimes)) {
                        $error_msg = $tr['error_rejection_file'];
                    } else {
                        // Create upload directory
                        $upload_dir = __DIR__ . '/uploads/rejections/';
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0755, true);
                        }
                        $new_name = 'REJECT_' . $resp_task_id . '_' . time() . '.' . $ext;
                        $dest_path = 'uploads/rejections/' . $new_name;
                        
                        if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
                            $status = 'Rejected';
                            $safe_reason = $conn->real_escape_string($reason);
                            
                            $conn->query("UPDATE tasks SET status = '$status', remarks = '$safe_reason' WHERE task_id = $resp_task_id");
                            $conn->query("UPDATE task_assignments SET status = '$status' WHERE task_id = $resp_task_id AND assigned_to_user = $user_id");
                            
                            // Log to task_tracking
                            $conn->query("INSERT INTO task_tracking (task_id, status, remarks, updated_by, updated_at) VALUES ($resp_task_id, '$status', '$safe_reason', $user_id, NOW())");
                            
                            // Insert to task_documents
                            $safe_path = $conn->real_escape_string($dest_path);
                            $safe_orig = $conn->real_escape_string($orig_name);
                            $safe_mime = $conn->real_escape_string($file_mime);
                            $conn->query("
                                INSERT INTO task_documents 
                                (task_id, file_path, original_name, file_type, file_size, uploaded_by)
                                VALUES 
                                ($resp_task_id, '$safe_path', '$safe_orig', '$safe_mime', $file_size, $user_id)
                            ");
                            
                            // Notify creator
                            createTaskNotification($conn, $resp_task_id, $task_title, $due_date ? $due_date : '', (int)$assignment['assigned_from_user'], $user_id, 'Rejected');
                            
                            $_SESSION['msg'] = $tr['msg_response_success'];
                            header("Location: respond_task.php?task_id=$resp_task_id&lang=$lang");
                            exit();
                        } else {
                            $error_msg = "Failed to save rejection document. Please check folder permissions.";
                        }
                    }
                }
            }
        }
    } else {
        $error_msg = $tr['msg_not_assignee'];
    }
}

// ─── Fetch Task Detail ───────────────────────────────────────────────
$task_id = isset($_GET['task_id']) ? (int)$_GET['task_id'] : 0;
$task_data = null;
$is_assignee = false;
$user_assignment = null;
$attachments = [];

if ($task_id > 0) {
    $task_res = $conn->query("
        SELECT t.*, u.full_name AS creator_name, dept.department_name,
               COALESCE(au.full_name, ar.role_name, 'Unassigned') AS assignee_name
        FROM tasks t
        LEFT JOIN users u ON t.created_by = u.user_id
        LEFT JOIN departments dept ON t.department_id = dept.id
        LEFT JOIN users au ON t.assigned_user_id = au.user_id
        LEFT JOIN roles ar ON t.assigned_role_id = ar.role_id
        WHERE t.task_id = $task_id
        LIMIT 1
    ");
    
    if ($task_res && $task_res->num_rows > 0) {
        $task_data = $task_res->fetch_assoc();
        
        // Fetch attachments
        $attach_res = $conn->query("
            SELECT * FROM task_documents 
            WHERE task_id = $task_id
            ORDER BY document_id DESC
        ");
        if ($attach_res) {
            while ($att = $attach_res->fetch_assoc()) {
                $attachments[] = $att;
            }
        }
        
        // Check assignment
        $assign_check = $conn->query("
            SELECT * FROM task_assignments 
            WHERE task_id = $task_id AND assigned_to_user = $user_id 
            LIMIT 1
        ");
        if ($assign_check && $assign_check->num_rows > 0) {
            $is_assignee = true;
            $user_assignment = $assign_check->fetch_assoc();
        }
    }
}

/* Friendly role label */
$sRole = $_SESSION['role_name'] ?? 'Officer';
$sName = $_SESSION['full_name'] ?? 'Government Employee';
$parts = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'O', 0, 1) . substr($parts[1] ?? '', 0, 1));
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('acTheme') || localStorage.getItem('theme');
            const sessionTheme = '<?= $_SESSION['pref_theme'] ?? '' ?>';
            const isDark = stored === 'dark' || (sessionTheme === 'dark') || (!stored && !sessionTheme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tr['title']) ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        border:      "hsl(var(--border))",
                        background:  "hsl(var(--background))",
                        foreground:  "hsl(var(--foreground))",
                        navy: {
                            50:  '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1a365d',
                            600: '#152b4a',
                            700: '#0f1f38',
                            900: '#0a1424'
                        },
                        govgreen: {
                            50:  '#edf7ed',
                            100: '#cce8cc',
                            500: '#2e7d32',
                            600: '#256428'
                        },
                        saffron: {
                            50:  '#fff3e0',
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
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --border: 214.3 31.8% 91.4%;
        }
        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }

        .glass-panel {
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        .dark .glass-panel {
            background: rgba(15,23,42,0.7);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .badge-low      { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-medium   { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-high     { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-critical { background: #fae8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .dark .badge-low      { background: #14532d33; color: #86efac; border-color: #166534; }
        .dark .badge-medium   { background: #78350f33; color: #fde047; border-color: #854d0e; }
        .dark .badge-high     { background: #7f1d1d33; color: #fca5a5; border-color: #991b1b; }
        .dark .badge-critical { background: #4a044e33; color: #d8b4fe; border-color: #6b21a8; }

        .form-label {
            @apply block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5;
        }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeSlideIn 0.35s ease both; }
        #toast { transition: opacity 0.4s, transform 0.4s; }
        #toast.hidden { opacity: 0; pointer-events: none; transform: translateY(20px); }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- SIDEBAR -->
<aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
    </div>

    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($tr['menu_main_modules']) ?></p>
            <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_dashboard']) ?>
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_task_alloc']) ?>
            </a>
            <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_notifications']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_appreciation']) ?>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($tr['menu_analytics']) ?></p>
            <a href="overdue_report.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_reports']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_gis']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_docs']) ?>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="user_creation.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($tr['menu_users']) ?>
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none">
            <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
            Ask Amravati AI
        </button>
    </div>
</aside>

<!-- MAIN WRAPPER -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <nav class="flex items-center text-sm" aria-label="Breadcrumb">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <a href="#" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Task Allocation</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['task_details']) ?></span>
            </nav>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language Toggle -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'respond_task.php?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                <?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?>
            </a>
            <!-- Theme Switcher -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            <!-- Profile Dropdown Container -->
            <div class="relative pl-4 ml-2 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none" aria-haspopup="true" aria-expanded="false">
                    <div class="flex flex-col text-right hidden sm:block mr-2">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?></span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm transition-transform duration-200 hover:scale-105 active:scale-95">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordReset.php?lang=<?= $lang ?>" class="flex items-center px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800/50 transition-colors">
                            <i data-lucide="key" class="w-4 h-4 mr-2.5 text-slate-400"></i>
                            <?= $lang === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                        </a>
                        <div class="border-t border-slate-150 dark:border-slate-800 my-1"></div>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2.5 text-red-500"></i>
                            <?= $lang === 'en' ? 'Logout' : 'लॉगआउट' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- PHP Alert Messages -->
        <?php if ($success_msg): ?>
        <div id="phpAlert" class="mb-6 flex items-start gap-3 p-4 bg-govgreen-50 dark:bg-green-900/20 border border-govgreen-100 dark:border-green-800 rounded-xl animate-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-govgreen-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-govgreen-700 dark:text-green-300"><?= $success_msg ?></p>
            </div>
            <button onclick="document.getElementById('phpAlert').remove()" class="ml-auto text-govgreen-500 hover:text-govgreen-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
        <div id="phpAlert" class="mb-6 flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl animate-in">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5"></i>
            <p class="text-sm font-medium text-red-700 dark:text-red-300"><?= htmlspecialchars($error_msg) ?></p>
            <button onclick="document.getElementById('phpAlert').remove()" class="ml-auto text-red-400 hover:text-red-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <?php endif; ?>

        <?php if (!$task_data): ?>
        <!-- Task Not Found fallback panel -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 text-center animate-in">
            <i data-lucide="alert-octagon" class="w-12 h-12 text-red-500 mx-auto mb-4"></i>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['msg_task_not_found']) ?></h2>
            <a href="dashboard.php?lang=<?= $lang ?>" class="mt-4 inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                <?= htmlspecialchars($tr['back_to_dashboard']) ?>
            </a>
        </div>
        <?php else: ?>

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 animate-in">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md">
                        <i data-lucide="eye" class="w-5 h-5 text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">
                        <?= htmlspecialchars($tr['task_details']) ?>
                    </h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 ml-13">
                    <?= htmlspecialchars($tr['task_info']) ?>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <i data-lucide="hash" class="w-3.5 h-3.5 mr-1.5"></i>
                    ID: <span class="font-bold ml-1"><?= htmlspecialchars($task_data['task_no']) ?></span>
                </span>
                <a href="dashboard.php?lang=<?= $lang ?>" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    <?= htmlspecialchars($tr['back_to_dashboard']) ?>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 animate-in">

            <!-- ── Left Column (Task Details) ── -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Card: Basic Information -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                            <i data-lucide="file-text" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['task_details']) ?></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($tr['task_no']) ?>: <?= htmlspecialchars($task_data['task_no']) ?></p>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Title & Description -->
                        <div>
                            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['task_title']) ?></span>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-1">
                                <?= htmlspecialchars($task_data['task_title']) ?>
                            </h3>
                        </div>
                        
                        <div>
                            <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['description']) ?></span>
                            <div class="mt-2 text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                <?= htmlspecialchars($task_data['task_description']) ?>
                            </div>
                        </div>

                        <!-- Meta Fields Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['priority']) ?></span>
                                <div class="mt-1.5">
                                    <?php
                                    $p_classes = match($task_data['priority'] ?? 'Medium') {
                                        'Low' => 'badge-low',
                                        'Medium' => 'badge-medium',
                                        'High' => 'badge-high',
                                        'Critical' => 'badge-critical',
                                        default => 'badge-medium'
                                    };
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $p_classes ?>">
                                        <?= htmlspecialchars($task_data['priority'] ?? 'Medium') ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['category']) ?></span>
                                <div class="mt-1.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 mr-1.5 text-slate-400"></i>
                                        <?= htmlspecialchars($task_data['task_category'] ?: 'General') ?>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['created_by']) ?></span>
                                <p class="text-sm font-medium text-slate-800 dark:text-white mt-1">
                                    <?= htmlspecialchars($task_data['creator_name'] ?: 'System') ?>
                                </p>
                            </div>
                            
                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['assigned_to']) ?></span>
                                <p class="text-sm font-medium text-slate-800 dark:text-white mt-1">
                                    <?= htmlspecialchars($task_data['assignee_name'] ?: 'Unassigned') ?>
                                </p>
                            </div>

                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['target']) ?></span>
                                <p class="text-sm font-medium text-slate-800 dark:text-white mt-1">
                                    <?= htmlspecialchars($task_data['remarks'] ?: '—') ?>
                                </p>
                            </div>

                            <div>
                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['due_date']) ?></span>
                                <div class="mt-1 flex items-center gap-2">
                                    <?php
                                    $is_overdue = (strtotime($task_data['due_date']) < time() && $task_data['status'] !== 'Completed');
                                    $due_color = $is_overdue ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-slate-800 dark:text-white';
                                    ?>
                                    <i data-lucide="calendar" class="w-4 h-4 <?= $is_overdue ? 'text-red-500' : 'text-slate-400' ?>"></i>
                                    <span class="text-sm <?= $due_color ?>">
                                        <?= $task_data['due_date'] ? date('d M Y, h:i A', strtotime($task_data['due_date'])) : 'Not Specified' ?>
                                    </span>
                                    <?php if ($is_overdue): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 animate-pulse uppercase">
                                        <?= htmlspecialchars($tr['status_overdue']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stepper Timeline Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 overflow-hidden animate-in" style="animation-delay:0.06s">
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white mb-6">Task Timeline</h3>
                    <div class="relative flex items-center justify-between">
                        <!-- Connecting Line -->
                        <div class="absolute left-4 right-4 top-4 h-0.5 bg-slate-200 dark:bg-slate-700 -z-0"></div>
                        
                        <?php
                        $status = $task_data['status'];
                        $step1_active = true;
                        $step2_active = ($status !== 'Pending');
                        $step3_active = ($status === 'Accepted' || $status === 'Acknowledged' || $status === 'In Progress' || $status === 'Completed');
                        $step4_active = ($status === 'Completed' || $status === 'Rejected');
                        
                        $step4_label = ($status === 'Rejected') ? $tr['status_rejected'] : $tr['status_completed'];
                        $step4_color = ($status === 'Rejected') ? 'bg-red-500 ring-red-100 dark:ring-red-900/30' : 'bg-govgreen-500 ring-green-100 dark:ring-green-900/30';
                        $step4_text_color = ($status === 'Rejected') ? 'text-red-500' : 'text-govgreen-500';
                        $step4_icon = ($status === 'Rejected') ? 'x' : 'check';

                        $t_tips = [
                            'en' => [
                                'assigned' => 'Click to revert task status to Pending',
                                'accepted' => 'Click to Accept this task',
                                'in_progress' => 'Click to set task status to In Progress',
                                'completed' => 'Click to mark task as Completed'
                            ],
                            'mr' => [
                                'assigned' => 'कामाची स्थिती प्रलंबित वर परत आणण्यासाठी क्लिक करा',
                                'accepted' => 'काम स्वीकारण्यासाठी क्लिक करा',
                                'in_progress' => 'काम प्रगतीपथावर म्हणून चिन्हांकित करण्यासाठी क्लिक करा',
                                'completed' => 'काम पूर्ण म्हणून चिन्हांकित करण्यासाठी क्लिक करा'
                            ]
                        ][$lang];

                        // Determine interactivity for assigned officer
                        $step1_click = ($is_assignee && $status !== 'Pending') ? 'onclick="submitTimelineAction(\'pending\')"' : '';
                        $step1_classes = ($is_assignee && $status !== 'Pending') ? 'cursor-pointer hover:scale-110 active:scale-95 transition-all duration-200' : '';
                        $step1_title = ($is_assignee && $status !== 'Pending') ? $t_tips['assigned'] : '';

                        $step2_click = ($is_assignee && $status === 'Pending') ? 'onclick="submitTimelineAction(\'accept\')"' : '';
                        $step2_classes = ($is_assignee && $status === 'Pending') ? 'cursor-pointer hover:scale-110 active:scale-95 transition-all duration-200' : '';
                        $step2_title = ($is_assignee && $status === 'Pending') ? $t_tips['accepted'] : '';

                        $step3_click = ($is_assignee && ($status === 'Accepted' || $status === 'Acknowledged' || $status === 'Pending')) ? 'onclick="submitTimelineAction(\'in_progress\')"' : '';
                        $step3_classes = ($is_assignee && ($status === 'Accepted' || $status === 'Acknowledged' || $status === 'Pending')) ? 'cursor-pointer hover:scale-110 active:scale-95 transition-all duration-200' : '';
                        $step3_title = ($is_assignee && ($status === 'Accepted' || $status === 'Acknowledged' || $status === 'Pending')) ? $t_tips['in_progress'] : '';

                        $step4_click = ($is_assignee && $status !== 'Completed' && $status !== 'Rejected') ? 'onclick="submitTimelineAction(\'complete\')"' : '';
                        $step4_classes = ($is_assignee && $status !== 'Completed' && $status !== 'Rejected') ? 'cursor-pointer hover:scale-110 active:scale-95 transition-all duration-200' : '';
                        $step4_title = ($is_assignee && $status !== 'Completed' && $status !== 'Rejected') ? $t_tips['completed'] : '';
                        ?>

                        <!-- Step 1: Assigned -->
                        <div class="relative z-10 flex flex-col items-center <?= $step1_classes ?>" <?= $step1_click ?> title="<?= htmlspecialchars($step1_title) ?>">
                            <div class="w-9 h-9 rounded-full bg-navy-600 text-white flex items-center justify-center shadow-md ring-4 ring-navy-50 dark:ring-slate-900">
                                <i data-lucide="user-plus" class="w-4.5 h-4.5"></i>
                            </div>
                            <span class="text-[11px] font-semibold text-navy-700 dark:text-blue-300 mt-2">Assigned</span>
                        </div>

                        <!-- Step 2: Responded -->
                        <div class="relative z-10 flex flex-col items-center <?= $step2_classes ?>" <?= $step2_click ?> title="<?= htmlspecialchars($step2_title) ?>">
                            <div class="w-9 h-9 rounded-full <?= $step2_active ? 'bg-navy-600 text-white ring-navy-50' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 ring-slate-100 dark:ring-slate-900' ?> flex items-center justify-center shadow ring-4">
                                <i data-lucide="message-square-text" class="w-4.5 h-4.5"></i>
                            </div>
                            <span class="text-[11px] font-semibold <?= $step2_active ? 'text-navy-700 dark:text-blue-300' : 'text-slate-400' ?> mt-2">
                                Responded
                            </span>
                        </div>

                        <!-- Step 3: Work In Progress -->
                        <div class="relative z-10 flex flex-col items-center <?= $step3_classes ?>" <?= $step3_click ?> title="<?= htmlspecialchars($step3_title) ?>">
                            <div class="w-9 h-9 rounded-full <?= $step3_active ? 'bg-navy-600 text-white ring-navy-50' : 'bg-slate-200 dark:bg-slate-700 text-slate-400 ring-slate-100 dark:ring-slate-900' ?> flex items-center justify-center shadow ring-4">
                                <i data-lucide="activity" class="w-4.5 h-4.5"></i>
                            </div>
                            <span class="text-[11px] font-semibold <?= $step3_active ? 'text-navy-700 dark:text-blue-300' : 'text-slate-400' ?> mt-2">
                                In Progress
                            </span>
                        </div>

                        <!-- Step 4: Finished -->
                        <div class="relative z-10 flex flex-col items-center <?= $step4_classes ?>" <?= $step4_click ?> title="<?= htmlspecialchars($step4_title) ?>">
                            <div class="w-9 h-9 rounded-full <?= $step4_active ? "$step4_color ring-4" : 'bg-slate-200 dark:bg-slate-700 text-slate-400 ring-slate-100 dark:ring-slate-900' ?> flex items-center justify-center shadow ring-4">
                                <i data-lucide="<?= $step4_icon ?>" class="w-4.5 h-4.5"></i>
                            </div>
                            <span class="text-[11px] font-semibold <?= $step4_active ? $step4_text_color : 'text-slate-400' ?> mt-2">
                                <?= $step4_label ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Hidden form for timeline quick status submissions -->
                <form id="timelineActionForm" method="POST" style="display:none;">
                    <input type="hidden" name="response_task_id" value="<?= $task_id ?>">
                    <input type="hidden" name="task_response_action" id="timeline_action_input" value="">
                </form>

                <!-- Card: Attachments -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.12s">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                            <i data-lucide="paperclip" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['attachments']) ?></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Supporting task documents</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if (empty($attachments)): ?>
                        <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-sm">
                            <i data-lucide="folder-open" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                            <?= htmlspecialchars($tr['no_attachments']) ?>
                        </div>
                        <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <?php foreach ($attachments as $doc): 
                                $fext = strtolower(pathinfo($doc['original_name'], PATHINFO_EXTENSION));
                                $ftype = match($fext) {
                                    'pdf' => ['icon' => 'file-text', 'bg' => 'bg-red-50 dark:bg-red-950/30', 'col' => 'text-red-500'],
                                    'jpg', 'jpeg', 'png' => ['icon' => 'image', 'bg' => 'bg-green-50 dark:bg-green-950/30', 'col' => 'text-green-500'],
                                    default => ['icon' => 'file', 'bg' => 'bg-slate-50 dark:bg-slate-900', 'col' => 'text-slate-500']
                                };
                            ?>
                            <div class="flex items-center justify-between p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 animate-in">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 <?= $ftype['bg'] ?>">
                                        <i data-lucide="<?= $ftype['icon'] ?>" class="w-4.5 h-4.5 <?= $ftype['col'] ?>"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-800 dark:text-white truncate" title="<?= htmlspecialchars($doc['original_name']) ?>">
                                            <?= htmlspecialchars($doc['original_name']) ?>
                                        </p>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            <?= number_format($doc['file_size'] / 1024, 1) ?> KB
                                        </p>
                                    </div>
                                </div>
                                <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank"
                                   class="ml-3 p-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-navy-600 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
                                   title="<?= htmlspecialchars($tr['view_attachment']) ?>">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- end left column -->

            <!-- ── Right Column (Status & Response Panel) ── -->
            <div class="space-y-6">

                <!-- Card: Current Status -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.05s">
                    <div class="p-6 flex flex-col items-center text-center">
                        <?php
                        $st_icon = 'clock';
                        $st_color = 'text-yellow-500';
                        $st_bg = 'bg-yellow-50 dark:bg-yellow-950/30';
                        $st_label = $tr['status_pending'];
                        
                        switch ($task_data['status']) {
                            case 'Accepted':
                                $st_icon = 'check-circle';
                                $st_color = 'text-blue-500';
                                $st_bg = 'bg-blue-50 dark:bg-blue-950/30';
                                $st_label = $tr['status_accepted'];
                                break;
                            case 'Acknowledged':
                                $st_icon = 'clipboard-check';
                                $st_color = 'text-indigo-500';
                                $st_bg = 'bg-indigo-50 dark:bg-indigo-950/30';
                                $st_label = $tr['status_acknowledged'];
                                break;
                            case 'Rejected':
                                $st_icon = 'alert-octagon';
                                $st_color = 'text-red-500';
                                $st_bg = 'bg-red-50 dark:bg-red-950/30';
                                $st_label = $tr['status_rejected'];
                                break;
                            case 'Completed':
                                $st_icon = 'award';
                                $st_color = 'text-green-500';
                                $st_bg = 'bg-green-50 dark:bg-green-950/30';
                                $st_label = $tr['status_completed'];
                                break;
                            case 'Overdue':
                                $st_icon = 'alert-triangle';
                                $st_color = 'text-rose-500';
                                $st_bg = 'bg-rose-50 dark:bg-rose-950/30';
                                $st_label = $tr['status_overdue'];
                                break;
                            case 'In Progress':
                                $st_icon = 'activity';
                                $st_color = 'text-blue-500';
                                $st_bg = 'bg-blue-50 dark:bg-blue-950/30';
                                $st_label = $tr['status_in_progress'];
                                break;
                        }
                        ?>
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-3 <?= $st_bg ?>">
                            <i data-lucide="<?= $st_icon ?>" class="w-8 h-8 <?= $st_color ?>"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($tr['status']) ?></span>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white mt-1"><?= htmlspecialchars($st_label) ?></h3>
                    </div>
                </div>

                <!-- Card: Response Panel / Actions -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.10s">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                        <div class="w-8 h-8 rounded-lg bg-navy-50 dark:bg-navy-900/30 flex items-center justify-center">
                            <i data-lucide="message-square-text" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['action_panel']) ?></h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Rights Panel</p>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if ($is_assignee): ?>
                            <?php if ($user_assignment['status'] === 'Pending'): ?>
                                <!-- RESPONSE FORM -->
                                <form method="POST" enctype="multipart/form-data" id="responseForm" class="space-y-4" novalidate>
                                    <input type="hidden" name="response_task_id" value="<?= $task_id ?>">
                                    <input type="hidden" name="task_response_action" id="task_response_action" value="">
                                    
                                    <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($tr['action_desc']) ?></p>
                                    
                                    <div class="space-y-2">
                                        <!-- Accept Option -->
                                        <button type="button" onclick="selectResponseAction('accept')" id="btn-action-accept"
                                                class="w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-navy-500 dark:hover:border-navy-400 transition-all text-left">
                                            <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <i data-lucide="check" class="w-4.5 h-4.5 text-blue-600 dark:text-blue-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['btn_accept']) ?></p>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($tr['action_accept_desc']) ?></p>
                                            </div>
                                        </button>
                                        
                                        <!-- Acknowledge Option -->
                                        <button type="button" onclick="selectResponseAction('acknowledge')" id="btn-action-acknowledge"
                                                class="w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-saffron-500 dark:hover:border-saffron-400 transition-all text-left">
                                            <div class="w-8 h-8 rounded-full bg-saffron-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <i data-lucide="bell" class="w-4.5 h-4.5 text-saffron-600 dark:text-orange-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['btn_acknowledge']) ?></p>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($tr['action_acknowledge_desc']) ?></p>
                                            </div>
                                        </button>
                                        
                                        <!-- Reject Option -->
                                        <button type="button" onclick="selectResponseAction('reject')" id="btn-action-reject"
                                                class="w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-red-500 dark:hover:border-red-400 transition-all text-left">
                                            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <i data-lucide="x" class="w-4.5 h-4.5 text-red-600 dark:text-red-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['btn_reject']) ?></p>
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5"><?= htmlspecialchars($tr['action_reject_desc']) ?></p>
                                            </div>
                                        </button>
                                    </div>
                                    
                                    <!-- Conditional Rejection Fields -->
                                    <div id="rejectionFields" class="hidden space-y-4 pt-3 border-t border-slate-100 dark:border-slate-700 animate-in">
                                        <div>
                                            <label class="form-label" for="rejection_reason">
                                                <?= htmlspecialchars($tr['label_reject_reason']) ?> <span class="text-red-500">*</span>
                                            </label>
                                            <textarea id="rejection_reason" name="rejection_reason" rows="3"
                                                      placeholder="<?= htmlspecialchars($tr['placeholder_reject_reason']) ?>"
                                                      class="w-full px-3 py-2.5 text-xs border border-slate-300 dark:border-slate-600 rounded-lg
                                                             bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                             focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                             transition-colors resize-none"></textarea>
                                        </div>
                                        
                                        <div>
                                            <label class="form-label">
                                                <?= htmlspecialchars($tr['label_reject_attachment']) ?> <span class="text-red-500">*</span>
                                            </label>
                                            <div id="rejectDropZone"
                                                 class="rounded-xl p-6 flex flex-col items-center justify-center text-center cursor-pointer border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-red-500 transition-all bg-slate-50/50 dark:bg-slate-900/30"
                                                 onclick="document.getElementById('rejection_attachment').click()">
                                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-2 mx-auto">
                                                    <i data-lucide="upload-cloud" class="w-5 h-5 text-slate-400"></i>
                                                </div>
                                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                    Drop file here or <span class="text-navy-600 dark:text-blue-400 underline">browse</span>
                                                </p>
                                                <p class="text-[10px] text-slate-400 mt-1"><?= htmlspecialchars($tr['desc_reject_attachment']) ?></p>
                                                <input type="file" id="rejection_attachment" name="rejection_attachment"
                                                       class="hidden" accept=".pdf,.jpg,.jpeg">
                                            </div>
                                            
                                            <!-- File Preview -->
                                            <div id="rejectFilePreview" class="hidden mt-3">
                                                <div class="flex items-center gap-3 p-2 bg-slate-50/50 dark:bg-slate-900/30 rounded-xl border border-slate-200 dark:border-slate-700">
                                                    <div class="w-8 h-8 rounded bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                                                        <i data-lucide="file-text" class="w-4 h-4 text-red-500"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p id="rejectFileName" class="text-xs font-medium text-slate-800 dark:text-white truncate"></p>
                                                        <p id="rejectFileSize" class="text-[10px] text-slate-400"></p>
                                                    </div>
                                                    <button type="button" onclick="clearRejectFile()" class="text-slate-400 hover:text-red-500 transition-colors">
                                                        <i data-lucide="x-circle" class="w-4.5 h-4.5"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <button type="submit" id="responseSubmitBtn"
                                            class="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-xl
                                                   bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600
                                                   text-white text-xs font-bold shadow-md hover:scale-[1.01] active:scale-[0.99]
                                                   focus:outline-none transition-all duration-200">
                                        <i data-lucide="send" class="w-4 h-4"></i>
                                        <span><?= htmlspecialchars($tr['btn_submit_response']) ?></span>
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- ALREADY RESPONDED STATE -->
                                <div class="text-center py-4 space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center mx-auto border border-slate-200 dark:border-slate-700">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 text-govgreen-500"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($tr['msg_already_responded']) ?></p>
                                        <p class="text-[11px] text-slate-400 mt-1">Status: <strong class="<?= $st_color ?>"><?= htmlspecialchars($st_label) ?></strong></p>
                                    </div>
                                    
                                    <?php if ($user_assignment['status'] === 'Rejected'): ?>
                                        <div class="text-left p-3.5 rounded-xl border border-red-200 dark:border-red-800/40 bg-red-50/20 dark:bg-red-950/10 mt-4 space-y-2 text-xs">
                                            <span class="font-bold text-red-700 dark:text-red-400"><?= htmlspecialchars($tr['rejection_detail']) ?>:</span>
                                            <p class="text-slate-700 dark:text-slate-300 mt-1 whitespace-pre-line"><?= htmlspecialchars($task_data['remarks']) ?></p>
                                            
                                            <?php
                                            $reject_doc = null;
                                            foreach ($attachments as $doc) {
                                                if ($doc['uploaded_by'] == $user_id && str_contains($doc['file_path'], 'REJECT_')) {
                                                    $reject_doc = $doc;
                                                    break;
                                                }
                                            }
                                            if ($reject_doc):
                                            ?>
                                            <div class="pt-2 mt-2 border-t border-red-100 dark:border-red-900/20">
                                                <a href="<?= htmlspecialchars($reject_doc['file_path']) ?>" target="_blank"
                                                   class="inline-flex items-center gap-1.5 text-red-600 dark:text-red-400 hover:underline">
                                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                                    <?= htmlspecialchars($reject_doc['original_name']) ?>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- NOT THE ASSIGNEE -->
                            <div class="text-center py-6 text-slate-400 dark:text-slate-500 text-xs">
                                <i data-lucide="shield-alert" class="w-8 h-8 mx-auto mb-2 opacity-50 text-slate-400"></i>
                                <?= htmlspecialchars($tr['msg_not_assignee']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- end right column -->

        </div>
        <?php endif; ?>

    </main>

</div><!-- end main wrapper -->

<!-- AI Chatbot FAB -->
<div class="fixed bottom-6 right-6 z-50">
    <button class="w-14 h-14 bg-gradient-to-r from-navy-600 to-navy-500 rounded-full shadow-lg flex items-center justify-center text-white hover:scale-105 transition-transform shadow-navy-500/30">
        <i data-lucide="message-square-text" class="w-6 h-6"></i>
    </button>
</div>

<!-- Toast Notification -->
<div id="toast" class="hidden fixed bottom-24 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium text-slate-800 dark:text-white max-w-xs">
    <i data-lucide="info" class="w-4 h-4 text-navy-600 dark:text-blue-400 flex-shrink-0" id="toastIcon"></i>
    <span id="toastMsg">Message here</span>
</div>

<!-- JAVASCRIPT -->
<script>
    const currentLang = '<?= $lang ?>';
    lucide.createIcons();

    // Theme Switcher
    const themeToggle = document.getElementById('themeToggle');
    const htmlEl = document.documentElement;
    themeToggle.addEventListener('click', () => {
        const isDark = htmlEl.classList.toggle('dark');
        localStorage.setItem('acTheme', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        lucide.createIcons();
    });

    // Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    sidebarToggle.addEventListener('click', () => {
        sidebar.style.display = (sidebar.style.display === 'none') ? 'flex' : 'none';
    });

    const fileTypeMap = {
        pdf:  { icon: 'file-text',  bg: 'bg-red-100 dark:bg-red-900/30',    color: 'text-red-500' },
        doc:  { icon: 'file-type',  bg: 'bg-blue-100 dark:bg-blue-900/30',  color: 'text-blue-500' },
        docx: { icon: 'file-type',  bg: 'bg-blue-100 dark:bg-blue-900/30',  color: 'text-blue-500' },
        jpg:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        jpeg: { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        png:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        gif:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' }
    };

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    // Response Actions logic
    const responseActionInput = document.getElementById('task_response_action');
    const rejectionFields = document.getElementById('rejectionFields');
    
    window.selectResponseAction = function(action) {
        if (!responseActionInput) return;
        responseActionInput.value = action;
        
        const acceptBtn = document.getElementById('btn-action-accept');
        const ackBtn = document.getElementById('btn-action-acknowledge');
        const rejectBtn = document.getElementById('btn-action-reject');
        
        acceptBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-navy-500 dark:hover:border-navy-400 transition-all text-left';
        ackBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-saffron-500 dark:hover:border-saffron-400 transition-all text-left';
        rejectBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-red-500 dark:hover:border-red-400 transition-all text-left';
        
        if (action === 'accept') {
            acceptBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-navy-500 dark:border-navy-400 bg-navy-50/50 dark:bg-navy-950/20 text-left';
            rejectionFields.classList.add('hidden');
        } else if (action === 'acknowledge') {
            ackBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-saffron-500 dark:border-orange-500 bg-saffron-50/30 dark:bg-orange-950/10 text-left';
            rejectionFields.classList.add('hidden');
        } else if (action === 'reject') {
            rejectBtn.className = 'w-full flex items-start gap-3 p-3 rounded-xl border-2 border-red-500 dark:border-red-400 bg-red-50/30 dark:bg-red-950/10 text-left';
            rejectionFields.classList.remove('hidden');
        }
        
        lucide.createIcons();
    };

    window.submitTimelineAction = function(action) {
        const msg = currentLang === 'mr' ? 'तुम्ही नक्की कामाची स्थिती अपडेट करू इच्छिता?' : 'Are you sure you want to update the task status?';
        if (confirm(msg)) {
            const form = document.getElementById('timelineActionForm');
            const input = document.getElementById('timeline_action_input');
            if (form && input) {
                input.value = action;
                form.submit();
            }
        }
    };

    // Rejection File upload
    const rejectDropZone = document.getElementById('rejectDropZone');
    const rejectFileInput = document.getElementById('rejection_attachment');
    const rejectFilePreview = document.getElementById('rejectFilePreview');
    const rejectFileName = document.getElementById('rejectFileName');
    const rejectFileSize = document.getElementById('rejectFileSize');

    if (rejectDropZone) {
        rejectFileInput.addEventListener('change', () => {
            if (rejectFileInput.files[0]) showRejectFilePreview(rejectFileInput.files[0]);
        });
        
        rejectDropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            rejectDropZone.classList.add('border-red-500', 'bg-red-50/20');
        });
        rejectDropZone.addEventListener('dragleave', () => {
            rejectDropZone.classList.remove('border-red-500', 'bg-red-50/20');
        });
        rejectDropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            rejectDropZone.classList.remove('border-red-500', 'bg-red-50/20');
            const dt = e.dataTransfer;
            if (dt.files.length) {
                const transfer = new DataTransfer();
                transfer.items.add(dt.files[0]);
                rejectFileInput.files = transfer.files;
                showRejectFilePreview(dt.files[0]);
            }
        });
    }

    function showRejectFilePreview(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (['pdf', 'jpg', 'jpeg'].includes(ext)) {
            rejectFileName.textContent = file.name;
            rejectFileSize.textContent = formatBytes(file.size);
            rejectFilePreview.classList.remove('hidden');
        } else {
            showToast('Only PDF and JPEG formats are allowed.', 'warning');
            clearRejectFile();
        }
    }

    window.clearRejectFile = function() {
        if (rejectFileInput) rejectFileInput.value = '';
        if (rejectFilePreview) rejectFilePreview.classList.add('hidden');
    };

    // Form Validation on Response
    const responseForm = document.getElementById('responseForm');
    if (responseForm) {
        responseForm.addEventListener('submit', function (e) {
            const action = responseActionInput.value;
            if (!action) {
                showToast('Please select an action: Accept, Acknowledge, or Reject.', 'warning');
                e.preventDefault();
                return;
            }
            if (action === 'reject') {
                const reason = document.getElementById('rejection_reason').value.trim();
                if (!reason) {
                    showToast('Reason for rejection is required.', 'warning');
                    e.preventDefault();
                    return;
                }
                if (!rejectFileInput.files.length) {
                    showToast('A mandatory rejection document attachment (PDF or JPEG) is required.', 'warning');
                    e.preventDefault();
                    return;
                }
            }
            
            const btn = document.getElementById('responseSubmitBtn');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
                btn.querySelector('span').textContent = 'Submitting...';
            }
        });
    }

    // Toast Notification
    function showToast(msg, type = 'info') {
        const toast = document.getElementById('toast');
        const iconEl = document.getElementById('toastIcon');
        document.getElementById('toastMsg').textContent = msg;
        const iconName = type === 'warning' ? 'alert-triangle' : 'check-circle';
        iconEl.setAttribute('data-lucide', iconName);
        lucide.createIcons();
        toast.classList.remove('hidden');
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.classList.add('hidden'), 400);
        }, 3500);
    }

    // Profile Dropdown Toggle
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    if (profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => {
            profileMenu.classList.add('hidden');
        });
    }

    <?php if ($success_msg): ?>
    showToast('<?= addslashes(strip_tags($success_msg)) ?>', 'success');
    <?php endif; ?>
</script>

</body>
</html>
