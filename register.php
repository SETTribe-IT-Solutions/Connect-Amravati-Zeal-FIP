<?php
/**
 * register.php - Connect Amravati Self-Registration Module
 */
session_start();
require_once 'include/dbConfig.php';

// Language Toggle Setup (Support Marathi & English)
$lang = isset($_GET['lang']) && $_GET['lang'] === 'mr' ? 'mr' : 'en';

$translations = [
    'en' => [
        'title' => 'Self Registration - Connect Amravati',
        'heading' => 'AMRAVATI CONNECT',
        'sub' => 'Official Online Self-Registration Portal',
        'personal_section' => '1. Personal & Identity Details',
        'official_section' => '2. Official & Designation Details',
        'address_section' => '3. Address Details',
        'login_section' => '4. Login Credentials',
        'lbl_first_name' => 'First Name *',
        'lbl_middle_name' => 'Middle Name (Optional)',
        'lbl_last_name' => 'Last Name *',
        'lbl_full_name' => 'Full Name (Auto-concatenated, Read-only)',
        'lbl_gender' => 'Gender *',
        'lbl_dob' => 'Date of Birth *',
        'lbl_mobile' => 'Mobile Number *',
        'lbl_alt_mobile' => 'Alternate Mobile (Optional)',
        'lbl_email' => 'Email ID *',
        'lbl_aadhaar' => 'Aadhaar / Government ID *',
        'lbl_photo' => 'Profile Photo (Optional)',
        'lbl_emp_code' => 'Employee Code (Auto-generated, Read-only)',
        'lbl_dept' => 'Department *',
        'lbl_role' => 'Role Applying For *',
        'lbl_taluka' => 'Official Taluka *',
        'lbl_village' => 'Official Village *',
        'lbl_joining' => 'Joining Date (Optional)',
        'lbl_reporting' => 'Reporting Office / Collector Office / Department Name *',
        'lbl_curr_addr' => 'Current Address *',
        'lbl_perm_addr' => 'Permanent Address *',
        'lbl_same_addr' => 'Same as Current Address',
        'lbl_state' => 'State',
        'lbl_district' => 'District',
        'lbl_city_taluka' => 'Taluka / City *',
        'lbl_city_village' => 'Village / City *',
        'lbl_pincode' => 'Pincode *',
        'lbl_username' => 'Username / Login ID *',
        'lbl_pass' => 'Password *',
        'lbl_confirm_pass' => 'Confirm Password *',
        'btn_verify' => 'Verify Details',
        'btn_submit' => 'Submit Registration',
        'btn_cancel' => 'Cancel / Back to Login',
        'select_dept' => '-- Select Department --',
        'select_role' => '-- Select Role --',
        'select_taluka' => '-- Select Taluka --',
        'select_village' => '-- Select Village --',
        'select_gender' => '-- Select Gender --',
        'opt_male' => 'Male',
        'opt_female' => 'Female',
        'opt_other' => 'Other',
        'msg_verified' => 'Your username and email address are unique. You can now submit your registration.',
        'msg_unverified' => 'Please verify your username and email address first by clicking \"Verify Details\".'
    ],
    'mr' => [
        'title' => 'स्वयं नोंदणी - अमरावती कनेक्ट',
        'heading' => 'अमरावती कनेक्ट',
        'sub' => 'अधिकृत ऑनलाइन स्वयं-नोंदणी पोर्टल',
        'personal_section' => '१. वैयक्तिक आणि ओळख तपशील',
        'official_section' => '२. अधिकृत आणि पद तपशील',
        'address_section' => '३. पत्त्याचा तपशील',
        'login_section' => '४. लॉगिन क्रेडेंशियल्स',
        'lbl_first_name' => 'पहिले नाव *',
        'lbl_middle_name' => 'मधले नाव (पर्यायी)',
        'lbl_last_name' => 'आडनाव *',
        'lbl_full_name' => 'पूर्ण नाव (स्वयंचलित, केवळ वाचण्यासाठी)',
        'lbl_gender' => 'लिंग *',
        'lbl_dob' => 'जन्मतारीख *',
        'lbl_mobile' => 'मोबाईल नंबर *',
        'lbl_alt_mobile' => 'पर्यायी मोबाईल (पर्यायी)',
        'lbl_email' => 'ईमेल आयडी *',
        'lbl_aadhaar' => 'आधार / सरकारी आयडी *',
        'lbl_photo' => 'प्रोफाइल फोटो (पर्यायी)',
        'lbl_emp_code' => 'कर्मचारी कोड (स्वयंचलित, केवळ वाचण्यासाठी)',
        'lbl_dept' => 'विभाग *',
        'lbl_role' => 'अर्ज केलेली भूमिका *',
        'lbl_taluka' => 'अधिकृत तालुका *',
        'lbl_village' => 'अधिकृत गाव *',
        'lbl_joining' => 'रुजू होण्याची तारीख (पर्यायी)',
        'lbl_reporting' => 'रिपोर्टिंग कार्यालय / जिल्हाधिकारी कार्यालय / विभाग *',
        'lbl_curr_addr' => 'सध्याचा पत्ता *',
        'lbl_perm_addr' => 'कायमचा पत्ता *',
        'lbl_same_addr' => 'सध्याच्या पत्त्यासारखाच',
        'lbl_state' => 'राज्य',
        'lbl_district' => 'जिल्हा',
        'lbl_city_taluka' => 'तालुका / शहर *',
        'lbl_city_village' => 'गाव / शहर *',
        'lbl_pincode' => 'पिनकोड *',
        'lbl_username' => 'वापरकर्ता नाव / लॉगिन आयडी *',
        'lbl_pass' => 'पासवर्ड *',
        'lbl_confirm_pass' => 'पासवर्डची पुष्टी करा *',
        'btn_verify' => 'तपशील सत्यापित करा',
        'btn_submit' => 'नोंदणी सबमिट करा',
        'btn_cancel' => 'रद्द करा / लॉगिनवर परत जा',
        'select_dept' => '-- विभाग निवडा --',
        'select_role' => '-- भूमिका निवडा --',
        'select_taluka' => '-- तालुका निवडा --',
        'select_village' => '-- गाव निवडा --',
        'select_gender' => '-- लिंग निवडा --',
        'opt_male' => 'पुरुष',
        'opt_female' => 'महिला',
        'opt_other' => 'इतर',
        'msg_verified' => 'तुमचे वापरकर्ता नाव आणि ईमेल आयडी अद्वितीय आहेत. आपण आता आपली नोंदणी सबमिट करू शकता.',
        'msg_unverified' => 'कृपया \"तपशील सत्यापित करा\" वर क्लिक करून प्रथम तुमचे वापरकर्ता नाव आणि ईमेल आयडी सत्यापित करा.'
    ]
];
$t = $translations[$lang];

