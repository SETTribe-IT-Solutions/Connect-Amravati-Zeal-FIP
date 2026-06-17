<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Registry & Member Management</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Modern CSS Reset & Variable Definitions */
        :root {
            --bg-gradient: linear-gradient(135deg, #0b0f19 0%, #111827 50%, #1e1b4b 100%);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --card-border-hover: rgba(99, 102, 241, 0.4);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-glow: rgba(99, 102, 241, 0.3);
            --success: #10b981;
            --success-bg: rgba(16, 185, 129, 0.15);
            --danger: #ef4444;
            --danger-bg: rgba(239, 68, 68, 0.15);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --text-inverse: #ffffff;
            --font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 2rem 1rem;
            overflow-x: hidden;
        }

        /* Ambient glowing backgrounds */
        .ambient-glow-1 {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            top: -100px;
            left: -100px;
            z-index: -1;
            pointer-events: none;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(0, 0, 0, 0) 70%);
            bottom: -100px;
            right: -100px;
            z-index: -1;
            pointer-events: none;
        }

        /* Container Layout */
        .container {
            width: 100%;
            max-width: 1200px;
            z-index: 1;
        }

        header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease-out;
        }

        header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #ffffff, #a5b4fc, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }

        header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            font-weight: 300;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            opacity: 0;
            animation: fadeInUp 0.8s ease-out 0.2s forwards;
        }

        @media (min-width: 992px) {
            .main-grid {
                grid-template-columns: 380px 1fr;
            }
        }

        /* Glassmorphism Card Style */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 2rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            border-color: var(--card-border-hover);
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5), 0 0 20px 0 var(--primary-glow);
        }

        .card h2 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #ffffff;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Interactive Form Styling */
        .form-group {
            margin-bottom: 1.25rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            color: var(--text-main);
            font-family: var(--font-family);
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background: rgba(0, 0, 0, 0.35);
        }

        .form-group:focus-within .form-label {
            color: var(--primary);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2em;
            padding-right: 2.5rem;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.85rem;
            color: var(--text-inverse);
            font-family: var(--font-family);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px var(--primary-glow);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--primary-glow);
            filter: brightness(1.1);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Modern Table Design */
        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.15);
            border: 1px solid var(--card-border);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.95rem;
        }

        th {
            background: rgba(0, 0, 0, 0.3);
            color: #ffffff;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--card-border);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-main);
            vertical-align: middle;
            transition: background 0.2s ease;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        /* Dynamic badges and avatars */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .member-name {
            font-weight: 600;
            color: #ffffff;
        }

        .member-email {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-active {
            background: var(--success-bg);
            color: var(--success);
        }

        .badge-inactive {
            background: var(--danger-bg);
            color: var(--danger);
        }

        .role-badge {
            background: rgba(99, 102, 241, 0.15);
            color: #a5b4fc;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .action-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .action-btn:hover {
            color: var(--danger);
            background: var(--danger-bg);
        }

        /* Floating Notification Toast */
        .toast-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 9999;
        }

        .toast {
            background: #111827;
            border: 1px solid var(--success);
            border-left: 4px solid var(--success);
            border-radius: 8px;
            padding: 1rem 1.5rem;
            color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            font-weight: 500;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-muted);
        }

        .empty-state svg {
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <div class="container">
        <!-- Header -->
        <header>
            <h1>Connect Workspace Registry</h1>
            <p>Manage and organize team members with real-time updates</p>
        </header>

        <!-- Main Layout Grid -->
        <main class="main-grid">
            
            <!-- Left Panel: Form Card -->
            <section class="card">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    Add Team Member
                </h2>
                <form id="memberForm" autocomplete="off">
                    <div class="form-group">
                        <label for="fullName" class="form-label">Full Name</label>
                        <input type="text" id="fullName" class="form-control" placeholder="John Doe" required minlength="2">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" class="form-control" placeholder="john@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" class="form-control" required>
                            <option value="" disabled selected hidden>Select Role</option>
                            <option value="Lead Developer">Lead Developer</option>
                            <option value="Software Engineer">Software Engineer</option>
                            <option value="UI/UX Designer">UI/UX Designer</option>
                            <option value="Product Manager">Product Manager</option>
                            <option value="QA Specialist">QA Specialist</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" class="form-control" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">
                        Add Member
                    </button>
                </form>
            </section>

            <!-- Right Panel: Table Card -->
            <section class="card">
                <h2>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Active Roster
                </h2>
                
                <div class="table-responsive">
                    <table id="rosterTable">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th style="width: 80px; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                    
                    <!-- Empty State Placeholder -->
                    <div id="emptyState" class="empty-state" style="display: none;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <p>No members registered in the team yet.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Toast Container -->
    <div class="toast-container">
        <div id="toast" class="toast">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span id="toastMessage">Success message goes here.</span>
        </div>
    </div>

    <!-- Interaction Script -->
    <script>
        // Pre-seeded initial data
        const initialMembers = [
            { name: "Amravati Admin", email: "admin@connect.amravati.in", role: "Product Manager", status: "active" },
            { name: "Mangesh Kumar", email: "mangesh@workspace.dev", role: "Lead Developer", status: "active" },
            { name: "Priyanjali Sen", email: "priya@design.io", role: "UI/UX Designer", status: "inactive" }
        ];

        const form = document.getElementById('memberForm');
        const tableBody = document.getElementById('tableBody');
        const emptyState = document.getElementById('emptyState');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');

        // Render Table Row
        function renderRow(member, index) {
            const tr = document.createElement('tr');
            tr.id = `member-row-${index}`;
            
            // Generate initials for Avatar
            const initials = member.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            
            tr.innerHTML = `
                <td>
                    <div class="member-info">
                        <div class="avatar">${initials}</div>
                        <div>
                            <div class="member-name">${escapeHTML(member.name)}</div>
                            <div class="member-email">${escapeHTML(member.email)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="role-badge">${escapeHTML(member.role)}</span>
                </td>
                <td>
                    <span class="badge badge-${member.status === 'active' ? 'active' : 'inactive'}">${member.status}</span>
                </td>
                <td style="text-align: center;">
                    <button class="action-btn" onclick="removeMember(${index})" aria-label="Delete member">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                    </button>
                </td>
            `;
            return tr;
        }

        // Initialize table
        let membersList = [...initialMembers];
        
        function updateTable() {
            tableBody.innerHTML = '';
            if (membersList.length === 0) {
                emptyState.style.display = 'block';
            } else {
                emptyState.style.display = 'none';
                membersList.forEach((member, idx) => {
                    tableBody.appendChild(renderRow(member, idx));
                });
            }
        }

        // Add Member
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nameInput = document.getElementById('fullName');
            const emailInput = document.getElementById('email');
            const roleInput = document.getElementById('role');
            const statusInput = document.getElementById('status');

            const newMember = {
                name: nameInput.value.trim(),
                email: emailInput.value.trim(),
                role: roleInput.value,
                status: statusInput.value
            };

            membersList.push(newMember);
            updateTable();
            
            // Show toast
            showToast(`Added ${newMember.name} successfully!`);
            
            // Reset form gracefully
            form.reset();
            roleInput.selectedIndex = 0;
            statusInput.value = 'active';
        });

        // Delete Member
        window.removeMember = function(index) {
            const memberName = membersList[index].name;
            const targetRow = document.getElementById(`member-row-${index}`);
            
            if (targetRow) {
                targetRow.style.opacity = '0';
                targetRow.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    membersList.splice(index, 1);
                    updateTable();
                    showToast(`Removed ${memberName} from active roster.`, true);
                }, 300);
            }
        };

        // Utility: Show Toast Notification
        function showToast(message, isInfo = false) {
            toastMessage.textContent = message;
            if (isInfo) {
                toast.style.borderColor = 'var(--primary)';
                toast.querySelector('svg').style.color = 'var(--primary)';
            } else {
                toast.style.borderColor = 'var(--success)';
                toast.querySelector('svg').style.color = 'var(--success)';
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Utility: Escape HTML input
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

        // Initial render
        updateTable();
    </script>
</body>
</html>
