<?php
session_start();
require_once 'include/dbConfig.php';

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        'title' => 'User Management - Amravati Connect',
        'brand_name' => 'Amravati Connect',
        'menu_main_modules' => 'Main Modules',
        'menu_dashboard' => 'Executive Dashboard',
        'menu_admin' => 'Administration',
        'menu_users' => 'User Management',
        'page_title' => 'User Management',
        'page_subtitle' => 'Create, update, and manage system users and officers.',
        'form_create_title' => 'Create New User',
        'form_update_title' => 'Update User Information',
        'label_emp_code' => 'Employee Code',
        'label_full_name' => 'Full Name *',
        'label_email' => 'Email *',
        'label_mobile' => 'Mobile *',
        'label_department' => 'Department',
        'select_department' => '-- Select Department --',
        'label_role' => 'Role',
        'select_role' => '-- Select Role --',
        'label_taluka' => 'Taluka',
        'select_taluka' => '-- Select Taluka --',
        'label_village' => 'Village',
        'select_village' => '-- Select Village --',
        'label_status' => 'Status',
        'status_active' => 'Active',
        'status_inactive' => 'Inactive',
        'btn_cancel' => 'Cancel',
        'btn_clear' => 'Clear Form',
        'btn_save' => 'Save User',
        'btn_update' => 'Update User',
        'table_title' => 'Registered Users',
        'search_placeholder' => 'Search users...',
        'btn_search' => 'Search',
        'btn_clear_search' => 'Clear',
        'col_sr_no' => 'Sr. No',
        'col_user_details' => 'User Details',
        'col_contact' => 'Contact',
        'col_dept_role' => 'Department / Role',
        'col_status' => 'Status',
        'col_actions' => 'Actions',
        'no_users' => 'No users found.',
        'confirm_delete' => 'Are you sure you want to deactivate/delete this user?',
        'role_administrator' => 'System Administrator',
        'role_collector' => 'District Collector',
        'role_additional_collector' => 'Additional Collector',
        'role_deputy_collector' => 'Deputy Collector',
        'role_sdo' => 'Sub-Divisional Officer',
        'role_tehsildar' => 'Tehsildar',
        'role_bdo' => 'Block Development Officer',
        'role_talathi' => 'Talathi',
        'role_gramsevak' => 'Gramsevak',
        'badge_level' => 'Level',
    ],
    'mr' => [
        'title' => 'वापरकर्ता व्यवस्थापन - अमरावती कनेक्ट',
        'brand_name' => 'अमरावती कनेक्ट',
        'menu_main_modules' => 'मुख्य मॉड्युल्स',
        'menu_dashboard' => 'कार्यकारी डॅशबोर्ड',
        'menu_admin' => 'प्रशासन',
        'menu_users' => 'वापरकर्ता व्यवस्थापन',
        'page_title' => 'वापरकर्ता व्यवस्थापन',
        'page_subtitle' => 'सिस्टम वापरकर्ते आणि अधिकार्‍यांची निर्मिती, अद्ययावत आणि व्यवस्थापन करा.',
        'form_create_title' => 'नवीन वापरकर्ता तयार करा',
        'form_update_title' => 'वापरकर्ता माहिती अद्ययावत करा',
        'label_emp_code' => 'कर्मचारी कोड',
        'label_full_name' => 'पूर्ण नाव *',
        'label_email' => 'ईमेल *',
        'label_mobile' => 'मोबाईल *',
        'label_department' => 'विभाग',
        'select_department' => '-- विभाग निवडा --',
        'label_role' => 'भूमिका / पद',
        'select_role' => '-- भूमिका निवडा --',
        'label_taluka' => 'तालुका',
        'select_taluka' => '-- तालुका निवडा --',
        'label_village' => 'गाव',
        'select_village' => '-- गाव निवडा --',
        'label_status' => 'स्थिती',
        'status_active' => 'सक्रिय',
        'status_inactive' => 'निष्क्रिय',
        'btn_cancel' => 'रद्द करा',
        'btn_clear' => 'फॉर्म साफ करा',
        'btn_save' => 'वापरकर्ता जतन करा',
        'btn_update' => 'वापरकर्ता अद्ययावत करा',
        'table_title' => 'नोंदणीकृत वापरकर्ते',
        'search_placeholder' => 'वापरकर्ते शोधा...',
        'btn_search' => 'शोधा',
        'btn_clear_search' => 'साफ करा',
        'col_sr_no' => 'अ.क्र.',
        'col_user_details' => 'वापरकर्त्याचा तपशील',
        'col_contact' => 'संपर्क',
        'col_dept_role' => 'विभाग / भूमिका',
        'col_status' => 'स्थिती',
        'col_actions' => 'कृती',
        'no_users' => 'कोणतेही वापरकर्ते आढळले नाहीत.',
        'confirm_delete' => 'आपण नक्की या वापरकर्त्याला निष्क्रिय/हटवू इच्छिता?',
        'role_administrator' => 'सिस्टम प्रशासक',
        'role_collector' => 'जिल्हाधिकारी',
        'role_additional_collector' => 'अपर जिल्हाधिकारी',
        'role_deputy_collector' => 'उपजिल्हाधिकारी',
        'role_sdo' => 'उपविभागीय अधिकारी (SDO)',
        'role_tehsildar' => 'तहसीलदार',
        'role_bdo' => 'गट विकास अधिकारी (BDO)',
        'role_talathi' => 'तलाठी',
        'role_gramsevak' => 'ग्रामसेवक',
        'badge_level' => 'स्तर',
    ]
];
$t = $translations[$lang];

