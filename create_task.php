<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

/**
 * Create Task Page
 * Amravati Connect - Government Workflow Platform
 *
 * Allows administrators to create and allocate tasks to employees
 * either by name (user) or by role (all users with that role get the task).
 */

session_start();
require_once 'include/dbConfig.php';

/* ─── Map login session keys to dashboard variables ────────────────── */
if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'] ?? 1;
    $_SESSION['user_village_id'] = $_SESSION['village_id'] ?? 1;
}

/* ─── Session defaults (dev preview) ───────────────────────── */
if (empty($_SESSION['user_role'])) {
    $_SESSION['user_role']       = 'Collector';
    $_SESSION['user_name']       = 'Hon. Collector';
    $_SESSION['user_taluka_id']  = 1;
    $_SESSION['user_village_id'] = 1;
}

$sRole      = $_SESSION['user_role'];
$sName      = $_SESSION['user_name'];

/* Avatar initials */
$parts    = array_filter(explode(' ', trim($sName)));
$first    = $parts[0] ?? 'U';
$second   = isset($parts[1]) ? $parts[1] : '';
$initials = strtoupper(substr($first, 0, 1) . substr($second, 0, 1));


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
    $role_id = (int)$_GET['role_id'];
    $res = $conn->query(
        "SELECT user_id, full_name, designation, department_id
           FROM users
          WHERE role_id = $role_id AND status = 'Active'
          ORDER BY full_name"
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
$users_result = $conn->query(
    "SELECT user_id, full_name, designation FROM users WHERE status = 'Active' ORDER BY full_name"
);
// roles table: primary key = role_id, name column = role_name
$roles_result = $conn->query(
    "SELECT role_id, role_name, role_level FROM roles WHERE status = 'Active' ORDER BY role_level, role_name"
);
// departments table
$departments_result = $conn->query(
    "SELECT id, department_name FROM departments ORDER BY department_name"
);

// ─── Handle Form Submission ───────────────────────────────────────────
$success_msg    = '';
$error_msg      = '';
$assigned_count = 0; // how many users received the task (for role-based)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
    $created_by       = $_SESSION['user_id'] ?? 1;

    // DB column `due_date` is DATE — strip the time part sent by datetime-local
    $due_date_raw = trim($_POST['due_date'] ?? '');
    $due_date     = '';
    if (!empty($due_date_raw)) {
        // datetime-local sends "YYYY-MM-DDTHH:MM" — convert to "YYYY-MM-DD HH:MM:SS"
        $due_date = $conn->real_escape_string(str_replace('T', ' ', $due_date_raw) . ':00');
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
                $error_msg = 'File upload failed. Please check folder permissions.';
            }
        } else {
            $error_msg = 'Invalid file type. Only PDF, Word, Images, Audio, and Video are allowed.';
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // AUTO-POPULATE: Pull department, role, district, taluka, village
    // from the selected user's own record — admin never types these.
    // ─────────────────────────────────────────────────────────────────
    $task_district_id = null;
    $task_taluka_id   = null;
    $task_village_id  = null;

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
    }
    // For by_role the task row keeps district/taluka/village NULL;
    // each user's own context is tracked via task_assignments.

    // ── Insert task into `tasks` table ────────────────────────────
    if (empty($error_msg)) {

        // Build nullable SQL literals
        $dept_sql     = $department_id    ? (int)$department_id    : 'NULL';
        $role_sql     = $assigned_role_id ? (int)$assigned_role_id : 'NULL';
        $user_sql     = ($allocation_type === 'by_name' && $assigned_user_id) ? (int)$assigned_user_id : 'NULL';
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
            $task_id_str = 'TASK_' . str_pad($new_task_id, 3, '0', STR_PAD_LEFT);

            // Update task_no with the real auto-incremented ID
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
                $assigned_count = 1;

                // ── Notification for single assigned user ──────────
                createTaskNotification(
                    $conn,
                    $new_task_id,
                    $task_title,
                    $due_date,
                    (int)$assigned_user_id,
                    $created_by
                );
            }

            $count_label = $assigned_count > 0
                ? " Assigned to <strong>$assigned_count</strong> employee" . ($assigned_count > 1 ? 's' : '') . '.'
                : '';
            $success_msg = "Task <strong>$task_id_str</strong> created successfully!$count_label";
        } else {
            $error_msg = 'Database error: ' . $conn->error;
        }
    }
}

