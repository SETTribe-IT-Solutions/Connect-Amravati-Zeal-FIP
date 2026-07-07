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
        'lbl_by_village' => 'By Village',
        'lbl_by_village_desc' => 'Assign to a specific village',
        'lbl_select_taluka' => 'Select Taluka',
        'opt_select_taluka' => '— Select a taluka —',
        'lbl_select_village' => 'Select Village',
        'opt_select_village' => '— Select a village —',
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
        'msg_no_employees_village' => 'No active employees mapped to this village.',
        'msg_fetching_village_employees' => 'Fetching mapped employees for this village…',
        'msg_responsible_identified' => 'Responsible person identified:',
        'btn_submitting' => 'Creating Task…',

        'toast_title_req' => 'Task title is required.',
        'toast_due_req' => 'Please select a due date & time.',
        'toast_employee_req' => 'Please select an employee to assign.',
        'toast_role_req' => 'Please select a role to assign.',
        'toast_taluka_req' => 'Please select a taluka.',
        'toast_village_req' => 'Please select a village.',
        'toast_created' => 'created!',
        'toast_warning' => 'warning',
        'toast_success' => 'success',
        
        'char_counter' => '%d / 255 characters'
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
        'lbl_by_village' => 'गावानुसार',
        'lbl_by_village_desc' => 'विशिष्ट गावाला कार्य वाटप करा',
        'lbl_select_taluka' => 'तालुका निवडा',
        'opt_select_taluka' => '— तालुका निवडा —',
        'lbl_select_village' => 'गाव निवडा',
        'opt_select_village' => '— गाव निवडा —',
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
        'msg_no_employees_village' => 'या गावाशी जोडलेले कोणतेही सक्रिय कर्मचारी आढळले नाहीत.',
        'msg_fetching_village_employees' => 'या गावाचे कर्मचारी शोधत आहे...',
        'msg_responsible_identified' => 'जबाबदार व्यक्ती ओळखली गेली:',
        'btn_submitting' => 'कार्य तयार करत आहे...',

        'toast_title_req' => 'कार्याचे शीर्षक आवश्यक आहे.',
        'toast_due_req' => 'कृपया नियत तारीख आणि वेळ निवडा.',
        'toast_employee_req' => 'कृपया नियुक्त करण्यासाठी कर्मचारी निवडा.',
        'toast_role_req' => 'कृपया नियुक्त करण्यासाठी भूमिका निवडा.',
        'toast_taluka_req' => 'कृपया तालुका निवडा.',
        'toast_village_req' => 'कृपया गाव निवडा.',
        'toast_created' => 'तयार केले!',
        'toast_warning' => 'चेतावणी',
        'toast_success' => 'यशस्वी',
        
        'char_counter' => '%d / २५५ अक्षरे'
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
$userLevel = $_SESSION['user_level'] ?? null;
if ($userLevel === null && !empty($sRole)) {
    if (isset($conn) && $conn instanceof mysqli) {
        try {
            $stmt = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $sRole);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($row = $res->fetch_assoc()) {
                    $userLevel = (int)$row['role_level'];
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log('create_task role level lookup error: ' . $e->getMessage());
        }
    }
    if ($userLevel === null) {
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
        $userLevel = $roleMap[$sRole] ?? 3;
    }
}

if ($userLevel === 3) {
    header("Location: dashboard.php?lang=" . $lang);
    exit();
}

