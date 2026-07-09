<?php
session_start();
require_once 'include/dbConfig.php';
require_once 'include/mailer.php';

// Auth Check
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];
$sRole  = $_SESSION['user_role'] ?? 'L3';
$sName  = $_SESSION['user_name'] ?? 'User';

// Restrict to Admin & Collector
if (!in_array($sRole, ['Administrator', 'System Administrator', 'Collector'])) {
    header("Location: " . app_url("dashboard.php"));
    exit;
}

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
        'title' => 'Appreciation Center - Connect Amravati',
        'page_title' => 'Appreciation Center',
        'page_subtitle' => 'Recognize and view outstanding work, certificates of completion, and professional awards.',
        'btn_submit' => 'Issue Appreciation Certificate',
        'card_give' => 'Give Appreciation',
        'card_my' => 'My Certificates & Honors',
        'no_appreciations' => 'No appreciations or certificates received yet.',
        'lbl_recipient' => 'Select Recipient / Officer *',
        'lbl_category' => 'Award Category *',
        'lbl_template' => 'Certificate Style Template *',
        'lbl_message' => 'Appreciation Citation / Message *',
        'lbl_task' => 'Select Associated Task (Optional)',
        'success_sent' => 'Appreciation certificate issued and email alert dispatched successfully!'
    ],
    'mr' => [
        'title' => 'कौतुक केंद्र - अमरावती कनेक्ट',
        'page_title' => 'कौतुक केंद्र',
        'page_subtitle' => 'उत्कृष्ट काम, पूर्णत्वाचे प्रमाणपत्र आणि व्यावसायिक पुरस्कारांची देवाणघेवाण करा.',
        'btn_submit' => 'प्रशंसा प्रमाणपत्र जारी करा',
        'card_give' => 'प्रशंसा द्या',
        'card_my' => 'माझे प्रमाणपत्र आणि सन्मान',
        'no_appreciations' => 'अद्याप कोणतेही प्रशंसा प्रमाणपत्र मिळालेले नाही.',
        'lbl_recipient' => 'प्राप्तकर्ता / अधिकारी निवडा *',
        'lbl_category' => 'पुरस्कार श्रेणी *',
        'lbl_template' => 'प्रमाणपत्र शैली टेम्पलेट *',
        'lbl_message' => 'प्रशंसा संदेश / वर्णन *',
        'lbl_task' => 'संबंधित कार्य निवडा (पर्यायी)',
        'success_sent' => 'प्रशंसा प्रमाणपत्र यशस्वीरित्या जारी केले आणि ईमेल पाठवला!'
    ]
];

$t = $translations[$lang];

