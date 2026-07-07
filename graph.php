<?php
session_start();
require_once 'include/dbConfig.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$sName = $_SESSION['user_name'] ?? 'User';
$sRole = $_SESSION['user_role'] ?? 'Officer';
$userId = (int)$_SESSION['user_id'];
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$level = match($sRole) {
    'Administrator', 'System Administrator', 'Collector', 'Additional Collector', 'Deputy Collector' => 1,
    'SDO', 'Tehsildar', 'BDO' => 2,
    default => 3
};

// ── EMPLOYEE PERFORMANCE ──────────────────────────────────────────────────────
$employees = [];
$resEmp = $conn->query("
    SELECT u.user_id, u.full_name, u.employee_code, r.role_name,
           d.department_name,
           COUNT(t.task_id)                                           AS total_tasks,
           SUM(t.status = 'Completed')                               AS completed,
           SUM(t.status = 'In Progress')                             AS in_progress,
           SUM(t.status = 'Pending')                                 AS pending,
           SUM(t.status = 'On Hold')                                 AS on_hold,
           SUM(t.status = 'Rejected')                                AS rejected,
           ROUND(SUM(t.status='Completed')/GREATEST(COUNT(t.task_id),1)*100,1) AS rate
    FROM users u
    LEFT JOIN roles r        ON u.role_id       = r.role_id
    LEFT JOIN departments d  ON u.department_id = d.department_id
    LEFT JOIN tasks t        ON t.assigned_user_id = u.user_id
    WHERE u.status = 'Active'
    GROUP BY u.user_id
    ORDER BY rate DESC, total_tasks DESC
");
if ($resEmp) while ($row = $resEmp->fetch_assoc()) $employees[] = $row;

// ── VILLAGE PERFORMANCE ───────────────────────────────────────────────────────
$villages = [];
$resVil = $conn->query("
    SELECT v.village_id, v.village_name, tk.taluka_name,
           COUNT(t.task_id)                                            AS total_tasks,
           SUM(t.status='Completed')                                   AS completed,
           SUM(t.status='In Progress')                                 AS in_progress,
           SUM(t.status='Pending')                                     AS pending,
           ROUND(SUM(t.status='Completed')/GREATEST(COUNT(t.task_id),1)*100,1) AS rate
    FROM villages v
    LEFT JOIN talukas tk ON v.taluka_id = tk.taluka_id
    LEFT JOIN tasks t    ON t.village_id = v.village_id
    GROUP BY v.village_id
    ORDER BY rate DESC, total_tasks DESC
");
if ($resVil) while ($row = $resVil->fetch_assoc()) $villages[] = $row;

// ── DISTRICT (TALUKA) PERFORMANCE ────────────────────────────────────────────
$talukas = [];
$resTal = $conn->query("
    SELECT tk.taluka_id, tk.taluka_name,
           COUNT(t.task_id)                                            AS total_tasks,
           SUM(t.status='Completed')                                   AS completed,
           SUM(t.status='In Progress')                                 AS in_progress,
           SUM(t.status='Pending')                                     AS pending,
           SUM(t.status='On Hold')                                     AS on_hold,
           ROUND(SUM(t.status='Completed')/GREATEST(COUNT(t.task_id),1)*100,1) AS rate
    FROM talukas tk
    LEFT JOIN tasks t ON t.taluka_id = tk.taluka_id
    GROUP BY tk.taluka_id
    ORDER BY rate DESC
");
if ($resTal) while ($row = $resTal->fetch_assoc()) $talukas[] = $row;

$pageTitle = 'Performance Analytics — Connect Amravati';
$extraHead = <<<'EOT'
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        .tab-btn { transition: all 0.2s; }
        .tab-btn.active { background: linear-gradient(135deg,#4f46e5,#7c3aed); color:#fff; box-shadow:0 4px 12px rgba(79,70,229,.35); }
        .tab-panel { display:none; }
        .tab-panel.active { display:block; }
        .perf-bar { transition: width 1s cubic-bezier(.4,0,.2,1); }
        @keyframes fadeSlideUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
        .anim-row { animation: fadeSlideUp .35s ease both; }
        .rate-ring { stroke-linecap:round; transition:stroke-dashoffset 1.2s ease; }
        @media print {
            body * { visibility:hidden; }
            #printZone, #printZone * { visibility:visible; }
            #printZone { position:fixed;top:0;left:0;width:100%;padding:32px; }
        }
    </style>
EOT;

include 'include/header.php';
$activePage = 'graph';
include 'include/sidebar.php';

// Helper: rate color
function rateColor(float $r): string {
    if ($r >= 80) return 'text-emerald-600 dark:text-emerald-400';
    if ($r >= 50) return 'text-amber-600 dark:text-amber-400';
    return 'text-red-600 dark:text-red-400';
}
function rateBg(float $r): string {
    if ($r >= 80) return 'bg-emerald-500';
    if ($r >= 50) return 'bg-amber-500';
    return 'bg-red-500';
}

$totalEmpTasks = array_sum(array_column($employees, 'total_tasks'));
$totalEmpDone  = array_sum(array_column($employees, 'completed'));
$totalVilTasks = array_sum(array_column($villages,  'total_tasks'));
$totalVilDone  = array_sum(array_column($villages,  'completed'));
$totalTalTasks = array_sum(array_column($talukas,   'total_tasks'));
$totalTalDone  = array_sum(array_column($talukas,   'completed'));
?>

<div class="flex-1 flex flex-col overflow-hidden">

  <!-- HEADER -->
  <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
    <div class="flex items-center flex-1">
      <button id="sidebarToggle" class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none block lg:hidden">
        <i data-lucide="menu" class="w-6 h-6"></i>
      </button>
    </div>
    <div class="flex items-center space-x-4">
      <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
        <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
        <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
      </button>
      <?php include 'include/notification_widget.php'; ?>
      <div class="relative pl-4 border-l border-slate-200 dark:border-slate-700">
        <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none">
          <div class="flex flex-col text-right hidden sm:block">
            <span class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($sName) ?></span>
            <span class="text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($sRole) ?></span>
          </div>
          <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border border-amber-500/40 shadow-sm">
            <?= strtoupper(substr($sName,0,1)) ?>
          </div>
        </button>
        <div id="profileDropdownMenu" class="hidden absolute right-0 top-full mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 z-50 text-left">
          <div class="py-1">
            <a href="profile_update.php" class="flex items-center px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"><i data-lucide="user" class="w-4 h-4 mr-2 text-slate-400"></i>Update Profile</a>
            <a href="logout.php" class="flex items-center px-4 py-2.5 text-xs text-red-600 hover:bg-red-50 dark:hover:bg-red-900/10"><i data-lucide="log-out" class="w-4 h-4 mr-2 text-red-500"></i>Logout</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8 space-y-8">

    <!-- Page Title -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg">
          <i data-lucide="bar-chart-2" class="w-6 h-6 text-white"></i>
        </div>
        <div>
          <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Performance Analytics</h1>
          <p class="text-sm text-slate-500 dark:text-slate-400">District · Taluka · Village · Employee performance reports</p>
        </div>
      </div>
      <div class="text-xs text-slate-400">Report as of <?= date('d M Y, h:i A') ?></div>
    </div>

    <!-- Summary Stat Row -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
      <div class="bg-gradient-to-br from-indigo-600 to-violet-700 text-white rounded-2xl p-5 shadow-md">
        <div class="text-indigo-200 text-xs font-bold uppercase mb-2">Employees Tracked</div>
        <div class="text-3xl font-black"><?= count($employees) ?></div>
        <div class="text-indigo-200 text-xs mt-1"><?= $totalEmpDone ?> / <?= $totalEmpTasks ?> tasks done</div>
      </div>
      <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-2xl p-5 shadow-md">
        <div class="text-emerald-100 text-xs font-bold uppercase mb-2">Villages Tracked</div>
        <div class="text-3xl font-black"><?= count($villages) ?></div>
        <div class="text-emerald-100 text-xs mt-1"><?= $totalVilDone ?> / <?= $totalVilTasks ?> tasks done</div>
      </div>
      <div class="col-span-2 md:col-span-1 bg-gradient-to-br from-amber-500 to-orange-600 text-white rounded-2xl p-5 shadow-md">
        <div class="text-amber-100 text-xs font-bold uppercase mb-2">Talukas (Districts)</div>
        <div class="text-3xl font-black"><?= count($talukas) ?></div>
        <div class="text-amber-100 text-xs mt-1"><?= $totalTalDone ?> / <?= $totalTalTasks ?> tasks done</div>
      </div>
    </div>

    <!-- TAB NAVIGATION -->
    <div class="flex items-center gap-3 p-1.5 bg-white dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 rounded-2xl w-fit shadow-sm">
      <button class="tab-btn active px-5 py-2.5 rounded-xl text-sm font-bold" onclick="switchTab('emp')">
        <i data-lucide="users" class="w-4 h-4 inline mr-1.5"></i>Employees
      </button>
      <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800" onclick="switchTab('vil')">
        <i data-lucide="map-pin" class="w-4 h-4 inline mr-1.5"></i>Villages
      </button>
      <button class="tab-btn px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800" onclick="switchTab('dist')">
        <i data-lucide="landmark" class="w-4 h-4 inline mr-1.5"></i>District / Taluka
      </button>
    </div>

    <!-- ============================= EMPLOYEE TAB ============================= -->
    <div id="tab-emp" class="tab-panel active space-y-6">

      <!-- Chart + Top Performers -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Employee Completion Rate Chart</h3>
          <canvas id="empChart" height="220"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">🏆 Top 5 Performers</h3>
          <div class="space-y-3">
            <?php foreach(array_slice($employees,0,5) as $i => $emp): ?>
            <div class="flex items-center gap-3">
              <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-xs font-black flex items-center justify-center"><?= $i+1 ?></span>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= htmlspecialchars($emp['full_name']) ?></div>
                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($emp['role_name'] ?? '') ?></div>
              </div>
              <span class="text-sm font-black <?= rateColor((float)$emp['rate']) ?>"><?= $emp['rate'] ?>%</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Employee Table -->
      <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
          <h3 class="font-bold text-slate-900 dark:text-white">All Employee Performance</h3>
          <div class="relative">
            <input type="text" id="empSearch" placeholder="Search employee..." oninput="filterTable('empSearch','empTbody')"
              class="pl-8 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-indigo-500">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/60">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Employee</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Department</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Total</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Done</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Pending</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase w-36">Rate</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Report</th>
              </tr>
            </thead>
            <tbody id="empTbody" class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <?php foreach($employees as $i => $emp):
                $rate = (float)$emp['rate'];
                $initials = strtoupper(substr($emp['full_name'],0,1));
              ?>
              <tr class="anim-row hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm" style="animation-delay:<?= $i*30 ?>ms">
                <td class="px-5 py-3.5 text-slate-400 text-xs"><?= $i+1 ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-950/40 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-black text-xs"><?= $initials ?></div>
                    <div>
                      <div class="font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($emp['full_name']) ?></div>
                      <div class="text-[10px] text-slate-400"><?= htmlspecialchars($emp['employee_code'] ?: '') ?> · <?= htmlspecialchars($emp['role_name'] ?? '') ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400"><?= htmlspecialchars($emp['department_name'] ?: '—') ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-slate-800 dark:text-white"><?= $emp['total_tasks'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-emerald-600 dark:text-emerald-400"><?= $emp['completed'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-amber-600 dark:text-amber-400"><?= $emp['pending'] ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div class="h-full <?= rateBg($rate) ?> perf-bar rounded-full" style="width:<?= min($rate,100) ?>%"></div>
                    </div>
                    <span class="text-xs font-black <?= rateColor($rate) ?> w-10 text-right"><?= $rate ?>%</span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-center">
                  <button onclick="downloadEmpReport(<?= htmlspecialchars(json_encode($emp)) ?>)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                    <i data-lucide="download" class="w-3 h-3"></i> PDF
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============================= VILLAGE TAB ============================= -->
    <div id="tab-vil" class="tab-panel space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Village Completion Rate Chart</h3>
          <canvas id="vilChart" height="220"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">🏅 Top 5 Villages</h3>
          <div class="space-y-3">
            <?php foreach(array_slice($villages,0,5) as $i => $vil): ?>
            <div class="flex items-center gap-3">
              <span class="w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-xs font-black flex items-center justify-center"><?= $i+1 ?></span>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= htmlspecialchars($vil['village_name']) ?></div>
                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($vil['taluka_name'] ?? '') ?></div>
              </div>
              <span class="text-sm font-black <?= rateColor((float)$vil['rate']) ?>"><?= $vil['rate'] ?>%</span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
          <h3 class="font-bold text-slate-900 dark:text-white">Village Performance Report</h3>
          <div class="relative">
            <input type="text" id="vilSearch" placeholder="Search village..." oninput="filterTable('vilSearch','vilTbody')"
              class="pl-8 pr-4 py-2 text-sm border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl focus:ring-2 focus:ring-emerald-500">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none"></i>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/60">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Village</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Taluka</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Total</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Done</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">In Progress</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase w-36">Rate</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Report</th>
              </tr>
            </thead>
            <tbody id="vilTbody" class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <?php foreach($villages as $i => $vil):
                $rate = (float)$vil['rate'];
              ?>
              <tr class="anim-row hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm" style="animation-delay:<?= $i*25 ?>ms">
                <td class="px-5 py-3.5 text-slate-400 text-xs"><?= $i+1 ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <i data-lucide="map-pin" class="w-4 h-4 text-emerald-500 flex-shrink-0"></i>
                    <span class="font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($vil['village_name']) ?></span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-xs text-slate-500"><?= htmlspecialchars($vil['taluka_name'] ?? '—') ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-slate-800 dark:text-white"><?= $vil['total_tasks'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-emerald-600 dark:text-emerald-400"><?= $vil['completed'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-blue-600 dark:text-blue-400"><?= $vil['in_progress'] ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div class="h-full <?= rateBg($rate) ?> perf-bar rounded-full" style="width:<?= min($rate,100) ?>%"></div>
                    </div>
                    <span class="text-xs font-black <?= rateColor($rate) ?> w-10 text-right"><?= $rate ?>%</span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-center">
                  <button onclick="downloadVilReport(<?= htmlspecialchars(json_encode($vil)) ?>)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition-all shadow-sm">
                    <i data-lucide="download" class="w-3 h-3"></i> PDF
                  </button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ============================= DISTRICT TAB ============================= -->
    <div id="tab-dist" class="tab-panel space-y-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">Taluka Completion Rate Chart</h3>
          <canvas id="distChart" height="220"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 p-6 shadow-sm">
          <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-4">📊 Task Status Overview</h3>
          <canvas id="distDonut" height="200"></canvas>
        </div>
      </div>

      <div class="bg-white dark:bg-slate-950 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-800">
          <h3 class="font-bold text-slate-900 dark:text-white">District / Taluka Performance</h3>
          <button onclick="downloadDistReport()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl transition-all shadow-sm">
            <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Download Full District Report
          </button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/60">
              <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">#</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase">Taluka</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Total</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Done</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">In Progress</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">Pending</th>
                <th class="px-5 py-3 text-center text-xs font-bold text-slate-400 uppercase">On Hold</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase w-40">Completion Rate</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
              <?php foreach($talukas as $i => $tal):
                $rate = (float)$tal['rate'];
              ?>
              <tr class="anim-row hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm" style="animation-delay:<?= $i*30 ?>ms">
                <td class="px-5 py-3.5 text-slate-400 text-xs"><?= $i+1 ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <i data-lucide="compass" class="w-4 h-4 text-amber-500 flex-shrink-0"></i>
                    <span class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($tal['taluka_name']) ?></span>
                  </div>
                </td>
                <td class="px-5 py-3.5 text-center font-bold text-slate-800 dark:text-white"><?= $tal['total_tasks'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-emerald-600 dark:text-emerald-400"><?= $tal['completed'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-blue-600 dark:text-blue-400"><?= $tal['in_progress'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-amber-600 dark:text-amber-400"><?= $tal['pending'] ?></td>
                <td class="px-5 py-3.5 text-center font-bold text-slate-500"><?= $tal['on_hold'] ?></td>
                <td class="px-5 py-3.5">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                      <div class="h-full <?= rateBg($rate) ?> perf-bar rounded-full" style="width:<?= min($rate,100) ?>%"></div>
                    </div>
                    <span class="text-xs font-black <?= rateColor($rate) ?> w-12 text-right"><?= $rate ?>%</span>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
/* ── DATA FROM PHP ────────────────────────────────────────────── */
const empData  = <?= json_encode(array_map(fn($e) => ['name'=>$e['full_name'],'rate'=>(float)$e['rate'],'total'=>(int)$e['total_tasks'],'done'=>(int)$e['completed']], $employees)) ?>;
const vilData  = <?= json_encode(array_map(fn($v) => ['name'=>$v['village_name'],'rate'=>(float)$v['rate'],'total'=>(int)$v['total_tasks'],'done'=>(int)$v['completed']], $villages)) ?>;
const distData = <?= json_encode(array_map(fn($t) => ['name'=>$t['taluka_name'],'rate'=>(float)$t['rate'],'total'=>(int)$t['total_tasks'],'done'=>(int)$t['completed'],'pending'=>(int)$t['pending'],'hold'=>(int)$t['on_hold'],'prog'=>(int)$t['in_progress']], $talukas)) ?>;

const totalDone = <?= $totalTalDone ?>;
const totalPend = <?= array_sum(array_column($talukas,'pending')) ?>;
const totalProg = <?= array_sum(array_column($talukas,'in_progress')) ?>;
const totalHold = <?= array_sum(array_column($talukas,'on_hold')) ?>;
const reportDate = '<?= date('d M Y') ?>';

/* ── TABS ─────────────────────────────────────────────────────── */
function switchTab(id) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('active');
        b.classList.add('text-slate-600','dark:text-slate-300');
    });
    document.getElementById('tab-' + id).classList.add('active');
    event.currentTarget.classList.add('active');
    event.currentTarget.classList.remove('text-slate-600','dark:text-slate-300');
    lucide.createIcons();
}

/* ── CHART HELPERS ────────────────────────────────────────────── */
function makeBarChart(id, labels, values, color) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    if (ctx._chart) ctx._chart.destroy();
    ctx._chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.slice(0,15),
            datasets: [{ label: 'Completion %', data: values.slice(0,15), backgroundColor: color, borderRadius: 6 }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%', font: { size: 11 } }, grid: { color: '#e2e8f0' } },
                y: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
}

function makeDonut(id, labels, values, colors) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    if (ctx._chart) ctx._chart.destroy();
    ctx._chart = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 1 }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10 } } } }
    });
}