$user_level = $userLevel;
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
if (isset($_GET['ajax']) && $_GET['ajax'] === 'role_users' && isset($_GET['role_id'])) {
    header('Content-Type: application/json');
    if (!$conn) {
        echo json_encode(['users' => [], 'count' => 0, 'error' => 'Database connection unavailable']);
        exit;
    }
    $role_id = (int)$_GET['role_id'];
    $res = $conn->query(
        "SELECT u.user_id, u.full_name, u.designation, u.department_id
           FROM users u
           JOIN roles r ON u.role_id = r.role_id
          WHERE u.role_id = $role_id AND u.status = 'Active' AND r.role_level >= $user_level
          ORDER BY u.full_name"
    );
    $users_list = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $users_list[] = [
                'id'          => (int)$row['user_id'],
                'full_name'   => $row['full_name'],
                'designation' => $row['designation'] ?? '',
            ];
        }
    }
    echo json_encode(['users' => $users_list, 'count' => count($users_list)]);
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === 'village_users' && isset($_GET['village_id'])) {
    header('Content-Type: application/json');
    if (!$conn) {
        echo json_encode(['users' => [], 'count' => 0, 'error' => 'Database connection unavailable']);
        exit;
    }
    $village_id = (int)$_GET['village_id'];
    $res = $conn->query(
        "SELECT u.user_id, u.full_name, u.designation, u.department_id
           FROM users u
           JOIN roles r ON u.role_id = r.role_id
          WHERE u.village_id = $village_id AND u.status = 'Active' AND r.role_level >= $user_level
          ORDER BY u.full_name"
    );
    $users_list = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $users_list[] = [
                'id'          => (int)$row['user_id'],
                'full_name'   => $row['full_name'],
                'designation' => $row['designation'] ?? '',
            ];
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
    $assigned_user_id = !empty($_POST['assigned_user_id']) ? (int)$_POST['assigned_user_id'] : null;
    $task_taluka_id   = !empty($_POST['task_taluka_id'])   ? (int)$_POST['task_taluka_id']   : null;
    $task_village_id  = !empty($_POST['task_village_id'])  ? (int)$_POST['task_village_id']  : null;
    $created_by       = $_SESSION['user_id'] ?? 1;

    if ($allocation_type === 'by_village') {
        if (!$task_taluka_id || !$task_village_id) {
            $error_msg = $lang === 'mr' ? 'कृपया तालुका आणि गाव निवडा.' : 'Please select both taluka and village.';
        }
    }

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
    // AUTO-POPULATE: Pull department, role, district, taluka, village
    // from the selected user's own record — admin never types these.
    // ─────────────────────────────────────────────────────────────────
    $task_district_id = null;
    if ($allocation_type !== 'by_village') {
        $task_taluka_id   = null;
        $task_village_id  = null;
    } else {
        $task_district_id = 1; // Hardcoded to Amravati (1)
    }

    if ($allocation_type === 'by_name' && $assigned_user_id) {
        $usr_res = $conn->query(
            "SELECT department_id, role_id, district_id, taluka_id, village_id
               FROM users
              WHERE user_id = $assigned_user_id AND status = 'Active'
              LIMIT 1"
        );
        if ($usr_res && $usr_data = $usr_res->fetch_assoc()) {
            // Use the user's own department (overrides any filter choice)
            $department_id    = !empty($usr_data['department_id']) ? (int)$usr_data['department_id'] : $department_id;
            // Sync role so tasks.assigned_role_id matches the user's role
            $assigned_role_id = !empty($usr_data['role_id'])       ? (int)$usr_data['role_id']       : $assigned_role_id;
            $task_district_id = !empty($usr_data['district_id'])   ? (int)$usr_data['district_id']   : null;
            $task_taluka_id   = !empty($usr_data['taluka_id'])     ? (int)$usr_data['taluka_id']     : null;
            $task_village_id  = !empty($usr_data['village_id'])    ? (int)$usr_data['village_id']    : null;
        }
    } elseif ($allocation_type === 'by_village' && $task_village_id) {
        // Automatically identify the responsible user/department for the selected village
        $v_users_res = $conn->query(
            "SELECT u.user_id, u.role_id, u.department_id
               FROM users u
              WHERE u.village_id = $task_village_id AND u.status = 'Active'
              ORDER BY u.role_id DESC"
        );
        $v_users = [];
        if ($v_users_res) {
            while ($row = $v_users_res->fetch_assoc()) {
                $v_users[] = $row;
            }
        }
        
        $responsible_user = null;
        if (!empty($v_users)) {
            // Priority 1: Talathi (7) or Gramsevak (8)
            foreach ($v_users as $vu) {
                if (in_array((int)$vu['role_id'], [7, 8])) {
                    $responsible_user = $vu;
                    break;
                }
            }
            if (!$responsible_user) {
                $responsible_user = $v_users[0];
            }
        }
        
        if ($responsible_user) {
            $assigned_user_id = (int)$responsible_user['user_id'];
            $assigned_role_id = $responsible_user['role_id'] ? (int)$responsible_user['role_id'] : null;
            $department_id    = $responsible_user['department_id'] ? (int)$responsible_user['department_id'] : null;
        } else {
            $error_msg = $lang === 'mr' ? 'या गावाशी जोडलेला कोणताही सक्रिय कर्मचारी आढळला नाही.' : 'No active employees mapped to the selected village.';
        }
    }
    // For by_role the task row keeps district/taluka/village NULL;
    // each user's own context is tracked via task_assignments.

    // ── Insert task into `tasks` table ────────────────────────────
    if (empty($error_msg)) {

        // Build nullable SQL literals
        $dept_sql     = $department_id    ? (int)$department_id    : 'NULL';
        $role_sql     = $assigned_role_id ? (int)$assigned_role_id : 'NULL';
        $user_sql     = (($allocation_type === 'by_name' || $allocation_type === 'by_village') && $assigned_user_id) ? (int)$assigned_user_id : 'NULL';
        $district_sql = $task_district_id ? (int)$task_district_id : 'NULL';
        $taluka_sql   = $task_taluka_id   ? (int)$task_taluka_id   : 'NULL';
        $village_sql  = $task_village_id  ? (int)$task_village_id  : 'NULL';
        $cat_sql      = !empty($task_category) ? "'" . $conn->real_escape_string($task_category) . "'" : 'NULL';
        $tgt_sql      = !empty($target)        ? "'" . $conn->real_escape_string($target)        . "'" : 'NULL';
        $due_sql      = !empty($due_date)      ? "'" . $due_date . "'"                                  : 'NULL';

        $tmp_task_no = 'TASK_TMP_' . time();

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
            if ($allocation_type === 'by_role' && $assigned_role_id) {
                // Fetch every active user with this role INCLUDING their location data
                $role_users = $conn->query(
                    "SELECT user_id, department_id, district_id, taluka_id, village_id
                       FROM users
                      WHERE role_id = $assigned_role_id AND status = 'Active'"
                );
                if ($role_users && $role_users->num_rows > 0) {
                    while ($ru = $role_users->fetch_assoc()) {
                        $uid = (int)$ru['user_id'];
                        $conn->query(
                            "INSERT INTO task_assignments
                                 (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                             VALUES ($new_task_id, $created_by, $uid, $assigned_role_id, NOW(), 'Pending')"
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
            } elseif ($allocation_type === 'by_name' && $assigned_user_id) {
                // Single user assignment
                $role_to_assign = $assigned_role_id ? (int)$assigned_role_id : 'NULL';
                $conn->query(
                    "INSERT INTO task_assignments (task_id, assigned_from_user, assigned_to_user, assigned_to_role, assigned_date, status)
                     VALUES ($new_task_id, $created_by, $assigned_user_id, $role_to_assign, NOW(), 'Pending')"
                );
                $assigned_count = $conn->affected_rows > 0 ? 1 : 0;

                // ── Notification for single assigned user ──────────
                if ($assigned_count > 0) {
                    createTaskNotification(
                        $conn,
                        $new_task_id,
                        $task_title,
                        $due_date,
                        (int)$assigned_user_id,
                        $created_by
                    );
                }
            } elseif ($allocation_type === 'by_village') {
                // Village-wise: assign to all eligible employees in the selected village
                $assigned_village_id = !empty($_POST['assigned_village_id']) ? (int)$_POST['assigned_village_id'] : 0;
                if ($assigned_village_id > 0) {
                    $village_users = $conn->query(
                        "SELECT u.user_id, u.department_id, u.district_id, u.taluka_id, u.village_id
                           FROM users u
                           JOIN roles r ON u.role_id = r.role_id
                          WHERE u.village_id = $assigned_village_id AND u.status = 'Active' AND r.role_level >= $user_level"
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
                        // Store village on the task row
                        $conn->query("UPDATE tasks SET village_id = $assigned_village_id WHERE task_id = $new_task_id");
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
}

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
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                        <?= htmlspecialchars($initials ?? 'U') ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50">
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
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="allocationTypeGroup">
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

                            <!-- By Name: Employee Dropdown -->
                            <div id="byNameSection" class="space-y-4 transition-all">
                                <div>
                                    <label class="form-label" for="assigned_user_id">
                                        <?= htmlspecialchars($t['lbl_select_employee'] ?? 'Select Employee') ?> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="user-search" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="assigned_user_id" name="assigned_user_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value=""><?= htmlspecialchars($t['opt_select_employee'] ?? '— Select an employee —') ?></option>
                                            <?php
                                            if ($users_result && $users_result->num_rows > 0) {
                                                $users_result->data_seek(0);
                                                while ($u = $users_result->fetch_assoc()):
                                                    $sel = (isset($_POST['assigned_user_id']) && $_POST['assigned_user_id'] == $u['user_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$u['user_id'] ?>" data-designation="<?= htmlspecialchars($u['designation'] ?? '') ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($u['full_name']) ?><?= !empty($u['designation']) ? ' — ' . htmlspecialchars($u['designation']) : '' ?>
                                            </option>
                                            <?php endwhile; } else { ?>
                                            <option value="" disabled><?= htmlspecialchars($t['opt_no_employees'] ?? 'No active employees found in database') ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Village: Taluka → Village cascade -->
                            <div id="byVillageSection" class="space-y-4 hidden transition-all">
                                <!-- Taluka -->
                                <div>
                                    <label class="form-label" for="filter_taluka_id">Select Taluka <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="filter_taluka_id" name="filter_taluka_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-govgreen-500 focus:border-govgreen-500
                                                       transition-colors appearance-none">
                                            <option value="">— Select Taluka —</option>
                                            <?php
                                            if ($talukas_result && $talukas_result->num_rows > 0) {
                                                $talukas_result->data_seek(0);
                                                while ($tk = $talukas_result->fetch_assoc()):
                                                    $sel = (isset($_POST['filter_taluka_id']) && $_POST['filter_taluka_id'] == $tk['taluka_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$tk['taluka_id'] ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($tk['taluka_name']) ?>
                                            </option>
                                            <?php endwhile; } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!-- Village -->
                                <div>
                                    <label class="form-label" for="assigned_village_id">Select Village <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="assigned_village_id" name="assigned_village_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-govgreen-500 focus:border-govgreen-500
                                                       transition-colors appearance-none">
                                            <option value="">— Select Village —</option>
                                            <?php
                                            // Pre-populate villages for selected taluka
                                            if ($villages_result && $villages_result->num_rows > 0) {
                                                $villages_result->data_seek(0);
                                                while ($vl = $villages_result->fetch_assoc()):
                                                    $sel = (isset($_POST['assigned_village_id']) && $_POST['assigned_village_id'] == $vl['village_id']) ? 'selected' : '';
                                                    $dat = 'data-taluka="' . (int)$vl['taluka_id'] . '"';
                                            ?>
                                            <option value="<?= (int)$vl['village_id'] ?>" <?= $dat ?> <?= $sel ?> class="village-opt">
                                                <?= htmlspecialchars($vl['village_name']) ?>
                                            </option>
                                            <?php endwhile; } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!-- Village user preview panel -->
                                <div id="villageUsersPanel" class="hidden space-y-3">
                                    <div id="villageUsersLoading" class="hidden flex items-center gap-2 text-xs text-slate-500">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Fetching employees in this village…
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
                                        No eligible employees found in this village.
                                    </div>
                                    <div id="villageUsersList" class="hidden mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                    </div>
                                </div>
                            </div>

                            <!-- By Role: Role + Department Dropdowns -->
                            <div id="byRoleSection" class="space-y-4 hidden transition-all">
                                <!-- Role -->
                                <div>
                                    <label class="form-label" for="assigned_role_id">
                                        <?= htmlspecialchars($t['lbl_select_role'] ?? 'Select Role') ?> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="shield" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="assigned_role_id" name="assigned_role_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value=""><?= htmlspecialchars($t['opt_select_role'] ?? '— Select a role —') ?></option>
                                            <?php
                                            if ($roles_result && $roles_result->num_rows > 0) {
                                                $roles_result->data_seek(0);
                                                while ($r = $roles_result->fetch_assoc()):
                                                    $sel = (isset($_POST['assigned_role_id']) && $_POST['assigned_role_id'] == $r['role_id']) ? 'selected' : '';
                                                    $role_key = 'role_' . strtolower(str_replace(' ', '_', trim($r['role_name'])));
                                                    $role_name_display = $t[$role_key] ?? $r['role_name'];
                                            ?>
                                            <option value="<?= (int)$r['role_id'] ?>" data-level="<?= (int)($r['role_level'] ?? 0) ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($role_name_display) ?>
                                            </option>
                                            <?php endwhile; } else { ?>
                                            <option value="" disabled><?= htmlspecialchars($t['opt_no_roles'] ?? 'No active roles found in database') ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
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

                            <!-- By Village: Taluka + Village Dropdowns -->
                            <div id="byVillageSection" class="space-y-4 hidden transition-all">
                                <!-- Taluka -->
                                <div>
                                    <label class="form-label" for="task_taluka_id">
                                        <?= htmlspecialchars($t['lbl_select_taluka'] ?? 'Select Taluka') ?> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="task_taluka_id" name="task_taluka_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value=""><?= htmlspecialchars($t['opt_select_taluka'] ?? '— Select a taluka —') ?></option>
                                            <?php
                                            if ($talukas_result && $talukas_result->num_rows > 0) {
                                                $talukas_result->data_seek(0);
                                                while ($tk = $talukas_result->fetch_assoc()):
                                                    $sel = (isset($_POST['task_taluka_id']) && $_POST['task_taluka_id'] == $tk['taluka_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$tk['taluka_id'] ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($tk['taluka_name']) ?>
                                            </option>
                                            <?php endwhile; } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Village -->
                                <div>
                                    <label class="form-label" for="task_village_id">
                                        <?= htmlspecialchars($t['lbl_select_village'] ?? 'Select Village') ?> <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="task_village_id" name="task_village_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value=""><?= htmlspecialchars($t['opt_select_village'] ?? '— Select a village —') ?></option>
                                            <?php
                                            if (!empty($task_taluka_id)) {
                                                $villages_res = $conn->query("SELECT village_id, village_name FROM villages WHERE taluka_id = $task_taluka_id ORDER BY village_name");
                                                if ($villages_res && $villages_res->num_rows > 0) {
                                                    while ($vg = $villages_res->fetch_assoc()) {
                                                        $sel = (isset($_POST['task_village_id']) && $_POST['task_village_id'] == $vg['village_id']) ? 'selected' : '';
                                                        echo '<option value="' . (int)$vg['village_id'] . '" ' . $sel . '>' . htmlspecialchars($vg['village_name']) . '</option>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Live: users-for-village preview panel -->
                                <div id="villageUsersPanel" class="hidden mt-2">
                                    <!-- Loading state -->
                                    <div id="villageUsersLoading" class="hidden flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 py-2">
                                        <svg class="animate-spin w-4 h-4 text-navy-500" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                        </svg>
                                        <span id="villageUsersLoadingText"><?= htmlspecialchars($t['msg_fetching_village_employees'] ?? 'Fetching mapped employees for this village…') ?></span>
                                    </div>

                                    <!-- Responsible User badge -->
                                    <div id="villageUsersBadge" class="hidden p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-800 rounded-xl">
                                        <div class="flex items-start gap-2.5">
                                            <i data-lucide="user-check" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5"></i>
                                            <div class="text-sm">
                                                <span class="font-medium text-emerald-700 dark:text-emerald-300 block">
                                                    <?= htmlspecialchars($t['msg_responsible_identified'] ?? 'Responsible person identified:') ?>
                                                </span>
                                                <div id="responsibleUserDetails" class="mt-1 font-semibold text-slate-800 dark:text-white">
                                                    <!-- Filled dynamically -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- No users warning -->
                                    <div id="villageUsersNone" class="hidden flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-500"></i>
                                        <span id="villageUsersNoneText"><?= htmlspecialchars($t['msg_no_employees_village'] ?? 'No active employees mapped to this village.') ?></span>
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
    const byVillageRadio   = document.getElementById('alloc_by_village');
    const byNameSection    = document.getElementById('byNameSection');
    const byRoleSection    = document.getElementById('byRoleSection');
    const byVillageSection = document.getElementById('byVillageSection');
    const boxByName        = document.getElementById('box-byname');
    const boxByRole        = document.getElementById('box-byrole');
    const boxByVillage     = document.getElementById('box-byvillage');
    const prevAllocation   = document.getElementById('prevAllocation');
    const roleSelect       = document.getElementById('assigned_role_id');
    const rolePanel        = document.getElementById('roleUsersPanel');
    const roleBadge        = document.getElementById('roleUsersBadge');
    const roleNone         = document.getElementById('roleUsersNone');
    const roleLoading      = document.getElementById('roleUsersLoading');
    const roleList         = document.getElementById('roleUsersList');
    const roleCountEl      = document.getElementById('roleUserCount');
    const villagePanel     = document.getElementById('villageUsersPanel');
    const villageBadge     = document.getElementById('villageUsersBadge');
    const villageNone      = document.getElementById('villageUsersNone');
    const villageLoading   = document.getElementById('villageUsersLoading');
    const villageList      = document.getElementById('villageUsersList');
    const villageCountEl   = document.getElementById('villageUserCount');
    const talukaSelect     = document.getElementById('filter_taluka_id');
    const villageSelect    = document.getElementById('assigned_village_id');
    let roleUsersListVisible    = false;
    let villageUsersListVisible = false;

    // Dynamic translations for JS
    const langCode = '<?= $lang ?>';
    const jsTranslations = {
        ByName:    '<?= addslashes($t['lbl_by_name'] ?? 'By Name') ?>',
        ByRole:    '<?= addslashes($t['lbl_by_role'] ?? 'By Role') ?>',
        ByVillage: 'By Village',
        ByRoleUsers:    '<?= addslashes($t['lbl_by_role'] ?? 'By Role') ?> (%count%)',
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
        byVillageSection.classList.add('hidden');
        // Reset all card borders
        boxByName.className    = clsInactive;
        boxByRole.className    = clsInactive;
        boxByVillage.className = clsInactive;

        if (type === 'by_name') {
            byNameSection.classList.remove('hidden');
            boxByName.className = clsActive + ' border-navy-500 bg-navy-50 dark:bg-navy-900/20';
            prevAllocation.textContent = jsTranslations.ByName;
        } else if (type === 'by_role') {
            byRoleSection.classList.remove('hidden');
            boxByRole.className = clsActive + ' border-saffron-500 bg-saffron-50 dark:bg-orange-900/20';
            prevAllocation.textContent = jsTranslations.ByRole;
            if (roleSelect.value) fetchRoleUsers(roleSelect.value);
        } else if (type === 'by_village') {
            byVillageSection.classList.remove('hidden');
            boxByVillage.className = clsActive + ' border-govgreen-500 bg-green-50 dark:bg-green-900/20';
            prevAllocation.textContent = jsTranslations.ByVillage;
            filterVillagesByTaluka(talukaSelect.value);
        }
        lucide.createIcons();
    }

    function updateVillagePreview() {
        if (villageSelect && villageSelect.value) {
            const selectedText = villageSelect.options[villageSelect.selectedIndex].text;
            prevAllocation.textContent = jsTranslations.ByVillage + ': ' + selectedText;
        } else {
            prevAllocation.textContent = jsTranslations.ByVillage;
        }
    }

    // ── AJAX: fetch users for the selected role ──────────────────
    function fetchRoleUsers(roleId) {
        if (!roleId) { rolePanel.classList.add('hidden'); return; }
        rolePanel.classList.remove('hidden');
        roleLoading.classList.remove('hidden');
        roleBadge.classList.add('hidden');
        roleNone.classList.add('hidden');
        roleList.classList.add('hidden');
        roleList.innerHTML = '';
        roleUsersListVisible = false;
        const btn = document.getElementById('toggleUsersBtn');
        if (btn) btn.textContent = jsTranslations.btn_show_all;

        fetch(`create_task.php?ajax=role_users&role_id=${roleId}`)
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

    // ── Taluka → Village cascade filter ────────────────────────
    function filterVillagesByTaluka(talukaId) {
        const opts = villageSelect.querySelectorAll('.village-opt');
        let visible = 0;
        opts.forEach(opt => {
            if (!talukaId || opt.dataset.taluka === talukaId) {
                opt.style.display = '';
                visible++;
            } else {
                opt.style.display = 'none';
            }
        });
        // Reset village selection
        villageSelect.value = '';
        villagePanel.classList.add('hidden');
    }

    talukaSelect.addEventListener('change', () => {
        filterVillagesByTaluka(talukaSelect.value);
    });

    // ── AJAX: fetch users for the selected village ───────────────
    function fetchVillageUsers(villageId) {
        if (!villageId) { villagePanel.classList.add('hidden'); return; }
        villagePanel.classList.remove('hidden');
        villageLoading.classList.remove('hidden');
        villageBadge.classList.add('hidden');
        villageNone.classList.add('hidden');
        villageList.classList.add('hidden');
        villageList.innerHTML = '';
        villageUsersListVisible = false;
        const btn = document.getElementById('toggleVillageUsersBtn');
        if (btn) btn.textContent = jsTranslations.btn_show_all;

        fetch(`create_task.php?ajax=village_users&village_id=${villageId}`)
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

    villageSelect.addEventListener('change', () => fetchVillageUsers(villageSelect.value));

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

    roleSelect.addEventListener('change', () => fetchRoleUsers(roleSelect.value));

    byNameRadio.addEventListener('change',    () => setAllocationUI('by_name'));
    byRoleRadio.addEventListener('change',    () => setAllocationUI('by_role'));
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

        if (alloc === 'by_name' && !document.getElementById('assigned_user_id').value) {
            showToast(jsTranslations.toast_employee_req, 'warning'); e.preventDefault(); return;
        }
        if (alloc === 'by_role' && !document.getElementById('assigned_role_id').value) {
            showToast(jsTranslations.toast_role_req, 'warning'); e.preventDefault(); return;
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
