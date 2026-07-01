<?php
/**
 * =============================================================
 *  reports.php  |  Amravati Connect – Task Reports & Analytics
 * =============================================================
 *  Creates a comprehensive task report allowing users to:
 *    - View Allocated and Assigned tasks.
 *    - Take action: Acknowledge, Reject (withReason + Attachment), Complete (Achievement base).
 *    - Export to Print, PDF, and Excel.
 *  Handles DB connection loss gracefully using robust mock fallbacks.
 *  Updates status dynamically in runtime via asynchronous AJAX.
 * =============================================================
 */

session_start();

// Enable exception reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';
$translations = [
    'en' => [
        'title' => 'Reports & Analytics — Amravati Connect',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_task_alloc' => 'Task Allocation',
        'menu_announcements' => 'Announcements',
        'menu_announcement_center' => 'Announcement Center',
        'menu_notifications' => 'Notification Center',
        'menu_appreciation' => 'Appreciation',
        'menu_analytics' => 'Analytics & Data',
        'menu_reports' => 'Reports & Analytics',
        'menu_gis' => 'Performance Report',
        'menu_docs' => 'Document Management',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'menu_hierarchy' => 'Location Hierarchy',
        'menu_audit' => 'Audit Logs',
        'menu_settings' => 'Settings',
        'menu_logout' => 'Logout',
        'btn_ask_ai' => 'Ask Amravati AI',
        'page_title' => 'Reports & Analytics',
        'page_subtitle' => 'Monitor and execute task assignments, rejections, completions, and performance reports.',
        'btn_print' => 'Print Report',
        'btn_pdf' => 'Export PDF',
        'btn_excel' => 'Export Excel',
        'tab_assigned' => 'Assigned to Me',
        'tab_allocated' => 'Allocated by Me',
        'tab_tracking' => 'Task Tracking',
        'kpi_total' => 'Total Tasks',
        'kpi_pending' => 'Pending Acknowledge',
        'kpi_in_progress' => 'In Progress',
        'kpi_completed' => 'Completed',
        'kpi_rejected' => 'Rejected',
        'kpi_overdue' => 'Overdue / Escalated',
        'search_placeholder' => 'Search by Title, ID, or Description...',
        'filter_all_status' => 'All Statuses',
        'filter_all_priority' => 'All Priorities',
        'col_task_no' => 'Task No',
        'col_title' => 'Task Title & Category',
        'col_worker' => 'Assigned To',
        'col_creator' => 'Allocated By',
        'col_priority' => 'Priority',
        'col_status' => 'Status',
        'col_due' => 'Due Date',
        'col_actions' => 'Actions',
        'status_pending' => 'Pending',
        'status_in_progress' => 'In Progress',
        'status_completed' => 'Completed',
        'status_rejected' => 'Rejected',
        'status_overdue' => 'Overdue',
        'status_accepted' => 'Accepted',
        'status_verified' => 'Verified',
        'status_pending_verification' => 'Pending Verification',
        'status_approved_rejection' => 'Approved Rejection',
        'status_denied' => 'Denied',
        'status_reassigned' => 'Reassigned',
        'priority_critical' => 'Critical',
        'priority_high' => 'High',
        'priority_medium' => 'Medium',
        'priority_low' => 'Low',
        'btn_acknowledge' => 'Acknowledge',
        'btn_reject' => 'Reject',
        'btn_complete' => 'Complete',
        'btn_view' => 'View Details',
        'no_tasks' => 'No tasks matching the criteria found.',
        'lbl_reason' => 'Rejection Reason',
        'lbl_achievements' => 'Achievements & Outcome Details',
        'lbl_attachment' => 'Upload Supporting Document / Proof',
        'lbl_submit' => 'Submit',
        'lbl_cancel' => 'Cancel',
        'details_title' => 'Task Timeline & Remarks',
        'lbl_history' => 'Status History Logs',
        'lbl_remarks' => 'Activity Comments & Remarks',
        'lbl_documents' => 'Attached Files & Proofs'
    ],
    'mr' => [
        'title' => 'अहवाल आणि विश्लेषण — अमरावती कनेक्ट',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_task_alloc' => 'कार्य वाटप',
        'menu_announcements' => 'घोषणा',
        'menu_announcement_center' => 'घोषणा केंद्र',
        'menu_notifications' => 'सूचना केंद्र',
        'menu_appreciation' => 'कौतुक',
        'menu_analytics' => 'विश्लेषण आणि डेटा',
        'menu_reports' => 'अहवाल आणि विश्लेषण',
        'menu_gis' => 'जीआयएस नकाशा',
        'menu_docs' => 'दस्तऐवज व्यवस्थापन',
        'menu_admin' => 'प्रशासन',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'menu_hierarchy' => 'स्थान उतरंड',
        'menu_audit' => 'ऑडिट लॉग्स',
        'menu_settings' => 'सेटिंग्ज',
        'menu_logout' => 'लॉगआउट',
        'btn_ask_ai' => 'अमरावती एआय विचारा',
        'page_title' => 'कार्य अहवाल आणि विश्लेषण',
        'page_subtitle' => 'कार्य वाटप, नकार, पूर्णता आणि कामगिरी अहवालांचे निरीक्षण व अंमलबजावणी करा.',
        'btn_print' => 'अहवाल मुद्रित करा',
        'btn_pdf' => 'पीडीएफ निर्यात',
        'btn_excel' => 'एक्सेल निर्यात',
        'tab_assigned' => 'मला सोपवलेली कार्ये',
        'tab_allocated' => 'मी दिलेली कार्ये',
        'tab_tracking' => 'कार्य मागोवा',
        'kpi_total' => 'एकूण कार्ये',
        'kpi_pending' => 'स्वीकृती प्रलंबित',
        'kpi_in_progress' => 'प्रगतीपथावर',
        'kpi_completed' => 'पूर्ण झालेली',
        'kpi_rejected' => 'नाकारलेली',
        'kpi_overdue' => 'थकीत / गंभीर',
        'search_placeholder' => 'शीर्षक, आयडी किंवा वर्णनाद्वारे शोधा...',
        'filter_all_status' => 'सर्व स्थिती',
        'filter_all_priority' => 'सर्व प्राधान्यक्रम',
        'col_task_no' => 'कार्य क्र.',
        'col_title' => 'कार्याचे शीर्षक आणि विभाग',
        'col_worker' => 'नियुक्त अधिकारी',
        'col_creator' => 'वाटप करणारे',
        'col_priority' => 'प्राधान्य',
        'col_status' => 'स्थिती',
        'col_due' => 'नियत तारीख',
        'col_actions' => 'कृती',
        'status_pending' => 'प्रलंबित',
        'status_in_progress' => 'प्रगतीपथावर',
        'status_completed' => 'पूर्ण',
        'status_rejected' => 'नाकारलेले',
        'status_overdue' => 'थकीत',
        'status_accepted' => 'स्वीकृत',
        'status_verified' => 'सत्यापित',
        'status_pending_verification' => 'सत्यापनासाठी प्रलंबित',
        'status_approved_rejection' => 'मंजूर नाकारणे',
        'status_denied' => 'नाकारलेले',
        'status_reassigned' => 'पुन्हा नियुक्त केलेले',
        'priority_critical' => 'गंभीर',
        'priority_high' => 'उच्च',
        'priority_medium' => 'मध्यम',
        'priority_low' => 'कमी',
        'btn_acknowledge' => 'स्वीकारा',
        'btn_reject' => 'नाकारा',
        'btn_complete' => 'पूर्ण करा',
        'btn_view' => 'तपशील पहा',
        'no_tasks' => 'निकषांशी जुळणारे कोणतेही कार्य आढळले नाही.',
        'lbl_reason' => 'नाकारण्याचे कारण',
        'lbl_achievements' => 'कामगिरी आणि यशाचा तपशील',
        'lbl_attachment' => 'सहाय्यक दस्तऐवज / पुरावा अपलोड करा',
        'lbl_submit' => 'जतन करा',
        'lbl_cancel' => 'रद्द करा',
        'details_title' => 'कार्य टाइमलाइन आणि शेरे',
        'lbl_history' => 'स्थिती इतिहास लॉग',
        'lbl_remarks' => 'क्रियाकलाप टिप्पण्या आणि शेरे',
        'lbl_documents' => 'संलग्न फाइल्स आणि पुरावे'
    ]
];
$t = $translations[$lang];

// Database Connection
require_once 'include/dbConfig.php';
$db_connected = true;

/* Session details */
if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'];
    $_SESSION['user_village_id'] = $_SESSION['village_id'];
}
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'Collector';
    $_SESSION['user_name'] = 'Hon. Collector';
    $_SESSION['user_taluka_id'] = 1;
    $_SESSION['user_village_id'] = 1;
}

$userId     = (int)$_SESSION['user_id'];
$sRole      = $_SESSION['user_role'];
$sName      = $_SESSION['user_name'];
$sTalukaId  = (int)($_SESSION['user_taluka_id']  ?? 1);
$sVillageId = (int)($_SESSION['user_village_id'] ?? 1);

$isCollector = ($sRole === 'Collector' || $sRole === 'Administrator' || $sRole === 'System Administrator');
$isL1 = ($isCollector || $sRole === 'Additional Collector' || $sRole === 'Deputy Collector');

// ═══════════════════════════════════════════════════════════════════
// AJAX Endpoint: Fetch Task Timeline Details
// ═══════════════════════════════════════════════════════════════════
if (isset($_GET['ajax']) && $_GET['ajax'] === 'task_details' && isset($_GET['task_id'])) {
    header('Content-Type: application/json');
    $taskId = (int)$_GET['task_id'];
    
    if ($db_connected) {
        $taskRes = $conn->query("SELECT t.*, u.full_name AS creator_name, ur.full_name AS assignee_name FROM tasks t LEFT JOIN users u ON t.created_by = u.user_id LEFT JOIN users ur ON t.assigned_user_id = ur.user_id WHERE t.task_id = $taskId");
        $task = $taskRes ? $taskRes->fetch_assoc() : null;
        
        if (!$task) {
            echo json_encode(['status' => 'error', 'message' => 'Task not found']);
            exit;
        }
        
        $historyRes = $conn->query("SELECT h.*, u.full_name AS changer_name FROM task_status_history h LEFT JOIN users u ON h.changed_by = u.user_id WHERE h.task_id = $taskId ORDER BY h.change_date DESC");
        $history = [];
        while ($row = $historyRes->fetch_assoc()) $history[] = $row;
        
        $remarksRes = $conn->query("SELECT r.*, u.full_name AS user_name FROM task_remarks r LEFT JOIN users u ON r.user_id = u.user_id WHERE r.task_id = $taskId ORDER BY r.created_at DESC");
        $remarks = [];
        while ($row = $remarksRes->fetch_assoc()) $remarks[] = $row;
        
        $docsRes = $conn->query("SELECT d.*, u.full_name AS uploader_name FROM task_documents d LEFT JOIN users u ON d.uploaded_by = u.user_id WHERE d.task_id = $taskId ORDER BY d.uploaded_at DESC");
        $docs = [];
        while ($row = $docsRes->fetch_assoc()) $docs[] = $row;
        
        echo json_encode([
            'status' => 'success',
            'task' => $task,
            'history' => $history,
            'remarks' => $remarks,
            'documents' => $docs
        ]);
    } else {
        // Mock Detailed data
        echo json_encode([
            'status' => 'success',
            'task' => [
                'task_title' => 'Crop Damage Assessment Report',
                'task_no' => 'TSK-MOCK-2026',
                'task_description' => 'Determine crop loss percentage in Chandur block villages due to hailstorm.',
                'creator_name' => 'Sanjay Deshmukh',
                'assignee_name' => $sName,
                'due_date' => '2026-06-30'
            ],
            'history' => [
                ['new_status' => 'In Progress', 'old_status' => 'Pending', 'change_date' => '2026-06-22 10:15', 'changer_name' => $sName, 'remarks' => 'Acknowledged task'],
                ['new_status' => 'Pending', 'old_status' => 'Start', 'change_date' => '2026-06-21 09:30', 'changer_name' => 'Sanjay Deshmukh', 'remarks' => 'Task allocated']
            ],
            'remarks' => [
                ['user_name' => $sName, 'remark_text' => 'Initiated verification camp details collection.', 'created_at' => '2026-06-22 10:20']
            ],
            'documents' => [
                ['file_name' => 'Rainfall_Stats.pdf', 'file_path' => '#', 'uploader_name' => 'Sanjay Deshmukh']
            ]
        ]);
    }
    exit;
}

