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
        'title' => 'Document Management - Connect Amravati',
        'page_title' => 'Document Management',
        'page_subtitle' => 'Access and upload task documents, system notification attachments, appreciations, and classified files.',
        'btn_upload' => 'Upload Classified Document',
        'lbl_subject' => 'Document Subject *',
        'lbl_desc' => 'Description / Abstract',
        'lbl_class' => 'Classification Level *',
        'lbl_audience' => 'Target Audience *',
        'lbl_file' => 'Select File *',
        'tab_all' => 'All Documents',
        'tab_confidential' => 'Confidential & Classified',
        'tab_notifications' => 'Notifications & Alerts',
        'tab_appreciations' => 'Appreciation Certificates'
    ],
    'mr' => [
        'title' => 'दस्तऐवज व्यवस्थापन - अमरावती कनेक्ट',
        'page_title' => 'दस्तऐवज व्यवस्थापन',
        'page_subtitle' => 'कार्य दस्तऐवज, सूचना जोडणी, प्रशंसा प्रमाणपत्रे आणि वर्गीकृत फायली पहा आणि अपलोड करा.',
        'btn_upload' => 'वर्गीकृत दस्तऐवज अपलोड करा',
        'lbl_subject' => 'दस्तऐवज विषय *',
        'lbl_desc' => 'वर्णन / सारांश',
        'lbl_class' => 'वर्गीकरण स्तर *',
        'lbl_audience' => 'लक्षित प्रेक्षक *',
        'lbl_file' => 'फाइल निवडा *',
        'tab_all' => 'सर्व दस्तऐवज',
        'tab_confidential' => 'गोपनीय आणि वर्गीकृत',
        'tab_notifications' => 'सूचना जोडणी',
        'tab_appreciations' => 'प्रशंसा प्रमाणपत्रे'
    ]
];

$t = $translations[$lang];

$alert_msg = '';
$alert_type = '';

// Handle Classified Document Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $classification = $_POST['classification_level'] ?? 'Confidential';
    $audience = $_POST['audience_type'] ?? 'All';
    
    if (!empty($subject) && isset($_FILES['file_doc']) && $_FILES['file_doc']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/documents/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['file_doc']['name'], PATHINFO_EXTENSION));
        $filename = 'DOC_' . uniqid() . '.' . $file_ext;
        
        if (move_uploaded_file($_FILES['file_doc']['tmp_name'], $upload_dir . $filename)) {
            $file_path = 'uploads/documents/' . $filename;
            
            try {
                $stmt = $conn->prepare("INSERT INTO confidential_documents (subject, description, classification_level, file_path, allow_download, allow_view, audience_type, created_by) VALUES (?, ?, ?, ?, 1, 1, ?, ?)");
                $stmt->bind_param("sssssi", $subject, $description, $classification, $file_path, $audience, $userId);
                
                if ($stmt->execute()) {
                    $alert_msg = "Classified document uploaded and secured successfully!";
                    $alert_type = "success";
                } else {
                    $alert_msg = "Database insert error: " . $conn->error;
                    $alert_type = "error";
                }
                $stmt->close();
            } catch (Throwable $e) {
                $alert_msg = "Error securing document: " . $e->getMessage();
                $alert_type = "error";
            }
        } else {
            $alert_msg = "Failed to move uploaded file.";
            $alert_type = "error";
        }
    } else {
        $alert_msg = "Please provide document subject and select a valid file.";
        $alert_type = "warning";
    }
}

// 1. Fetch Confidential Documents (Secured via classification & audience check)
$confidentialDocs = [];
$confQuery = "
    SELECT cd.*, u.full_name AS creator_name
    FROM confidential_documents cd
    LEFT JOIN users u ON cd.created_by = u.user_id
    WHERE cd.created_by = $userId
       OR cd.audience_type = 'All'
       OR (cd.audience_type = 'L1' AND $level = 1)
       OR (cd.audience_type = 'L2' AND $level <= 2)
       OR (cd.audience_type = 'L3' AND $level <= 3)
       OR (cd.audience_type = 'Custom' AND cd.document_id IN (SELECT document_id FROM confidential_document_audience WHERE user_id = $userId))
    ORDER BY cd.created_at DESC
";
$resConf = $conn->query($confQuery);
if ($resConf) {
    while ($row = $resConf->fetch_assoc()) {
        $confidentialDocs[] = $row;
    }
}

// 2. Fetch Notification attachments
$notificationDocs = [];
$notifQuery = "
    SELECT n.notification_id, n.title, n.message, n.attachment_path, n.created_at, u.full_name AS sender_name
    FROM notifications n
    LEFT JOIN users u ON n.sender_id = u.user_id
    WHERE n.attachment_path IS NOT NULL AND n.attachment_path != '' AND (n.receiver_id = $userId OR n.sender_id = $userId)
    ORDER BY n.created_at DESC
