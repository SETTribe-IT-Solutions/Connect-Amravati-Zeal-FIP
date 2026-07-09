<?php
/**
 * Create Task Page
 * Amravati Connect - Government Workflow Platform
 *
 * Allows administrators to create and allocate tasks to employees
 * either by name (user) or by role (all users with that role get the task).
 */

session_start();
require_once 'include/dbConfig.php';


// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        // Sidebar & Header Menu
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

        // Profile Menu
        'profile_update' => 'User Profile Update',
        'profile_settings' => 'Settings',
        'profile_password_change' => 'Password Change',
        'profile_logout' => 'Logout',

        // Roles
        'role_administrator' => 'System Administrator',
        'role_collector' => 'District Collector',
        'role_additional_collector' => 'Additional Collector',
        'role_deputy_collector' => 'Deputy Collector',
        'role_sdo' => 'Sub-Divisional Officer',
        'role_tehsildar' => 'Tehsildar',
        'role_bdo' => 'Block Development Officer',
        'role_talathi' => 'Talathi',
        'role_gramsevak' => 'Gramsevak',

        // Page Headings & Breadcrumbs
        'breadcrumb_dashboard' => 'Dashboard',
        'breadcrumb_task_allocation' => 'Task Allocation',
        'breadcrumb_create_task' => 'Create Task',
        'page_title' => 'Task Allocation',
        'page_subtitle' => 'Assign official tasks to government employees by name or by role/department.',
        'lbl_auto_id' => 'Auto ID:',
        'lbl_auto_generated' => 'Auto-Generated',
        'btn_back' => 'Back',

        // Form fields & sections
        'section_basic_info' => 'Basic Information',
        'section_basic_info_desc' => 'Core task details',
        'lbl_task_id' => 'Task ID',
        'lbl_task_title' => 'Task Title',
        'placeholder_task_title' => 'e.g. Crop Damage Assessment Report – Chandur Block',
        'lbl_task_desc' => 'Task Description',
        'placeholder_task_desc' => 'Provide a detailed description of the task, objectives, and expected outcomes...',
        'lbl_task_category' => 'Task Category',
        'placeholder_task_category' => 'e.g. Revenue, Health, Education, Infrastructure',

        'section_task_alloc' => 'Task Allocation',
        'section_task_alloc_desc' => 'Assign to an individual or a role/department',
        'lbl_alloc_type' => 'Allocation Type',
        'lbl_by_name' => 'By Name',
        'lbl_by_name_desc' => 'Assign to specific employee',
        'lbl_by_role' => 'By Role',
        'lbl_by_role_desc' => 'Assign to a role/department',
        'lbl_select_employee' => 'Select Employee',
        'opt_select_employee' => '— Select an employee —',
        'opt_no_employees' => 'No active employees found in database',
        'lbl_select_role' => 'Select Role',
        'opt_select_role' => '— Select a role —',
        'opt_no_roles' => 'No active roles found in database',
        'lbl_filter_department' => 'Filter by Department',
        'lbl_optional' => '(optional)',
        'opt_all_departments' => '— All Departments —',

        'section_attachment' => 'Attachment',
        'section_attachment_desc' => 'PDF, Word, Images, Audio & Video accepted',
        'drop_zone_text' => 'Drop your file here or',
        'drop_zone_browse' => 'browse',
        'drop_zone_types' => 'PDF · Word · JPG · PNG · MP3 · MP4 · and more (max 20 MB)',

        'section_schedule' => 'Schedule',
        'section_schedule_desc' => 'Deadlines & targets',
        'lbl_due_date' => 'Due Date & Time',
        'msg_date_past' => 'Due date cannot be in the past.',
        'lbl_target' => 'Target / Milestone',
        'placeholder_target' => 'e.g. Submit 50 survey forms',

        'section_priority' => 'Priority Level',
        'section_priority_desc' => 'Set task urgency',
        'priority_low' => 'Low',
        'priority_medium' => 'Medium',
        'priority_high' => 'High',
        'priority_critical' => 'Critical',

        'section_preview' => 'Live Preview',
        'lbl_title' => 'Title',
        'lbl_priority' => 'Priority',
        'lbl_allocation' => 'Allocation',
        'lbl_attachment' => 'Attachment',
        'lbl_none' => 'None',

        'btn_submit' => 'Create & Allocate Task',
        'btn_reset' => 'Reset Form',

        'msg_task_visible' => 'The task is now visible to the assigned employee(s).',
        'msg_upload_failed' => 'File upload failed. Please check folder permissions.',
        'msg_invalid_type' => 'Invalid file type. Only PDF, Word, Images, Audio, and Video are allowed.',
        'msg_db_unavailable' => 'Database connection unavailable. Cannot create task.',

        'msg_fetching_employees' => 'Fetching employees with this role…',
        'msg_employees_receive' => '%d employee(s) will receive this task',
        'msg_employees_receive_preview' => 'users',
        'btn_show_all' => 'Show all',
        'btn_hide' => 'Hide',
        'msg_no_employees_role' => 'No active employees found with this role.',
        'btn_submitting' => 'Creating Task…',

        'toast_title_req' => 'Task title is required.',
        'toast_due_req' => 'Please select a due date & time.',
        'toast_employee_req' => 'Please select an employee to assign.',
        'toast_role_req' => 'Please select a role to assign.',
        'toast_created' => 'created!',
        'toast_warning' => 'warning',
        'toast_success' => 'success',
        
        'char_counter' => '%d / 255 characters',

        // Wizard pagination
        'wizard_step1' => 'Task Details & Allocation',
        'wizard_step2' => 'Attachments, Schedule & Priority',
        'btn_next' => 'Next: Schedule & Attachments',
        'btn_prev' => 'Previous: Task Details',
        'wizard_step_of' => 'Step %d of %d'
    ],
    'mr' => [
        // Sidebar & Header Menu
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

        // Profile Menu
        'profile_update' => 'वापरकर्ता प्रोफाइल अपडेट',
        'profile_settings' => 'सेटिंग्ज',
        'profile_password_change' => 'पासवर्ड बदला',
        'profile_logout' => 'लॉगआउट',

        // Roles
        'role_administrator' => 'सिस्टम प्रशासक',
        'role_collector' => 'जिल्हाधिकारी',
        'role_additional_collector' => 'अपर जिल्हाधिकारी',
        'role_deputy_collector' => 'उपजिल्हाधिकारी',
        'role_sdo' => 'उपविभागीय अधिकारी (SDO)',
        'role_tehsildar' => 'तहसीलदार',
        'role_bdo' => 'गट विकास अधिकारी (BDO)',
        'role_talathi' => 'तलाठी',
        'role_gramsevak' => 'ग्रामसेवक',

        // Page Headings & Breadcrumbs
        'breadcrumb_dashboard' => 'डॅशबोर्ड',
        'breadcrumb_task_allocation' => 'कार्य वाटप',
        'breadcrumb_create_task' => 'कार्य तयार करा',
        'page_title' => 'कार्य तयार करा आणि वाटप करा',
        'page_subtitle' => 'शासकीय कर्मचाऱ्यांना नावानुसार किंवा भूमिका/विभागानुसार अधिकृत कार्ये सोपवा.',
        'lbl_auto_id' => 'स्वयंचलित आयडी (Auto ID):',
        'lbl_auto_generated' => 'स्वयंचलित व्युत्पन्न',
        'btn_back' => 'मागे',

        // Form fields & sections
        'section_basic_info' => 'मूलभूत माहिती',
        'section_basic_info_desc' => 'कार्याचा मुख्य तपशील',
        'lbl_task_id' => 'कार्य आयडी',
        'lbl_task_title' => 'कार्याचे शीर्षक',
        'placeholder_task_title' => 'उदा. पीक नुकसान मूल्यांकन अहवाल – चांदूर रेल्वे गट',
        'lbl_task_desc' => 'कार्याचे वर्णन',
        'placeholder_task_desc' => 'कार्याचे सविस्तर वर्णन, उद्दिष्टे आणि अपेक्षित परिणाम प्रदान करा...',
        'lbl_task_category' => 'कार्याची श्रेणी',
        'placeholder_task_category' => 'उदा. महसूल, आरोग्य, शिक्षण, पायाभूत सुविधा',

        'section_task_alloc' => 'कार्य वाटप',
        'section_task_alloc_desc' => 'वैयक्तिक कर्मचारी किंवा भूमिका/विभागानुसार नियुक्त करा',
        'lbl_alloc_type' => 'वाटपाचा प्रकार',
        'lbl_by_name' => 'नावानुसार',
        'lbl_by_name_desc' => 'विशिष्ट कर्मचाऱ्याला नियुक्त करा',
        'lbl_by_role' => 'भूमिकेनुसार',
        'lbl_by_role_desc' => 'भूमिका किंवा विभागानुसार नियुक्त करा',
        'lbl_select_employee' => 'कर्मचारी निवडा',
        'opt_select_employee' => '— कर्मचारी निवडा —',
        'opt_no_employees' => 'डेटाबेसमध्ये कोणतेही सक्रिय कर्मचारी आढळले नाहीत',
        'lbl_select_role' => 'भूमिका निवडा',
        'opt_select_role' => '— भूमिका निवडा —',
        'opt_no_roles' => 'डेटाबेसमध्ये कोणतीही सक्रिय भूमिका आढळली नाही',
        'lbl_filter_department' => 'विभागानुसार फिल्टर करा',
        'lbl_optional' => '(पर्यायी)',
        'opt_all_departments' => '— सर्व विभाग —',

        'section_attachment' => 'जोडलेली फाईल (अटॅचमेंट)',
        'section_attachment_desc' => 'पीडीएफ, वर्ड, प्रतिमा, ऑडिओ आणि व्हिडिओ स्वीकारले जातात',
        'drop_zone_text' => 'तुमची फाईल येथे ड्रॉप करा किंवा',
        'drop_zone_browse' => 'ब्राउझ करा',
        'drop_zone_types' => 'पीडीएफ · वर्ड · जेपीजी · पीएनजी · एमपी३ · एमपी४ · आणि इतर (कमाल २० एमबी)',

        'section_schedule' => 'वेळापत्रक',
        'section_schedule_desc' => 'अंतिम मुदत आणि उद्दिष्टे',
        'lbl_due_date' => 'नियत तारीख आणि वेळ',
        'msg_date_past' => 'नियत तारीख भूतकाळातील असू शकत नाही.',
        'lbl_target' => 'लक्ष्य / टप्पा (Milestone)',
        'placeholder_target' => 'उदा. ५० सर्वेक्षण फॉर्म सबमिट करा',

        'section_priority' => 'प्राधान्य पातळी',
        'section_priority_desc' => 'कार्याची निकड सेट करा',
        'priority_low' => 'कमी',
        'priority_medium' => 'मध्यम',
        'priority_high' => 'उच्च',
        'priority_critical' => 'अति-तातडीचे (Critical)',

        'section_preview' => 'थेट पूर्वदृश्य (Live Preview)',
        'lbl_title' => 'शीर्षक',
        'lbl_priority' => 'प्राधान्य',
        'lbl_allocation' => 'वाटप',
        'lbl_attachment' => 'अटॅचमेंट',
        'lbl_none' => 'काहीही नाही',

        'btn_submit' => 'कार्य तयार करा आणि वाटप करा',
        'btn_reset' => 'फॉर्म रिसेट करा',

        'msg_task_visible' => 'कार्य आता नियुक्त केलेल्या कर्मचाऱ्यास (कर्मचाऱ्यांना) दिसेल.',
        'msg_upload_failed' => 'फाईल अपलोड अयशस्वी. कृपया फोल्डर परवानग्या तपासा.',
        'msg_invalid_type' => 'अवैध फाईल प्रकार. फक्त पीडीएफ, वर्ड, प्रतिमा, ऑडिओ आणि व्हिडिओ अपलोड करण्याची परवानगी आहे.',
        'msg_db_unavailable' => 'डेटाबेस कनेक्शन उपलब्ध नाही. कार्य तयार करणे शक्य नाही.',

        'msg_fetching_employees' => 'या भूमिकेचे कर्मचारी शोधत आहे...',
        'msg_employees_receive' => '%d कर्मचाऱ्यांना हे कार्य प्राप्त होईल',
        'msg_employees_receive_preview' => 'कर्मचारी',
        'btn_show_all' => 'सर्व दाखवा',
        'btn_hide' => 'लपवा',
        'msg_no_employees_role' => 'या भूमिकेसह कोणतेही सक्रिय कर्मचारी आढळले नाहीत.',
        'btn_submitting' => 'कार्य तयार करत आहे...',

        'toast_title_req' => 'कार्याचे शीर्षक आवश्यक आहे.',
        'toast_due_req' => 'कृपया नियत तारीख आणि वेळ निवडा.',
        'toast_employee_req' => 'कृपया नियुक्त करण्यासाठी कर्मचारी निवडा.',
        'toast_role_req' => 'कृपया नियुक्त करण्यासाठी भूमिका निवडा.',
        'toast_created' => 'तयार केले!',
        'toast_warning' => 'चेतावणी',
        'toast_success' => 'यशस्वी',
        
        'char_counter' => '%d / २५५ अक्षरे',

        // Wizard pagination
        'wizard_step1' => 'कार्य तपशील आणि वाटप',
        'wizard_step2' => 'संलग्नक, वेळापत्रक आणि प्राधान्य',
        'btn_next' => 'पुढे: वेळापत्रक आणि संलग्नक',
        'btn_prev' => 'मागे: कार्य तपशील',
        'wizard_step_of' => 'पायरी %d पैकी %d'
    ]
];