// Fetch lists for dropdowns
$departments = $conn->query("SELECT department_id, department_name FROM departments WHERE status = 'Active' ORDER BY department_name ASC");
$rolesResult = $conn->query("SELECT role_id, role_name FROM roles WHERE status = 'Active' ORDER BY role_name ASC");
$talukas = $conn->query("SELECT taluka_id, taluka_name FROM talukas ORDER BY taluka_name ASC");
$villages = $conn->query("SELECT village_id, village_name, taluka_id FROM villages ORDER BY village_name ASC");

$rolesList = [];
if ($rolesResult) {
    while ($r = $rolesResult->fetch_assoc()) {
        $rolesList[] = $r;
    }
}

$allVillages = [];
if ($villages) {
    while ($v = $villages->fetch_assoc()) {
        $allVillages[] = $v;
    }
}

// Generate sequential ID for Employee Code prefixing
$nextSeq = 1;
$resSeq1 = $conn->query("SELECT MAX(id) as max_id FROM user_registration_requests");
$resSeq2 = $conn->query("SELECT MAX(user_id) as max_id FROM users");
$max1 = $resSeq1 ? (int)$resSeq1->fetch_assoc()['max_id'] : 0;
$max2 = $resSeq2 ? (int)$resSeq2->fetch_assoc()['max_id'] : 0;
$nextSeq = max($max1, $max2) + 1;
$nextSeqStr = str_pad($nextSeq, 5, '0', STR_PAD_LEFT);