// ─── Auto-generate Task No preview ─────────────────────────────────
$result  = $conn->query("SELECT AUTO_INCREMENT AS next_id FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tasks'");
$row     = $result ? $result->fetch_assoc() : null;
$next_id = (int)($row['next_id'] ?? 1);
$task_id_preview = 'TASK_' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - Amravati Connect</title>
    <meta name="description" content="Create and allocate tasks to employees by name or role on the Amravati Connect Government Workflow Platform.">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Tailwind Config (matches design system) -->
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
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

<!-- ═══════════════════════════════════════════════════════════════════
     SIDEBAR (same as blank template)
════════════════════════════════════════════════════════════════════ -->
<aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
            <i data-lucide="landmark" class="text-white w-5 h-5"></i>
        </div>
        <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4">
        <nav class="space-y-1 px-3">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Main Modules</p>
            <a href="dashboard.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                Executive Dashboard
            </a>
            <a href="create_task.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-navy-50 text-navy-700 dark:bg-slate-800 dark:text-white">
                <i data-lucide="network" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                Task Allocation
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                Announcements
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="award" class="w-5 h-5 mr-3 text-slate-400"></i>
                Appreciation
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Analytics &amp; Data</p>
            <a href="reports.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                Reports &amp; Analytics
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map" class="w-5 h-5 mr-3 text-slate-400"></i>
                GIS Map View
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>
                Document Management
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                User Management
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="map-pin" class="w-5 h-5 mr-3 text-slate-400"></i>
                Location Hierarchy
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i>
                Audit Logs
            </a>
            <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="settings" class="w-5 h-5 mr-3 text-slate-400"></i>
                Settings
            </a>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800">
        <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none">
            <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
            Ask Amravati AI
        </button>
    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════════════
     MAIN WRAPPER