// Render all charts
makeBarChart('empChart',  empData.map(e=>e.name),  empData.map(e=>e.rate),  '#4f46e5');
makeBarChart('vilChart',  vilData.map(v=>v.name),  vilData.map(v=>v.rate),  '#10b981');
makeBarChart('distChart', distData.map(d=>d.name), distData.map(d=>d.rate), '#f59e0b');
makeDonut('distDonut', ['Completed','In Progress','Pending','On Hold'], [totalDone, totalProg, totalPend, totalHold], ['#10b981','#3b82f6','#f59e0b','#94a3b8']);

/* ── TABLE SEARCH ─────────────────────────────────────────────── */
let _filterTableDebounce = {};

function filterTable(searchId, tbodyId) {
    const inputEl = document.getElementById(searchId);
    if (!inputEl) return;
    const q = inputEl.value.trim().toLowerCase();

    // Validation: require at least 2 chars if user has entered something
    if (q.length > 0 && q.length < 2) {
        // Don't alert on every keystroke, just silently wait
        document.querySelectorAll('#' + tbodyId + ' tr').forEach(row => {
            row.style.display = '';
        });
        return;
    }

    let visibleCount = 0;
    document.querySelectorAll('#' + tbodyId + ' tr').forEach(row => {
        const matches = !q || row.textContent.toLowerCase().includes(q);
        row.style.display = matches ? '' : 'none';
        if (matches) visibleCount++;
    });

    // Show SweetAlert if no results found (debounce to avoid rapid alerts)
    clearTimeout(_filterTableDebounce[searchId]);
    if (q.length >= 2 && visibleCount === 0) {
        _filterTableDebounce[searchId] = setTimeout(() => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'info',
                    title: 'No Records Found',
                    html: `<p>No results found for <strong>"${inputEl.value.trim()}"</strong>.</p><p class="text-sm text-slate-500 mt-1">Please enter correct data and try again.</p>`,
                    confirmButtonColor: '#4f46e5',
                    confirmButtonText: 'OK'
                });
            }
        }, 600);
    }
}