// Process Give Appreciation Form
$alert_msg = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['give_appreciation'])) {
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $template = trim($_POST['template_type'] ?? 'Classic');
    $message = trim($_POST['message'] ?? '');
    $taskId = !empty($_POST['task_id']) ? (int)$_POST['task_id'] : null;

    if ($recipientId > 0 && !empty($category) && !empty($message)) {
        try {
            // Save appreciation to database
            $certNo = 'CERT-' . strtoupper(uniqid());
            $stmt = $conn->prepare("INSERT INTO appreciation_certificates (certificate_no, employee_id, award_type, achievement_title, description, issued_by, issue_date, certificate_file, status) VALUES (?, ?, ?, 'Work Appreciation', ?, ?, NOW(), ?, 'Active')");
            $stmt->bind_param("sisiss", $certNo, $recipientId, $category, $message, $userId, $template);
            
            if ($stmt->execute()) {
                $appreciationId = $stmt->insert_id;
                $stmt->close();

                // Create System Notification
                $notifTitle = "Certificate of Completion: $category";
                $notifMsg = "You have been awarded a Work Appreciation certificate by $sName ($sRole) for: $message";
                
                // Set attachment path link pointing to the appreciations page
                $notifAttachment = "appreciations.php?view_id=" . $appreciationId;
                
                $stmtNotif = $conn->prepare("INSERT INTO notifications (notification_type, title, message, sender_id, receiver_id, status, attachment_path, certificate_id, redirect_url) VALUES ('System', ?, ?, ?, ?, 'Unread', ?, ?, ?)");
                $stmtNotif->bind_param("ssiisis", $notifTitle, $notifMsg, $userId, $recipientId, $notifAttachment, $appreciationId, $notifAttachment);
                $stmtNotif->execute();
                $stmtNotif->close();

                // Fetch Recipient Email details
                $resRecip = $conn->query("SELECT full_name, email FROM users WHERE user_id = $recipientId LIMIT 1");
                $recipientUser = $resRecip ? $resRecip->fetch_assoc() : null;

                $alert_msg = $t['success_sent'];
                $alert_type = 'success';

                if ($recipientUser && !empty($recipientUser['email'])) {
                    $toEmail = $recipientUser['email'];
                    $toName = $recipientUser['full_name'];
                    $subject = "CONGRATULATIONS: Work Appreciation Certificate Received!";
                    $year = date('Y');
                    $formattedDate = date('d M Y');

                    // Beautiful HTML email representing the certificate
                    $emailHtml = "
                    <!DOCTYPE html>
                    <html><head><meta charset='UTF-8'></head>
                    <body style='margin:0;padding:0;background:#f8fafc;font-family:Georgia,serif;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;background:#f8fafc;'>
                    <tr><td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background:#fff;border:10px double #d4af37;border-radius:8px;box-shadow:0 8px 30px rgba(0,0,0,0.15);max-width:600px;text-align:center;'>
                          <tr><td style='padding:40px 32px;'>
                            <div style='font-size:28px;color:#d4af37;letter-spacing:4px;text-transform:uppercase;'>Certificate of Appreciation</div>
                            <div style='font-size:12px;color:#64748b;letter-spacing:2px;text-transform:uppercase;margin-top:5px;'>CONNECT AMRAVATI PORTAL</div>
                            
                            <hr style='border:none;border-top:1px solid #e2e8f0;width:80%;margin:25px auto;'>
                            
                            <p style='font-size:15px;color:#475569;font-style:italic;margin:20px 0 10px;'>This certificate is proudly presented to</p>
                            <h2 style='font-size:24px;color:#1e3a8a;margin:0 0 10px;font-weight:700;'>$toName</h2>
                            
                            <p style='font-size:14px;color:#475569;max-width:450px;margin:15px auto;line-height:1.6;'>
                                In recognition of outstanding commitment and excellent performance in the category of <strong>$category</strong>.
                            </p>
                            
                            <p style='font-size:13px;color:#64748b;background:#fdfbeb;border:1px solid #fef3c7;padding:12px 20px;border-radius:6px;max-width:480px;margin:20px auto;line-height:1.5;font-family:sans-serif;'>
                                &ldquo;$message&rdquo;
                            </p>
                            
                            <table width='100%' style='margin-top:35px;font-family:sans-serif;'>
                                <tr>
                                    <td width='50%' style='text-align:center;'>
                                        <div style='font-size:13px;color:#1e3a8a;font-weight:bold;'>$sName</div>
                                        <div style='font-size:11px;color:#64748b;margin-top:2px;'>$sRole</div>
                                    </td>
                                    <td width='50%' style='text-align:center;'>
                                        <div style='font-size:13px;color:#1e3a8a;font-weight:bold;'>$formattedDate</div>
                                        <div style='font-size:11px;color:#64748b;margin-top:2px;'>Date Issued</div>
                                    </td>
                                </tr>
                            </table>
                            
                          </td></tr>
                        </table>
                    </td></tr>
                    </table>
                    </body></html>
                    ";

                    if (SMTP_ENABLED) {
                        try {
                            send_smtp_email(
                                $toEmail, $subject, $emailHtml,
                                SMTP_USER, SMTP_FROM_NAME,
                                SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE
                            );
                        } catch (Throwable $mailEx) {
                            error_log("Failed to send SMTP email: " . $mailEx->getMessage());
                            $alert_msg = "Appreciation certificate issued successfully, but email notification could not be dispatched.";
                            $alert_type = 'warning';
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $alert_msg = "Error issuing appreciation: " . $e->getMessage();
            $alert_type = 'error';
        }
    } else {
        $alert_msg = "Please fill all mandatory fields.";
        $alert_type = 'warning';
    }
}

// Fetch Active Users List for Form Dropdown
$usersList = [];
$resUsers = $conn->query("SELECT user_id, full_name, employee_code, role_id FROM users WHERE status = 'Active' AND user_id != $userId ORDER BY full_name ASC");
if ($resUsers) {
    while ($uRow = $resUsers->fetch_assoc()) {
        $usersList[] = $uRow;
    }
}

// Tasks are loaded dynamically via AJAX based on selected recipient
$tasksList = [];

// Fetch Appreciations Received by Me
$myAppreciations = [];
$resMy = $conn->query("
    SELECT ac.certificate_id AS appreciation_id, ac.award_type AS category, ac.description AS message, ac.certificate_file AS template_type, ac.issue_date AS created_at,
           sender.full_name AS sender_name, r_sender.role_name AS sender_role
    FROM appreciation_certificates ac
    LEFT JOIN users sender ON ac.issued_by = sender.user_id
    LEFT JOIN roles r_sender ON sender.role_id = r_sender.role_id
    WHERE ac.employee_id = $userId
    ORDER BY ac.issue_date DESC, ac.certificate_id DESC
");
if ($resMy) {
    while ($mRow = $resMy->fetch_assoc()) {
        $myAppreciations[] = $mRow;
    }
}

// Fetch Appreciations Given by Me (if L1/L2)
$givenAppreciations = [];
$resGiven = $conn->query("
    SELECT ac.certificate_id AS appreciation_id, ac.award_type AS category, ac.description AS message, ac.certificate_file AS template_type, ac.issue_date AS created_at,
           recip.full_name AS recipient_name, r_recip.role_name AS recipient_role
    FROM appreciation_certificates ac
    LEFT JOIN users recip ON ac.employee_id = recip.user_id
    LEFT JOIN roles r_recip ON recip.role_id = r_recip.role_id
    WHERE ac.issued_by = $userId
    ORDER BY ac.issue_date DESC, ac.certificate_id DESC
");
if ($resGiven) {
    while ($gRow = $resGiven->fetch_assoc()) {
        $givenAppreciations[] = $gRow;
    }
}

// Safe Location display
include 'include/header.php';
$activePage = 'appreciations';
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
            <!-- Notifications Widget -->
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
    <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8 no-print">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight"><?= htmlspecialchars($t['page_title']) ?></h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= htmlspecialchars($t['page_subtitle']) ?></p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center space-x-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold badge-l<?= $level ?>">
                    <i data-lucide="shield" class="w-3.5 h-3.5"></i>
                    <?= htmlspecialchars($sRole) ?> (L<?= $level ?>)
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- SECTION A: Give Appreciation Form (Visible to L1 and L2 only) -->
            <?php if ($level <= 2): ?>
            <div class="lg:col-span-1 glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md p-6 h-fit bg-white dark:bg-slate-950">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                    <i data-lucide="award" class="w-5 h-5 text-indigo-500"></i>
                    <?= htmlspecialchars($t['card_give']) ?>
                </h2>

                <form action="appreciations.php?lang=<?= $lang ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="give_appreciation" value="1">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_recipient']) ?></label>
                        <select name="recipient_id" id="recipientSelect" required onchange="loadRecipientTasks(this.value)" class="block w-full border border-slate-200 dark:border-slate-850 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Select Recipient --</option>
                            <?php foreach ($usersList as $u): ?>
                                <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name'] . ' (' . $u['employee_code'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_category']) ?></label>
                        <select name="category" required class="block w-full border border-slate-200 dark:border-slate-850 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="Excellent Performance">Excellent Performance</option>
                            <option value="Leadership Excellence">Leadership Excellence</option>
                            <option value="Community Impact">Community Impact</option>
                            <option value="Fast Track Execution">Fast Track Execution</option>
                            <option value="Distinguished Public Service">Distinguished Public Service</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_template']) ?></label>
                        <select name="template_type" required class="block w-full border border-slate-200 dark:border-slate-850 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="Classic">Classic Gold Royal</option>
                            <option value="Modern">Modern Minimalist Navy</option>
                            <option value="Executive">Executive Star Crest</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_task']) ?></label>
                        <div class="relative">
                            <select name="task_id" id="taskSelect" disabled class="block w-full border border-slate-200 dark:border-slate-850 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">-- Select recipient first --</option>
                            </select>
                            <div id="taskLoading" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                                <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </div>
                        </div>
                        <p id="taskHint" class="text-[10px] text-slate-400 mt-1">Tasks will load after selecting a recipient.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_message']) ?></label>
                        <textarea name="message" required rows="4" placeholder="Detail the outstanding achievements of the officer..." class="block w-full border border-slate-200 dark:border-slate-850 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-navy-600 text-white rounded-xl text-sm font-semibold hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i data-lucide="award" class="w-4 h-4"></i>
                        <?= htmlspecialchars($t['btn_submit']) ?>
                    </button>
                </form>
            </div>
            <?php endif; ?>

            <!-- SECTION B: Certificates List (Visible to All) -->
            <div class="<?= ($level <= 2) ? 'lg:col-span-2' : 'lg:col-span-3' ?> space-y-6">
                
                <!-- MY RECEIVED CERTIFICATES -->
                <div class="glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md p-6 bg-white dark:bg-slate-950">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                        <i data-lucide="certificate" class="w-5 h-5 text-amber-500"></i>
                        <?= htmlspecialchars($t['card_my']) ?>
                    </h2>

                    <?php if (empty($myAppreciations)): ?>
                        <div class="py-12 text-center text-sm text-slate-500 italic"><?= htmlspecialchars($t['no_appreciations']) ?></div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach ($myAppreciations as $app): ?>
                                <div class="p-5 border border-slate-100 dark:border-slate-800/60 rounded-2xl flex flex-col justify-between bg-slate-50/50 dark:bg-slate-900/30 hover:border-amber-400/40 transition-all duration-150 relative overflow-hidden group">
                                    <div class="absolute -right-6 -top-6 w-20 h-20 bg-amber-500/10 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                        <i data-lucide="award" class="w-8 h-8 text-amber-500/70"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-1"><?= htmlspecialchars($app['category']) ?></div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white pr-8"><?= htmlspecialchars($app['sender_name']) ?></h3>
                                        <p class="text-xs text-slate-400 mb-3"><?= htmlspecialchars($app['sender_role'] ? $app['sender_role'] : 'Superior Officer') ?></p>
                                        <p class="text-xs text-slate-650 dark:text-slate-350 line-clamp-3 leading-relaxed mb-4 italic">"<?= htmlspecialchars($app['message']) ?>"</p>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 pt-3 mt-1">
                                        <span class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($app['created_at'])) ?></span>
                                        <button onclick="viewCertificate(<?= $app['appreciation_id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View Certificate
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ISSUED BY ME LIST (Visible to L1 and L2 only) -->
                <?php if ($level <= 2): ?>
                <div class="glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md p-6 bg-white dark:bg-slate-950">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                        <i data-lucide="send" class="w-5 h-5 text-indigo-500"></i>
                        Issued Appreciations & Certificates
                    </h2>
                    
                    <?php if (empty($givenAppreciations)): ?>
                        <div class="py-8 text-center text-sm text-slate-500 italic">No certificates issued by you yet.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-800">
                                <thead class="bg-slate-50 dark:bg-slate-900/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Recipient</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Award Category</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date Issued</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                    <?php foreach ($givenAppreciations as $gv): ?>
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-slate-900 dark:text-white"><?= htmlspecialchars($gv['recipient_name']) ?></div>
                                                <div class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($gv['recipient_role'] ? $gv['recipient_role'] : 'Officer') ?></div>
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-350"><?= htmlspecialchars($gv['category']) ?></td>
                                            <td class="px-4 py-3 text-xs text-slate-550 dark:text-slate-400"><?= date('d M Y', strtotime($gv['created_at'])) ?></td>
                                            <td class="px-4 py-3 text-right">
                                                <button onclick="viewCertificate(<?= $gv['appreciation_id'] ?>)" class="px-3 py-1.5 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 text-slate-700 dark:text-slate-300 dark:hover:bg-slate-850 rounded-lg text-xs font-bold transition-all inline-flex items-center gap-1">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </main>
</div>

<!-- =========================================================================
     MODAL: Premium Certificate Viewer with Printing Style Layouts
     ========================================================================= -->
<div id="certificateModal" class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-50 flex items-center justify-center hidden no-print">
    <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-800 m-4 flex flex-col max-h-[90vh]">
        
        <!-- Header Actions -->
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center no-print">
            <h3 class="font-bold text-slate-850 dark:text-white text-base">Appreciation Citation Viewer</h3>
            <div class="flex items-center space-x-2">
                <button type="button" onclick="printCertificate()" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-yellow-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm hover:opacity-90">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print / Save PDF
                </button>
                <button type="button" onclick="closeCertificate()" class="text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white p-1"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
        </div>

        <!-- Scrollable Certificate Canvas -->
        <div class="p-8 overflow-y-auto flex-1 bg-slate-100/50 dark:bg-slate-950/50 flex justify-center items-center">
            
            <!-- Canvas container -->
            <div id="print-canvas" class="w-full max-w-2xl bg-white text-slate-950 p-12 border-8 shadow-xl relative overflow-hidden transition-all duration-200">
                <!-- Inner design tags will be injected via JS -->
            </div>

        </div>
    </div>
</div>

<!-- Print-Only Page Wrapper -->
<div id="print-only-layout" class="hidden print:block absolute inset-0 bg-white text-black p-12">
    <!-- Injected print layout -->
</div>

<!-- Certificate styling configurations -->
<style>
    /* Classic Template style */
    .cert-classic {
        border-style: double;
        border-color: #c5a059;
        border-width: 10px;
        border-radius: 4px;
        font-family: Georgia, serif;
    }
    .cert-classic-seal {
        background: radial-gradient(circle, #fcd34d 0%, #d97706 100%);
        border: 2px dashed #b45309;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.4);
    }
    
    /* Modern Template style */
    .cert-modern {
        border: 4px solid #1e3a8a;
        background: radial-gradient(circle at top left, #f8fafc 0%, #ffffff 100%);
        font-family: sans-serif;
    }
    .cert-modern::before {
        content: "";
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 8px;
        background: linear-gradient(to right, #3b82f6, #1e3a8a);
    }
    .cert-modern-seal {
        background: #1e3a8a;
        border: 3px solid #3b82f6;
    }

    /* Executive Template style */
    .cert-executive {
        border: 6px double #334155;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        font-family: 'Playfair Display', serif;
    }
    .cert-executive-seal {
        background: radial-gradient(circle, #f8fafc 0%, #475569 100%);
        border: 2px solid #334155;
        transform: rotate(45deg);
    }

    @media print {
        body * {
            visibility: hidden;
        }
        #print-only-layout, #print-only-layout * {
            visibility: visible;
        }
        #print-only-layout {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: white !important;
            color: black !important;
        }
    }
</style>

<script>
    // Lucide Icons
    lucide.createIcons();

    // Trigger sweet alerts if set
    <?php if (!empty($alert_msg) && !empty($alert_type)): ?>
        Swal.fire({
            icon: '<?= $alert_type ?>',
            title: '<?= $alert_type === "success" ? "Success" : "Attention" ?>',
            text: '<?= htmlspecialchars(addslashes($alert_msg)) ?>',
            confirmButtonColor: '#4f46e5'
        });
    <?php endif; ?>

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

    // View Certificate dynamic injection
    function viewCertificate(id) {
        fetch('api/get_certificate_details.php?id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const canvas = document.getElementById('print-canvas');
                    const c = data.certificate;
                    const dateFormatted = new Date(c.created_at).toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });

                    let templateClass = '';
                    let sealHtml = '';
                    
                    if (c.template_type === 'Classic') {
                        templateClass = 'cert-classic';
                        sealHtml = `
                            <div class="w-16 h-16 rounded-full cert-classic-seal flex items-center justify-center mx-auto mb-2 relative">
                                <i data-lucide="award" class="w-8 h-8 text-white"></i>
                                <div class="absolute inset-0 rounded-full border border-white opacity-40 scale-90"></div>
                            </div>
                            <span class="text-[9px] uppercase font-bold tracking-widest text-amber-700">Official Seal</span>
                        `;
                    } else if (c.template_type === 'Modern') {
                        templateClass = 'cert-modern';
                        sealHtml = `
                            <div class="w-16 h-16 rounded-full cert-modern-seal flex items-center justify-center mx-auto mb-2">
                                <i data-lucide="check-circle" class="w-8 h-8 text-white animate-pulse"></i>
                            </div>
                            <span class="text-[9px] uppercase font-bold tracking-widest text-blue-600">Verification Seal</span>
                        `;
                    } else {
                        templateClass = 'cert-executive';
                        sealHtml = `
                            <div class="w-16 h-16 cert-executive-seal flex items-center justify-center mx-auto mb-2 shadow-md">
                                <i data-lucide="shield-check" class="w-7 h-7 text-white -rotate-45"></i>
                            </div>
                            <span class="text-[9px] uppercase font-bold tracking-widest text-slate-700">Executive Seal</span>
                        `;
                    }

                    const innerHtml = `
                        <div class="w-full text-center relative ${templateClass} p-8 min-h-[460px] flex flex-col justify-between">
                            <div>
                                <h3 class="text-3xl text-amber-600 font-bold uppercase tracking-widest Georgia">Certificate of Completion</h3>
                                <p class="text-slate-500 text-xs uppercase tracking-wider mt-1.5 font-bold font-sans">CONNECT AMRAVATI WORK RECOGNITION</p>
                                <div class="w-32 h-0.5 bg-amber-400 mx-auto my-5"></div>
                            </div>

                            <div>
                                <p class="text-sm italic text-slate-500 font-serif">This appreciation award is proudly presented to</p>
                                <h2 class="text-2xl font-extrabold text-navy-800 mt-2 font-serif">${c.recipient_name}</h2>
                                <p class="text-xs text-slate-400 font-sans mt-0.5">${c.recipient_role}</p>
                                
                                <p class="text-sm text-slate-700 max-w-lg mx-auto leading-relaxed mt-5 font-serif">
                                    In recognition of outstanding dedication, exceptional public service, and successful contribution in the category of <strong class="text-navy-700">${c.category}</strong>.
                                </p>

                                <div class="my-6 px-6 py-4 bg-slate-50 border border-slate-100 rounded-xl max-w-md mx-auto italic text-xs text-slate-650 text-center shadow-sm">
                                    "${c.message}"
                                </div>
                            </div>

                            <div class="flex justify-between items-end mt-4 px-4 font-sans">
                                <div class="text-left w-1/3">
                                    <div class="border-b border-slate-300 pb-1.5 text-sm font-bold text-slate-800">${c.sender_name}</div>
                                    <div class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">${c.sender_role}</div>
                                </div>

                                <div class="w-1/3 text-center">
                                    ${sealHtml}
                                </div>

                                <div class="text-right w-1/3">
                                    <div class="border-b border-slate-300 pb-1.5 text-sm font-bold text-slate-800">${dateFormatted}</div>
                                    <div class="text-[10px] text-slate-400 mt-1 uppercase font-semibold">Date Issued</div>
                                </div>
                            </div>
                        </div>
                    `;

                    canvas.innerHTML = innerHtml;
                    document.getElementById('print-only-layout').innerHTML = innerHtml;
                    lucide.createIcons();
                    document.getElementById('certificateModal').classList.remove('hidden');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: data.message
                    });
                }
            });
    }

    function closeCertificate() {
        document.getElementById('certificateModal').classList.add('hidden');
    }

    function printCertificate() {
        window.print();
    }

    // Auto view if view_id query param is present (notification redirection check)
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const viewId = urlParams.get('view_id');
        if (viewId) {
            viewCertificate(parseInt(viewId));
        }
    });

    // Dynamic task loader — fetches only tasks assigned to the selected recipient
    function loadRecipientTasks(recipientId) {
        const taskSelect  = document.getElementById('taskSelect');
        const taskLoading = document.getElementById('taskLoading');
        const taskHint    = document.getElementById('taskHint');

        if (!recipientId) {
            taskSelect.innerHTML = '<option value="">-- Select recipient first --</option>';
            taskSelect.disabled  = true;
            taskHint.textContent = 'Tasks will load after selecting a recipient.';
            return;
        }

        // Show spinner
        taskLoading.classList.remove('hidden');
        taskSelect.disabled = true;
        taskHint.textContent = 'Loading tasks...';

        fetch(`api/get_recipient_tasks.php?recipient_id=${recipientId}`)
            .then(res => res.json())
            .then(tasks => {
                taskLoading.classList.add('hidden');
                taskSelect.innerHTML = '';

                const defaultOpt = document.createElement('option');
                defaultOpt.value = '';
                defaultOpt.textContent = '-- None (no specific task) --';
                taskSelect.appendChild(defaultOpt);

                if (tasks.length === 0) {
                    taskHint.textContent = 'No tasks found assigned to this officer.';
                    taskSelect.disabled = false;
                    return;
                }

                tasks.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.task_id;
                    const statusLabel = t.status ? ` [${t.status}]` : '';
                    opt.textContent = `${t.task_no} — ${t.task_title}${statusLabel}`;
                    taskSelect.appendChild(opt);
                });

                taskSelect.disabled = false;
                taskHint.textContent = `${tasks.length} task(s) assigned to this officer.`;
            })
            .catch(() => {
                taskLoading.classList.add('hidden');
                taskSelect.disabled = false;
                taskHint.textContent = 'Error loading tasks. Please try again.';
            });
    }
</script>
<?php include 'include/footer.php'; ?>
