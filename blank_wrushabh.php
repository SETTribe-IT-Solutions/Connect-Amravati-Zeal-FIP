<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMRAVATI CONNECT - Enterprise Workflow Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* ==========================================================================
           DESIGN SYSTEM & CSS VARIABLES
           ========================================================================== */
        :root {
            /* Primary Colors */
            --primary-blue: #0f62fe;
            --primary-hover: #0043ce;
            --success-green: #24a148;
            --warning-orange: #f1c21b;
            --danger-red: #da1e28;
            
            /* Neutral Palette (Light Mode) */
            --neutral-100: #f4f4f4;
            --neutral-200: #e0e0e0;
            --neutral-300: #c6c6c6;
            --neutral-700: #525252;
            --neutral-800: #393939;
            --neutral-900: #161616;
            
            /* Semantic Variables */
            --bg-color: #f4f7f9;
            --surface-color: #ffffff;
            --text-primary: #161616;
            --text-secondary: #525252;
            --border-color: #e0e0e0;
            
            /* Typography */
            --font-family: 'Inter', sans-serif;
            
            /* Layout Dimensions */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 70px;
            --header-height: 64px;
            
            /* Animation */
            --transition-speed: 0.3s;
        }

        /* Dark Mode Override */
        [data-theme="dark"] {
            --bg-color: #121212;
            --surface-color: #1e1e1e;
            --text-primary: #f4f4f4;
            --text-secondary: #a8a8a8;
            --border-color: #393939;
            --neutral-100: #2c2c2c;
            --neutral-800: #2a2a2a;
        }

        /* ==========================================================================
           GLOBAL RESETS
           ========================================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-family);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            display: flex;
            height: 100vh;
            overflow: hidden;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        /* ==========================================================================
           SIDEBAR COMPONENT
           ========================================================================== */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--surface-color);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            transition: width var(--transition-speed);
            z-index: 100;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-blue);
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px 0;
            list-style: none;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: background-color 0.2s;
            white-space: nowrap;
            overflow: hidden;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .menu-item:hover, .menu-item.active {
            background-color: var(--neutral-100);
            color: var(--primary-blue);
            border-right: 3px solid var(--primary-blue);
        }

        .menu-item i {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 15px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
        }

        /* ==========================================================================
           MAIN CONTENT & HEADER
           ========================================================================== */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            height: var(--header-height);
            background-color: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 90;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toggle-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-secondary);
            cursor: pointer;
            padding: 5px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background-color: var(--neutral-100);
            border-radius: 6px;
            padding: 8px 15px;
            width: 350px;
            border: 1px solid transparent;
            transition: border-color 0.2s;
        }

        .search-bar:focus-within {
            border-color: var(--primary-blue);
        }

        .search-bar input {
            border: none;
            background: none;
            outline: none;
            margin-left: 10px;
            width: 100%;
            color: var(--text-primary);
            font-size: 0.9rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .icon-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s;
        }

        .icon-btn:hover {
            color: var(--primary-blue);
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-red);
            color: white;
            font-size: 0.6rem;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 10px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding-left: 15px;
            border-left: 1px solid var(--border-color);
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* ==========================================================================
           PAGE CONTENT AREA
           ========================================================================== */
        .page-content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .breadcrumb {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .breadcrumb span.active {
            color: var(--primary-blue);
            font-weight: 500;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
            box-shadow: 0 2px 4px rgba(15, 98, 254, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }
        
        .btn-secondary {
            background-color: var(--surface-color);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--neutral-100);
        }

        /* ==========================================================================
           EXECUTIVE DASHBOARD COMPONENTS
           ========================================================================== */
        
        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: var(--surface-color);
            border-radius: 8px;
            padding: 24px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .kpi-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .kpi-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .kpi-trend {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .trend-up { color: var(--success-green); }
        .trend-down { color: var(--danger-red); }

        /* Charts & Activity Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 30px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }

        /* Activity Feed */
        .activity-feed {
            list-style: none;
            position: relative;
            padding-left: 5px;
        }

        .activity-item {
            display: flex;
            gap: 15px;
            margin-bottom: 24px;
            position: relative;
        }

        .activity-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 35px;
            bottom: -20px;
            width: 2px;
            background-color: var(--border-color);
        }

        .activity-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: white;
            z-index: 1;
        }

        .activity-content {
            flex: 1;
            padding-top: 5px;
        }

        .activity-text {
            font-size: 0.9rem;
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* ==========================================================================
           DATA TABLE SYSTEM
           ========================================================================== */
        .table-container {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th, .data-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        .data-table th {
            font-weight: 600;
            color: var(--text-secondary);
            background-color: var(--neutral-100);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            transition: background-color 0.2s;
        }

        .data-table tbody tr:hover {
            background-color: var(--neutral-100);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .status-completed { background-color: rgba(36, 161, 72, 0.1); color: var(--success-green); }
        .status-completed::before { background-color: var(--success-green); }
        
        .status-pending { background-color: rgba(241, 194, 27, 0.1); color: #b8910d; }
        .status-pending::before { background-color: #b8910d; }
        
        .status-progress { background-color: rgba(15, 98, 254, 0.1); color: var(--primary-blue); }
        .status-progress::before { background-color: var(--primary-blue); }

        .table-actions {
            display: flex;
            gap: 12px;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 1rem;
            transition: color 0.2s;
        }

        .action-btn:hover { color: var(--primary-blue); }

        /* ==========================================================================
           RESPONSIVE DESIGN
           ========================================================================== */
        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                height: 100%;
                transform: translateX(-100%);
                box-shadow: 5px 0 15px rgba(0,0,0,0.1);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .header-left .search-bar {
                display: none;
            }
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-layer-group" style="margin-right: 15px;"></i>
            <span class="logo-text">AMRAVATI CONNECT</span>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="menu-text">Dashboard</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-list-check"></i>
                <span class="menu-text">Task Management</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-comments"></i>
                <span class="menu-text">Communication Center</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-users"></i>
                <span class="menu-text">Users & Roles</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-sitemap"></i>
                <span class="menu-text">Departments & Projects</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-folder-open"></i>
                <span class="menu-text">Documents</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-chart-line"></i>
                <span class="menu-text">Reports & Analytics</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-bell"></i>
                <span class="menu-text">Notifications</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span class="menu-text">Audit Logs</span>
            </li>
            <li class="menu-item" style="margin-top: 20px;">
                <i class="fa-solid fa-gear"></i>
                <span class="menu-text">System Settings</span>
            </li>
            <li class="menu-item">
                <i class="fa-solid fa-circle-question"></i>
                <span class="menu-text">Help & Support</span>
            </li>
        </ul>
        <div class="sidebar-footer">
            <span class="logo-text">v2.4.1 Enterprise Edition</span>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <main class="main-content">
        
        <!-- HEADER -->
        <header class="header">
            <div class="header-left">
                <button class="toggle-btn" id="toggleSidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="search-bar">
                    <i class="fa-solid fa-search text-secondary"></i>
                    <input type="text" placeholder="Search across all modules...">
                </div>
            </div>
            
            <div class="header-right">
                <button class="icon-btn" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <button class="icon-btn" title="Messages">
                    <i class="fa-regular fa-comment-dots"></i>
                </button>
                <button class="icon-btn" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge">5</span>
                </button>
                <button class="icon-btn" title="Language">
                    <i class="fa-solid fa-globe"></i>
                </button>
                
                <div class="user-profile">
                    <div class="avatar">JS</div>
                    <div class="user-info">
                        <span class="user-name">John Smith</span>
                        <span class="user-role">Department Head</span>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem; color: var(--text-secondary);"></i>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT CONTAINER -->
        <div class="page-content">
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h1>Executive Dashboard</h1>
                    <div class="breadcrumb">
                        <span>Home</span> <i class="fa-solid fa-angle-right" style="font-size: 0.7rem; margin: 0 5px;"></i>
                        <span>Dashboards</span> <i class="fa-solid fa-angle-right" style="font-size: 0.7rem; margin: 0 5px;"></i>
                        <span class="active">Overview</span>
                    </div>
                </div>
                <div class="action-toolbar">
                    <button class="btn btn-secondary"><i class="fa-solid fa-calendar"></i> This Month</button>
                    <button class="btn btn-secondary"><i class="fa-solid fa-download"></i> Generate Report</button>
                    <button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Task</button>
                </div>
            </div>

            <!-- KPI Cards Section -->
            <div class="kpi-grid">
                <div class="card">
                    <div class="kpi-header">
                        <span>Total Active Users</span>
                        <div class="kpi-icon" style="color: var(--primary-blue); background-color: rgba(15, 98, 254, 0.1);">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="kpi-value">2,845</div>
                    <div class="kpi-trend trend-up">
                        <i class="fa-solid fa-arrow-trend-up"></i> +14% vs last month
                    </div>
                </div>
                
                <div class="card">
                    <div class="kpi-header">
                        <span>Tasks In Progress</span>
                        <div class="kpi-icon" style="color: var(--warning-orange); background-color: rgba(241, 194, 27, 0.1);">
                            <i class="fa-solid fa-spinner"></i>
                        </div>
                    </div>
                    <div class="kpi-value">842</div>
                    <div class="kpi-trend trend-down">
                        <i class="fa-solid fa-arrow-trend-down"></i> -3% vs last week
                    </div>
                </div>
                
                <div class="card">
                    <div class="kpi-header">
                        <span>Tasks Completed</span>
                        <div class="kpi-icon" style="color: var(--success-green); background-color: rgba(36, 161, 72, 0.1);">
                            <i class="fa-solid fa-check-double"></i>
                        </div>
                    </div>
                    <div class="kpi-value">12,490</div>
                    <div class="kpi-trend trend-up">
                        <i class="fa-solid fa-arrow-trend-up"></i> +28% vs last month
                    </div>
                </div>
                
                <div class="card">
                    <div class="kpi-header">
                        <span>Overdue Tasks</span>
                        <div class="kpi-icon" style="color: var(--danger-red); background-color: rgba(218, 30, 40, 0.1);">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                    <div class="kpi-value">34</div>
                    <div class="kpi-trend trend-up" style="color: var(--danger-red);">
                        <i class="fa-solid fa-arrow-trend-up"></i> +5 since yesterday
                    </div>
                </div>
            </div>

            <!-- Main Charts & Activity Feed -->
            <div class="charts-grid">
                
                <!-- Chart Container -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Productivity & Task Completion Trends</h3>
                        <button class="icon-btn"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                    </div>
                    <div class="chart-container">
                        <canvas id="productivityChart"></canvas>
                    </div>
                </div>

                <!-- Activity Feed -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent System Activity</h3>
                        <a href="#" style="font-size: 0.85rem; color: var(--primary-blue); text-decoration: none;">View All</a>
                    </div>
                    <ul class="activity-feed">
                        <li class="activity-item">
                            <div class="activity-icon" style="background-color: var(--success-green);"><i class="fa-solid fa-check"></i></div>
                            <div class="activity-content">
                                <p class="activity-text"><strong>Sarah Jenkins</strong> approved the <em>Q4 Budget Proposal</em></p>
                                <span class="activity-time">10 minutes ago</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon" style="background-color: var(--primary-blue);"><i class="fa-solid fa-file-arrow-up"></i></div>
                            <div class="activity-content">
                                <p class="activity-text"><strong>Michael Chang</strong> uploaded v2 of <em>Compliance Guidelines</em></p>
                                <span class="activity-time">45 minutes ago</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon" style="background-color: var(--warning-orange);"><i class="fa-solid fa-user-pen"></i></div>
                            <div class="activity-content">
                                <p class="activity-text"><strong>System Admin</strong> updated permissions for <em>Finance Role</em></p>
                                <span class="activity-time">2 hours ago</span>
                            </div>
                        </li>
                        <li class="activity-item">
                            <div class="activity-icon" style="background-color: var(--danger-red);"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="activity-content">
                                <p class="activity-text">Escalation triggered on <em>Server Maintenance</em> task.</p>
                                <span class="activity-time">Yesterday at 14:30</span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="card">
                <div class="card-header" style="margin-bottom: 20px;">
                    <h3 class="card-title" style="margin-bottom: 0;">Priority Task Pipeline</h3>
                    <div style="display: flex; gap: 15px;">
                        <div class="search-bar" style="width: 250px; background-color: var(--bg-color);">
                            <i class="fa-solid fa-search text-secondary"></i>
                            <input type="text" placeholder="Search tasks...">
                        </div>
                        <button class="btn btn-secondary"><i class="fa-solid fa-filter"></i></button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox"></th>
                                <th>Task ID</th>
                                <th>Task Description</th>
                                <th>Owner</th>
                                <th>Department</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><strong>#TSK-0891</strong></td>
                                <td>Annual Security Audit Compliance Review</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.7rem; background-color: #8a3ffc;">AM</div>
                                        Alice Morgan
                                    </div>
                                </td>
                                <td>IT Security</td>
                                <td>Oct 15, 2026</td>
                                <td><span class="status-badge status-progress">In Progress</span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="action-btn" title="View"><i class="fa-solid fa-eye"></i></button>
                                        <button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="action-btn" title="More"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><strong>#TSK-0892</strong></td>
                                <td>Q3 Departmental Financial Reconciliation</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.7rem; background-color: #08bdba;">RC</div>
                                        Robert Chen
                                    </div>
                                </td>
                                <td>Finance</td>
                                <td>Oct 10, 2026</td>
                                <td><span class="status-badge status-pending">Pending Review</span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="action-btn" title="View"><i class="fa-solid fa-eye"></i></button>
                                        <button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="action-btn" title="More"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><strong>#TSK-0885</strong></td>
                                <td>Onboard New Field Staff Software Setup</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.7rem; background-color: #ff832b;">DW</div>
                                        David Wilson
                                    </div>
                                </td>
                                <td>Human Resources</td>
                                <td>Oct 05, 2026</td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="action-btn" title="View"><i class="fa-solid fa-eye"></i></button>
                                        <button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="action-btn" title="More"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><strong>#TSK-0895</strong></td>
                                <td>Draft Government RFP Response Document</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div class="avatar" style="width: 24px; height: 24px; font-size: 0.7rem; background-color: #da1e28;">LJ</div>
                                        Laura Johnson
                                    </div>
                                </td>
                                <td>Legal & Procurement</td>
                                <td>Oct 18, 2026</td>
                                <td><span class="status-badge status-progress">In Progress</span></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="action-btn" title="View"><i class="fa-solid fa-eye"></i></button>
                                        <button class="action-btn" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="action-btn" title="More"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; color: var(--text-secondary); font-size: 0.85rem;">
                    <span>Showing 1 to 4 of 248 entries</span>
                    <div style="display: flex; gap: 5px;">
                        <button class="btn btn-secondary" style="padding: 5px 10px;">Previous</button>
                        <button class="btn btn-primary" style="padding: 5px 12px;">1</button>
                        <button class="btn btn-secondary" style="padding: 5px 12px;">2</button>
                        <button class="btn btn-secondary" style="padding: 5px 12px;">3</button>
                        <button class="btn btn-secondary" style="padding: 5px 10px;">Next</button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- INTERACTIVE SCRIPTS -->
    <script>
        // Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Responsive Sidebar on Mobile
        if(window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
        }

        // Dark Mode Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const root = document.documentElement;
        let isDarkMode = false;

        themeToggle.addEventListener('click', () => {
            isDarkMode = !isDarkMode;
            if (isDarkMode) {
                root.setAttribute('data-theme', 'dark');
                themeToggle.innerHTML = '<i class="fa-solid fa-sun"></i>';
                updateChartTheme(true);
            } else {
                root.removeAttribute('data-theme');
                themeToggle.innerHTML = '<i class="fa-solid fa-moon"></i>';
                updateChartTheme(false);
            }
        });

        // Initialize Productivity Chart (Chart.js)
        const ctx = document.getElementById('productivityChart').getContext('2d');
        
        // Setup Gradient for primary line
        const gradientBlue = ctx.createLinearGradient(0, 0, 0, 400);
        gradientBlue.addColorStop(0, 'rgba(15, 98, 254, 0.4)');
        gradientBlue.addColorStop(1, 'rgba(15, 98, 254, 0.0)');

        let myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [
                    {
                        label: 'Tasks Completed',
                        data: [120, 150, 140, 180, 160, 210, 190, 240, 230, 280],
                        borderColor: '#0f62fe',
                        backgroundColor: gradientBlue,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#0f62fe',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'New Tasks Assigned',
                        data: [140, 130, 160, 150, 190, 180, 220, 200, 250, 240],
                        borderColor: '#24a148',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        pointBackgroundColor: '#24a148',
                        pointRadius: 0,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            color: '#525252',
                            font: {
                                family: "'Inter', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(22, 22, 22, 0.9)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 13 },
                        padding: 12,
                        cornerRadius: 6
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#e0e0e0',
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#525252',
                            font: { family: "'Inter', sans-serif" }
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#525252',
                            font: { family: "'Inter', sans-serif" }
                        }
                    }
                }
            }
        });

        // Function to update chart colors dynamically when Dark Mode changes
        function updateChartTheme(isDark) {
            const textColor = isDark ? '#c6c6c6' : '#525252';
            const gridColor = isDark ? '#393939' : '#e0e0e0';
            
            myChart.options.plugins.legend.labels.color = textColor;
            myChart.options.scales.y.ticks.color = textColor;
            myChart.options.scales.x.ticks.color = textColor;
            myChart.options.scales.y.grid.color = gridColor;
            
            // Adjust tooltip theme
            myChart.options.plugins.tooltip.backgroundColor = isDark ? 'rgba(255, 255, 255, 0.9)' : 'rgba(22, 22, 22, 0.9)';
            myChart.options.plugins.tooltip.titleColor = isDark ? '#161616' : '#ffffff';
            myChart.options.plugins.tooltip.bodyColor = isDark ? '#161616' : '#ffffff';
            
            myChart.update();
        }
    </script>
</body>
</html>
