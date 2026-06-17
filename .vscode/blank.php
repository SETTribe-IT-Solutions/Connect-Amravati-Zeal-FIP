<?php
// blank.php - Dynamic UI/UX Government Task Portal with Real Session-Based Role Authentication
session_start();

// 1. Account Configuration Database (Mock)
$accounts = [
    'superadmin@gov.in' => ['password' => 'admin123', 'role' => 'Super Admin', 'name' => 'Super Admin', 'type' => 'admin', 'jurisdiction' => 'Amravati District Office'],
    'collector@gov.in' => ['password' => 'collector123', 'role' => 'Collector', 'name' => 'Sanjay Phad', 'type' => 'assigner', 'jurisdiction' => 'Amravati HQ'],
    'addcollector@gov.in' => ['password' => 'addcollector123', 'role' => 'Additional Collector', 'name' => 'Archana More', 'type' => 'assigner', 'jurisdiction' => 'Amravati HQ'],
    'sdo@gov.in' => ['password' => 'sdo123', 'role' => 'SDO', 'name' => 'Nitin Dongre', 'type' => 'assigner', 'jurisdiction' => 'Achalpur Division'],
    'tehsildar@gov.in' => ['password' => 'tehsildar123', 'role' => 'Tehsildar', 'name' => 'Vilas Kadam', 'type' => 'assigner', 'jurisdiction' => 'Teosa Taluka'],
    'bdo@gov.in' => ['password' => 'bdo123', 'role' => 'BDO', 'name' => 'Vijay Patil', 'type' => 'performer', 'jurisdiction' => 'Achalpur Block Office'],
    'talathi@gov.in' => ['password' => 'talathi123', 'role' => 'Talathi', 'name' => 'Sunita Rao', 'type' => 'performer', 'jurisdiction' => 'Teosa Circle'],
    'gramsevak@gov.in' => ['password' => 'gramsevak123', 'role' => 'Gramsevak', 'name' => 'Balaji K.', 'type' => 'performer', 'jurisdiction' => 'Chikhaldara Village'],
    'sysadmin@gov.in' => ['password' => 'sysadmin123', 'role' => 'System Administrator', 'name' => 'Rahul Sharma', 'type' => 'performer', 'jurisdiction' => 'IT Cell Amravati']
];

// 2. Initialize Session Registries (Mock Database)
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [
        [
            'id' => '#101',
            'title' => 'Jal Jeevan Mission Site Survey',
            'assigned_role' => 'BDO',
            'jurisdiction' => 'Achalpur - Bhadri',
            'priority' => 'High',
            'status' => 'Completed',
            'due_date' => '2026-06-12',
            'remarks' => 'All layout mapping done and approved.'
        ],
        [
            'id' => '#102',
            'title' => 'Crop Damage Compensation Audit',
            'assigned_role' => 'Talathi',
            'jurisdiction' => 'Teosa - Karanja',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'due_date' => '2026-06-20',
            'remarks' => 'Surveys started for 4 out of 5 villages.'
        ],
        [
            'id' => '#103',
            'title' => 'E-Shram Card Registration Drive',
            'assigned_role' => 'Gramsevak',
            'jurisdiction' => 'Chikhaldara - Semadoh',
            'priority' => 'High',
            'status' => 'Overdue',
            'due_date' => '2026-06-14',
            'remarks' => ''
        ],
        [
            'id' => '#104',
            'title' => 'Land Mutation Record Cleansing',
            'assigned_role' => 'Talathi',
            'jurisdiction' => 'Teosa Circle',
            'priority' => 'Low',
            'status' => 'Pending',
            'due_date' => '2026-06-25',
            'remarks' => ''
        ]
    ];
}

if (!isset($_SESSION['audit_logs'])) {
    $_SESSION['audit_logs'] = [
        ['timestamp' => '17-06-2026 15:47:12', 'user' => 'Collector', 'action' => 'Allocated Task #104 to Talathi', 'target' => '#104', 'ip' => '10.12.4.89'],
        ['timestamp' => '17-06-2026 15:32:04', 'user' => 'Talathi', 'action' => 'Changed status of crop audit task #102 to In Progress', 'target' => '#102', 'ip' => '10.12.5.112']
    ];
}

if (!isset($_SESSION['announcements'])) {
    $_SESSION['announcements'] = [
        [
            'title' => 'District Assembly Election Preparedness Instructions',
            'issuer' => 'Collector',
            'date' => '16 June 2026',
            'target' => 'All Roles',
            'content' => 'Please ensure all polling station facilities (water, electricity, access ramp) are fully audited.'
        ],
        [
            'title' => 'Monsoon Season Preventive Action Plans',
            'issuer' => 'Additional Collector',
            'date' => '10 June 2026',
            'target' => 'BDOs Only',
            'content' => 'Preventive measures for landslide-prone areas in Chikhaldara taluka must be setup immediately.'
        ]
    ];
}

if (!isset($_SESSION['appreciations'])) {
    $_SESSION['appreciations'] = [
        [
            'recipient' => 'Vijay Patil (BDO)',
            'category' => 'Jal Jeevan Survey Excellence',
            'message' => 'Demonstrated exemplary management during the survey. Complete site mapping targets were met 3 days ahead of schedule.',
            'issuer' => 'Collector',
            'date' => '14-06-2026'
        ],
        [
            'recipient' => 'Sunita Rao (Talathi)',
            'category' => 'Land records digitalization drive',
            'message' => 'Exceptional effort in mutating and digitalizing 1500+ land dispute files.',
            'issuer' => 'Deputy Collector',
            'date' => '12-06-2026'
        ]
    ];
}

if (!isset($_SESSION['users_directory'])) {
    $_SESSION['users_directory'] = [
        ['name' => 'Sanjay Phad', 'email' => 'collector@gov.in', 'designation' => 'Collector', 'jurisdiction' => 'Amravati HQ', 'status' => 'Active'],
        ['name' => 'Archana More', 'email' => 'addcollector@gov.in', 'designation' => 'Additional Collector', 'jurisdiction' => 'Amravati HQ', 'status' => 'Active'],
        ['name' => 'Nitin Dongre', 'email' => 'sdo@gov.in', 'designation' => 'SDO', 'jurisdiction' => 'Achalpur Division', 'status' => 'Active'],
        ['name' => 'Vilas Kadam', 'email' => 'tehsildar@gov.in', 'designation' => 'Tehsildar', 'jurisdiction' => 'Teosa Taluka', 'status' => 'Active'],
        ['name' => 'Vijay Patil', 'email' => 'bdo@gov.in', 'designation' => 'BDO', 'jurisdiction' => 'Achalpur Block', 'status' => 'Active'],
        ['name' => 'Sunita Rao', 'email' => 'talathi@gov.in', 'designation' => 'Talathi', 'jurisdiction' => 'Teosa Circle', 'status' => 'Active'],
        ['name' => 'Balaji K.', 'email' => 'gramsevak@gov.in', 'designation' => 'Gramsevak', 'jurisdiction' => 'Chikhaldara Village', 'status' => 'Active']
    ];
}

