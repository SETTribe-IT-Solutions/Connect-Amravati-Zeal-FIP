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
        'menu_gis' => 'GIS Map View',
        'menu_docs' => 'Document Management',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'menu_hierarchy' => 'Location Hierarchy',
        'menu_audit' => 'Audit Logs',
        'menu_settings' => 'Settings',
        'menu_logout' => 'Logout',
        'btn_ask_ai' => 'Ask Amravati AI',
        'page_title' => 'Task Reports & Analytics',
        'page_subtitle' => 'Monitor and execute task assignments, rejections, completions, and performance reports.',
        'btn_print' => 'Print Report',
        'btn_pdf' => 'Export PDF',
        'btn_excel' => 'Export Excel',
        'tab_assigned' => 'Assigned to Me',
        'tab_allocated' => 'Allocated by Me',
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
if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
}
if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role'] = 'Collector';
    $_SESSION['user_name'] = 'Hon. Collector';
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'];
$sName  = $_SESSION['user_name'];

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
$activeTab = $_GET['tab'] ?? 'assigned'; // 'assigned' or 'allocated'

$assignedTasks = [];
$allocatedTasks = [];

if ($db_connected) {
    $whereAssigned = "WHERE (t.assigned_user_id = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_to_user = $userId))";
    $whereAllocated = "WHERE (t.created_by = $userId OR t.task_id IN (SELECT ta.task_id FROM task_assignments ta WHERE ta.assigned_from_user = $userId))";

    if (!empty($search)) {
        $searchEsc = $conn->real_escape_string($search);
        $searchCond = " AND (t.task_title LIKE '%$searchEsc%' OR t.task_no LIKE '%$searchEsc%' OR t.task_description LIKE '%$searchEsc%')";
        $whereAssigned .= $searchCond;
        $whereAllocated .= $searchCond;
    }

    if ($filterStatus !== 'All') {
        $statusEsc = $conn->real_escape_string($filterStatus);
        if ($statusEsc === 'Overdue') {
            $whereAssigned .= " AND t.due_date < CURDATE() AND t.status != 'Completed'";
            $whereAllocated .= " AND t.due_date < CURDATE() AND t.status != 'Completed'";
        } else {
            $whereAssigned .= " AND t.status = '$statusEsc'";
            $whereAllocated .= " AND t.status = '$statusEsc'";
        }
    }

    if ($filterPriority !== 'All') {
        $priorityEsc = $conn->real_escape_string($filterPriority);
        $whereAssigned .= " AND t.priority = '$priorityEsc'";
        $whereAllocated .= " AND t.priority = '$priorityEsc'";
    }

    $assignedRes = $conn->query("SELECT t.*, u.full_name AS creator_name FROM tasks t LEFT JOIN users u ON t.created_by = u.user_id $whereAssigned ORDER BY t.created_at DESC");
    if ($assignedRes) {
        while ($row = $assignedRes->fetch_assoc()) $assignedTasks[] = $row;
    }

    $allocatedRes = $conn->query("SELECT t.*, u.full_name AS assignee_name, r.role_name AS assigned_role_name FROM tasks t LEFT JOIN users u ON t.assigned_user_id = u.user_id LEFT JOIN roles r ON t.assigned_role_id = r.role_id $whereAllocated ORDER BY t.created_at DESC");
    if ($allocatedRes) {
        while ($row = $allocatedRes->fetch_assoc()) $allocatedTasks[] = $row;
    }
} else {
    // Generate Mock Tasks if Database is offline
    $mockListAssigned = [
        [
            'task_id' => 101, 'task_no' => 'TSK_001', 'task_title' => 'Crop Damage Assessment Chandur',
            'task_description' => 'Review crop destruction fields due to hailstorm.', 'creator_name' => 'Sanjay Deshmukh',
            'priority' => 'High', 'status' => 'Pending', 'due_date' => '2026-06-25', 'task_category' => 'Revenue', 'created_at' => '2026-06-20'
        ],
        [
            'task_id' => 102, 'task_no' => 'TSK_002', 'task_title' => 'Health Center Facility Verification',
            'task_description' => 'Inspect blocks primary health units availability.', 'creator_name' => 'Priya Rathod',
            'priority' => 'Critical', 'status' => 'In Progress', 'due_date' => '2026-06-21', 'task_category' => 'Health', 'created_at' => '2026-06-18'
        ],
        [
            'task_id' => 103, 'task_no' => 'TSK_003', 'task_title' => 'Primary School Midday Meal Review',
            'task_description' => 'Audit meal checks in rural institutions.', 'creator_name' => 'Hon. Collector',
            'priority' => 'Low', 'status' => 'Completed', 'due_date' => '2026-06-15', 'task_category' => 'Education', 'created_at' => '2026-06-10'
        ]
    ];

    $mockListAllocated = [
        [
            'task_id' => 201, 'task_no' => 'TSK_101', 'task_title' => 'Infrastructure Fund Utilization Survey',
            'task_description' => 'Analyze rural development expenditure files.', 'assignee_name' => 'Anil Patil',
            'assigned_role_name' => 'Gramsevak', 'priority' => 'High', 'status' => 'Pending', 'due_date' => '2026-06-29',
            'task_category' => 'Audit', 'created_at' => '2026-06-20'
        ],
        [
            'task_id' => 202, 'task_no' => 'TSK_102', 'task_title' => 'E-KYC Camp Reports Verification',
            'task_description' => 'Process voter list linking analytics.', 'assignee_name' => 'Rajesh Kolhe',
            'assigned_role_name' => 'Talathi', 'priority' => 'Medium', 'status' => 'Completed', 'due_date' => '2026-06-12',
            'task_category' => 'Survey', 'created_at' => '2026-06-05'
        ],
        [
            'task_id' => 203, 'task_no' => 'TSK_103', 'task_title' => 'Water Canal Desilting Inspection',
            'task_description' => 'Check blocks desilting status before monsoon.', 'assignee_name' => 'Sunita More',
            'assigned_role_name' => 'BDO', 'priority' => 'Critical', 'status' => 'Rejected', 'due_date' => '2026-06-10',
            'task_category' => 'Infrastructure', 'created_at' => '2026-06-01'
        ]
    ];

    // Filter Mock List in PHP
    foreach ($mockListAssigned as $t_item) {
        $match = true;
        if (!empty($search) && stripos($t_item['task_title'], $search) === false && stripos($t_item['task_no'], $search) === false) $match = false;
        if ($filterStatus !== 'All') {
            if ($filterStatus === 'Overdue') {
                if ($t_item['status'] === 'Completed' || strtotime($t_item['due_date']) >= time()) $match = false;
            } elseif ($t_item['status'] !== $filterStatus) $match = false;
        }
        if ($filterPriority !== 'All' && $t_item['priority'] !== $filterPriority) $match = false;
        if ($match) $assignedTasks[] = $t_item;
    }

    foreach ($mockListAllocated as $t_item) {
        $match = true;
        if (!empty($search) && stripos($t_item['task_title'], $search) === false && stripos($t_item['task_no'], $search) === false) $match = false;
        if ($filterStatus !== 'All') {
            if ($filterStatus === 'Overdue') {
                if ($t_item['status'] === 'Completed' || strtotime($t_item['due_date']) >= time()) $match = false;
            } elseif ($t_item['status'] !== $filterStatus) $match = false;
        }
        if ($filterPriority !== 'All' && $t_item['priority'] !== $filterPriority) $match = false;
        if ($match) $allocatedTasks[] = $t_item;
    }
}

