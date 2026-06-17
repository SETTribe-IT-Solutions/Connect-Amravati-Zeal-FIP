<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zeal Faculty Immersion Program | Connect Amravati</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --font-primary: 'Plus Jakarta Sans', sans-serif;
            
            /* Dark Theme (Default) */
            --bg-app: #0b0f19;
            --bg-card: rgba(17, 25, 40, 0.65);
            --bg-card-hover: rgba(23, 33, 53, 0.85);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-color-focus: rgba(99, 102, 241, 0.4);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-glow: rgba(99, 102, 241, 0.15);
            
            --accent: #d946ef;
            --accent-glow: rgba(217, 70, 239, 0.15);
            
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.1);
            --warning: #f59e0b;
            --warning-bg: rgba(245, 158, 11, 0.1);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.1);
            
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            --glass-backdrop: blur(16px);
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-theme="light"] {
            /* Light Theme */
            --bg-app: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.75);
            --bg-card-hover: rgba(255, 255, 255, 0.95);
            --border-color: rgba(0, 0, 0, 0.08);
            --border-color-focus: rgba(79, 70, 229, 0.4);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            
            --primary: #4f46e5;
            --primary-hover: #3730a3;
            --primary-glow: rgba(79, 70, 229, 0.08);
            
            --accent: #c084fc;
            --accent-glow: rgba(192, 132, 252, 0.08);
            
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
            --glass-backdrop: blur(12px);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-primary);
            background-color: var(--bg-app);
            color: var(--text-primary);
            min-height: 100vh;
            padding: 2rem;
            transition: background-color 0.4s ease, color 0.4s ease;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Blobs for Aesthetics */
        .ambient-blob-1, .ambient-blob-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.15;
            pointer-events: none;
            transition: var(--transition);
        }
        .ambient-blob-1 {
            background: var(--primary);
            top: -100px;
            right: -100px;
        }
        .ambient-blob-2 {
            background: var(--accent);
            bottom: -150px;
            left: -100px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Header Styles */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--glass-shadow);
            backdrop-filter: var(--glass-backdrop);
            -webkit-backdrop-filter: var(--glass-backdrop);
        }

        .header-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .header-title p {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .theme-toggle-btn {
            background: var(--border-color);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.75rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .theme-toggle-btn:hover {
            transform: scale(1.05);
            background: var(--primary-glow);
            border-color: var(--primary);
        }

        /* Layout Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Card Base */
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            box-shadow: var(--glass-shadow);
            backdrop-filter: var(--glass-backdrop);
            -webkit-backdrop-filter: var(--glass-backdrop);
            padding: 2rem;
            transition: var(--transition);
        }

        .glass-card:hover {
            border-color: rgba(255, 255, 255, 0.12);
            box-shadow: 0 12px 40px 0 rgba(0, 0, 0, 0.45);
        }

        .card-header {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .card-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-header h2 i {
            color: var(--primary);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            color: var(--text-muted);
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-primary);
            font-size: 0.9rem;
            outline: none;
            transition: var(--transition);
        }

        [data-theme="light"] .form-control {
            background: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: rgba(0, 0, 0, 0.3);
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        select.form-control {
            appearance: none;
            cursor: pointer;
        }

        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            color: var(--text-muted);
            pointer-events: none;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.75rem;
        }

        .btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-sm);
            font-family: var(--font-primary);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: var(--transition);
            outline: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        /* Roster & Table Section */
        .roster-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            max-width: 350px;
            flex: 1;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .search-box input {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.5rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-primary);
            font-size: 0.85rem;
            outline: none;
            transition: var(--transition);
        }

        [data-theme="light"] .search-box input {
            background: rgba(255, 255, 255, 0.5);
        }

        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* Custom Table Styling */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.9rem;
        }

        .custom-table th {
            background: rgba(0, 0, 0, 0.4);
            color: var(--text-secondary);
            font-weight: 600;
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        [data-theme="light"] .custom-table th {
            background: rgba(0, 0, 0, 0.02);
        }

        .custom-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            transition: var(--transition);
            white-space: nowrap;
        }

        .custom-table tbody tr {
            transition: var(--transition);
        }

        .custom-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        [data-theme="light"] .custom-table tbody tr:hover {
            background: rgba(0, 0, 0, 0.01);
        }

        /* Avatar and Faculty Info Group */
        .faculty-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .faculty-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
        }

        .faculty-meta {
            display: flex;
            flex-direction: column;
        }

        .faculty-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .faculty-email {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-pending {
            background: var(--warning-bg);
            color: var(--warning);
        }

        .badge-approved {
            background: var(--success-bg);
            color: var(--success);
        }

        .badge-danger {
            background: var(--danger-bg);
            color: var(--danger);
        }

        /* Row Action Buttons */
        .action-btns {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            width: 32px;
            height: 32px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .action-btn:hover {
            color: var(--text-primary);
        }

        .action-btn.btn-delete:hover {
            background: var(--danger-bg);
            border-color: var(--danger);
            color: var(--danger);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state p {
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
        }

        /* Toast Notifications */
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
            min-width: 300px;
            padding: 1rem;
            background: rgba(17, 25, 40, 0.9);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-sm);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff;
            backdrop-filter: blur(10px);
            transform: translateX(120%);
            animation: slideIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transition: var(--transition);
        }

        @keyframes slideIn {
            to { transform: translateX(0); }
        }

        .toast.toast-success { border-left-color: var(--success); }
        .toast.toast-warning { border-left-color: var(--warning); }
        .toast.toast-danger { border-left-color: var(--danger); }

        .toast-content {
            flex: 1;
            font-size: 0.85rem;
        }

        .toast-close {
            background: transparent;
            border: none;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            font-size: 1rem;
        }

        .toast-close:hover {
            color: #ffffff;
        }

        /* Table Animations */
        @keyframes fadeInRow {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .row-animate {
            animation: fadeInRow 0.4s ease forwards;
        }
    </style>
</head>
<body data-theme="dark">

    <!-- Ambient background decorations -->
    <div class="ambient-blob-1"></div>
    <div class="ambient-blob-2"></div>

    <div class="container">
        
        <!-- Header -->
        <header>
            <div class="header-title">
                <h1>Zeal Faculty Immersion Program</h1>
                <p>Connect Amravati — Faculty Industry Exposure Portal</p>
            </div>
            <button class="theme-toggle-btn" id="themeToggle" aria-label="Toggle Theme">
                <i class="fa-solid fa-sun" id="themeIcon"></i>
            </button>
        </header>

        <!-- Main Dashboard Layout -->
        <div class="dashboard-grid">
            
            <!-- Left Side: Interactive Registration Form -->
            <div class="glass-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-user-plus"></i> Faculty Registration</h2>
                </div>
                
                <form id="facultyForm" autocomplete="off">
                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <div class="input-wrapper">
                            <input type="text" id="fullName" class="form-control" placeholder="Dr. Shubhangi R. Patil" required>
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Work Email</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" class="form-control" placeholder="shubhangi.zeal@gmail.com" required>
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <div class="input-wrapper select-wrapper">
                            <select id="department" class="form-control" required>
                                <option value="" disabled selected>Select Department</option>
                                <option value="Computer Engineering">Computer Engineering</option>
                                <option value="Information Technology">Information Technology</option>
                                <option value="E&TC Engineering">E&TC Engineering</option>
                                <option value="Mechanical Engineering">Mechanical Engineering</option>
                                <option value="Civil Engineering">Civil Engineering</option>
                            </select>
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="industryPartner">Industry Partner / Domain</label>
                        <div class="input-wrapper">
                            <input type="text" id="industryPartner" class="form-control" placeholder="e.g. AWS Cloud Security" required>
                            <i class="fa-solid fa-building"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Immersion Status</label>
                        <div class="input-wrapper select-wrapper">
                            <select id="status" class="form-control" required>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Completed">Completed</option>
                            </select>
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="button" id="resetBtn" class="btn btn-secondary"><i class="fa-solid fa-rotate-right"></i> Reset</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Register</button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Live Roster & Table -->
            <div class="glass-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-table-list"></i> Registered Faculty Roster</h2>
                </div>

                <div class="roster-actions">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by name, dept, partner...">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <button id="exportCsv" class="btn btn-secondary" style="flex: 0 0 auto; padding: 0.65rem 1rem;"><i class="fa-solid fa-file-csv"></i> Export CSV</button>
                </div>

                <div class="table-responsive">
                    <table class="custom-table" id="facultyTable">
                        <thead>
                            <tr>
                                <th>Faculty Member</th>
                                <th>Department</th>
                                <th>Industry Partner</th>
                                <th>Status</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Populated via Javascript -->
                        </tbody>
                    </table>
                </div>

                <div id="emptyState" class="empty-state" style="display: none;">
                    <i class="fa-solid fa-folder-open"></i>
                    <p>No faculty registrations found.</p>
                    <small>Fill out the form on the left to add dynamic roster items.</small>
                </div>
            </div>

        </div>
    </div>

    <!-- Container for dynamic Toast notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // Data Structure & Storage Key
        const STORAGE_KEY = 'connect_amravati_fip_roster';
        let rosterData = [];

        // Sample initial data if storage is empty
        const initialSampleData = [
            {
                id: '1',
                name: 'Dr. Shubhangi R. Patil',
                email: 'shubhangi.patil@zeal.edu.in',
                department: 'Computer Engineering',
                partner: 'NVIDIA AI Academy',
                status: 'Approved'
            },
            {
                id: '2',
                name: 'Prof. Amol K. Deshmukh',
                email: 'amol.deshmukh@zeal.edu.in',
                department: 'Information Technology',
                partner: 'AWS Cloud Security',
                status: 'Pending'
            },
            {
                id: '3',
                name: 'Dr. Priya S. Joshi',
                email: 'priya.joshi@zeal.edu.in',
                department: 'E&TC Engineering',
                partner: 'Tata Communications VLSI Lab',
                status: 'Completed'
            }
        ];

        // Elements
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const facultyForm = document.getElementById('facultyForm');
        const resetBtn = document.getElementById('resetBtn');
        const tableBody = document.getElementById('tableBody');
        const searchInput = document.getElementById('searchInput');
        const emptyState = document.getElementById('emptyState');
        const toastContainer = document.getElementById('toastContainer');
        const exportCsv = document.getElementById('exportCsv');

        // Initialize App
        document.addEventListener('DOMContentLoaded', () => {
            // Theme Setup
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.body.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);

            // Load Data
            const stored = localStorage.getItem(STORAGE_KEY);
            if (stored) {
                rosterData = JSON.parse(stored);
            } else {
                rosterData = [...initialSampleData];
                localStorage.setItem(STORAGE_KEY, JSON.stringify(rosterData));
            }
            renderTable();
        });

        // Theme Toggle Handler
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
            showToast(`Switched to ${newTheme} mode`, 'success');
        });

        function updateThemeIcon(theme) {
            if (theme === 'light') {
                themeIcon.className = 'fa-solid fa-moon';
            } else {
                themeIcon.className = 'fa-solid fa-sun';
            }
        }

        // Form Submit Handler
        facultyForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const name = document.getElementById('fullName').value.trim();
            const email = document.getElementById('email').value.trim();
            const department = document.getElementById('department').value;
            const partner = document.getElementById('industryPartner').value.trim();
            const status = document.getElementById('status').value;

            // Simple validation
            if (!name || !email || !department || !partner || !status) {
                showToast('Please fill out all required fields.', 'danger');
                return;
            }

            // Create registration object
            const newRegistration = {
                id: Date.now().toString(),
                name,
                email,
                department,
                partner,
                status
            };

            // Add to roster data
            rosterData.unshift(newRegistration);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(rosterData));

            // Reset Form and Render Table
            facultyForm.reset();
            renderTable(newRegistration.id); // highlight the new item
            showToast(`Successfully registered ${name}!`, 'success');
        });

        // Reset Form Action
        resetBtn.addEventListener('click', () => {
            facultyForm.reset();
            showToast('Form fields cleared.', 'warning');
        });

        // Search Input Filter
        searchInput.addEventListener('input', () => {
            renderTable();
        });

        // Render Table Data
        function renderTable(highlightId = null) {
            const query = searchInput.value.toLowerCase().trim();
            tableBody.innerHTML = '';
            
            // Filter Data
            const filteredData = rosterData.filter(item => {
                return (
                    item.name.toLowerCase().includes(query) ||
                    item.department.toLowerCase().includes(query) ||
                    item.partner.toLowerCase().includes(query) ||
                    item.email.toLowerCase().includes(query) ||
                    item.status.toLowerCase().includes(query)
                );
            });

            if (filteredData.length === 0) {
                emptyState.style.display = 'block';
                return;
            } else {
                emptyState.style.display = 'none';
            }

            filteredData.forEach(item => {
                const tr = document.createElement('tr');
                tr.setAttribute('data-id', item.id);
                
                // Add fade-in animation to newly created elements
                if (highlightId && item.id === highlightId) {
                    tr.classList.add('row-animate');
                }

                // Initial Avatar
                const initials = item.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();

                // Status Badge styling
                let badgeClass = 'badge-pending';
                if (item.status === 'Approved') badgeClass = 'badge-approved';
                if (item.status === 'Completed') badgeClass = 'badge-approved'; // green
                
                let statusIcon = 'fa-clock';
                if (item.status === 'Approved') statusIcon = 'fa-check-double';
                if (item.status === 'Completed') statusIcon = 'fa-graduation-cap';

                tr.innerHTML = `
                    <td>
                        <div class="faculty-info">
                            <div class="faculty-avatar">${initials}</div>
                            <div class="faculty-meta">
                                <span class="faculty-name">${escapeHTML(item.name)}</span>
                                <span class="faculty-email">${escapeHTML(item.email)}</span>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHTML(item.department)}</td>
                    <td>${escapeHTML(item.partner)}</td>
                    <td>
                        <span class="badge ${badgeClass}">
                            <i class="fa-solid ${statusIcon}"></i> ${item.status}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <div class="action-btns" style="justify-content: center;">
                            <button class="action-btn btn-delete" title="Delete Registration" onclick="deleteRecord('${item.id}')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Delete Registration
        window.deleteRecord = function(id) {
            const record = rosterData.find(item => item.id === id);
            if (!record) return;

            const tr = document.querySelector(`tr[data-id="${id}"]`);
            if (tr) {
                tr.style.transform = 'translateX(-30px)';
                tr.style.opacity = '0';
                tr.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    rosterData = rosterData.filter(item => item.id !== id);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(rosterData));
                    renderTable();
                    showToast(`Removed registration of ${record.name}`, 'danger');
                }, 300);
            }
        };

        // Export to CSV Functionality
        exportCsv.addEventListener('click', () => {
            if (rosterData.length === 0) {
                showToast('No roster data to export.', 'warning');
                return;
            }

            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "ID,Name,Email,Department,Industry Partner/Domain,Status\n";

            rosterData.forEach(item => {
                const row = [
                    item.id,
                    `"${item.name.replace(/"/g, '""')}"`,
                    `"${item.email.replace(/"/g, '""')}"`,
                    `"${item.department.replace(/"/g, '""')}"`,
                    `"${item.partner.replace(/"/g, '""')}"`,
                    item.status
                ].join(",");
                csvContent += row + "\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `zeal_fip_roster_${Date.now()}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showToast('Roster exported to CSV successfully!', 'success');
        });

        // Toast Helper
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            let icon = 'fa-circle-check';
            if (type === 'warning') icon = 'fa-triangle-exclamation';
            if (type === 'danger') icon = 'fa-trash-can';

            toast.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Helper to escape HTML characters
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