/* ─── Map login session keys to dashboard variables ────────────────── */
if (isset($_SESSION['role_name'])) {
    $_SESSION['user_role']       = $_SESSION['role_name'];
    $_SESSION['user_name']       = $_SESSION['full_name'];
    $_SESSION['user_taluka_id']  = $_SESSION['taluka_id'];
    $_SESSION['user_village_id'] = $_SESSION['village_id'];
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
$sTalukaId  = (int) ($_SESSION['user_taluka_id']  ?? 1);
$sVillageId = (int) ($_SESSION['user_village_id'] ?? 1);

/* ─── Role → Level map ─────────────────────────────────────── */
const ROLE_LEVEL_MAP = [
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

function getDashboardLevel(string $role, mysqli $conn): int {
    try {
        $stmt = $conn->prepare("SELECT role_level FROM roles WHERE role_name = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('s', $role);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $stmt->close();
                return (int)$row['role_level'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log('getDashboardLevel DB error: ' . $e->getMessage());
    }
    return ROLE_LEVEL_MAP[$role] ?? 3;
}

$level = getDashboardLevel($sRole, $conn);
if ($level !== 1) {
    header("Location: dashboard.php?lang=" . $lang);
    exit();
}

$roleKey = match($sRole) {
    'Administrator', 'System Administrator' => 'role_administrator',
    'Collector' => 'role_collector',
    'Additional Collector' => 'role_additional_collector',
    'Deputy Collector' => 'role_deputy_collector',
    'SDO' => 'role_sdo',
    'Tehsildar' => 'role_tehsildar',
    'BDO' => 'role_bdo',
    'Talathi' => 'role_talathi',
    'Gramsevak' => 'role_gramsevak',
    default => '',
};
$roleLabel = $roleKey ? $t[$roleKey] : $sRole;

$parts    = array_filter(explode(' ', trim($sName)));
$initials = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));

// Initialize variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$edit_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';
$msgType = '';

// Handle Create and Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_user'])) {
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $employee_code = $_POST['employee_code'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $department_id = !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;
        $role_id = !empty($_POST['role_id']) ? intval($_POST['role_id']) : null;
        $district_id = 1; // Hardcoded to Amravati (1)
        $taluka_id = !empty($_POST['taluka_id']) ? intval($_POST['taluka_id']) : null;
        $village_id = !empty($_POST['village_id']) ? intval($_POST['village_id']) : null;
        $status = $_POST['status'];

        // Basic validations
        if (empty($full_name) || empty($email) || empty($mobile)) {
            $msg = "Full Name, Email, and Mobile are required.";
            $msgType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid email format.";
            $msgType = "error";
        } else {
            // Check for duplicate email
            $checkEmailSql = "SELECT user_id FROM users WHERE email = ? AND user_id != ?";
            $stmtCheck = $conn->prepare($checkEmailSql);
            $stmtCheck->bind_param("si", $email, $user_id);
            $stmtCheck->execute();
            $stmtCheck->store_result();
            if ($stmtCheck->num_rows > 0) {
                $msg = "Email already exists. Please use a different email.";
                $msgType = "error";
            } else {
                if ($user_id > 0) {
                    // Update
                    $updateSql = "UPDATE users SET employee_code=?, full_name=?, email=?, mobile=?, department_id=?, role_id=?, district_id=?, taluka_id=?, village_id=?, status=? WHERE user_id=?";
                    $stmtUpdate = $conn->prepare($updateSql);
                    $stmtUpdate->bind_param("ssssiiiiisi", $employee_code, $full_name, $email, $mobile, $department_id, $role_id, $district_id, $taluka_id, $village_id, $status, $user_id);
                    if ($stmtUpdate->execute()) {
                        $_SESSION['msg'] = "User updated successfully!";
                        $_SESSION['msgType'] = "success";
                        header("Location: user_creation.php?lang=" . $lang);
                        exit();
                    } else {
                        $msg = "Error updating user: " . $conn->error;
                        $msgType = "error";
                    }
                } else {
                    // Insert - Auto generate employee code
                    $maxIdQuery = $conn->query("SELECT MAX(user_id) AS max_id FROM users");
                    $maxRow = $maxIdQuery->fetch_assoc();
                    $nextId = intval($maxRow['max_id']) + 1;
                    $generated_employee_code = "EMP" . str_pad($nextId, 5, "0", STR_PAD_LEFT);

                    // Fetch role_name to determine the default password
                    $defaultPassText = 'test@123';
                    if ($role_id) {
                        $roleStmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
                        if ($roleStmt) {
                            $roleStmt->bind_param("i", $role_id);
                            $roleStmt->execute();
                            $roleRes = $roleStmt->get_result();
                            if ($roleRow = $roleRes->fetch_assoc()) {
                                $roleNameStr = $roleRow['role_name'];
                                if ($roleNameStr === 'Administrator' || $roleNameStr === 'System Administrator') {
                                    $defaultPassText = 'Admin@123';
                                }
                            }
                            $roleStmt->close();
                        }
                    }
                    $defaultPassword = password_hash($defaultPassText, PASSWORD_DEFAULT);

                    $insertSql = "INSERT INTO users (employee_code, full_name, email, mobile, department_id, role_id, district_id, taluka_id, village_id, password_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtInsert = $conn->prepare($insertSql);
                    $stmtInsert->bind_param("ssssiiiiiss", $generated_employee_code, $full_name, $email, $mobile, $department_id, $role_id, $district_id, $taluka_id, $village_id, $defaultPassword, $status);
                    if ($stmtInsert->execute()) {
                        $_SESSION['msg'] = "User created successfully!";
                        $_SESSION['msgType'] = "success";
                        header("Location: user_creation.php?lang=" . $lang);
                        exit();
                    } else {
                        $msg = "Error creating user: " . $conn->error;
                        $msgType = "error";
                    }
                }
            }
        }
    }
}

// Handle Delete (Soft Delete)
if ($action === 'delete' && $edit_id > 0) {
    $deleteSql = "UPDATE users SET status = 'Inactive' WHERE user_id = ?";
    $stmtDelete = $conn->prepare($deleteSql);
    $stmtDelete->bind_param("i", $edit_id);
    if ($stmtDelete->execute()) {
        $_SESSION['msg'] = "User deleted (deactivated) successfully!";
        $_SESSION['msgType'] = "success";
    } else {
        $_SESSION['msg'] = "Error deleting user.";
        $_SESSION['msgType'] = "error";
    }
    header("Location: user_creation.php?lang=" . $lang);
    exit();
}

// Get message from session if exists
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    $msgType = $_SESSION['msgType'];
    unset($_SESSION['msg']);
    unset($_SESSION['msgType']);
}