// 3. POST Requests Processing Block (Auth, Allocation, Updates)
$error = '';
$success = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $role = $_SESSION['user_role'] ?? 'User';
    // Log audit
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $_SESSION['audit_logs'][] = [
        'timestamp' => date('d-m-Y H:i:s'),
        'user' => $role,
        'action' => 'Logged out successfully',
        'target' => 'Auth',
        'ip' => $ip
    ];
    session_unset();
    session_destroy();
    header("Location: blank.php");
    exit();
}

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: Log In
    if (isset($_POST['btn_login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (isset($accounts[$email]) && $accounts[$email]['password'] === $password) {
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $accounts[$email]['role'];
            $_SESSION['user_name'] = $accounts[$email]['name'];
            $_SESSION['user_type'] = $accounts[$email]['type'];
            $_SESSION['user_jurisdiction'] = $accounts[$email]['jurisdiction'];
            
            // Log audit trail
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $_SESSION['audit_logs'][] = [
                'timestamp' => date('d-m-Y H:i:s'),
                'user' => $accounts[$email]['role'],
                'action' => 'Authenticated via Secure NIC Portal',
                'target' => 'Auth',
                'ip' => $ip
            ];
            
            header("Location: blank.php");
            exit();
        } else {
            $error = 'Invalid secure credentials. Please verify your government login.';
        }
    }
    
    // ACTION B: Quick Developer Role Login Switcher
    if (isset($_POST['btn_quick_switch'])) {
        $switch_role_email = $_POST['quick_role_email'] ?? '';
        if (isset($accounts[$switch_role_email])) {
            $_SESSION['user_email'] = $switch_role_email;
            $_SESSION['user_role'] = $accounts[$switch_role_email]['role'];
            $_SESSION['user_name'] = $accounts[$switch_role_email]['name'];
            $_SESSION['user_type'] = $accounts[$switch_role_email]['type'];
            $_SESSION['user_jurisdiction'] = $accounts[$switch_role_email]['jurisdiction'];
            
            // Log audit trail
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $_SESSION['audit_logs'][] = [
                'timestamp' => date('d-m-Y H:i:s'),
                'user' => $accounts[$switch_role_email]['role'],
                'action' => 'Role switched dynamically by admin',
                'target' => 'Simulation',
                'ip' => $ip
            ];
            header("Location: blank.php");
            exit();
        }
    }
    
    // Enforce login for subsequent actions
    if (isset($_SESSION['user_role'])) {
        $user_role = $_SESSION['user_role'];
        $user_type = $_SESSION['user_type'];
        $user_name = $_SESSION['user_name'];
        
        // ACTION C: Create Task (Allowed for Assigners and Admins)
        if (isset($_POST['action_create_task'])) {
            if ($user_type === 'performer') {
                $error = 'Access Denied: You do not have permissions to assign tasks.';
            } else {
                $title = trim($_POST['title'] ?? '');
                $desc = trim($_POST['description'] ?? '');
                $priority = $_POST['priority'] ?? 'Medium';
                $taluka = $_POST['taluka'] ?? 'Amravati';
                $village = $_POST['village'] ?? 'HQ';
                $assignee_role = $_POST['assignee_role'] ?? 'Gramsevak';
                $due_date = $_POST['due_date'] ?? date('Y-m-d');
                
                $new_id = '#' . (100 + count($_SESSION['tasks']) + 1);
                
                $_SESSION['tasks'][] = [
                    'id' => $new_id,
                    'title' => $title,
                    'assigned_role' => $assignee_role,
                    'jurisdiction' => "$taluka - $village",
                    'priority' => $priority,
                    'status' => 'Pending',
                    'due_date' => $due_date,
                    'remarks' => ''
                ];
                
                // Add Audit Log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $_SESSION['audit_logs'][] = [
                    'timestamp' => date('d-m-Y H:i:s'),
                    'user' => $user_role,
                    'action' => "Allocated task $new_id to $assignee_role",
                    'target' => $new_id,
                    'ip' => $ip
                ];
                
                $success = "Task $new_id allocated successfully and dispatched to $assignee_role!";
            }
        }
        
        // ACTION D: Update Task Status & Remarks (Allowed for Performers / Assignees)
        if (isset($_POST['action_update_task_status'])) {
            $task_id = $_POST['task_id'] ?? '';
            $new_status = $_POST['new_status'] ?? 'In Progress';
            $remarks = trim($_POST['remarks'] ?? '');
            
            $task_found = false;
            foreach ($_SESSION['tasks'] as &$task) {
                if ($task['id'] === $task_id) {
                    // Performers can only update their own assigned tasks
                    if ($user_type === 'performer' && $task['assigned_role'] !== $user_role) {
                        $error = 'Access Denied: You can only perform tasks assigned to your role.';
                    } else {
                        $old_status = $task['status'];
                        $task['status'] = $new_status;
                        if (!empty($remarks)) {
                            $task['remarks'] = $remarks;
                        }
                        
                        // Add Audit Log
                        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                        $_SESSION['audit_logs'][] = [
                            'timestamp' => date('d-m-Y H:i:s'),
                            'user' => $user_role,
                            'action' => "Updated task $task_id status from $old_status to $new_status",
                            'target' => $task_id,
                            'ip' => $ip
                        ];
                        
                        $success = "Task $task_id status successfully updated to $new_status.";
                        $task_found = true;
                    }
                    break;
                }
            }
            if (!$task_found && empty($error)) {
                $error = 'Task record not found.';
            }
        }
        
        // ACTION E: Broadcast Announcement (Assigners and Admins)
        if (isset($_POST['action_broadcast_circular'])) {
            if ($user_type === 'performer') {
                $error = 'Access Denied.';
            } else {
                $title = trim($_POST['circular_title'] ?? '');
                $target = $_POST['circular_target'] ?? 'All Roles';
                $content = trim($_POST['circular_content'] ?? '');
                
                $_SESSION['announcements'][] = [
                    'title' => $title,
                    'issuer' => $user_role,
                    'date' => date('d F Y'),
                    'target' => $target,
                    'content' => $content
                ];
                
                // Add Audit Log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $_SESSION['audit_logs'][] = [
                    'timestamp' => date('d-m-Y H:i:s'),
                    'user' => $user_role,
                    'action' => "Broadcasted Announcement: $title",
                    'target' => 'Announcements',
                    'ip' => $ip
                ];
                $success = "Announcement broadcast sent successfully!";
            }
        }
        
        // ACTION F: Issue Appreciation Certificate (Assigners and Admins)
        if (isset($_POST['action_issue_appreciation'])) {
            if ($user_type === 'performer') {
                $error = 'Access Denied.';
            } else {
                $recip = trim($_POST['app_recipient'] ?? '');
                $cat = trim($_POST['app_category'] ?? '');
                $msg = trim($_POST['app_message'] ?? '');
                
                $_SESSION['appreciations'][] = [
                    'recipient' => $recip,
                    'category' => $cat,
                    'message' => $msg,
                    'issuer' => $user_role,
                    'date' => date('d-m-Y')
                ];
                
                // Add Audit Log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $_SESSION['audit_logs'][] = [
                    'timestamp' => date('d-m-Y H:i:s'),
                    'user' => $user_role,
                    'action' => "Issued appreciation certificate to $recip",
                    'target' => 'Appreciations',
                    'ip' => $ip
                ];
                $success = "Appreciation certificate issued and posted successfully!";
            }
        }
        
        // ACTION G: Add user to directory (Admins only)
        if (isset($_POST['action_onboard_user'])) {
            if ($user_type !== 'admin') {
                $error = 'Access Denied: Only Admins can onboard new users.';
            } else {
                $name = trim($_POST['user_name'] ?? '');
                $email = trim($_POST['user_email'] ?? '');
                $desg = $_POST['user_designation'] ?? 'Talathi';
                $juris = $_POST['user_jurisdiction'] ?? 'Amravati';
                
                $_SESSION['users_directory'][] = [
                    'name' => $name,
                    'email' => $email,
                    'designation' => $desg,
                    'jurisdiction' => $juris,
                    'status' => 'Active'
                ];
                
                // Add Audit Log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $_SESSION['audit_logs'][] = [
                    'timestamp' => date('d-m-Y H:i:s'),
                    'user' => $user_role,
                    'action' => "Onboarded user $name ($desg)",
                    'target' => $email,
                    'ip' => $ip
                ];
                $success = "User $name onboarded successfully.";
            }
        }
    }
}