$t = $translations[$lang];

if (isset($_GET['role']) && in_array($_GET['role'], ['Collector', 'SDO', 'Tehsildar', 'BDO', 'Talathi', 'Gramsevak'])) {
    $_SESSION['user_role']       = $_GET['role'];
    $_SESSION['user_name']       = 'Demo – ' . $_GET['role'];
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'];
    $_SESSION['user_village_id'] = $_SESSION['village_id'];
}

if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role']       = 'Collector';
    $_SESSION['user_name']       = 'Hon. Collector';
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

$sRole      = $_SESSION['user_role'];
$sName      = $_SESSION['user_name'];
$sTalukaId  = (int) ($_SESSION['user_taluka_id']  ?? 1);
$sVillageId = (int) ($_SESSION['user_village_id'] ?? 1);

// Get user role level and redirect Level 3 users back to dashboard
$user_level = $_SESSION['user_level'] ?? null;
if ($user_level === null && !empty($sRole)) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $stmt = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $sRole);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $user_level = (int)$row['role_level'];
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log('create_task role level lookup error: ' . $e->getMessage());
        }
    }
    if ($user_level === null) {
        $roleMap = [
            'Administrator'        => 1,
            'System Administrator' => 1,
            'Collector'            => 1,
            'Additional Collector' => 1,
            'Deputy Collector'     => 1,
            'SDO'                  => 2,
            'Tehsildar'            => 2,
            'BDO'                  => 2,
            'Talathi'              => 3,
            'Gramsevak'            => 3,
        ];
        $user_level = $roleMap[$sRole] ?? 3;
    }
}

if ($user_level > 2) {
    header("Location: dashboard.php?lang=" . $lang);
    exit();
}
// Avatar initials
$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

// ═══════════════════════════════════════════════════════════════════
// HELPER: Create notification + delivery log for one assigned user
// Adapts to the live DB schema visible in phpMyAdmin screenshots.
// ═══════════════════════════════════════════════════════════════════
/**
 * @param mysqli $conn        Active DB connection
 * @param int    $task_id     ID of the newly-created task
 * @param string $task_title  Human-readable task title
 * @param string $due_date    Formatted due date string stored in tasks.due_date
 * @param int    $receiver_id user_id of the employee receiving the notification
 * @param int    $sender_id   user_id of the person who created the task
 * @param string $notif_type  Notification type label (e.g. 'Task Assigned')
 * @return array{ok:bool, notification_id:int|null, error:string}
 */
function createTaskNotification(
    mysqli $conn,
    int    $task_id,
    string $task_title,
    string $due_date,
    int    $receiver_id,
    int    $sender_id,
    string $notif_type = 'Task Assigned'
): array {
    // ── 1. Build the human-readable message ─────────────────────
    $title   = $conn->real_escape_string('New Task Assigned: ' . $task_title);
    $message = $conn->real_escape_string(
        'New task assigned: ' . $task_title .
        '. Deadline: ' . ($due_date ?: 'Not specified')
    );
    $notif_type_safe = $conn->real_escape_string($notif_type);

    // ── 2. Insert into `notifications` ──────────────────────────
    // Schema (from phpMyAdmin): notification_id, notification_type,
    // title, message, task_id, announcement_id, certificate_id,
    // sender_id, receiver_id, status, created_at
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

    // ── 3. Insert into `notification_delivery_logs` ──────────────
    // Schema (from phpMyAdmin): delivery_id, notification_id,
    // channel (enum: System/Email/SMS/WhatsApp/Mobile),
    // delivery_status, delivery_time, remarks
    if ($notification_id !== null) {
        $log_notif_id    = $notification_id;
        $delivery_status = $conn->real_escape_string('Sent');
        $channel         = $conn->real_escape_string('System');

        $log_sql = "INSERT INTO notification_delivery_logs
                        (notification_id, channel, delivery_status, delivery_time)
                    VALUES
                        ($log_notif_id, '$channel', '$delivery_status', NOW())";
        $conn->query($log_sql); // Best-effort; don't fail the task creation
    } else {
        // Notification insert failed – log the failure with null notification_id
        $safe_err = $conn->real_escape_string('Notification insert failed: ' . $error_msg);
        $channel  = $conn->real_escape_string('System');

        $log_fail_sql = "INSERT INTO notification_delivery_logs
                             (notification_id, channel, delivery_status,
                              delivery_time, remarks)
                         VALUES
                             (NULL, '$channel', 'Failed', NOW(), '$safe_err')";
        $conn->query($log_fail_sql);
    }

    return [
        'ok'              => $insert_ok,
        'notification_id' => $notification_id,
        'error'           => $error_msg,
    ];
}

// ═══════════════════════════════════════════════════════════════════
// AJAX: Return users for a given role_id (called by JS fetch)
// ═══════════════════════════════════════════════════════════════════
// AJAX: Return users for a given list of role_ids
if (isset($_GET['ajax']) && $_GET['ajax'] === 'role_users' && (isset($_GET['role_id']) || isset($_GET['role_ids']))) {
    header('Content-Type: application/json');
    if (!$conn) {
        echo json_encode(['users' => [], 'count' => 0, 'error' => 'Database connection unavailable']);
        exit;
    }
    $role_ids_str = $_GET['role_ids'] ?? $_GET['role_id'];
    $role_ids = array_filter(array_map('intval', explode(',', $role_ids_str)));
    
    $users_list = [];
    if (!empty($role_ids)) {
        $ids_in = implode(',', $role_ids);
        $res = $conn->query(
            "SELECT u.user_id, u.full_name, u.designation, u.department_id
               FROM users u
               JOIN roles r ON u.role_id = r.role_id
              WHERE u.role_id IN ($ids_in) AND u.status = 'Active' AND r.role_level >= $user_level
              ORDER BY u.full_name"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users_list[] = [
                    'id'          => (int)$row['user_id'],
                    'full_name'   => $row['full_name'],
                    'designation' => $row['designation'] ?? '',
                ];
            }
        }
    }
    echo json_encode(['users' => $users_list, 'count' => count($users_list)]);
    exit;
}

// AJAX: Return users for a given list of taluka_ids
if (isset($_GET['ajax']) && $_GET['ajax'] === 'taluka_users' && isset($_GET['taluka_ids'])) {
    header('Content-Type: application/json');
    if (!$conn) {
        echo json_encode(['users' => [], 'count' => 0, 'error' => 'Database connection unavailable']);
        exit;
    }
    $taluka_ids = array_filter(array_map('intval', explode(',', $_GET['taluka_ids'])));
    
    $users_list = [];
    if (!empty($taluka_ids)) {
        $ids_in = implode(',', $taluka_ids);
        $res = $conn->query(
            "SELECT u.user_id, u.full_name, u.designation, u.department_id
               FROM users u
               JOIN roles r ON u.role_id = r.role_id
              WHERE u.taluka_id IN ($ids_in) AND u.status = 'Active' AND r.role_level >= $user_level
              ORDER BY u.full_name"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users_list[] = [
                    'id'          => (int)$row['user_id'],
                    'full_name'   => $row['full_name'],
                    'designation' => $row['designation'] ?? '',
                ];
            }
        }
    }
    echo json_encode(['users' => $users_list, 'count' => count($users_list)]);
    exit;
}

// AJAX: Return users for a given list of village_ids
if (isset($_GET['ajax']) && $_GET['ajax'] === 'village_users' && (isset($_GET['village_id']) || isset($_GET['village_ids']))) {
    header('Content-Type: application/json');
    if (!$conn) {
        echo json_encode(['users' => [], 'count' => 0, 'error' => 'Database connection unavailable']);
        exit;
    }
    $village_ids_str = $_GET['village_ids'] ?? $_GET['village_id'];
    $village_ids = array_filter(array_map('intval', explode(',', $village_ids_str)));
    
    $users_list = [];
    if (!empty($village_ids)) {
        $ids_in = implode(',', $village_ids);
        $res = $conn->query(
            "SELECT u.user_id, u.full_name, u.designation, u.department_id
               FROM users u
               JOIN roles r ON u.role_id = r.role_id
              WHERE u.village_id IN ($ids_in) AND u.status = 'Active' AND r.role_level >= $user_level
              ORDER BY u.full_name"
        );
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $users_list[] = [
                    'id'          => (int)$row['user_id'],
                    'full_name'   => $row['full_name'],
                    'designation' => $row['designation'] ?? '',
                ];
            }
        }
    }
    echo json_encode(['users' => $users_list, 'count' => count($users_list)]);
    exit;
}

// ─── Fetch dropdown data ─────────────────────────────────────────────
// users table: primary key = user_id, name column = full_name
$users_result = $conn ? $conn->query(
    "SELECT u.user_id, u.full_name, u.designation 
       FROM users u
       JOIN roles r ON u.role_id = r.role_id
      WHERE u.status = 'Active' AND r.role_level >= $user_level
      ORDER BY u.full_name"
) : false;
// roles table: primary key = role_id, name column = role_name
$roles_result = $conn ? $conn->query(
    "SELECT role_id, role_name, role_level FROM roles WHERE status = 'Active' AND role_level >= $user_level ORDER BY role_level, role_name"
) : false;
// departments table
$departments_result = $conn ? $conn->query(
    "SELECT department_id, department_name FROM departments ORDER BY department_name"
) : false;

// talukas and villages (for By Village allocation)
$talukas_result = $conn ? $conn->query(
    "SELECT taluka_id, taluka_name FROM talukas ORDER BY taluka_name"
) : false;
$villages_result = $conn ? $conn->query(
    "SELECT village_id, village_name, taluka_id FROM villages ORDER BY village_name"
) : false;