/* ── PDF DOWNLOAD ─────────────────────────────────────────────── */
function downloadEmpReport(emp) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const date = reportDate;

    // Header bar
    doc.setFillColor(79,70,229);
    doc.rect(0,0,220,32,'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('CONNECT AMRAVATI', 14, 14);
    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.text('Individual Employee Performance Report', 14, 23);
    doc.text(date, 170, 14);

    // Name block
    doc.setTextColor(30,30,30);
    doc.setFontSize(18); doc.setFont('helvetica','bold');
    doc.text(emp.full_name, 14, 50);
    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.setTextColor(100,100,100);
    doc.text((emp.employee_code || '') + '  ·  ' + (emp.role_name || '') + '  ·  ' + (emp.department_name || ''), 14, 58);

    // Stats boxes
    const stats = [
        { label: 'Total Tasks',  val: emp.total_tasks,  color: [79,70,229] },
        { label: 'Completed',    val: emp.completed,    color: [16,185,129] },
        { label: 'In Progress',  val: emp.in_progress,  color: [59,130,246] },
        { label: 'Pending',      val: emp.pending,      color: [245,158,11] },
        { label: 'On Hold',      val: emp.on_hold,      color: [148,163,184] },
        { label: 'Completion %', val: emp.rate + '%',   color: [220,38,38]  },
    ];
    stats.forEach((s,i) => {
        const x = 14 + (i%3)*65, y = 70 + Math.floor(i/3)*28;
        doc.setFillColor(...s.color);
        doc.roundedRect(x,y,60,22,3,3,'F');
        doc.setTextColor(255,255,255);
        doc.setFontSize(16); doc.setFont('helvetica','bold');
        doc.text(String(s.val), x+8, y+13);
        doc.setFontSize(8); doc.setFont('helvetica','normal');
        doc.text(s.label, x+8, y+19);
    });

    // Footer
    doc.setFillColor(240,240,245);
    doc.rect(0,278,220,20,'F');
    doc.setTextColor(100,100,100);
    doc.setFontSize(8);
    doc.text('Amravati District Administration · Connect Amravati Portal · Confidential Report', 14, 288);

    doc.save('Employee_' + emp.full_name.replace(/\s+/g,'_') + '_Report.pdf');
}

