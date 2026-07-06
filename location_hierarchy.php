<?php
session_start();
require_once 'include/dbConfig.php';

// Auth Check
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';
$sName  = $_SESSION['user_name'] ?? 'User';

// Determine Level
$level = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    'Talathi', 'Gramsevak' => 3,
    default => 3
};

$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        'title' => 'Location Hierarchy - Connect Amravati',
        'page_title' => 'Location Hierarchy',
        'page_subtitle' => 'Explore the administrative map structure across Amravati District, Talukas, and local Villages.',
        'lbl_district' => 'District Jurisdiction',
        'lbl_talukas' => 'Registered Talukas',
        'lbl_villages' => 'Assigned Villages'
    ],
    'mr' => [
        'title' => 'स्थान उतरंड - अमरावती कनेक्ट',
        'page_title' => 'स्थान उतरंड (प्रशासकीय रचना)',
        'page_subtitle' => 'अमरावती जिल्हा, तालुके आणि स्थानिक गावे यांची प्रशासकीय रचना पहा.',
        'lbl_district' => 'जिल्हा अधिकार क्षेत्र',
        'lbl_talukas' => 'नोंदणीकृत तालुके',
        'lbl_villages' => 'नियुक्त गावे'
    ]
];

$t = $translations[$lang];

// Fetch District (Amravati is default ID 1)
$district = null;
$resDist = $conn->query("SELECT * FROM districts LIMIT 1");
if ($resDist) {
    $district = $resDist->fetch_assoc();
}
if (!$district) {
    $district = ['district_id' => 1, 'district_name' => 'Amravati'];
}

// Fetch all talukas
$talukas = [];
$resTal = $conn->query("SELECT * FROM talukas ORDER BY taluka_name ASC");
if ($resTal) {
    while ($row = $resTal->fetch_assoc()) {
        $talukas[] = $row;
    }
}

// Fetch all villages grouped by taluka
$villagesByTaluka = [];
$resVil = $conn->query("SELECT * FROM villages ORDER BY village_name ASC");
if ($resVil) {
    while ($row = $resVil->fetch_assoc()) {
        $villagesByTaluka[$row['taluka_id']][] = $row;
    }
}

$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
include 'include/header.php';
$activePage = 'hierarchy';
include 'include/sidebar.php';
?>
<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Header -->
    <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
        <div class="flex items-center flex-1">
            <button id="sidebarToggle" class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none block lg:hidden">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Theme Toggle -->
            <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
            </button>
            
            <!-- Notifications Bell -->
            <?php include 'include/notification_widget.php'; ?>

            <!-- Profile Dropdown -->
            <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
                        <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?> (<?= htmlspecialchars($headerLocationDisplay) ?>)</span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
                        <?= strtoupper(substr($sName, 0, 1)) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 z-50 text-left">
                    <div class="py-1">
                        <a href="profile_update.php" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i>Update Profile
                        </a>
                        <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-650 hover:bg-red-50 dark:hover:bg-red-900/10">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
            </div>
            
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold badge-l<?= $level ?>">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($t['lbl_district']) ?>: <?= htmlspecialchars($district['district_name']) ?>
                </span>
            </div>
        </div>

        <!-- DISTRICT CARD HEADER -->
        <div class="bg-gradient-to-br from-navy-700 to-indigo-800 rounded-3xl p-6 md:p-8 text-white shadow-lg mb-8 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/5 rounded-full flex items-center justify-center pointer-events-none">
                <i data-lucide="map" class="w-24 h-24 opacity-10"></i>
            </div>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center shadow-inner">
                    <i data-lucide="landmark" class="w-8 h-8 text-amber-400"></i>
                </div>
                <div>
                    <span class="text-xs uppercase tracking-widest text-indigo-200 font-bold"><?= htmlspecialchars($t['lbl_district']) ?></span>
                    <h2 class="text-3xl font-extrabold tracking-tight mt-0.5"><?= htmlspecialchars($district['district_name']) ?></h2>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-8 pt-6 border-t border-white/10">
                <div>
                    <span class="block text-indigo-200 text-xs font-bold uppercase">Total Talukas</span>
                    <span class="text-2xl font-bold mt-1 block"><?= count($talukas) ?></span>
                </div>
                <div>
                    <span class="block text-indigo-200 text-xs font-bold uppercase">Total Villages</span>
                    <span class="text-2xl font-bold mt-1 block">
                        <?php
                        $totVils = 0;
                        foreach($villagesByTaluka as $vlist) $totVils += count($vlist);
                        echo $totVils;
                        ?>
                    </span>
                </div>
                <div>
                    <span class="block text-indigo-200 text-xs font-bold uppercase">State</span>
                    <span class="text-sm font-semibold mt-2 block">Maharashtra</span>
                </div>
                <div>
                    <span class="block text-indigo-200 text-xs font-bold uppercase">Country</span>
                    <span class="text-sm font-semibold mt-2 block">India</span>
                </div>
            </div>
        </div>

        <!-- TALUKA LIST ACCORDION / GRID -->
        <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4 uppercase tracking-wider flex items-center gap-2">
            <i data-lucide="network" class="w-5 h-5 text-indigo-500"></i>
            <?= htmlspecialchars($t['lbl_talukas']) ?>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($talukas as $tal): ?>
                <?php $tId = $tal['taluka_id']; ?>
                <div onclick="showLocationReport(<?= $tal['taluka_id'] ?>, 0)" class="cursor-pointer bg-white dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-800 transition-all duration-200">
                    <div class="flex justify-between items-start pb-4 border-b border-slate-100 dark:border-slate-850">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl flex items-center justify-center">
                                <i data-lucide="compass" class="w-5 h-5 text-indigo-650 dark:text-indigo-400"></i>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($tal['taluka_name']) ?></h4>
                                <span class="text-xs text-slate-400">Taluka Jurisdiction</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-full text-xs font-bold text-slate-550 dark:text-slate-400">
                            <?= isset($villagesByTaluka[$tId]) ? count($villagesByTaluka[$tId]) : 0 ?> Villages
                        </span>
                    </div>

                    <!-- Village tags list -->
                    <div class="mt-4">
                        <span class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-widest block mb-2"><?= htmlspecialchars($t['lbl_villages']) ?></span>
                        <div class="flex flex-wrap gap-2">
                            <?php if (empty($villagesByTaluka[$tId])): ?>
                                <span class="text-xs text-slate-400 italic">No villages registered.</span>
                            <?php else: ?>
                                <?php foreach ($villagesByTaluka[$tId] as $vil): ?>
                                    <span onclick="event.stopPropagation(); showLocationReport(0, <?= $vil['village_id'] ?>)" class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 bg-slate-50 hover:bg-indigo-100 dark:bg-slate-900/60 dark:hover:bg-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300 rounded-xl border border-slate-200/50 dark:border-slate-800 transition-colors">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        <?= htmlspecialchars($vil['village_name']) ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </main>