";
$resNotif = $conn->query($notifQuery);
if ($resNotif) {
    while ($row = $resNotif->fetch_assoc()) {
        $notificationDocs[] = $row;
    }
}

// 3. Fetch Appreciation Certificates
$appreciationDocs = [];
$appQuery = "
    SELECT ac.certificate_id AS appreciation_id, ac.award_type AS category, ac.description AS message, ac.certificate_file AS template_type, ac.issue_date AS created_at,
           recip.full_name AS recipient_name, sender.full_name AS sender_name
    FROM appreciation_certificates ac
    LEFT JOIN users recip ON ac.employee_id = recip.user_id
    LEFT JOIN users sender ON ac.issued_by = sender.user_id
    WHERE ac.employee_id = $userId OR ac.issued_by = $userId
    ORDER BY ac.issue_date DESC
";
$resApp = $conn->query($appQuery);
if ($resApp) {
    while ($row = $resApp->fetch_assoc()) {
        $appreciationDocs[] = $row;
    }
}

include 'include/header.php';
$activePage = 'documents';
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
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white shadow-sm">
                        <?= strtoupper(substr($sName, 0, 1)) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 z-50">
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
                <button onclick="openUploadModal()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-navy-600 text-white rounded-lg text-sm font-semibold shadow-md transition-colors flex items-center gap-2">
                    <i data-lucide="upload" class="w-4 h-4"></i>
                    <?= htmlspecialchars($t['btn_upload']) ?>
                </button>
            </div>
        </div>

        <!-- TABS BAR -->
        <div class="border-b border-slate-200 dark:border-slate-700 mb-6 overflow-x-auto">
            <nav class="flex space-x-6 min-w-max">
                <button onclick="switchTab('conf')" id="tbtn-conf" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-navy-500 text-navy-600 font-bold dark:border-blue-400 dark:text-blue-400"><?= htmlspecialchars($t['tab_confidential']) ?></button>
                <button onclick="switchTab('notif')" id="tbtn-notif" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400"><?= htmlspecialchars($t['tab_notifications']) ?></button>
                <button onclick="switchTab('apprec')" id="tbtn-apprec" class="tab-btn border-b-2 py-4 px-1 text-sm font-medium border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400"><?= htmlspecialchars($t['tab_appreciations']) ?></button>
            </nav>
        </div>

        <!-- SECTION A: Confidential Documents -->
        <div id="sec-conf" class="tab-section">
            <div class="glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md bg-white dark:bg-slate-950 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-850">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Document Subject</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Classification</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Audience</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Uploaded By</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date Securing</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850/60">
                            <?php if (empty($confidentialDocs)): ?>
                                <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-slate-400 italic">No confidential documents secured for your tier.</td></tr>
                            <?php else: ?>
                                <?php foreach ($confidentialDocs as $doc): ?>
                                    <?php
                                    $classColor = match($doc['classification_level']) {
                                        'Highly Confidential' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-950/20 dark:text-red-400 dark:border-red-900',
                                        'Confidential' => 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-950/20 dark:text-orange-400 dark:border-orange-900',
                                        'Internal' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900',
                                        default => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900'
                                    };
                                    ?>
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-850 dark:text-white"><?= htmlspecialchars($doc['subject']) ?></div>
                                            <div class="text-xs text-slate-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($doc['description']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2.5 py-0.5 inline-flex text-xs leading-5 font-bold rounded-full border <?= $classColor ?>"><?= htmlspecialchars($doc['classification_level']) ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase">Tier: <?= htmlspecialchars($doc['audience_type']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-550 dark:text-slate-400"><?= htmlspecialchars($doc['creator_name'] ? $doc['creator_name'] : 'System') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400"><?= date('d M Y, h:i A', strtotime($doc['created_at'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                            <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 dark:hover:bg-indigo-900/40 rounded-lg font-bold transition-all inline-flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Open / View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION B: Notifications & Alerts Documents -->
        <div id="sec-notif" class="tab-section hidden">
            <div class="glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md bg-white dark:bg-slate-950 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-855">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Attachment Context</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Sender</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date & Time Received</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-855/60">
                            <?php if (empty($notificationDocs)): ?>
                                <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400 italic">No attachments found in your alerts list.</td></tr>
                            <?php else: ?>
                                <?php foreach ($notificationDocs as $doc): ?>
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-850 dark:text-white"><?= htmlspecialchars($doc['title']) ?></div>
                                            <div class="text-xs text-slate-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($doc['message']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-slate-550 dark:text-slate-400"><?= htmlspecialchars($doc['sender_name'] ? $doc['sender_name'] : 'System') ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500 dark:text-slate-400"><?= date('d M Y, h:i A', strtotime($doc['created_at'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                            <a href="<?= htmlspecialchars($doc['attachment_path']) ?>" target="_blank" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400 dark:hover:bg-blue-900/40 rounded-lg font-bold transition-all inline-flex items-center gap-1">
                                                <i data-lucide="download" class="w-3.5 h-3.5"></i> Download File
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION C: Appreciation Documents -->
        <div id="sec-apprec" class="tab-section hidden">
            <div class="glass-panel rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-md bg-white dark:bg-slate-950 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-855">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Recipient Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Honor / Category</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Awarded By</th>
                                <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Date Granted</th>
                                <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-855/60">
                            <?php if (empty($appreciationDocs)): ?>
                                <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-400 italic">No appreciation certificates recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($appreciationDocs as $doc): ?>
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 text-sm">
                                        <td class="px-6 py-4 font-bold text-slate-850 dark:text-white"><?= htmlspecialchars($doc['recipient_name']) ?></td>
                                        <td class="px-6 py-4 font-semibold text-amber-600 dark:text-amber-400"><?= htmlspecialchars($doc['category']) ?></td>
                                        <td class="px-6 py-4 text-xs font-semibold text-slate-550 dark:text-slate-400"><?= htmlspecialchars($doc['sender_name'] ? $doc['sender_name'] : 'System') ?></td>
                                        <td class="px-6 py-4 text-xs text-slate-500 dark:text-slate-400"><?= date('d M Y', strtotime($doc['created_at'])) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-medium">
                                            <a href="appreciations.php?lang=<?= $lang ?>&view_id=<?= $doc['appreciation_id'] ?>" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 dark:bg-amber-950/30 dark:text-amber-450 dark:hover:bg-amber-900/40 rounded-lg font-bold transition-all inline-flex items-center gap-1">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> View Certificate
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
</div>

