<?php
/**
 * Amravati Connect - Task Allocation and Tracking System
 * Master Template: blank.php
 * 
 * This is the master template file for all modules. It includes:
 * - Session Authentication Placeholder
 * - Professional Government ERP-style Layout
 * - Sidebar Navigation (Dynamic Active State)
 * - Top Navbar with Notifications & Profile
 * - Standard KPI Cards (Total, Pending, In Progress, Completed, Overdue)
 * - Main Content Placeholders (Tables, Forms, Reports)
 * - Integrated DataTables & Chart.js Support
 * 
 * Usage in other pages:
 * <?php
 *   $pageTitle = "Task Details";
 *   $activeMenu = "task_management";
 *   // Define your main content here...
 *   include("blank.php"); 
 * ?>
 */

// 1. SESSION & AUTHENTICATION CHECK
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authentication Guard Placeholder
/*
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
*/

// Mock session user data for template visualization
$sessionUser = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Shri. R. K. Deshmukh";
$sessionRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : "Collector & District Magistrate";
$sessionDept = isset($_SESSION['user_dept']) ? $_SESSION['user_dept'] : "District Administration, Amravati";

// 2. DYNAMIC PAGE CONFIGURATION
$activeMenu = isset($activeMenu) ? $activeMenu : (isset($_GET['menu']) ? $_GET['menu'] : 'dashboard');