</div>

<!-- =========================================================================
     MODAL: LOCATION DETAIL REPORT (Chart.js + Tasks List)
     ========================================================================= -->
<div id="locationReportModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-md z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-900 w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 m-4 flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 bg-gradient-to-r from-navy-700 to-indigo-850 text-white flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i data-lucide="map-pin" class="w-5 h-5 text-amber-450"></i>
                <h3 class="font-bold text-lg" id="modalLocName">Location Report</h3>
            </div>
            <button type="button" onclick="closeLocModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <div class="p-6 md:p-8 overflow-y-auto flex-1 space-y-6">
            <!-- Stats grid -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-100 dark:border-slate-850">
                    <span class="text-xs text-slate-400 font-bold uppercase">Total Tasks</span>
                    <span class="block text-2xl font-black text-slate-900 dark:text-white mt-1" id="statTotal">0</span>
                </div>
                <div class="bg-green-50 dark:bg-green-950/20 p-4 rounded-xl border border-green-100/50 dark:border-green-900/30">
                    <span class="text-xs text-green-600 dark:text-green-400 font-bold uppercase">Completed</span>
                    <span class="block text-2xl font-black text-green-750 dark:text-green-300 mt-1" id="statCompleted">0</span>
                </div>
                <div class="bg-blue-50 dark:bg-blue-950/20 p-4 rounded-xl border border-blue-100/50 dark:border-blue-900/30">
                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold uppercase">In Progress</span>
                    <span class="block text-2xl font-black text-blue-750 dark:text-blue-300 mt-1" id="statProgress">0</span>
                </div>
                <div class="bg-amber-50 dark:bg-amber-950/20 p-4 rounded-xl border border-amber-100/50 dark:border-amber-900/30">
                    <span class="text-xs text-amber-650 dark:text-amber-400 font-bold uppercase">On Hold</span>
                    <span class="block text-2xl font-black text-amber-700 dark:text-amber-300 mt-1" id="statHold">0</span>
                </div>
                <div class="bg-red-50 dark:bg-red-950/20 p-4 rounded-xl border border-red-100/50 dark:border-red-900/30">
                    <span class="text-xs text-red-600 dark:text-red-400 font-bold uppercase">Pending / Reject</span>
                    <span class="block text-2xl font-black text-red-700 dark:text-red-300 mt-1" id="statPending">0</span>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Status Chart -->
                <div class="bg-white dark:bg-slate-950 p-6 rounded-xl border border-slate-200/50 dark:border-slate-800/80 shadow-sm flex flex-col items-center">
                    <h4 class="text-xs font-bold text-slate-450 dark:text-slate-400 uppercase tracking-wider mb-4">Task Status Distribution</h4>
                    <div class="w-full max-w-[240px]">
                        <canvas id="statusChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Category Chart -->
                <div class="bg-white dark:bg-slate-950 p-6 rounded-xl border border-slate-200/50 dark:border-slate-800/80 shadow-sm flex flex-col items-center">
                    <h4 class="text-xs font-bold text-slate-455 dark:text-slate-400 uppercase tracking-wider mb-4">Task Category Breakdown</h4>
                    <div class="w-full max-w-[300px]">
                        <canvas id="categoryChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Task List Table -->
            <div>
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-3">Task Allocations Detail</h4>
                <div class="glass-panel rounded-xl border border-slate-200/60 dark:border-slate-800/60 overflow-hidden shadow-inner">
                    <div class="max-h-72 overflow-y-auto">
                        <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                            <thead class="bg-slate-50 dark:bg-slate-900 sticky top-0 z-10">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase">Task Title</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase">Category</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 tracking-wider uppercase">Allocated By</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 tracking-wider uppercase">Assigned To</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs" id="modalTaskList">
                                <!-- Ajax rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Lucide Icons
    lucide.createIcons();

    // Profile dropdown
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    if(profileBtn && profileMenu) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.classList.toggle('hidden');
        });
        document.addEventListener('click', () => profileMenu.classList.add('hidden'));
    }

    // Location Detail Report logic
    let statusChart = null;
    let categoryChart = null;

    function showLocationReport(talukaId, villageId) {
        document.getElementById('locationReportModal').classList.remove('hidden');
        document.getElementById('modalTaskList').innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400"><i class="animate-spin inline-block w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full mr-2"></i> Loading location metrics...</td></tr>`;

        fetch(`api/get_location_report.php?taluka_id=${talukaId}&village_id=${villageId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('modalLocName').innerText = data.location_name + ' Analytics Report';
                    document.getElementById('statTotal').innerText = data.total_tasks;
                    document.getElementById('statCompleted').innerText = data.status_counts.Completed;
                    document.getElementById('statProgress').innerText = data.status_counts['In Progress'] || 0;
                    document.getElementById('statHold').innerText = data.status_counts['On Hold'] || 0;
                    document.getElementById('statPending').innerText = (data.status_counts.Pending || 0) + (data.status_counts.Rejected || 0);

                    // Doughnut Chart status
                    if (statusChart) statusChart.destroy();
                    const statusCtx = document.getElementById('statusChartCanvas').getContext('2d');
                    statusChart = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Completed', 'In Progress', 'On Hold', 'Pending', 'Rejected'],
                            datasets: [{
                                data: [
                                    data.status_counts.Completed,
                                    data.status_counts['In Progress'] || 0,
                                    data.status_counts['On Hold'] || 0,
                                    data.status_counts.Pending || 0,
                                    data.status_counts.Rejected || 0
                                ],
                                backgroundColor: ['#064e3b', '#1e3a8a', '#334155', '#92400e', '#7f1d1d'],
                                borderWidth: 3,
                                borderColor: '#ffffff',
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } }
                            }
                        }
                    });

                    // Bar Chart categories
                    if (categoryChart) categoryChart.destroy();
                    const catLabels = Object.keys(data.category_counts);
                    const catValues = Object.values(data.category_counts);
                    const catCtx = document.getElementById('categoryChartCanvas').getContext('2d');
                    categoryChart = new Chart(catCtx, {
                        type: 'bar',
                        data: {
                            labels: catLabels.length ? catLabels : ['No Tasks'],
                            datasets: [{
                                label: 'Tasks Qty',
                                data: catValues.length ? catValues : [0],
                                backgroundColor: '#312e81',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });

                    // Render Table rows
                    const listBody = document.getElementById('modalTaskList');
                    listBody.innerHTML = '';
                    if (data.tasks.length === 0) {
                        listBody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400 italic">No tasks allocated in this region.</td></tr>`;
                    } else {
                        data.tasks.forEach(t => {
                            let badgeStyle = 'bg-slate-100 text-slate-700';
                            if (t.status === 'Completed') badgeStyle = 'bg-green-100 text-green-800';
                            if (t.status === 'In Progress') badgeStyle = 'bg-blue-100 text-blue-800';
                            if (t.status === 'On Hold') badgeStyle = 'bg-amber-100 text-amber-800';
                            if (t.status === 'Rejected') badgeStyle = 'bg-red-100 text-red-800';

                            const tr = document.createElement('tr');
                            tr.className = "hover:bg-slate-50 dark:hover:bg-slate-800/40 text-slate-700 dark:text-slate-350";
                            tr.innerHTML = `
                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                                    <div>${t.task_title}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">No: ${t.task_no || '#' + t.task_id}</div>
                                </td>
                                <td class="px-4 py-3 font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">${t.task_category || 'General'}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">${t.creator_name || 'System'}</div>
                                    <div class="text-[9px] text-slate-400">${t.creator_role || ''}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">${t.assignee_name || 'Unassigned'}</div>
                                    <div class="text-[9px] text-slate-400">${t.assignee_role || ''}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${badgeStyle}">${t.status}</span>
                                </td>
                            `;
                            listBody.appendChild(tr);
                        });
                    }
                    lucide.createIcons();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error loading details.');
            });
    }

    function closeLocModal() {
        document.getElementById('locationReportModal').classList.add('hidden');
    }
</script>
<?php include 'include/footer.php'; ?>