<!-- =========================================================================
     MODAL: Upload Classified Document
     ========================================================================= -->
<div id="uploadDocModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center hidden">
    <div class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-700 m-4">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-navy-600 text-white flex justify-between items-center">
            <h3 class="font-bold text-lg">Upload Secured Document</h3>
            <button type="button" onclick="closeUploadModal()" class="text-white hover:opacity-80"><i data-lucide="x" class="w-6 h-6"></i></button>
        </div>
        
        <form action="documents.php?lang=<?= $lang ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="upload_document" value="1">
            
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_subject']) ?></label>
                <input type="text" name="subject" required placeholder="e.g. Annual Amravati Development Plan" class="block w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_desc']) ?></label>
                <textarea name="description" rows="3" placeholder="Brief summary of document content..." class="block w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_class']) ?></label>
                    <select name="classification_level" class="block w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="Confidential">Confidential</option>
                        <option value="Highly Confidential">Highly Confidential</option>
                        <option value="Internal">Internal</option>
                        <option value="Public">Public</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_audience']) ?></label>
                    <select name="audience_type" class="block w-full border border-slate-200 dark:border-slate-700 dark:bg-slate-900 dark:text-white rounded-xl p-3 text-xs focus:ring-2 focus:ring-indigo-500">
                        <option value="All">All Tiers</option>
                        <option value="L1">L1 Officers Only</option>
                        <option value="L2">L1 & L2 Tiers</option>
                        <option value="L3">All Levels (L3)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1.5"><?= htmlspecialchars($t['lbl_file']) ?></label>
                <input type="file" name="file_doc" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-slate-200 dark:border-slate-700 rounded-xl p-1.5 dark:bg-slate-900">
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-650 dark:text-slate-350 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-850">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-1.5"><i data-lucide="upload" class="w-4 h-4"></i> Secure Upload</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Lucide Icons
    lucide.createIcons();

    // SweetAlert triggers
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

    // Modal view
    function openUploadModal() {
        document.getElementById('uploadDocModal').classList.remove('hidden');
    }
    function closeUploadModal() {
        document.getElementById('uploadDocModal').classList.add('hidden');
    }

    // Tab switcher
    function switchTab(tabId) {
        // Toggle tab highlights
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
            btn.classList.add('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
        });
        const activeBtn = document.getElementById('tbtn-' + tabId);
        if(activeBtn) {
            activeBtn.classList.add('border-navy-500', 'text-navy-600', 'font-bold', 'dark:border-blue-400', 'dark:text-blue-400');
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'hover:text-slate-700', 'dark:text-slate-400');
        }

        // Toggle visibility
        document.querySelectorAll('.tab-section').forEach(sec => sec.classList.add('hidden'));
        document.getElementById('sec-' + tabId).classList.remove('hidden');
    }
</script>
<?php include 'include/footer.php'; ?>