if (!isset($pageTitle)) {
    switch ($activeMenu) {
        case 'user_management':
            $pageTitle = "User Management Portal";
            break;
        case 'task_management':
            $pageTitle = "Task Allocation & Tracking";
            break;
        case 'communication':
            $pageTitle = "Inter-Departmental Communication";
            break;
        case 'appreciation':
            $pageTitle = "Officer Appreciation & Awards";
            break;
        case 'reports':
            $pageTitle = "Analytical Reports & KPIs";
            break;
        case 'audit_logs':
            $pageTitle = "System Audit Logs";
            break;
        case 'notifications':
            $pageTitle = "Official Notifications";
            break;
        case 'settings':
            $pageTitle = "System Settings";
            break;
        case 'dashboard':
        default:
            $pageTitle = "Amravati Connect Dashboard";
            $activeMenu = "dashboard";
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Amravati Connect Task Allocation and Tracking System - Government ERP Portal">
    <meta name="author" content="Amravati District Administration">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Amravati Connect</title>
    
    <!-- Google Fonts (Outfit & Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons & FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- DataTables Bootstrap 5 CSS CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

    <!-- Theme Custom Styling -->
    <style>
        :root {
            /* Official Indian Government Portal Theme Palette */
            --gov-navy: #0B2240;       /* Deep Navy Blue - Principal brand color */
            --gov-navy-light: #1A365D; /* Light Navy for sidebar hover/active state */
            --gov-saffron: #F37021;    /* Accent Saffron */
            --gov-green: #0F8544;      /* Green Accent */
            --gov-tricolor-stripe: linear-gradient(to right, #FF9933 0%, #FFFFFF 50%, #138808 100%);
            
            /* UI colors */
            --bg-light: #F4F6F9;
            --card-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
            --transition-speed: 0.3s;
            
            /* Task Status Colors */
            --status-total: #4B5563;
            --status-pending: #F59E0B;
            --status-progress: #3B82F6;
            --status-completed: #10B981;
            --status-overdue: #EF4444;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .brand-title {
            font-family: 'Outfit', sans-serif;
        }

        /* Top Government Banner Stripe */
        .gov-banner-stripe {
            height: 4px;
            background: var(--gov-tricolor-stripe);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1050;
        }

        /* Wrapper & Layout */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            margin-top: 4px; /* Space for tricolor banner */
        }

        /* Sidebar Styling */
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: var(--gov-navy);
            color: #ffffff;
            transition: all var(--transition-speed);
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        #sidebar.collapsed {
            margin-left: -260px;
        }

        .sidebar-brand {
            padding: 1.5rem 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand img {
            width: 35px;
            height: auto;
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.2;
            margin: 0;
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: #a5b4fc;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            padding: 0.2rem 1rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.92rem;
            transition: all 0.2s ease;
        }

        .sidebar-menu a:hover {
            color: #ffffff;
            background-color: var(--gov-navy-light);
            transform: translateX(4px);
        }

        .sidebar-menu li.active a {
            color: #ffffff;
            background-color: var(--gov-saffron);
            box-shadow: 0 4px 12px rgba(243, 112, 33, 0.3);
        }

        .sidebar-menu a i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background-color: rgba(0, 0, 0, 0.15);
            font-size: 0.75rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Top Navbar Header */
        #content-wrapper {
            width: 100%;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 4px);
            transition: all var(--transition-speed);
        }

        .main-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .navbar-toggle-btn {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gov-navy);
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            transition: background 0.2s;
        }

        .navbar-toggle-btn:hover {
            background-color: #f1f5f9;
        }

        .navbar-search {
            max-width: 300px;
            position: relative;
        }

        .navbar-search input {
            border-radius: 20px;
            padding-left: 2.25rem;
            font-size: 0.88rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .navbar-search i {
            position: absolute;
            left: 0.85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.85rem;
        }

        /* Dropdown custom badges & menus */
        .nav-icon-badge {
            position: relative;
            font-size: 1.2rem;
            color: var(--gov-navy);
            padding: 0.5rem;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .nav-icon-badge:hover {
            background-color: #f1f5f9;
        }

        .nav-icon-badge .badge-count {
            position: absolute;
            top: 2px;
            right: 2px;
            font-size: 0.65rem;
            padding: 0.25em 0.45em;
            border-radius: 50%;
        }

        .profile-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: none;
            border: none;
            padding: 0.35rem 0.75rem;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .profile-dropdown-btn:hover {
            background-color: #f8fafc;
        }

        .profile-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--gov-navy);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            border: 2px solid #e2e8f0;
        }

        /* Content Container */
        .content-body {
            padding: 1.5rem;
            flex-grow: 1;
        }

        /* Breadcrumbs */
        .breadcrumb-container {
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item a {
            color: var(--gov-navy);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item.active {
            color: #64748b;
        }

        /* Custom 5-column grid for Bootstrap 5 */
        @media (min-width: 992px) {
            .col-lg-2-5 {
                flex: 0 0 auto;
                width: 20%;
            }
        }

        /* Dashboard KPI Cards styling */
        .kpi-card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            background-color: #ffffff;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .kpi-indicator {
            height: 4px;
            width: 100%;
        }

        .kpi-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .kpi-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--gov-navy);
            margin: 0;
        }

        .kpi-icon-container {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        /* Modern ERP Content Cards */
        .erp-card {
            border: none;
            border-radius: 12px;
            background-color: #ffffff;
            box-shadow: var(--card-shadow);
            margin-bottom: 1.5rem;
        }

        .erp-card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f5f9;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .erp-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gov-navy);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .erp-card-body {
            padding: 1.5rem;
        }

        /* Government Portal Seals/Logos Header */
        .gov-banner-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.5rem 1.5rem 0.75rem 1.5rem;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }

        .gov-banner-info img {
            height: 45px;
        }

        .gov-title-block {
            border-left: 2px solid #cbd5e1;
            padding-left: 0.75rem;
        }

        .gov-dept-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gov-saffron);
            margin: 0;
            text-transform: uppercase;
        }

        .gov-system-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--gov-navy);
            margin: 0;
        }

        /* Custom Badges & Status */
        .priority-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .priority-high { background-color: #fee2e2; color: #ef4444; }
        .priority-medium { background-color: #fef3c7; color: #d97706; }
        .priority-low { background-color: #d1fae5; color: #059669; }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.6rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-completed { background-color: #ecfdf5; color: #10b981; }
        .status-completed::before { background-color: #10b981; }
        .status-progress { background-color: #eff6ff; color: #3b82f6; }
        .status-progress::before { background-color: #3b82f6; }
        .status-pending { background-color: #fffbeb; color: #f59e0b; }
        .status-pending::before { background-color: #f59e0b; }
        .status-overdue { background-color: #fef2f2; color: #ef4444; }
        .status-overdue::before { background-color: #ef4444; }

        /* Footer styling */
        footer {
            background-color: var(--gov-navy);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.85rem;
            border-top: 3px solid var(--gov-saffron);
            margin-top: auto;
        }

        .footer-top {
            padding: 2.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-bottom {
            padding: 1.25rem 1.5rem;
            background-color: rgba(0, 0, 0, 0.2);
            font-size: 0.8rem;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: #ffffff;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                height: 100vh;
                margin-left: -260px;
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content-wrapper {
                width: 100%;
            }
            .gov-system-name {
                font-size: 0.95rem;
            }
            .gov-dept-name {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body>

    <!-- Saffron-White-Green Top Banner -->
    <div class="gov-banner-stripe"></div>

    <div id="wrapper">
        
        <!-- SIDEBAR COMPONENT -->
        <aside id="sidebar" class="d-flex flex-column">
            <!-- Brand Info -->
            <div class="sidebar-brand">
                <!-- Fallback Icon if Emblem image is missing -->
                <div class="avatar shadow-sm" style="width: 38px; height: 38px; background-color: #ffffff; border-radius: 6px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-monument" style="color: var(--gov-navy); font-size: 1.2rem;"></i>
                </div>
                <div>
                    <h2 class="brand-title">Amravati</h2>
                    <p class="brand-subtitle">Connect Portal</p>
                </div>
            </div>

            <!-- Sidebar Navigation Links -->
            <ul class="sidebar-menu">
                <li class="<?php echo ($activeMenu === 'dashboard') ? 'active' : ''; ?>">
                    <a href="?menu=dashboard">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'user_management') ? 'active' : ''; ?>">
                    <a href="?menu=user_management">
                        <i class="bi bi-people"></i>
                        <span>User Management</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'task_management') ? 'active' : ''; ?>">
                    <a href="?menu=task_management">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Task Management</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'communication') ? 'active' : ''; ?>">
                    <a href="?menu=communication">
                        <i class="bi bi-chat-left-text"></i>
                        <span>Communication</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'appreciation') ? 'active' : ''; ?>">
                    <a href="?menu=appreciation">
                        <i class="bi bi-award"></i>
                        <span>Appreciation</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'reports') ? 'active' : ''; ?>">
                    <a href="?menu=reports">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'audit_logs') ? 'active' : ''; ?>">
                    <a href="?menu=audit_logs">
                        <i class="bi bi-journal-text"></i>
                        <span>Audit Logs</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'notifications') ? 'active' : ''; ?>">
                    <a href="?menu=notifications">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="<?php echo ($activeMenu === 'settings') ? 'active' : ''; ?>">
                    <a href="?menu=settings">
                        <i class="bi bi-gear"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>

            <!-- Sidebar Footer / Badge -->
            <div class="sidebar-footer">
                <div>Amravati Connect v1.0.0</div>
                <div class="mt-1" style="font-size: 0.65rem;">Govt. of Maharashtra</div>
            </div>
        </aside>

        <!-- MAIN LAYOUT CONTENT WRAPPER -->
        <div id="content-wrapper">
            
            <!-- TOP NAVBAR -->
            <nav class="navbar main-navbar d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button class="navbar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <i class="bi bi-list"></i>
                    </button>
                    
                    <!-- Search Bar -->
                    <div class="navbar-search d-none d-md-block">
                        <i class="bi bi-search"></i>
                        <input type="text" class="form-control" placeholder="Search tasks, files, users...">
                    </div>
                </div>

                <!-- Right Utility Navigation -->
                <div class="d-flex align-items-center gap-3">
                    
                    <!-- Quick Notification Alert -->
                    <div class="dropdown">
                        <a href="#" class="nav-icon-badge" id="alertDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell"></i>
                            <span class="badge-count badge bg-danger">4</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-0" aria-labelledby="alertDropdown" style="width: 320px; font-size: 0.85rem;">
                            <li class="bg-light p-3 border-bottom rounded-top">
                                <h6 class="mb-0 fw-semibold text-dark d-flex justify-content-between align-items-center">
                                    <span>Notifications</span>
                                    <span class="badge bg-primary">4 New</span>
                                </h6>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 border-bottom" href="#">
                                    <div class="fw-semibold text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> Overdue Task Alert</div>
                                    <div class="text-muted text-truncate">"E-Governance Audit Report" deadline has passed.</div>
                                    <small class="text-secondary">5 mins ago</small>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 border-bottom" href="#">
                                    <div class="fw-semibold text-primary"><i class="bi bi-person-fill-add me-1"></i> New Task Assigned</div>
                                    <div class="text-muted text-truncate">You are assigned: "Clean City Campaign Review".</div>
                                    <small class="text-secondary">2 hours ago</small>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 border-bottom" href="#">
                                    <div class="fw-semibold text-success"><i class="bi bi-award-fill me-1"></i> Appreciation Received</div>
                                    <div class="text-muted text-truncate">District Collector appreciated your role in FIP implementation.</div>
                                    <small class="text-secondary">Yesterday</small>
                                </a>
                            </li>
                            <li class="text-center py-2">
                                <a href="?menu=notifications" class="text-decoration-none fw-semibold text-primary">View All Notifications</a>
                            </li>
                        </ul>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="dropdown">
                        <button class="profile-dropdown-btn dropdown-toggle" id="userProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="profile-avatar">
                                <?php 
                                    // Render initials of user
                                    $nameParts = explode(" ", $sessionUser);
                                    $initials = "";
                                    foreach ($nameParts as $part) {
                                        if (strpos($part, ".") === false) {
                                            $initials .= substr($part, 0, 1);
                                        }
                                    }
                                    echo htmlspecialchars(strtoupper(substr($initials, 0, 2)));
                                ?>
                            </div>
                            <div class="text-start d-none d-sm-block" style="line-height: 1.1;">
                                <div class="fw-semibold text-dark" style="font-size: 0.85rem;"><?php echo htmlspecialchars($sessionUser); ?></div>
                                <small class="text-muted" style="font-size: 0.72rem;"><?php echo htmlspecialchars($sessionRole); ?></small>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userProfileDropdown">
                            <li class="p-2 border-bottom text-center">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($sessionUser); ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($sessionDept); ?></div>
                            </li>
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2 text-muted"></i> My Profile</a></li>
                            <li><a class="dropdown-item py-2" href="?menu=settings"><i class="bi bi-gear me-2 text-muted"></i> Account Settings</a></li>
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-shield-check me-2 text-muted"></i> Security Portal</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item py-2 text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i> Secure Logout</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- NATIONAL/STATE BANNER INFORMATION -->
            <div class="gov-banner-info">
                <!-- Ashok Stambh Emblem Fallback Visual -->
                <div class="d-flex align-items-center justify-content-center" style="width: 32px; height: 50px; background-color: #f1f5f9; border-radius: 4px; border: 1px solid #cbd5e1;">
                    <i class="fa-solid fa-building-columns" style="color: var(--gov-navy); font-size: 1.1rem;"></i>
                </div>
                <div class="gov-title-block">
                    <p class="gov-dept-name">Amravati District Administration, Government of Maharashtra</p>
                    <h1 class="gov-system-name">Amravati Connect: Task Allocation & Tracking System</h1>
                </div>
            </div>

            <!-- BREADCRUMBS & ACTION HEADER -->
            <div class="content-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
                    <div>
                        <nav aria-label="breadcrumb" class="breadcrumb-container mb-1">
                            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                                <li class="breadcrumb-item"><a href="?menu=dashboard"><i class="bi bi-house-door-fill me-1"></i>Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($pageTitle); ?></li>
                            </ol>
                        </nav>
                        <h2 class="mb-0 fw-bold text-dark" style="font-size: 1.6rem;"><?php echo htmlspecialchars($pageTitle); ?></h2>
                    </div>
                    
                    <!-- Date & Time Dashboard Stamp -->
                    <div class="bg-white px-3 py-2 border rounded shadow-sm text-end" style="font-size: 0.82rem;">
                        <span class="text-muted"><i class="bi bi-calendar3 me-1 text-primary"></i> Current Date: </span>
                        <span class="fw-semibold text-dark" id="currentDateDisplay"><?php echo date("d F Y"); ?></span>
                        <div style="font-size: 0.75rem;" class="text-secondary"><i class="bi bi-clock me-1"></i> System Time (IST)</div>
                    </div>
                </div>

                <!-- 3. DASHBOARD STATS CARD SECTION (TOTAL, PENDING, IN PROGRESS, COMPLETED, OVERDUE) -->
                <div class="row g-3 mb-4">
                    <!-- Total Tasks -->
                    <div class="col-12 col-sm-6 col-lg-2-5 col-xl">
                        <div class="kpi-card card">
                            <div class="kpi-indicator" style="background-color: var(--status-total);"></div>
                            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="kpi-title">Total Tasks</div>
                                    <h3 class="kpi-value">148</h3>
                                    <small class="text-success"><i class="bi bi-arrow-up-short"></i> +12 this week</small>
                                </div>
                                <div class="kpi-icon-container bg-light text-dark">
                                    <i class="bi bi-layers-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Tasks -->
                    <div class="col-12 col-sm-6 col-lg-2-5 col-xl">
                        <div class="kpi-card card">
                            <div class="kpi-indicator" style="background-color: var(--status-pending);"></div>
                            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="kpi-title">Pending</div>
                                    <h3 class="kpi-value" style="color: var(--status-pending);">34</h3>
                                    <small class="text-muted"><i class="bi bi-clock-history"></i> Awaiting start</small>
                                </div>
                                <div class="kpi-icon-container" style="background-color: #fffbeb; color: var(--status-pending);">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- In Progress Tasks -->
                    <div class="col-12 col-sm-6 col-lg-2-5 col-xl">
                        <div class="kpi-card card">
                            <div class="kpi-indicator" style="background-color: var(--status-progress);"></div>
                            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="kpi-title">In Progress</div>
                                    <h3 class="kpi-value" style="color: var(--status-progress);">52</h3>
                                    <small class="text-primary"><i class="bi bi-arrow-repeat"></i> Active tracking</small>
                                </div>
                                <div class="kpi-icon-container" style="background-color: #eff6ff; color: var(--status-progress);">
                                    <i class="bi bi-gear-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Tasks -->
                    <div class="col-12 col-sm-6 col-lg-2-5 col-xl">
                        <div class="kpi-card card">
                            <div class="kpi-indicator" style="background-color: var(--status-completed);"></div>
                            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="kpi-title">Completed</div>
                                    <h3 class="kpi-value" style="color: var(--status-completed);">54</h3>
                                    <small class="text-success"><i class="bi bi-check-all"></i> 91% satisfaction</small>
                                </div>
                                <div class="kpi-icon-container" style="background-color: #ecfdf5; color: var(--status-completed);">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overdue Tasks -->
                    <div class="col-12 col-sm-6 col-lg-2-5 col-xl">
                        <div class="kpi-card card">
                            <div class="kpi-indicator" style="background-color: var(--status-overdue);"></div>
                            <div class="card-body py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="kpi-title">Overdue</div>
                                    <h3 class="kpi-value" style="color: var(--status-overdue);">8</h3>
                                    <small class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Immediate action</small>
                                </div>
                                <div class="kpi-icon-container" style="background-color: #fef2f2; color: var(--status-overdue);">
                                    <i class="bi bi-calendar-x-fill"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. DYNAMIC CONTENT AREA / PLACEHOLDERS -->
                <!-- Developer Tip: Standard dynamic content begins here. Replace this row structure with custom content -->
                <div class="row g-4">
                    
                    <!-- Table View Module -->
                    <div class="col-12 col-lg-8">
                        <div class="erp-card card">
                            <div class="erp-card-header">
                                <h3 class="erp-card-title">
                                    <i class="bi bi-list-task text-primary"></i>
                                    Recent Allocated Tasks
                                </h3>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="tableFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-funnel-fill me-1"></i> Filter Status
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="tableFilterDropdown">
                                        <li><a class="dropdown-item" href="#">All Tasks</a></li>
                                        <li><a class="dropdown-item" href="#">High Priority</a></li>
                                        <li><a class="dropdown-item" href="#">Awaiting Approval</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="erp-card-body">
                                <div class="table-responsive">
                                    <table id="masterTasksTable" class="table table-hover table-striped w-100" style="font-size: 0.9rem;">
                                        <thead>
                                            <tr>
                                                <th>Task ID</th>
                                                <th>Task Title</th>
                                                <th>Assigned To Dept</th>
                                                <th>Target Date</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th style="width: 100px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="fw-bold text-secondary">AMR-2026-004</span></td>
                                                <td>FIP Infrastructure Layout Approval</td>
                                                <td>Urban Dev. Dept</td>
                                                <td>20 Jun 2026</td>
                                                <td><span class="priority-badge priority-high">High</span></td>
                                                <td><span class="status-badge status-progress">In Progress</span></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit Task"><i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i></a>
                                                        <a href="#" class="btn btn-sm btn-outline-info py-0 px-2" title="View Progress"><i class="bi bi-eye" style="font-size: 0.85rem;"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="fw-bold text-secondary">AMR-2026-012</span></td>
                                                <td>Collectorate IT Infrastructure Audit</td>
                                                <td>National Informatics Centre</td>
                                                <td>15 Jun 2026</td>
                                                <td><span class="priority-badge priority-high">High</span></td>
                                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit Task"><i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i></a>
                                                        <a href="#" class="btn btn-sm btn-outline-info py-0 px-2" title="View Progress"><i class="bi bi-eye" style="font-size: 0.85rem;"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="fw-bold text-secondary">AMR-2026-031</span></td>
                                                <td>FIP Ground Survey Submissions</td>
                                                <td>Revenue Department</td>
                                                <td>30 Jun 2026</td>
                                                <td><span class="priority-badge priority-medium">Medium</span></td>
                                                <td><span class="status-badge status-pending">Pending</span></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit Task"><i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i></a>
                                                        <a href="#" class="btn btn-sm btn-outline-info py-0 px-2" title="View Progress"><i class="bi bi-eye" style="font-size: 0.85rem;"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><span class="fw-bold text-secondary">AMR-2026-042</span></td>
                                                <td>Clean City Campaign Launch Program</td>
                                                <td>Amravati Municipal Corp</td>
                                                <td>12 Jun 2026</td>
                                                <td><span class="priority-badge priority-low">Low</span></td>
                                                <td><span class="status-badge status-completed">Completed</span></td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" title="Edit Task"><i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i></a>
                                                        <a href="#" class="btn btn-sm btn-outline-info py-0 px-2" title="View Progress"><i class="bi bi-eye" style="font-size: 0.85rem;"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Graphical Reports Chart -->
                        <div class="erp-card card">
                            <div class="erp-card-header">
                                <h3 class="erp-card-title">
                                    <i class="bi bi-bar-chart-line text-success"></i>
                                    Department Wise Performance & Deliverables
                                </h3>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshChart()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                            </div>
                            <div class="erp-card-body">
                                <div style="height: 280px; position: relative;">
                                    <canvas id="departmentStatsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input Form Module -->
                    <div class="col-12 col-lg-4">
                        <div class="erp-card card">
                            <div class="erp-card-header">
                                <h3 class="erp-card-title">
                                    <i class="bi bi-plus-circle-fill text-warning"></i>
                                    Allocate New Task
                                </h3>
                            </div>
                            <div class="erp-card-body">
                                <form id="allocateTaskForm" class="needs-validation" novalidate autocomplete="off">
                                    
                                    <div class="mb-3">
                                        <label for="taskTitle" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Task Title/Subject <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="taskTitle" placeholder="Enter task descriptive title" required>
                                        <div class="invalid-feedback">Please enter the task title.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="deptAssign" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Assigned Department <span class="text-danger">*</span></label>
                                        <select class="form-select" id="deptAssign" required>
                                            <option value="" disabled selected hidden>Choose Department</option>
                                            <option value="Urban Dev">Urban Development Dept</option>
                                            <option value="Revenue">Revenue & Lands</option>
                                            <option value="NIC">NIC Infrastructure</option>
                                            <option value="Municipal Corp">Amravati Municipal Corporation</option>
                                        </select>
                                        <div class="invalid-feedback">Please choose a department.</div>
                                    </div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label for="taskTargetDate" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Target Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="taskTargetDate" required>
                                            <div class="invalid-feedback">Select target date.</div>
                                        </div>
                                        <div class="col-6">
                                            <label for="taskPriority" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Priority Level <span class="text-danger">*</span></label>
                                            <select class="form-select" id="taskPriority" required>
                                                <option value="high">High</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="low">Low</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="taskDesc" class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">Description / Directives</label>
                                        <textarea class="form-control" id="taskDesc" rows="3" placeholder="Provide detailed instructions for the department officers..."></textarea>
                                    </div>

                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="notifyOfficer" checked>
                                        <label class="form-check-label text-muted" for="notifyOfficer" style="font-size: 0.82rem;">Notify Head of Department instantly via SMS/Email</label>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background-color: var(--gov-navy); border-color: var(--gov-navy);">
                                        <i class="bi bi-send-fill me-1"></i> Issue Allocation Order
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Info/Guide Panel -->
                        <div class="card p-3 border-0 shadow-sm" style="background-color: #e0f2fe; border-left: 5px solid #0284c7 !important; border-radius: 8px;">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill text-info" style="font-size: 1.25rem;"></i>
                                <div>
                                    <h5 class="fw-semibold text-info-emphasis mb-1" style="font-size: 0.95rem;">Official Guidelines</h5>
                                    <p class="text-info-emphasis mb-0" style="font-size: 0.82rem; line-height: 1.4;">
                                        All tasks issued via this platform carry the weight of an official administrative directive under section 4B of governance rules.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FOOTER COMPONENT -->
            <footer>
                <div class="footer-top">
                    <div class="container-fluid">
                        <div class="row g-4">
                            <div class="col-12 col-md-4">
                                <h5 class="text-white fw-bold mb-3">Amravati Connect</h5>
                                <p style="font-size: 0.8rem; line-height: 1.6; color: rgba(255,255,255,0.6);">
                                    An official task allocation, review, and dynamic tracking portal designed to enhance transparency, inter-departmental collaboration, and speedy project completion across Amravati District.
                                </p>
                            </div>
                            <div class="col-6 col-md-4">
                                <h5 class="text-white fw-bold mb-3">Quick Links</h5>
                                <ul class="list-unstyled d-flex flex-column gap-2" style="font-size: 0.8rem;">
                                    <li><a href="#" class="footer-link"><i class="bi bi-chevron-right me-1 text-warning"></i> Maharashtra State Portal</a></li>
                                    <li><a href="#" class="footer-link"><i class="bi bi-chevron-right me-1 text-warning"></i> National Informatics Centre (NIC)</a></li>
                                    <li><a href="#" class="footer-link"><i class="bi bi-chevron-right me-1 text-warning"></i> District Collectorate Office</a></li>
                                    <li><a href="#" class="footer-link"><i class="bi bi-chevron-right me-1 text-warning"></i> Privacy Policy & Disclaimers</a></li>
                                </ul>
                            </div>
                            <div class="col-6 col-md-4">
                                <h5 class="text-white fw-bold mb-3">Support Helpdesk</h5>
                                <p class="mb-1" style="font-size: 0.8rem;"><i class="bi bi-envelope-fill text-warning me-2"></i> connect.amravati@maharashtra.gov.in</p>
                                <p class="mb-1" style="font-size: 0.8rem;"><i class="bi bi-telephone-fill text-warning me-2"></i> +91 721-255-0810 (Ext: 12)</p>
                                <p style="font-size: 0.8rem;"><i class="bi bi-building text-warning me-2"></i> IT Cell, Ground Floor, Collectorate Office, Amravati.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer-bottom text-center">
                    <div class="container-fluid d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                        <div>
                            &copy; <?php echo date("Y"); ?> Amravati District Administration. All rights reserved.
                        </div>
                        <div>
                            System Powered by <a href="https://settribe.com" class="text-white fw-semibold text-decoration-none">SETTribe IT Solutions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- BOOTSTRAP BUNDLE JS WITH POPPER CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JQUERY & DATATABLES JS CDN -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <!-- CHART.JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Master Template Layout Controller -->
    <script>
        $(document).ready(function() {
            // 1. Sidebar Toggle Mechanics
            $('#sidebarToggle').on('click', function() {
                $('#sidebar').toggleClass('collapsed');
                // Support mobile active states overlay
                $('#sidebar').toggleClass('active');
            });

            // Auto-collapse sidebar on smaller screens
            $(window).resize(function() {
                if ($(window).width() < 768) {
                    $('#sidebar').addClass('collapsed').removeClass('active');
                } else {
                    $('#sidebar').removeClass('collapsed');
                }
            });

            // 2. DataTables Initialization
            if ($('#masterTasksTable').length) {
                $('#masterTasksTable').DataTable({
                    responsive: true,
                    pageLength: 5,
                    lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
                    order: [[3, "asc"]], // Order by target date
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "Search records...",
                        lengthMenu: "Show _MENU_ entries",
                        info: "Showing _START_ to _END_ of _TOTAL_ directives",
                        paginate: {
                            previous: "<i class='bi bi-chevron-left'></i>",
                            next: "<i class='bi bi-chevron-right'></i>"
                        }
                    }
                });
            }

            // 3. Form Validation Controller
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    } else {
                        // Demo submit intercept
                        event.preventDefault();
                        alert("Task allocated and official directive emitted successfully!");
                        form.reset();
                        form.classList.remove('was-validated');
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // 4. Initialize Chart.js
            initChart();
        });

        // 5. Chart.js Implementation
        let performanceChart;
        function initChart() {
            const ctx = document.getElementById('departmentStatsChart');
            if (!ctx) return;

            const chartConfig = {
                type: 'bar',
                data: {
                    labels: ['Urban Dev', 'Revenue & Lands', 'NIC IT Cell', 'Municipal Corp', 'Health Dept', 'Public Works'],
                    datasets: [
                        {
                            label: 'Completed Tasks',
                            data: [18, 24, 12, 30, 21, 15],
                            backgroundColor: '#10B981', // green
                            borderRadius: 6
                        },
                        {
                            label: 'In Progress / Pending',
                            data: [12, 8, 14, 15, 6, 12],
                            backgroundColor: '#3B82F6', // blue
                            borderRadius: 6
                        },
                        {
                            label: 'Overdue Deliverables',
                            data: [2, 1, 0, 3, 2, 4],
                            backgroundColor: '#EF4444', // red
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    family: "'Inter', sans-serif",
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            padding: 10,
                            titleFont: { family: "'Outfit', sans-serif" }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: "'Inter', sans-serif" } }
                        },
                        y: {
                            stacked: true,
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { font: { family: "'Inter', sans-serif" } }
                        }
                    }
                }
            };

            performanceChart = new Chart(ctx, chartConfig);
        }

        // Action helper for demo
        function refreshChart() {
            if (performanceChart) {
                // Mock dynamic update
                performanceChart.data.datasets.forEach((dataset) => {
                    dataset.data = dataset.data.map(() => Math.floor(Math.random() * 35));
                });
                performanceChart.update();
            }
        }
    </script>
</body>
</html>