// Helper: Create notification & delivery log
function createReportNotification(
    mysqli $conn,
    int    $task_id,
    string $task_title,
    int    $receiver_id,
    int    $sender_id,
    string $notif_type,
    string $message_text
): array {
    $title   = $conn->real_escape_string($notif_type . ': ' . $task_title);
    $message = $conn->real_escape_string($message_text);
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

// ═══════════════════════════════════════════════════════════════════
// POST Form Handler: Asynchronous AJAX-driven status updates in runtime
// ═══════════════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $taskId = (int)$_POST['task_id'];

    if ($db_connected) {
        $taskRes = $conn->query("SELECT * FROM tasks WHERE task_id = $taskId");
        $task = $taskRes ? $taskRes->fetch_assoc() : null;

        if ($task) {
            $taskTitle = $task['task_title'];
            $creatorId = (int)$task['created_by'];
            $oldStatus = $task['status'];

            if ($action === 'acknowledge') {
                $conn->query("UPDATE tasks SET status = 'In Progress' WHERE task_id = $taskId");
                $conn->query("UPDATE task_assignments SET status = 'In Progress' WHERE task_id = $taskId AND assigned_to_user = $userId");
                
                $stmt = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'In Progress', ?, NOW(), 'Task acknowledged by employee')");
                $stmt->bind_param("isi", $taskId, $oldStatus, $userId);
                $stmt->execute();
                $stmt->close();

                createReportNotification($conn, $taskId, $taskTitle, $creatorId, $userId, 'Task Acknowledged', "$sName has acknowledged the task and is working on it.");

                echo json_encode(['status' => 'success', 'task_title' => $taskTitle]);
                exit;
            }
            elseif ($action === 'reject') {
                $reason = trim($_POST['reason'] ?? '');
                if (empty($reason)) {
                    echo json_encode(['status' => 'error', 'message' => 'Rejection reason is required.']);
                    exit;
                }

                $attachment_path = null;
                $file_name = null;
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/uploads/tasks/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    
                    $file_ext  = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $file_name = 'REJECT_' . strtoupper(uniqid()) . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $file_name)) {
                        $attachment_path = 'uploads/tasks/' . $file_name;
                    }
                }

                $conn->query("UPDATE tasks SET status = 'Rejected' WHERE task_id = $taskId");
                $conn->query("UPDATE task_assignments SET status = 'Rejected' WHERE task_id = $taskId AND assigned_to_user = $userId");
                
                $stmt = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Rejected', ?, NOW(), ?)");
                $stmt->bind_param("isis", $taskId, $oldStatus, $userId, $reason);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Rejected')");
                $stmt->bind_param("iis", $taskId, $userId, $reason);
                $stmt->execute();
                $stmt->close();

                if ($attachment_path && $file_name) {
                    $orig_name = $_FILES['attachment']['name'];
                    $stmt = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $taskId, $orig_name, $attachment_path, $userId);
                    $stmt->execute();
                    $stmt->close();
                }

                createReportNotification($conn, $taskId, $taskTitle, $creatorId, $userId, 'Task Rejected', "$sName has rejected the task. Reason: $reason");

                echo json_encode(['status' => 'success']);
                exit;
            }
            elseif ($action === 'complete') {
                $achievements = trim($_POST['achievements'] ?? '');
                if (empty($achievements)) {
                    echo json_encode(['status' => 'error', 'message' => 'Achievement details are required.']);
                    exit;
                }

                $attachment_path = null;
                $file_name = null;
                if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/uploads/tasks/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    
                    $file_ext  = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
                    $file_name = 'COMPLETE_' . strtoupper(uniqid()) . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $file_name)) {
                        $attachment_path = 'uploads/tasks/' . $file_name;
                    }
                }

                $conn->query("UPDATE tasks SET status = 'Completed', completion_date = NOW() WHERE task_id = $taskId");
                $conn->query("UPDATE task_assignments SET status = 'Completed' WHERE task_id = $taskId AND assigned_to_user = $userId");
                
                $stmt = $conn->prepare("INSERT INTO task_status_history (task_id, old_status, new_status, changed_by, change_date, remarks) VALUES (?, ?, 'Completed', ?, NOW(), ?)");
                $stmt->bind_param("isis", $taskId, $oldStatus, $userId, $achievements);
                $stmt->execute();
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark) VALUES (?, ?, ?, 'Completed')");
                $stmt->bind_param("iis", $taskId, $userId, $achievements);
                $stmt->execute();
                $stmt->close();

                if ($attachment_path && $file_name) {
                    $orig_name = $_FILES['attachment']['name'];
                    $stmt = $conn->prepare("INSERT INTO task_documents (task_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $taskId, $orig_name, $attachment_path, $userId);
                    $stmt->execute();
                    $stmt->close();
                }

                createReportNotification($conn, $taskId, $taskTitle, $creatorId, $userId, 'Task Completed', "$sName has completed the task. Achievements: $achievements");

                echo json_encode(['status' => 'success']);
                exit;
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Task details not found.']);
            exit;
        }
    } else {
        // Handle mock actions
        echo json_encode(['status' => 'success', 'task_title' => 'Mock Task', 'demo' => true]);
        exit;
    }
}

// ═══════════════════════════════════════════════════════════════════
// Query Filters & Search parameters
// ═══════════════════════════════════════════════════════════════════
$search = $_GET['search'] ?? '';
$filterStatus = $_GET['status'] ?? 'All';
$filterPriority = $_GET['priority'] ?? 'All';
$filterUser = $_GET['filter_user'] ?? 'All';
$dateStart = $_GET['date_start'] ?? '';
$dateEnd = $_GET['date_end'] ?? '';
$activeTab = $_GET['tab'] ?? 'assigned'; // 'assigned' or 'allocated' or 'tracking'

$assignedTasks = [];
$allocatedTasks = [];
$trackingTasks = [];
$usersList = [];

// Determine level
$level = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

if ($db_connected) {
    // Fetch users for filter dropdown
    $userRes = $conn->query("SELECT user_id, full_name, employee_code FROM users ORDER BY full_name ASC");
    if ($userRes) {
        while ($uRow = $userRes->fetch_assoc()) {
            $usersList[] = $uRow;
        }
    }

    $whereAssigned = "WHERE (t.assigned_user_id = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_to_user = $userId))";
    $whereAllocated = "WHERE (t.created_by = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_from_user = $userId))";
    
    // Tracking scope where clause
    if ($level === 2) {
        $whereTracking = "WHERE (t.taluka_id = $sTalukaId OR creator.taluka_id = $sTalukaId)";
    } elseif ($level === 3) {
        $whereTracking = "WHERE (t.village_id = $sVillageId OR creator.village_id = $sVillageId)";
    } else {
        $whereTracking = "WHERE 1=1";
    }

    if (!empty($search)) {
        $searchEsc = $conn->real_escape_string($search);
        $searchCond = " AND (t.task_title LIKE '%$searchEsc%' OR t.task_no LIKE '%$searchEsc%' OR t.task_description LIKE '%$searchEsc%')";
        $whereAssigned .= $searchCond;
        $whereAllocated .= $searchCond;
        $whereTracking .= $searchCond;
    }

    if ($filterStatus !== 'All') {
        $statusEsc = $conn->real_escape_string($filterStatus);
        if ($statusEsc === 'Overdue') {
            $overdueCond = " AND t.due_date < CURDATE() AND t.status != 'Completed'";
            $whereAssigned .= $overdueCond;
            $whereAllocated .= $overdueCond;
            $whereTracking .= $overdueCond;
        } else {
            $whereAssigned .= " AND t.status = '$statusEsc'";
            $whereAllocated .= " AND t.status = '$statusEsc'";
            $whereTracking .= " AND t.status = '$statusEsc'";
        }
    }

    if ($filterPriority !== 'All') {
        $priorityEsc = $conn->real_escape_string($filterPriority);
        $whereAssigned .= " AND t.priority = '$priorityEsc'";
        $whereAllocated .= " AND t.priority = '$priorityEsc'";
        $whereTracking .= " AND t.priority = '$priorityEsc'";
    }

    if ($filterUser !== 'All') {
        $userFilterId = (int)$filterUser;
        $whereAssigned .= " AND (t.created_by = $userFilterId)";
        $whereAllocated .= " AND (t.assigned_user_id = $userFilterId OR t.task_id IN (SELECT ta3.task_id FROM task_assignments ta3 WHERE ta3.assigned_to_user = $userFilterId))";
        $whereTracking .= " AND (t.created_by = $userFilterId OR t.assigned_user_id = $userFilterId OR t.task_id IN (SELECT ta3.task_id FROM task_assignments ta3 WHERE ta3.assigned_to_user = $userFilterId))";
    }

    if (!empty($dateStart)) {
        $dateStartEsc = $conn->real_escape_string($dateStart);
        $whereAssigned .= " AND t.created_at >= '$dateStartEsc 00:00:00'";
        $whereAllocated .= " AND t.created_at >= '$dateStartEsc 00:00:00'";
        $whereTracking .= " AND t.created_at >= '$dateStartEsc 00:00:00'";
    }

    if (!empty($dateEnd)) {
        $dateEndEsc = $conn->real_escape_string($dateEnd);
        $whereAssigned .= " AND t.created_at <= '$dateEndEsc 23:59:59'";
        $whereAllocated .= " AND t.created_at <= '$dateEndEsc 23:59:59'";
        $whereTracking .= " AND t.created_at <= '$dateEndEsc 23:59:59'";
    }

    $assignedRes = $conn->query("SELECT t.*, u.full_name AS creator_name FROM tasks t LEFT JOIN users u ON t.created_by = u.user_id $whereAssigned ORDER BY t.created_at DESC");
    if ($assignedRes) {
        while ($row = $assignedRes->fetch_assoc()) $assignedTasks[] = $row;
    }

    $allocatedRes = $conn->query("SELECT t.*, u.full_name AS assignee_name, r.role_name AS assigned_role_name FROM tasks t LEFT JOIN users u ON t.assigned_user_id = u.user_id LEFT JOIN roles r ON t.assigned_role_id = r.role_id $whereAllocated ORDER BY t.created_at DESC");
    if ($allocatedRes) {
        while ($row = $allocatedRes->fetch_assoc()) $allocatedTasks[] = $row;
    }

    $trackingRes = $conn->query("
        SELECT 
            t.*, 
            creator.full_name AS creator_name,
            GROUP_CONCAT(DISTINCT COALESCE(assignee.full_name, assignee.employee_code, 'Unassigned') SEPARATOR ', ') AS assigned_to_name
        FROM tasks t
        LEFT JOIN users creator ON t.created_by = creator.user_id
        LEFT JOIN task_assignments ta ON t.task_id = ta.task_id
        LEFT JOIN users assignee ON ta.assigned_to_user = assignee.user_id
        $whereTracking
        GROUP BY t.task_id
        ORDER BY t.created_at DESC
    ");
    if ($trackingRes) {
        while ($row = $trackingRes->fetch_assoc()) $trackingTasks[] = $row;
    }
}

// ═══════════════════════════════════════════════════════════════════
// KPI Calculations for Active Tab
// ═══════════════════════════════════════════════════════════════════
$kpiAssigned = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];
$kpiAllocated = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];
$kpiTracking = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];