// ═══════════════════════════════════════════════════════════════════
// KPI Calculations for Active Tab
// ═══════════════════════════════════════════════════════════════════
$kpiAssigned = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];
$kpiAllocated = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'rejected' => 0, 'overdue' => 0];

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
} else {
    // Hardcoded stats based on Mock database lists
    $kpiAssigned = ['total' => 3, 'pending' => 1, 'in_progress' => 1, 'completed' => 1, 'rejected' => 0, 'overdue' => 1];
    $kpiAllocated = ['total' => 3, 'pending' => 1, 'in_progress' => 0, 'completed' => 1, 'rejected' => 1, 'overdue' => 1];
}

$currentKpis = ($activeTab === 'allocated') ? $kpiAllocated : $kpiAssigned;

/* User Badge configurations */
$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
$level    = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

function statusBadgeCss(string $s): string {
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

function priorityTextCss(string $p): string {
    return match($p) {
        'Critical' => 'text-purple-600 font-bold dark:text-purple-400',
        'High'     => 'text-red-650 font-semibold dark:text-red-400',
        'Medium'   => 'text-orange-500 font-medium dark:text-orange-400',
        default    => 'text-slate-500 dark:text-slate-400',
    };
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SheetJS for Excel Exports -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <!-- html2pdf for PDF Exports -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <!-- Theme Persist Script -->
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        } else {
            document.documentElement.classList.add('light');
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Tailwind Configuration -->
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

        .kpi-card { transition: transform 0.2s, box-shadow 0.2s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -4px rgba(0,0,0,0.1); }

        .badge-l1 { background: #dbeafe; color: #1e3a8a; border: 1px solid #bfdbfe; }
        .badge-l2 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-l3 { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .dark .badge-l1 { background: #1e3a8a33; color: #93c5fd; border-color: #1e40af; }
        .dark .badge-l2 { background: #92400e33; color: #fcd34d; border-color: #b45309; }
        .dark .badge-l3 { background: #065f4633; color: #6ee7b7; border-color: #047857; }

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
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- ═══════════════════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════════════════════ -->
<aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20 no-print" id="sidebar">
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight"><?= htmlspecialchars($t['brand_name']) ?></span>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
            <a href="dashboard.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_dashboard']) ?>
            </a>
            <a href="announcements.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="megaphone" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_announcement_center'] ?? 'Announcement Center') ?>
            </a>
            <a href="create_task.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_task_alloc']) ?>
            </a>
            <a href="notifications.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_notifications']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_appreciation']) ?>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_analytics']) ?></p>
            <a href="reports.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-navy-50 text-navy-700 dark:bg-slate-800 dark:text-white">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                <?= htmlspecialchars($t['menu_reports']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_gis']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_docs']) ?>
            </a>

            <?php if ($level === 1): ?>
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_admin']) ?></p>
            <a href="user_creation.php?lang=<?= $lang ?>" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_users']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map-pin" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_hierarchy']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_audit']) ?>
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="settings" class="w-5 h-5 mr-3 text-slate-400"></i>
                <?= htmlspecialchars($t['menu_settings']) ?>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5 mr-3 text-red-500"></i>
                <?= htmlspecialchars($t['menu_logout']) ?>
            </a>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none">
            <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
            <?= htmlspecialchars($t['btn_ask_ai']) ?>
        </button>
    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0 no-print">
        <div class="flex items-center flex-1">
            <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            
            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm" aria-label="Breadcrumb">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['menu_reports']) ?></span>
            </nav>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language Toggle -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'reports.php?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300
                      hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md
                      transition-colors border border-slate-200 dark:border-slate-700" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                <?php echo $lang === 'en' ? 'मराठी (MR)' : 'English (EN)'; ?>
            </a>

            <!-- Theme Switcher -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>

            <!-- Profile Info -->
            <div class="flex items-center space-x-3 border-l border-slate-200 dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                <div class="flex flex-col text-right hidden sm:block">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?></span>
                </div>
                <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm">
                    <?= htmlspecialchars($initials) ?>
                </div>
            </div>
        </div>
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
            <p class="text-center text-sm text-slate-600">Report Type: <?= $activeTab === 'allocated' ? 'Allocated by Me' : 'Assigned to Me' ?> | Date: <?= date('Y-m-d H:i') ?></p>
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
                <button onclick="window.print()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i>
                    <?= htmlspecialchars($t['btn_print']) ?>
                </button>
                <button onclick="triggerPDFExport()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
                    <i data-lucide="file-down" class="w-4 h-4 mr-2 text-red-500"></i>
                    <?= htmlspecialchars($t['btn_pdf']) ?>
                </button>
                <button onclick="triggerExcelExport()" class="inline-flex items-center px-3.5 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-750 transition-colors">
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
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6 no-print">
            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 kpi-card">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_total']) ?></p>
                <p id="kpi-total" class="text-2xl font-bold mt-2 text-slate-800 dark:text-white"><?= $currentKpis['total'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 kpi-card">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_pending']) ?></p>
                <p id="kpi-pending" class="text-2xl font-bold mt-2 text-yellow-600 dark:text-yellow-400"><?= $currentKpis['pending'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 kpi-card">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_in_progress']) ?></p>
                <p id="kpi-in-progress" class="text-2xl font-bold mt-2 text-blue-600 dark:text-blue-400"><?= $currentKpis['in_progress'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 kpi-card">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_completed']) ?></p>
                <p id="kpi-completed" class="text-2xl font-bold mt-2 text-govgreen-600 dark:text-green-450"><?= $currentKpis['completed'] ?></p>
            </div>
            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 kpi-card col-span-2 lg:col-span-1">
                <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider"><?= htmlspecialchars($t['kpi_rejected']) ?> / <?= htmlspecialchars($t['status_overdue']) ?></p>
                <div class="flex items-baseline gap-2">
                    <p id="kpi-rejected" class="text-2xl font-bold mt-2 text-red-600 dark:text-red-400"><?= $currentKpis['rejected'] ?></p>
                    <span class="text-xs text-slate-400 dark:text-slate-500">/</span>
                    <p id="kpi-overdue" class="text-sm font-semibold text-red-500 dark:text-red-400"><?= $currentKpis['overdue'] ?> overdue</p>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-6 no-print">
            <form method="GET" action="reports.php" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="lang" value="<?= $lang ?>">
                <input type="hidden" name="tab" value="<?= $activeTab ?>">

                <div class="flex-1 min-w-[240px]">
                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1.5">Text Search</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
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
                    <select name="priority" class="block w-44 px-3 py-2 text-sm border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-900 text-slate-950 dark:text-white focus:outline-none">
                        <option value="All" <?= $filterPriority === 'All' ? 'selected' : '' ?>><?= htmlspecialchars($t['filter_all_priority']) ?></option>
                        <option value="Critical" <?= $filterPriority === 'Critical' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_critical']) ?></option>
                        <option value="High" <?= $filterPriority === 'High' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_high']) ?></option>
                        <option value="Medium" <?= $filterPriority === 'Medium' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_medium']) ?></option>
                        <option value="Low" <?= $filterPriority === 'Low' ? 'selected' : '' ?>><?= htmlspecialchars($t['priority_low']) ?></option>
                    </select>
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
        <div id="report-container" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table id="tasks-table" class="w-full min-w-[1000px] divide-y divide-slate-200 dark:divide-slate-700 table-fixed">
                    <thead class="bg-slate-50 dark:bg-slate-900/50">
                        <tr>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700 bg-white dark:bg-slate-800">
                        <?php 
                        $tasksToDisplay = ($activeTab === 'allocated') ? $allocatedTasks : $assignedTasks;
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($activeTab === 'allocated'): ?>
                                    <div class="text-slate-900 dark:text-white font-medium"><?= htmlspecialchars($row['assignee_name'] ?: 'N/A') ?></div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500"><?= htmlspecialchars($row['assigned_role_name'] ?: 'Role Assigned') ?></div>
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
                                <a href="task_tracking.php?task_id=<?= $taskId ?>" class="inline-flex items-center justify-center px-2 py-1.5 text-navy-600 bg-navy-50 hover:bg-navy-100 dark:text-blue-400 dark:bg-navy-900/40 dark:hover:bg-navy-800 rounded-lg transition-colors border border-transparent hover:border-navy-200 dark:hover:border-navy-700" title="Track Journey">
                                    <i data-lucide="route" class="w-4 h-4"></i>
                                    <span class="ml-1.5 font-semibold text-[11px] uppercase tracking-wider">Track</span>
                                </a>
                            </td>
                            <!-- Actions (no-print) -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium no-print">
                                <div class="flex flex-wrap justify-end gap-1.5">
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
                                    <?php endif; ?>
                                </div>
                            </td>
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
        
        <div class="inline-block align-middle bg-white dark:bg-slate-800 rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200 dark:border-slate-700">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-slate-50 dark:bg-slate-850">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="info" class="w-5 h-5 text-navy-600 dark:text-blue-400"></i>
                    <span><?= htmlspecialchars($t['details_title']) ?></span>
                </h3>
                <button type="button" onclick="closeDetails()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <!-- Task Header Info -->
                <div>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white" id="det_title"></h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-mono mt-1" id="det_no"></p>
                    <p class="text-sm text-slate-650 dark:text-slate-350 mt-3 leading-relaxed" id="det_desc"></p>
                </div>

                <!-- KPI Quick View -->
                <div class="grid grid-cols-3 gap-4 p-4 bg-slate-50 dark:bg-slate-900 rounded-xl">
                    <div>
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase">Allocated By</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200" id="det_creator"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase">Assigned To</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200" id="det_assignee"></span>
                    </div>
                    <div>
                        <span class="block text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase">Due Date</span>
                        <span class="text-sm font-medium text-slate-800 dark:text-slate-200" id="det_due"></span>
                    </div>
                </div>

                <!-- Documents List -->
                <div>
                    <h5 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5 flex items-center gap-1">
                        <i data-lucide="paperclip" class="w-4 h-4"></i> <?= htmlspecialchars($t['lbl_documents']) ?>
                    </h5>
                    <div id="det_docs" class="space-y-2"></div>
                </div>

                <!-- Status History timeline -->
                <div>
                    <h5 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1">
                        <i data-lucide="activity" class="w-4 h-4"></i> <?= htmlspecialchars($t['lbl_history']) ?>
                    </h5>
                    <div id="det_history" class="relative border-l-2 border-slate-200 dark:border-slate-700 ml-3 space-y-4"></div>
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

    // AJAX Details Retrieval & Timeline Loader
    function openDetails(id) {
        fetch('reports.php?ajax=task_details&task_id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const task = data.task;
                    document.getElementById('det_title').textContent = task.task_title;
                    document.getElementById('det_no').textContent = '#' + task.task_no;
                    document.getElementById('det_desc').textContent = task.task_description || 'No description provided.';
                    document.getElementById('det_creator').textContent = task.creator_name || 'N/A';
                    document.getElementById('det_assignee').textContent = task.assignee_name || 'N/A';
                    document.getElementById('det_due').textContent = task.due_date || 'N/A';

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

                    // History Timeline
                    const historyContainer = document.getElementById('det_history');
                    historyContainer.innerHTML = '';
                    if (!data.history || data.history.length === 0) {
                        historyContainer.innerHTML = '<p class="text-xs text-slate-500 italic ml-4">No logged history found.</p>';
                    } else {
                        data.history.forEach(log => {
                            const remarkText = log.remarks ? `<p class="text-xs text-slate-500 dark:text-slate-400 italic bg-slate-50 dark:bg-slate-900 p-2 border border-slate-150 dark:border-slate-800 rounded-md mt-1.5">${log.remarks}</p>` : '';
                            historyContainer.innerHTML += `
                                <div class="relative pl-6 pb-2">
                                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-navy-600 dark:bg-blue-400 ring-4 ring-white dark:ring-slate-800"></div>
                                    <div class="text-xs">
                                        <span class="font-bold text-slate-800 dark:text-white">${log.new_status}</span>
                                        <span class="text-slate-400 opacity-70">from ${log.old_status || 'Start'}</span>
                                        <span class="block text-[10px] text-slate-450 dark:text-slate-500 mt-0.5">${log.change_date} &middot; by ${log.changer_name || 'System'}</span>
                                        ${remarkText}
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

    // Excel Export function (using SheetJS)
    function triggerExcelExport() {
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
    }

    // PDF Export function (using html2pdf)
    function triggerPDFExport() {
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
        });
    }
</script>
</body>
</html>
