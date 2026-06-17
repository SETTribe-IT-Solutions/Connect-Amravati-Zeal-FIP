<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Amravati - Dashboard & Security Hub</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern Reset & Color Variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        :root {
            --bg-dark: #080c18;
            --bg-card: rgba(15, 23, 42, 0.65);
            --bg-card-hover: rgba(22, 38, 70, 0.85);
            --border: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(99, 102, 241, 0.6);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            --accent-gradient: linear-gradient(135deg, #a855f7 0%, #7e22ce 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            
            --glow-primary: rgba(99, 102, 241, 0.3);
            --glow-secondary: rgba(6, 182, 212, 0.3);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            display: flex;
        }

        /* Ambient Dynamic Background Glows */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            z-index: -1;
            filter: blur(150px);
            opacity: 0.18;
            pointer-events: none;
        }

        body::before {
            background: #6366f1;
            top: -10%;
            left: -10%;
            animation: floatGlow 18s infinite alternate ease-in-out;
        }

        body::after {
            background: #a855f7;
            bottom: -10%;
            right: -10%;
            animation: floatGlow 18s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(80px, 50px) scale(1.15); }
        }

        /* Sidebar navigation */
        aside {
            width: 280px;
            background: rgba(10, 15, 30, 0.85);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        @media (max-width: 992px) {
            aside {
                display: none; /* Collapsed on mobile/tablets for responsiveness */
            }
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .brand-logo {
            background: var(--primary-gradient);
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            box-shadow: 0 0 20px var(--glow-primary);
        }

        .brand-name h1 {
            font-size: 1.3rem;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-name p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            list-style: none;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 1.1rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .nav-item.active a, .nav-item a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .nav-item.active a {
            border-left: 3px solid var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        /* Main Dashboard Content Area */
        .dashboard-wrapper {
            flex: 1;
            padding: 2.5rem;
            overflow-y: auto;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        @media (max-width: 576px) {
            .dashboard-wrapper {
                padding: 1.5rem 1rem;
            }
        }

        /* Top Header Navigation */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
        }

        .header-title h2 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .header-title p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Dynamic Role Switcher Dropdown */
        .role-switcher-container {
            display: flex;
            align-items: center;
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.5rem 1rem;
            gap: 0.5rem;
        }

        .role-switcher-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .role-dropdown {
            background: transparent;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            outline: none;
            cursor: pointer;
        }

        .role-dropdown option {
            background: #0f172a;
            color: white;
        }

        /* Profile Area */
        .profile-badge {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--accent-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        /* Glassmorphic Dashboard Cards */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.35);
            position: relative;
            margin-bottom: 2rem;
        }

        .glass-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            padding: 1px;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.12), transparent);
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }

        .card-heading {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-heading i {
            color: var(--primary);
        }

        /* Grid System for Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            .dashboard-grid {
                grid-template-columns: 1.2fr 0.8fr;
            }
        }

        /* Form styling */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            background: rgba(10, 15, 30, 0.6);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            color: white;
            font-size: 0.9rem;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.25);
            background: rgba(10, 15, 30, 0.8);
        }

        /* Buttons styling */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }

        .btn-primary:hover {
            box-shadow: 0 0 15px var(--glow-primary);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--text-muted);
        }

        .btn-danger {
            background: var(--danger-gradient);
            color: white;
        }

        .btn-danger:hover {
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
            transform: translateY(-1px);
        }

        /* FEATURE 1: 2-Step Authentication (2FA) UI */
        .fa-card-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .fa-card-layout {
                grid-template-columns: 1fr 1fr;
            }
        }

        .fa-status-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .fa-status-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .fa-status-left i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        /* Switch Toggle button */
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border);
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background: var(--success-gradient);
            border-color: transparent;
        }

        input:checked + .slider:before {
            transform: translateX(24px);
        }

        /* OTP Code Verification Inputs */
        .otp-input-group {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1.5rem 0;
        }

        .otp-box {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            background: rgba(10, 15, 30, 0.7);
            border: 1px solid var(--border);
            text-align: center;
            font-size: 1.25rem;
            font-weight: 700;
            color: white;
            outline: none;
        }

        .otp-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
        }

        /* Authenticator Setup QR section */
        .qr-setup-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(10, 15, 30, 0.4);
            border-radius: 12px;
            border: 1px solid var(--border);
        }

        .qr-mock {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 8px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Draw a simulated QR Code with pure CSS grid lines */
        .qr-pattern {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            width: 100%;
            height: 100%;
            gap: 3px;
        }

        .qr-block {
            background-color: #0b0f19;
            border-radius: 2px;
        }

        .qr-block.empty {
            background-color: transparent;
        }

        /* Backup code cards */
        .backup-codes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .backup-code-pill {
            background: rgba(255, 255, 255, 0.04);
            border: 1px dashed var(--border);
            border-radius: 6px;
            padding: 0.5rem;
            text-align: center;
            font-family: monospace;
            font-size: 0.9rem;
            letter-spacing: 1px;
            color: var(--text-muted);
        }

        /* FEATURE 2: Task Allocation based on Roles */
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .task-progress-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            font-size: 0.85rem;
        }

        .progress-bar-outer {
            width: 100px;
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar-inner {
            height: 100%;
            background: var(--primary-gradient);
            width: 0%;
            transition: width 0.5s ease;
        }

        .task-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            max-height: 350px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        /* Custom Scrollbar for elements */
        .task-list::-webkit-scrollbar {
            width: 6px;
        }
        .task-list::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            gap: 1rem;
        }

        .task-item:hover {
            border-color: rgba(99, 102, 241, 0.25);
            background: rgba(255, 255, 255, 0.05);
        }

        .task-left {
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }

        .task-checkbox-container {
            margin-top: 3px;
        }

        .task-details h4 {
            font-size: 0.95rem;
            font-weight: 600;
        }

        .task-details p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        .task-item.completed .task-details h4 {
            text-decoration: line-through;
            color: var(--text-muted);
        }

        .priority-badge {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .priority-high {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
        }

        .priority-medium {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
        }

        .priority-low {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }

        /* Checkbox custom style */
        .task-checkbox {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid var(--border);
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .task-checkbox i {
            display: none;
            color: white;
            font-size: 0.7rem;
        }

        .task-item.completed .task-checkbox {
            background: var(--success-gradient);
            border-color: transparent;
        }

        .task-item.completed .task-checkbox i {
            display: block;
        }

        /* FEATURE 3: Login details & session management */
        .session-info-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .session-detail-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
        }

        .session-detail-card h5 {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .session-detail-card p {
            font-size: 1.05rem;
            font-weight: 700;
        }

        /* Recent Sessions table */
        .table-responsive {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(10, 15, 30, 0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.85rem;
        }

        th {
            background: rgba(15, 23, 42, 0.8);
            color: var(--text-muted);
            padding: 0.85rem 1.2rem;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 0.85rem 1.2rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge-current {
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Alert Toast Styling */
        .toast-wrapper {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast-item {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            color: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.85rem;
            font-weight: 600;
            animation: toastIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes toastIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast-item.success { border-left-color: #10b981; }
        .toast-item.warning { border-left-color: #f59e0b; }
        .toast-item.danger { border-left-color: #ef4444; }
    </style>
</head>
<body>

    <!-- Sidebar component -->
    <aside>
        <div class="brand-section">
            <div class="brand-logo">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div class="brand-name">
                <h1>Amravati Connect</h1>
                <p>Zeal FIP Administration</p>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item active">
                <a href="#dashboard"><i class="fa-solid fa-gauge"></i> Dashboard Overview</a>
            </li>
            <li class="nav-item">
                <a href="#tasks"><i class="fa-solid fa-list-check"></i> Role Allocations</a>
            </li>
            <li class="nav-item">
                <a href="#security"><i class="fa-solid fa-shield-halved"></i> 2-Step Authentication</a>
            </li>
            <li class="nav-item">
                <a href="#login"><i class="fa-solid fa-user-lock"></i> Session Logs</a>
            </li>
        </ul>

        <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 1rem;">
            <div class="avatar" id="sidebarAvatar">A</div>
            <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <h4 style="font-size: 0.85rem; font-weight: 600;" id="sidebarUser">Administrator</h4>
                <p style="font-size: 0.75rem; color: var(--text-muted);" id="sidebarRole">Super User</p>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="dashboard-wrapper">
        <!-- Top header bar -->
        <header>
            <div class="header-title">
                <h2 id="welcomeHeading">Welcome back, Admin</h2>
                <p>Here is what's happening with Amravati Connect workspace today.</p>
            </div>

            <div class="header-controls">
                <div class="role-switcher-container">
                    <span class="role-switcher-label">Select Role:</span>
                    <select id="dashboardRoleSelector" class="role-dropdown" onchange="changeActiveRole(this.value)">
                        <option value="Administrator">Administrator</option>
                        <option value="Developer">Lead Developer</option>
                        <option value="UI/UX Designer">UI/UX Designer</option>
                        <option value="Project Manager">Project Manager</option>
                    </select>
                </div>

                <div class="profile-badge">
                    <div class="avatar" id="headerAvatar">A</div>
                </div>
            </div>
        </header>

        <!-- Dashboard content grids -->
        <div class="dashboard-grid">
            
            <!-- LEFT PANEL: Tasks Allocation & Form -->
            <div class="left-panel">
                
                <!-- FEATURE 2: Task Allocation based on Roles -->
                <section class="glass-card" id="tasks">
                    <div class="task-header">
                        <h3 class="card-heading">
                            <i class="fa-solid fa-list-check"></i>
                            Task Allocations for <span id="currentRoleTitle">Administrator</span>
                        </h3>
                        
                        <div class="task-progress-container">
                            <span>Progress: <strong id="progressText">0%</strong></span>
                            <div class="progress-bar-outer">
                                <div class="progress-bar-inner" id="progressBar"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Task List elements -->
                    <div class="task-list" id="taskListContainer">
                        <!-- Loaded dynamically via JS -->
                    </div>

                    <!-- Add new task item box -->
                    <div style="background: rgba(10,15,30,0.3); border: 1px dashed var(--border); padding: 1.5rem; border-radius: 12px; margin-top: 1.5rem;">
                        <h4 style="font-size: 0.9rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i> Allocate New Task
                        </h4>
                        
                        <form id="newTaskForm" onsubmit="createNewTask(event)">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Task Name</label>
                                    <input type="text" id="newTaskTitle" class="form-control" placeholder="Configure server replication" required>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Target Role Allocation</label>
                                    <select id="newTaskRole" class="form-control" required>
                                        <option value="Administrator">Administrator</option>
                                        <option value="Developer">Lead Developer</option>
                                        <option value="UI/UX Designer">UI/UX Designer</option>
                                        <option value="Project Manager">Project Manager</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Priority</label>
                                    <select id="newTaskPriority" class="form-control">
                                        <option value="high">High Priority</option>
                                        <option value="medium" selected>Medium Priority</option>
                                        <option value="low">Low Priority</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">Estimate / Deadline</label>
                                    <input type="text" id="newTaskDeadline" class="form-control" placeholder="2 days remaining">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="width: auto;">
                                <i class="fa-solid fa-circle-check"></i> Assign Task
                            </button>
                        </form>
                    </div>
                </section>

                <!-- FEATURE 3: Login Details & Active Session Logging -->
                <section class="glass-card" id="login">
                    <h3 class="card-heading">
                        <i class="fa-solid fa-user-lock"></i>
                        Login Details & Active Sessions
                    </h3>

                    <!-- Quick Session Stat boxes -->
                    <div class="session-info-bar">
                        <div class="session-detail-card">
                            <h5>Current User ID</h5>
                            <p id="sessionUser">admin_fip_01</p>
                        </div>
                        <div class="session-detail-card">
                            <h5>Access Token Type</h5>
                            <p>Bearer JWT (Expiring in 2h)</p>
                        </div>
                        <div class="session-detail-card">
                            <h5>Your IP Address</h5>
                            <p>192.168.1.142</p>
                        </div>
                    </div>

                    <h4 style="font-size: 0.95rem; margin-bottom: 1rem; color: var(--text-muted);">Registered Devices & Sessions</h4>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Device / Browser</th>
                                    <th>IP Address</th>
                                    <th>Location</th>
                                    <th>Last Activity</th>
                                    <th>Status</th>
                                    <th style="width: 80px; text-align: center;">Revoke</th>
                                </tr>
                            </thead>
                            <tbody id="sessionsTableBody">
                                <!-- Dynamic JS list -->
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>

            <!-- RIGHT PANEL: 2-Step Authentication Config Cards -->
            <div class="right-panel">
                
                <!-- FEATURE 1: 2-Step Authentication Card -->
                <section class="glass-card" id="security">
                    <h3 class="card-heading">
                        <i class="fa-solid fa-shield-halved"></i>
                        2-Step Authentication (2FA)
                    </h3>

                    <!-- 2FA Status Toggle Panel -->
                    <div class="fa-status-banner">
                        <div class="fa-status-left">
                            <i class="fa-solid fa-key" id="faStatusIcon"></i>
                            <div>
                                <h4 style="font-size: 0.95rem;" id="faStatusTitle">2FA is Currently Disabled</h4>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">Secure login access validation</p>
                            </div>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="faToggleSwitch" onchange="toggle2FASettings(this.checked)">
                            <span class="slider"></span>
                        </label>
                    </div>

                    <!-- 2FA Interaction Layout -->
                    <div id="faInteractiveContent" style="display: none;">
                        
                        <!-- Panel A: Authenticator App setup -->
                        <div class="qr-setup-area" style="margin-bottom: 1.5rem;">
                            <div class="qr-mock">
                                <div class="qr-pattern">
                                    <!-- A mock QR layout -->
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>

                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block empty"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block"></div>
                                    <div class="qr-block empty"></div>
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <h4 style="font-size: 0.9rem; margin-bottom: 0.25rem;">Scan QR Authenticator</h4>
                                <p style="font-size: 0.75rem; color: var(--text-muted);">Use Google Authenticator or Microsoft Auth app</p>
                            </div>
                        </div>

                        <!-- Panel B: Enter verification code mockup -->
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: center;">
                            <h4 style="font-size: 0.9rem;">Verify Setup Code</h4>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Enter the 6-digit OTP code shown in your app</p>
                            
                            <div class="otp-input-group">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 1)">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 2)">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 3)">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 4)">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 5)">
                                <input type="text" maxlength="1" class="otp-box" onkeyup="focusNextOtp(this, 6)">
                            </div>

                            <button class="btn btn-primary" onclick="verifyOtpCode()" style="width: 100%;">
                                <i class="fa-solid fa-shield-check"></i> Complete Verification
                            </button>
                        </div>

                        <!-- Panel C: Backup Codes -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <h4 style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">One-time Backup Codes</h4>
                                <button class="btn-outline" style="border: none; background: transparent; font-size: 0.75rem; color: var(--primary); cursor: pointer;" onclick="regenerateBackupCodes()">Regenerate</button>
                            </div>
                            <p style="font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.75rem;">Save these codes in a safe place. They can bypass 2FA check.</p>
                            <div class="backup-codes-grid" id="backupCodesGrid">
                                <!-- Loaded dynamically via JS -->
                            </div>
                        </div>

                    </div>

                </section>
            </div>

        </div>
    </div>

    <!-- Active Toast Alerts container -->
    <div class="toast-wrapper" id="toastWrapper"></div>

    <script>
        // State Management Objects
        const systemState = {
            activeRole: "Administrator",
            twoFactorEnabled: false,
            twoFactorVerified: false,
            backupCodes: ["9382-1029", "8823-4410", "1204-9938", "5019-3829"],
            sessions: [
                {
                    id: 1,
                    device: "Chrome / Windows 11",
                    ip: "192.168.1.142",
                    location: "Amravati, MH, India",
                    lastActive: "Just Now",
                    status: "Current"
                },
                {
                    id: 2,
                    device: "Firefox / macOS Sequoia",
                    ip: "103.24.18.99",
                    location: "Pune, India",
                    lastActive: "2 hours ago",
                    status: "Active"
                },
                {
                    id: 3,
                    device: "Safari / iPhone 15 Pro",
                    ip: "182.12.82.110",
                    location: "Nagpur, MH, India",
                    lastActive: "1 day ago",
                    status: "Active"
                }
            ],
            tasks: [
                // Tasks for Admin
                { id: 1, name: "Configure Two-Factor Authentication server variables", role: "Administrator", priority: "high", deadline: "1 hour remaining", completed: false },
                { id: 2, name: "Review workspace member access logs & audit tokens", role: "Administrator", priority: "medium", deadline: "4 hours remaining", completed: true },
                { id: 3, name: "Approve database migrations for FIP student tables", role: "Administrator", priority: "high", deadline: "Completed", completed: true },
                
                // Tasks for Lead Developer
                { id: 4, name: "Optimize MySQL queries in FIP record search handler", role: "Developer", priority: "high", deadline: "3 hours remaining", completed: false },
                { id: 5, name: "Build interactive 2FA passcode input transition animation", role: "Developer", priority: "medium", deadline: "Tomorrow", completed: false },
                
                // Tasks for UI/UX Designer
                { id: 6, name: "Create high-fidelity dark mockups for FIP landing", role: "UI/UX Designer", priority: "medium", deadline: "2 days remaining", completed: false },
                { id: 7, name: "Redesign glassmorphic button shadow glows & active states", role: "UI/UX Designer", priority: "low", deadline: "Completed", completed: true },
                
                // Tasks for Project Manager
                { id: 8, name: "Sync with Zeal organization coordinators for faculty enrollment", role: "Project Manager", priority: "high", deadline: "Tomorrow", completed: false },
                { id: 9, name: "Deliver progress report on Connect-Amravati milestones", role: "Project Manager", priority: "medium", deadline: "Friday", completed: false }
            ]
        };

        // Cache elements
        const welcomeHeading = document.getElementById('welcomeHeading');
        const currentRoleTitle = document.getElementById('currentRoleTitle');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const taskListContainer = document.getElementById('taskListContainer');
        const sessionsTableBody = document.getElementById('sessionsTableBody');
        
        // 2FA cache
        const faToggleSwitch = document.getElementById('faToggleSwitch');
        const faStatusTitle = document.getElementById('faStatusTitle');
        const faStatusIcon = document.getElementById('faStatusIcon');
        const faInteractiveContent = document.getElementById('faInteractiveContent');
        const backupCodesGrid = document.getElementById('backupCodesGrid');
        
        // Sidebar cache
        const sidebarAvatar = document.getElementById('sidebarAvatar');
        const sidebarUser = document.getElementById('sidebarUser');
        const sidebarRole = document.getElementById('sidebarRole');
        const headerAvatar = document.getElementById('headerAvatar');

        // Initial setup run
        document.addEventListener("DOMContentLoaded", () => {
            renderRoleTasks();
            renderSessions();
            renderBackupCodes();
        });

        // Toast alert launcher
        function displayToast(msg, type = "success") {
            const wrapper = document.getElementById('toastWrapper');
            const toast = document.createElement('div');
            toast.className = `toast-item ${type}`;
            
            let icon = '<i class="fa-solid fa-circle-check"></i>';
            if (type === 'warning') icon = '<i class="fa-solid fa-triangle-exclamation"></i>';
            if (type === 'danger') icon = '<i class="fa-solid fa-circle-xmark"></i>';

            toast.innerHTML = `${icon} <span>${msg}</span>`;
            wrapper.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ROLE SWITCHER LOGIC
        function changeActiveRole(role) {
            systemState.activeRole = role;
            
            // Adjust header welcome text
            let welcomeName = "Admin";
            let roleDisplay = "Super User";
            let shortName = "A";
            
            if (role === "Developer") {
                welcomeName = "Lead Developer";
                roleDisplay = "Lead Developer";
                shortName = "D";
            } else if (role === "UI/UX Designer") {
                welcomeName = "Lead Designer";
                roleDisplay = "UI Designer";
                shortName = "U";
            } else if (role === "Project Manager") {
                welcomeName = "Project Manager";
                roleDisplay = "Project Lead";
                shortName = "P";
            }

            welcomeHeading.innerText = `Welcome back, ${welcomeName}`;
            currentRoleTitle.innerText = role;
            
            // Update Avatar badges
            sidebarAvatar.innerText = shortName;
            headerAvatar.innerText = shortName;
            sidebarUser.innerText = welcomeName;
            sidebarRole.innerText = roleDisplay;

            // Animate card items
            const card = document.getElementById('tasks');
            card.style.transform = "scale(0.99)";
            card.style.opacity = "0.9";
            setTimeout(() => {
                card.style.transform = "scale(1)";
                card.style.opacity = "1";
            }, 100);

            // Re-render
            renderRoleTasks();
            displayToast(`Switched active context to ${role}`, "success");
        }

        // TASK RENDER & LOGIC
        function renderRoleTasks() {
            taskListContainer.innerHTML = '';
            
            // Filter tasks
            const filteredTasks = systemState.tasks.filter(t => t.role === systemState.activeRole);
            
            if (filteredTasks.length === 0) {
                taskListContainer.innerHTML = `
                    <div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--primary);"></i>
                        <p>No tasks allocated for this role yet.</p>
                    </div>
                `;
                updateProgress(0);
                return;
            }

            let completedCount = 0;

            filteredTasks.forEach(task => {
                if (task.completed) completedCount++;

                const taskDiv = document.createElement('div');
                taskDiv.className = `task-item ${task.completed ? 'completed' : ''}`;
                
                const priorityClass = `priority-${task.priority}`;

                taskDiv.innerHTML = `
                    <div class="task-left">
                        <div class="task-checkbox-container">
                            <div class="task-checkbox" onclick="toggleTaskState(${task.id})">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        </div>
                        <div class="task-details">
                            <h4>${escapeHTML(task.name)}</h4>
                            <p><i class="fa-regular fa-clock"></i> Deadline: ${escapeHTML(task.deadline)}</p>
                        </div>
                    </div>
                    <div>
                        <span class="priority-badge ${priorityClass}">${task.priority}</span>
                    </div>
                `;
                taskListContainer.appendChild(taskDiv);
            });

            // Calculate progress percentage
            const percentage = Math.round((completedCount / filteredTasks.length) * 100);
            updateProgress(percentage);
        }

        function updateProgress(percentage) {
            progressText.innerText = `${percentage}%`;
            progressBar.style.width = `${percentage}%`;
        }

        function toggleTaskState(id) {
            systemState.tasks = systemState.tasks.map(t => {
                if (t.id === id) {
                    return { ...t, completed: !t.completed };
                }
                return t;
            });
            renderRoleTasks();
            displayToast("Task status updated", "success");
        }

        function createNewTask(e) {
            e.preventDefault();
            const titleInput = document.getElementById('newTaskTitle');
            const roleInput = document.getElementById('newTaskRole');
            const priorityInput = document.getElementById('newTaskPriority');
            const deadlineInput = document.getElementById('newTaskDeadline');

            const title = titleInput.value.trim();
            const role = roleInput.value;
            const priority = priorityInput.value;
            const deadline = deadlineInput.value.trim() || "No deadline";

            const newTask = {
                id: Date.now(),
                name: title,
                role: role,
                priority: priority,
                deadline: deadline,
                completed: false
            };

            systemState.tasks.push(newTask);
            
            // Reset input title
            titleInput.value = '';
            deadlineInput.value = '';

            // If task was assigned to current role, render right away
            if (role === systemState.activeRole) {
                renderRoleTasks();
            } else {
                displayToast(`Task assigned successfully to ${role}`, "success");
            }
        }

        // 2FA TOGGLE & CODE INPUTS LOGIC
        function toggle2FASettings(isEnabled) {
            systemState.twoFactorEnabled = isEnabled;
            
            if (isEnabled) {
                faStatusTitle.innerText = "2FA Setting: Setup Pending";
                faStatusTitle.style.color = "var(--text-main)";
                faStatusIcon.style.color = "var(--primary)";
                faInteractiveContent.style.display = "block";
                displayToast("Please scan the QR code to finish setting up 2FA", "warning");
            } else {
                systemState.twoFactorVerified = false;
                faStatusTitle.innerText = "2FA is Currently Disabled";
                faStatusTitle.style.color = "var(--text-muted)";
                faStatusIcon.style.color = "var(--text-muted)";
                faInteractiveContent.style.display = "none";
                displayToast("Two-Factor Authentication deactivated", "danger");
            }
        }

        function focusNextOtp(currentInput, index) {
            // Keep numerical values only
            currentInput.value = currentInput.value.replace(/[^0-9]/g, '');

            if (currentInput.value && index < 6) {
                const nextInput = document.querySelectorAll('.otp-box')[index];
                if (nextInput) nextInput.focus();
            }
        }

        function verifyOtpCode() {
            const boxes = document.querySelectorAll('.otp-box');
            let code = "";
            boxes.forEach(b => code += b.value);

            if (code.length < 6) {
                displayToast("Please enter all 6 digits of the OTP code", "warning");
                return;
            }

            // Simulate code check
            if (code === "123456" || code.endsWith("2")) { // any code ending in 2 or 123456 works for test mock
                systemState.twoFactorVerified = true;
                faStatusTitle.innerText = "2FA is Enabled & Verified";
                faStatusTitle.style.color = "#10b981";
                faStatusIcon.style.color = "#10b981";
                
                displayToast("2FA verified successfully! Backup logs configured.", "success");
            } else {
                displayToast("Invalid verification OTP. Try code '123456' or any code ending in 2.", "danger");
                // Clear inputs
                boxes.forEach(b => b.value = '');
                boxes[0].focus();
            }
        }

        function regenerateBackupCodes() {
            // Generate mock codes
            systemState.backupCodes = Array.from({length: 4}, () => 
                Math.floor(1000 + Math.random() * 9000) + "-" + Math.floor(1000 + Math.random() * 9000)
            );
            renderBackupCodes();
            displayToast("Regenerated secure recovery codes.", "success");
        }

        function renderBackupCodes() {
            backupCodesGrid.innerHTML = '';
            systemState.backupCodes.forEach(code => {
                const pill = document.createElement('div');
                pill.className = "backup-code-pill";
                pill.innerText = code;
                backupCodesGrid.appendChild(pill);
            });
        }

        // LOGIN SESSIONS LOGIC
        function renderSessions() {
            sessionsTableBody.innerHTML = '';

            systemState.sessions.forEach(session => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-session-id', session.id);

                const isCurrent = session.status === 'Current';
                const revokeBtn = isCurrent ? 
                    `<button class="btn-outline" style="border:none; padding:0.4rem; color:var(--text-muted); cursor:not-allowed;" disabled>Active</button>` :
                    `<button class="btn btn-danger" style="padding:0.4rem 0.8rem; font-size:0.75rem; border-radius:6px;" onclick="revokeSession(${session.id})">Revoke</button>`;

                tr.innerHTML = `
                    <td>
                        <div style="font-weight:600; color:var(--text-primary);"><i class="fa-solid fa-laptop-code" style="margin-right: 0.5rem; color: var(--primary);"></i> ${escapeHTML(session.device)}</div>
                    </td>
                    <td>${escapeHTML(session.ip)}</td>
                    <td>${escapeHTML(session.location)}</td>
                    <td>${escapeHTML(session.lastActive)}</td>
                    <td>
                        <span class="${isCurrent ? 'badge-current' : 'badge badge-info'}" style="font-size:0.7rem;">${escapeHTML(session.status)}</span>
                    </td>
                    <td style="text-align: center;">${revokeBtn}</td>
                `;
                sessionsTableBody.appendChild(tr);
            });
        }

        function revokeSession(id) {
            const tr = document.querySelector(`tr[data-session-id="${id}"]`);
            if (tr) {
                tr.style.opacity = '0';
                tr.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    systemState.sessions = systemState.sessions.filter(s => s.id !== id);
                    renderSessions();
                    displayToast("Session revoked and user logout forced", "danger");
                }, 300);
            }
        }

        // HTML Escaper helper
        function escapeHTML(str) {
            return str.replace(/[&<>'"]/g, 
                tag => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#39;',
                    '"': '&quot;'
                }[tag] || tag)
            );
        }
    </script>
</body>
</html>