if ($db_connected) {
    $assignedKpiRes = $conn->query("SELECT t.status, t.due_date FROM tasks t WHERE t.assigned_user_id = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_to_user = $userId)");
    if ($assignedKpiRes) {
        while ($row = $assignedKpiRes->fetch_assoc()) {
            $kpiAssigned['total']++;
            $status = $row['status'];
            if ($status === 'Pending') $kpiAssigned['pending']++;
            elseif ($status === 'In Progress') $kpiAssigned['in_progress']++;
            elseif ($status === 'Completed') $kpiAssigned['completed']++;
            elseif ($status === 'Rejected') $kpiAssigned['rejected']++;
            
            if ($status !== 'Completed' && !empty($row['due_date']) && strtotime($row['due_date']) < time()) {
                $kpiAssigned['overdue']++;
            }
        }
    }

    $allocatedKpiRes = $conn->query("SELECT t.status, t.due_date FROM tasks t WHERE t.created_by = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_from_user = $userId)");
    if ($allocatedKpiRes) {
        while ($row = $allocatedKpiRes->fetch_assoc()) {
            $kpiAllocated['total']++;
            $status = $row['status'];
            if ($status === 'Pending') $kpiAllocated['pending']++;
            elseif ($status === 'In Progress') $kpiAllocated['in_progress']++;
            elseif ($status === 'Completed') $kpiAllocated['completed']++;
            elseif ($status === 'Rejected') $kpiAllocated['rejected']++;
            
            if ($status !== 'Completed' && !empty($row['due_date']) && strtotime($row['due_date']) < time()) {
                $kpiAllocated['overdue']++;
            }
        }
    }

    $whereTrackingKpi = "";
    if ($level === 2) {
        $whereTrackingKpi = "WHERE (t.taluka_id = $sTalukaId OR creator.taluka_id = $sTalukaId)";
    } elseif ($level === 3) {
        $whereTrackingKpi = "WHERE (t.village_id = $sVillageId OR creator.village_id = $sVillageId)";
    } else {
        $whereTrackingKpi = "WHERE 1=1";
    }
    $trackingKpiRes = $conn->query("SELECT t.status, t.due_date FROM tasks t LEFT JOIN users creator ON t.created_by = creator.user_id $whereTrackingKpi");
    if ($trackingKpiRes) {
        while ($row = $trackingKpiRes->fetch_assoc()) {
            $kpiTracking['total']++;
            $status = $row['status'];
            if ($status === 'Pending') $kpiTracking['pending']++;
            elseif ($status === 'In Progress') $kpiTracking['in_progress']++;
            elseif ($status === 'Completed') $kpiTracking['completed']++;
            elseif ($status === 'Rejected') $kpiTracking['rejected']++;
            
            if ($status !== 'Completed' && !empty($row['due_date']) && strtotime($row['due_date']) < time()) {
                $kpiTracking['overdue']++;
            }
        }
    }
} else {
    // Hardcoded stats based on Mock database lists
    $kpiAssigned = ['total' => 3, 'pending' => 1, 'in_progress' => 1, 'completed' => 1, 'rejected' => 0, 'overdue' => 1];
    $kpiAllocated = ['total' => 3, 'pending' => 1, 'in_progress' => 0, 'completed' => 1, 'rejected' => 1, 'overdue' => 1];
    $kpiTracking = ['total' => 2, 'pending' => 1, 'in_progress' => 1, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];
}

$currentKpis = match($activeTab) {
    'allocated' => $kpiAllocated,
    'tracking' => $kpiTracking,
    default => $kpiAssigned,
};

$all_users = [];
if ($db_connected) {
    $res = $conn->query("SELECT user_id, full_name, employee_code FROM users WHERE status = 'Active' ORDER BY full_name ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $all_users[] = $row;
        }
    }
}

/* User Badge configurations */
$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
$level    = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

function priorityTextCss(?string $priority): string {
    $p = $priority ? strtolower(trim($priority)) : '';
    return match($p) {
        'critical' => 'text-red-650 font-bold dark:text-red-400',
        'high' => 'text-orange-600 font-semibold dark:text-orange-400',
        'medium' => 'text-blue-600 font-medium dark:text-blue-400',
        'low' => 'text-green-600 dark:text-green-400',
        default => 'text-slate-600 dark:text-slate-400',
    };
}

function statusBadgeCss(?string $s): string {
    return match($s) {
        'Completed', 'Verified' => 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
        'Pending', 'Assigned', 'Reassigned' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800',
        'In Progress' => 'bg-amber-100 text-amber-800 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
        'Accepted' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800',
        'Rejected', 'Approved Rejection' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800',
        'Pending Verification' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
        'Overdue' => 'bg-red-100 text-red-850 border-red-200 dark:bg-red-900/30 dark:text-red-450 dark:border-red-800',
        default => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600',
    };
}

