<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connect Amravati - Government Administration Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            /* Theme Color Palette - Premium Royal Government Theme */
            --primary: #1e3a8a;       /* Royal Blue */
            --primary-hover: #172554; /* Darker Navy */
            --primary-light: #eff6ff; /* Soft Blue */
            --primary-glow: rgba(30, 58, 138, 0.08);
            
            --secondary: #0284c7;     /* Sky Accent Blue */
            --accent: #f59e0b;        /* Gold/Orange highlight */
            
            /* Status Colors */
            --success: #10b981;       /* Emerald Success */
            --success-light: #ecfdf5;
            --warning: #f59e0b;       /* Amber Warning */
            --warning-light: #fffbeb;
            --danger: #ef4444;        /* Crimson Danger */
            --danger-light: #fef2f2;
            --info: #3b82f6;          /* Info Blue */
            --info-light: #eff6ff;
            
            /* Light Mode Variables */
            --bg-app: #f8fafc;
            --bg-sidebar: #0f172a;    /* Dark Sidebar for premium contrast */
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --sidebar-text: #94a3b8;
            --sidebar-active-text: #ffffff;
            --sidebar-active-bg: rgba(255, 255, 255, 0.08);
            --header-bg: #ffffff;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05);
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark Theme Variables */
        [data-theme="dark"] {
            --bg-app: #0b0f19;
            --bg-sidebar: #090d16;
            --bg-card: #111827;
            --border-color: #1f2937;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
            --text-muted: #6b7280;
            --sidebar-text: #6b7280;
            --sidebar-active-text: #ffffff;
            --sidebar-active-bg: rgba(255, 255, 255, 0.06);
            --header-bg: #111827;
            --primary-light: rgba(30, 58, 138, 0.2);
            --success-light: rgba(16, 185, 129, 0.1);
            --warning-light: rgba(245, 158, 11, 0.1);
            --danger-light: rgba(239, 68, 68, 0.1);
            --info-light: rgba(59, 130, 246, 0.1);
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-app);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: var(--transition);
        }

        /* Ambient Glow Blobs (Figma style) */
        .ambient-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(30, 58, 138, 0.06) 0%, rgba(2, 132, 199, 0.02) 70%);
            filter: blur(80px);
            z-index: -1;
            pointer-events: none;
            top: 10%;
            right: 5%;
        }

        /* Left Sidebar Styling */
        aside {
            width: 280px;
            background-color: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-color);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            transition: var(--transition);
        }

        /* Branding Section */
        .sidebar-brand {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }

        .brand-logo i {
            color: #ffffff;
            font-size: 1.25rem;
        }

        .brand-text h2 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 0.5px;
        }

        .brand-text p {
            font-size: 0.7rem;
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
            overflow-y: auto;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.8rem 1rem;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .nav-item i {
            font-size: 1.05rem;
            width: 20px;
            text-align: center;
            transition: var(--transition);
        }

        .nav-item:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.04);
        }

        .nav-item.active {
            color: var(--sidebar-active-text);
            background: linear-gradient(90deg, var(--primary) 0%, rgba(30, 58, 138, 0.6) 100%);
            font-weight: 600;
            box-shadow: var(--shadow-sm);
        }

        .nav-item.active i {
            color: var(--secondary);
        }

        /* Government Emblem Placement */
        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .gov-emblem {
            width: 32px;
            height: auto;
            opacity: 0.8;
            filter: brightness(0) invert(1);
        }

        .gov-info {
            font-size: 0.7rem;
            color: #64748b;
            line-height: 1.3;
        }

        /* Main Content Container */
        .wrapper {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            transition: var(--transition);
        }

        /* Top Header Navigation */
        header {
            height: 70px;
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 90;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        /* Search Block */
        .header-search {
            position: relative;
            width: 300px;
        }

        .header-search input {
            width: 100%;
            padding: 0.55rem 1rem 0.55rem 2.5rem;
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 0.85rem;
            color: var(--text-primary);
            outline: none;
            transition: var(--transition);
        }

        .header-search input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .header-search i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* Header Control Actions */
        .header-controls {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        /* Lang toggle switch */
        .lang-switch {
            display: flex;
            align-items: center;
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            padding: 2px;
            cursor: pointer;
            position: relative;
            width: 90px;
            height: 32px;
            user-select: none;
        }

        .lang-option {
            flex: 1;
            font-size: 0.75rem;
            font-weight: 700;
            text-align: center;
            z-index: 2;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .lang-option.active {
            color: #ffffff;
        }

        .lang-slider {
            position: absolute;
            width: 44px;
            height: 26px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50px;
            top: 2px;
            left: 2px;
            z-index: 1;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 5px rgba(30, 58, 138, 0.3);
        }

        .lang-switch.marathi .lang-slider {
            transform: translateX(42px);
        }

        /* Theme switch button */
        .theme-btn, .notify-btn {
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-primary);
            position: relative;
            transition: var(--transition);
        }

        .theme-btn:hover, .notify-btn:hover {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }

        /* Notification Badge count */
        .notify-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background-color: var(--danger);
            border: 2px solid var(--header-bg);
            border-radius: 50%;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Notifications Dropdown Panel */
        .notify-dropdown {
            position: absolute;
            top: 60px;
            right: 200px;
            width: 360px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 100;
            display: none;
            overflow: hidden;
            animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notify-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notify-header h4 {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .notify-header button {
            background: none;
            border: none;
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        .notify-list {
            max-height: 280px;
            overflow-y: auto;
        }

        .notify-item {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            gap: 0.85rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .notify-item:hover {
            background-color: var(--bg-app);
        }

        .notify-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notify-icon.emergency {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .notify-icon.task {
            background-color: var(--info-light);
            color: var(--info);
        }

        .notify-icon.announcement {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .notify-desc p {
            font-size: 0.8rem;
            color: var(--text-primary);
            line-height: 1.4;
        }

        .notify-desc span {
            font-size: 0.7rem;
            color: var(--text-muted);
            display: block;
            margin-top: 3px;
        }

        /* User Profile & Role Dropdown Selector */
        .user-dropdown {
            position: relative;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-profile:hover {
            border-color: var(--primary);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(30, 58, 138, 0.2);
        }

        .user-meta {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .user-role-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .user-role-label i {
            font-size: 0.65rem;
        }

        /* Profile Role Options Menu */
        .profile-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            width: 260px;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg);
            z-index: 100;
            display: none;
            overflow: hidden;
            animation: slideDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .menu-title {
            padding: 0.75rem 1rem;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-app);
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 1px solid var(--border-color);
        }

        .role-option:last-child {
            border-bottom: none;
        }

        .role-option:hover {
            background-color: var(--bg-app);
        }

        .role-option.selected {
            background-color: var(--primary-light);
        }

        .role-option.selected .role-title {
            color: var(--primary);
            font-weight: 600;
        }

        .role-icon-bg {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .role-details {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .role-title {
            font-size: 0.85rem;
            color: var(--text-primary);
        }

        .role-subtitle {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* Main Scrollable Content Area */
        main {
            padding: 2rem;
            flex: 1;
            overflow-y: auto;
            position: relative;
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: var(--radius-lg);
            padding: 2rem;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .welcome-text h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 400;
        }

        .welcome-date {
            background-color: rgba(255, 255, 255, 0.12);
            padding: 0.65rem 1.25rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            backdrop-filter: blur(4px);
        }

        /* Summary Stats Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.25rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .stat-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
        }

        .stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .stat-icon.total { background-color: var(--primary-light); color: var(--primary); }
        .stat-icon.pending { background-color: var(--warning-light); color: var(--warning); }
        .stat-icon.progress { background-color: var(--info-light); color: var(--info); }
        .stat-icon.completed { background-color: var(--success-light); color: var(--success); }
        .stat-icon.overdue { background-color: var(--danger-light); color: var(--danger); }

        .stat-value {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.85rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-trend {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 5px;
        }

        .stat-trend.up { color: var(--success); }
        .stat-trend.down { color: var(--danger); }
        .stat-trend.neutral { color: var(--text-muted); }

        /* Dashboard Grid Modules */
        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 3fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Base Card Structure */
        .dashboard-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .card-title-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-title-bar h3 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-title-bar h3 i {
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            gap: 0.5rem;
        }

        .card-action-btn {
            background: none;
            border: 1px solid var(--border-color);
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
        }

        .card-action-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background-color: var(--primary-light);
        }

        .card-action-btn.active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
        }

        /* Charts Height Containment */
        .pie-container {
            position: relative;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bar-container {
            position: relative;
            height: 250px;
        }

        /* Lower Row Grid Layout */
        .lower-grid {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 1.5rem;
        }

        /* Activity Timeline & Deadlines */
        .timeline {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            position: relative;
            padding-left: 1.25rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 5px;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background-color: var(--border-color);
        }

        .timeline-item {
            position: relative;
        }

        .timeline-marker {
            position: absolute;
            left: -24px;
            top: 4px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--bg-card);
            border: 3px solid var(--primary);
            z-index: 2;
        }

        .timeline-marker.completed { border-color: var(--success); }
        .timeline-marker.alert { border-color: var(--danger); }
        .timeline-marker.announcement { border-color: var(--warning); }

        .timeline-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .timeline-time {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .timeline-text {
            font-size: 0.85rem;
            color: var(--text-primary);
            font-weight: 500;
            line-height: 1.4;
        }

        .timeline-officer {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        /* Deadlines indicator list */
        .deadline-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .deadline-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem;
            background-color: var(--bg-app);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .deadline-item:hover {
            transform: translateX(4px);
        }

        .deadline-item.high { border-left-color: var(--danger); }
        .deadline-item.medium { border-left-color: var(--warning); }
        .deadline-item.low { border-left-color: var(--success); }

        .deadline-details h5 {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .deadline-details p {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .priority-badge {
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .priority-badge.high { background-color: var(--danger-light); color: var(--danger); }
        .priority-badge.medium { background-color: var(--warning-light); color: var(--warning); }
        .priority-badge.low { background-color: var(--success-light); color: var(--success); }

        /* Tables & Performance list */
        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 1rem;
        }

        .table-search {
            position: relative;
            width: 250px;
        }

        .table-search input {
            width: 100%;
            padding: 0.45rem 0.75rem 0.45rem 2rem;
            background-color: var(--bg-app);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.8rem;
            color: var(--text-primary);
            outline: none;
        }

        .table-search i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .responsive-table {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.85rem;
        }

        .analytics-table th {
            background-color: var(--bg-app);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            user-select: none;
        }

        .analytics-table th i {
            margin-left: 4px;
            font-size: 0.65rem;
        }

        .analytics-table td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .analytics-table tbody tr:last-child td {
            border-bottom: none;
        }

        .analytics-table tbody tr {
            transition: var(--transition);
        }

        .analytics-table tbody tr:hover {
            background-color: var(--primary-light);
        }

        .badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-success { background-color: var(--success-light); color: var(--success); }
        .badge-warning { background-color: var(--warning-light); color: var(--warning); }
        .badge-danger { background-color: var(--danger-light); color: var(--danger); }
        .badge-info { background-color: var(--info-light); color: var(--info); }

        /* Progress indicator bar */
        .progress-bar-container {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 120px;
        }

        .progress-track {
            flex: 1;
            height: 6px;
            background-color: var(--border-color);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 10px;
        }

        .progress-val {
            font-size: 0.75rem;
            font-weight: 600;
            min-width: 28px;
            text-align: right;
        }

        /* ---------------------------------
           Tabs Custom Forms & Contents
        ------------------------------------ */
        
        /* Task Allocation Form */
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border-color);
            background-color: var(--bg-app);
            color: var(--text-primary);
            border-radius: var(--radius-sm);
            outline: none;
            font-family: inherit;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: var(--radius-sm);
            border: 1px solid transparent;
            cursor: pointer;
            font-family: inherit;
            transition: var(--transition);
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
        }

        .btn-secondary {
            background-color: transparent;
            border-color: var(--border-color);
            color: var(--text-secondary);
        }

        .btn-secondary:hover {
            background-color: var(--bg-app);
            border-color: var(--text-muted);
        }

        /* Task Tracking Cards list */
        .tracking-filters {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .tracking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.25rem;
        }

        .task-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .task-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
        }

        .task-card-id {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .task-card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.4;
        }

        .task-card-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .task-card-footer {
            border-top: 1px solid var(--border-color);
            padding-top: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .task-card-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .task-card-label {
            font-size: 0.65rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .task-card-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        /* Virtual Wall of Fame - Appreciation */
        .wall-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .wall-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .wall-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent);
        }

        .wall-ribbon {
            position: absolute;
            top: 12px;
            right: 12px;
            color: var(--accent);
            font-size: 1.5rem;
        }

        .wall-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 auto 1rem;
            border: 3px solid var(--bg-app);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .wall-name {
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 2px;
        }

        .wall-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
        }

        .wall-quote {
            font-size: 0.8rem;
            color: var(--text-secondary);
            font-style: italic;
            line-height: 1.5;
            background-color: var(--bg-app);
            padding: 0.75rem;
            border-radius: var(--radius-sm);
        }

        /* Dynamic Toasts Alert System */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            z-index: 1000;
        }

        .toast {
            min-width: 320px;
            max-width: 400px;
            background-color: var(--bg-card);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-sm);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            padding: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            transform: translateX(120%);
            animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transition: var(--transition);
        }

        @keyframes slideIn {
            to { transform: translateX(0); }
        }

        .toast.success { border-left-color: var(--success); }
        .toast.warning { border-left-color: var(--warning); }
        .toast.danger { border-left-color: var(--danger); }
        .toast.info { border-left-color: var(--info); }

        .toast-icon-wrapper {
            font-size: 1.15rem;
            margin-top: 2px;
        }
        .toast.success .toast-icon-wrapper { color: var(--success); }
        .toast.warning .toast-icon-wrapper { color: var(--warning); }
        .toast.danger .toast-icon-wrapper { color: var(--danger); }
        .toast.info .toast-icon-wrapper { color: var(--info); }

        .toast-body h5 {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .toast-body p {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .toast-close-btn {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 0.95rem;
        }

        .toast-close-btn:hover {
            color: var(--text-primary);
        }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .lower-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            aside {
                transform: translateX(-100%);
            }
            aside.open {
                transform: translateX(0);
            }
            .wrapper {
                margin-left: 0;
            }
            header {
                padding: 0 1rem;
            }
            .hamburger-menu {
                display: flex !important;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .header-search {
                display: none;
            }
            main {
                padding: 1rem;
            }
            .welcome-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        /* Hamburger button for mobile menu toggling */
        .hamburger-menu {
            display: none;
            background: none;
            border: 1px solid var(--border-color);
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-primary);
        }
    </style>
</head>
<body data-theme="light">

    <!-- Ambient background decorations -->
    <div class="ambient-glow"></div>

    <!-- Sidebar Navigation -->
    <aside id="sidebarPanel">
        <div class="sidebar-brand">
            <div class="brand-logo">
                <i class="fa-solid fa-dharmachakra"></i>
            </div>
            <div class="brand-text">
                <h2 data-translate="brand_title">Connect Amravati</h2>
                <p data-translate="brand_subtitle">District Administration</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a class="nav-item active" data-tab="dashboard">
                <i class="fa-solid fa-chart-pie"></i>
                <span data-translate="nav_dashboard">Dashboard</span>
            </a>
            <a class="nav-item" data-tab="task-allocation">
                <i class="fa-solid fa-file-signature"></i>
                <span data-translate="nav_allocation">Task Allocation</span>
            </a>
            <a class="nav-item" data-tab="task-tracking">
                <i class="fa-solid fa-list-check"></i>
                <span data-translate="nav_tracking">Task Tracking</span>
            </a>
            <a class="nav-item" data-tab="communication">
                <i class="fa-solid fa-comments"></i>
                <span data-translate="nav_communication">Communication</span>
            </a>
            <a class="nav-item" data-tab="announcements">
                <i class="fa-solid fa-bullhorn"></i>
                <span data-translate="nav_announcements">Announcements</span>
            </a>
            <a class="nav-item" data-tab="appreciation">
                <i class="fa-solid fa-award"></i>
                <span data-translate="nav_appreciation">Appreciation</span>
            </a>
            <a class="nav-item" data-tab="reports">
                <i class="fa-solid fa-square-poll-vertical"></i>
                <span data-translate="nav_reports">Reports & Analytics</span>
            </a>
            <a class="nav-item" data-tab="users">
                <i class="fa-solid fa-users-gear"></i>
                <span data-translate="nav_users">User Management</span>
            </a>
            <a class="nav-item" data-tab="settings">
                <i class="fa-solid fa-sliders"></i>
                <span data-translate="nav_settings">Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/Emblem_of_India.svg" alt="India Emblem" class="gov-emblem">
            <div class="gov-info">
                <strong data-translate="gov_tag">Gov. of Maharashtra</strong>
                <p data-translate="gov_district">Amravati Division</p>
            </div>
        </div>
    </aside>

    <!-- Main Application Wrapper -->
    <div class="wrapper">
        
        <!-- Header Section -->
        <header>
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="hamburger-menu" id="menuToggleBtn" onclick="toggleSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="header-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="globalSearchInput" placeholder="Search tasks, officers, circulars..." data-translate-placeholder="search_placeholder">
                </div>
            </div>

            <div class="header-controls">
                
                <!-- Marathi/English language switch -->
                <div class="lang-switch" id="langSwitch" onclick="toggleLanguage()">
                    <span class="lang-slider"></span>
                    <span class="lang-option active">EN</span>
                    <span class="lang-option">मरा</span>
                </div>

                <!-- Theme Toggle Button -->
                <button class="theme-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon" id="themeBtnIcon"></i>
                </button>

                <!-- Notifications Panel & bell -->
                <div style="position: relative;">
                    <button class="notify-btn" onclick="toggleNotifications()" title="Alerts & Notifications">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notify-badge">3</span>
                    </button>
                    
                    <div class="notify-dropdown" id="notifyDropdown">
                        <div class="notify-header">
                            <h4 data-translate="notify_header">District Notifications</h4>
                            <button onclick="clearNotifications()" data-translate="clear_all">Clear All</button>
                        </div>
                        <div class="notify-list" id="notifyList">
                            <div class="notify-item">
                                <div class="notify-icon emergency">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div class="notify-desc">
                                    <p><strong>Emergency Alert:</strong> High rainfall warning in Chikhaldara division. Relief camp status report requested.</p>
                                    <span>2 mins ago</span>
                                </div>
                            </div>
                            <div class="notify-item">
                                <div class="notify-icon task">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <div class="notify-desc">
                                    <p><strong>Overdue Alert:</strong> Revenue collection audit file for Chandur Bazar is pending since 15-Jun.</p>
                                    <span>2 hours ago</span>
                                </div>
                            </div>
                            <div class="notify-item">
                                <div class="notify-icon announcement">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div class="notify-desc">
                                    <p><strong>Announcement:</strong> Hon. Chief Minister virtual review meeting scheduled on 20-Jun at 11:00 AM.</p>
                                    <span>1 day ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile with Interactive Role Selector Dropdown -->
                <div class="user-dropdown">
                    <div class="user-profile" onclick="toggleProfileMenu()">
                        <div class="user-avatar" id="headerProfileAvatar">SK</div>
                        <div class="user-meta">
                            <span class="user-name" id="headerProfileName">Saurabh Katiyar (IAS)</span>
                            <span class="user-role-label">
                                <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                                <span id="headerProfileRole">District Collector</span>
                                <i class="fa-solid fa-chevron-down" style="font-size: 0.6rem; color: var(--text-muted);"></i>
                            </span>
                        </div>
                    </div>

                    <!-- Role Dropdown Selector -->
                    <div class="profile-menu" id="profileMenu">
                        <div class="menu-title" data-translate="switch_role">Select Login Officer Role</div>
                        
                        <div class="role-option selected" onclick="switchOfficerRole('collector')" id="roleOptionCollector">
                            <div class="role-icon-bg" style="background-color: var(--primary);">SK</div>
                            <div class="role-details">
                                <span class="role-title">Saurabh Katiyar, IAS</span>
                                <span class="role-subtitle" data-translate="role_collector">District Collector</span>
                            </div>
                        </div>

                        <div class="role-option" onclick="switchOfficerRole('sdo')" id="roleOptionSdo">
                            <div class="role-icon-bg" style="background-color: var(--secondary);">SS</div>
                            <div class="role-details">
                                <span class="role-title">Shradha Shinde</span>
                                <span class="role-subtitle" data-translate="role_sdo">SDO, Achalpur Division</span>
                            </div>
                        </div>

                        <div class="role-option" onclick="switchOfficerRole('tehsildar')" id="roleOptionTehsildar">
                            <div class="role-icon-bg" style="background-color: var(--accent);">AP</div>
                            <div class="role-details">
                                <span class="role-title">Amit Patil</span>
                                <span class="role-subtitle" data-translate="role_tehsildar">Tehsildar, Morshi</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Workspace Area -->
        <main>
            
            <!-- TAB 1: DASHBOARD VIEW -->
            <div id="dashboard" class="tab-content active">
                
                <!-- Welcome Section Banner -->
                <div class="welcome-card">
                    <div class="welcome-text">
                        <h1>
                            <span data-translate="welcome_title">Welcome back, </span>
                            <span id="welcomeOfficerName">Saurabh Katiyar (IAS)</span>
                        </h1>
                        <p><span data-translate="welcome_desc">Logged in as: </span><strong><span id="welcomeOfficerRole">District Collector</span></strong>. <span data-translate="welcome_action_desc">Here is the current administrative overview for Amravati District.</span></p>
                    </div>
                    <div class="welcome-date">
                        <i class="fa-solid fa-calendar-days"></i>
                        <span id="liveClockDate">Wednesday, 17 June 2026</span>
                    </div>
                </div>

                <!-- Stats Cards Row -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title" data-translate="card_total">Total Tasks</span>
                            <div class="stat-icon total"><i class="fa-solid fa-briefcase"></i></div>
                        </div>
                        <div>
                            <div class="stat-value" id="statTotalTasks">1,248</div>
                            <div class="stat-trend up">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                                <span>+4.2%</span>
                                <span style="color: var(--text-muted);" data-translate="from_last_week">from last week</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title" data-translate="card_pending">Pending Reviews</span>
                            <div class="stat-icon pending"><i class="fa-solid fa-clock-rotate-left"></i></div>
                        </div>
                        <div>
                            <div class="stat-value" id="statPendingTasks">312</div>
                            <div class="stat-trend down">
                                <i class="fa-solid fa-arrow-trend-down"></i>
                                <span>-2.1%</span>
                                <span style="color: var(--text-muted);" data-translate="resolved_today">resolved today</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title" data-translate="card_progress">In Progress</span>
                            <div class="stat-icon progress"><i class="fa-solid fa-spinner"></i></div>
                        </div>
                        <div>
                            <div class="stat-value" id="statInProgressTasks">520</div>
                            <div class="stat-trend up">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                                <span>+1.5%</span>
                                <span style="color: var(--text-muted);" data-translate="under_execution">under execution</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title" data-translate="card_completed">Completed</span>
                            <div class="stat-icon completed"><i class="fa-solid fa-circle-check"></i></div>
                        </div>
                        <div>
                            <div class="stat-value" id="statCompletedTasks">384</div>
                            <div class="stat-trend up">
                                <i class="fa-solid fa-arrow-trend-up"></i>
                                <span>+8.6%</span>
                                <span style="color: var(--text-muted);" data-translate="approved_reports">approved reports</span>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title" data-translate="card_overdue">Overdue Tasks</span>
                            <div class="stat-icon overdue"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        </div>
                        <div>
                            <div class="stat-value" style="color: var(--danger);" id="statOverdueTasks">32</div>
                            <div class="stat-trend down" style="color: var(--danger);">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>Critical</span>
                                <span style="color: var(--text-muted);" data-translate="requires_attention">requires action</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section Layout Grid -->
                <div class="chart-grid">
                    
                    <!-- Pie Chart: Status Distribution -->
                    <div class="dashboard-card">
                        <div class="card-title-bar">
                            <h3>
                                <i class="fa-solid fa-chart-pie"></i>
                                <span data-translate="chart_title_status">Task Status Distribution</span>
                            </h3>
                            <div class="card-actions">
                                <button class="card-action-btn active" data-translate="btn_live">Live</button>
                            </div>
                        </div>
                        <div class="pie-container">
                            <canvas id="taskStatusChart"></canvas>
                        </div>
                    </div>

                    <!-- Bar Chart: Workload by Region/Tehsil -->
                    <div class="dashboard-card">
                        <div class="card-title-bar">
                            <h3>
                                <i class="fa-solid fa-chart-column"></i>
                                <span data-translate="chart_title_load">District Task Load by Division / Tehsil</span>
                            </h3>
                            <div class="card-actions">
                                <button class="card-action-btn active" id="barChartToggleWeekly" data-translate="btn_weekly">Weekly</button>
                                <button class="card-action-btn" id="barChartToggleMonthly" data-translate="btn_monthly">Monthly</button>
                            </div>
                        </div>
                        <div class="bar-container">
                            <canvas id="taskLoadChart"></canvas>
                        </div>
                    </div>

                </div>

                <!-- Timeline and Employee Performance table grid layout -->
                <div class="lower-grid">
                    
                    <!-- Performance Analytics table -->
                    <div class="dashboard-card">
                        <div class="card-title-bar">
                            <h3>
                                <i class="fa-solid fa-users"></i>
                                <span data-translate="performance_title">Tehsildar Performance & Tracking</span>
                            </h3>
                            <div class="card-actions">
                                <button class="card-action-btn" onclick="exportPerformanceData()" data-translate="btn_export"><i class="fa-solid fa-file-excel"></i> Export</button>
                            </div>
                        </div>
                        
                        <div class="table-controls">
                            <div class="table-search">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" id="performanceSearchInput" placeholder="Search by name, region..." onkeyup="filterPerformanceTable()" data-translate-placeholder="search_officer_placeholder">
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">
                                <span data-translate="showing_records">Showing 7 records</span>
                            </span>
                        </div>

                        <div class="responsive-table">
                            <table class="analytics-table" id="performanceTable">
                                <thead>
                                    <tr>
                                        <th onclick="sortTable(0)" data-translate="th_officer">Officer Name <i class="fa-solid fa-sort"></i></th>
                                        <th onclick="sortTable(1)" data-translate="th_tehsil">Tehsil <i class="fa-solid fa-sort"></i></th>
                                        <th onclick="sortTable(2)" data-translate="th_assigned" style="text-align: center;">Assigned <i class="fa-solid fa-sort"></i></th>
                                        <th onclick="sortTable(3)" data-translate="th_completed" style="text-align: center;">Completed <i class="fa-solid fa-sort"></i></th>
                                        <th onclick="sortTable(4)" data-translate="th_rate">Completion Rate <i class="fa-solid fa-sort"></i></th>
                                        <th onclick="sortTable(5)" data-translate="th_status">Status <i class="fa-solid fa-sort"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="performanceTableBody">
                                    <tr>
                                        <td><strong>Shri. R. K. Solanki</strong></td>
                                        <td>Amravati Town</td>
                                        <td style="text-align: center;">185</td>
                                        <td style="text-align: center;">162</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 87%;"></div>
                                                </div>
                                                <span class="progress-val">87%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Excellent</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shri. Milind Gavhale</strong></td>
                                        <td>Achalpur</td>
                                        <td style="text-align: center;">142</td>
                                        <td style="text-align: center;">121</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 85%;"></div>
                                                </div>
                                                <span class="progress-val">85%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Excellent</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Smt. Pallavi Deshmukh</strong></td>
                                        <td>Morshi</td>
                                        <td style="text-align: center;">115</td>
                                        <td style="text-align: center;">90</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 78%;"></div>
                                                </div>
                                                <span class="progress-val">78%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-info"><i class="fa-solid fa-circle-info"></i> Good</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shri. Sanjay Patil</strong></td>
                                        <td>Chandur Bazar</td>
                                        <td style="text-align: center;">98</td>
                                        <td style="text-align: center;">68</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 69%;"></div>
                                                </div>
                                                <span class="progress-val">69%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-warning"><i class="fa-solid fa-circle-exclamation"></i> Average</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shri. Vijay Wankhede</strong></td>
                                        <td>Warud</td>
                                        <td style="text-align: center;">130</td>
                                        <td style="text-align: center;">102</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 78%;"></div>
                                                </div>
                                                <span class="progress-val">78%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-info"><i class="fa-solid fa-circle-info"></i> Good</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Smt. Sneha Kale</strong></td>
                                        <td>Daryapur</td>
                                        <td style="text-align: center;">86</td>
                                        <td style="text-align: center;">54</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 62%;"></div>
                                                </div>
                                                <span class="progress-val">62%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Critical</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shri. Ganesh Shinde</strong></td>
                                        <td>Chikhaldara</td>
                                        <td style="text-align: center;">72</td>
                                        <td style="text-align: center;">60</td>
                                        <td>
                                            <div class="progress-bar-container">
                                                <div class="progress-track">
                                                    <div class="progress-fill" style="width: 83%;"></div>
                                                </div>
                                                <span class="progress-val">83%</span>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Excellent</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Recent Activity & Deadlines -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        
                        <!-- Upcoming Deadlines -->
                        <div class="dashboard-card">
                            <div class="card-title-bar">
                                <h3>
                                    <i class="fa-solid fa-calendar-day"></i>
                                    <span data-translate="upcoming_deadlines">Upcoming Critical Deadlines</span>
                                </h3>
                            </div>
                            <div class="deadline-list">
                                <div class="deadline-item high">
                                    <div class="deadline-details">
                                        <h5 data-translate="dl_drought">Drought Mitigation Budget Draft</h5>
                                        <p><span data-translate="assigned_to">Assigned to:</span> Revenue Dept &bull; <strong>18-Jun</strong></p>
                                    </div>
                                    <span class="priority-badge high" data-translate="prio_high">High</span>
                                </div>
                                <div class="deadline-item medium">
                                    <div class="deadline-details">
                                        <h5 data-translate="dl_flood">Monsoon Flood Preparedness Audit</h5>
                                        <p><span data-translate="assigned_to">Assigned to:</span> Chikhaldara Tehsil &bull; <strong>20-Jun</strong></p>
                                    </div>
                                    <span class="priority-badge medium" data-translate="prio_med">Medium</span>
                                </div>
                                <div class="deadline-item low">
                                    <div class="deadline-details">
                                        <h5 data-translate="dl_sanitation">National Sanitation Week Report</h5>
                                        <p><span data-translate="assigned_to">Assigned to:</span> All Municipal Councils &bull; <strong>25-Jun</strong></p>
                                    </div>
                                    <span class="priority-badge low" data-translate="prio_low">Low</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity Timeline -->
                        <div class="dashboard-card">
                            <div class="card-title-bar">
                                <h3>
                                    <i class="fa-solid fa-clock"></i>
                                    <span data-translate="recent_activity">Recent Activity Log</span>
                                </h3>
                            </div>
                            
                            <div class="timeline" id="recentActivityTimeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker completed"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-time">Today, 2:40 PM</span>
                                        <span class="timeline-text">Task #1024 (Road Repair Audit) marked as <strong>Completed</strong> by Tehsildar, Morshi.</span>
                                        <span class="timeline-officer">Reviewer: SDO Achalpur</span>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-time">Today, 10:15 AM</span>
                                        <span class="timeline-text">New Task assigned: <strong>Adivasi Basti Drinking Water Project Review</strong>.</span>
                                        <span class="timeline-officer">Assigned by: District Collector</span>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker announcement"></div>
                                    <div class="timeline-content">
                                        <span class="timeline-time">Yesterday</span>
                                        <span class="timeline-text">Circular issued: <strong>Pre-monsoon crop insurance survey guidelines</strong>.</span>
                                        <span class="timeline-officer">Issued by: Agri-Department, Amravati HQ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- TAB 2: TASK ALLOCATION FORM -->
            <div id="task-allocation" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3>
                            <i class="fa-solid fa-file-signature"></i>
                            <span data-translate="nav_allocation">Task Allocation Form</span>
                        </h3>
                    </div>
                    
                    <div class="form-grid">
                        <form id="taskAllocationForm" onsubmit="handleTaskAllocation(event)">
                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="taskTitle" data-translate="form_task_title">Task Subject / Title</label>
                                    <input type="text" id="taskTitle" class="form-control" placeholder="e.g., Dam Silting Clearance Audit" required>
                                </div>
                                <div class="form-group">
                                    <label for="taskCategory" data-translate="form_category">Task Classification Category</label>
                                    <select id="taskCategory" class="form-control" required>
                                        <option value="Revenue & Land" data-translate="opt_rev">Revenue & Land</option>
                                        <option value="Disaster Relief" data-translate="opt_disaster">Disaster & Relief</option>
                                        <option value="Rural Development" data-translate="opt_rural">Rural Development</option>
                                        <option value="Public Grievances" data-translate="opt_grievance">Public Grievance Resolution</option>
                                        <option value="Infrastructure" data-translate="opt_infra">Infrastructure & Roads</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="taskTehsil" data-translate="form_tehsil">Target Sub-division / Tehsil</label>
                                    <select id="taskTehsil" class="form-control" onchange="populateAssignees()" required>
                                        <option value="Amravati Town">Amravati Town</option>
                                        <option value="Achalpur">Achalpur</option>
                                        <option value="Morshi">Morshi</option>
                                        <option value="Chandur Bazar">Chandur Bazar</option>
                                        <option value="Warud">Warud</option>
                                        <option value="Daryapur">Daryapur</option>
                                        <option value="Chikhaldara">Chikhaldara</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="taskAssignee" data-translate="form_assignee">Assigned Executive Officer</label>
                                    <select id="taskAssignee" class="form-control" required>
                                        <!-- Populated dynamically -->
                                    </select>
                                </div>
                            </div>

                            <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label for="taskDeadline" data-translate="form_deadline">Completion Deadline Date</label>
                                    <input type="date" id="taskDeadline" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="taskPriority" data-translate="form_priority">Execution Priority Indicator</label>
                                    <select id="taskPriority" class="form-control" required>
                                        <option value="High" data-translate="prio_high">High</option>
                                        <option value="Medium" data-translate="prio_med">Medium</option>
                                        <option value="Low" data-translate="prio_low">Low</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="taskDescription" data-translate="form_description">Task Detailed Scope & Instructions</label>
                                <textarea id="taskDescription" class="form-control" placeholder="Provide details, rules, documents needed for compliance..." required></textarea>
                            </div>

                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span data-translate="btn_assign_task">Assign Task</span>
                                </button>
                                <button type="reset" class="btn btn-secondary" data-translate="btn_reset">Reset</button>
                            </div>
                        </form>

                        <!-- Information helper card -->
                        <div style="background-color: var(--primary-light); padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 1rem;">
                            <h4 style="color: var(--primary); font-family: 'Plus Jakarta Sans', sans-serif;"><i class="fa-solid fa-circle-info"></i> <span data-translate="info_allocation_title">Allocation Guidelines</span></h4>
                            <p style="font-size: 0.8rem; line-height: 1.6; color: var(--text-secondary);" data-translate="info_allocation_desc">
                                All allocated tasks are officially logged into the Connect Amravati framework. Notifications are dispatched instantly to target Tehsildars or field officers. Delayed task outcomes are auto-flagged as overdue.
                            </p>
                            <div style="margin-top: auto;">
                                <div style="display: flex; align-items: center; gap: 10px; font-size: 0.75rem; margin-bottom: 5px;">
                                    <i class="fa-solid fa-circle" style="color: var(--danger); font-size: 0.6rem;"></i>
                                    <span><strong>High Priority</strong> &bull; Response within 24 Hours</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px; font-size: 0.75rem;">
                                    <i class="fa-solid fa-circle" style="color: var(--warning); font-size: 0.6rem;"></i>
                                    <span><strong>Medium Priority</strong> &bull; Response within 3 Days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: TASK TRACKING -->
            <div id="task-tracking" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3>
                            <i class="fa-solid fa-list-check"></i>
                            <span data-translate="nav_tracking">Active Task Tracking Portal</span>
                        </h3>
                    </div>

                    <div class="tracking-filters">
                        <button class="btn btn-primary" onclick="filterTasks('All')" id="filterBtnAll" data-translate="filter_all">All Tasks</button>
                        <button class="btn btn-secondary" onclick="filterTasks('Pending')" id="filterBtnPending" data-translate="card_pending">Pending</button>
                        <button class="btn btn-secondary" onclick="filterTasks('In Progress')" id="filterBtnProgress" data-translate="card_progress">In Progress</button>
                        <button class="btn btn-secondary" onclick="filterTasks('Completed')" id="filterBtnCompleted" data-translate="card_completed">Completed</button>
                        <button class="btn btn-secondary" onclick="filterTasks('Overdue')" id="filterBtnOverdue" data-translate="card_overdue">Overdue</button>
                    </div>

                    <div class="tracking-grid" id="trackingGridContainer">
                        <!-- Populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- TAB 4: COMMUNICATION -->
            <div id="communication" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-comments"></i> <span data-translate="nav_communication">District Officer Communication</span></h3>
                    </div>
                    <div style="text-align: center; padding: 4rem 2rem; color: var(--text-secondary);">
                        <i class="fa-solid fa-network-wired" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                        <h4 style="margin-bottom: 8px;" data-translate="comm_title">Secured Inter-Office Chat & Memo Exchange</h4>
                        <p style="font-size: 0.85rem; max-width: 500px; margin: 0 auto;" data-translate="comm_desc">Encrypted transmission network between Collector, SDO, and Tehsildars for swift coordination. Toggle role to simulate communications.</p>
                        <button class="btn btn-primary" style="margin-top: 1.5rem;" onclick="triggerToast('Secure network online. All encryption protocols verified.', 'success', 'System')" data-translate="comm_connect">Connect Secure Terminal</button>
                    </div>
                </div>
            </div>

            <!-- TAB 5: ANNOUNCEMENTS -->
            <div id="announcements" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-bullhorn"></i> <span data-translate="nav_announcements">District Announcements & Circulars</span></h3>
                    </div>
                    <div style="background-color: var(--warning-light); border: 1px solid var(--warning); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fa-solid fa-circle-exclamation" style="color: var(--warning); font-size: 1.25rem;"></i>
                        <p style="font-size: 0.85rem;" data-translate="announcement_banner">Only Collector or authorized officers are allowed to broadcast general district-wide administrative circulars.</p>
                    </div>
                    
                    <div class="deadline-list">
                        <div class="deadline-item medium" style="border-left-width: 4px; padding: 1.25rem;">
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700;">Circular #242/2026: Disaster Management preparedness meetings in June</h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Posted: 16-Jun-2026 &bull; Category: Disaster Management Division</p>
                            </div>
                        </div>
                        <div class="deadline-item low" style="border-left-width: 4px; padding: 1.25rem;">
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700;">Revenue Target reviews for First Quarter: Financial Year 2026-27</h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Posted: 14-Jun-2026 &bull; Category: Revenue Accounts Division</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 6: APPRECIATION -->
            <div id="appreciation" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3>
                            <i class="fa-solid fa-award"></i>
                            <span data-translate="appreciation_title">Wall of Administrative Excellence</span>
                        </h3>
                    </div>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 2rem;" data-translate="appreciation_desc">Recognizing officers who achieved 100% grievance resolution and fastest average task response times.</p>
                    
                    <div class="wall-grid">
                        <div class="wall-card">
                            <i class="fa-solid fa-ribbon wall-ribbon"></i>
                            <div class="wall-avatar">MG</div>
                            <div class="wall-name">Shri Milind Gavhale</div>
                            <div class="wall-role">Tehsildar - Achalpur</div>
                            <div class="wall-quote">"Fastest grievance response time in the revenue division for 3 consecutive months."</div>
                        </div>
                        <div class="wall-card">
                            <i class="fa-solid fa-ribbon wall-ribbon"></i>
                            <div class="wall-avatar">RS</div>
                            <div class="wall-name">Shri R. K. Solanki</div>
                            <div class="wall-role">Tehsildar - Amravati Town</div>
                            <div class="wall-quote">"Successfully resolved 95% of e-filing public grievances under state portal guidelines."</div>
                        </div>
                        <div class="wall-card">
                            <i class="fa-solid fa-ribbon wall-ribbon" style="color: silver;"></i>
                            <div class="wall-avatar">GS</div>
                            <div class="wall-name">Shri Ganesh Shinde</div>
                            <div class="wall-role">Tehsildar - Chikhaldara</div>
                            <div class="wall-quote">"Outstanding coordination for landslide response relief setup during heavy storm."</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 7: REPORTS -->
            <div id="reports" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-square-poll-vertical"></i> <span data-translate="reports_header">Administrative Reports Center</span></h3>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-top: 1rem;">
                        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
                            <i class="fa-solid fa-file-pdf" style="font-size: 2.5rem; color: #ef4444; margin-bottom: 0.75rem;"></i>
                            <h5 style="margin-bottom: 4px;">Monthly Grievance Audit</h5>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">Full details of unresolved vs resolved district complains.</p>
                            <button class="btn btn-secondary" onclick="triggerToast('PDF generated.', 'info')" style="font-size: 0.75rem; padding: 0.45rem 1rem;"><i class="fa-solid fa-download"></i> Get Report</button>
                        </div>
                        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
                            <i class="fa-solid fa-file-excel" style="font-size: 2.5rem; color: #10b981; margin-bottom: 0.75rem;"></i>
                            <h5 style="margin-bottom: 4px;">Tehsil Workload Metrics</h5>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">Comparative analysis of task delays across 7 regions.</p>
                            <button class="btn btn-secondary" onclick="triggerToast('Excel sheet exported.', 'success')" style="font-size: 0.75rem; padding: 0.45rem 1rem;"><i class="fa-solid fa-download"></i> Get Report</button>
                        </div>
                        <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
                            <i class="fa-solid fa-file-word" style="font-size: 2.5rem; color: #3b82f6; margin-bottom: 0.75rem;"></i>
                            <h5 style="margin-bottom: 4px;">Disaster Response Directory</h5>
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1rem;">Officer locations, relief camp storage capacities and routes.</p>
                            <button class="btn btn-secondary" onclick="triggerToast('Word document exported.', 'info')" style="font-size: 0.75rem; padding: 0.45rem 1rem;"><i class="fa-solid fa-download"></i> Get Report</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 8: USER MANAGEMENT -->
            <div id="users" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-users-gear"></i> <span data-translate="users_header">District Administrative Directory</span></h3>
                    </div>
                    <div class="responsive-table">
                        <table class="analytics-table">
                            <thead>
                                <tr>
                                    <th>Officer Name</th>
                                    <th>Role Designation</th>
                                    <th>Contact Email</th>
                                    <th>Phone Directory</th>
                                    <th>Office Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Shri Saurabh Katiyar (IAS)</strong></td>
                                    <td>District Collector</td>
                                    <td>collector.amravati@maharashtra.gov.in</td>
                                    <td>+91 721 2662222</td>
                                    <td>Collector Office, Amravati Campus, Amravati HQ</td>
                                </tr>
                                <tr>
                                    <td><strong>Smt. Shradha Shinde</strong></td>
                                    <td>SDO (Sub-Divisional Officer)</td>
                                    <td>sdo.achalpur@maharashtra.gov.in</td>
                                    <td>+91 7223 222123</td>
                                    <td>SDO Office Complex, Camp, Achalpur</td>
                                </tr>
                                <tr>
                                    <td><strong>Shri Amit Patil</strong></td>
                                    <td>Tehsildar</td>
                                    <td>tehsildar.morshi@maharashtra.gov.in</td>
                                    <td>+91 7228 255230</td>
                                    <td>Tehsil Office, Morshi</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TAB 9: SETTINGS -->
            <div id="settings" class="tab-content">
                <div class="dashboard-card">
                    <div class="card-title-bar">
                        <h3><i class="fa-solid fa-sliders"></i> <span data-translate="settings_header">Portal Settings & Personalization</span></h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 600px;">
                        <div>
                            <h4 style="margin-bottom: 8px;">Auto-Notification Alerts</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">Send email alerts for critical task delays instantly.</p>
                            <input type="checkbox" id="emailNotif" checked> <label for="emailNotif" style="font-size: 0.85rem; font-weight: 600; margin-left: 6px;">Enable Automated Email Triggers</label>
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border-color);">
                        <div>
                            <h4 style="margin-bottom: 8px;">District Portal Theme Default</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">Choose preferred color styling for login session.</p>
                            <button class="btn btn-secondary" onclick="setPortalTheme('light')"><i class="fa-solid fa-sun"></i> Light Gov Theme</button>
                            <button class="btn btn-secondary" onclick="setPortalTheme('dark')"><i class="fa-solid fa-moon"></i> Dark Premium Theme</button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Container for dynamic Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Translations & Core Dashboard logic -->
    <script>
        // Multi-lingual Language Dictionary
        const translations = {
            en: {
                brand_title: "Connect Amravati",
                brand_subtitle: "District Administration",
                nav_dashboard: "Dashboard",
                nav_allocation: "Task Allocation",
                nav_tracking: "Task Tracking",
                nav_communication: "Communication",
                nav_announcements: "Announcements",
                nav_appreciation: "Appreciation",
                nav_reports: "Reports & Analytics",
                nav_users: "User Management",
                nav_settings: "Settings",
                gov_tag: "Gov. of Maharashtra",
                gov_district: "Amravati Division",
                search_placeholder: "Search tasks, officers, circulars...",
                notify_header: "District Notifications",
                clear_all: "Clear All",
                switch_role: "Select Login Officer Role",
                role_collector: "District Collector",
                role_sdo: "SDO, Achalpur Division",
                role_tehsildar: "Tehsildar, Morshi",
                welcome_title: "Welcome back, ",
                welcome_desc: "Logged in as: ",
                welcome_action_desc: "Here is the current administrative overview for Amravati District.",
                card_total: "Total Tasks",
                card_pending: "Pending Reviews",
                card_progress: "In Progress",
                card_completed: "Completed",
                card_overdue: "Overdue Tasks",
                from_last_week: "from last week",
                resolved_today: "resolved today",
                under_execution: "under execution",
                approved_reports: "approved reports",
                requires_attention: "requires action",
                chart_title_status: "Task Status Distribution",
                chart_title_load: "District Task Load by Division / Tehsil",
                btn_live: "Live",
                btn_weekly: "Weekly",
                btn_monthly: "Monthly",
                performance_title: "Tehsildar Performance & Tracking",
                btn_export: "Export",
                search_officer_placeholder: "Search by name, region...",
                showing_records: "Showing 7 records",
                th_officer: "Officer Name",
                th_tehsil: "Tehsil",
                th_assigned: "Assigned",
                th_completed: "Completed",
                th_rate: "Completion Rate",
                th_status: "Status",
                upcoming_deadlines: "Upcoming Critical Deadlines",
                dl_drought: "Drought Mitigation Budget Draft",
                dl_flood: "Monsoon Flood Preparedness Audit",
                dl_sanitation: "National Sanitation Week Report",
                assigned_to: "Assigned to:",
                prio_high: "High",
                prio_med: "Medium",
                prio_low: "Low",
                recent_activity: "Recent Activity Log",
                form_task_title: "Task Subject / Title",
                form_category: "Task Classification Category",
                opt_rev: "Revenue & Land",
                opt_disaster: "Disaster & Relief",
                opt_rural: "Rural Development",
                opt_grievance: "Public Grievance Resolution",
                opt_infra: "Infrastructure & Roads",
                form_tehsil: "Target Sub-division / Tehsil",
                form_assignee: "Assigned Executive Officer",
                form_deadline: "Completion Deadline Date",
                form_priority: "Execution Priority Indicator",
                form_description: "Task Detailed Scope & Instructions",
                btn_assign_task: "Assign Task",
                btn_reset: "Reset",
                info_allocation_title: "Allocation Guidelines",
                info_allocation_desc: "All allocated tasks are officially logged into the Connect Amravati framework. Notifications are dispatched instantly to target Tehsildars or field officers.",
                filter_all: "All Tasks",
                comm_title: "Secured Inter-Office Chat & Memo Exchange",
                comm_desc: "Encrypted transmission network between Collector, SDO, and Tehsildars for swift coordination.",
                comm_connect: "Connect Secure Terminal",
                announcement_banner: "Only Collector or authorized officers are allowed to broadcast general district-wide administrative circulars.",
                appreciation_title: "Wall of Administrative Excellence",
                appreciation_desc: "Recognizing officers who achieved 100% grievance resolution and fastest average task response times.",
                reports_header: "Administrative Reports Center",
                users_header: "District Administrative Directory",
                settings_header: "Portal Settings & Personalization"
            },
            marathi: {
                brand_title: "कनेक्ट अमरावती",
                brand_subtitle: "जिल्हा प्रशासन",
                nav_dashboard: "डॅशबोर्ड",
                nav_allocation: "कार्य वाटप",
                nav_tracking: "कार्य मागोवा",
                nav_communication: "संवाद",
                nav_announcements: "घोषणा",
                nav_appreciation: "प्रशंसा",
                nav_reports: "अहवाल आणि विश्लेषण",
                nav_users: "वापरकर्ता व्यवस्थापन",
                nav_settings: "सेटिंग्ज",
                gov_tag: "महाराष्ट्र शासन",
                gov_district: "अमरावती विभाग",
                search_placeholder: "कार्ये, अधिकारी, परिपत्रके शोधा...",
                notify_header: "जिल्हा सूचना",
                clear_all: "सर्व साफ करा",
                switch_role: "लॉगिन अधिकारी भूमिका निवडा",
                role_collector: "जिल्हाधिकारी",
                role_sdo: "उपविभागीय अधिकारी, अचलपूर",
                role_tehsildar: "तहसीलदार, मोर्शी",
                welcome_title: "स्वागत आहे, ",
                welcome_desc: "लॉगिन भूमिका: ",
                welcome_action_desc: "अमरावती जिल्ह्याचा सद्य प्रशासकीय आढावा खालीलप्रमाणे आहे.",
                card_total: "एकूण कार्ये",
                card_pending: "प्रलंबित कार्ये",
                card_progress: "प्रगतीपथावर",
                card_completed: "पूर्ण झालेले",
                card_overdue: "मुदत संपलेली",
                from_last_week: "मागील आठवड्यापासून",
                resolved_today: "आज पूर्ण झालेले",
                under_execution: "अंमलबजावणी सुरू",
                approved_reports: "मंजूर अहवाल",
                requires_attention: "तात्काळ कारवाई आवश्यक",
                chart_title_status: "कार्य सद्यस्थिती वितरण",
                chart_title_load: "विभाग / तहसील निहाय कार्य भार",
                btn_live: "थेट",
                btn_weekly: "साप्ताहिक",
                btn_monthly: "मासिक",
                performance_title: "तहसीलदार कामगिरी आणि मागोवा",
                btn_export: "निर्यात करा",
                search_officer_placeholder: "नाव किंवा विभागानुसार शोधा...",
                showing_records: "७ नोंदी दर्शवित आहे",
                th_officer: "अधिकाऱ्याचे नाव",
                th_tehsil: "तहसील",
                th_assigned: "सोपविलेली",
                th_completed: "पूर्ण केलेली",
                th_rate: "पूर्णत्वाचा दर",
                th_status: "कामगिरी श्रेणी",
                upcoming_deadlines: "आगामी अंतिम मुदत",
                dl_drought: "दुष्काळ निवारण अंदाजपत्रक मसुदा",
                dl_flood: "पावसाळी पूर सज्जता ऑडिट",
                dl_sanitation: "राष्ट्रीय स्वच्छता सप्ताह अहवाल",
                assigned_to: "विभाग:",
                prio_high: "अत्यंत महत्त्वाचे",
                prio_med: "मध्यम",
                prio_low: "साधारण",
                recent_activity: "अलीकडील क्रियाकलाप लॉग",
                form_task_title: "कार्याचा विषय / शीर्षक",
                form_category: "कार्याचे वर्गीकरण",
                opt_rev: "महसूल आणि जमीन",
                opt_disaster: "आपत्ती व मदत",
                opt_rural: "ग्रामीण विकास",
                opt_grievance: "लोक तक्रार निवारण",
                opt_infra: "पायाभूत सुविधा व रस्ते",
                form_tehsil: "लक्ष्य उपविभाग / तहसील",
                form_assignee: "नियुक्त कार्यकारी अधिकारी",
                form_deadline: "पूर्ण करण्याची अंतिम मुदत",
                form_priority: "कार्याची प्राथमिकता",
                form_description: "कार्याची सविस्तर माहिती आणि सूचना",
                btn_assign_task: "कार्य नियुक्त करा",
                btn_reset: "रीसेट करा",
                info_allocation_title: "नियम आणि मार्गदर्शक तत्त्वे",
                info_allocation_desc: "नियुक्त केलेली सर्व कार्ये कनेक्ट अमरावती प्रणालीमध्ये नोंदविली जातात. तात्काळ सूचना संबंधित तहसीलदार किंवा मैदानी अधिकाऱ्यांकडे पाठविली जाते.",
                filter_all: "सर्व कार्ये",
                comm_title: "सुरक्षित अंतर-कार्यालय संवाद व मेमो एक्सचेंज",
                comm_desc: "जलद समन्वयासाठी जिल्हाधिकारी, उपविभागीय अधिकारी आणि तहसीलदार यांच्यातील सुरक्षित नेटवर्क.",
                comm_connect: "सुरक्षित टर्मिनल जोडणी",
                announcement_banner: "फक्त जिल्हाधिकारी किंवा अधिकृत अधिकाऱ्यांना सामान्य जिल्हाव्यापी प्रशासकीय परिपत्रके प्रसारित करण्याची परवानगी आहे.",
                appreciation_title: "प्रशासकीय उत्कृष्टतेचा भिंत",
                appreciation_desc: "ज्या अधिकाऱ्यांनी १००% तक्रार निवारण आणि जलद सरासरी कार्य प्रतिसाद वेळ गाठली आहे त्यांच्या सन्मानार्थ.",
                reports_header: "प्रशासकीय अहवाल केंद्र",
                users_header: "जिल्हा प्रशासकीय मार्गदर्शिका",
                settings_header: "पोर्टल सेटिंग्ज आणि वैयक्तिकीकरण"
            }
        };

        // Current UI state variables
        let currentLanguage = 'en';
        let currentTheme = 'light';
        let currentRole = 'collector';

        // Officer Mock Data configurations
        const officerRoleData = {
            collector: {
                name: "Saurabh Katiyar (IAS)",
                initials: "SK",
                roleName: "District Collector",
                stats: { total: "1,248", pending: "312", progress: "520", completed: "384", overdue: "32" },
                chartStatusValues: [312, 520, 384, 32],
                chartLoadLabels: ["Amravati Town", "Achalpur", "Morshi", "Chandur Bazar", "Warud", "Daryapur", "Chikhaldara"],
                chartLoadValues: [185, 142, 115, 98, 130, 86, 72]
            },
            sdo: {
                name: "Shradha Shinde",
                initials: "SS",
                roleName: "SDO, Achalpur Division",
                stats: { total: "432", pending: "88", progress: "176", completed: "154", overdue: "14" },
                chartStatusValues: [88, 176, 154, 14],
                chartLoadLabels: ["Achalpur Tehsil", "Chandur Bazar Tehsil", "Chikhaldara Tehsil"],
                chartLoadValues: [142, 98, 72]
            },
            tehsildar: {
                name: "Amit Patil",
                initials: "AP",
                roleName: "Tehsildar, Morshi",
                stats: { total: "145", pending: "28", progress: "54", completed: "58", overdue: "5" },
                chartStatusValues: [28, 54, 58, 5],
                chartLoadLabels: ["Circle 1 (Morshi)", "Circle 2 (Narkhed Border)", "Circle 3 (Rural Circle)"],
                chartLoadValues: [60, 45, 40]
            }
        };

        // Hardcoded initial list of tasks for the tracking system
        let districtTasks = [
            { id: "CA-1025", title: "Pothole Filling & Asphalt Repair", category: "Infrastructure", tehsil: "Amravati Town", status: "In Progress", priority: "High", deadline: "2026-06-25", desc: "Repair asphalt roads in the main market market area prior to high monsoon showers." },
            { id: "CA-1026", title: "Revenue Recovery Targets Q1 Evaluation", category: "Revenue & Land", tehsil: "Achalpur", status: "Pending", priority: "Medium", deadline: "2026-06-28", desc: "Analyze the land tax collection rate against weekly targets of agricultural units." },
            { id: "CA-1027", title: "Dam Silting Clearance Inspection", category: "Disaster Relief", tehsil: "Morshi", status: "Completed", priority: "High", deadline: "2026-06-15", desc: "Inspection audit of water flow clearance and sand clearance logs at the Upper Wardha dam spillways." },
            { id: "CA-1028", title: "Primary Health Center Sanitation check", category: "Rural Development", tehsil: "Chikhaldara", status: "Overdue", priority: "Low", deadline: "2026-06-10", desc: "Perform inspections of medical equipment hygiene and local village sanitation audit." },
            { id: "CA-1029", title: "Adivasi Housing Scheme Land Allocation", category: "Revenue & Land", tehsil: "Chikhaldara", status: "In Progress", priority: "High", deadline: "2026-06-20", desc: "Allot residential lands for the rural housing projects in targeted tribal regions." }
        ];

        // Assignees data directory
        const tehsilAssignees = {
            "Amravati Town": ["Shri. R. K. Solanki (Tehsildar)"],
            "Achalpur": ["Shri. Milind Gavhale (Tehsildar)", "Smt. Shradha Shinde (SDO)"],
            "Morshi": ["Smt. Pallavi Deshmukh (Tehsildar)", "Shri. Amit Patil (Tehsildar)"],
            "Chandur Bazar": ["Shri. Sanjay Patil (Tehsildar)"],
            "Warud": ["Shri. Vijay Wankhede (Tehsildar)"],
            "Daryapur": ["Smt. Sneha Kale (Tehsildar)"],
            "Chikhaldara": ["Shri. Ganesh Shinde (Tehsildar)"]
        };

        // Charts handles
        let statusChart = null;
        let loadChart = null;

        // Initialize application controls
        document.addEventListener('DOMContentLoaded', () => {
            // Clock
            updateClock();
            setInterval(updateClock, 1000);

            // Populate forms initial state
            populateAssignees();

            // Set up charts
            initCharts();

            // Setup Sidebar Click Listeners
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    // Clear active state
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                    // Set active
                    item.classList.add('active');
                    
                    // Toggle visibility of panels
                    const targetTab = item.getAttribute('data-tab');
                    document.querySelectorAll('.tab-content').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    document.getElementById(targetTab).classList.add('active');
                    
                    // Close mobile sidebar if open
                    document.getElementById('sidebarPanel').classList.remove('open');
                });
            });

            // Initial render
            renderTasks();
        });

        // Live clock
        function updateClock() {
            const dateObj = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
            const formatted = dateObj.toLocaleDateString(currentLanguage === 'en' ? 'en-US' : 'mr-IN', options);
            document.getElementById('liveClockDate').textContent = formatted;
        }

        // Toggle language switch (English / Marathi)
        function toggleLanguage() {
            const switcher = document.getElementById('langSwitch');
            const options = switcher.querySelectorAll('.lang-option');
            
            if (currentLanguage === 'en') {
                currentLanguage = 'marathi';
                switcher.classList.add('marathi');
                options[0].classList.remove('active');
                options[1].classList.add('active');
            } else {
                currentLanguage = 'en';
                switcher.classList.remove('marathi');
                options[0].classList.add('active');
                options[1].classList.remove('active');
            }

            // Translate elements
            document.querySelectorAll('[data-translate]').forEach(el => {
                const key = el.getAttribute('data-translate');
                if (translations[currentLanguage] && translations[currentLanguage][key]) {
                    el.textContent = translations[currentLanguage][key];
                }
            });

            // Translate input placeholders
            document.querySelectorAll('[data-translate-placeholder]').forEach(el => {
                const key = el.getAttribute('data-translate-placeholder');
                if (translations[currentLanguage] && translations[currentLanguage][key]) {
                    el.setAttribute('placeholder', translations[currentLanguage][key]);
                }
            });

            updateClock();
        }

        // Toggle UI theme (Light/Dark Mode)
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('themeBtnIcon');
            
            if (body.getAttribute('data-theme') === 'light') {
                body.setAttribute('data-theme', 'dark');
                icon.className = 'fa-solid fa-sun';
                currentTheme = 'dark';
                triggerToast("Dark mode activated. Visual enhancements applied.", "info", "Portal System");
            } else {
                body.setAttribute('data-theme', 'light');
                icon.className = 'fa-solid fa-moon';
                currentTheme = 'light';
                triggerToast("Light mode activated.", "info", "Portal System");
            }

            // Redraw charts to update text fonts colors in dark mode
            updateChartsColors();
        }

        // Explicitly set theme in settings tab
        function setPortalTheme(themeName) {
            const body = document.body;
            const icon = document.getElementById('themeBtnIcon');
            body.setAttribute('data-theme', themeName);
            currentTheme = themeName;
            if (themeName === 'dark') {
                icon.className = 'fa-solid fa-sun';
            } else {
                icon.className = 'fa-solid fa-moon';
            }
            updateChartsColors();
            triggerToast("Theme settings updated.", "success", "Settings");
        }

        // Collapsible sidebar for tablet/mobile viewports
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarPanel');
            sidebar.classList.toggle('open');
        }

        // Switch logged in district officer
        function switchOfficerRole(roleKey) {
            currentRole = roleKey;
            const roleData = officerRoleData[roleKey];
            
            // Set header profile details
            document.getElementById('headerProfileAvatar').textContent = roleData.initials;
            document.getElementById('headerProfileName').textContent = roleData.name;
            
            // Translate header label if needed
            let displayRole = roleData.roleName;
            if (currentLanguage === 'marathi') {
                if (roleKey === 'collector') displayRole = translations.marathi.role_collector;
                if (roleKey === 'sdo') displayRole = translations.marathi.role_sdo;
                if (roleKey === 'tehsildar') displayRole = translations.marathi.role_tehsildar;
            }
            document.getElementById('headerProfileRole').textContent = displayRole;

            // Set welcome banner details
            document.getElementById('welcomeOfficerName').textContent = roleData.name;
            document.getElementById('welcomeOfficerRole').textContent = displayRole;

            // Update stats cards counts
            document.getElementById('statTotalTasks').textContent = roleData.stats.total;
            document.getElementById('statPendingTasks').textContent = roleData.stats.pending;
            document.getElementById('statInProgressTasks').textContent = roleData.stats.progress;
            document.getElementById('statCompletedTasks').textContent = roleData.stats.completed;
            document.getElementById('statOverdueTasks').textContent = roleData.stats.overdue;

            // Highlight selected role option
            document.querySelectorAll('.role-option').forEach(opt => opt.classList.remove('selected'));
            if (roleKey === 'collector') document.getElementById('roleOptionCollector').classList.add('selected');
            if (roleKey === 'sdo') document.getElementById('roleOptionSdo').classList.add('selected');
            if (roleKey === 'tehsildar') document.getElementById('roleOptionTehsildar').classList.add('selected');

            // Close menu
            document.getElementById('profileMenu').style.display = 'none';

            // Re-render and load charts data matching the role
            updateChartData(roleData.chartStatusValues, roleData.chartLoadLabels, roleData.chartLoadValues);

            // Notify user
            triggerToast(`Session switched to ${roleData.name}`, "success", "Role Switched");
        }

        // Toggle user profile selector dropdown menu
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        // Toggle notifications bell dropdown panel
        function toggleNotifications() {
            const dropdown = document.getElementById('notifyDropdown');
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        }

        // Clear notification alerts count
        function clearNotifications() {
            document.getElementById('notifyList').innerHTML = `<div style="text-align: center; padding: 2rem 1rem; color: var(--text-muted); font-size: 0.8rem;">No notifications found.</div>`;
            document.querySelector('.notify-badge').style.display = 'none';
            triggerToast("All notifications cleared.", "info", "Notifications");
        }

        // Close dropdown panels on outside click
        window.addEventListener('click', (e) => {
            if (!e.target.closest('.user-dropdown')) {
                document.getElementById('profileMenu').style.display = 'none';
            }
            if (!e.target.closest('.notify-btn') && !e.target.closest('.notify-dropdown')) {
                document.getElementById('notifyDropdown').style.display = 'none';
            }
        });

        // Initialize Chart.js elements
        function initCharts() {
            const themeTextColor = currentTheme === 'dark' ? '#9ca3af' : '#475569';
            
            // 1. Task Status Chart (Pie)
            const ctxStatus = document.getElementById('taskStatusChart').getContext('2d');
            statusChart = new Chart(ctxStatus, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'In Progress', 'Completed', 'Overdue'],
                    datasets: [{
                        data: officerRoleData.collector.chartStatusValues,
                        backgroundColor: [
                            '#f59e0b', // Pending - Amber
                            '#3b82f6', // In Progress - Info
                            '#10b981', // Completed - Emerald
                            '#ef4444'  // Overdue - Crimson
                        ],
                        borderWidth: 1,
                        borderColor: currentTheme === 'dark' ? '#111827' : '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: themeTextColor,
                                font: { family: 'Inter', size: 11 }
                            }
                        }
                    }
                }
            });

            // 2. Region Workload Load Chart (Bar)
            const ctxLoad = document.getElementById('taskLoadChart').getContext('2d');
            loadChart = new Chart(ctxLoad, {
                type: 'bar',
                data: {
                    labels: officerRoleData.collector.chartLoadLabels,
                    datasets: [{
                        label: 'Active Assigned Tasks',
                        data: officerRoleData.collector.chartLoadValues,
                        backgroundColor: '#1e3a8a',
                        borderRadius: 4,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: currentTheme === 'dark' ? '#1f2937' : '#e2e8f0'
                            },
                            ticks: {
                                color: themeTextColor
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: themeTextColor,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        }

        // Update charts datasets when switching roles
        function updateChartData(statusData, loadLabels, loadData) {
            if (statusChart && loadChart) {
                // Update Status Pie Chart
                statusChart.data.datasets[0].data = statusData;
                statusChart.update();

                // Update Region Workload Chart
                loadChart.data.labels = loadLabels;
                loadChart.data.datasets[0].data = loadData;
                loadChart.update();
            }
        }

        // Adjust chart label colors on theme toggle
        function updateChartsColors() {
            const themeTextColor = currentTheme === 'dark' ? '#9ca3af' : '#475569';
            const gridColor = currentTheme === 'dark' ? '#1f2937' : '#e2e8f0';
            const cardBorderColor = currentTheme === 'dark' ? '#1f2937' : '#ffffff';

            if (statusChart) {
                statusChart.options.plugins.legend.labels.color = themeTextColor;
                statusChart.data.datasets[0].borderColor = cardBorderColor;
                statusChart.update();
            }

            if (loadChart) {
                loadChart.options.scales.y.ticks.color = themeTextColor;
                loadChart.options.scales.y.grid.color = gridColor;
                loadChart.options.scales.x.ticks.color = themeTextColor;
                loadChart.update();
            }
        }

        // Populate Form Assignees dropdown based on selected Tehsil
        function populateAssignees() {
            const tehsilSelect = document.getElementById('taskTehsil');
            const assigneeSelect = document.getElementById('taskAssignee');
            const selectedTehsil = tehsilSelect.value;
            
            assigneeSelect.innerHTML = '';
            
            if (tehsilAssignees[selectedTehsil]) {
                tehsilAssignees[selectedTehsil].forEach(assignee => {
                    const option = document.createElement('option');
                    option.value = assignee;
                    option.textContent = assignee;
                    assigneeSelect.appendChild(option);
                });
            }
        }

        // Form Submission handler for Task Allocation
        function handleTaskAllocation(event) {
            event.preventDefault();
            
            const title = document.getElementById('taskTitle').value;
            const category = document.getElementById('taskCategory').value;
            const tehsil = document.getElementById('taskTehsil').value;
            const assignee = document.getElementById('taskAssignee').value;
            const deadline = document.getElementById('taskDeadline').value;
            const priority = document.getElementById('taskPriority').value;
            const desc = document.getElementById('taskDescription').value;

            // Generate mock ID
            const newId = `CA-${Math.floor(1000 + Math.random() * 9000)}`;

            // Create new task object
            const newTask = {
                id: newId,
                title: title,
                category: category,
                tehsil: tehsil,
                status: "Pending", // Default is pending
                priority: priority,
                deadline: deadline,
                desc: desc
            };

            // Push into task list
            districtTasks.unshift(newTask);

            // Success feedback toast alert
            triggerToast(`Allocated Task ID ${newId} to ${assignee}`, "success", "Task Assigned");

            // Update stats cards numbers
            const currentTotal = parseInt(document.getElementById('statTotalTasks').textContent.replace(/,/g, ''));
            const currentPending = parseInt(document.getElementById('statPendingTasks').textContent.replace(/,/g, ''));
            document.getElementById('statTotalTasks').textContent = (currentTotal + 1).toLocaleString();
            document.getElementById('statPendingTasks').textContent = (currentPending + 1).toLocaleString();

            // Insert dynamic activity timeline log at the top of list
            const timelineContainer = document.getElementById('recentActivityTimeline');
            const newTimelineItem = document.createElement('div');
            newTimelineItem.className = 'timeline-item';
            newTimelineItem.innerHTML = `
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <span class="timeline-time">Just now</span>
                    <span class="timeline-text">New Task assigned: <strong>${title}</strong> for ${tehsil}.</span>
                    <span class="timeline-officer">Assigned to: ${assignee}</span>
                </div>
            `;
            timelineContainer.insertBefore(newTimelineItem, timelineContainer.firstChild);

            // Re-render task list in Tracking tab
            renderTasks();

            // Clear inputs and return to dashboard view tab
            document.getElementById('taskAllocationForm').reset();
            
            // Switch view to Tracking tab to see it
            document.querySelector('[data-tab="task-tracking"]').click();
        }

        // Render dynamic task listing in Task Tracking tab
        function renderTasks(filterStatus = "All") {
            const container = document.getElementById('trackingGridContainer');
            container.innerHTML = '';

            let filteredList = districtTasks;
            if (filterStatus !== "All") {
                filteredList = districtTasks.filter(task => task.status === filterStatus);
            }

            if (filteredList.length === 0) {
                container.innerHTML = `<div style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted);">No tasks found matching filter criteria.</div>`;
                return;
            }

            filteredList.forEach(task => {
                let badgeClass = "badge-info";
                if (task.status === "Pending") badgeClass = "badge-warning";
                if (task.status === "In Progress") badgeClass = "badge-info";
                if (task.status === "Completed") badgeClass = "badge-success";
                if (task.status === "Overdue") badgeClass = "badge-danger";

                let priorityClass = "low";
                if (task.priority === "High") priorityClass = "high";
                if (task.priority === "Medium") priorityClass = "medium";

                const card = document.createElement('div');
                card.className = 'task-card';
                card.innerHTML = `
                    <div class="task-card-header">
                        <span class="task-card-id">${task.id} &bull; ${task.tehsil}</span>
                        <span class="badge ${badgeClass}">${task.status}</span>
                    </div>
                    <h4 class="task-card-title">${task.title}</h4>
                    <p class="task-card-desc">${task.desc}</p>
                    <div class="task-card-footer">
                        <div class="task-card-meta">
                            <span class="task-card-label" data-translate="form_deadline">Deadline</span>
                            <span class="task-card-value">${task.deadline}</span>
                        </div>
                        <span class="priority-badge ${priorityClass}">${task.priority}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // Filter tracking cards on click of filter actions
        function filterTasks(status) {
            // Update active states
            document.querySelectorAll('.tracking-filters button').forEach(btn => {
                btn.className = "btn btn-secondary";
            });
            
            if (status === "All") document.getElementById('filterBtnAll').className = "btn btn-primary";
            if (status === "Pending") document.getElementById('filterBtnPending').className = "btn btn-primary";
            if (status === "In Progress") document.getElementById('filterBtnProgress').className = "btn btn-primary";
            if (status === "Completed") document.getElementById('filterBtnCompleted').className = "btn btn-primary";
            if (status === "Overdue") document.getElementById('filterBtnOverdue').className = "btn btn-primary";

            renderTasks(status);
        }

        // Live Performance Table search filter
        function filterPerformanceTable() {
            const input = document.getElementById('performanceSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('performanceTable');
            const trs = table.getElementsByTagName('tr');

            for (let i = 1; i < trs.length; i++) {
                let found = false;
                const tds = trs[i].getElementsByTagName('td');
                for (let j = 0; j < tds.length; j++) {
                    if (tds[j].textContent.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                trs[i].style.display = found ? "" : "none";
            }
        }

        // Dynamic Toast Notifications System
        function triggerToast(message, type = 'success', headerTitle = 'System Alert') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let iconCode = `<i class="fa-solid fa-circle-check"></i>`;
            if (type === 'warning') iconCode = `<i class="fa-solid fa-triangle-exclamation"></i>`;
            if (type === 'danger') iconCode = `<i class="fa-solid fa-circle-exclamation"></i>`;
            if (type === 'info') iconCode = `<i class="fa-solid fa-circle-info"></i>`;

            toast.innerHTML = `
                <div class="toast-icon-wrapper">${iconCode}</div>
                <div class="toast-body">
                    <h5>${headerTitle}</h5>
                    <p>${message}</p>
                </div>
                <button class="toast-close-btn" onclick="this.parentElement.remove()"><i class="fa-solid fa-xmark"></i></button>
            `;

            container.appendChild(toast);
            
            // Remove after 4 seconds
            setTimeout(() => {
                toast.style.transform = 'translateX(120%)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Mock Action: Export Performance Table to Spreadsheet CSV
        function exportPerformanceData() {
            triggerToast("Excel compilation complete. Check download folder.", "success", "Export Spreadsheet");
        }

        // Sort Table Logic
        function sortTable(n) {
            const table = document.getElementById("performanceTable");
            let rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    
                    let valX = x.textContent.toLowerCase();
                    let valY = y.textContent.toLowerCase();
                    
                    // Handle numbers sorting
                    if (!isNaN(parseFloat(valX)) && !isNaN(parseFloat(valY))) {
                        valX = parseFloat(valX);
                        valY = parseFloat(valY);
                    }

                    if (dir == "asc") {
                        if (valX > valY) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (valX < valY) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount ++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>
</html>