// ─── Handle Form Submission ───────────────────────────────────────────
$success_msg    = '';
$error_msg      = '';
$assigned_count = 0; // how many users received the task (for role-based)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$conn) {
        $error_msg = $lang === 'mr' ? 'डेटाबेस कनेक्शन उपलब्ध नाही. कार्य तयार करणे शक्य नाही.' : "Database connection unavailable. Cannot create task.";
    } else {
        // ── Sanitise inputs ────────────────────────────────────────────
        $task_title       = $conn->real_escape_string(trim($_POST['task_title']       ?? ''));
        $task_description = $conn->real_escape_string(trim($_POST['task_description'] ?? ''));
    $allocation_type  = trim($_POST['allocation_type'] ?? 'by_name');
    $priority         = $conn->real_escape_string(trim($_POST['priority']         ?? 'Medium'));
    $task_category    = $conn->real_escape_string(trim($_POST['task_category']    ?? ''));
    $target           = $conn->real_escape_string(trim($_POST['target']           ?? ''));
    $department_id    = !empty($_POST['department_id'])    ? (int)$_POST['department_id']    : null;
    $assigned_role_id = !empty($_POST['assigned_role_id']) ? (int)$_POST['assigned_role_id'] : null;
    
    $assigned_user_ids = [];
    if (isset($_POST['assigned_user_ids']) && is_array($_POST['assigned_user_ids'])) {
        foreach ($_POST['assigned_user_ids'] as $uid) {
            $val = (int)$uid;
            if ($val > 0) $assigned_user_ids[] = $val;
        }
    } elseif (!empty($_POST['assigned_user_id'])) {
        $assigned_user_ids[] = (int)$_POST['assigned_user_id'];
    }
    $created_by       = $_SESSION['user_id'] ?? 1;

    // DB column `due_date` is DATE — strip the time part sent by datetime-local
    $due_date_raw = trim($_POST['due_date'] ?? '');
    $due_date     = '';
    if (!empty($due_date_raw)) {
        // datetime-local sends "YYYY-MM-DDTHH:MM" — convert to "YYYY-MM-DD HH:MM:SS"
        $due_date_ts = strtotime($due_date_raw);
        if ($due_date_ts < time() - 60) { // allow a 60 second grace period for clock drift
            $error_msg = $lang === 'mr' ? 'नियत तारीख भूतकाळातील असू शकत नाही.' : 'Due date cannot be in the past.';
        } else {
            $due_date = $conn->real_escape_string(str_replace('T', ' ', $due_date_raw) . ':05');
        }
    }

    // ── File Upload ────────────────────────────────────────────────
    $attachment_path = null;
    $file_mime       = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/tasks/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $allowed_types = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'audio/mpeg', 'audio/wav', 'audio/ogg',
            'video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv',
        ];
        $file_mime = mime_content_type($_FILES['attachment']['tmp_name']);
        if (in_array($file_mime, $allowed_types)) {
            $file_ext  = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $file_name = 'TASK_' . strtoupper(uniqid()) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_dir . $file_name)) {
                $attachment_path = 'uploads/tasks/' . $file_name;
            } else {
                $error_msg = $lang === 'mr' ? 'फाईल अपलोड अयशस्वी. कृपया फोल्डर परवानग्या तपासा.' : 'File upload failed. Please check folder permissions.';
            }
        } else {
            $error_msg = $lang === 'mr' ? 'अवैध फाईल प्रकार. फक्त पीडीएफ, वर्ड, प्रतिमा, ऑडिओ आणि व्हिडिओ अपलोड करण्याची परवानगी आहे.' : 'Invalid file type. Only PDF, Word, Images, Audio, and Video are allowed.';
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Build a list of user allocation targets to iterate through
    // ─────────────────────────────────────────────────────────────────
    $targets_to_create = [];
    
    if ($allocation_type === 'by_name' && !empty($assigned_user_ids)) {
        foreach ($assigned_user_ids as $uid) {
            $targets_to_create[] = [
                'user_id' => $uid,
                'role_id' => null,
                'type'    => 'by_name'
            ];
        }
    } elseif ($allocation_type === 'by_role' && !empty($_POST['assigned_role_ids'])) {
        foreach ($_POST['assigned_role_ids'] as $rid) {
            $val = (int)$rid;
            if ($val > 0) {
                $targets_to_create[] = [
                    'user_id' => null,
                    'role_id' => $val,
                    'type'    => 'by_role'
                ];
            }
        }
    } elseif ($allocation_type === 'by_taluka' && !empty($_POST['assigned_taluka_ids'])) {
        foreach ($_POST['assigned_taluka_ids'] as $tid) {
            $val = (int)$tid;
            if ($val > 0) {
                $targets_to_create[] = [
                    'user_id'   => null,
                    'role_id'   => null,
                    'taluka_id' => $val,
                    'type'      => 'by_taluka'
                ];
            }
        }
    } elseif ($allocation_type === 'by_village' && !empty($_POST['assigned_village_ids'])) {
        foreach ($_POST['assigned_village_ids'] as $vid) {
            $val = (int)$vid;
            if ($val > 0) {
                $targets_to_create[] = [
                    'user_id'    => null,
                    'role_id'    => null,
                    'village_id' => $val,
                    'type'       => 'by_village'
                ];
            }
        }
    }

    if (empty($targets_to_create) && empty($error_msg)) {
        $error_msg = $lang === 'mr' ? 'कृपया कार्य नियुक्त करण्यासाठी किमान एक लक्ष्य निवडा.' : 'Please select at least one assignment target.';
    }

    if (empty($error_msg)) {
        $task_id_str = ''; // will hold the last created task no string
        
        foreach ($targets_to_create as $tgt_item) {
            $curr_user_id = $tgt_item['user_id'];
            $curr_role_id = $tgt_item['role_id'];
            $curr_taluka_id = $tgt_item['taluka_id'] ?? null;
            $curr_village_id = $tgt_item['village_id'] ?? null;
            $curr_type    = $tgt_item['type'];
            
            $task_district_id = null;
            $task_taluka_id   = $curr_taluka_id;
            $task_village_id  = $curr_village_id;
            $curr_dept_id     = $department_id;
            $curr_assigned_role_id = $curr_role_id;
            
            if ($curr_type === 'by_name' && $curr_user_id) {
                $usr_res = $conn->query(
                    "SELECT department_id, role_id, district_id, taluka_id, village_id
                       FROM users
                      WHERE user_id = $curr_user_id AND status = 'Active'
                      LIMIT 1"
                );
                if ($usr_res && $usr_data = $usr_res->fetch_assoc()) {
                    $curr_dept_id          = !empty($usr_data['department_id']) ? (int)$usr_data['department_id'] : $curr_dept_id;
                    $curr_assigned_role_id = !empty($usr_data['role_id'])       ? (int)$usr_data['role_id']       : $curr_assigned_role_id;
                    $task_district_id      = !empty($usr_data['district_id'])   ? (int)$usr_data['district_id']   : null;
                    $task_taluka_id        = !empty($usr_data['taluka_id'])     ? (int)$usr_data['taluka_id']     : null;
                    $task_village_id       = !empty($usr_data['village_id'])    ? (int)$usr_data['village_id']    : null;
                }
            }

            if ($curr_type === 'by_village' && $curr_village_id) {
                $vil_res = $conn->query("SELECT taluka_id FROM villages WHERE village_id = $curr_village_id LIMIT 1");
                if ($vil_res && $vil_data = $vil_res->fetch_assoc()) {
                    $task_taluka_id = (int)$vil_data['taluka_id'];
                }
            }
            
            // Build nullable SQL literals
            $dept_sql     = $curr_dept_id          ? (int)$curr_dept_id          : 'NULL';
            $role_sql     = $curr_assigned_role_id ? (int)$curr_assigned_role_id : 'NULL';
            $user_sql     = ($curr_type === 'by_name' && $curr_user_id) ? (int)$curr_user_id : 'NULL';
            $district_sql = $task_district_id ? (int)$task_district_id : 'NULL';
            $taluka_sql   = $task_taluka_id   ? (int)$task_taluka_id   : 'NULL';
            $village_sql  = $task_village_id  ? (int)$task_village_id  : 'NULL';
            $cat_sql      = !empty($task_category) ? "'" . $conn->real_escape_string($task_category) . "'" : 'NULL';
            $tgt_sql      = !empty($target)        ? "'" . $conn->real_escape_string($target)        . "'" : 'NULL';
            $due_sql      = !empty($due_date)      ? "'" . $due_date . "'"                                  : 'NULL';

            $tmp_task_no = 'TASK_TMP_' . time() . '_' . rand(100, 999);

            $sql = "INSERT INTO tasks
                        (task_no, task_title, task_description, priority, task_category,
                         department_id, created_by, assigned_role_id, assigned_user_id,
                         district_id, taluka_id, village_id,
                         due_date, status, remarks)
                    VALUES
                        ('" . $conn->real_escape_string($tmp_task_no) . "',
                         '$task_title', '$task_description', '$priority', $cat_sql,
                         $dept_sql, $created_by, $role_sql, $user_sql,
                         $district_sql, $taluka_sql, $village_sql,
                         $due_sql, 'Pending', $tgt_sql)";

            if ($conn->query($sql)) {
                $new_task_id = $conn->insert_id;
                
                // Use actual row count for sequential task_no (no gaps from failed inserts)
                $seq_result  = $conn->query("SELECT COUNT(*) AS cnt FROM tasks WHERE task_id <= $new_task_id");
                $seq_row     = $seq_result ? $seq_result->fetch_assoc() : null;
                $seq_num     = (int)($seq_row['cnt'] ?? $new_task_id);
                $task_id_str = 'TASK_' . str_pad($seq_num, 3, '0', STR_PAD_LEFT);

                // Update task_no with the sequential number
                $conn->query("UPDATE tasks SET task_no = '" . $conn->real_escape_string($task_id_str) . "' WHERE task_id = $new_task_id");

                // ── task_activity_logs ─────────────────────────────────
                $activity_desc = $conn->real_escape_string("Task created and assigned.");
                $conn->query("INSERT INTO task_activity_logs (task_id, user_id, activity_type, description, activity_time) VALUES ($new_task_id, $created_by, 'Task Created', '$activity_desc', NOW())");

                // ── task_remarks ───────────────────────────────────────
                if (!empty($target)) {
                    $conn->query("INSERT INTO task_remarks (task_id, user_id, remark_text, status_after_remark, created_at) VALUES ($new_task_id, $created_by, $tgt_sql, 'Pending', NOW())");
                }

                // ── Attachment record ──────────────────────────────────
                if ($attachment_path && $file_mime) {
                    $safe_path = $conn->real_escape_string($attachment_path);
                    $orig_name = $conn->real_escape_string($_FILES['attachment']['name']);
                    $conn->query(
                        "INSERT INTO task_documents
                             (task_id, file_name, file_path, uploaded_by)
                         VALUES
                             ($new_task_id, '$orig_name', '$safe_path', $created_by)"
                    );
                }

                // ── task_assignments: allocate to users ────────────────
                if ($curr_type === 'by_role' && $curr_assigned_role_id) {
                    $role_users = $conn->query(
                        "SELECT user_id, department_id, district_id, taluka_id, village_id
                           FROM users
                          WHERE role_id = $curr_assigned_role_id AND status = 'Active'"
                    );
                    if ($role_users && $role_users->num_rows > 0) {
                        while ($ru = $role_users->fetch_assoc()) {
                            $uid = (int)$ru['user_id'];
                            $conn->query(
                                "INSERT INTO task_assignments
                                     (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                                 VALUES ($new_task_id, $created_by, $uid, $curr_assigned_role_id, NOW(), 'Pending')"
                            );
                            $assigned_count++;

                            // ── Notification per role-based assigned user ──
                            createTaskNotification(
                                $conn,
                                $new_task_id,
                                $task_title,
                                $due_date,
                                $uid,
                                $created_by
                            );
                        }
                    }
                } elseif ($curr_type === 'by_name' && $curr_user_id) {
                    // Single user assignment inside the loop
                    $role_to_assign = $curr_assigned_role_id ? (int)$curr_assigned_role_id : 'NULL';
                    $conn->query(
                        "INSERT INTO task_assignments (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                         VALUES ($new_task_id, $created_by, $curr_user_id, $role_to_assign, NOW(), 'Pending')"
                    );
                    $assigned_this_item = $conn->affected_rows > 0 ? 1 : 0;
                    $assigned_count += $assigned_this_item;

                    // ── Notification for single assigned user ──────────
                    if ($assigned_this_item > 0) {
                        createTaskNotification(
                            $conn,
                            $new_task_id,
                            $task_title,
                            $due_date,
                            (int)$curr_user_id,
                            $created_by
                        );
                    }
                } elseif ($curr_type === 'by_taluka' && $task_taluka_id) {
                    // Taluka-wise assignment
                    $taluka_users = $conn->query(
                        "SELECT u.user_id, u.department_id, u.district_id, u.taluka_id, u.village_id
                           FROM users u
                           JOIN roles r ON u.role_id = r.role_id
                          WHERE u.taluka_id = $task_taluka_id AND u.status = 'Active' AND r.role_level >= $user_level"
                    );
                    if ($taluka_users && $taluka_users->num_rows > 0) {
                        while ($tu = $taluka_users->fetch_assoc()) {
                            $tuid = (int)$tu['user_id'];
                            $conn->query(
                                "INSERT INTO task_assignments
                                     (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                                 VALUES ($new_task_id, $created_by, $tuid, NULL, NOW(), 'Pending')"
                            );
                            if ($conn->affected_rows > 0) {
                                $assigned_count++;
                                createTaskNotification(
                                    $conn,
                                    $new_task_id,
                                    $task_title,
                                    $due_date,
                                    $tuid,
                                    $created_by
                                );
                            }
                        }
                    }
                } elseif ($curr_type === 'by_village' && $curr_village_id) {
                    // Village-wise: assign to all eligible employees in the selected village
                    $village_users = $conn->query(
                        "SELECT u.user_id, u.department_id, u.district_id, u.taluka_id, u.village_id
                           FROM users u
                           JOIN roles r ON u.role_id = r.role_id
                          WHERE u.village_id = $curr_village_id AND u.status = 'Active' AND r.role_level >= $user_level"
                    );
                    if ($village_users && $village_users->num_rows > 0) {
                        while ($vu = $village_users->fetch_assoc()) {
                            $vuid = (int)$vu['user_id'];
                            $conn->query(
                                "INSERT INTO task_assignments
                                     (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                                 VALUES ($new_task_id, $created_by, $vuid, NULL, NOW(), 'Pending')"
                            );
                            if ($conn->affected_rows > 0) {
                                $assigned_count++;
                                createTaskNotification(
                                    $conn,
                                    $new_task_id,
                                    $task_title,
                                    $due_date,
                                    $vuid,
                                    $created_by
                                );
                            }
                        }
                    }
                }
            }
        }
            $count_label = $assigned_count > 0
                ? ($lang === 'mr'
                    ? " <strong>$assigned_count</strong> कर्मचाऱ्यास नियुक्त केले."
                    : " Assigned to <strong>$assigned_count</strong> employee" . ($assigned_count > 1 ? 's' : '') . '.')
                : '';
            // POST-REDIRECT-GET: redirect with success params to auto-refresh the form
            $redir_lang = urlencode($lang);
            $redir_msg  = urlencode(strip_tags($task_id_str) . '|' . $assigned_count);
            header("Location: create_task.php?lang={$redir_lang}&task_success=1&task_info={$redir_msg}");
            exit;
        } else {
            $error_msg = ($lang === 'mr' ? 'डेटाबेस त्रुटी: ' : 'Database error: ') . $conn->error;
        }
    }
    } // End of if(!$conn) check

// ── Handle success redirect ────────────────────────────────────────────
$success_msg = '';
if (!empty($_GET['task_success']) && !empty($_GET['task_info'])) {
    $info_parts = explode('|', urldecode($_GET['task_info']));
    $s_task_id  = htmlspecialchars($info_parts[0] ?? '');
    $s_count    = (int)($info_parts[1] ?? 0);
    $count_label = $s_count > 0
        ? ($lang === 'mr'
            ? " <strong>$s_count</strong> कर्मचाऱ्यास नियुक्त केले."
            : " Assigned to <strong>$s_count</strong> employee" . ($s_count > 1 ? 's' : '') . '.')
        : '';
    $success_msg = $lang === 'mr'
        ? "कार्य <strong>$s_task_id</strong> यशस्वीरित्या तयार केले!$count_label"
        : "Task <strong>$s_task_id</strong> created successfully!$count_label";
}

// ─── Auto-generate Task No preview (use actual MAX to avoid AUTO_INCREMENT gaps) ─
$result  = $conn ? $conn->query("SELECT COALESCE(MAX(task_id), 0) + 1 AS next_id FROM tasks") : false;
$row     = $result ? $result->fetch_assoc() : null;
$next_id = (int)($row['next_id'] ?? 1);
$task_id_preview = 'TASK_' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
?>
<?php
$pageTitle = $t['breadcrumb_create_task'] . ' - ' . $t['brand_name'];
$pageDesc = $t['page_subtitle'];
$extraHead = <<<'EOT'
    <style>
        /* Form inputs */
        .form-input {
            @apply w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                   bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                   placeholder-slate-400 dark:placeholder-slate-500
                   focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                   transition-colors duration-150;
        }
        .form-label {
            @apply block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5;
        }

        /* Priority badges */
        .badge-low      { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-medium   { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-high     { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-critical { background: #fae8ff; color: #6b21a8; border: 1px solid #e9d5ff; }
        .dark .badge-low      { background: #14532d33; color: #86efac; border-color: #166534; }
        .dark .badge-medium   { background: #78350f33; color: #fde047; border-color: #854d0e; }
        .dark .badge-high     { background: #7f1d1d33; color: #fca5a5; border-color: #991b1b; }
        .dark .badge-critical { background: #4a044e33; color: #d8b4fe; border-color: #6b21a8; }

        /* Drag-drop zone */
        #dropZone {
            border: 2px dashed #cbd5e1;
            transition: border-color 0.2s, background 0.2s;
        }
        #dropZone.drag-over {
            border-color: #1a365d;
            background: #eef2f6;
        }
        .dark #dropZone { border-color: #475569; }
        .dark #dropZone.drag-over { border-color: #60a5fa; background: #1e3a5f22; }

        /* Step indicator */
        .step-dot {
            width: 10px; height: 10px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: background 0.3s;
        }
        .step-dot.active { background: #1a365d; }

        /* ── Wizard Page Stepper ── */
        .wizard-stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .wizard-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            position: relative;
        }
        .wizard-step .ws-num {
            width: 2rem; height: 2rem;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700;
            border: 2px solid #cbd5e1;
            color: #94a3b8;
            background: #fff;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        .dark .wizard-step .ws-num { background: #1e293b; border-color: #475569; color: #64748b; }
        .wizard-step.active .ws-num {
            background: linear-gradient(135deg, #1a365d, #2563eb);
            border-color: #2563eb;
            color: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.15);
        }
        .wizard-step.completed .ws-num {
            background: linear-gradient(135deg, #059669, #10b981);
            border-color: #10b981;
            color: #fff;
        }
        .wizard-step .ws-label {
            font-size: 0.8125rem; font-weight: 600;
            color: #94a3b8;
            transition: color 0.3s;
            white-space: nowrap;
        }
        .wizard-step.active .ws-label { color: #1e293b; }
        .dark .wizard-step.active .ws-label { color: #f1f5f9; }
        .wizard-step.completed .ws-label { color: #059669; }
        .wizard-connector {
            width: 4rem; height: 2px;
            background: #e2e8f0;
            transition: background 0.3s;
            flex-shrink: 0;
        }
        .dark .wizard-connector { background: #334155; }
        .wizard-connector.done { background: linear-gradient(90deg, #10b981, #2563eb); }

        /* Wizard pages */
        .wizard-page { display: none; animation: wizardFadeIn 0.35s ease both; }
        .wizard-page.active { display: block; }
        @keyframes wizardFadeIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Wizard nav buttons */
        .wizard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            gap: 1rem;
        }
        .wizard-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.75rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
        }
        .wizard-nav-btn:hover { transform: translateY(-1px); }
        .wizard-nav-btn:active { transform: translateY(0); }
        .wizard-nav-btn.btn-next {
            background: linear-gradient(135deg, #1a365d, #2563eb);
            color: #fff;
            box-shadow: 0 4px 14px rgba(37,99,235,0.25);
        }
        .wizard-nav-btn.btn-next:hover {
            box-shadow: 0 6px 20px rgba(37,99,235,0.35);
        }
        .wizard-nav-btn.btn-prev {
            background: #fff;
            color: #475569;
            border: 1.5px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .dark .wizard-nav-btn.btn-prev {
            background: #1e293b;
            color: #cbd5e1;
            border-color: #475569;
        }
        .wizard-nav-btn.btn-prev:hover {
            border-color: #94a3b8;
            background: #f8fafc;
        }
        .dark .wizard-nav-btn.btn-prev:hover { background: #334155; }
        .wizard-page-indicator {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #94a3b8;
        }

        /* Animate in */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-in { animation: fadeSlideIn 0.35s ease both; }

        /* Toast notification */
        #toast {
            transition: opacity 0.4s, transform 0.4s;
        }
        #toast.hidden { opacity: 0; pointer-events: none; transform: translateY(20px); }
    </style>
EOT;
include 'include/header.php';
$activePage = 'create_task';
include 'include/sidebar.php';
?>

<!-- ═══════════════════════════════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none block lg:hidden" id="sidebarToggle">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm" aria-label="Breadcrumb">
                <a href="dashboard.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors"><?= htmlspecialchars($t['breadcrumb_dashboard'] ?? 'Dashboard') ?></a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <a href="create_task.php?lang=<?= $lang ?>" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors"><?= htmlspecialchars($t['breadcrumb_task_allocation'] ?? 'Task Allocation') ?></a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['breadcrumb_create_task'] ?? 'Create Task') ?></span>
            </nav>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language Toggle -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = 'create_task.php?' . http_build_query($queryParams);
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
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
                        <?= htmlspecialchars($initials ?? 'U') ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50 text-left">
                    <div class="py-1">
                        <a href="profile_update.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i><?= htmlspecialchars($t['profile_update'] ?? 'User Profile Update') ?>
                        </a>
                        <a href="settings.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="settings" class="w-4 h-4 mr-2 text-slate-400"></i><?= htmlspecialchars($t['profile_settings'] ?? 'Settings') ?>
                        </a>
                        <a href="passwordChange.php?lang=<?= $lang ?? 'en' ?>" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="key" class="w-4 h-4 mr-2 text-slate-400"></i><?= htmlspecialchars($t['profile_password_change'] ?? 'Password Change') ?>
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i><?= htmlspecialchars($t['profile_logout'] ?? 'Logout') ?>
                        </a>
                    </div>
                </div>
            </div></div>
    </header>

    <!-- ═══════════════ MAIN CONTENT ═══════════════ -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- PHP Alert Messages -->
        <?php if ($success_msg): ?>
        <div id="phpAlert" class="mb-6 flex items-start gap-3 p-4 bg-govgreen-50 dark:bg-green-900/20 border border-govgreen-100 dark:border-green-800 rounded-xl animate-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-govgreen-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-govgreen-700 dark:text-green-300"><?= $success_msg ?></p>
                <p class="text-xs text-govgreen-600 dark:text-green-400 mt-0.5"><?= htmlspecialchars($t['msg_task_visible'] ?? 'The task is now visible to the assigned employee(s).') ?></p>
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

        <!-- Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-navy-600 to-navy-500 flex items-center justify-center shadow-md">
                        <i data-lucide="clipboard-plus" class="w-5 h-5 text-white"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title'] ?? 'Create & Allocate Task') ?></h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 ml-13"><?= htmlspecialchars($t['page_subtitle'] ?? 'Assign official tasks to government employees by name or by role/department.') ?></p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <i data-lucide="hash" class="w-3.5 h-3.5 mr-1.5"></i>
                    <?= htmlspecialchars($t['lbl_auto_id'] ?? 'Auto ID:') ?> <span id="taskIdPreview" class="font-bold ml-1"><?= htmlspecialchars($task_id_preview) ?></span>
                </span>
                <a href="dashboard.php?lang=<?= $lang ?>" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    <?= htmlspecialchars($t['btn_back'] ?? 'Back') ?>
                </a>
            </div>
        </div>

        <!-- ── Form Card ── -->
        <?php if ($success_msg): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 text-green-800 dark:text-green-200 animate-in">
            <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-green-600 dark:text-green-300"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold"><?= $lang === 'mr' ? 'यशस्वी!' : 'Task Created Successfully!' ?></p>
                <p class="text-sm mt-0.5"><?= $success_msg ?></p>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
        <div class="mb-6 flex items-start gap-3 p-4 rounded-2xl bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 text-red-800 dark:text-red-200 animate-in">
            <div class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-300"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold"><?= $lang === 'mr' ? 'त्रुटी!' : 'Error!' ?></p>
                <p class="text-sm mt-0.5"><?= $error_msg ?></p>
            </div>
        </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" id="createTaskForm" novalidate>

            <!-- ── Wizard Stepper ── -->
            <div class="wizard-stepper" id="wizardStepper">
                <div class="wizard-step active" id="ws1" onclick="goToWizardPage(1)">
                    <span class="ws-num">1</span>
                    <span class="ws-label"><?= htmlspecialchars($t['wizard_step1'] ?? 'Task Details & Allocation') ?></span>
                </div>
                <div class="wizard-connector" id="wc1"></div>
                <div class="wizard-step" id="ws2" onclick="goToWizardPage(2)">
                    <span class="ws-num">2</span>
                    <span class="ws-label"><?= htmlspecialchars($t['wizard_step2'] ?? 'Attachments, Schedule & Priority') ?></span>
                </div>
            </div>

            <!-- ═══════ WIZARD PAGE 1: Task Details & Allocation ═══════ -->
            <div class="wizard-page active" id="wizardPage1">

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                <!-- ── Left Column (main fields) ── -->
                <div class="xl:col-span-2 space-y-6">

                    <!-- Card: Basic Information -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                <i data-lucide="file-text" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['section_basic_info'] ?? 'Basic Information') ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['section_basic_info_desc'] ?? 'Core task details') ?></p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Task ID (read-only) -->
                            <div>
                                <label class="form-label" for="task_no">
                                    <?= htmlspecialchars($t['lbl_task_id'] ?? 'Task ID') ?> <span class="text-saffron-500">*</span>
                                    <span class="ml-2 text-xs font-normal text-govgreen-600 dark:text-green-400 inline-flex items-center gap-1">
                                        <i data-lucide="zap" class="w-3 h-3"></i> <?= htmlspecialchars($t['lbl_auto_generated'] ?? 'Auto-Generated') ?>
                                    </span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="hash" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" id="task_no" name="task_no"
                                           value="<?= htmlspecialchars($task_id_preview) ?>"
                                           readonly
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-200 dark:border-slate-600 rounded-lg
                                                  bg-slate-50 dark:bg-slate-900 text-slate-500 dark:text-slate-400
                                                  cursor-not-allowed select-none font-mono tracking-widest">
                                </div>
                            </div>

                            <!-- Task Title -->
                            <div>
                                <label class="form-label" for="task_title">
                                    <?= htmlspecialchars($t['lbl_task_title'] ?? 'Task Title') ?> <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="task_title" name="task_title" required
                                       placeholder="<?= htmlspecialchars($t['placeholder_task_title'] ?? 'e.g. Crop Damage Assessment Report – Chandur Block') ?>"
                                       value="<?= htmlspecialchars($_POST['task_title'] ?? '') ?>"
                                       class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                              bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                              placeholder-slate-400 dark:placeholder-slate-500
                                              focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                              transition-colors">
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500" id="titleCounter"><?= sprintf($t['char_counter'] ?? '%d / 255 characters', 0) ?></p>
                            </div>

                            <!-- Task Description -->
                            <div>
                                <label class="form-label" for="task_description">
                                    <?= htmlspecialchars($t['lbl_task_desc'] ?? 'Task Description') ?> <span class="text-red-500">*</span>
                                </label>
                                <textarea id="task_description" name="task_description" required rows="4"
                                          placeholder="<?= htmlspecialchars($t['placeholder_task_desc'] ?? 'Provide a detailed description of the task, objectives, and expected outcomes...') ?>"
                                          class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                 bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                 placeholder-slate-400 dark:placeholder-slate-500
                                                 focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                 transition-colors resize-none"><?= htmlspecialchars($_POST['task_description'] ?? '') ?></textarea>
                            </div>

                            <!-- Task Category -->
                            <div>
                                <label class="form-label" id="lbl_task_category" for="task_category"><?= htmlspecialchars($t['lbl_task_category'] ?? 'Task Category') ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="tag" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" id="task_category" name="task_category"
                                           placeholder="<?= htmlspecialchars($t['placeholder_task_category'] ?? 'e.g. Revenue, Health, Education, Infrastructure') ?>"
                                           value="<?= htmlspecialchars($_POST['task_category'] ?? '') ?>"
                                           list="category_list"
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                  bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                  placeholder-slate-400 dark:placeholder-slate-500
                                                  focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                  transition-colors">
                                    <datalist id="category_list">
                                        <option value="Revenue">
                                        <option value="Health">
                                        <option value="Education">
                                        <option value="Infrastructure">
                                        <option value="Agriculture">
                                        <option value="Social Welfare">
                                        <option value="Water Supply">
                                        <option value="Audit">
                                        <option value="Survey">
                                        <option value="UIDAI / KYC">
                                    </datalist>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Card: Task Allocation -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.08s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-saffron-50 dark:bg-orange-900/30 flex items-center justify-center">
                                <i data-lucide="users-2" class="w-4 h-4 text-saffron-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['section_task_alloc'] ?? 'Task Allocation') ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['section_task_alloc_desc'] ?? 'Assign to an individual or a role/department') ?></p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Allocation Type Toggle -->
                            <div>
                                <label class="form-label">
                                    <?= htmlspecialchars($t['lbl_alloc_type'] ?? 'Allocation Type') ?> <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3" id="allocationTypeGroup">
                                    <!-- By Name -->
                                    <label class="allocation-card cursor-pointer" id="card-byname">
                                        <input type="radio" name="allocation_type" value="by_name"
                                               id="alloc_by_name" class="sr-only"
                                               <?= (($_POST['allocation_type'] ?? 'by_name') === 'by_name') ? 'checked' : '' ?>>
                                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-navy-500 bg-navy-50 dark:bg-navy-900/20 transition-all" id="box-byname">
                                            <div class="w-9 h-9 rounded-full bg-navy-100 dark:bg-navy-800 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="user" class="w-4 h-4 text-navy-600 dark:text-blue-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-navy-700 dark:text-blue-300"><?= htmlspecialchars($t['lbl_by_name'] ?? 'By Name') ?></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['lbl_by_name_desc'] ?? 'Assign to specific employee') ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <!-- By Role -->
                                    <label class="allocation-card cursor-pointer" id="card-byrole">
                                        <input type="radio" name="allocation_type" value="by_role"
                                               id="alloc_by_role" class="sr-only"
                                               <?= (($_POST['allocation_type'] ?? '') === 'by_role') ? 'checked' : '' ?>>
                                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 hover:border-saffron-400 bg-white dark:bg-slate-800 transition-all" id="box-byrole">
                                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="briefcase" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-700 dark:text-white"><?= htmlspecialchars($t['lbl_by_role'] ?? 'By Role') ?></p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['lbl_by_role_desc'] ?? 'Assign to a role/department') ?></p>
                                            </div>
                                        </div>
                                    </label>
                                    <!-- By Taluka -->
                                    <label class="allocation-card cursor-pointer" id="card-bytaluka">
                                        <input type="radio" name="allocation_type" value="by_taluka"
                                               id="alloc_by_taluka" class="sr-only"
                                               <?= (($_POST['allocation_type'] ?? '') === 'by_taluka') ? 'checked' : '' ?>>
                                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 hover:border-sky-400 bg-white dark:bg-slate-800 transition-all" id="box-bytaluka">
                                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="map" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-700 dark:text-white">By Taluka</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Assign all in a Taluka</p>
                                            </div>
                                        </div>
                                    </label>
                                    <!-- By Village -->
                                    <label class="allocation-card cursor-pointer" id="card-byvillage">
                                        <input type="radio" name="allocation_type" value="by_village"
                                               id="alloc_by_village" class="sr-only"
                                               <?= (($_POST['allocation_type'] ?? '') === 'by_village') ? 'checked' : '' ?>>
                                        <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 hover:border-govgreen-400 bg-white dark:bg-slate-800 transition-all" id="box-byvillage">
                                            <div class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center flex-shrink-0">
                                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-700 dark:text-white">By Village</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Assign all in a village</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- By Name: Employee Checkbox List -->
                            <div id="byNameSection" class="space-y-4 transition-all">
                                <div>
                                    <label class="form-label mb-2 block">
                                        <?= htmlspecialchars($t['lbl_select_employee'] ?? 'Select Employee') ?> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="max-h-60 overflow-y-auto p-3 rounded-lg border border-slate-300 dark:border-slate-650 bg-white dark:bg-slate-800 space-y-1" id="employee_checkboxes_container">
                                        <?php
                                        if ($users_result && $users_result->num_rows > 0) {
                                            $users_result->data_seek(0);
                                            $selected_ids = $_POST['assigned_user_ids'] ?? (isset($_POST['assigned_user_id']) ? [$_POST['assigned_user_id']] : []);
                                            while ($u = $users_result->fetch_assoc()):
                                                $checked = in_array($u['user_id'], $selected_ids) ? 'checked' : '';
                                        ?>
                                        <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors">
                                            <input type="checkbox" name="assigned_user_ids[]" value="<?= (int)$u['user_id'] ?>" <?= $checked ?> class="w-4 h-4 text-navy-600 dark:text-blue-500 border-slate-300 dark:border-slate-600 rounded focus:ring-navy-500">
                                            <span class="text-sm text-slate-750 dark:text-slate-200">
                                                <?= htmlspecialchars($u['full_name']) ?><?= !empty($u['designation']) ? ' — ' . htmlspecialchars($u['designation']) : '' ?>
                                            </span>
                                        </label>
                                        <?php endwhile; } else { ?>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 p-2"><?= htmlspecialchars($t['opt_no_employees'] ?? 'No active employees found in database') ?></p>
                                        <?php } ?>
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Select one or more employees from the list above.</p>
                                </div>
                            </div>

                            <!-- By Role: Multiple Roles Selection -->
                            <div id="byRoleSection" class="space-y-4 hidden transition-all">
                                <div>
                                    <label class="form-label mb-2 block">
                                        Select Roles <span class="text-red-500">*</span>
                                    </label>
                                    <div class="max-h-60 overflow-y-auto p-3 rounded-lg border border-slate-300 dark:border-slate-650 bg-white dark:bg-slate-800 space-y-1">
                                        <?php
                                        if ($roles_result && $roles_result->num_rows > 0) {
                                            $roles_result->data_seek(0);
                                            $selected_role_ids = $_POST['assigned_role_ids'] ?? [];
                                            while ($r = $roles_result->fetch_assoc()):
                                                $checked = in_array($r['role_id'], $selected_role_ids) ? 'checked' : '';
                                                $role_key = 'role_' . strtolower(str_replace(' ', '_', trim($r['role_name'])));
                                                $role_name_display = $t[$role_key] ?? $r['role_name'];
                                        ?>
                                        <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors">
                                            <input type="checkbox" name="assigned_role_ids[]" value="<?= (int)$r['role_id'] ?>" <?= $checked ?> class="w-4 h-4 text-navy-600 dark:text-blue-500 border-slate-300 dark:border-slate-600 rounded focus:ring-navy-500 role-checkbox">
                                            <span class="text-sm text-slate-750 dark:text-slate-200">
                                                <?= htmlspecialchars($role_name_display) ?>
                                            </span>
                                        </label>
                                        <?php endwhile; } else { ?>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 p-2"><?= htmlspecialchars($t['opt_no_roles'] ?? 'No active roles found in database') ?></p>
                                        <?php } ?>
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Select one or more roles from the list above.</p>
                                </div>

                                <!-- Live: users-for-role preview panel -->
                                <div id="roleUsersPanel" class="hidden">
                                    <!-- Loading state -->
                                    <div id="roleUsersLoading" class="hidden flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 py-2">
                                        <svg class="animate-spin w-4 h-4 text-navy-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                        </svg>
                                        <?= htmlspecialchars($t['msg_fetching_employees'] ?? 'Fetching employees with this role…') ?>
                                    </div>

                                    <!-- Count badge -->
                                    <div id="roleUsersBadge" class="hidden flex items-center justify-between p-3 bg-saffron-50 dark:bg-orange-900/20 border border-saffron-100 dark:border-orange-800 rounded-xl">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="users" class="w-4 h-4 text-saffron-600 dark:text-orange-400"></i>
                                            <span class="text-sm font-medium text-saffron-700 dark:text-orange-300">
                                                <?php
                                                if ($lang === 'mr') {
                                                    echo '<span id="roleUserCount">0</span> कर्मचाऱ्यांना हे कार्य प्राप्त होईल';
                                                } else {
                                                    echo '<span id="roleUserCount">0</span> employee(s) will receive this task';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                        <button type="button" onclick="toggleRoleUsersList()" id="toggleUsersBtn"
                                                class="text-xs font-medium text-saffron-600 dark:text-orange-400 hover:underline">
                                            <?= htmlspecialchars($t['btn_show_all'] ?? 'Show all') ?>
                                        </button>
                                    </div>

                                    <!-- No users warning -->
                                    <div id="roleUsersNone" class="hidden flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        <?= htmlspecialchars($t['msg_no_employees_role'] ?? 'No active employees found with this role.') ?>
                                    </div>

                                    <!-- Expandable user list -->
                                    <div id="roleUsersList" class="hidden mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                    </div>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label class="form-label" for="department_id"><?= htmlspecialchars($t['lbl_filter_department'] ?? 'Filter by Department') ?> <span class="text-xs font-normal text-slate-400"><?= htmlspecialchars($t['lbl_optional'] ?? '(optional)') ?></span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="department_id" name="department_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value=""><?= htmlspecialchars($t['opt_all_departments'] ?? '— All Departments —') ?></option>
                                            <?php
                                            if ($departments_result && $departments_result->num_rows > 0) {
                                                $departments_result->data_seek(0);
                                                while ($d = $departments_result->fetch_assoc()):
                                                    $sel = (isset($_POST['department_id']) && $_POST['department_id'] == $d['department_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$d['department_id'] ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($d['department_name']) ?>
                                            </option>
                                            <?php endwhile; } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Taluka: Multiple Talukas Selection -->
                            <div id="byTalukaSection" class="space-y-4 hidden transition-all">
                                <div>
                                    <label class="form-label mb-2 block">
                                        Select Talukas <span class="text-red-500">*</span>
                                    </label>
                                    <div class="max-h-60 overflow-y-auto p-3 rounded-lg border border-slate-300 dark:border-slate-650 bg-white dark:bg-slate-800 space-y-1">
                                        <?php
                                        if ($talukas_result && $talukas_result->num_rows > 0) {
                                            $talukas_result->data_seek(0);
                                            $selected_taluka_ids = $_POST['assigned_taluka_ids'] ?? [];
                                            while ($tk = $talukas_result->fetch_assoc()):
                                                $checked = in_array($tk['taluka_id'], $selected_taluka_ids) ? 'checked' : '';
                                        ?>
                                        <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors">
                                            <input type="checkbox" name="assigned_taluka_ids[]" value="<?= (int)$tk['taluka_id'] ?>" <?= $checked ?> class="w-4 h-4 text-navy-600 dark:text-blue-500 border-slate-300 dark:border-slate-600 rounded focus:ring-navy-500 taluka-checkbox">
                                            <span class="text-sm text-slate-750 dark:text-slate-200">
                                                <?= htmlspecialchars($tk['taluka_name']) ?>
                                            </span>
                                        </label>
                                        <?php endwhile; } else { ?>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 p-2">No active talukas found in database</p>
                                        <?php } ?>
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Select one or more talukas from the list above.</p>
                                </div>

                                <!-- Live: users-for-taluka preview panel -->
                                <div id="talukaUsersPanel" class="hidden">
                                    <div id="talukaUsersLoading" class="hidden flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 py-2">
                                        <svg class="animate-spin w-4 h-4 text-navy-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                        </svg>
                                        Fetching employees in selected taluka(s)…
                                    </div>
                                    <div id="talukaUsersBadge" class="hidden flex items-center justify-between p-3 bg-sky-50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900 rounded-xl">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="users" class="w-4 h-4 text-sky-600 dark:text-sky-400"></i>
                                            <span class="text-sm font-medium text-sky-700 dark:text-sky-300">
                                                <span id="talukaUserCount">0</span> employee(s) will receive this task
                                            </span>
                                        </div>
                                        <button type="button" onclick="toggleTalukaUsersList()" id="toggleTalukaUsersBtn"
                                                class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:underline">
                                            Show all
                                        </button>
                                    </div>
                                    <div id="talukaUsersNone" class="hidden flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        No active employees found in selected taluka(s).
                                    </div>
                                    <div id="talukaUsersList" class="hidden mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                    </div>
                                </div>
                            </div>

                            <!-- By Village: Filter by Taluka + Multiple Villages Selection -->
                            <div id="byVillageSection" class="space-y-4 hidden transition-all">
                                <!-- Taluka filter dropdown -->
                                <div>
                                    <label class="form-label" for="filter_taluka_id_village">Filter Villages by Taluka</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="filter_taluka_id_village" class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-650 rounded-lg
                                                               bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                               focus:outline-none focus:ring-2 focus:ring-govgreen-500 focus:border-govgreen-500
                                                               transition-colors appearance-none">
                                            <option value="">— Show All Talukas —</option>
                                            <?php
                                            if ($talukas_result && $talukas_result->num_rows > 0) {
                                                $talukas_result->data_seek(0);
                                                while ($tk = $talukas_result->fetch_assoc()):
                                            ?>
                                            <option value="<?= (int)$tk['taluka_id'] ?>">
                                                <?= htmlspecialchars($tk['taluka_name']) ?>
                                            </option>
                                            <?php endwhile; } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Village selection checkboxes -->
                                <div>
                                    <label class="form-label mb-2 block">
                                        Select Villages <span class="text-red-500">*</span>
                                    </label>
                                    <div class="max-h-60 overflow-y-auto p-3 rounded-lg border border-slate-300 dark:border-slate-650 bg-white dark:bg-slate-800 space-y-1">
                                        <?php
                                        if ($villages_result && $villages_result->num_rows > 0) {
                                            $villages_result->data_seek(0);
                                            $selected_village_ids = $_POST['assigned_village_ids'] ?? [];
                                            while ($vl = $villages_result->fetch_assoc()):
                                                $checked = in_array($vl['village_id'], $selected_village_ids) ? 'checked' : '';
                                        ?>
                                        <label class="flex items-center space-x-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors village-checkbox-label" data-taluka="<?= (int)$vl['taluka_id'] ?>">
                                            <input type="checkbox" name="assigned_village_ids[]" value="<?= (int)$vl['village_id'] ?>" <?= $checked ?> class="w-4 h-4 text-navy-600 dark:text-blue-500 border-slate-300 dark:border-slate-600 rounded focus:ring-navy-500 village-checkbox">
                                            <span class="text-sm text-slate-750 dark:text-slate-200">
                                                <?= htmlspecialchars($vl['village_name']) ?>
                                            </span>
                                        </label>
                                        <?php endwhile; } else { ?>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 p-2">No active villages found in database</p>
                                        <?php } ?>
                                    </div>
                                    <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">Select one or more villages from the list above.</p>
                                </div>

                                <!-- Village user preview panel -->
                                <div id="villageUsersPanel" class="hidden space-y-3">
                                    <div id="villageUsersLoading" class="hidden flex items-center gap-2 text-xs text-slate-500">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Fetching employees in selected village(s)…
                                    </div>
                                    <div id="villageUsersBadge" class="hidden flex items-center justify-between">
                                        <span class="text-xs text-slate-600 dark:text-slate-400">
                                            <span id="villageUserCount">0</span> employee(s) will receive this task
                                        </span>
                                        <button type="button" onclick="toggleVillageUsersList()" id="toggleVillageUsersBtn"
                                                class="text-xs font-medium text-govgreen-600 dark:text-green-400 hover:underline">
                                            Show all
                                        </button>
                                    </div>
                                    <div id="villageUsersNone" class="hidden flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        No active employees found with selected village(s).
                                    </div>
                                    <div id="villageUsersList" class="hidden mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Card: Attachment Upload -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.12s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                                <i data-lucide="paperclip" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['section_attachment'] ?? 'Attachment') ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['section_attachment_desc'] ?? 'PDF, Word, Images, Audio & Video accepted') ?></p>
                            </div>
                        </div>
                        <div class="p-6">
                            <!-- Drop Zone -->
                            <div id="dropZone"
                                 class="rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer"
                                 onclick="document.getElementById('attachment').click()">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-3">
                                    <i data-lucide="upload-cloud" class="w-7 h-7 text-slate-400"></i>
                                </div>
                                <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                    <?= htmlspecialchars($t['drop_zone_text'] ?? 'Drop your file here or') ?> <span class="text-navy-600 dark:text-blue-400 underline"><?= htmlspecialchars($t['drop_zone_browse'] ?? 'browse') ?></span>
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1"><?= htmlspecialchars($t['drop_zone_types'] ?? 'PDF · Word · JPG · PNG · MP3 · MP4 · and more (max 20 MB)') ?></p>
                                <input type="file" id="attachment" name="attachment"
                                       class="hidden"
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp,.mp3,.wav,.ogg,.mp4,.avi,.mov,.wmv">
                            </div>

                            <!-- File Preview -->
                            <div id="filePreview" class="hidden mt-4">
                                <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div id="fileIcon" class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="file" class="w-5 h-5"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p id="fileName" class="text-sm font-medium text-slate-800 dark:text-white truncate"></p>
                                        <p id="fileSize" class="text-xs text-slate-400 mt-0.5"></p>
                                    </div>
                                    <button type="button" onclick="clearFile()" class="text-slate-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="x-circle" class="w-5 h-5"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- end left column -->

                <!-- ── Right Column (metadata) ── -->
                <div class="space-y-6">

                    <!-- Card: Scheduling (Attractive Gradient/Design) -->
                    <div class="bg-gradient-to-r from-amber-50/50 to-orange-50/30 dark:from-slate-800 dark:to-slate-900 rounded-2xl shadow-md border-2 border-orange-500/20 overflow-hidden animate-in hover:shadow-lg transition-all" style="animation-delay:0.05s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-orange-200/30 dark:border-slate-700 bg-gradient-to-r from-orange-100/50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
                                <i data-lucide="calendar-clock" class="w-5 h-5 text-orange-600 dark:text-orange-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($t['section_schedule'] ?? 'Schedule') ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['section_schedule_desc'] ?? 'Deadlines & targets') ?></p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Due Date & Time -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2" for="due_date">
                                    <?= htmlspecialchars($t['lbl_due_date'] ?? 'Due Date & Time') ?> <span class="text-red-500">*</span>
                               </label> 
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="w-4 h-4 text-orange-500"></i>
                                    </div>
                                    <input type="datetime-local" id="due_date" name="due_date" required
                                           value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>"
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border-2 border-orange-200 dark:border-slate-700 rounded-lg
                                                  bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                  focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20
                                                  transition-colors">
                                </div>
                                <p id="dueDateWarning" class="hidden mt-1 text-xs text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i> <?= htmlspecialchars($t['msg_date_past'] ?? 'Due date cannot be in the past.') ?>
                                </p>
                            </div>

                            <!-- Target / Milestone -->
                            <div>
                                <label class="form-label" for="target"><?= htmlspecialchars($t['lbl_target'] ?? 'Target / Milestone') ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="target" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" id="target" name="target"
                                           placeholder="<?= htmlspecialchars($t['placeholder_target'] ?? 'e.g. Submit 50 survey forms') ?>"
                                           value="<?= htmlspecialchars($_POST['target'] ?? '') ?>"
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                  bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                  placeholder-slate-400 dark:placeholder-slate-500
                                                  focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                  transition-colors">
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Card: Priority -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.10s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                                <i data-lucide="flag" class="w-4 h-4 text-red-500 dark:text-red-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white"><?= htmlspecialchars($t['section_priority'] ?? 'Priority Level') ?></h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($t['section_priority_desc'] ?? 'Set task urgency') ?></p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="priorityGroup">
                                <?php
                                $priorities = [
                                    'Low'      => ['icon' => 'arrow-down', 'cls' => 'badge-low',      'ring' => 'border-green-400', 'lbl' => $t['priority_low'] ?? 'Low'],
                                    'Medium'   => ['icon' => 'minus',      'cls' => 'badge-medium',    'ring' => 'border-yellow-400', 'lbl' => $t['priority_medium'] ?? 'Medium'],
                                    'High'     => ['icon' => 'arrow-up',   'cls' => 'badge-high',      'ring' => 'border-red-400', 'lbl' => $t['priority_high'] ?? 'High'],
                                    'Critical' => ['icon' => 'zap',        'cls' => 'badge-critical',  'ring' => 'border-purple-400', 'lbl' => $t['priority_critical'] ?? 'Critical'],
                                ];
                                $sel_priority = $_POST['priority'] ?? 'Medium';
                                foreach ($priorities as $pname => $pdata):
                                    $checked = $sel_priority === $pname ? 'checked' : '';
                                    $active  = $sel_priority === $pname ? 'ring-2 ' . $pdata['ring'] : 'border-slate-200 dark:border-slate-600 hover:border-slate-300 dark:hover:border-slate-500';
                                ?>
                                <label class="cursor-pointer priority-card" data-priority="<?= $pname ?>">
                                    <input type="radio" name="priority" value="<?= $pname ?>" <?= $checked ?> class="sr-only priority-radio">
                                    <div class="<?= $pdata['cls'] ?> <?= $active ?> p-3 rounded-xl border-2 transition-all flex flex-col items-center gap-1.5 text-center priority-box" data-priority="<?= $pname ?>">
                                        <i data-lucide="<?= $pdata['icon'] ?>" class="w-4 h-4"></i>
                                        <span class="text-xs font-semibold"><?= htmlspecialchars($pdata['lbl']) ?></span>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <!-- Hidden input that gets the chosen priority value for submission -->
                            <input type="hidden" name="priority" id="priorityValue" value="<?= htmlspecialchars($sel_priority) ?>">
                        </div>
                    </div>

                    <!-- Card: Summary Preview -->
                    <div class="bg-gradient-to-br from-navy-600 to-navy-700 rounded-2xl shadow-md overflow-hidden animate-in" style="animation-delay:0.15s">
                        <div class="px-6 py-4 border-b border-white/10">
                            <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                                <i data-lucide="eye" class="w-4 h-4 opacity-70"></i>
                                <?= htmlspecialchars($t['section_preview'] ?? 'Live Preview') ?>
                            </h2>
                        </div>
                        <div class="p-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_task_id'] ?? 'Task ID') ?></span>
                                <span class="text-white font-mono font-bold" id="prevTaskId"><?= htmlspecialchars($task_id_preview) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_title'] ?? 'Title') ?></span>
                                <span class="text-white font-medium truncate max-w-[130px] text-right" id="prevTitle">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_priority'] ?? 'Priority') ?></span>
                                <span id="prevPriority" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800"><?= htmlspecialchars($t['priority_medium'] ?? 'Medium') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_allocation'] ?? 'Allocation') ?></span>
                                <span class="text-white" id="prevAllocation"><?= htmlspecialchars($t['lbl_by_name'] ?? 'By Name') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_due_date'] ?? 'Due Date & Time') ?></span>
                                <span class="text-white" id="prevDue">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60"><?= htmlspecialchars($t['lbl_attachment'] ?? 'Attachment') ?></span>
                                <span class="text-white/70" id="prevAttachment"><?= htmlspecialchars($t['lbl_none'] ?? 'None') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit & Reset Buttons -->
                    <div class="space-y-3 animate-in" style="animation-delay:0.18s">
                        <button type="submit" id="submitBtn"
                                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl
                                       bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600
                                       text-white text-sm font-semibold shadow-lg shadow-navy-600/30
                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:ring-offset-2
                                       transition-all duration-200 hover:scale-[1.02] active:scale-[0.98]">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span id="submitBtnText"><?= htmlspecialchars($t['btn_submit'] ?? 'Create & Allocate Task') ?></span>
                        </button>
                        <button type="reset" onclick="resetForm()"
                                class="w-full flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl
                                       border border-slate-300 dark:border-slate-600
                                       bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700
                                       text-slate-700 dark:text-slate-200 text-sm font-medium
                                       focus:outline-none transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            <?= htmlspecialchars($t['btn_reset'] ?? 'Reset Form') ?>
                        </button>
                    </div>

                </div><!-- end right column -->

            </div><!-- end grid -->

        </form>

    </main><!-- end main -->

</div><!-- end main wrapper -->

<!-- ─── AI Chatbot Floating Widget ─── -->
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

<!-- ═══════════════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════════════════ -->
<script>
    // ── Lucide Icons ─────────────────────────────────────────────
    lucide.createIcons();



    // ── Sidebar Toggle ───────────────────────────────────────────
    const sidebar       = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    sidebarToggle.addEventListener('click', () => {
        if (sidebar.style.display === 'none') {
            sidebar.style.display = 'flex';
        } else {
            sidebar.style.display = 'none';
        }
    });

    // ── Allocation Type Toggle ───────────────────────────────────
    const byNameRadio      = document.getElementById('alloc_by_name');
    const byRoleRadio      = document.getElementById('alloc_by_role');
    const byTalukaRadio    = document.getElementById('alloc_by_taluka');
    const byVillageRadio   = document.getElementById('alloc_by_village');
    const byNameSection    = document.getElementById('byNameSection');
    const byRoleSection    = document.getElementById('byRoleSection');
    const byTalukaSection  = document.getElementById('byTalukaSection');
    const byVillageSection = document.getElementById('byVillageSection');
    const boxByName        = document.getElementById('box-byname');
    const boxByRole        = document.getElementById('box-byrole');
    const boxByTaluka      = document.getElementById('box-bytaluka');
    const boxByVillage     = document.getElementById('box-byvillage');
    const prevAllocation   = document.getElementById('prevAllocation');

    // Role section preview elements
    const rolePanel        = document.getElementById('roleUsersPanel');
    const roleBadge        = document.getElementById('roleUsersBadge');
    const roleNone         = document.getElementById('roleUsersNone');
    const roleLoading      = document.getElementById('roleUsersLoading');
    const roleList         = document.getElementById('roleUsersList');
    const roleCountEl      = document.getElementById('roleUserCount');

    // Taluka section preview elements
    const talukaPanel      = document.getElementById('talukaUsersPanel');
    const talukaBadge      = document.getElementById('talukaUsersBadge');
    const talukaNone       = document.getElementById('talukaUsersNone');
    const talukaLoading    = document.getElementById('talukaUsersLoading');
    const talukaList       = document.getElementById('talukaUsersList');
    const talukaCountEl    = document.getElementById('talukaUserCount');

    // Village section preview elements
    const villagePanel     = document.getElementById('villageUsersPanel');
    const villageBadge     = document.getElementById('villageUsersBadge');
    const villageNone      = document.getElementById('villageUsersNone');
    const villageLoading   = document.getElementById('villageUsersLoading');
    const villageList      = document.getElementById('villageUsersList');
    const villageCountEl   = document.getElementById('villageUserCount');
    const talukaSelectVillage = document.getElementById('filter_taluka_id_village');

    let roleUsersListVisible    = false;
    let talukaUsersListVisible  = false;
    let villageUsersListVisible = false;

    // Dynamic translations for JS
    const langCode = '<?= $lang ?>';
    const jsTranslations = {
        ByName:    '<?= addslashes($t['lbl_by_name'] ?? 'By Name') ?>',
        ByRole:    '<?= addslashes($t['lbl_by_role'] ?? 'By Role') ?>',
        ByTaluka:  'By Taluka',
        ByVillage: 'By Village',
        ByRoleUsers:    '<?= addslashes($t['lbl_by_role'] ?? 'By Role') ?> (%count%)',
        ByTalukaUsers:  'By Taluka (%count%)',
        ByVillageUsers: 'By Village (%count%)',
        Low:      '<?= addslashes($t['priority_low'] ?? 'Low') ?>',
        Medium:   '<?= addslashes($t['priority_medium'] ?? 'Medium') ?>',
        High:     '<?= addslashes($t['priority_high'] ?? 'High') ?>',
        Critical: '<?= addslashes($t['priority_critical'] ?? 'Critical') ?>',
        none:     '<?= addslashes($t['lbl_none'] ?? 'None') ?>',
        toast_title_req:    '<?= addslashes($t['toast_title_req'] ?? 'Task title is required.') ?>',
        toast_due_req:      '<?= addslashes($t['toast_due_req'] ?? 'Please select a due date & time.') ?>',
        toast_employee_req: '<?= addslashes($t['toast_employee_req'] ?? 'Please select an employee to assign.') ?>',
        toast_role_req:     '<?= addslashes($t['toast_role_req'] ?? 'Please select a role to assign.') ?>',
        toast_taluka_req:   'Please select a taluka to assign.',
        toast_village_req:  'Please select a village to assign.',
        btn_submitting: '<?= addslashes($t['btn_submitting'] ?? 'Creating Task…') ?>',
        btn_show_all:   '<?= addslashes($t['btn_show_all'] ?? 'Show all') ?>',
        btn_hide:       '<?= addslashes($t['btn_hide'] ?? 'Hide') ?>',
        msg_fetching_employees: '<?= addslashes($t['msg_fetching_employees'] ?? 'Fetching employees with this role…') ?>'
    };

    const clsActive   = 'flex items-center gap-3 p-4 rounded-xl border-2 transition-all';
    const clsInactive = 'flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 transition-all';

    function setAllocationUI(type) {
        // Hide all sections
        byNameSection.classList.add('hidden');
        byRoleSection.classList.add('hidden');
        byTalukaSection.classList.add('hidden');
        byVillageSection.classList.add('hidden');
        // Reset all card borders
        boxByName.className    = clsInactive;
        boxByRole.className    = clsInactive;
        boxByTaluka.className  = clsInactive;
        boxByVillage.className = clsInactive;

        if (type === 'by_name') {
            byNameSection.classList.remove('hidden');
            boxByName.className = clsActive + ' border-navy-500 bg-navy-50 dark:bg-navy-900/20';
            prevAllocation.textContent = jsTranslations.ByName;
        } else if (type === 'by_role') {
            byRoleSection.classList.remove('hidden');
            boxByRole.className = clsActive + ' border-saffron-500 bg-saffron-50 dark:bg-orange-900/20';
            prevAllocation.textContent = jsTranslations.ByRole;
            updateRoleUsersPreview();
        } else if (type === 'by_taluka') {
            byTalukaSection.classList.remove('hidden');
            boxByTaluka.className = clsActive + ' border-sky-550 bg-sky-50 dark:bg-sky-900/20';
            prevAllocation.textContent = jsTranslations.ByTaluka;
            updateTalukaUsersPreview();
        } else if (type === 'by_village') {
            byVillageSection.classList.remove('hidden');
            boxByVillage.className = clsActive + ' border-govgreen-500 bg-green-50 dark:bg-green-900/20';
            prevAllocation.textContent = jsTranslations.ByVillage;
            filterVillagesByTaluka(talukaSelectVillage.value);
            updateVillageUsersPreview();
        }
        lucide.createIcons();
    }

    // ── AJAX: fetch users for the selected roles ──────────────────
    function updateRoleUsersPreview() {
        const checkedRoles = Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => cb.value);
        if (checkedRoles.length === 0) {
            rolePanel.classList.add('hidden');
            prevAllocation.textContent = jsTranslations.ByRole;
            return;
        }
        rolePanel.classList.remove('hidden');
        roleLoading.classList.remove('hidden');
        roleBadge.classList.add('hidden');
        roleNone.classList.add('hidden');
        roleList.classList.add('hidden');
        roleList.innerHTML = '';
        roleUsersListVisible = false;
        const btn = document.getElementById('toggleUsersBtn');
        if (btn) btn.textContent = jsTranslations.btn_show_all;

        const roleIdsParam = checkedRoles.join(',');
        fetch(`create_task.php?ajax=role_users&role_ids=${roleIdsParam}`)
            .then(r => r.json())
            .then(data => {
                roleLoading.classList.add('hidden');
                if (data.count === 0) {
                    roleNone.classList.remove('hidden');
                } else {
                    roleBadge.classList.remove('hidden');
                    roleCountEl.textContent = data.count;
                    roleList.innerHTML = buildUserListHTML(data.users);
                    lucide.createIcons();
                }
                prevAllocation.textContent = jsTranslations.ByRoleUsers.replace('%count%', data.count);
            })
            .catch(() => { roleLoading.classList.add('hidden'); roleNone.classList.remove('hidden'); });
    }

    function toggleRoleUsersList() {
        roleUsersListVisible = !roleUsersListVisible;
        roleList.classList.toggle('hidden', !roleUsersListVisible);
        document.getElementById('toggleUsersBtn').textContent = roleUsersListVisible ? jsTranslations.btn_hide : jsTranslations.btn_show_all;
    }

    // ── AJAX: fetch users for selected talukas ────────────────────
    function updateTalukaUsersPreview() {
        const checkedTalukas = Array.from(document.querySelectorAll('.taluka-checkbox:checked')).map(cb => cb.value);
        if (checkedTalukas.length === 0) {
            talukaPanel.classList.add('hidden');
            prevAllocation.textContent = jsTranslations.ByTaluka;
            return;
        }
        talukaPanel.classList.remove('hidden');
        talukaLoading.classList.remove('hidden');
        talukaBadge.classList.add('hidden');
        talukaNone.classList.add('hidden');
        talukaList.classList.add('hidden');
        talukaList.innerHTML = '';
        talukaUsersListVisible = false;
        const btn = document.getElementById('toggleTalukaUsersBtn');
        if (btn) btn.textContent = jsTranslations.btn_show_all;

        const talukaIdsParam = checkedTalukas.join(',');
        fetch(`create_task.php?ajax=taluka_users&taluka_ids=${talukaIdsParam}`)
            .then(r => r.json())
            .then(data => {
                talukaLoading.classList.add('hidden');
                if (data.count === 0) {
                    talukaNone.classList.remove('hidden');
                } else {
                    talukaBadge.classList.remove('hidden');
                    talukaCountEl.textContent = data.count;
                    talukaList.innerHTML = buildUserListHTML(data.users);
                    lucide.createIcons();
                }
                prevAllocation.textContent = jsTranslations.ByTalukaUsers.replace('%count%', data.count);
            })
            .catch(() => { talukaLoading.classList.add('hidden'); talukaNone.classList.remove('hidden'); });
    }

    function toggleTalukaUsersList() {
        talukaUsersListVisible = !talukaUsersListVisible;
        talukaList.classList.toggle('hidden', !talukaUsersListVisible);
        document.getElementById('toggleTalukaUsersBtn').textContent = talukaUsersListVisible ? jsTranslations.btn_hide : jsTranslations.btn_show_all;
    }

    // ── Taluka → Village cascade filter ────────────────────────
    function filterVillagesByTaluka(talukaId) {
        const labels = document.querySelectorAll('.village-checkbox-label');
        labels.forEach(label => {
            if (!talukaId || label.dataset.taluka === talukaId) {
                label.style.display = '';
            } else {
                label.style.display = 'none';
            }
        });
    }

    talukaSelectVillage.addEventListener('change', () => {
        filterVillagesByTaluka(talukaSelectVillage.value);
    });

    // ── AJAX: fetch users for the selected villages ───────────────
    function updateVillageUsersPreview() {
        const checkedVillages = Array.from(document.querySelectorAll('.village-checkbox:checked')).map(cb => cb.value);
        if (checkedVillages.length === 0) {
            villagePanel.classList.add('hidden');
            prevAllocation.textContent = jsTranslations.ByVillage;
            return;
        }
        villagePanel.classList.remove('hidden');
        villageLoading.classList.remove('hidden');
        villageBadge.classList.add('hidden');
        villageNone.classList.add('hidden');
        villageList.classList.add('hidden');
        villageList.innerHTML = '';
        villageUsersListVisible = false;
        const btn = document.getElementById('toggleVillageUsersBtn');
        if (btn) btn.textContent = jsTranslations.btn_show_all;

        const villageIdsParam = checkedVillages.join(',');
        fetch(`create_task.php?ajax=village_users&village_ids=${villageIdsParam}`)
            .then(r => r.json())
            .then(data => {
                villageLoading.classList.add('hidden');
                if (data.count === 0) {
                    villageNone.classList.remove('hidden');
                } else {
                    villageBadge.classList.remove('hidden');
                    villageCountEl.textContent = data.count;
                    villageList.innerHTML = buildUserListHTML(data.users);
                    lucide.createIcons();
                }
                prevAllocation.textContent = jsTranslations.ByVillageUsers.replace('%count%', data.count);
            })
            .catch(() => { villageLoading.classList.add('hidden'); villageNone.classList.remove('hidden'); });
    }

    function toggleVillageUsersList() {
        villageUsersListVisible = !villageUsersListVisible;
        villageList.classList.toggle('hidden', !villageUsersListVisible);
        document.getElementById('toggleVillageUsersBtn').textContent = villageUsersListVisible ? jsTranslations.btn_hide : jsTranslations.btn_show_all;
    }

    // ── Shared: build user row HTML ──────────────────────────────
    function buildUserListHTML(users) {
        return users.map(u => `
            <div class="flex items-center gap-3 px-4 py-2.5 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <div class="w-8 h-8 rounded-full bg-navy-100 dark:bg-navy-800 flex items-center justify-center text-xs font-bold text-navy-700 dark:text-blue-300 flex-shrink-0">
                    ${u.full_name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 dark:text-white truncate">${u.full_name}</p>
                    ${u.designation ? `<p class="text-xs text-slate-500 dark:text-slate-400">${u.designation}</p>` : ''}
                </div>
                <i data-lucide="check" class="w-3.5 h-3.5 text-govgreen-500 flex-shrink-0"></i>
            </div>`
        ).join('');
    }

    // Bind event listeners to checkbox selections
    document.querySelectorAll('.role-checkbox').forEach(cb => cb.addEventListener('change', updateRoleUsersPreview));
    document.querySelectorAll('.taluka-checkbox').forEach(cb => cb.addEventListener('change', updateTalukaUsersPreview));
    document.querySelectorAll('.village-checkbox').forEach(cb => cb.addEventListener('change', updateVillageUsersPreview));

    byNameRadio.addEventListener('change',    () => setAllocationUI('by_name'));
    byRoleRadio.addEventListener('change',    () => setAllocationUI('by_role'));
    byTalukaRadio.addEventListener('change',  () => setAllocationUI('by_taluka'));
    byVillageRadio.addEventListener('change', () => setAllocationUI('by_village'));

    // Initial state
    const initAlloc = document.querySelector('input[name="allocation_type"]:checked')?.value || 'by_name';
    setAllocationUI(initAlloc);

    // ── Priority Selector ────────────────────────────────────────
    const priorityCards  = document.querySelectorAll('.priority-card');
    const priorityValue  = document.getElementById('priorityValue');
    const prevPriority   = document.getElementById('prevPriority');

    const priorityConfig = {
        Low:      { cls: 'bg-green-100 text-green-800',   ring: 'ring-2 ring-green-400' },
        Medium:   { cls: 'bg-yellow-100 text-yellow-800', ring: 'ring-2 ring-yellow-400' },
        High:     { cls: 'bg-red-100 text-red-800',       ring: 'ring-2 ring-red-400' },
        Critical: { cls: 'bg-purple-100 text-purple-800', ring: 'ring-2 ring-purple-400' },
    };

    function selectPriority(value) {
        priorityCards.forEach(card => {
            const box = card.querySelector('.priority-box');
            const p   = card.dataset.priority;
            const radio = card.querySelector('input[type=radio]');
            if (p === value) {
                radio.checked = true;
                box.classList.add(priorityConfig[p].ring.split(' ')[0], priorityConfig[p].ring.split(' ')[1]);
            } else {
                radio.checked = false;
                box.classList.remove('ring-2', 'ring-green-400', 'ring-yellow-400', 'ring-red-400', 'ring-purple-400');
            }
        });
        priorityValue.value = value;
        prevPriority.textContent = jsTranslations[value];
        prevPriority.className = 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ' + priorityConfig[value].cls;
    }

    priorityCards.forEach(card => {
        card.addEventListener('click', () => selectPriority(card.dataset.priority));
    });

    // Init priority
    selectPriority(priorityValue.value || 'Medium');

    // ── Character Counter for Title ──────────────────────────────
    const titleInput   = document.getElementById('task_title');
    const titleCounter = document.getElementById('titleCounter');
    titleInput.addEventListener('input', () => {
        const len = titleInput.value.length;
        const charTemplate = '<?= $t['char_counter'] ?? "%d / 255 characters" ?>';
        titleCounter.textContent = charTemplate.replace('%d', len);
        titleCounter.className = 'mt-1 text-xs ' + (len > 240 ? 'text-red-500' : 'text-slate-400 dark:text-slate-500');
        document.getElementById('prevTitle').textContent = titleInput.value || '—';
    });

    // ── Due Date Validation & Min Date Setup ──────────────────────
    const dueDateInput   = document.getElementById('due_date');
    
    // Set min date-time to now
    function updateMinDateTime() {
        const now = new Date();
        const localNow = now.getFullYear() + '-' + 
                         String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(now.getDate()).padStart(2, '0') + 'T' + 
                         String(now.getHours()).padStart(2, '0') + ':' + 
                         String(now.getMinutes()).padStart(2, '0');
        dueDateInput.min = localNow;
    }
    updateMinDateTime();
    setInterval(updateMinDateTime, 30000); // Update min time every 30s
    const dueDateWarning = document.getElementById('dueDateWarning');
    const prevDue        = document.getElementById('prevDue');

    dueDateInput.addEventListener('change', () => {
        const val = new Date(dueDateInput.value);
        if (val < new Date()) {
            dueDateWarning.classList.remove('hidden');
        } else {
            dueDateWarning.classList.add('hidden');
        }
        if (dueDateInput.value) {
            const opts = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            const localeCode = langCode === 'mr' ? 'mr-IN' : 'en-IN';
            prevDue.textContent = val.toLocaleDateString(localeCode, opts);
        } else {
            prevDue.textContent = '—';
        }
    });

    // ── File Upload / Drag-Drop ──────────────────────────────────
    const dropZone    = document.getElementById('dropZone');
    const fileInput   = document.getElementById('attachment');
    const filePreview = document.getElementById('filePreview');
    const prevAttach  = document.getElementById('prevAttachment');

    const fileTypeMap = {
        pdf:  { icon: 'file-text',  bg: 'bg-red-100 dark:bg-red-900/30',    color: 'text-red-500' },
        doc:  { icon: 'file-type',  bg: 'bg-blue-100 dark:bg-blue-900/30',  color: 'text-blue-500' },
        docx: { icon: 'file-type',  bg: 'bg-blue-100 dark:bg-blue-900/30',  color: 'text-blue-500' },
        jpg:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        jpeg: { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        png:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        gif:  { icon: 'image',      bg: 'bg-green-100 dark:bg-green-900/30',color: 'text-green-500' },
        mp3:  { icon: 'music',      bg: 'bg-purple-100 dark:bg-purple-900/30',color: 'text-purple-500' },
        wav:  { icon: 'music',      bg: 'bg-purple-100 dark:bg-purple-900/30',color: 'text-purple-500' },
        mp4:  { icon: 'video',      bg: 'bg-orange-100 dark:bg-orange-900/30',color: 'text-orange-500' },
        avi:  { icon: 'video',      bg: 'bg-orange-100 dark:bg-orange-900/30',color: 'text-orange-500' },
        mov:  { icon: 'video',      bg: 'bg-orange-100 dark:bg-orange-900/30',color: 'text-orange-500' },
    };

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(2) + ' MB';
    }

    function showFilePreview(file) {
        const ext  = file.name.split('.').pop().toLowerCase();
        const type = fileTypeMap[ext] || { icon: 'file', bg: 'bg-slate-100 dark:bg-slate-700', color: 'text-slate-500' };

        document.getElementById('fileIcon').className  = `w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 ${type.bg}`;
        document.getElementById('fileIcon').innerHTML  = `<i data-lucide="${type.icon}" class="w-5 h-5 ${type.color}"></i>`;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatBytes(file.size);
        filePreview.classList.remove('hidden');
        prevAttach.textContent = file.name;
        lucide.createIcons();
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) showFilePreview(fileInput.files[0]);
    });

    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const dt = e.dataTransfer;
        if (dt.files.length) {
            // Create a new DataTransfer to assign to input
            const transfer = new DataTransfer();
            transfer.items.add(dt.files[0]);
            fileInput.files = transfer.files;
            showFilePreview(dt.files[0]);
        }
    });

    function clearFile() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        prevAttach.textContent = jsTranslations.none;
    }

    // ── Form Submission Loading State ────────────────────────────
    document.getElementById('createTaskForm').addEventListener('submit', function (e) {
        const title = titleInput.value.trim();
        const due   = dueDateInput.value;
        const alloc = document.querySelector('input[name="allocation_type"]:checked')?.value;

        if (!title) { showToast(jsTranslations.toast_title_req, 'warning'); e.preventDefault(); return; }
        if (!due)   { showToast(jsTranslations.toast_due_req,   'warning'); e.preventDefault(); return; }

        if (alloc === 'by_name') {
            const checkedBoxes = document.querySelectorAll('input[name="assigned_user_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                showToast(jsTranslations.toast_employee_req, 'warning'); e.preventDefault(); return;
            }
        }
        if (alloc === 'by_role') {
            const checkedBoxes = document.querySelectorAll('input[name="assigned_role_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                showToast(jsTranslations.toast_role_req, 'warning'); e.preventDefault(); return;
            }
        }
        if (alloc === 'by_taluka') {
            const checkedBoxes = document.querySelectorAll('input[name="assigned_taluka_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                showToast(jsTranslations.toast_taluka_req, 'warning'); e.preventDefault(); return;
            }
        }
        if (alloc === 'by_village') {
            const checkedBoxes = document.querySelectorAll('input[name="assigned_village_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                showToast(jsTranslations.toast_village_req, 'warning'); e.preventDefault(); return;
            }
        }

        const btn  = document.getElementById('submitBtn');
        const text = document.getElementById('submitBtnText');
        btn.disabled  = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.textContent = jsTranslations.btn_submitting;
    });

    // ── Toast Notification ───────────────────────────────────────
    function showToast(msg, type = 'info') {
        const toast = document.getElementById('toast');
        document.getElementById('toastMsg').textContent = msg;
        const icon = type === 'warning' ? 'alert-triangle' : 'check-circle';
        document.getElementById('toastIcon').setAttribute('data-lucide', icon);
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

    // ── Reset Form ───────────────────────────────────────────────
    function resetForm() {
        clearFile();
        selectPriority('Medium');
        setAllocationUI('by_name');
        
        // Uncheck all checkboxes
        document.querySelectorAll('.role-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.taluka-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.village-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('input[name="assigned_user_ids[]"]').forEach(cb => cb.checked = false);
        
        // Reset selectors
        if (talukaSelectVillage) talukaSelectVillage.value = '';
        
        // Hide all preview panels
        if (villagePanel) villagePanel.classList.add('hidden');
        if (rolePanel)    rolePanel.classList.add('hidden');
        if (talukaPanel)  talukaPanel.classList.add('hidden');
        
        document.getElementById('prevTitle').textContent  = '—';
        document.getElementById('prevDue').textContent    = '—';
        const charTemplate = '<?= $t['char_counter'] ?? "%d / 255 characters" ?>';
        titleCounter.textContent = charTemplate.replace('%d', 0);
        dueDateWarning.classList.add('hidden');
        lucide.createIcons();
    }

    // ── Re-init icons after DOM updates ─────────────────────────
    lucide.createIcons();

    <?php if ($success_msg): ?>
    // Auto-show success toast on page load (after redirect)
    window.addEventListener('load', () => {
        showToast('<?= addslashes(strip_tags($success_msg)) ?>', 'success');
    });
    <?php endif; ?>
</script>

<?php include 'include/footer.php'; ?>