$error = '';
$success = '';

// Handle POST Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_registration'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $applicant_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
    $applicant_name = preg_replace('/\s+/', ' ', $applicant_name); // Clean extra spaces
    
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $mobile = trim($_POST['mobile'] ?? '');
    $alternate_mobile = trim($_POST['alternate_mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $aadhaar = trim($_POST['aadhaar'] ?? '');
    
    $employee_code = trim($_POST['employee_code'] ?? '');
    $department_id = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $role_id = !empty($_POST['role_id']) ? (int)$_POST['role_id'] : null;
    $taluka_id = !empty($_POST['taluka_id']) ? (int)$_POST['taluka_id'] : null;
    $village_id = !empty($_POST['village_id']) ? (int)$_POST['village_id'] : null;
    // Validate joining_date: must be a proper YYYY-MM-DD date
    $joining_date_raw = trim($_POST['joining_date'] ?? '');
    $joining_date = null;
    if (!empty($joining_date_raw)) {
        $dt = DateTime::createFromFormat('Y-m-d', $joining_date_raw);
        if ($dt && $dt->format('Y-m-d') === $joining_date_raw) {
            $joining_date = $joining_date_raw;
        }
    }
    $reporting_office = trim($_POST['reporting_office'] ?? '');
    
    $current_address = trim($_POST['current_address'] ?? '');
    $permanent_address = trim($_POST['permanent_address'] ?? '');
    $state = trim($_POST['state'] ?? 'Maharashtra');
    $district = trim($_POST['district'] ?? 'Amravati');
    $address_taluka = trim($_POST['address_taluka'] ?? '');
    $address_village = trim($_POST['address_village'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Backend validation checks
    if (empty($first_name) || empty($last_name) || empty($gender) || empty($dob) || empty($mobile) || empty($email) || empty($aadhaar) || empty($employee_code) || empty($department_id) || empty($role_id) || empty($taluka_id) || empty($village_id) || empty($reporting_office) || empty($current_address) || empty($permanent_address) || empty($address_taluka) || empty($address_village) || empty($pincode) || empty($username) || empty($password)) {
        $error = "All mandatory fields (*) are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Password and Confirm Password do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Validate duplicates in Database
        $dup = false;
        $checkSql = "SELECT user_id FROM users WHERE email = ? OR employee_code = ? LIMIT 1";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Username or Email is already registered.";
            $dup = true;
        }
        $stmt->close();
        
        if (!$dup) {
            $checkReqSql = "SELECT id FROM user_registration_requests WHERE (email = ? OR username = ?) AND request_status = 'Pending' LIMIT 1";
            $stmt = $conn->prepare($checkReqSql);
            $stmt->bind_param("ss", $email, $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "A pending registration request already exists for this email or username.";
                $dup = true;
            }
            $stmt->close();
        }
        
        if (!$dup) {
            // Profile photo upload handling
            $profile_photo_path = null;
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/profile_photos/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($file_ext, $allowed_exts)) {
                    $new_filename = 'PHOTO_' . uniqid() . '.' . $file_ext;
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $new_filename)) {
                        $profile_photo_path = 'uploads/profile_photos/' . $new_filename;
                    }
                }
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert Registration Request
            $insertSql = "INSERT INTO user_registration_requests 
                (employee_code, first_name, middle_name, last_name, applicant_name, gender, dob, mobile, alternate_mobile, email, aadhaar, profile_photo, department_id, role_id, taluka_id, village_id, joining_date, reporting_office, username, password_hash, current_address, permanent_address, state, district, taluka_name, village_or_city, pincode, verify_status, request_status, registration_source, submitted_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Verified', 'Pending', 'Self Registration', NOW())";
                
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("ssssssssssssiiiisssssssssss", 
                $employee_code, $first_name, $middle_name, $last_name, $applicant_name, $gender, $dob, $mobile, $alternate_mobile, $email, $aadhaar, $profile_photo_path, 
                $department_id, $role_id, $taluka_id, $village_id, $joining_date, $reporting_office, $username, $password_hash, 
                $current_address, $permanent_address, $state, $district, $address_taluka, $address_village, $pincode
            );
            
            if ($stmt->execute()) {
                $requestId = $stmt->insert_id;
                $stmt->close();
                
                // Log Audit Log (guest action)
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                $browser = substr($_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN', 0, 255);
                $auditSql = "INSERT INTO audit_logs (user_id, module_name, action_name, record_id, old_value, new_value, ip_address, browser_details) VALUES (NULL, 'User', 'Self Registration Submitted', ?, NULL, 'Pending', ?, ?)";
                $stmtAudit = $conn->prepare($auditSql);
                $stmtAudit->bind_param("iss", $requestId, $ip, $browser);
                $stmtAudit->execute();
                $stmtAudit->close();
                
                // Send notifications to all active Admins & Collectors
                $adminRes = $conn->query("SELECT u.user_id FROM users u JOIN roles r ON u.role_id = r.role_id WHERE r.role_name IN ('Collector', 'System Administrator', 'Administrator') AND u.status = 'Active'");
                if ($adminRes) {
                    $notifTitle = "New Registration Request";
                    $notifMsg = "Self registration submitted by {$applicant_name} (Code: {$employee_code}). Approval required.";
                    $notifRedirect = "user_creation.php?tab=requests&req_id=" . $requestId;
                    
                    $stmtNotif = $conn->prepare("INSERT INTO notifications (notification_type, title, message, sender_id, receiver_id, status, redirect_url) VALUES ('System', ?, ?, NULL, ?, 'Unread', ?)");
                    while ($admin = $adminRes->fetch_assoc()) {
                        $adminId = (int)$admin['user_id'];
                        $stmtNotif->bind_param("ssis", $notifTitle, $notifMsg, $adminId, $notifRedirect);
                        $stmtNotif->execute();
                    }
                    $stmtNotif->close();
                }
                
                $_SESSION['registration_success'] = "Registration request submitted successfully! It is pending approval from the Collector / Administrator.";
                header("Location: login.php?lang=" . $lang);
                exit;
            } else {
                $error = "Error submitting registration request: " . $conn->error;
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($t['title']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], formal: ['Roboto', 'sans-serif'] },
                    colors: {
                        navy: {
                            50: '#e6f0fa', 100: '#cce1f5', 200: '#99c3eb', 300: '#66a5e1',
                            400: '#3387d7', 500: '#0069cd', 600: '#0054a4', 700: '#003f7b',
                            800: '#002a52', 900: '#001529'
                        },
                        saffron: { 50:'#fff3e0', 100:'#ffe0b2', 500:'#ef6c00', 600:'#e65100' }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(225, 144, 34, 0.2);
        }
        .form-input {
            border-radius: 0.5rem;
            border: 1.5px solid #cbd5e1;
            padding: 0.6rem 0.75rem;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            color: #1e293b;
            font-size: 0.875rem;
        }
        .form-input:focus {
            border-color: #0054a4;
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 84, 164, 0.15);
        }
        .section-header {
            border-left: 4px solid #ef6c00;
            padding-left: 0.75rem;
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col font-sans overflow-y-auto">
    <!-- National Tricolor Bar -->
    <div class="w-full h-1.5 bg-gradient-to-r from-[#FF9933] via-white to-[#138808] z-50 shrink-0"></div>

    <!-- Main Outer Container -->
    <div class="flex-1 flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-5xl glass-card rounded-2xl shadow-xl overflow-hidden my-4 border border-amber-200">
            <!-- Header Block -->
            <header class="bg-gradient-to-r from-navy-900 to-navy-800 text-white p-6 relative overflow-hidden flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <img src="assets/images/maharashtra_seal.jpg" alt="Seal of Maharashtra" class="h-16 w-auto brightness-200" style="mix-blend-mode: lighten;">
                    <div>
                        <h1 class="text-2xl font-black tracking-wide font-formal uppercase text-white"><?= htmlspecialchars($t['heading']) ?></h1>
                        <p class="text-xs text-slate-300 font-medium"><?= htmlspecialchars($t['sub']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="register.php?lang=<?= $lang === 'en' ? 'mr' : 'en' ?>" class="px-3 py-1.5 text-xs font-semibold bg-white/10 hover:bg-white/20 border border-white/20 rounded-lg transition-colors flex items-center gap-1.5">
                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                        <?= $lang === 'en' ? 'मराठी (MR)' : 'English (EN)' ?>
                    </a>
                    <a href="login.php?lang=<?= $lang ?>" class="px-3 py-1.5 text-xs font-semibold bg-navy-600 hover:bg-navy-500 border border-amber-500/30 rounded-lg transition-colors flex items-center gap-1">
                        <i data-lucide="log-in" class="w-3.5 h-3.5"></i> Login
                    </a>
                </div>
            </header>

            <div class="p-6 md:p-8">
                <!-- Session errors / success -->
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3 mb-6 shadow-sm">
                        <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5 text-red-500 shrink-0"></i>
                        <span class="text-sm font-medium"><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form id="registerForm" method="POST" enctype="multipart/form-data" class="space-y-8" autocomplete="off">
                    
                    <!-- SECTION 1: PERSONAL DETAILS -->
                    <div>
                        <h3 class="text-base font-bold text-slate-900 section-header mb-4 uppercase tracking-wide"><?= htmlspecialchars($t['personal_section']) ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_first_name']) ?></label>
                                <input type="text" name="first_name" id="first_name" required class="form-input w-full" placeholder="First Name">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_middle_name']) ?></label>
                                <input type="text" name="middle_name" id="middle_name" class="form-input w-full" placeholder="Middle Name">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_last_name']) ?></label>
                                <input type="text" name="last_name" id="last_name" required class="form-input w-full" placeholder="Last Name">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-600 mb-1.5"><?= htmlspecialchars($t['lbl_full_name']) ?></label>
                                <input type="text" name="applicant_name" id="applicant_name" readonly class="form-input w-full bg-slate-50 cursor-not-allowed font-medium text-slate-700">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_gender']) ?></label>
                                <select name="gender" required class="form-input w-full bg-white">
                                    <option value=""><?= htmlspecialchars($t['select_gender']) ?></option>
                                    <option value="Male"><?= htmlspecialchars($t['opt_male']) ?></option>
                                    <option value="Female"><?= htmlspecialchars($t['opt_female']) ?></option>
                                    <option value="Other"><?= htmlspecialchars($t['opt_other']) ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_dob']) ?></label>
                                <input type="date" name="dob" required class="form-input w-full">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_aadhaar']) ?></label>
                                <input type="text" name="aadhaar" required class="form-input w-full" placeholder="12-digit Aadhaar Number" pattern="\d{12}" title="Aadhaar must be a 12 digit number">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_mobile']) ?></label>
                                <input type="tel" name="mobile" required class="form-input w-full" placeholder="10-digit mobile" pattern="[6789]\d{9}" title="Please enter a valid 10-digit mobile number starting with 6,7,8 or 9">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-750 mb-1.5"><?= htmlspecialchars($t['lbl_alt_mobile']) ?></label>
                                <input type="tel" name="alternate_mobile" class="form-input w-full" placeholder="Alternate mobile" pattern="[6789]\d{9}">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_email']) ?></label>
                                <input type="email" name="email" id="email" required class="form-input w-full" placeholder="user@domain.gov.in">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-750 mb-1.5"><?= htmlspecialchars($t['lbl_photo']) ?></label>
                                <input type="file" name="profile_photo" accept="image/*" class="form-input w-full bg-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-navy-50 file:text-navy-700 hover:file:bg-navy-100">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: OFFICIAL DETAILS -->
                    <div>
                        <h3 class="text-base font-bold text-slate-900 section-header mb-4 uppercase tracking-wide"><?= htmlspecialchars($t['official_section']) ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5"><?= htmlspecialchars($t['lbl_emp_code']) ?></label>
                                <input type="text" name="employee_code" id="employee_code" readonly class="form-input w-full bg-slate-50 cursor-not-allowed font-bold text-navy-800">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_dept']) ?></label>
                                <select name="department_id" required class="form-input w-full bg-white">
                                    <option value=""><?= htmlspecialchars($t['select_dept']) ?></option>
                                    <?php while ($dept = $departments->fetch_assoc()): ?>
                                        <option value="<?= $dept['department_id'] ?>"><?= htmlspecialchars($dept['department_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_role']) ?></label>
                                <select name="role_id" id="role_id" required class="form-input w-full bg-white" onchange="updateEmployeeCode()">
                                    <option value=""><?= htmlspecialchars($t['select_role']) ?></option>
                                    <?php foreach ($rolesList as $role): ?>
                                        <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_taluka']) ?></label>
                                <select name="taluka_id" id="taluka_id" required class="form-input w-full bg-white">
                                    <option value=""><?= htmlspecialchars($t['select_taluka']) ?></option>
                                    <?php while ($taluka = $talukas->fetch_assoc()): ?>
                                        <option value="<?= $taluka['taluka_id'] ?>"><?= htmlspecialchars($taluka['taluka_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_village']) ?></label>
                                <select name="village_id" id="village_id" required class="form-input w-full bg-white">
                                    <option value=""><?= htmlspecialchars($t['select_village']) ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-750 mb-1.5"><?= htmlspecialchars($t['lbl_joining']) ?></label>
                                <input type="date" name="joining_date" class="form-input w-full">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_reporting']) ?></label>
                                <input type="text" name="reporting_office" required class="form-input w-full" placeholder="e.g. Collector Office, Amravati or Department Name">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: ADDRESS DETAILS -->
                    <div>
                        <h3 class="text-base font-bold text-slate-900 section-header mb-4 uppercase tracking-wide"><?= htmlspecialchars($t['address_section']) ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_curr_addr']) ?></label>
                                <textarea name="current_address" id="current_address" required rows="3" class="form-input w-full" placeholder="House/Flat No., Street, Locality..."></textarea>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700"><?= htmlspecialchars($t['lbl_perm_addr']) ?></label>
                                    <label class="inline-flex items-center text-xs font-semibold text-navy-600 cursor-pointer">
                                        <input type="checkbox" id="same_address_check" class="rounded mr-1 text-navy-600 focus:ring-navy-500" onclick="copyAddress()">
                                        <?= htmlspecialchars($t['lbl_same_addr']) ?>
                                    </label>
                                </div>
                                <textarea name="permanent_address" id="permanent_address" required rows="3" class="form-input w-full" placeholder="House/Flat No., Street, Locality..."></textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5"><?= htmlspecialchars($t['lbl_state']) ?></label>
                                <input type="text" name="state" value="Maharashtra" readonly class="form-input w-full bg-slate-50 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1.5"><?= htmlspecialchars($t['lbl_district']) ?></label>
                                <input type="text" name="district" value="Amravati" readonly class="form-input w-full bg-slate-50 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_city_taluka']) ?></label>
                                <input type="text" name="address_taluka" required class="form-input w-full" placeholder="Taluka name">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_city_village']) ?></label>
                                <input type="text" name="address_village" required class="form-input w-full" placeholder="Village / City">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_pincode']) ?></label>
                                <input type="text" name="pincode" required class="form-input w-full" placeholder="6-digit PIN" pattern="\d{6}" title="Pincode must be exactly 6 digits">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: LOGIN CREDENTIALS -->
                    <div>
                        <h3 class="text-base font-bold text-slate-900 section-header mb-4 uppercase tracking-wide"><?= htmlspecialchars($t['login_section']) ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_username']) ?></label>
                                <input type="text" name="username" id="username" required class="form-input w-full" placeholder="Choose unique Username">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_pass']) ?></label>
                                <div class="relative">
                                    <input type="password" name="password" id="password" required minlength="6" class="form-input w-full pr-10" placeholder="••••••••">
                                    <button type="button" onclick="togglePass('password', 'pass_eye')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-navy-600">
                                        <i data-lucide="eye" id="pass_eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5"><?= htmlspecialchars($t['lbl_confirm_pass']) ?></label>
                                <div class="relative">
                                    <input type="password" name="confirm_password" id="confirm_password" required minlength="6" class="form-input w-full pr-10" placeholder="••••••••">
                                    <button type="button" onclick="togglePass('confirm_password', 'cpass_eye')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-navy-600">
                                        <i data-lucide="eye" id="cpass_eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex flex-col sm:flex-row justify-end items-center gap-3 pt-6 border-t border-slate-200">
                        <a href="login.php?lang=<?= $lang ?>" class="w-full sm:w-auto px-5 py-2.5 border border-slate-350 rounded-lg text-slate-700 font-semibold text-center hover:bg-slate-50 transition-colors">
                            <?= htmlspecialchars($t['btn_cancel']) ?>
                        </a>
                        <button type="button" onclick="verifyForm()" class="w-full sm:w-auto px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                            <?= htmlspecialchars($t['btn_verify']) ?>
                        </button>
                        <button type="submit" name="submit_registration" id="submit_btn" disabled class="w-full sm:w-auto px-6 py-2.5 bg-navy-700 hover:bg-navy-600 text-white font-black rounded-lg transition-colors flex items-center justify-center gap-1.5 shadow-md opacity-45 cursor-not-allowed pointer-events-none">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            <?= htmlspecialchars($t['btn_submit']) ?>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Script Block -->
    <script>
        // Init Lucide
        lucide.createIcons();

        // Autoconcat Full Name
        const fName = document.getElementById('first_name');
        const mName = document.getElementById('middle_name');
        const lName = document.getElementById('last_name');
        const fullName = document.getElementById('applicant_name');

        function updateFullName() {
            fullName.value = [fName.value, mName.value, lName.value]
                .map(v => v.trim())
                .filter(Boolean)
                .join(' ');
        }
        fName.addEventListener('input', updateFullName);
        mName.addEventListener('input', updateFullName);
        lName.addEventListener('input', updateFullName);

        // Address copy logic
        function copyAddress() {
            if (document.getElementById('same_address_check').checked) {
                document.getElementById('permanent_address').value = document.getElementById('current_address').value;
            }
        }
        document.getElementById('current_address').addEventListener('input', () => {
            if (document.getElementById('same_address_check').checked) {
                document.getElementById('permanent_address').value = document.getElementById('current_address').value;
            }
        });

        // Dynamic Employee Code Generation logic
        const rolesData = <?= json_encode($rolesList) ?>;
        const nextSeq = "<?= $nextSeqStr ?>";
        const empCodeEl = document.getElementById('employee_code');

        function updateEmployeeCode() {
            const roleId = document.getElementById('role_id').value;
            const role = rolesData.find(r => r.role_id == roleId);
            let prefix = 'USR';
            if (role) {
                const name = role.role_name.toLowerCase();
                if (name.includes('collector') || name.includes('sdo') || name.includes('tehsildar') || name.includes('bdo') || name.includes('officer')) {
                    prefix = 'OFF';
                } else if (name.includes('talathi') || name.includes('gramsevak') || name.includes('clerk') || name.includes('staff') || name.includes('employee')) {
                    prefix = 'EMP';
                }
            }
            empCodeEl.value = roleId ? (prefix + nextSeq) : '';
        }

        // Dynamic Village population based on Taluka
        const allVillages = <?= json_encode($allVillages) ?>;
        const talukaSelect = document.getElementById('taluka_id');
        const villageSelect = document.getElementById('village_id');

        talukaSelect.addEventListener('change', () => {
            const tVal = talukaSelect.value;
            villageSelect.innerHTML = '<option value=""><?= htmlspecialchars($t['select_village']) ?></option>';
            if (tVal) {
                const filtered = allVillages.filter(v => v.taluka_id == tVal);
                filtered.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.village_id;
                    opt.textContent = v.village_name;
                    villageSelect.appendChild(opt);
                });
            }
        });

        // Toggle passwords
        function togglePass(id, eyeId) {
            const el = document.getElementById(id);
            const eye = document.getElementById(eyeId);
            el.type = el.type === 'password' ? 'text' : 'password';
            eye.setAttribute('data-lucide', el.type === 'password' ? 'eye' : 'eye-off');
            lucide.createIcons();
        }

        // Form fields change listener to reset verification state
        let isVerified = false;
        const inputsToWatch = ['email', 'username', 'password', 'confirm_password'];
        inputsToWatch.forEach(id => {
            document.getElementById(id).addEventListener('input', () => {
                if (isVerified) {
                    isVerified = false;
                    const submitBtn = document.getElementById('submit_btn');
                    submitBtn.disabled = true;
                    submitBtn.classList.add('opacity-45', 'cursor-not-allowed', 'pointer-events-none');
                }
            });
        });

        // Verification Flow
        function verifyForm() {
            const form = document.getElementById('registerForm');
            
            // Check native HTML5 validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Check passwords match
            const pass = document.getElementById('password').value;
            const cpass = document.getElementById('confirm_password').value;
            if (pass !== cpass) {
                Swal.fire({
                    icon: 'error',
                    title: 'Password Mismatch',
                    text: 'Password and Confirm Password fields must match.',
                    confirmButtonColor: '#0054a4'
                });
                return;
            }

            if (pass.length < 6) {
                Swal.fire({
                    icon: 'error',
                    title: 'Weak Password',
                    text: 'Password must be at least 6 characters long.',
                    confirmButtonColor: '#0054a4'
                });
                return;
            }

            const emailVal = document.getElementById('email').value;
            const userVal = document.getElementById('username').value;

            // Trigger AJAX duplicate validation check
            Swal.fire({
                title: 'Verifying details...',
                text: 'Checking uniqueness of username and email address...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('api/verify_registration.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: emailVal, username: userVal })
            })
            .then(res => res.json())
            .then(res => {
                Swal.close();
                if (res.status === 'success') {
                    isVerified = true;
                    const submitBtn = document.getElementById('submit_btn');
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-45', 'cursor-not-allowed', 'pointer-events-none');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Verification Successful',
                        text: '<?= htmlspecialchars($t['msg_verified']) ?>',
                        confirmButtonColor: '#1b5e20'
                    });
                } else {
                    isVerified = false;
                    Swal.fire({
                        icon: 'error',
                        title: 'Verification Failed',
                        text: res.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'An error occurred during network communication. Please try again.',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        // Warn before submit if not verified
        document.getElementById('registerForm').addEventListener('submit', (e) => {
            if (!isVerified) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Verification Required',
                    text: '<?= htmlspecialchars($t['msg_unverified']) ?>',
                    confirmButtonColor: '#ef6c00'
                });
            }
        });
    </script>
</body>
</html>