════════════════════════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col overflow-hidden">

    <!-- GLOBAL HEADER -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm" aria-label="Breadcrumb">
                <a href="dashboard.php" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Dashboard</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <a href="#" class="text-slate-500 dark:text-slate-400 hover:text-navy-600 dark:hover:text-blue-400 transition-colors">Task Allocation</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-slate-400"></i>
                <span class="font-semibold text-slate-800 dark:text-white">Create Task</span>
            </nav>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Language Toggle -->
            <button class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md transition-colors border border-slate-200 dark:border-slate-700">
                <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                EN / MR
            </button>
            <!-- Theme Switcher -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            <!-- Notifications -->
            <button class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-saffron-500 ring-2 ring-white dark:ring-slate-900"></span>
            </button>
            <!-- Profile -->
            <div class="flex items-center space-x-3 border-l border-slate-200 dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                <div class="flex flex-col text-right hidden sm:block">
                    <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                    <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?></span>
                </div>
                <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm"><?= htmlspecialchars($initials) ?></div>
            </div>
        </div>
    </header>

    <!-- ═══════════════ MAIN CONTENT ═══════════════ -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">

        <!-- PHP Alert Messages -->
        <?php if ($success_msg): ?>
        <div id="phpAlert" class="mb-6 flex items-start gap-3 p-4 bg-govgreen-50 dark:bg-green-900/20 border border-govgreen-100 dark:border-green-800 rounded-xl animate-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-govgreen-600 dark:text-green-400 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-govgreen-700 dark:text-green-300"><?= $success_msg ?></p>
                <p class="text-xs text-govgreen-600 dark:text-green-400 mt-0.5">The task is now visible to the assigned employee(s).</p>
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
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Create &amp; Allocate Task</h1>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400 ml-13">Assign official tasks to government employees by name or by role/department.</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                    <i data-lucide="hash" class="w-3.5 h-3.5 mr-1.5"></i>
                    Auto ID: <span id="taskIdPreview" class="font-bold ml-1"><?= htmlspecialchars($task_id_preview) ?></span>
                </span>
                <a href="dashboard.php" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-lg text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Back
                </a>
            </div>
        </div>

        <!-- ── Form Card ── -->
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
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Basic Information</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Core task details</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Task ID (read-only) -->
                            <div>
                                <label class="form-label" for="task_no">
                                    Task ID <span class="text-saffron-500">*</span>
                                    <span class="ml-2 text-xs font-normal text-govgreen-600 dark:text-green-400 inline-flex items-center gap-1">
                                        <i data-lucide="zap" class="w-3 h-3"></i> Auto-Generated
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
                                    Task Title <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="task_title" name="task_title" required
                                       placeholder="e.g. Crop Damage Assessment Report – Chandur Block"
                                       value="<?= htmlspecialchars($_POST['task_title'] ?? '') ?>"
                                       class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                              bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                              placeholder-slate-400 dark:placeholder-slate-500
                                              focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                              transition-colors">
                                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500" id="titleCounter">0 / 255 characters</p>
                            </div>

                            <!-- Task Description -->
                            <div>
                                <label class="form-label" for="task_description">
                                    Task Description <span class="text-red-500">*</span>
                                </label>
                                <textarea id="task_description" name="task_description" required rows="4"
                                          placeholder="Provide a detailed description of the task, objectives, and expected outcomes..."
                                          class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                 bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                 placeholder-slate-400 dark:placeholder-slate-500
                                                 focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                 transition-colors resize-none"><?= htmlspecialchars($_POST['task_description'] ?? '') ?></textarea>
                            </div>

                            <!-- Task Category -->
                            <div>
                                <label class="form-label" for="task_category">Task Category</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="tag" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" id="task_category" name="task_category"
                                           placeholder="e.g. Revenue, Health, Education, Infrastructure"
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
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Task Allocation</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Assign to an individual or a role/department</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Allocation Type Toggle -->
                            <div>
                                <label class="form-label">
                                    Allocation Type <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-3" id="allocationTypeGroup">
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
                                                <p class="text-sm font-semibold text-navy-700 dark:text-blue-300">By Name</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Assign to specific employee</p>
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
                                                <p class="text-sm font-semibold text-slate-700 dark:text-white">By Role</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Assign to a role/department</p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- By Name: Employee Dropdown -->
                            <div id="byNameSection" class="space-y-4 transition-all">
                                <div>
                                    <label class="form-label" for="assigned_user_id">
                                        Select Employee <span class="text-red-500">*</span>
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
                                            <option value="">— Select an employee —</option>
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
                                            <option value="" disabled>No active employees found in database</option>
                                            <?php } ?>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- By Role: Role + Department Dropdowns -->
                            <div id="byRoleSection" class="space-y-4 hidden transition-all">
                                <!-- Role -->
                                <div>
                                    <label class="form-label" for="assigned_role_id">
                                        Select Role <span class="text-red-500">*</span>
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
                                            <option value="">— Select a role —</option>
                                            <?php
                                            if ($roles_result && $roles_result->num_rows > 0) {
                                                $roles_result->data_seek(0);
                                                while ($r = $roles_result->fetch_assoc()):
                                                    $sel = (isset($_POST['assigned_role_id']) && $_POST['assigned_role_id'] == $r['role_id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$r['role_id'] ?>" data-level="<?= (int)($r['role_level'] ?? 0) ?>" <?= $sel ?>>
                                                <?= htmlspecialchars($r['role_name']) ?>
                                            </option>
                                            <?php endwhile; } else { ?>
                                            <option value="" disabled>No active roles found in database</option>
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
                                        Fetching employees with this role…
                                    </div>

                                    <!-- Count badge -->
                                    <div id="roleUsersBadge" class="hidden flex items-center justify-between p-3 bg-saffron-50 dark:bg-orange-900/20 border border-saffron-100 dark:border-orange-800 rounded-xl">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="users" class="w-4 h-4 text-saffron-600 dark:text-orange-400"></i>
                                            <span class="text-sm font-medium text-saffron-700 dark:text-orange-300">
                                                <span id="roleUserCount">0</span> employee(s) will receive this task
                                            </span>
                                        </div>
                                        <button type="button" onclick="toggleRoleUsersList()" id="toggleUsersBtn"
                                                class="text-xs font-medium text-saffron-600 dark:text-orange-400 hover:underline">
                                            Show all
                                        </button>
                                    </div>

                                    <!-- No users warning -->
                                    <div id="roleUsersNone" class="hidden flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-600 dark:text-red-400">
                                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                                        No active employees found with this role.
                                    </div>

                                    <!-- Expandable user list -->
                                    <div id="roleUsersList" class="hidden mt-2 max-h-48 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                                    </div>
                                </div>

                                <!-- Department -->
                                <div>
                                    <label class="form-label" for="department_id">Filter by Department <span class="text-xs font-normal text-slate-400">(optional)</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                                        </div>
                                        <select id="department_id" name="department_id"
                                                class="w-full pl-10 pr-10 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                       bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                       focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                       transition-colors appearance-none">
                                            <option value="">— All Departments —</option>
                                            <?php
                                            if ($departments_result && $departments_result->num_rows > 0) {
                                                $departments_result->data_seek(0);
                                                while ($d = $departments_result->fetch_assoc()):
                                                    $sel = (isset($_POST['department_id']) && $_POST['department_id'] == $d['id']) ? 'selected' : '';
                                            ?>
                                            <option value="<?= (int)$d['id'] ?>" <?= $sel ?>>
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

                        </div>
                    </div>

                    <!-- Card: Attachment Upload -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.12s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center">
                                <i data-lucide="paperclip" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Attachment</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">PDF, Word, Images, Audio &amp; Video accepted</p>
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
                                    Drop your file here or <span class="text-navy-600 dark:text-blue-400 underline">browse</span>
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">PDF · Word · JPG · PNG · MP3 · MP4 · and more (max 20 MB)</p>
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

                    <!-- Card: Scheduling -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden animate-in" style="animation-delay:0.05s">
                        <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800">
                            <div class="w-8 h-8 rounded-lg bg-govgreen-50 dark:bg-green-900/30 flex items-center justify-center">
                                <i data-lucide="calendar-clock" class="w-4 h-4 text-govgreen-600 dark:text-green-400"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Schedule</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Deadlines &amp; targets</p>
                            </div>
                        </div>
                        <div class="p-6 space-y-5">

                            <!-- Due Date & Time -->
                            <div>
                                <label class="form-label" for="due_date">
                                    Due Date &amp; Time <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="datetime-local" id="due_date" name="due_date" required
                                           value="<?= htmlspecialchars($_POST['due_date'] ?? '') ?>"
                                           class="w-full pl-10 pr-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-lg
                                                  bg-white dark:bg-slate-800 text-slate-900 dark:text-white
                                                  focus:outline-none focus:ring-2 focus:ring-navy-500 focus:border-navy-500
                                                  transition-colors">
                                </div>
                                <p id="dueDateWarning" class="hidden mt-1 text-xs text-red-500 flex items-center gap-1">
                                    <i data-lucide="alert-triangle" class="w-3 h-3"></i> Due date cannot be in the past.
                                </p>
                            </div>

                            <!-- Target / Milestone -->
                            <div>
                                <label class="form-label" for="target">Target / Milestone</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="target" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                    <input type="text" id="target" name="target"
                                           placeholder="e.g. Submit 50 survey forms"
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
                                <h2 class="text-sm font-semibold text-slate-800 dark:text-white">Priority Level</h2>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Set task urgency</p>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 gap-3" id="priorityGroup">
                                <?php
                                $priorities = [
                                    'Low'      => ['icon' => 'arrow-down', 'cls' => 'badge-low',      'ring' => 'border-green-400'],
                                    'Medium'   => ['icon' => 'minus',      'cls' => 'badge-medium',    'ring' => 'border-yellow-400'],
                                    'High'     => ['icon' => 'arrow-up',   'cls' => 'badge-high',      'ring' => 'border-red-400'],
                                    'Critical' => ['icon' => 'zap',        'cls' => 'badge-critical',  'ring' => 'border-purple-400'],
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
                                        <span class="text-xs font-semibold"><?= $pname ?></span>
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
                                Live Preview
                            </h2>
                        </div>
                        <div class="p-6 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-white/60">Task ID</span>
                                <span class="text-white font-mono font-bold" id="prevTaskId"><?= htmlspecialchars($task_id_preview) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60">Title</span>
                                <span class="text-white font-medium truncate max-w-[130px] text-right" id="prevTitle">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60">Priority</span>
                                <span id="prevPriority" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Medium</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60">Allocation</span>
                                <span class="text-white" id="prevAllocation">By Name</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60">Due Date</span>
                                <span class="text-white" id="prevDue">—</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-white/60">Attachment</span>
                                <span class="text-white/70" id="prevAttachment">None</span>
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
                            <span id="submitBtnText">Create &amp; Allocate Task</span>
                        </button>
                        <button type="reset" onclick="resetForm()"
                                class="w-full flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl
                                       border border-slate-300 dark:border-slate-600
                                       bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700
                                       text-slate-700 dark:text-slate-200 text-sm font-medium
                                       focus:outline-none transition-colors">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                            Reset Form
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

    // ── Dark Mode ────────────────────────────────────────────────
    const themeToggle  = document.getElementById('themeToggle');
    const htmlEl       = document.documentElement;

    themeToggle.addEventListener('click', () => {
        htmlEl.classList.toggle('dark');
        lucide.createIcons();
    });

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
    const byNameRadio    = document.getElementById('alloc_by_name');
    const byRoleRadio    = document.getElementById('alloc_by_role');
    const byNameSection  = document.getElementById('byNameSection');
    const byRoleSection  = document.getElementById('byRoleSection');
    const boxByName      = document.getElementById('box-byname');
    const boxByRole      = document.getElementById('box-byrole');
    const prevAllocation = document.getElementById('prevAllocation');
    const roleSelect     = document.getElementById('assigned_role_id');
    const rolePanel      = document.getElementById('roleUsersPanel');
    const roleBadge      = document.getElementById('roleUsersBadge');
    const roleNone       = document.getElementById('roleUsersNone');
    const roleLoading    = document.getElementById('roleUsersLoading');
    const roleList       = document.getElementById('roleUsersList');
    const roleCountEl    = document.getElementById('roleUserCount');
    let roleUsersListVisible = false;

    function setAllocationUI(type) {
        if (type === 'by_name') {
            byNameSection.classList.remove('hidden');
            byRoleSection.classList.add('hidden');
            boxByName.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-navy-500 bg-navy-50 dark:bg-navy-900/20 transition-all';
            boxByRole.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 transition-all';
            prevAllocation.textContent = 'By Name';
        } else {
            byRoleSection.classList.remove('hidden');
            byNameSection.classList.add('hidden');
            boxByRole.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-saffron-500 bg-saffron-50 dark:bg-orange-900/20 transition-all';
            boxByName.className = 'flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 transition-all';
            prevAllocation.textContent = 'By Role';
            // Trigger fetch if a role is already selected
            if (roleSelect.value) fetchRoleUsers(roleSelect.value);
        }
        lucide.createIcons();
    }

    // ── AJAX: fetch users for the selected role ──────────────────
    function fetchRoleUsers(roleId) {
        if (!roleId) {
            rolePanel.classList.add('hidden');
            return;
        }
        // Show panel + loading
        rolePanel.classList.remove('hidden');
        roleLoading.classList.remove('hidden');
        roleBadge.classList.add('hidden');
        roleNone.classList.add('hidden');
        roleList.classList.add('hidden');
        roleList.innerHTML = '';
        roleUsersListVisible = false;
        document.getElementById('toggleUsersBtn') && (document.getElementById('toggleUsersBtn').textContent = 'Show all');

        fetch(`create_task.php?ajax=role_users&role_id=${roleId}`)
            .then(r => r.json())
            .then(data => {
                roleLoading.classList.add('hidden');
                if (data.count === 0) {
                    roleNone.classList.remove('hidden');
                    roleBadge.classList.add('hidden');
                } else {
                    roleNone.classList.add('hidden');
                    roleBadge.classList.remove('hidden');
                    roleCountEl.textContent = data.count;

                    // Build the user list HTML
                    roleList.innerHTML = data.users.map(u => `
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
                    lucide.createIcons();
                }
                // Update preview
                document.getElementById('prevAllocation').textContent = `By Role (${data.count} users)`;
            })
            .catch(() => {
                roleLoading.classList.add('hidden');
                roleNone.classList.remove('hidden');
            });
    }

    function toggleRoleUsersList() {
        roleUsersListVisible = !roleUsersListVisible;
        roleList.classList.toggle('hidden', !roleUsersListVisible);
        document.getElementById('toggleUsersBtn').textContent = roleUsersListVisible ? 'Hide' : 'Show all';
    }

    roleSelect.addEventListener('change', () => fetchRoleUsers(roleSelect.value));

    byNameRadio.addEventListener('change', () => setAllocationUI('by_name'));
    byRoleRadio.addEventListener('change', () => setAllocationUI('by_role'));

    // Initial state
    setAllocationUI(byRoleRadio.checked ? 'by_role' : 'by_name');

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
        prevPriority.textContent = value;
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
        titleCounter.textContent = `${len} / 255 characters`;
        titleCounter.className = 'mt-1 text-xs ' + (len > 240 ? 'text-red-500' : 'text-slate-400 dark:text-slate-500');
        document.getElementById('prevTitle').textContent = titleInput.value || '—';
    });

    // ── Due Date Validation ──────────────────────────────────────
    const dueDateInput   = document.getElementById('due_date');
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
            prevDue.textContent = val.toLocaleDateString('en-IN', opts);
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
        prevAttach.textContent = 'None';
    }

    // ── Form Submission Loading State ────────────────────────────
    document.getElementById('createTaskForm').addEventListener('submit', function (e) {
        // Basic validation
        const title = titleInput.value.trim();
        const due   = dueDateInput.value;
        const alloc = document.querySelector('input[name="allocation_type"]:checked')?.value;

        if (!title) { showToast('Task title is required.', 'warning'); e.preventDefault(); return; }
        if (!due)   { showToast('Please select a due date & time.', 'warning'); e.preventDefault(); return; }

        if (alloc === 'by_name' && !document.getElementById('assigned_user_id').value) {
            showToast('Please select an employee to assign.', 'warning'); e.preventDefault(); return;
        }
        if (alloc === 'by_role' && !document.getElementById('assigned_role_id').value) {
            showToast('Please select a role to assign.', 'warning'); e.preventDefault(); return;
        }

        const btn  = document.getElementById('submitBtn');
        const text = document.getElementById('submitBtnText');
        btn.disabled  = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.textContent = 'Creating Task…';
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
        titleCounter.textContent = '0 / 255 characters';
        dueDateWarning.classList.add('hidden');
        lucide.createIcons();
    }

    // ── Re-init icons after DOM updates ─────────────────────────
    lucide.createIcons();

    <?php if ($success_msg): ?>
    showToast('<?= addslashes(strip_tags($success_msg)) ?> created!', 'success');
    <?php endif; ?>
</script>

</body>
</html>