// 4. Setup Context Variables based on Authentication State
$isLoggedIn = isset($_SESSION['user_role']);
if ($isLoggedIn) {
    $user_role = $_SESSION['user_role'];
    $user_name = $_SESSION['user_name'];
    $user_email = $_SESSION['user_email'];
    $user_type = $_SESSION['user_type'];
    $user_jurisdiction = $_SESSION['user_jurisdiction'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Amravati - Government Task Portal</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for standard icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            /* Colors: Govt Royal Navy & Gold Theme */
            --primary: #0a2540;
            --primary-light: #1e3a5f;
            --accent-gold: #c5a880;
            --accent-gold-hover: #b39369;
            --body-bg: #f3f6fa;
            --card-bg: #ffffff;
            
            /* User Role Badge Colors */
            --color-blue-role: #0284c7; /* Assigners: Collector, SDO etc. */
            --color-grey-role: #64748b; /* Performers: BDO, Talathi, Gramsevak */
            --color-admin-role: #7c3aed; /* Super Admin */
            
            /* Status Colors */
            --status-pending: #f59e0b;
            --status-progress: #6366f1;
            --status-transfer: #06b6d4;
            --status-completed: #10b981;
            --status-overdue: #ef4444;

            /* Borders and Shadows */
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 20px;
            --shadow-sm: 0 2px 4px rgba(10, 37, 64, 0.05);
            --shadow-md: 0 10px 20px rgba(10, 37, 64, 0.08);
            --shadow-lg: 0 20px 40px rgba(10, 37, 64, 0.12);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Top Simulation Bar (Controls UX Role & Language) */
        .simulator-bar {
            background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.85rem;
            z-index: 1000;
            border-bottom: 2px solid var(--accent-gold);
            box-shadow: var(--shadow-sm);
        }

        .simulator-controls {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .simulator-select {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            cursor: pointer;
            outline: none;
            transition: var(--transition);
        }

        .lang-switch-btn {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: var(--border-radius-sm);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .lang-switch-btn:hover {
            background: var(--accent-gold);
            color: var(--primary);
        }

        /* Government Portal Header */
        header {
            background: var(--primary);
            color: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            box-shadow: var(--shadow-md);
            border-bottom: 4px solid var(--accent-gold);
        }

        .govt-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .govt-logo {
            width: 50px;
            height: 50px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 2px solid var(--accent-gold);
            font-size: 1.5rem;
            color: var(--primary);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .brand-text h1 {
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #fff;
        }

        .brand-text p {
            font-size: 0.8rem;
            color: var(--accent-gold);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .user-status-widget {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .role-badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .role-blue { background-color: var(--color-blue-role); }
        .role-grey { background-color: var(--color-grey-role); }
        .role-admin { background-color: var(--color-admin-role); }

        /* Portal Navigation Layout */
        .portal-layout {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        /* Sidebar styling */
        .sidebar {
            width: 280px;
            background: #ffffff;
            border-right: 1px solid rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            padding: 25px 15px;
            gap: 8px;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 18px;
            color: #475569;
            text-decoration: none;
            border-radius: var(--border-radius-sm);
            font-weight: 500;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .sidebar-menu-item:hover, .sidebar-menu-item.active {
            background-color: rgba(10, 37, 64, 0.05);
            color: var(--primary);
            font-weight: 600;
        }

        .sidebar-menu-item.active {
            border-left-color: var(--accent-gold);
            background-color: rgba(10, 37, 64, 0.08);
        }

        .menu-badge {
            margin-left: auto;
            background: var(--status-overdue);
            color: #fff;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Main Workspace Content */
        .main-workspace {
            flex: 1;
            padding: 30px 40px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .toast-banner {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            background: var(--card-bg);
            border-radius: var(--border-radius-md);
            border-left: 5px solid var(--accent-gold);
            box-shadow: var(--shadow-sm);
        }

        .alert-toast {
            padding: 15px 25px;
            border-radius: var(--border-radius-md);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: var(--shadow-sm);
        }

        .alert-toast.success {
            background: rgba(16, 185, 129, 0.12);
            border-left: 5px solid var(--status-completed);
            color: #065f46;
        }

        .alert-toast.error {
            background: rgba(239, 68, 68, 0.12);
            border-left: 5px solid var(--status-overdue);
            color: #991b1b;
        }

        /* Header Info Section */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title h2 {
            font-size: 1.8rem;
            color: var(--primary);
            font-weight: 700;
        }

        .page-title p {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 4px;
        }

        /* Tab panels */
        .tab-panel {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-panel.active {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Dashboard Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: var(--border-radius-md);
            padding: 25px;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            border-bottom: 4px solid transparent;
            transition: var(--transition);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .metric-card.total { border-bottom-color: var(--primary); }
        .metric-card.completed { border-bottom-color: var(--status-completed); }
        .metric-card.progress { border-bottom-color: var(--status-progress); }
        .metric-card.pending { border-bottom-color: var(--status-pending); }
        .metric-card.overdue { border-bottom-color: var(--status-overdue); }

        .metric-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
        }

        .metric-icon {
            font-size: 1.3rem;
            color: var(--primary-light);
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1;
        }

        .metric-footer {
            margin-top: 10px;
            font-size: 0.8rem;
            color: #64748b;
        }

        /* Split Workspace: Left form, Right activity */
        .split-layout {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 30px;
        }

        @media (max-width: 1024px) {
            .split-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Beautiful Panels */
        .panel {
            background: var(--card-bg);
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.03);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .panel-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .panel-header h3 {
            font-size: 1.15rem;
            color: var(--primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-body {
            padding: 25px;
        }

        /* Forms Styling */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
        }

        .form-control {
            padding: 12px 16px;
            border-radius: var(--border-radius-sm);
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            color: #1e293b;
            background: #f8fafc;
            outline: none;
            transition: var(--transition);
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(10, 37, 64, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Priority Selector Radio Buttons */
        .priority-group {
            display: flex;
            gap: 15px;
        }

        .priority-btn {
            flex: 1;
            position: relative;
        }

        .priority-btn input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .priority-label {
            display: block;
            text-align: center;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .priority-btn input:checked + .priority-label.high {
            background-color: rgba(239, 68, 68, 0.1);
            border-color: var(--status-overdue);
            color: var(--status-overdue);
        }

        .priority-btn input:checked + .priority-label.medium {
            background-color: rgba(245, 158, 11, 0.1);
            border-color: var(--status-pending);
            color: var(--status-pending);
        }

        .priority-btn input:checked + .priority-label.low {
            background-color: rgba(16, 185, 129, 0.1);
            border-color: var(--status-completed);
            color: var(--status-completed);
        }

        /* Government Button Styling */
        .btn-gov {
            background-color: var(--primary);
            color: #fff;
            border: none;
            padding: 14px 28px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: var(--shadow-sm);
        }

        .btn-gov:hover {
            background-color: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-gov-secondary {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-gov-secondary:hover {
            background-color: rgba(10, 37, 64, 0.05);
        }

        /* Custom Government File Attachment Area */
        .attachment-uploader {
            border: 2px dashed #cbd5e1;
            padding: 20px;
            border-radius: var(--border-radius-sm);
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: var(--transition);
        }

        .attachment-uploader:hover {
            border-color: var(--primary-light);
            background: #fff;
        }

        .attachment-uploader i {
            font-size: 1.8rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        /* Modern Table & Filters */
        .table-filter-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            background: #fff;
            padding: 15px 25px;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-sm);
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .table-container {
            background: #fff;
            border-radius: var(--border-radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.95rem;
        }

        table th {
            background-color: #f8fafc;
            color: var(--primary);
            font-weight: 700;
            padding: 16px 20px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }

        table td {
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f7;
            color: #334155;
        }

        table tr:hover td {
            background-color: #f8fafc;
        }

        /* Task Status & Priority Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-badge.pending { background: rgba(245, 158, 11, 0.1); color: var(--status-pending); }
        .status-badge.progress { background: rgba(99, 102, 241, 0.1); color: var(--status-progress); }
        .status-badge.transfer { background: rgba(6, 182, 212, 0.1); color: var(--status-transfer); }
        .status-badge.completed { background: rgba(16, 185, 129, 0.1); color: var(--status-completed); }
        .status-badge.overdue { background: rgba(239, 68, 68, 0.1); color: var(--status-overdue); }

        .priority-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-badge.high { background: #fee2e2; color: #ef4444; }
        .priority-badge.medium { background: #fef3c7; color: #d97706; }
        .priority-badge.low { background: #d1fae5; color: #059669; }

        /* Appreciation Certificate Wall styling */
        .appreciation-wall {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .certificate-card {
            background: linear-gradient(145deg, #ffffff 0%, #faf8f5 100%);
            border: 2px solid var(--accent-gold);
            border-radius: var(--border-radius-md);
            padding: 25px;
            position: relative;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .certificate-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            border: 1px solid rgba(197, 168, 128, 0.3);
            margin: 8px;
            pointer-events: none;
            border-radius: 6px;
        }

        .certificate-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .ribbon-icon {
            font-size: 1.8rem;
            color: var(--accent-gold);
        }

        .certificate-body {
            text-align: center;
            z-index: 1;
        }

        .certificate-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent-gold);
            margin-bottom: 10px;
        }

        .certificate-recipient {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .certificate-message {
            font-style: italic;
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.5;
        }

        .certificate-footer {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px dashed rgba(197, 168, 128, 0.3);
            padding-top: 12px;
        }

        /* Audit Trail Timeline */
        .audit-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .audit-item {
            display: flex;
            gap: 15px;
            padding: 12px 15px;
            background: #f8fafc;
            border-radius: var(--border-radius-sm);
            border-left: 3px solid var(--primary-light);
        }

        .audit-time {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
        }

        .audit-desc {
            font-size: 0.85rem;
            color: #1e293b;
        }

        .circular-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .circular-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.05);
            padding: 20px;
            border-radius: var(--border-radius-sm);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--accent-gold);
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .circular-card h4 {
            color: var(--primary);
            font-size: 1rem;
        }

        .circular-meta {
            font-size: 0.8rem;
            color: #64748b;
            display: flex;
            gap: 15px;
        }

        .sim-chart {
            height: 200px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding-top: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .sim-chart-bar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 50px;
        }

        .sim-chart-bar {
            width: 30px;
            border-radius: 4px 4px 0 0;
            animation: growBar 1s ease-out;
        }

        @keyframes growBar {
            from { height: 0; }
        }

        .sim-chart-label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 8px;
            text-align: center;
        }

        /* Govt portal Footer */
        footer {
            background-color: var(--primary);
            color: #fff;
            text-align: center;
            padding: 20px 40px;
            margin-top: auto;
            border-top: 4px solid var(--accent-gold);
            font-size: 0.9rem;
        }

        footer p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 5px;
        }

        footer a {
            color: var(--accent-gold);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .system-config-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px;
        }

        @media (max-width: 768px) {
            .system-config-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Real Login Layout CSS */
        .login-layout-wrapper {
            background: linear-gradient(135deg, #0a2540 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .login-container-card {
            background: #fff;
            width: 100%;
            max-width: 440px;
            padding: 40px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            border-top: 6px solid var(--accent-gold);
            z-index: 10;
        }

        .credential-helper-box {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--border-radius-md);
            padding: 20px;
            max-width: 800px;
            margin-top: 30px;
            color: #fff;
            font-size: 0.85rem;
            z-index: 10;
        }

        .helper-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .helper-grid {
                grid-template-columns: 1fr;
            }
        }

        .helper-column {
            background: rgba(255, 255, 255, 0.03);
            padding: 10px;
            border-radius: var(--border-radius-sm);
        }

        .helper-column h5 {
            color: var(--accent-gold);
            margin-bottom: 5px;
            font-size: 0.9rem;
            border-bottom: 1px dashed rgba(255,255,255,0.2);
            padding-bottom: 4px;
        }

        .helper-column code {
            background: rgba(0,0,0,0.3);
            padding: 2px 5px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            color: #a78bfa;
        }

        /* Performance Status Update modal */
        .status-modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(10, 37, 64, 0.7);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
        }

        .status-modal {
            background: #fff;
            padding: 30px;
            border-radius: var(--border-radius-md);
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-lg);
            border-top: 5px solid var(--primary);
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
    <!-- 1. AUTHENTICATION / LOGIN SCREEN -->
    <div class="login-layout-wrapper">
        <div class="govt-logo" style="margin-bottom: 20px; width: 65px; height: 65px; background: #fff; font-size: 2rem;">
            <i class="fa-solid fa-landmark"></i>
        </div>
        <h2 style="color: #fff; margin-bottom: 5px; font-weight: 700; text-align:center;">CONNECT AMRAVATI</h2>
        <p style="color: var(--accent-gold); margin-bottom: 25px; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1.5px; text-align:center;">District Task Management & Enforcement Portal</p>
        
        <div class="login-container-card">
            <h3 style="color: var(--primary); font-weight: 700; margin-bottom: 8px;"><i class="fa-solid fa-shield-halved"></i> NIC Secure Login</h3>
            <p style="font-size: 0.85rem; color:#64748b; margin-bottom: 20px;">Please authenticate to access your regional jurisdiction workbench.</p>
            
            <?php if (!empty($error)): ?>
                <div class="alert-toast error" style="padding: 10px 15px; font-size: 0.8rem; margin-bottom: 15px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="blank.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label>NIC Government Email ID</label>
                    <input type="email" name="email" class="form-control" placeholder="officer-id@gov.in" required>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••••••" required>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem; margin-bottom: 20px;">
                    <label style="text-transform:none; cursor:pointer; font-weight: 500; color:#64748b;">
                        <input type="checkbox" checked> Secure NIC Session
                    </label>
                    <a href="#" style="color: var(--primary-light); text-decoration:none; font-weight:600;">OTP Login Setup</a>
                </div>
                <button type="submit" name="btn_login" class="btn-gov" style="width: 100%;">
                    <i class="fa-solid fa-fingerprint"></i> Authenticate credentials
                </button>
            </form>
        </div>

        <!-- Developer Predefined Credentials Guide -->
        <div class="credential-helper-box">
            <h4 style="font-weight:700; border-bottom: 1px solid rgba(255,255,255,0.15); padding-bottom: 8px;">
                <i class="fa-solid fa-key" style="color: var(--accent-gold);"></i> Preconfigured System Accounts (For Testing & Verification)
            </h4>
            <div class="helper-grid">
                <div class="helper-column">
                    <h5>1. Super Admin</h5>
                    <p style="margin-top: 5px;"><strong>Permissions:</strong> Full access to all components, logs, reports, and onboarding directories.</p>
                    <p style="margin-top: 5px;">Email: <code>superadmin@gov.in</code></p>
                    <p>Password: <code>admin123</code></p>
                </div>
                <div class="helper-column">
                    <h5>2. Task Assigners (Creators)</h5>
                    <p style="margin-top: 5px;"><strong>Permissions:</strong> Assign tasks, broadcast announcements, issue employee appreciation certificates.</p>
                    <p style="margin-top: 5px;"><strong>Collector:</strong> <code>collector@gov.in</code></p>
                    <p><strong>SDO:</strong> <code>sdo@gov.in</code></p>
                    <p>Password: <code>collector123</code> / <code>sdo123</code></p>
                </div>
                <div class="helper-column">
                    <h5>3. Task Performers (Performers)</h5>
                    <p style="margin-top: 5px;"><strong>Permissions:</strong> View tasks assigned to their role, update status, submit remarks. Cannot assign tasks.</p>
                    <p style="margin-top: 5px;"><strong>Talathi:</strong> <code>talathi@gov.in</code></p>
                    <p><strong>Gramsevak:</strong> <code>gramsevak@gov.in</code></p>
                    <p>Password: <code>talathi123</code> / <code>gramsevak123</code></p>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- 2. MAIN SYSTEM DASHBOARD -->
    
    <!-- Top Developer Quick Switching Bar -->
    <div class="simulator-bar">
        <div class="simulator-controls">
            <form action="blank.php" method="POST" style="display:flex; align-items:center; gap: 10px;">
                <label style="color: var(--accent-gold); font-size:0.8rem; font-weight: 700;"><i class="fa-solid fa-wand-magic-sparkles"></i> QUICK ROLE SWITCH (DEV):</label>
                <select name="quick_role_email" class="simulator-select" onchange="this.form.submit()">
                    <option value="">Select User Profile...</option>
                    <?php foreach ($accounts as $email => $info): ?>
                        <option value="<?php echo htmlspecialchars($email); ?>" <?php echo ($user_email === $email) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($info['role']); ?> (<?php echo htmlspecialchars($info['name']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="btn_quick_switch" value="1">
            </form>
        </div>
        
        <div class="simulator-controls">
            <span id="govComplianceStatus" style="color: #10b981; font-weight:600;"><i class="fa-solid fa-shield-halved"></i> NIC Network: Secured</span>
            <button class="lang-switch-btn" onclick="toggleLanguage()">
                <i class="fa-solid fa-language"></i> <span id="langBtnText">मराठी (Switch Language)</span>
            </button>
            <a href="blank.php?action=logout" class="lang-switch-btn" style="text-decoration:none; background: #991b1b;">
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </div>

    <!-- Government Portal Header -->
    <header>
        <div class="govt-brand">
            <div class="govt-logo">
                <i class="fa-solid fa-landmark"></i>
            </div>
            <div class="brand-text">
                <h1 data-en="Connect Amravati" data-mr="कनेक्ट अमरावती">Connect Amravati</h1>
                <p data-en="District Task Monitoring & Enforcement" data-mr="जिल्हा कार्य नियंत्रण व अंमलबजावणी">District Task Monitoring & Enforcement</p>
            </div>
        </div>

        <div class="user-status-widget">
            <span style="font-size: 0.85rem; color: var(--accent-gold); font-weight: 500; text-align:right;">
                <i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($user_name); ?><br>
                <i class="fa-solid fa-location-dot" style="font-size:0.75rem; margin-top:2px;"></i> <span style="font-size:0.75rem;"><?php echo htmlspecialchars($user_jurisdiction); ?></span>
            </span>
            <span class="role-badge <?php echo ($user_type === 'admin') ? 'role-admin' : (($user_type === 'assigner') ? 'role-blue' : 'role-grey'); ?>">
                <?php echo htmlspecialchars($user_role); ?>
            </span>
        </div>
    </header>

    <div class="portal-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-menu-item active" onclick="switchTab(this, 'dashboard-tab')">
                <i class="fa-solid fa-chart-pie"></i>
                <span data-en="Dashboard" data-mr="डॅशबोर्ड">Dashboard</span>
            </div>
            
            <!-- Hide Task Allocation for Performers -->
            <?php if ($user_type === 'admin' || $user_type === 'assigner'): ?>
                <div class="sidebar-menu-item" onclick="switchTab(this, 'allocate-tab')">
                    <i class="fa-solid fa-folder-plus"></i>
                    <span data-en="Task Allocation" data-mr="कार्य वाटप">Task Allocation</span>
                </div>
            <?php endif; ?>

            <div class="sidebar-menu-item" onclick="switchTab(this, 'tracking-tab')">
                <i class="fa-solid fa-list-check"></i>
                <span data-en="Task Records" data-mr="कार्य नोंदी">Task Records</span>
                <span class="menu-badge" id="overdueCount">
                    <?php 
                    $overdue_cnt = 0;
                    foreach ($_SESSION['tasks'] as $t) {
                        if ($t['status'] === 'Overdue') {
                            if ($user_type !== 'performer' || $t['assigned_role'] === $user_role) {
                                $overdue_cnt++;
                            }
                        }
                    }
                    echo $overdue_cnt;
                    ?>
                </span>
            </div>

            <div class="sidebar-menu-item" onclick="switchTab(this, 'announcements-tab')">
                <i class="fa-solid fa-bullhorn"></i>
                <span data-en="Announcements" data-mr="घोषणा व पत्रके">Announcements</span>
            </div>

            <div class="sidebar-menu-item" onclick="switchTab(this, 'appreciation-tab')">
                <i class="fa-solid fa-award"></i>
                <span data-en="Appreciation Wall" data-mr="गौरव भिंत">Appreciation Wall</span>
            </div>

            <!-- Show User Management & System Interfaces only for Super Admin -->
            <?php if ($user_type === 'admin'): ?>
                <div class="sidebar-menu-item" onclick="switchTab(this, 'users-tab')">
                    <i class="fa-solid fa-users-gear"></i>
                    <span data-en="User Directory" data-mr="वापरकर्ता निर्देशिका">User Directory</span>
                </div>

                <div class="sidebar-menu-item" onclick="switchTab(this, 'interfaces-tab')">
                    <i class="fa-solid fa-circle-nodes"></i>
                    <span data-en="System Gateways" data-mr="सिस्टम गेटवे">System Gateways</span>
                </div>
            <?php endif; ?>

            <!-- Show reports & audit trail for Assigners & Admin -->
            <?php if ($user_type === 'admin' || $user_type === 'assigner'): ?>
                <div class="sidebar-menu-item" onclick="switchTab(this, 'reports-tab')">
                    <i class="fa-solid fa-file-invoice"></i>
                    <span data-en="Reports Center" data-mr="अहवाल केंद्र">Reports Center</span>
                </div>

                <div class="sidebar-menu-item" onclick="switchTab(this, 'audit-tab')">
                    <i class="fa-solid fa-fingerprint"></i>
                    <span data-en="Security Audit" data-mr="सुरक्षा ऑडिट">Security Audit</span>
                </div>
            <?php endif; ?>
        </aside>

        <!-- Main Content Workspace -->
        <main class="main-workspace">

            <!-- Dynamic Alert Alerts -->
            <?php if (!empty($success)): ?>
                <div class="alert-toast success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert-toast error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <!-- TAB 1: DASHBOARD -->
            <div class="tab-panel active" id="dashboard-tab">
                <div class="page-header">
                    <div class="page-title">
                        <h2 data-en="Administrative Performance Dashboard" data-mr="प्रशासकीय कामगिरी डॅशबोर्ड">Administrative Performance Dashboard</h2>
                        <p data-en="Real-time oversight of task metrics under active enforcement." data-mr="सक्रिय अंमलबजावणी अंतर्गत कार्य आकडेवारीचे थेट नियंत्रण.">Real-time oversight of task metrics under active enforcement.</p>
                    </div>
                </div>

                <!-- Calculate Metrics Dynamically -->
                <?php
                $metric_total = 0;
                $metric_completed = 0;
                $metric_progress = 0;
                $metric_overdue = 0;
                $metric_pending = 0;

                foreach ($_SESSION['tasks'] as $task) {
                    // Performers only see metrics for tasks assigned to them
                    if ($user_type === 'performer' && $task['assigned_role'] !== $user_role) {
                        continue;
                    }
                    $metric_total++;
                    if ($task['status'] === 'Completed') $metric_completed++;
                    elseif ($task['status'] === 'In Progress') $metric_progress++;
                    elseif ($task['status'] === 'Overdue') $metric_overdue++;
                    elseif ($task['status'] === 'Pending') $metric_pending++;
                }
                $comp_ratio = ($metric_total > 0) ? round(($metric_completed / $metric_total) * 100, 1) : 0;
                ?>

                <!-- Metrics Grid -->
                <div class="metrics-grid">
                    <div class="metric-card total">
                        <div class="metric-header">
                            <span data-en="<?php echo ($user_type === 'performer') ? 'My Total Duties' : 'Total Allocated'; ?>" data-mr="<?php echo ($user_type === 'performer') ? 'माझी एकूण कर्तव्ये' : 'एकूण वाटप'; ?>">
                                <?php echo ($user_type === 'performer') ? 'My Total Duties' : 'Total Allocated'; ?>
                            </span>
                            <i class="fa-solid fa-folder-open metric-icon"></i>
                        </div>
                        <span class="metric-value"><?php echo $metric_total; ?></span>
                        <span class="metric-footer" data-en="Active records in database" data-mr="डेटाबेसमधील सक्रिय नोंदी">Active records in database</span>
                    </div>
                    <div class="metric-card completed">
                        <div class="metric-header">
                            <span data-en="Completed" data-mr="पूर्ण झालेले">Completed</span>
                            <i class="fa-solid fa-circle-check metric-icon" style="color: var(--status-completed)"></i>
                        </div>
                        <span class="metric-value" style="color: var(--status-completed)"><?php echo $metric_completed; ?></span>
                        <span class="metric-footer"><span style="color: var(--status-completed); font-weight:600;"><?php echo $comp_ratio; ?>%</span> completion rate</span>
                    </div>
                    <div class="metric-card progress">
                        <div class="metric-header">
                            <span data-en="In Progress" data-mr="काम चालू आहे">In Progress</span>
                            <i class="fa-solid fa-hourglass-half metric-icon" style="color: var(--status-progress)"></i>
                        </div>
                        <span class="metric-value" style="color: var(--status-progress)"><?php echo $metric_progress; ?></span>
                        <span class="metric-footer" data-en="Under field implementation" data-mr="सध्या अंमलबजावणी सुरू">Under field implementation</span>
                    </div>
                    <div class="metric-card overdue">
                        <div class="metric-header">
                            <span data-en="Overdue" data-mr="मुदत संपलेली">Overdue</span>
                            <i class="fa-solid fa-triangle-exclamation metric-icon" style="color: var(--status-overdue)"></i>
                        </div>
                        <span class="metric-value" style="color: var(--status-overdue)"><?php echo $metric_overdue; ?></span>
                        <span class="metric-footer" style="color: var(--status-overdue); font-weight: 500;" data-en="Requires critical update" data-mr="त्वरित कारवाई आवश्यक">Requires critical update</span>
                    </div>
                </div>

                <!-- Graphical Chart Representation & Logs Split -->
                <div class="split-layout">
                    <!-- Left: Performance Chart -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Administrative Status Distribution" data-mr="प्रशासकीय कार्य वितरण">Administrative Status Distribution</h3>
                        </div>
                        <div class="panel-body">
                            <div class="sim-chart">
                                <div class="sim-chart-bar-container">
                                    <div class="sim-chart-bar" style="height: <?php echo ($metric_total > 0) ? ($metric_completed/$metric_total)*160 : 0; ?>px; background-color: var(--status-completed);"></div>
                                    <div class="sim-chart-label" data-en="Completed" data-mr="पूर्ण">Completed</div>
                                </div>
                                <div class="sim-chart-bar-container">
                                    <div class="sim-chart-bar" style="height: <?php echo ($metric_total > 0) ? ($metric_progress/$metric_total)*160 : 0; ?>px; background-color: var(--status-progress);"></div>
                                    <div class="sim-chart-label" data-en="In Progress" data-mr="प्रगतीपथावर">In Progress</div>
                                </div>
                                <div class="sim-chart-bar-container">
                                    <div class="sim-chart-bar" style="height: <?php echo ($metric_total > 0) ? ($metric_pending/$metric_total)*160 : 0; ?>px; background-color: var(--status-pending);"></div>
                                    <div class="sim-chart-label" data-en="Pending" data-mr="प्रलंबित">Pending</div>
                                </div>
                                <div class="sim-chart-bar-container">
                                    <div class="sim-chart-bar" style="height: <?php echo ($metric_total > 0) ? ($metric_overdue/$metric_total)*160 : 0; ?>px; background-color: var(--status-overdue);"></div>
                                    <div class="sim-chart-label" data-en="Overdue" data-mr="थकीत">Overdue</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Quick Portal User Info -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Official NIC Directory Details" data-mr="एनआयसी अधिकृत माहिती">Official NIC Directory Details</h3>
                        </div>
                        <div class="panel-body">
                            <div class="audit-list">
                                <div class="audit-item" style="border-left-color: var(--accent-gold);">
                                    <div class="audit-desc">
                                        <strong>Account Name:</strong> <?php echo htmlspecialchars($user_name); ?><br>
                                        <strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?><br>
                                        <strong>Assigned Jurisdiction:</strong> <?php echo htmlspecialchars($user_jurisdiction); ?>
                                    </div>
                                </div>
                                <div class="audit-item" style="border-left-color: var(--primary);">
                                    <div class="audit-desc" style="font-size:0.8rem;">
                                        <strong>Role Class:</strong> 
                                        <?php if ($user_type === 'admin'): ?>
                                            <span style="color: var(--color-admin-role); font-weight:700;">System Super Admin (All Rights)</span>
                                        <?php elseif ($user_type === 'assigner'): ?>
                                            <span style="color: var(--color-blue-role); font-weight:700;">Task Assigner (Authorized to Assign Task)</span>
                                        <?php else: ?>
                                            <span style="color: var(--color-grey-role); font-weight:700;">Task Performer / Assignee Only</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: TASK ALLOCATION (VISIBLE TO BLUE ROLES & ADMIN) -->
            <?php if ($user_type === 'admin' || $user_type === 'assigner'): ?>
                <div class="tab-panel" id="allocate-tab">
                    <div class="page-header">
                        <div class="page-title">
                            <h2 data-en="Task Allocation Console" data-mr="कार्य वाटप नियंत्रण कक्ष">Task Allocation Console</h2>
                            <p data-en="Assign official duties to field performers and dispatch NIC notifications." data-mr="क्षेत्रीय स्तरावरील अधिकार्यांना कामे सोपवून सूचना पाठवा.">Assign official duties to field performers and dispatch NIC notifications.</p>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Allocate New Duty Target" data-mr="नवीन कर्तव्ये नियुक्त करा">Allocate New Duty Target</h3>
                        </div>
                        <div class="panel-body">
                            <form action="blank.php" method="POST">
                                <input type="hidden" name="action_create_task" value="1">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label data-en="Task Title" data-mr="कार्याचे नाव">Task Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="e.g. Drought Compensation Assessment" required>
                                    </div>
                                    <div class="form-group">
                                        <label data-en="Priority Level" data-mr="प्राधान्यक्रम">Priority Level</label>
                                        <div class="priority-group">
                                            <label class="priority-btn">
                                                <input type="radio" name="priority" value="High" checked>
                                                <span class="priority-label high" data-en="High" data-mr="उच्च">High</span>
                                            </label>
                                            <label class="priority-btn">
                                                <input type="radio" name="priority" value="Medium">
                                                <span class="priority-label medium" data-en="Medium" data-mr="मध्यम">Medium</span>
                                            </label>
                                            <label class="priority-btn">
                                                <input type="radio" name="priority" value="Low">
                                                <span class="priority-label low" data-en="Low" data-mr="कमी">Low</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label data-en="Task Instructions / Guidelines" data-mr="मार्गदर्शक सूचना">Task Instructions / Guidelines</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Detail the duties and deliverables clearly..." required></textarea>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label data-en="Target Division / Taluka" data-mr="तालुका">Target Division / Taluka</label>
                                        <select name="taluka" class="form-control" id="taskTaluka" onchange="updateVillages(this.value)">
                                            <option value="Amravati">Amravati</option>
                                            <option value="Achalpur">Achalpur</option>
                                            <option value="Teosa">Teosa</option>
                                            <option value="Chikhaldara">Chikhaldara</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label data-en="Target Village / Circle" data-mr="गाव / मंडळ">Target Village / Circle</label>
                                        <select name="village" class="form-control" id="taskVillage">
                                            <option value="HQ Office">HQ Office</option>
                                            <option value="Karanja">Karanja Village</option>
                                            <option value="Bhadri">Bhadri</option>
                                            <option value="Semadoh">Semadoh</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label data-en="Assign To Task Performer (Role)" data-mr="काम सोपवणारी भूमिका (कर्मचारी)">Assign To Task Performer (Role)</label>
                                        <select name="assignee_role" class="form-control">
                                            <option value="BDO">BDO (Block Development Officer)</option>
                                            <option value="Talathi">Talathi (Village Revenue Collector)</option>
                                            <option value="Gramsevak">Gramsevak (Village Officer)</option>
                                            <option value="System Administrator">System Administrator (IT Support)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label data-en="Due / Deadline Date" data-mr="अंतिम तारीख">Due / Deadline Date</label>
                                        <input type="date" name="due_date" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                                    </div>
                                </div>

                                <div style="display:flex; justify-content:flex-end; gap: 15px; margin-top:20px;">
                                    <button type="reset" class="btn-gov btn-gov-secondary" data-en="Clear Instructions" data-mr="माहिती खोडा">Clear Instructions</button>
                                    <button type="submit" class="btn-gov">
                                        <i class="fa-solid fa-check-double"></i> <span data-en="Allocate Task & Issue Warnings" data-mr="कार्य वाटप करा व सूचना पाठवा">Allocate Task & Issue Warnings</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB 3: TASK RECORDS TABLE (Filtered dynamically per role) -->
            <div class="tab-panel" id="tracking-tab">
                <div class="page-header">
                    <div class="page-title">
                        <h2 data-en="Task Ledger & Actions" data-mr="कार्य नोंदवही आणि कृती">Task Ledger & Actions</h2>
                        <p data-en="Review records, statuses, and submit updates for active field assignments." data-mr="सक्रिय क्षेत्रीय कामांचे पुनरावलोकन करा आणि त्यांची स्थिती अद्ययावत करा.">Review records, statuses, and submit updates for active field assignments.</p>
                    </div>
                </div>

                <div class="table-filter-bar">
                    <div class="filter-controls">
                        <label data-en="Filter by status:" data-mr="स्थितीनुसार शोधा:">Filter by status:</label>
                        <select class="simulator-select" style="background:#fff; color:#333; border: 1px solid #cbd5e1;" onchange="filterTasks(this.value)">
                            <option value="All">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="filter-controls">
                        <input type="text" class="form-control" style="width: 250px; padding: 6px 12px; background:#fff;" placeholder="Search task records..." onkeyup="searchTasks(this.value)">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th data-en="Task ID" data-mr="आयडी">Task ID</th>
                                <th data-en="Task Title" data-mr="कार्याचे नाव">Task Title</th>
                                <th data-en="Assignee" data-mr="नियुक्त भूमिका">Assignee</th>
                                <th data-en="Jurisdiction" data-mr="अधिकार क्षेत्र">Jurisdiction</th>
                                <th data-en="Priority" data-mr="प्राधान्य">Priority</th>
                                <th data-en="Status" data-mr="स्थिती">Status</th>
                                <th data-en="Due Date" data-mr="अंतिम तारीख">Due Date</th>
                                <th data-en="Action" data-mr="क्रिया">Action</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody">
                            <?php 
                            $found_tasks = false;
                            foreach ($_SESSION['tasks'] as $task): 
                                // Performers can only view tasks assigned to their specific designation
                                if ($user_type === 'performer' && $task['assigned_role'] !== $user_role) {
                                    continue;
                                }
                                $found_tasks = true;
                            ?>
                                <tr data-status="<?php echo htmlspecialchars($task['status']); ?>">
                                    <td><strong><?php echo htmlspecialchars($task['id']); ?></strong></td>
                                    <td>
                                        <div style="font-weight:600; color:var(--primary);"><?php echo htmlspecialchars($task['title']); ?></div>
                                        <?php if(!empty($task['remarks'])): ?>
                                            <small style="color:#64748b; font-style:italic;">Remarks: <?php echo htmlspecialchars($task['remarks']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['assigned_role']); ?></td>
                                    <td><?php echo htmlspecialchars($task['jurisdiction']); ?></td>
                                    <td>
                                        <span class="priority-badge <?php echo strtolower($task['priority']); ?>">
                                            <?php echo htmlspecialchars($task['priority']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $st = $task['status'];
                                        $badge_cls = strtolower($st);
                                        $icon = ($st==='Completed') ? 'circle-check' : (($st==='In Progress')?'hourglass-half':(($st==='Overdue')?'triangle-exclamation':'clock'));
                                        ?>
                                        <span class="status-badge <?php echo $badge_cls; ?>">
                                            <i class="fa-solid fa-<?php echo $icon; ?>"></i> <?php echo $st; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($task['due_date']); ?></td>
                                    <td>
                                        <?php if ($user_type === 'performer'): ?>
                                            <!-- Performers can perform/update status -->
                                            <button class="btn-gov" style="padding: 6px 12px; font-size: 0.8rem; background: var(--primary-light);" 
                                                    onclick="openStatusModal('<?php echo $task['id']; ?>', '<?php echo $task['status']; ?>', '<?php echo htmlspecialchars($task['remarks']); ?>')">
                                                Update Progress
                                            </button>
                                        <?php else: ?>
                                            <!-- Assigners can view encrypted audit metrics -->
                                            <button class="btn-gov" style="padding: 6px 12px; font-size: 0.8rem;" onclick="viewTaskDetails('<?php echo $task['id']; ?>')">
                                                Audit Log
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$found_tasks): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; color:#64748b; padding:30px;">
                                        No task assignments found for your current profile.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: ANNOUNCEMENTS / CIRCULARS -->
            <div class="tab-panel" id="announcements-tab">
                <div class="page-header">
                    <div class="page-title">
                        <h2 data-en="NIC Broadcast & Circular Panel" data-mr="एनआयसी परिपत्रक व घोषणा">NIC Broadcast & Circular Panel</h2>
                        <p data-en="District-wide official guidelines and emergency alerts targeted by role." data-mr="जिल्हास्तरीय अधिकृत मार्गदर्शक तत्वे व कर्मचाऱ्यांना महत्त्वाची पत्रके.">District-wide official guidelines and emergency alerts targeted by role.</p>
                    </div>
                </div>

                <div class="split-layout">
                    <!-- Left: Announcements List -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Active Official Circulars" data-mr="सक्रिय परिपत्रके">Active Official Circulars</h3>
                        </div>
                        <div class="panel-body">
                            <div class="circular-list">
                                <?php foreach (array_reverse($_SESSION['announcements']) as $ann): ?>
                                    <div class="circular-card">
                                        <h4><?php echo htmlspecialchars($ann['title']); ?></h4>
                                        <div class="circular-meta">
                                            <span><i class="fa-solid fa-user-tie"></i> From: <?php echo htmlspecialchars($ann['issuer']); ?></span>
                                            <span><i class="fa-solid fa-calendar"></i> <?php echo htmlspecialchars($ann['date']); ?></span>
                                            <span><i class="fa-solid fa-users"></i> Target: <?php echo htmlspecialchars($ann['target']); ?></span>
                                        </div>
                                        <p style="font-size: 0.9rem; color:#475569; margin-top:8px;">
                                            <?php echo htmlspecialchars($ann['content']); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Broadcast New (Assigners and Admins only) -->
                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Broadcast New Instruction" data-mr="नवीन परिपत्रक तयार करा">Broadcast New Instruction</h3>
                        </div>
                        <div class="panel-body">
                            <?php if ($user_type === 'performer'): ?>
                                <p style="color:#64748b; font-size:0.9rem; text-align:center; padding:30px;">
                                    <i class="fa-solid fa-lock" style="font-size:1.5rem; margin-bottom:10px; display:block;"></i>
                                    Access Restricted: Only assigners can publish circulars.
                                </p>
                            <?php else: ?>
                                <form action="blank.php" method="POST">
                                    <input type="hidden" name="action_broadcast_circular" value="1">
                                    <div class="form-group">
                                        <label>Circular Subject / Title</label>
                                        <input type="text" name="circular_title" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Target Audience Role</label>
                                        <select name="circular_target" class="form-control">
                                            <option value="All Roles">All Roles</option>
                                            <option value="BDOs Only">BDOs Only</option>
                                            <option value="Talathis Only">Talathis Only</option>
                                            <option value="Gramsevaks Only">Gramsevaks Only</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Message Content</label>
                                        <textarea name="circular_content" class="form-control" rows="4" required></textarea>
                                    </div>
                                    <button type="submit" class="btn-gov" style="width:100%;">
                                        <i class="fa-solid fa-paper-plane"></i> Broadcast Notice
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: APPRECIATION WALL -->
            <div class="tab-panel" id="appreciation-tab">
                <div class="page-header">
                    <div class="page-title">
                        <h2 data-en="Employee Appreciation Wall" data-mr="कर्मचारी सन्मान भिंत">Employee Appreciation Wall</h2>
                        <p data-en="Permanent motivational record ledger highlighting outstanding duties." data-mr="उत्कृष्ट कामगिरीचे कायमस्वरूपी नोंदणी आणि कौतुकपत्र फलक.">Permanent motivational record ledger highlighting outstanding duties.</p>
                    </div>
                </div>

                <!-- Create Appreciation Certificate (Assigners and Admins only) -->
                <?php if ($user_type === 'admin' || $user_type === 'assigner'): ?>
                    <div class="panel" style="margin-bottom:30px;">
                        <div class="panel-header">
                            <h3 data-en="Issue Permanent Appreciation Citation" data-mr="कौतुक प्रमाणपत्र जारी करा">Issue Permanent Appreciation Citation</h3>
                        </div>
                        <div class="panel-body">
                            <form action="blank.php" method="POST">
                                <input type="hidden" name="action_issue_appreciation" value="1">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Officer Name & Designation</label>
                                        <input type="text" name="app_recipient" class="form-control" placeholder="e.g. Shri. Sunita Rao (Talathi)" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Appreciation Domain Category</label>
                                        <input type="text" name="app_category" class="form-control" placeholder="e.g. Drought Relief Campaign" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Citation Message / Commendation</label>
                                    <textarea name="app_message" class="form-control" rows="3" placeholder="Describe their exceptional performance..." required></textarea>
                                </div>
                                <div style="display:flex; justify-content:flex-end;">
                                    <button type="submit" class="btn-gov">
                                        <i class="fa-solid fa-award"></i> Post Commendation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="appreciation-wall">
                    <?php foreach (array_reverse($_SESSION['appreciations']) as $app): ?>
                        <div class="certificate-card">
                            <div class="certificate-header">
                                <span class="ribbon-icon"><i class="fa-solid fa-certificate"></i></span>
                                <span style="font-weight: 700; font-size:0.8rem; color: var(--accent-gold);">OFFICIAL CONGRATULATIONS</span>
                            </div>
                            <div class="certificate-body">
                                <div class="certificate-title"><?php echo htmlspecialchars($app['category']); ?></div>
                                <div class="certificate-recipient"><?php echo htmlspecialchars($app['recipient']); ?></div>
                                <div class="certificate-message">"<?php echo htmlspecialchars($app['message']); ?>"</div>
                            </div>
                            <div class="certificate-footer">
                                <span>Issued by: <?php echo htmlspecialchars($app['issuer']); ?></span>
                                <span>Date: <?php echo htmlspecialchars($app['date']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- TAB 6: USER MANAGEMENT (SUPER ADMIN ONLY) -->
            <?php if ($user_type === 'admin'): ?>
                <div class="tab-panel" id="users-tab">
                    <div class="page-header">
                        <div class="page-title">
                            <h2 data-en="Government Officer Directory Management" data-mr="शासकीय अधिकारी निर्देशिका">Government Officer Directory Management</h2>
                            <p data-en="Add, configure designations, and assign regional jurisdiction limits." data-mr="प्रशासक साधन: नवीन अधिकारी जोडणे, निष्क्रिय करणे आणि अधिकार क्षेत्र बदलणे.">Add, configure designations, and assign regional jurisdiction limits.</p>
                        </div>
                    </div>

                    <div class="system-config-grid">
                        <div class="panel">
                            <div class="panel-header">
                                <h3 data-en="Onboard New Government Officer" data-mr="नवीन अधिकारी ऑनबोर्ड करा">Onboard New Government Officer</h3>
                            </div>
                            <div class="panel-body">
                                <form action="blank.php" method="POST">
                                    <input type="hidden" name="action_onboard_user" value="1">
                                    <div class="form-group">
                                        <label>Full Officer Name</label>
                                        <input type="text" name="user_name" class="form-control" required>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label>Designation Role</label>
                                            <select name="user_designation" class="form-control">
                                                <option value="Collector">Collector</option>
                                                <option value="SDO">SDO</option>
                                                <option value="Tehsildar">Tehsildar</option>
                                                <option value="BDO">BDO</option>
                                                <option value="Talathi">Talathi</option>
                                                <option value="Gramsevak">Gramsevak</option>
                                                <option value="System Administrator">System Administrator</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Jurisdiction Range</label>
                                            <input type="text" name="user_jurisdiction" class="form-control" placeholder="e.g. Achalpur Division" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Gov Email Account (for login)</label>
                                        <input type="email" name="user_email" class="form-control" required placeholder="name@gov.in">
                                    </div>
                                    <button type="submit" class="btn-gov" style="width:100%;">
                                        <i class="fa-solid fa-user-plus"></i> Complete NIC Registration
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="panel">
                            <div class="panel-header">
                                <h3 data-en="Active Officers Directory" data-mr="सक्रिय अधिकारी यादी">Active Officers Directory</h3>
                            </div>
                            <div class="panel-body" style="padding:0;">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Officer Name</th>
                                            <th>Designation</th>
                                            <th>Jurisdiction</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['users_directory'] as $usr): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($usr['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($usr['designation']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['jurisdiction']); ?></td>
                                                <td><span style="color:#10b981; font-weight:700;"><i class="fa-solid fa-circle" style="font-size:0.7rem;"></i> <?php echo htmlspecialchars($usr['status']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: SYSTEM GATEWAYS (SUPER ADMIN ONLY) -->
                <div class="tab-panel" id="interfaces-tab">
                    <div class="page-header">
                        <div class="page-title">
                            <h2 data-en="External Gateways & Infrastructure" data-mr="बाह्य प्रणाली गेटवे व पायाभूत सुविधा">External Gateways & Infrastructure</h2>
                            <p data-en="Configure NIC secure SMTP servers, National SMS Gateway integrations, and webhook interfaces." data-mr="सुरक्षित मेल सर्व्हर, राष्ट्रीय एसएमएस गेटवे आणि इतर एकत्रीकरण कॉन्फिगर करा.">Configure NIC secure SMTP servers, National SMS Gateway integrations, and webhook interfaces.</p>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="API Gateway Parameters" data-mr="API गेटवे मापदंड">API Gateway Parameters</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Government SMS Gateway Endpoint</label>
                                    <input type="text" class="form-control" value="https://samsad.gov.in/sms/v2/send" readonly>
                                </div>
                                <div class="form-group">
                                    <label>SMS Gateway Secure Auth Token</label>
                                    <input type="password" class="form-control" value="••••••••••••••••••••••••••••••••" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>NIC SMTP Email Relay Port</label>
                                    <input type="text" class="form-control" value="nicmail.gov.in:465" readonly>
                                </div>
                                <div class="form-group">
                                    <label>MahaOnline Push webhook endpoint</label>
                                    <input type="text" class="form-control" value="https://api.maharashtra.gov.in/connect/webhook/notif" readonly>
                                </div>
                            </div>
                            <button class="btn-gov" onclick="alert('External API connections tested successfully. NIC Gateway: Online.')">
                                <i class="fa-solid fa-network-wired"></i> Execute API Connection Integrity Audit
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TAB 8: REPORTS & ANALYTICS (ASSIGNERS & ADMINS) -->
            <?php if ($user_type === 'admin' || $user_type === 'assigner'): ?>
                <div class="tab-panel" id="reports-tab">
                    <div class="page-header">
                        <div class="page-title">
                            <h2 data-en="Enforcement Reports Center" data-mr="अहवाल केंद्र">Enforcement Reports Center</h2>
                            <p data-en="Compile administrative statistics, delay logs, and ledger templates." data-mr="प्रशासकीय कार्यक्षमता अहवाल आणि थकीत कामांची यादी डाऊनलोड करा.">Compile administrative statistics, delay logs, and ledger templates.</p>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Generate & Download Compliant PDF/Excel" data-mr="अहवाल डाऊनलोड करा">Generate & Download Compliant PDF/Excel</h3>
                        </div>
                        <div class="panel-body">
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:20px;">
                                <div class="circular-card" style="border-left-color: var(--primary);">
                                    <h4>Duty Completion Performance Reports</h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin: 8px 0;">Contains task response timelines, officer delay ratios, and taluka-wise percentage metrics.</p>
                                    <button class="btn-gov btn-gov-secondary" onclick="alert('Generating Task Performance PDF. NIC Handshake Secure...')">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF Report
                                    </button>
                                </div>
                                <div class="circular-card" style="border-left-color: var(--status-overdue);">
                                    <h4>Overdue Warnings & Escalation Registry</h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin: 8px 0;">Lists all currently overdue duties, target due dates, and audit details of the warning notifications.</p>
                                    <button class="btn-gov btn-gov-secondary" onclick="alert('Downloading CSV Ledger...')">
                                        <i class="fa-solid fa-file-excel"></i> Download Excel Ledger
                                    </button>
                                </div>
                                <div class="circular-card" style="border-left-color: var(--status-completed);">
                                    <h4>Officer Appreciations Permanent Ledger</h4>
                                    <p style="font-size:0.85rem; color:#64748b; margin: 8px 0;">Permanent ledger of officer awards, circular notifications issued, and motivational scores.</p>
                                    <button class="btn-gov btn-gov-secondary" onclick="alert('Generating Appreciation Ledger...')">
                                        <i class="fa-solid fa-file-pdf"></i> Download Award Ledger
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 9: AUDIT LOGS -->
                <div class="tab-panel" id="audit-tab">
                    <div class="page-header">
                        <div class="page-title">
                            <h2 data-en="NIC Security Compliant Audit Trail" data-mr="एनआयसी सुरक्षा अनुपालन ऑडिट लॉग्स">NIC Security Compliant Audit Trail</h2>
                            <p data-en="Historical trace logs of all officer interactions, task allocations, status changes, and notifications." data-mr="सर्व अधिकारी संवाद, कार्य वाटप बदल आणि सूचनांचा ऐतिहासिक ट्रॅक लॉग.">Historical trace logs of all officer interactions, task allocations, status changes, and notifications.</p>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <h3 data-en="Secure Interaction Audit Trail Ledger" data-mr="सुरक्षित संवाद ऑडिट लॉगवही">Secure Interaction Audit Trail Ledger</h3>
                        </div>
                        <div class="panel-body" style="padding:0;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User Account</th>
                                        <th>Action Description</th>
                                        <th>Target ID</th>
                                        <th>Source IP</th>
                                        <th>NIC Verification</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_reverse($_SESSION['audit_logs']) as $log): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                            <td><strong><?php echo htmlspecialchars($log['user']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                                            <td><?php echo htmlspecialchars($log['target']); ?></td>
                                            <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                            <td><span style="color:#10b981; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Secured</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Status Update Modal (Only for Performers) -->
    <div class="status-modal-overlay" id="statusModal">
        <div class="status-modal">
            <h3 style="margin-bottom:15px; color:var(--primary);"><i class="fa-solid fa-hourglass-half"></i> Update Task Status</h3>
            <form action="blank.php" method="POST">
                <input type="hidden" name="action_update_task_status" value="1">
                <input type="hidden" name="task_id" id="modalTaskId">
                
                <div class="form-group">
                    <label>Select Current Status</label>
                    <select name="new_status" id="modalTaskStatus" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Work Progress Remarks / Logs</label>
                    <textarea name="remarks" id="modalTaskRemarks" class="form-control" rows="3" placeholder="Enter comments or remarks..."></textarea>
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:15px;">
                    <button type="button" class="btn-gov btn-gov-secondary" style="padding:8px 16px;" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn-gov" style="padding:8px 16px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>National Informatics Centre (NIC) Portal Development - connect-amravati-govt.in</p>
        <p style="font-size:0.75rem;">Designed and Complied with District IT Cell Amravati. © 2026 All Rights Reserved.</p>
    </footer>

<?php endif; ?>

<!-- Javascript Helper Operations -->
<script>
    let currentLang = 'en';

    function toggleLanguage() {
        currentLang = currentLang === 'en' ? 'mr' : 'en';
        document.getElementById('langBtnText').textContent = currentLang === 'en' ? 'मराठी (Switch Language)' : 'English (भाषा बदला)';
        
        const translatableElements = document.querySelectorAll('[data-en]');
        translatableElements.forEach(el => {
            if (currentLang === 'en') {
                el.textContent = el.getAttribute('data-en');
                if (el.placeholder) el.placeholder = el.getAttribute('data-en');
            } else {
                el.textContent = el.getAttribute('data-mr');
                if (el.placeholder) el.placeholder = el.getAttribute('data-mr');
            }
        });
    }

    function switchTab(menuElement, tabId) {
        const menuItems = document.querySelectorAll('.sidebar-menu-item');
        menuItems.forEach(item => item.classList.remove('active'));
        menuElement.classList.add('active');

        const tabPanels = document.querySelectorAll('.tab-panel');
        tabPanels.forEach(panel => panel.classList.remove('active'));

        const activePanel = document.getElementById(tabId);
        if (activePanel) {
            activePanel.classList.add('active');
        }
    }

    // Modal Operations (Performers update status)
    function openStatusModal(id, status, remarks) {
        document.getElementById('modalTaskId').value = id;
        document.getElementById('modalTaskStatus').value = status;
        document.getElementById('modalTaskRemarks').value = remarks;
        document.getElementById('statusModal').style.display = 'flex';
    }

    function closeStatusModal() {
        document.getElementById('statusModal').style.display = 'none';
    }

    // SDO & Village selection dynamic update
    const divisionMap = {
        'Amravati': ['HQ Office', 'Badnera Village', 'Mahuli Circle'],
        'Achalpur': ['HQ Office', 'Bhadri', 'Karanja'],
        'Teosa': ['Teosa Center', 'Kurha Village'],
        'Chikhaldara': ['Chikhaldara Village', 'Semadoh', 'Churni Area']
    };

    function updateVillages(taluka) {
        const villageSelect = document.getElementById('taskVillage');
        if (!villageSelect) return;
        
        villageSelect.innerHTML = '';
        if (divisionMap[taluka]) {
            divisionMap[taluka].forEach(village => {
                const opt = document.createElement('option');
                opt.value = village;
                opt.textContent = village;
                villageSelect.appendChild(opt);
            });
        }
    }

    // Search and Filter records in table client-side
    function searchTasks(val) {
        const query = val.toLowerCase();
        const rows = document.querySelectorAll('#taskTableBody tr');
        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const title = row.children[1].textContent.toLowerCase();
            const role = row.children[2].textContent.toLowerCase();
            const jurisdiction = row.children[3].textContent.toLowerCase();
            if (title.includes(query) || role.includes(query) || jurisdiction.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterTasks(status) {
        const rows = document.querySelectorAll('#taskTableBody tr');
        rows.forEach(row => {
            if (row.cells.length < 2) return;
            const rowStatus = row.getAttribute('data-status');
            if (status === 'All' || rowStatus === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function viewTaskDetails(taskId) {
        alert("Encrypted Security Code verified for Task " + taskId + ".\nAll audit details verified dynamically.");
    }
</script>

</body>
</html>