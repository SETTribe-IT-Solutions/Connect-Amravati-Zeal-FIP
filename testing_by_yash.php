<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Immersion Program (FIP) Portal - Connect Amravati</title>
    <meta name="description" content="Connect Amravati - Zeal Faculty Immersion Program tracker. Easily record, view, search, and manage faculty training records in real-time.">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern Reset & Base Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        :root {
            --bg-dark: #070a13;
            --bg-card: rgba(15, 23, 42, 0.65);
            --bg-card-hover: rgba(23, 37, 68, 0.8);
            --border: rgba(255, 255, 255, 0.08);
            --border-focus: rgba(99, 102, 241, 0.6);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            
            --primary: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --secondary-gradient: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            
            --glow-primary: rgba(99, 102, 241, 0.35);
            --glow-secondary: rgba(6, 182, 212, 0.35);
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
            padding: 2.5rem 1.5rem;
        }

        /* Ambient Dynamic Background Glows */
        body::before, body::after {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            z-index: -1;
            filter: blur(140px);
            opacity: 0.22;
            pointer-events: none;
        }

        body::before {
            background: #6366f1;
            top: -10%;
            left: -10%;
            animation: floatGlow 15s infinite alternate ease-in-out;
        }

        body::after {
            background: #06b6d4;
            bottom: -10%;
            right: -10%;
            animation: floatGlow 15s infinite alternate-reverse ease-in-out;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, 40px) scale(1.2); }
        }

        /* Container */
        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        /* Header section */
        header {
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.5rem;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .logo-icon {
            background: var(--primary-gradient);
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            box-shadow: 0 0 25px var(--glow-primary);
        }

        .logo-title h1 {
            font-size: 1.85rem;
            font-weight: 800;
            background: linear-gradient(to right, #ffffff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.5px;
        }

        .logo-title p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.02), transparent);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(99, 102, 241, 0.35);
            box-shadow: 0 12px 24px rgba(0,0,0,0.35);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }

        .stat-icon.blue {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        .stat-icon.cyan {
            background: rgba(6, 182, 212, 0.15);
            color: #22d3ee;
        }

        .stat-icon.green {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
        }

        .stat-info h3 {
            font-size: 1.85rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .stat-info p {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 0.25rem;
        }

        /* Workspace Grid Layout */
        main.workspace-grid {
            display: grid;
            grid-template-columns: 390px 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            main.workspace-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.3);
            position: relative;
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

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff;
        }

        .card-title i {
            color: var(--primary);
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.55rem;
            color: var(--text-main);
        }

        .form-control {
            width: 100%;
            background: rgba(10, 14, 28, 0.55);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 0.8rem 1.1rem;
            color: white;
            font-size: 0.925rem;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--border-focus);
            box-shadow: 0 0 12px rgba(99, 102, 241, 0.25);
            background: rgba(10, 14, 28, 0.85);
        }

        select.form-control option {
            background: #0d1222;
            color: white;
            padding: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 11px;
            font-size: 0.95rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.25);
        }

        .btn-primary {
            background: var(--primary-gradient);
        }

        .btn-primary:hover {
            box-shadow: 0 0 22px var(--glow-primary);
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Registry (Table Card Section Header) */
        .registry-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 1.25rem;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        @media (max-width: 640px) {
            .search-box {
                width: 100%;
            }
        }

        .search-box i {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-box input {
            padding-left: 2.75rem;
        }

        /* Table Area Layout & Custom Scrollbar */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(10, 14, 28, 0.25);
        }

        .table-responsive::-webkit-scrollbar {
            height: 7px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.15);
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 4px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.22);
        }

        /* Modern Typography Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.925rem;
        }

        th {
            background: rgba(8, 12, 23, 0.75);
            color: var(--text-muted);
            font-weight: 600;
            padding: 1.1rem 1.35rem;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 1.1rem 1.35rem;
            border-bottom: 1px solid var(--border);
            color: var(--text-main);
        }

        tr {
            background: transparent;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.025);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Distinct Pills/Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.08);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.08);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .badge-info {
            background: rgba(6, 182, 212, 0.08);
            color: #22d3ee;
            border: 1px solid rgba(6, 182, 212, 0.2);
        }

        /* Delete Button Action */
        .btn-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            border: 1px solid rgba(239, 68, 68, 0.25);
            background: rgba(239, 68, 68, 0.06);
            color: #f87171;
            cursor: pointer;
        }

        .btn-action:hover {
            background: var(--danger-gradient);
            color: white;
            border-color: transparent;
            box-shadow: 0 0 12px rgba(239, 68, 68, 0.45);
            transform: scale(1.08);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #6366f1, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .empty-state p {
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Toast notifications system */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .toast {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            padding: 1.1rem 1.6rem;
            border-radius: 12px;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            box-shadow: 0 12px 28px rgba(0,0,0,0.5);
            animation: slideIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.9rem;
            font-weight: 500;
        }

        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .toast.success {
            border-left-color: #10b981;
        }
        .toast.danger {
            border-left-color: #ef4444;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Main Top Header -->
        <header>
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div class="logo-title">
                    <h1>Faculty Immersion Program</h1>
                    <p>Connect Amravati - Zeal FIP Registration & Management</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem;">
                <span class="badge badge-info" style="padding: 0.5rem 1rem; border-radius: 10px;">
                    <i class="fa-regular fa-clock"></i> Active Tracking Session
                </span>
            </div>
        </header>

        <!-- Stats Dashboard Overview -->
        <section class="stats-grid" aria-label="Program Statistics Summary">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 id="stat-total">0</h3>
                    <p>Total Registered</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cyan">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div class="stat-info">
                    <h3 id="stat-active">0</h3>
                    <p>In Progress</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="stat-info">
                    <h3 id="stat-completed">0</h3>
                    <p>Completed Trainings</p>
                </div>
            </div>
        </section>

        <!-- Workspace Section -->
        <main class="workspace-grid">
            <!-- Left Panel: Faculty Submission Form -->
            <section class="glass-card" aria-labelledby="form-heading">
                <h2 id="form-heading" class="card-title">
                    <i class="fa-solid fa-circle-plus"></i>
                    Add Faculty Record
                </h2>
                
                <form id="fip-form">
                    <div class="form-group">
                        <label for="faculty-name">Faculty Name</label>
                        <input type="text" id="faculty-name" class="form-control" placeholder="Dr. Yash Sharma" required>
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <select id="department" class="form-control" required>
                            <option value="" disabled selected>Select Department</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="E&TC Engineering">E&TC Engineering</option>
                            <option value="Mechanical Engineering">Mechanical Engineering</option>
                            <option value="Civil Engineering">Civil Engineering</option>
                            <option value="MBA / Management">MBA / Management</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="host-industry">Host Industry / Organization</label>
                        <input type="text" id="host-industry" class="form-control" placeholder="e.g. Google India, TCS, Zeal IT" required>
                    </div>

                    <div class="form-group">
                        <label for="domain">Training Domain</label>
                        <input type="text" id="domain" class="form-control" placeholder="e.g. Machine Learning, Cloud Architecture" required>
                    </div>

                    <div class="form-group">
                        <label for="duration">Duration (Weeks)</label>
                        <input type="number" id="duration" class="form-control" min="1" max="52" placeholder="e.g. 6" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Immersion Status</label>
                        <select id="status" class="form-control" required>
                            <option value="Planned">Planned</option>
                            <option value="In Progress" selected>In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <button type="submit" id="fip-submit-btn" class="btn btn-primary">
                        <i class="fa-solid fa-paper-plane"></i> Submit Record
                    </button>
                </form>
            </section>

            <!-- Right Panel: Registry Table -->
            <section class="glass-card" aria-labelledby="registry-heading">
                <div class="registry-header">
                    <h2 id="registry-heading" class="card-title" style="margin-bottom: 0;">
                        <i class="fa-solid fa-table-list"></i>
                        Immersion Registry
                    </h2>
                    
                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="search-input" class="form-control" placeholder="Search by name, industry, domain...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="registry-table">
                        <thead>
                            <tr>
                                <th>Faculty Details</th>
                                <th>Industry & Domain</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th style="text-align: center; width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Injected dynamically by JS -->
                        </tbody>
                    </table>
                    
                    <div id="empty-state" class="empty-state" style="display: none;">
                        <i class="fa-solid fa-folder-open"></i>
                        <p>No records found. Try adding a new record or adjust your search.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Active Toast Alerts container -->
    <div class="toast-container" id="toast-container"></div>

    <!-- Interactive Client Logic -->
    <script>
        // Pre-seeded dummy data for an initial high-fidelity presentation
        const defaultRecords = [
            {
                id: 1,
                name: "Dr. Amit Patle",
                dept: "Computer Engineering",
                industry: "Amazon Web Services",
                domain: "Cloud Infrastructure & DevOps",
                duration: 8,
                status: "Completed"
            },
            {
                id: 2,
                name: "Prof. Neha Deshmukh",
                dept: "Information Technology",
                industry: "Infosys Labs",
                domain: "Full-Stack Development (MERN)",
                duration: 6,
                status: "In Progress"
            },
            {
                id: 3,
                name: "Prof. Rajesh Verma",
                dept: "E&TC Engineering",
                industry: "Intel India",
                domain: "Edge AI & IoT Systems",
                duration: 12,
                status: "Planned"
            }
        ];

        // Retrieve existing records or seed with default
        let records = JSON.parse(localStorage.getItem('fip_records'));
        if (!records) {
            records = defaultRecords;
            localStorage.setItem('fip_records', JSON.stringify(records));
        }

        // Cache DOM elements
        const tableBody = document.getElementById('table-body');
        const emptyState = document.getElementById('empty-state');
        const fipForm = document.getElementById('fip-form');
        const searchInput = document.getElementById('search-input');
        const toastContainer = document.getElementById('toast-container');

        // Statistic displays
        const statTotal = document.getElementById('stat-total');
        const statActive = document.getElementById('stat-active');
        const statCompleted = document.getElementById('stat-completed');

        // Initial render
        updateView();

        // Handle Form Submission
        fipForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('faculty-name').value.trim();
            const dept = document.getElementById('department').value;
            const industry = document.getElementById('host-industry').value.trim();
            const domain = document.getElementById('domain').value.trim();
            const duration = parseInt(document.getElementById('duration').value);
            const status = document.getElementById('status').value;

            const newRecord = {
                id: Date.now(),
                name,
                dept,
                industry,
                domain,
                duration,
                status
            };

            records.push(newRecord);
            saveRecords();
            updateView(searchInput.value.toLowerCase());
            fipForm.reset();

            showToast("Faculty record registered successfully!", "success");
        });

        // Search Input Filter
        searchInput.addEventListener('input', function() {
            updateView(this.value.toLowerCase());
        });

        // Save records utility
        function saveRecords() {
            localStorage.setItem('fip_records', JSON.stringify(records));
        }

        // Delete Row Action
        function deleteRecord(id) {
            records = records.filter(r => r.id !== id);
            saveRecords();
            updateView(searchInput.value.toLowerCase());
            showToast("Record removed from registry.", "danger");
        }

        // Render & Statistics Update Loop
        function updateView(filterQuery = "") {
            tableBody.innerHTML = "";
            
            // Filter query matching
            const filtered = records.filter(r => {
                const searchStr = `${r.name} ${r.dept} ${r.industry} ${r.domain}`.toLowerCase();
                return searchStr.includes(filterQuery);
            });

            // Re-calculate stats based on absolute records state
            statTotal.textContent = records.length;
            statActive.textContent = records.filter(r => r.status === "In Progress").length;
            statCompleted.textContent = records.filter(r => r.status === "Completed").length;

            if (filtered.length === 0) {
                emptyState.style.display = "block";
                return;
            }

            emptyState.style.display = "none";

            filtered.forEach(record => {
                const tr = document.createElement('tr');
                
                // Class and icon resolution based on status
                let badgeClass = "badge-info";
                let iconClass = "fa-calendar";
                if (record.status === "Completed") {
                    badgeClass = "badge-success";
                    iconClass = "fa-check-double";
                } else if (record.status === "In Progress") {
                    badgeClass = "badge-warning";
                    iconClass = "fa-hourglass-half";
                }

                tr.innerHTML = `
                    <td>
                        <div style="font-weight: 700; color: #ffffff;">${escapeHtml(record.name)}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">${escapeHtml(record.dept)}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #f3f4f6;">${escapeHtml(record.industry)}</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;">${escapeHtml(record.domain)}</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: #e5e7eb;">${record.duration} Weeks</div>
                    </td>
                    <td>
                        <span class="badge ${badgeClass}">
                            <i class="fa-solid ${iconClass}"></i>
                            ${record.status}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <button class="btn-action" title="Delete Record" aria-label="Delete Record" onclick="deleteRecord(${record.id})">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        // Clean character escaping for security
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Custom Toast Dialog Display
        function showToast(message, type = "success") {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';
            
            toast.innerHTML = `
                <i class="fa-solid ${icon}"></i>
                <span>${message}</span>
            `;
            
            toastContainer.appendChild(toast);
            
            // Auto fade out after duration
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(12px)';
                setTimeout(() => toast.remove(), 350);
            }, 3000);
        }
    </script>
</body>
</html>