// Fetch dropdown data
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE status = 'Active'");
$roles = $conn->query("SELECT role_id, role_name FROM roles WHERE status = 'Active'");
$talukas = $conn->query("SELECT taluka_id, taluka_name FROM talukas");
$villages = $conn->query("SELECT village_id, village_name, taluka_id FROM villages");
$allVillages = [];
if ($villages) {
    while ($v = $villages->fetch_assoc()) {
        $allVillages[] = $v;
    }
}

// Fetch single user for edit
$editData = null;
if ($action === 'edit' && $edit_id > 0) {
    $editQuery = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $editQuery->bind_param("i", $edit_id);
    $editQuery->execute();
    $editResult = $editQuery->get_result();
    $editData = $editResult->fetch_assoc();
}

// Fetch all users for table (with search)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$whereClause = "";
if ($search) {
    $searchTerm = "%" . $conn->real_escape_string($search) . "%";
    $whereClause = "WHERE u.full_name LIKE '$searchTerm' OR u.email LIKE '$searchTerm' OR u.mobile LIKE '$searchTerm' OR u.employee_code LIKE '$searchTerm'";
}

$usersQuery = "SELECT u.*, d.department_name, r.role_name 
               FROM users u 
               LEFT JOIN departments d ON u.department_id = d.department_id 
               LEFT JOIN roles r ON u.role_id = r.role_id 
               $whereClause 
               ORDER BY u.user_id DESC";