function downloadVilReport(vil) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFillColor(16,185,129);
    doc.rect(0,0,220,32,'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('CONNECT AMRAVATI', 14, 14);
    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.text('Village Performance Report · ' + reportDate, 14, 23);

    doc.setTextColor(30,30,30);
    doc.setFontSize(18); doc.setFont('helvetica','bold');
    doc.text(vil.village_name, 14, 50);
    doc.setFontSize(10); doc.setFont('helvetica','normal');
    doc.setTextColor(100,100,100);
    doc.text('Taluka: ' + (vil.taluka_name || 'N/A'), 14, 58);

    const stats = [
        { label: 'Total Tasks', val: vil.total_tasks, color: [16,185,129] },
        { label: 'Completed',   val: vil.completed,   color: [79,70,229]  },
        { label: 'In Progress', val: vil.in_progress, color: [59,130,246] },
        { label: 'Pending',     val: vil.pending,     color: [245,158,11] },
        { label: 'Completion %',val: vil.rate+'%',    color: [220,38,38]  },
    ];
    stats.forEach((s,i) => {
        const x = 14 + (i%3)*65, y = 70 + Math.floor(i/3)*28;
        doc.setFillColor(...s.color);
        doc.roundedRect(x,y,60,22,3,3,'F');
        doc.setTextColor(255,255,255);
        doc.setFontSize(16); doc.setFont('helvetica','bold');
        doc.text(String(s.val), x+8, y+13);
        doc.setFontSize(8); doc.setFont('helvetica','normal');
        doc.text(s.label, x+8, y+19);
    });

    doc.setFillColor(240,240,245);
    doc.rect(0,278,220,20,'F');
    doc.setTextColor(100,100,100);
    doc.setFontSize(8);
    doc.text('Amravati District Administration · Connect Amravati Portal', 14, 288);

    doc.save('Village_' + vil.village_name.replace(/\s+/g,'_') + '_Report.pdf');
}

