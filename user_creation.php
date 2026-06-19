<?php
session_start();
require_once 'include/dbConfig.php';

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
                        header("Location: user_creation.php");
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

                    $defaultPassword = password_hash('Welcome@123', PASSWORD_DEFAULT);
                    $insertSql = "INSERT INTO users (employee_code, full_name, email, mobile, department_id, role_id, district_id, taluka_id, village_id, password_hash, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmtInsert = $conn->prepare($insertSql);
                    $stmtInsert->bind_param("ssssiiiiiss", $generated_employee_code, $full_name, $email, $mobile, $department_id, $role_id, $district_id, $taluka_id, $village_id, $defaultPassword, $status);
                    if ($stmtInsert->execute()) {
                        $_SESSION['msg'] = "User created successfully!";
                        $_SESSION['msgType'] = "success";
                        header("Location: user_creation.php");
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
    header("Location: user_creation.php");
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
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Amravati Connect</title>
    
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
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-navy-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
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
                <a href="index.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Executive Dashboard
                </a>
                
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
                <a href="user_creation.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-r-md bg-saffron-50 text-saffron-600 border-l-4 border-saffron-500 dark:bg-slate-800 dark:text-saffron-400 dark:border-saffron-500">
                    <i data-lucide="users" class="w-5 h-5 mr-3 text-saffron-600 dark:text-saffron-400"></i>
                    User Management
                </a>
            </nav>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- National Tricolor Bar -->
        <div class="h-1.5 w-full bg-gradient-to-r from-[#FF9933] via-white to-[#138808] shrink-0"></div>

        <!-- GLOBAL HEADER -->
        <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Theme Switcher -->
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                </button>
            </div>
        </header>

        <!-- MAIN CONTENT SCROLL AREA -->
        <main class="flex-1 overflow-y-auto bg-navy-50 dark:bg-slate-900 p-6 sm:p-8">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">User Management</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create, update, and manage system users and officers.</p>
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
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border-t-4 border-t-navy-600 border-x border-b border-slate-200 dark:border-slate-700 mb-8 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-navy-50 dark:bg-slate-800/50">
                    <h2 class="text-lg font-semibold text-navy-700 dark:text-white">
                        <?= $editData ? 'Update User Information' : 'Create New User' ?>
                    </h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="user_creation.php">
                        <input type="hidden" name="user_id" value="<?= $editData ? $editData['user_id'] : '' ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            
                            <!-- Employee Code -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Employee Code</label>
                                <input type="text" name="employee_code" value="<?= $editData ? htmlspecialchars($editData['employee_code']) : '(Auto-generated)' ?>" readonly
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md bg-slate-100 dark:bg-slate-600 text-slate-500 dark:text-slate-400 cursor-not-allowed focus:outline-none">
                            </div>

                            <!-- Full Name -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                                <input type="text" name="full_name" required value="<?= $editData ? htmlspecialchars($editData['full_name']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email *</label>
                                <input type="email" name="email" required value="<?= $editData ? htmlspecialchars($editData['email']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Mobile -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mobile *</label>
                                <input type="text" name="mobile" required value="<?= $editData ? htmlspecialchars($editData['mobile']) : '' ?>"
                                    class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                            </div>

                            <!-- Department -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Department</label>
                                <select name="department_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="">-- Select Department --</option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?= $dept['department_id'] ?>" <?= ($editData && $editData['department_id'] == $dept['department_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['department_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Role -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Role</label>
                                <select name="role_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="">-- Select Role --</option>
                                    <?php while ($role = $roles->fetch_assoc()): ?>
                                        <option value="<?= $role['role_id'] ?>" <?= ($editData && $editData['role_id'] == $role['role_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['role_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Taluka -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Taluka</label>
                                <select name="taluka_id" id="taluka_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="">-- Select Taluka --</option>
                                    <?php while ($taluka = $talukas->fetch_assoc()): ?>
                                        <option value="<?= $taluka['taluka_id'] ?>" <?= ($editData && $editData['taluka_id'] == $taluka['taluka_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($taluka['taluka_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Village -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Village</label>
                                <select name="village_id" id="village_id" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="">-- Select Village --</option>
                                    <!-- Populated by JavaScript based on Taluka -->
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-md focus:outline-none focus:ring-1 focus:ring-navy-500 bg-white dark:bg-slate-700 text-slate-900 dark:text-white">
                                    <option value="Active" <?= ($editData && $editData['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= ($editData && $editData['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-4 border-t border-slate-200 dark:border-slate-700 pt-5">
                            <?php if ($editData): ?>
                                <a href="user_creation.php" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Cancel
                                </a>
                            <?php else: ?>
                                <button type="reset" class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                    Clear Form
                                </button>
                            <?php endif; ?>
                            
                            <button type="submit" name="save_user" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-bold rounded-md text-white bg-govgreen-600 hover:bg-govgreen-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-govgreen-500 transition-colors">
                                <i data-lucide="<?= $editData ? 'save' : 'plus' ?>" class="w-4 h-4 mr-2"></i>
                                <?= $editData ? 'Update User' : 'Save User' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Registered Users</h2>
                    
                    <form method="GET" action="user_creation.php" class="flex space-x-2">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                            </div>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search users..." 
                                class="block w-full pl-10 pr-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500">
                        </div>
                        <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                            Search
                        </button>
                        <?php if ($search): ?>
                            <a href="user_creation.php" class="px-4 py-2 border border-slate-300 dark:border-slate-600 rounded-md shadow-sm text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 transition-colors">Clear</a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-navy-50 dark:bg-slate-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Sr. No</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">User Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Contact</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Department / Role</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
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
                                            <div class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($row['role_name'] ?: 'N/A') ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($row['status'] == 'Active'): ?>
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
                                                    Active
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                    Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="user_creation.php?action=edit&id=<?= $row['user_id'] ?>" class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 mr-3 inline-flex items-center">
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
                                        No users found.
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
            if (confirm("Are you sure you want to deactivate/delete this user?")) {
                window.location.href = "user_creation.php?action=delete&id=" + id;
            }
        }

        // Dynamic Village Dropdown Logic
        const allVillages = <?= json_encode($allVillages) ?>;
        const selectedVillageId = <?= $editData && $editData['village_id'] ? $editData['village_id'] : 'null' ?>;
        const talukaSelect = document.getElementById('taluka_id');
        const villageSelect = document.getElementById('village_id');

        function populateVillages() {
            const talukaId = talukaSelect.value;
            villageSelect.innerHTML = '<option value="">-- Select Village --</option>';
            
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