$usersResult = $conn->query($usersQuery);

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
    
    <!-- Tailwind Config for Design System -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        border: "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        navy: {
                            50: '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1a365d',
                            600: '#152b4a',
                            700: '#0f1f38',
                            900: '#0a1424'
                        },
                        govgreen: {
                            50: '#edf7ed',
                            100: '#cce8cc',
                            500: '#2e7d32',
                            600: '#256428'
                        },
                        saffron: {
                            50: '#fff3e0',
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
        /* Base styles and ShadCN-like variables */
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

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Level badges */
        .badge-l1 { background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; }
        .badge-l2 { background:#fef3c7; color:#92400e; border:1px solid #fde68a; }
        .badge-l3 { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .dark .badge-l1 { background:#1e3a8a33; color:#93c5fd; border-color:#1e40af; }
        .dark .badge-l2 { background:#92400e33; color:#fcd34d; border-color:#b45309; }
        .dark .badge-l3 { background:#065f4633; color:#6ee7b7; border-color:#047857; }

        /* Active nav */
        .nav-active { background:#eef2f6; color:#152b4a; }
        .dark .nav-active { background:#1e293b; color:#fff; }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
    <aside id="sidebar"
           class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800
                  flex flex-col transition-all duration-300 z-20">

        <!-- Logo / Brand -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
                <i data-lucide="landmark" class="text-white w-5 h-5"></i>
            </div>
            <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight"><?= htmlspecialchars($t['brand_name']) ?></span>
        </div>

        <!-- Navigation -->
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-3">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4"><?= htmlspecialchars($t['menu_main_modules']) ?></p>
                <a href="dashboard.php?lang=<?= $lang ?>"
                   class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                    <?= htmlspecialchars($t['menu_dashboard']) ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="network"   class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_task_alloc'] ?? 'Task Allocation') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_announcements'] ?? 'Announcements') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="award"     class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_appreciation'] ?? 'Appreciation') ?>
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($translations[$lang]['menu_analytics'] ?? 'Analytics & Data') ?></p>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="pie-chart"   class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_reports'] ?? 'Reports & Analytics') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="map"         class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_gis'] ?? 'GIS Map View') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_docs'] ?? 'Document Management') ?>
                </a>

                <?php if ($level === 1): ?>
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6"><?= htmlspecialchars($t['menu_admin']) ?></p>
                <a href="user_creation.php?lang=<?= $lang ?>"
                   class="nav-active flex items-center px-3 py-2.5 text-sm font-medium rounded-md">
                    <i data-lucide="users" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                    <?= htmlspecialchars($t['menu_users']) ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="map-pin"      class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_hierarchy'] ?? 'Location Hierarchy') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_audit'] ?? 'Audit Logs') ?>
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="settings"     class="w-5 h-5 mr-3 text-slate-400"></i><?= htmlspecialchars($translations[$lang]['menu_settings'] ?? 'Settings') ?>
                </a>
                <?php endif; ?>
                <a href="logout.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md
                    text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5 mr-3 text-red-500"></i><?= htmlspecialchars($translations[$lang]['menu_logout'] ?? 'Logout') ?>
                </a>
            </nav>
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
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <?php
                $queryParams = $_GET;
                $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
                $lang_switch_url = 'user_creation.php?' . http_build_query($queryParams);
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

                <!-- Notifications -->
                <button class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400
                               dark:hover:text-slate-200 rounded-full
                               hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full
                                 bg-saffron-500 ring-2 ring-white dark:ring-slate-900"></span>
                </button>

                <!-- Profile -->
                <div class="flex items-center space-x-3 border-l border-slate-200
                            dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                            <?= htmlspecialchars($sName) ?>
                        </span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($roleLabel) ?>
                        </span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center
                                text-white font-bold text-sm border-2 border-white dark:border-slate-800 shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT SCROLL AREA -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex items-center space-x-3 flex-wrap gap-y-2">
                    <!-- Access level badge -->
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                                 <?= $level===1 ? 'badge-l1' : ($level===2 ? 'badge-l2' : 'badge-l3') ?>">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                        <?= htmlspecialchars($t['badge_level']) ?> <?= $level ?> &middot; <?= htmlspecialchars($roleLabel) ?>
                    </span>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($msg): ?>
                <div class="mb-6 px-4 py-3 rounded-md flex items-center shadow-sm <?= $msgType === 'success' ? 'bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-400' : 'bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-800 dark:text-red-400' ?>">
                    <i data-lucide="<?= $msgType === 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-5 h-5 mr-2"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($msg) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                        <?= $editData ? htmlspecialchars($t['form_update_title']) : htmlspecialchars($t['form_create_title']) ?>
                    </h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="user_creation.php?lang=<?= $lang ?>">
                        <input type="hidden" name="user_id" value="<?= $editData ? $editData['user_id'] : '' ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            
                            <!-- Employee Code -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_emp_code']) ?></label>
                                <input type="text" name="employee_code" value="<?= $editData ? htmlspecialchars($editData['employee_code']) : '(Auto-generated)' ?>" readonly
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-slate-100 dark:bg-slate-600 text-slate-500 dark:text-slate-400 cursor-not-allowed focus:outline-none">
                            </div>

                            <!-- Full Name -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_full_name']) ?></label>
                                <input type="text" name="full_name" required value="<?= $editData ? htmlspecialchars($editData['full_name']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_email']) ?></label>
                                <input type="email" name="email" required value="<?= $editData ? htmlspecialchars($editData['email']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Mobile -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_mobile']) ?></label>
                                <input type="text" name="mobile" required value="<?= $editData ? htmlspecialchars($editData['mobile']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_department']) ?></label>
                                <select name="department_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value=""><?= htmlspecialchars($t['select_department']) ?></option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?= $dept['department_id'] ?>" <?= ($editData && $editData['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['department_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Role -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_role']) ?></label>
                                <select name="role_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value=""><?= htmlspecialchars($t['select_role']) ?></option>
                                    <?php while ($role = $roles->fetch_assoc()): ?>
                                        <option value="<?= $role['role_id'] ?>" <?= ($editData && $editData['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['role_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Taluka -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_taluka']) ?></label>
                                <select name="taluka_id" id="taluka_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value=""><?= htmlspecialchars($t['select_taluka']) ?></option>
                                    <?php while ($taluka = $talukas->fetch_assoc()): ?>
                                        <option value="<?= $taluka['taluka_id'] ?>" <?= ($editData && $editData['taluka_id'] == $taluka['taluka_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($taluka['taluka_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Village -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_village']) ?></label>
                                <select name="village_id" id="village_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value=""><?= htmlspecialchars($t['select_village']) ?></option>
                                    <!-- Populated by JavaScript based on Taluka -->
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"><?= htmlspecialchars($t['label_status']) ?></label>
                                <select name="status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="Active" <?= ($editData && $editData['status'] == 'Active') ? 'selected' : '' ?>><?= htmlspecialchars($t['status_active']) ?></option>
                                    <option value="Inactive" <?= ($editData && $editData['status'] == 'Inactive') ? 'selected' : '' ?>><?= htmlspecialchars($t['status_inactive']) ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-4 border-t border-slate-200 dark:border-slate-700 pt-5">
                            <?php if ($editData): ?>
                                <a href="user_creation.php?lang=<?= $lang ?>" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <?= htmlspecialchars($t['btn_cancel']) ?>
                                </a>
                            <?php else: ?>
                                <button type="reset" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    <?= htmlspecialchars($t['btn_clear']) ?>
                                </button>
                            <?php endif; ?>
                            
                            <button type="submit" name="save_user" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                                <i data-lucide="<?= $editData ? 'save' : 'plus' ?>" class="w-4 h-4 mr-2"></i>
                                <?= $editData ? htmlspecialchars($t['btn_update']) : htmlspecialchars($t['btn_save']) ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($t['table_title']) ?></h2>
                    
                    <form method="GET" action="user_creation.php" class="flex space-x-2">
                        <input type="hidden" name="lang" value="<?= $lang ?>">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                            </div>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="<?= htmlspecialchars($t['search_placeholder']) ?>" 
                                class="block w-full pl-10 pr-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500">
                        </div>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                            <?= htmlspecialchars($t['btn_search']) ?>
                        </button>
                        <?php if ($search): ?>
                            <a href="user_creation.php?lang=<?= $lang ?>" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 transition-colors"><?= htmlspecialchars($t['btn_clear_search']) ?></a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_sr_no']) ?></th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_user_details']) ?></th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_contact']) ?></th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_dept_role']) ?></th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_status']) ?></th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider"><?= htmlspecialchars($t['col_actions']) ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <?php if ($usersResult->num_rows > 0): ?>
                                <?php $sr = 1; while ($row = $usersResult->fetch_assoc()): ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                                            <?= $sr++ ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white mr-3">
                                                    <?= strtoupper(substr($row['full_name'], 0, 2)) ?>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900 dark:text-white"><?= htmlspecialchars($row['full_name']) ?></div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400">Code: <?= htmlspecialchars($row['employee_code'] ?: 'N/A') ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900 dark:text-white"><?= htmlspecialchars($row['email']) ?></div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($row['mobile']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900 dark:text-white"><?= htmlspecialchars($row['department_name'] ?: 'N/A') ?></div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                <?php
                                                $dbRoleKey = match($row['role_name']) {
                                                    'Administrator', 'System Administrator' => 'role_administrator',
                                                    'Collector' => 'role_collector',
                                                    'Additional Collector' => 'role_additional_collector',
                                                    'Deputy Collector' => 'role_deputy_collector',
                                                    'SDO' => 'role_sdo',
                                                    'Tehsildar' => 'role_tehsildar',
                                                    'BDO' => 'role_bdo',
                                                    'Talathi' => 'role_talathi',
                                                    'Gramsevak' => 'role_gramsevak',
                                                    default => '',
                                                };
                                                echo htmlspecialchars($dbRoleKey ? $t[$dbRoleKey] : $row['role_name']);
                                                ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($row['status'] == 'Active'): ?>
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
                                                    <?= htmlspecialchars($t['status_active']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                    <?= htmlspecialchars($t['status_inactive']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="user_creation.php?action=edit&id=<?= $row['user_id'] ?>&lang=<?= $lang ?>" class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 mr-3 inline-flex items-center">
                                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?= $row['user_id'] ?>)" class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 inline-flex items-center">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                        <?= htmlspecialchars($t['no_users']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>

    <!-- Initialize Icons & Dark Mode Logic -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Dark Mode Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        function updateTheme(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
        }

        themeToggle.addEventListener('click', () => {
            const isDark = !htmlElement.classList.contains('dark');
            updateTheme(isDark);
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full') || sidebar.style.display === 'none') {
                sidebar.classList.remove('-translate-x-full');
                sidebar.style.display = 'flex';
            } else {
                sidebar.classList.add('-translate-x-full');
                setTimeout(() => sidebar.style.display = 'none', 300);
            }
        });

        // Delete Confirmation
        function confirmDelete(id) {
            if (confirm("<?= htmlspecialchars($t['confirm_delete']) ?>")) {
                window.location.href = "user_creation.php?action=delete&id=" + id + "&lang=<?= $lang ?>";
            }
        }

        // Dynamic Village Dropdown Logic
        const allVillages = <?= json_encode($allVillages) ?>;
        const selectedVillageId = <?= $editData && $editData['village_id'] ? $editData['village_id'] : 'null' ?>;
        const talukaSelect = document.getElementById('taluka_id');
        const villageSelect = document.getElementById('village_id');

        function populateVillages() {
            const talukaId = talukaSelect.value;
            villageSelect.innerHTML = '<option value=""><?= htmlspecialchars($t['select_village']) ?></option>';
            
            if (talukaId) {
                const filteredVillages = allVillages.filter(v => v.taluka_id == talukaId);
                filteredVillages.forEach(v => {
                    const option = document.createElement('option');
                    option.value = v.village_id;
                    option.textContent = v.village_name;
                    if (selectedVillageId && selectedVillageId == v.village_id) {
                        option.selected = true;
                    }
                    villageSelect.appendChild(option);
                });
            }
        }

        talukaSelect.addEventListener('change', populateVillages);
        
        // Initial population if taluka is already selected (e.g. edit mode)
        if (talukaSelect.value) {
            populateVillages();
        }
    </script>
</body>
</html>