function downloadDistReport() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation:'landscape' });

    doc.setFillColor(245,158,11);
    doc.rect(0,0,300,30,'F');
    doc.setTextColor(255,255,255);
    doc.setFontSize(16); doc.setFont('helvetica','bold');
    doc.text('CONNECT AMRAVATI — District Performance Report', 14, 14);
    doc.setFontSize(9); doc.setFont('helvetica','normal');
    doc.text('Amravati District · ' + reportDate + ' · All Talukas', 14, 22);

    doc.autoTable({
        startY: 38,
        head: [['#','Taluka','Total Tasks','Completed','In Progress','Pending','On Hold','Rate %']],
        body: distData.map((d,i) => [i+1, d.name, d.total, d.done, d.prog, d.pending, d.hold, d.rate+'%']),
        theme: 'grid',
        headStyles: { fillColor: [245,158,11], textColor:255, fontStyle:'bold', fontSize:9 },
        bodyStyles: { fontSize: 9 },
        alternateRowStyles: { fillColor: [255,253,245] },
    });

    doc.setFontSize(8); doc.setTextColor(120,120,120);
    doc.text('Confidential · Amravati District Administration · Connect Amravati Portal', 14, doc.lastAutoTable.finalY + 12);
    doc.save('District_Full_Performance_Report.pdf');
}

/* ── LUCIDE + PROFILE ─────────────────────────────────────────── */
lucide.createIcons();
const profileBtn  = document.getElementById('profileDropdownBtn');
const profileMenu = document.getElementById('profileDropdownMenu');
if (profileBtn && profileMenu) {
    profileBtn.addEventListener('click', e => { e.stopPropagation(); profileMenu.classList.toggle('hidden'); });
    document.addEventListener('click', () => profileMenu.classList.add('hidden'));
}
</script>

<?php include 'include/footer.php'; ?>