close_db_connection();
?>
<?php
$pageTitle = $t['title'] ?? 'Reports & Analytics';
$pageDesc = $t['page_subtitle'] ?? 'Monitor task assignments';
$extraHead = <<<'EOT'
    <!-- SheetJS for Excel Exports -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- html2pdf for PDF Exports -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        .kpi-card { transition: transform 0.2s, box-shadow 0.2s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -4px rgba(0,0,0,0.1); }

        .badge-l1 { background: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-l2 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-l3 { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .dark .badge-l1 { background: #1e3a8a33; color: #93c5fd; border-color: #1e40af; }
        .dark .badge-l2 { background: #92400e33; color: #fcd34d; border-color: #b45309; }
        .dark .badge-l3 { background: #065f4633; color: #6ee7b7; border-color: #047857; }

        /* ── Status Progress Bar ──────────────────────────── */
        .progress-step { flex:1; text-align:center; position:relative; }
        .progress-step::before {
            content:''; position:absolute; top:16px; left:-50%; right:50%;
            height:2px; background:#e2e8f0; z-index:0;
        }
        .dark .progress-step::before { background:#334155; }
        .progress-step:first-child::before { display:none; }
        .progress-step.active::before,
        .progress-step.done::before { background:#152b4a; }
        .dark .progress-step.active::before,
        .dark .progress-step.done::before { background:#60a5fa; }

        /* ── Timeline ─────────────────────────────── */
        .timeline-wrapper { position:relative; }
        .timeline-line {
            position:absolute; left:19px; top:44px; bottom:0;
            width:2px;
            background:linear-gradient(to bottom, #cbd5e1 0%, #cbd5e1 88%, transparent 100%);
        }
        .dark .timeline-line { background:linear-gradient(to bottom, #334155 0%, #334155 88%, transparent 100%); }

        .tl-node { position:relative; padding-left:56px; padding-bottom:32px; }
        .tl-node:last-child { padding-bottom:0; }

        .tl-dot {
            position:absolute; left:0; top:0;
            width:40px; height:40px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 0 0 4px #fff, 0 2px 10px rgba(0,0,0,.12);
            z-index:2;
            transition:transform .2s;
        }
        .dark .tl-dot { box-shadow:0 0 0 4px #0f172a, 0 2px 10px rgba(0,0,0,.3); }
        .tl-node:hover .tl-dot { transform:scale(1.08); }

        .tl-card {
            border-radius:14px;
            padding:16px 20px;
            position:relative;
            background:#fff;
            border:1px solid #e2e8f0;
            box-shadow:0 1px 4px rgba(0,0,0,.05);
            transition:box-shadow .2s, transform .2s;
        }
        .dark .tl-card { background:#1e293b; border-color:#334155; }
        .tl-card:hover { box-shadow:0 6px 24px rgba(0,0,0,.09); transform:translateY(-1px); }

        /* Change badge (old → new) */
        .change-badge {
            display:inline-flex; align-items:center; gap:6px;
            padding:3px 10px; border-radius:999px;
            font-size:11px; font-weight:700; font-family:monospace;
            background:rgba(99,102,241,.1); color:#4338ca;
            border:1px solid rgba(99,102,241,.2);
        }
        .dark .change-badge { background:rgba(99,102,241,.15); color:#a5b4fc; border-color:rgba(99,102,241,.3); }

        /* Animations */
        @keyframes fadeSlideIn { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
        .animate-in { animation:fadeSlideIn .35s ease both; }

        /* Print styles */
        @media print {
            aside, header, .no-print, button, .modal-backdrop, .tabs-nav {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                background: white !important;
                color: black !important;
            }
            .print-header {
                display: block !important;
            }
            table {
                width: 100% !important;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #94a3b8 !important;
                padding: 6px 10px !important;
                color: #000 !important;
            }
        }
    </style>
EOT;
include 'include/header.php';
$activePage = 'reports';
include 'include/sidebar.php';
?>

<!-- ═══════════════════════════════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0 no-print">
        <div class="flex items-center flex-1">
            <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none block lg:hidden" id="sidebarToggle">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            
            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm" aria-label="Breadcrumb">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['menu_reports']) ?></span>
            </nav>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Language Toggle -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'reports.php?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                      hover:bg-slate-100 dark:hover:bg-slate-800 px-2 sm:px-3 py-1.5 rounded-md
                      transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 sm:mr-2 text-slate-500"></i>
                <span class="hidden sm:inline"><?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?></span>
            </a>

            <!-- Theme Switcher -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>

            <!-- Notifications -->
            <?php include 'include/notification_widget.php'; ?>
                        <!-- Profile dropdown container -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName ?? 'User') ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($sRole ?? $roleLabel ?? 'Officer') ?>
                            <?= ' (' . htmlspecialchars($headerLocationDisplay) . ')' ?>
                        </span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                        <?= htmlspecialchars($initials ?? 'U') ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'User Profile Update' : 'वापरकर्ता प्रोफाइल अपडेट' ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Settings' : 'सेटिंग्ज' ?>
                        </a>
                        <a href="passwordChange.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i><?= ($lang ?? 'en') === 'en' ? 'Password Change' : 'पासवर्ड बदला' ?>
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i><?= ($lang ?? 'en') === 'en' ? 'Logout' : 'लॉगआउट' ?>
                        </a>
                    </div>
                </div>
            </div></div>
    </header>

    <!-- MAIN CONTENT SCROLL AREA -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- DB Status Banner for Mock demo (no-print) -->
        <?php if (!$db_connected): ?>
        <div class="mb-6 p-3.5 bg-saffron-50 dark:bg-saffron-950/20 border border-saffron-200 dark:border-saffron-900 text-saffron-700 dark:text-saffron-400 rounded-xl text-xs flex items-center gap-2.5 no-print">
            <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0"></i>
            <span>Database hosts are currently unreachable. Displaying cached reports and mock data analytics. Action executions and document downloads are simulated in runtime.</span>
        </div>
        <?php endif; ?>

        <!-- Printable Header (Only displayed during Print) -->
        <div class="hidden print-header mb-6">
            <h1 class="text-2xl font-bold text-center text-black">Amravati Connect - Task Allocation Report</h1>
            <p class="text-center text-sm text-slate-600">Report Type: <?= $activeTab === 'allocated' ? 'Allocated by Me' : ($activeTab === 'tracking' ? 'Task Tracking' : 'Assigned to Me') ?> | Date: <?= date('Y-m-d H:i') ?></p>
            <p class="text-center text-sm text-slate-600">User: <?= htmlspecialchars($sName) ?> (<?= htmlspecialchars($sRole) ?>)</p>
            <hr class="my-4 border-slate-300">
        </div>

        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4 no-print">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md">
                        <i data-lucide="pie-chart" class="w-5 h-5 text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
            </div>
            
            <!-- Export Options -->
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold badge-l<?= $level ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($sRole) ?> (L<?= $level ?>)
                </span>
                <button onclick="window.print()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-755 transition-colors">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    <?= htmlspecialchars($t['btn_print']) ?>
                </button>
                <button onclick="triggerPDFExport()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-755 transition-colors">
                    <i data-lucide="file-down" class="w-4 h-4 mr-2 text-red-500"></i>
                    <?= htmlspecialchars($t['btn_pdf']) ?>
                </button>
                <button onclick="triggerExcelExport()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-755 transition-colors">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2 text-govgreen-500"></i>
                    <?= htmlspecialchars($t['btn_excel']) ?>
                </button>
            </div>
        </div>

        <!-- Tabbed Navigation -->
        <div class="flex border-b border-slate-200 dark:border-slate-700 mb-6 tabs-nav no-print">
            <a href="reports.php?lang=<?= $lang ?>&tab=assigned" class="px-6 py-3 text-sm font-medium border-b-2 transition-all duration-150 <?= $activeTab === 'assigned' ? 'border-navy-600 text-navy-600 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200' ?>">
                <i data-lucide="user-check" class="inline-block w-4 h-4 mr-2 -mt-0.5"></i>
                <?= htmlspecialchars($t['tab_assigned']) ?> (<?= $kpiAssigned['total'] ?>)
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=allocated" class="px-6 py-3 text-sm font-medium border-b-2 transition-all duration-150 <?= $activeTab === 'allocated' ? 'border-navy-600 text-navy-600 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200' ?>">
                <i data-lucide="network" class="inline-block w-4 h-4 mr-2 -mt-0.5"></i>
                <?= htmlspecialchars($t['tab_allocated']) ?> (<?= $kpiAllocated['total'] ?>)
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=tracking" class="px-6 py-3 text-sm font-medium border-b-2 transition-all duration-150 <?= $activeTab === 'tracking' ? 'border-navy-600 text-navy-600 dark:border-blue-400 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 dark:text-slate-400 dark:hover:text-slate-200' ?>">
                <i data-lucide="line-chart" class="inline-block w-4 h-4 mr-2 -mt-0.5"></i>
                <?= htmlspecialchars($t['tab_tracking']) ?> (<?= $kpiTracking['total'] ?>)
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6 no-print">
            <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>&status=All" class="block kpi-card bg-gradient-to-br from-blue-50 to-white dark:from-slate-800 dark:to-slate-900 p-5 rounded-xl border-l-4 border-blue-500 shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_total']) ?></p>
                <p id="kpi-total" class="text-2xl font-bold mt-2 text-slate-800 dark:text-white"><?= $currentKpis['total'] ?></p>
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>&status=Pending" class="block kpi-card bg-gradient-to-br from-yellow-50 to-white dark:from-slate-800 dark:to-slate-900 p-5 rounded-xl border-l-4 border-yellow-500 shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_pending']) ?></p>
                <p id="kpi-pending" class="text-2xl font-bold mt-2 text-yellow-600 dark:text-yellow-400"><?= $currentKpis['pending'] ?></p>
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>&status=In+Progress" class="block kpi-card bg-gradient-to-br from-indigo-50 to-white dark:from-slate-800 dark:to-slate-900 p-5 rounded-xl border-l-4 border-indigo-500 shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_in_progress']) ?></p>
                <p id="kpi-in-progress" class="text-2xl font-bold mt-2 text-blue-600 dark:text-blue-400"><?= $currentKpis['in_progress'] ?></p>
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>&status=Completed" class="block kpi-card bg-gradient-to-br from-green-50 to-white dark:from-slate-800 dark:to-slate-900 p-5 rounded-xl border-l-4 border-govgreen-500 shadow-sm hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_completed']) ?></p>
                <p id="kpi-completed" class="text-2xl font-bold mt-2 text-govgreen-600 dark:text-govgreen-450"><?= $currentKpis['completed'] ?></p>
            </a>
            <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>&status=Overdue" class="block kpi-card bg-gradient-to-br from-red-50 to-white dark:from-slate-800 dark:to-slate-900 p-5 rounded-xl border-l-4 border-red-500 shadow-sm col-span-2 lg:col-span-1 hover:shadow-md hover:scale-[1.02] transition-all duration-200 cursor-pointer">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_overdue']) ?></p>
                <p id="kpi-overdue" class="text-2xl font-bold mt-2 text-red-600 dark:text-red-400"><?= $currentKpis['overdue'] ?></p>
            </a>
        </div>

        <!-- Filter Panel -->
        <div class="glass-panel p-5 rounded-xl shadow-official border border-slate-200/50 dark:border-slate-700/50 mb-6 no-print">
            <form method="GET" action="reports.php" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="lang" value="<?= htmlspecialchars($lang) ?>">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">

                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Search</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                        </span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" class="block w-full pl-10 pr-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-950 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-navy-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Filter Status</label>
                    <select name="status" class="block w-44 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-950 dark:text-white focus:outline-none">
                        <option value="All" <?= $filterStatus === 'All' ? 'selected' : '' ?>><?= htmlspecialchars($t['filter_all_status']) ?></option>
                        <option value="Pending" <?= $filterStatus === 'Pending' ? 'selected' : '' ?>><?= htmlspecialchars($t['status_pending']) ?></option>
                        <option value="In Progress" <?= $filterStatus === 'In Progress' ? 'selected' : '' ?>><?= htmlspecialchars($t['status_in_progress']) ?></option>
                        <option value="Completed" <?= $filterStatus === 'Completed' ? 'selected' : '' ?>><?= htmlspecialchars($t['status_completed']) ?></option>
                        <option value="Rejected" <?= $filterStatus === 'Rejected' ? 'selected' : '' ?>><?= htmlspecialchars($t['status_rejected']) ?></option>
                        <option value="Overdue" <?= $filterStatus === 'Overdue' ? 'selected' : '' ?>><?= htmlspecialchars($t['status_overdue']) ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Filter Priority</label>
                    <select name="priority" class="block w-44 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-955 dark:text-white focus:outline-none">
                        <option value="All" <?= $filterPriority === 'All' ? 'selected' : '' ?>><?= htmlspecialchars($t['filter_all_priority']) ?></option>
                        <option value="Critical" <?= $filterPriority === 'Critical' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_critical']) ?></option>
                        <option value="High" <?= $filterPriority === 'High' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_high']) ?></option>
                        <option value="Medium" <?= $filterPriority === 'Medium' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_medium']) ?></option>
                        <option value="Low" <?= $filterPriority === 'Low' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_low']) ?></option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">User/Officer</label>
                    <select name="filter_user" class="block w-44 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-955 dark:text-white focus:outline-none">
                        <option value="All" <?= $filterUser === 'All' ? 'selected' : '' ?>>All Officers</option>
                        <?php foreach ($usersList as $uRow): ?>
                            <option value="<?= $uRow['user_id'] ?>" <?= (string)$filterUser === (string)$uRow['user_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($uRow['full_name'] ?: $uRow['employee_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Start Date</label>
                    <input type="date" name="date_start" value="<?= htmlspecialchars($dateStart) ?>" class="block w-40 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-955 dark:text-white focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">End Date</label>
                    <input type="date" name="date_end" value="<?= htmlspecialchars($dateEnd) ?>" class="block w-40 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-955 dark:text-white focus:outline-none">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-navy-600 hover:bg-navy-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5 shadow-sm">
                        <i data-lucide="filter" class="w-4 h-4"></i> Apply
                    </button>
                    <a href="reports.php?lang=<?= $lang ?>&tab=<?= $activeTab ?>" class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-755 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium transition-colors">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Task Report Content Container (Captured by PDF exporter) -->
        <div id="report-container" class="glass-panel rounded-xl shadow-official border border-slate-200/50 dark:border-slate-700/50 overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table id="tasks-table" class="w-full min-w-[1000px] divide-y divide-slate-200 dark:divide-slate-700 table-fixed">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
                            <?php if ($filterStatus === 'Overdue'): ?>
                                <th class="w-[20%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Candidate Name & Role</th>
                                <th class="w-[10%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task ID</th>
                                <th class="w-[25%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task Name</th>
                                <th class="w-[12%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Date</th>
                                <th class="w-[12%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Due Date</th>
                                <th class="w-[21%] px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider no-print"><?= htmlspecialchars($t['col_actions']) ?></th>
                            <?php else: ?>
                                <th class="w-[8%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_task_no']) ?></th>
                                <th class="w-[24%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_title']) ?></th>
                                <?php if ($activeTab === 'allocated'): ?>
                                <th class="w-[13%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_worker']) ?></th>
                                <?php else: ?>
                                <th class="w-[13%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_creator']) ?></th>
                                <?php endif; ?>
                                <th class="w-[8%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_priority']) ?></th>
                                <th class="w-[11%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_status']) ?></th>
                                <th class="w-[10%] px-6 py-3.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_due']) ?></th>
                                <th class="w-[10%] px-6 py-3.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tracking</th>
                                <th class="w-[16%] px-6 py-3.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider no-print"><?= htmlspecialchars($t['col_actions']) ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                        <?php 
                        if ($activeTab === 'allocated') {
                            $tasksToDisplay = $allocatedTasks;
                        } elseif ($activeTab === 'tracking') {
                            $tasksToDisplay = $trackingTasks;
                        } else {
                            $tasksToDisplay = $assignedTasks;
                        }
                        if (empty($tasksToDisplay)): 
                        ?>
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400 font-medium">
                                <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-400 opacity-60"></i>
                                <?= htmlspecialchars($t['no_tasks']) ?>
                            </td>
                        </tr>
                        <?php 
                        else: 
                            foreach ($tasksToDisplay as $row):
                                $taskId = (int)$row['task_id'];
                                $isOverdue = ($row['status'] !== 'Completed' && !empty($row['due_date']) && strtotime($row['due_date']) < time());
                                $displayStatus = $isOverdue ? 'Overdue' : $row['status'];
                                $statusBadge = statusBadgeCss($displayStatus);
                                $priorityCss = priorityTextCss($row['priority']);
                                $dueFormatted = !empty($row['due_date']) ? date('M d, Y', strtotime($row['due_date'])) : 'N/A';
                                $dueColor = $isOverdue ? 'text-red-650 font-bold dark:text-red-400' : 'text-slate-650 dark:text-slate-300';
                        ?>
                        <tr id="task-row-<?= $taskId ?>" class="hover:bg-slate-50/50 dark:hover:bg-slate-750/30 transition-colors">
                            <?php if ($filterStatus === 'Overdue'): ?>
                                <!-- Name & Role -->
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($activeTab === 'allocated'): ?>
                                        <div class="text-slate-900 dark:text-white font-medium"><?= htmlspecialchars($row['assignee_name'] ?: 'N/A') ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($row['assigned_role_name'] ?: 'Role Assigned') ?></div>
                                    <?php elseif ($activeTab === 'tracking'): ?>
                                        <div class="text-slate-900 dark:text-white font-medium">To: <?= htmlspecialchars($row['assigned_to_name'] ?: 'Unassigned') ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500">By: <?= htmlspecialchars($row['creator_name'] ?: 'N/A') ?></div>
                                    <?php else: ?>
                                        <div class="text-slate-900 dark:text-white font-medium"><?= htmlspecialchars($row['creator_name'] ?: 'N/A') ?></div>
                                    <?php endif; ?>
                                </td>
                                <!-- Task ID -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-semibold text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($row['task_no']) ?>
                                </td>
                                <!-- Task Name -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row['task_title']) ?></div>
                                </td>
                                <!-- Assigned Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-650 dark:text-slate-300">
                                    <?= !empty($row['created_at']) ? date('M d, Y', strtotime($row['created_at'])) : 'N/A' ?>
                                </td>
                                <!-- Due Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm due-cell <?= $dueColor ?>">
                                    <?= $dueFormatted ?>
                                </td>
                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium no-print">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <button onclick="takeAction(<?= $taskId ?>, this)" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Action Taken
                                        </button>
                                        <button onclick="openReassignModal(<?= $taskId ?>)" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i> Reassign
                                        </button>
                                        <button onclick="openDetails(<?= $taskId ?>)" class="px-2.5 py-1 bg-slate-105 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-750 dark:text-slate-250 rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs" title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($t['btn_view']) ?>
                                        </button>
                                    </div>
                                </td>
                            <?php else: ?>
                                <!-- Task No -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-semibold text-slate-900 dark:text-white">
                                    <?= htmlspecialchars($row['task_no']) ?>
                                </td>
                                <!-- Title & Description -->
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($row['task_title']) ?></div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm line-clamp-2 leading-relaxed whitespace-normal break-words"><?= htmlspecialchars($row['task_description']) ?></div>
                                    <?php if (!empty($row['task_category'])): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 mt-1">
                                        <?= htmlspecialchars($row['task_category']) ?>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <!-- Creator / Assignee -->
                                <td class="px-6 py-4 text-sm">
                                    <?php if ($activeTab === 'allocated'): ?>
                                        <div class="text-slate-900 dark:text-white font-medium"><?= htmlspecialchars($row['assignee_name'] ?: 'N/A') ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($row['assigned_role_name'] ?: 'Role Assigned') ?></div>
                                    <?php elseif ($activeTab === 'tracking'): ?>
                                        <div class="text-slate-900 dark:text-white font-medium">To: <?= htmlspecialchars($row['assigned_to_name'] ?: 'Unassigned') ?></div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500">By: <?= htmlspecialchars($row['creator_name'] ?: 'N/A') ?></div>
                                    <?php else: ?>
                                        <div class="text-slate-900 dark:text-white font-medium"><?= htmlspecialchars($row['creator_name'] ?: 'N/A') ?></div>
                                    <?php endif; ?>
                                </td>
                                <!-- Priority -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm <?= $priorityCss ?>">
                                    <?= htmlspecialchars($t['priority_' . strtolower($row['priority'])]) ?>
                                </td>
                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm status-cell">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?= $statusBadge ?>">
                                        <?php 
                                        $statusKey = 'status_' . str_replace(' ', '_', strtolower($displayStatus));
                                        $statusText = isset($t[$statusKey]) ? $t[$statusKey] : $displayStatus;
                                        ?>
                                        <?= htmlspecialchars($statusText) ?>
                                    </span>
                                </td>
                                <!-- Due Date -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm due-cell <?= $dueColor ?>">
                                    <?= $dueFormatted ?>
                                </td>
                                <!-- Tracking -->
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button type="button" onclick="openDetails(<?= $taskId ?>)" class="inline-flex items-center justify-center px-2 py-1.5 text-navy-600 bg-navy-50 hover:bg-navy-100 dark:text-blue-400 dark:bg-navy-900/40 dark:hover:bg-navy-800 rounded-lg transition-colors border border-transparent hover:border-navy-200 dark:hover:border-navy-700" title="Track Journey">
                                        <i data-lucide="route" class="w-4 h-4"></i>
                                        <span class="ml-1.5 font-semibold text-[11px] uppercase tracking-wider">Track</span>
                                    </button>
                                </td>
                                <!-- Actions (no-print) -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium no-print">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <button onclick="openDetails(<?= $taskId ?>)" class="px-2.5 py-1 bg-slate-105 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-750 dark:text-slate-250 rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs" title="View Details">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($t['btn_view']) ?>
                                        </button>

                                    <?php if ($activeTab === 'assigned'): ?>
                                        <?php if ($row['status'] === 'Pending' || $row['status'] === 'Reassigned'): ?>
                                        <button onclick="acknowledgeTask(<?= $taskId ?>, this)" class="px-2.5 py-1 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i> Accept
                                        </button>
                                        <button onclick="openRejectTaskModal(<?= $taskId ?>)" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Reject
                                        </button>
                                        <?php endif; ?>

                                        <!-- Complete Action (visible in In Progress, Accepted, Reassigned) -->
                                        <?php if (in_array($row['status'], ['In Progress', 'Accepted'])): ?>
                                        <button onclick="openCompleteTaskModal(<?= $taskId ?>, '<?= htmlspecialchars(addslashes($row['task_title'])) ?>')" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> <?= htmlspecialchars($t['btn_complete']) ?>
                                        </button>
                                        <?php endif; ?>

                                        <!-- Hold/Resume/Transfer for Assignee -->
                                        <?php if (in_array($row['status'], ['In Progress', 'Accepted', 'Reassigned'])): ?>
                                        <button onclick="toggleHoldTask(<?= $taskId ?>, 'hold', this)" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i> Put On Hold
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($row['status'] === 'On Hold'): ?>
                                        <button onclick="toggleHoldTask(<?= $taskId ?>, 'resume', this)" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="play-circle" class="w-3.5 h-3.5"></i> Resume
                                        </button>
                                        <?php endif; ?>
                                        <?php if (in_array($row['status'], ['Pending', 'Reassigned', 'Accepted', 'In Progress', 'On Hold'])): ?>
                                        <button onclick="openReassignModal(<?= $taskId ?>)" class="px-2.5 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="share-2" class="w-3.5 h-3.5"></i> Transfer
                                        </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($activeTab === 'allocated'): ?>
                                        <!-- Verify Completion (visible to L1 / creator when Completed) -->
                                        <?php if ($row['status'] === 'Completed' && $isL1): ?>
                                        <button onclick="verifyCompletion(<?= $taskId ?>, this)" class="px-2.5 py-1 bg-purple-500 hover:bg-purple-650 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verify Completion
                                        </button>
                                        <?php endif; ?>

                                        <!-- Verify Rejection (visible to L1 / creator when Pending Verification) -->
                                        <?php if ($row['status'] === 'Pending Verification' && $isL1): ?>
                                        <button onclick="openReviewRejectionModal(<?= $taskId ?>)" class="px-2.5 py-1 bg-navy-500 hover:bg-navy-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Verify Rejection
                                        </button>
                                        <?php endif; ?>

                                        <!-- Hold/Resume/Transfer for Creator -->
                                        <?php if (in_array($row['status'], ['In Progress', 'Accepted', 'Reassigned', 'Pending'])): ?>
                                        <button onclick="toggleHoldTask(<?= $taskId ?>, 'hold', this)" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="pause-circle" class="w-3.5 h-3.5"></i> Put On Hold
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($row['status'] === 'On Hold'): ?>
                                        <button onclick="toggleHoldTask(<?= $taskId ?>, 'resume', this)" class="px-2.5 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="play-circle" class="w-3.5 h-3.5"></i> Resume
                                        </button>
                                        <?php endif; ?>
                                        <?php if (in_array($row['status'], ['Pending', 'Reassigned', 'Accepted', 'In Progress', 'On Hold'])): ?>
                                        <button onclick="openReassignModal(<?= $taskId ?>)" class="px-2.5 py-1 bg-indigo-500 hover:bg-indigo-600 text-white rounded-md transition-colors inline-flex items-center gap-1 shadow-sm font-semibold text-xs">
                                            <i data-lucide="share-2" class="w-3.5 h-3.5"></i> Transfer
                                        </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php 
                            endforeach; 
                        endif; 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODALS FOR TASK WORKFLOW ACTIONS
════════════════════════════════════════════════════════════════════ -->
<div id="reassignTaskModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-blue-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Reassign Task</h3>
            <button type="button" onclick="closeReassignModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form id="reassignTaskForm" onsubmit="submitReassignment(event)" class="p-6 space-y-4">
            <input type="hidden" id="reassignTaskId" name="task_id">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Select New Assignee *</label>
                <select name="new_assignee_id" id="new_assignee_id" required class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-blue-500">
                    <option value="">-- Select Candidate --</option>
                    <?php if (isset($usersList) && is_array($usersList)): ?>
                        <?php foreach ($usersList as $u): ?>
                            <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name'] . ' (' . $u['employee_code'] . ')') ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeReassignModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">Confirm Reassignment</button>
            </div>
        </form>
    </div>
</div>
<div id="rejectTaskModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-red-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Task Rejection Submission</h3>
            <button type="button" onclick="closeRejectTaskModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form id="rejectTaskForm" onsubmit="submitRejectTask(event)" class="p-6 space-y-4">
            <input type="hidden" id="rejectTaskId" name="task_id">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Rejection Reason *</label>
                <select name="reason" required class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-red-500">
                    <option value="">-- Choose Rejection Reason --</option>
                    <option value="Overlapping Priorities">Overlapping Priorities</option>
                    <option value="Resource Unavailability">Resource Unavailability</option>
                    <option value="Outside Area of Responsibility">Outside Area of Responsibility</option>
                    <option value="Technical Insufficiency">Technical Insufficiency</option>
                    <option value="Health / Leave Period">Health / Leave Period</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Detailed Remarks *</label>
                <textarea name="remarks" rows="4" required placeholder="Explain in detail why you are rejecting this task assignment..." class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-red-500"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Proof Upload (Mandatory Document) *</label>
                <input type="file" name="proof_file" required class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 border border-slate-300 dark:border-slate-600 rounded-lg p-1 dark:bg-slate-700">
                <p class="text-[10px] text-slate-400 mt-1">Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP. Max file size: 10MB.</p>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeRejectTaskModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-semibold transition-colors">Submit Rejection</button>
            </div>
        </form>
    </div>
</div>

<div id="completeTaskModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-blue-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Task Completion Submission</h3>
            <button type="button" onclick="closeCompleteTaskModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form id="completeTaskForm" onsubmit="submitCompleteTask(event)" class="p-6 space-y-4" enctype="multipart/form-data">
            <input type="hidden" id="completeTaskId" name="task_id">
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Achievements *</label>
                <textarea name="achievements" rows="4" required placeholder="Detail the outcome of this task. List block-wise targets met, percentages achieved, or field assessments completed..." class="block w-full border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-lg p-2.5 text-sm focus:ring-blue-500"></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Proof of Completion (Optional)</label>
                <input type="file" name="complete_file" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 dark:border-slate-600 rounded-lg p-1 dark:bg-slate-700">
                <p class="text-[10px] text-slate-400 mt-1">Supported formats: PDF, DOC, DOCX, JPG, PNG, ZIP. Max file size: 10MB.</p>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeCompleteTaskModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors">Submit Completion</button>
            </div>
        </form>
    </div>
</div>

<div id="reviewRejectionModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-navy-500 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Rejection Verification Review</h3>
            <button type="button" onclick="closeReviewRejectionModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <div class="p-6 space-y-4">
            <input type="hidden" id="reviewTaskId">
            
            <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg">
                <div>
                    <span class="block text-xs text-slate-400 font-bold uppercase">Employee</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-white" id="reviewEmployeeName">Employee Name</span>
                </div>
                <div>
                    <span class="block text-xs text-slate-400 font-bold uppercase">Task Name</span>
                    <span class="text-sm font-semibold text-slate-800 dark:text-white" id="reviewTaskTitle">Task Name</span>
                </div>
            </div>

            <div>
                <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Rejection Reason</span>
                <span class="text-sm font-semibold text-red-650 dark:text-red-400 bg-red-50 dark:bg-red-950/20 px-3 py-1 rounded-md" id="reviewReason">Reason Description</span>
            </div>

            <div>
                <span class="block text-xs text-slate-400 font-bold uppercase mb-1">Detailed Remarks</span>
                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/30 p-3 rounded-lg border border-slate-200 dark:border-slate-700 whitespace-pre-wrap" id="reviewRemarks">Detailed description remarks...</p>
            </div>

            <div class="border border-slate-200 dark:border-slate-700 p-4 rounded-xl flex justify-between items-center bg-slate-50 dark:bg-slate-900/30">
                <div class="flex items-center">
                    <i data-lucide="paperclip" class="w-5 h-5 text-slate-400 mr-2"></i>
                    <span class="text-sm font-semibold" id="reviewProofName">proof_file.pdf</span>
                </div>
                <a href="#" id="reviewProofDownload" target="_blank" class="px-4 py-2 bg-navy-500 hover:bg-navy-600 text-white rounded-lg text-xs font-bold transition-colors">Download Proof</a>
            </div>

            <div class="flex flex-wrap gap-2 justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="openClarificationModal()" class="px-3.5 py-2 bg-saffron-500 hover:bg-saffron-600 text-white rounded-lg text-xs font-semibold transition-colors">Request Clarification</button>
                <button type="button" onclick="submitRejectionReview('reject_rejection')" class="px-3.5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors">Deny Rejection (Reassign)</button>
                <button type="button" onclick="submitRejectionReview('approve_rejection')" class="px-3.5 py-2 bg-govgreen-500 hover:bg-govgreen-600 text-white rounded-lg text-xs font-semibold transition-colors">Approve Rejection</button>
            </div>
        </div>
    </div>
</div>

<div id="clarificationModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-saffron-500 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Clarification Message</h3>
            <button type="button" onclick="closeClarificationModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        <form onsubmit="submitClarification(event)" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">Enter your request or clarification notes *</label>
                <textarea id="clarificationMessage" required rows="4" placeholder="Please clarify the dates or provide further context..." class="block w-full border border-slate-300 dark:border-slate-650 rounded-lg p-2.5 text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-white focus:ring-saffron-500"></textarea>
            </div>
            
            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="button" onclick="closeClarificationModal()" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-semibold hover:bg-slate-100 dark:hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-saffron-500 hover:bg-saffron-600 text-white rounded-lg text-sm font-semibold transition-colors">Send Request</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL: View Task Details and Timeline Logs
════════════════════════════════════════════════════════════════════ -->
<div id="detailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto no-print">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" onclick="closeDetails()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        
        <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-200 dark:border-slate-700">
            <!-- Dark Navy Header like task_tracking -->
            <div class="px-6 py-5 bg-navy-700 dark:bg-slate-900 flex justify-between items-start">
                <div class="flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl bg-navy-600/50 flex items-center justify-center border border-navy-500/30">
                        <i data-lucide="git-branch" class="w-5 h-5 text-white"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold text-blue-300 uppercase tracking-wider mb-0.5" id="det_no"></p>
                        <h3 class="text-lg font-bold text-white leading-tight" id="det_title"></h3>
                    </div>
                </div>
                <button type="button" onclick="closeDetails()" class="text-slate-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- KPI Quick View -->
                <div class="grid grid-cols-4 gap-4 pb-2">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Task ID</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-white" id="det_id_val"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Assigned To</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200" id="det_assignee"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Priority</span>
                        <span class="text-sm font-bold" id="det_priority"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Due Date</span>
                        <span class="text-sm font-bold text-red-600 dark:text-red-400" id="det_due"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase mb-1">Description</span>
                    <p class="text-sm text-slate-650 dark:text-slate-350 leading-relaxed" id="det_desc"></p>
                </div>

                <!-- HORIZONTAL STATUS BAR -->
                <div class="border-y border-slate-100 dark:border-slate-700/50 py-6">
                    <div id="det_progress" class="flex items-start w-full"></div>
                </div>

                <!-- Documents List -->
                <div>
                    <h5 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5 flex items-center gap-1">
                        <i data-lucide="paperclip" class="w-4 h-4"></i> <?= htmlspecialchars($t['lbl_documents']) ?>
                    </h5>
                    <div id="det_docs" class="space-y-2"></div>
                </div>

                <!-- Status History timeline -->
                <div class="mt-4">
                    <h5 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-5 flex items-center gap-1">
                        <i data-lucide="activity" class="w-4 h-4"></i> <?= htmlspecialchars($t['lbl_history']) ?>
                    </h5>
                    <div id="det_history" class="timeline-wrapper pt-2"></div>
                </div>
            </div>

            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-855 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <button type="button" onclick="closeDetails()" class="px-4 py-2 bg-navy-600 hover:bg-navy-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Floating Toast Alert -->
<div id="toast" class="hidden fixed bottom-6 left-6 z-50 flex items-center gap-3 px-4 py-3 bg-slate-900 text-white dark:bg-white dark:text-slate-950 rounded-xl shadow-lg border border-slate-800 dark:border-slate-205 transition-all duration-300 transform translate-y-10 opacity-0">
    <i id="toastIcon" data-lucide="check-circle" class="w-5 h-5 text-govgreen-500"></i>
    <span id="toastMsg" class="text-sm font-medium"></span>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Toast alerts helper
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        const toastIcon = document.getElementById('toastIcon');

        toastMsg.textContent = msg;

        let icon = 'check-circle';
        let colorClass = 'text-govgreen-500';
        if (type === 'warning' || type === 'error') {
            icon = 'alert-triangle';
            colorClass = 'text-red-500';
        }

        toastIcon.setAttribute('data-lucide', icon);
        toastIcon.className = `w-5 h-5 ${colorClass}`;
        lucide.createIcons();

        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        }, 50);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3500);
    }

    // Dynamic runtime counters update
    function adjustKpiCounters(oldStatus, newStatus) {
        const kpiPending = document.getElementById('kpi-pending');
        const kpiInProgress = document.getElementById('kpi-in-progress');
        const kpiCompleted = document.getElementById('kpi-completed');
        const kpiRejected = document.getElementById('kpi-rejected');

        if (oldStatus === 'Pending' && kpiPending) {
            kpiPending.textContent = Math.max(0, parseInt(kpiPending.textContent) - 1);
        }
        if (oldStatus === 'In Progress' && kpiInProgress) {
            kpiInProgress.textContent = Math.max(0, parseInt(kpiInProgress.textContent) - 1);
        }

        if (newStatus === 'In Progress' && kpiInProgress) {
            kpiInProgress.textContent = parseInt(kpiInProgress.textContent) + 1;
        } else if (newStatus === 'Completed' && kpiCompleted) {
            kpiCompleted.textContent = parseInt(kpiCompleted.textContent) + 1;
        } else if (newStatus === 'Rejected' && kpiRejected) {
            kpiRejected.textContent = parseInt(kpiRejected.textContent) + 1;
        }
    }

    // Acknowledge task AJAX logic (accept action)
    function acknowledgeTask(taskId, buttonElement) {
        buttonElement.disabled = true;
        buttonElement.innerHTML = `<i class="w-3.5 h-3.5 animate-spin"></i> Processing...`;

        fetch('api/task_notification_actions.php?action=accept&task_id=' + taskId)
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = `<i data-lucide="check" class="w-3.5 h-3.5"></i> Accept`;
                    lucide.createIcons();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection failed.');
                buttonElement.disabled = false;
                buttonElement.innerHTML = `<i data-lucide="check" class="w-3.5 h-3.5"></i> Accept`;
                lucide.createIcons();
            });
    }

    // Modal Control: Reject
    function openRejectTaskModal(taskId) {
        document.getElementById('rejectTaskId').value = taskId;
        document.getElementById('rejectTaskModal').classList.remove('hidden');
    }
    function closeRejectTaskModal() {
        document.getElementById('rejectTaskModal').classList.add('hidden');
        document.getElementById('rejectTaskForm').reset();
    }
    function submitRejectTask(e) {
        e.preventDefault();
        const form = document.getElementById('rejectTaskForm');
        const fd = new FormData(form);
        fd.append('action', 'reject');

        fetch('api/task_notification_actions.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                closeRejectTaskModal();
                window.location.reload();
            }
        })
        .catch(() => alert('Network error submitting rejection. Ensure remarks and file upload size matches.'));
    }

    // Modal Control: Complete
    function openCompleteTaskModal(taskId, title) {
        document.getElementById('completeTaskId').value = taskId;
        document.getElementById('completeTaskModal').classList.remove('hidden');
    }
    function closeCompleteTaskModal() {
        document.getElementById('completeTaskModal').classList.add('hidden');
        document.getElementById('completeTaskForm').reset();
    }
    function submitCompleteTask(e) {
        e.preventDefault();
        const form = document.getElementById('completeTaskForm');
        const fd = new FormData(form);
        fd.append('action', 'complete');

        fetch('api/task_notification_actions.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                closeCompleteTaskModal();
                window.location.reload();
            }
        })
        .catch(() => alert('Network error submitting completion. Ensure achievements is filled and file upload size matches.'));
    }

    // L1 Verification Actions
    function verifyCompletion(taskId, buttonElement) {
        buttonElement.disabled = true;
        buttonElement.innerHTML = `<i class="w-3.5 h-3.5 animate-spin"></i> Processing...`;
        fetch(`api/task_notification_actions.php?action=verify&task_id=${taskId}`)
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    window.location.reload();
                } else {
                    buttonElement.disabled = false;
                    buttonElement.innerHTML = `<i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verify Completion`;
                    lucide.createIcons();
                }
            })
            .catch(() => {
                buttonElement.disabled = false;
                buttonElement.innerHTML = `<i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Verify Completion`;
                lucide.createIcons();
            });
    }

    function openReviewRejectionModal(taskId) {
        document.getElementById('reviewTaskId').value = taskId;
        
        fetch(`api/task_notification_actions.php?action=get_rejection_details&task_id=${taskId}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const rej = res.rejection;
                    document.getElementById('reviewEmployeeName').innerText = rej.full_name;
                    document.getElementById('reviewTaskTitle').innerText = `ID: ${taskId}`;
                    document.getElementById('reviewReason').innerText = rej.rejection_reason;
                    document.getElementById('reviewRemarks').innerText = rej.remarks;
                    
                    const fileName = rej.file_path.split('/').pop();
                    document.getElementById('reviewProofName').innerText = fileName;
                    document.getElementById('reviewProofDownload').href = rej.file_path;
                    
                    document.getElementById('reviewRejectionModal').classList.remove('hidden');
                    lucide.createIcons();
                } else {
                    alert(res.message);
                }
            });
    }

    function closeReviewRejectionModal() {
        document.getElementById('reviewRejectionModal').classList.add('hidden');
    }

    function submitRejectionReview(actionName) {
        const taskId = document.getElementById('reviewTaskId').value;
        fetch(`api/task_notification_actions.php?action=${actionName}&task_id=${taskId}`)
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.status === 'success') {
                    closeReviewRejectionModal();
                    window.location.reload();
                }
            });
    }

    function openClarificationModal() {
        document.getElementById('clarificationModal').classList.remove('hidden');
    }

    function closeClarificationModal() {
        document.getElementById('clarificationModal').classList.add('hidden');
        document.getElementById('clarificationMessage').value = '';
    }

    function submitClarification(e) {
        e.preventDefault();
        const taskId = document.getElementById('reviewTaskId').value;
        const message = document.getElementById('clarificationMessage').value;

        const fd = new FormData();
        fd.append('action', 'request_clarification');
        fd.append('task_id', taskId);
        fd.append('message', message);

        fetch('api/task_notification_actions.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                closeClarificationModal();
                closeReviewRejectionModal();
                window.location.reload();
            }
        });
    }

    function openReassignModal(taskId) {
        document.getElementById('reassignTaskId').value = taskId;
        document.getElementById('reassignTaskModal').classList.remove('hidden');
    }

    function closeReassignModal() {
        document.getElementById('reassignTaskModal').classList.add('hidden');
        document.getElementById('new_assignee_id').value = '';
    }

    function submitReassignment(e) {
        e.preventDefault();
        const taskId = document.getElementById('reassignTaskId').value;
        const newAssigneeId = document.getElementById('new_assignee_id').value;

        if (!newAssigneeId) {
            alert('Please select a new assignee.');
            return;
        }

        const fd = new FormData();
        fd.append('action', 'reassign_task');
        fd.append('task_id', taskId);
        fd.append('new_assignee_id', newAssigneeId);

        fetch('api/task_notification_actions.php', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.status === 'success') {
                closeReassignModal();
                window.location.reload();
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while reassigning the task.');
        });
    }

    function toggleHoldTask(taskId, type, btn) {
        const actionName = type === 'hold' ? 'hold_task' : 'resume_task';
        const confirmMsg = type === 'hold' ? 'Are you sure you want to put this task on hold?' : 'Are you sure you want to resume this task?';
        if (!confirm(confirmMsg)) return;

        btn.disabled = true;
        btn.innerHTML = `<i class="w-3.5 h-3.5 animate-spin"></i> Processing...`;

        fetch(`api/task_notification_actions.php?action=${actionName}&task_id=${taskId}`)
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    window.location.reload();
                } else {
                    btn.disabled = false;
                    btn.innerHTML = type === 'hold' ? '<i data-lucide="pause-circle" class="w-3.5 h-3.5"></i> Put On Hold' : '<i data-lucide="play-circle" class="w-3.5 h-3.5"></i> Resume';
                    lucide.createIcons();
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection failed.');
                btn.disabled = false;
                btn.innerHTML = type === 'hold' ? '<i data-lucide="pause-circle" class="w-3.5 h-3.5"></i> Put On Hold' : '<i data-lucide="play-circle" class="w-3.5 h-3.5"></i> Resume';
                lucide.createIcons();
            });
    }

    function takeAction(taskId, btn) {
        if (!confirm('Are you sure you want to take action and notify the candidate?')) return;
        
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-3.5 h-3.5 animate-spin"></i> Processing...';
        btn.disabled = true;

        fetch('api/task_notification_actions.php?action=take_action', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'task_id=' + taskId
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                window.location.reload();
            } else {
                btn.innerHTML = originalText;
                btn.disabled = false;
                lucide.createIcons();
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred.');
            btn.innerHTML = originalText;
            btn.disabled = false;
            lucide.createIcons();
        });
    }

    // AJAX Details Retrieval & Timeline Loader
    function openDetails(id) {
        fetch('reports.php?ajax=task_details&task_id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const task = data.task;
                    document.getElementById('det_title').textContent = task.task_title;
                    document.getElementById('det_no').textContent = task.task_no || ('#' + task.task_id);
                    document.getElementById('det_id_val').textContent = task.task_no || ('#' + task.task_id);
                    document.getElementById('det_desc').textContent = task.task_description || 'No description provided.';
                    
                    document.getElementById('det_assignee').textContent = task.assignee_name || 'Unassigned';
                    
                    const prioritySpan = document.getElementById('det_priority');
                    prioritySpan.textContent = task.priority || '—';
                    if(task.priority === 'Critical') prioritySpan.className = 'text-sm font-bold text-purple-600 dark:text-purple-400';
                    else if(task.priority === 'High') prioritySpan.className = 'text-sm font-bold text-red-600 dark:text-red-400';
                    else if(task.priority === 'Medium') prioritySpan.className = 'text-sm font-bold text-orange-500 dark:text-orange-400';
                    else prioritySpan.className = 'text-sm font-bold text-slate-500 dark:text-slate-400';

                    function formatSimpleDate(dateStr) {
                        if(!dateStr) return '';
                        const d = new Date(dateStr);
                        if(isNaN(d)) return dateStr;
                        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        return `${d.getDate().toString().padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
                    }
                    document.getElementById('det_due').textContent = formatSimpleDate(task.due_date) || '—';

                    // HORIZONTAL PROGRESS BAR LOGIC
                    const currentStatus = task.status || 'Pending';
                    const baseFlow = ['Pending', 'Assigned', 'In Progress', 'Completed'];
                    let statuses = [...baseFlow];
                    if (currentStatus.toLowerCase() === 'overdue' || currentStatus.toLowerCase() === 'escalated') {
                        statuses = ['Pending', 'Assigned', 'In Progress', 'Completed', 'Overdue'];
                    } else if (currentStatus.toLowerCase() === 'rejected') {
                        statuses = ['Pending', 'Assigned', 'In Progress', 'Rejected'];
                    }
                    if (!statuses.includes(currentStatus) && !['overdue', 'escalated'].includes(currentStatus.toLowerCase())) {
                        if (currentStatus.toLowerCase() !== 'accepted') {
                            statuses.push(currentStatus);
                        }
                    }
                    let currentIdx = statuses.indexOf(currentStatus);
                    if (currentStatus.toLowerCase() === 'accepted') {
                        currentIdx = statuses.indexOf('Assigned'); // Treat Accepted similar to Assigned for UI progression if not explicit
                    }
                    if(currentIdx === -1) currentIdx = statuses.length - 1; // Fallback
                    
                    const progressContainer = document.getElementById('det_progress');
                    progressContainer.innerHTML = '';
                    statuses.forEach((s, idx) => {
                        let cls = '';
                        if (idx < currentIdx) cls = 'done';
                        else if (idx === currentIdx) cls = 'active';
                        
                        let dotBg = 'bg-slate-200 dark:bg-slate-600';
                        let labelCls = 'text-slate-400 dark:text-slate-500';
                        let innerDot = `<div class="w-2 h-2 rounded-full bg-slate-400 dark:bg-slate-500"></div>`;
                        let dotColorHtml = ``;
                        let inlineLineStyle = '';

                        if (idx <= currentIdx) {
                            const sl = s.toLowerCase();
                            if (sl === 'escalated' || sl === 'overdue' || sl === 'rejected') {
                                dotColorHtml = `style="background-color: #ef4444; border-color: #ef4444;"`;
                                labelCls = 'text-red-600 dark:text-red-400 font-bold';
                                inlineLineStyle = 'background-color: #ef4444;';
                            } else if (sl === 'completed') {
                                dotColorHtml = `style="background-color: #22c55e; border-color: #22c55e;"`;
                                labelCls = 'text-green-600 dark:text-green-400 font-bold';
                                inlineLineStyle = 'background-color: #22c55e;';
                            } else if (sl === 'in progress') {
                                dotColorHtml = `style="background-color: #a855f7; border-color: #a855f7;"`;
                                labelCls = 'text-purple-600 dark:text-purple-400 font-bold';
                                inlineLineStyle = 'background-color: #a855f7;';
                            } else if (sl === 'assigned') {
                                dotColorHtml = `style="background-color: #3b82f6; border-color: #3b82f6;"`;
                                labelCls = 'text-blue-600 dark:text-blue-400 font-bold';
                                inlineLineStyle = 'background-color: #3b82f6;';
                            } else if (sl === 'pending') {
                                dotColorHtml = `style="background-color: #eab308; border-color: #eab308;"`;
                                labelCls = 'text-yellow-600 dark:text-yellow-400 font-bold';
                                inlineLineStyle = 'background-color: #eab308;';
                            } else {
                                dotColorHtml = `style="background-color: #1e3a8a; border-color: #1e3a8a;"`;
                                labelCls = 'text-navy-600 dark:text-blue-400 font-semibold';
                                inlineLineStyle = 'background-color: #1e3a8a;';
                            }
                        }
                        
                        let iconName = '';
                        if (idx < currentIdx) {
                            iconName = 'check';
                        } else if (idx === currentIdx) {
                            const sl = s.toLowerCase();
                            if (sl === 'pending') iconName = 'clock';
                            else if (sl === 'assigned') iconName = 'user-check';
                            else if (sl === 'in progress') iconName = 'loader';
                            else if (sl === 'completed') iconName = 'check-circle-2';
                            else if (sl === 'overdue' || sl === 'rejected') iconName = 'alert-triangle';
                            else iconName = 'activity';
                        }
                        
                        if (idx <= currentIdx) {
                            innerDot = `<i data-lucide="${iconName}" class="w-4 h-4 text-white"></i>`;
                        }
                        
                        let lineHtml = '';
                        if (idx > 0) {
                            const lineBg = idx <= currentIdx ? '' : 'bg-slate-200 dark:bg-slate-600';
                            lineHtml = `<div class="absolute top-4 right-1/2 left-0 h-0.5 ${lineBg}" style="${idx <= currentIdx ? inlineLineStyle : ''}; z-index: 1;"></div>`;
                        }

                        progressContainer.innerHTML += `
                            <div class="progress-step ${cls} flex-1">
                                <div class="relative flex justify-center mb-2">
                                    ${lineHtml}
                                    <div class="relative w-8 h-8 rounded-full ${idx <= currentIdx ? '' : dotBg} flex items-center justify-center" ${dotColorHtml} style="z-index: 2;">
                                        ${innerDot}
                                    </div>
                                </div>
                                <div class="text-[11px] text-center leading-tight mt-2 ${labelCls}">${s}</div>
                            </div>
                        `;
                    });

                    // Documents List
                    const docsContainer = document.getElementById('det_docs');
                    docsContainer.innerHTML = '';
                    if (!data.documents || data.documents.length === 0) {
                        docsContainer.innerHTML = '<p class="text-xs text-slate-500 italic font-medium">No files attached.</p>';
                    } else {
                        data.documents.forEach(doc => {
                            docsContainer.innerHTML += `
                                <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-xs">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="file" class="w-4 h-4 text-slate-400"></i>
                                        <span class="font-medium text-slate-700 dark:text-slate-350 truncate max-w-[200px]">${doc.file_name}</span>
                                        <span class="text-slate-400 opacity-60">by ${doc.uploader_name || 'System'}</span>
                                    </div>
                                    <a href="${doc.file_path}" target="_blank" class="text-navy-600 dark:text-blue-400 hover:underline font-bold flex items-center gap-0.5">
                                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                                    </a>
                                </div>
                            `;
                        });
                    }

                    // Helper to format date like '22 Jun 2026 06:59 am'
                    function formatTimelineDate(dateStr) {
                        if(!dateStr) return '';
                        const d = new Date(dateStr);
                        if(isNaN(d)) return dateStr;
                        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        let hours = d.getHours();
                        const ampm = hours >= 12 ? 'pm' : 'am';
                        hours = hours % 12;
                        hours = hours ? hours : 12; // the hour '0' should be '12'
                        const mins = d.getMinutes().toString().padStart(2, '0');
                        return `${d.getDate().toString().padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()} &middot; ${hours.toString().padStart(2, '0')}:${mins} ${ampm}`;
                    }

                    // History Timeline
                    const historyContainer = document.getElementById('det_history');
                    historyContainer.innerHTML = '<div class="timeline-line"></div>';
                    
                    const createdDate = formatTimelineDate(task.created_at) || 'Initial';
                    const hasAssignment = task.assignee_name && task.assignee_name !== 'Unassigned' && task.assignee_name !== 'N/A';
                    const hasMoreEvents = hasAssignment || (data.history && data.history.length > 0);

                    historyContainer.innerHTML += `
                        <div class="tl-node animate-in">
                            <div class="tl-dot bg-gradient-to-br from-slate-500 to-slate-600">
                                <i data-lucide="plus-circle" class="w-[18px] h-[18px] text-white"></i>
                            </div>
                            <div class="tl-card" style="border-left:4px solid #64748b">
                                <div class="absolute -top-2.5 left-4">
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">
                                        CREATED
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1 mb-2 flex-wrap">
                                    <h4 class="text-base font-bold text-slate-900 dark:text-white">Task Created</h4>
                                    ${!hasMoreEvents ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gradient-to-r from-navy-600 to-navy-500 text-white"><i data-lucide="sparkles" class="w-2.5 h-2.5"></i> LATEST</span>' : ''}
                                </div>
                                <div class="mb-2.5">
                                    <span class="change-badge">
                                        &mdash; <i data-lucide="arrow-right" class="w-3 h-3"></i> Pending
                                    </span>
                                </div>
                                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                                    "${task.task_title || task.task_no}" was created and added to the system.
                                </p>
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3 mt-1">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                        <strong class="text-slate-700 dark:text-slate-200 font-semibold">${task.creator_name || 'System'}</strong>
                                        <span class="text-slate-400 dark:text-slate-500">&middot; Creator</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                                        <span class="font-mono">${createdDate}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    if (hasAssignment) {
                        const assignedDate = formatTimelineDate(task.assigned_at || task.created_at) || 'Initial';
                        const isLatestAssignment = !data.history || data.history.length === 0;
                        historyContainer.innerHTML += `
                            <div class="tl-node animate-in">
                                <div class="tl-dot bg-gradient-to-br from-blue-500 to-blue-600">
                                    <i data-lucide="user-plus" class="w-[18px] h-[18px] text-white"></i>
                                </div>
                                <div class="tl-card" style="border-left:4px solid #3b82f6">
                                    <div class="absolute -top-2.5 left-4">
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">
                                            ASSIGNMENT
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1 mb-2 flex-wrap">
                                        <h4 class="text-base font-bold text-slate-900 dark:text-white">Task Assigned</h4>
                                        ${isLatestAssignment ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gradient-to-r from-navy-600 to-navy-500 text-white"><i data-lucide="sparkles" class="w-2.5 h-2.5"></i> LATEST</span>' : ''}
                                    </div>
                                    <div class="mb-2.5">
                                        <span class="change-badge">
                                            Pending <i data-lucide="arrow-right" class="w-3 h-3"></i> Assigned
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                                        Task assigned to <strong>${task.assignee_name}</strong>.
                                    </p>
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3 mt-1">
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                            <strong class="text-slate-700 dark:text-slate-200 font-semibold">System</strong>
                                            <span class="text-slate-400 dark:text-slate-500">&middot; Allocator</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                                            <span class="font-mono">${assignedDate}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    if (data.history && data.history.length > 0) {
                        const chronHistory = data.history.slice().reverse();
                        chronHistory.forEach((log, idx) => {
                            const isLatest = idx === chronHistory.length - 1;
                            const latestBadge = isLatest ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gradient-to-r from-navy-600 to-navy-500 text-white"><i data-lucide="sparkles" class="w-2.5 h-2.5"></i> LATEST</span>` : '';
                            const remarkText = log.remarks ? `<p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-3">${log.remarks}</p>` : '';
                            
                            let ev_icon = 'refresh-cw';
                            let ev_bg_cls = 'from-indigo-500 to-indigo-600';
                            let ev_color = '#6366f1';
                            if(log.new_status === 'Completed') { ev_bg_cls = 'from-green-500 to-green-600'; ev_color = '#22c55e'; ev_icon = 'check-circle'; }
                            else if(log.new_status === 'Rejected' || log.new_status === 'Overdue') { ev_bg_cls = 'from-red-500 to-red-600'; ev_color = '#ef4444'; ev_icon = 'alert-triangle'; }
                            else if(log.new_status === 'In Progress') { ev_bg_cls = 'from-purple-500 to-purple-600'; ev_color = '#a855f7'; ev_icon = 'play-circle'; }
                            else if(log.new_status === 'Accepted') { ev_bg_cls = 'from-blue-500 to-blue-600'; ev_color = '#3b82f6'; ev_icon = 'thumbs-up'; }
                            else if(log.new_status === 'Assigned' || log.new_status === 'Reassigned') { ev_bg_cls = 'from-blue-500 to-blue-600'; ev_color = '#3b82f6'; ev_icon = 'user-plus'; }

                            historyContainer.innerHTML += `
                                <div class="tl-node animate-in">
                                    <div class="tl-dot bg-gradient-to-br ${ev_bg_cls}">
                                        <i data-lucide="${ev_icon}" class="w-[18px] h-[18px] text-white"></i>
                                    </div>
                                    <div class="tl-card" style="border-left:4px solid ${ev_color}">
                                        <div class="absolute -top-2.5 left-4">
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 shadow-sm">
                                                STATUS CHANGED
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 mb-2 flex-wrap">
                                            <h4 class="text-base font-bold text-slate-900 dark:text-white">Status Changed</h4>
                                            ${latestBadge}
                                        </div>
                                        <div class="mb-2.5">
                                            <span class="change-badge">
                                                ${log.old_status || 'Start'} <i data-lucide="arrow-right" class="w-3 h-3"></i> ${log.new_status}
                                            </span>
                                        </div>
                                        ${remarkText}
                                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500 dark:text-slate-400 border-t border-slate-100 dark:border-slate-700 pt-3 mt-1">
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="user" class="w-3 h-3 flex-shrink-0"></i>
                                                <strong class="text-slate-700 dark:text-slate-200 font-semibold">${log.changer_name || 'System'}</strong>
                                                <span class="text-slate-400 dark:text-slate-500">&middot; User</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="clock" class="w-3 h-3 flex-shrink-0"></i>
                                                <span class="font-mono">${formatTimelineDate(log.change_date)}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    }

                    lucide.createIcons();
                    document.getElementById('detailsModal').classList.remove('hidden');
                } else {
                    showToast(data.message || 'Details fetch failed.', 'error');
                }
            })
            .catch(err => console.error('Details fetch error: ', err));
    }

    function closeDetails() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    // Excel Export function (using SheetJS with server-side fallback)
    function triggerExcelExport() {
        if (typeof XLSX !== 'undefined') {
            try {
                const table = document.getElementById('tasks-table');
                const filename = 'Amravati_Connect_Tasks_<?= $activeTab ?>.xlsx';
                
                const cloneTable = table.cloneNode(true);
                const rows = cloneTable.querySelectorAll('tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('th, td');
                    if(cells.length > 0) {
                        cells[cells.length - 1].remove();
                    }
                });

                const wb = XLSX.utils.table_to_book(cloneTable, { sheet: "Tasks Report" });
                XLSX.writeFile(wb, filename);
                return;
            } catch (err) {
                console.error('Client-side Excel export failed, falling back to server-side', err);
            }
        }
        // Server-side fallback / network-blocked workaround
        const url = `api/export_data.php?type=excel&scope=reports&tab=<?= $activeTab ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filterStatus) ?>&priority=<?= urlencode($filterPriority) ?>&lang=<?= $lang ?>`;
        window.location.href = url;
    }

    // PDF Export function (using html2pdf with print-dialog fallback)
    function triggerPDFExport() {
        if (typeof html2pdf === 'undefined') {
            alert('PDF generation library is offline. Opening print dialog as fallback. Please choose "Save as PDF" in your print options.');
            window.print();
            return;
        }
        try {
            const element = document.getElementById('report-container');
            const filename = 'Amravati_Connect_Tasks_<?= $activeTab ?>.pdf';
            
            const style = document.createElement('style');
            style.innerHTML = `
                #report-container .no-print, 
                #report-container th:last-child, 
                #report-container td:last-child {
                    display: none !important;
                }
            `;
            document.head.appendChild(style);

            const opt = {
                margin:       0.3,
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                style.remove();
            }).catch(err => {
                console.error(err);
                style.remove();
                window.print();
            });
        } catch (e) {
            console.error(e);
            window.print();
        }
    }
</script>
<?php include 'include/footer.php'; ?>
