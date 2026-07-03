<?php
// include/header.php
// Common HTML <head> and standard government UI styling

$lang = $lang ?? 'en';
$pageTitle = $pageTitle ?? 'Amravati Connect | Government Portal';
$pageDesc = $pageDesc ?? 'Official District Administration Portal';

$current_page = basename($_SERVER['PHP_SELF']);
$is_auth_page = in_array($current_page, ['login.php', 'passwordReset.php', 'logout.php']);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Session Inactivity Timeout (3 minutes = 180 seconds) ──────────────────
define('SESSION_TIMEOUT_SECONDS', 180);

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT_SECONDS) {
        // Session has expired due to inactivity — destroy and redirect
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Location: login.php?auto_logout=1");
        exit;
    }
    // Update last activity timestamp on every page load
    $_SESSION['last_activity'] = time();
}

$headerDistrictName = 'Amravati';
$headerTalukaName = '';
$headerVillageName = '';

if (isset($_SESSION['user_id'])) {
    $distId = $_SESSION['district_id'] ?? 1;
    $talId = $_SESSION['taluka_id'] ?? null;
    $vilId = $_SESSION['village_id'] ?? null;
    
    if (isset($conn) && $conn instanceof mysqli) {
        $_SESSION['header_district_name'] = 'Amravati';
        
        if ($talId && empty($_SESSION['header_taluka_name'])) {
            $stmtTal = $conn->prepare("SELECT taluka_name FROM talukas WHERE taluka_id = ?");
            if ($stmtTal) {
                $stmtTal->bind_param("i", $talId);
                if ($stmtTal->execute()) {
                    $res = $stmtTal->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $_SESSION['header_taluka_name'] = $row['taluka_name'];
                    }
                }
                $stmtTal->close();
            }
        }
        
        if ($vilId && empty($_SESSION['header_village_name'])) {
            $stmtVil = $conn->prepare("SELECT village_name FROM villages WHERE village_id = ?");
            if ($stmtVil) {
                $stmtVil->bind_param("i", $vilId);
                if ($stmtVil->execute()) {
                    $res = $stmtVil->get_result();
                    if ($row = $res->fetch_assoc()) {
                        $_SESSION['header_village_name'] = $row['village_name'];
                    }
                }
                $stmtVil->close();
            }
        }
    }
    
    $headerDistrictName = $_SESSION['header_district_name'] ?? 'Amravati';
    $headerTalukaName = $_SESSION['header_taluka_name'] ?? '';
    $headerVillageName = $_SESSION['header_village_name'] ?? '';

    $userRole = $_SESSION['user_role'] ?? '';

    if (in_array($userRole, ['System Administrator', 'District Collector', 'Collector', 'Additional Collector', 'Deputy Collector', 'Administrator'])) {
        // Collector, Deputy, Admin: show only default district name Amravati
        $headerLocationDisplay = $headerDistrictName;
    } elseif (in_array($userRole, ['SDO', 'Tehsildar', 'BDO'])) {
        // BDO, SDO, Tehsildar: show Taluka
        $headerLocationDisplay = !empty($headerTalukaName) ? $headerTalukaName : $headerDistrictName;
    } elseif (in_array($userRole, ['Talathi', 'Gramsevak'])) {
        // Gramsevak, Talathi: show Village
        $headerLocationDisplay = !empty($headerVillageName) ? $headerVillageName : (!empty($headerTalukaName) ? $headerTalukaName : $headerDistrictName);
    } else {
        // Fallback
        $headerLocationDisplay = $headerDistrictName;
    }
} else {
    $headerLocationDisplay = 'Amravati';
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" class="light" id="htmlRoot">
<head>
    <script>
        (function() {
            const stored = localStorage.getItem('acTheme') || localStorage.getItem('theme');
            const prefersDark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            const htmlEl = document.documentElement;
            if (prefersDark) {
                htmlEl.classList.add('dark');
                htmlEl.classList.remove('light');
            } else {
                htmlEl.classList.remove('dark');
                htmlEl.classList.add('light');
            }
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Official look with Roboto and Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Optional: ApexCharts / Chart.js included on demand or globally -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- SweetAlert2 for attractive alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind config for Government theme -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['Inter', 'sans-serif'],
                        formal: ['Roboto', 'sans-serif']
                    },
                    colors: {
                        border:     "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        // Official Government Navy
                        navy: {
                            50: '#e6f0fa', 100: '#cce1f5', 200: '#99c3eb', 300: '#66a5e1',
                            400: '#3387d7', 500: '#0069cd', 600: '#0054a4', 700: '#003f7b',
                            800: '#002a52', 900: '#001529', 950: '#000b14'
                        },
                        // Official Forest Green
                        govgreen: { 
                            50:'#edf7ed', 100:'#cce8cc', 500:'#1b5e20', 600:'#144718' 
                        },
                        // Saffron/Orange
                        saffron:  { 
                            50:'#fff3e0', 100:'#ffe0b2', 500:'#ef6c00', 600:'#e65100' 
                        }
                    },
                    boxShadow: {
                        'official': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                        'official-hover': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                        'glow-navy': '0 0 15px rgba(0, 84, 164, 0.4)',
                        'glow-saffron': '0 0 15px rgba(239, 108, 0, 0.4)'
                    },
                    backgroundImage: {
                        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
                        'gradient-formal': 'linear-gradient(135deg, #001529 0%, #003f7b 100%)',
                    },
                    animation: {
                        'skeleton': 'skeleton 2s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.4s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards'
                    },
                    keyframes: {
                        skeleton: {
                            '0%, 100%': { opacity: 0.5 },
                            '50%': { opacity: 0.8 },
                        },
                        fadeIn: {
                            '0%': { opacity: 0 },
                            '100%': { opacity: 1 },
                        },
                        slideUp: {
                            '0%': { opacity: 0, transform: 'translateY(20px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root { 
            --background: 210 20% 98%; /* slate-50 */
            --foreground: 222.2 84% 4.9%; 
            --border: 214.3 31.8% 91.4%; 
        }
        .dark { 
            --background: 222.2 84% 4.9%; 
            --foreground: 210 40% 98%; 
            --border: 217.2 32.6% 17.5%; 
        }

        body { 
            font-family:'Inter', sans-serif; 
            background-color:hsl(var(--background)); 
            color:hsl(var(--foreground)); 
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .font-formal { font-family: 'Roboto', sans-serif; }

        /* Scrollbar */
        ::-webkit-scrollbar { width:8px; height:8px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }

        /* Custom Scrollbar for Sidebar (.scrollbar-thin) */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.02);
            border-radius: 3px;
        }
        .dark .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(0, 84, 164, 0.3); /* Translucent navy thumb */
            border-radius: 3px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #0054a4; /* Solid navy on hover */
        }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
        }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #66a5e1; /* Light blue on hover in dark mode */
        }

        /* Firefox support for scrollbar-thin */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 84, 164, 0.3) rgba(0, 0, 0, 0.02);
        }
        .dark .scrollbar-thin {
            scrollbar-color: rgba(255, 255, 255, 0.2) rgba(255, 255, 255, 0.02);
        }

        /* Advanced UI Elements */
        .glass-panel { 
            background: rgba(255,255,255,0.85); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.6); 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .dark .glass-panel { 
            background: rgba(15,23,42,0.85); 
            border: 1px solid rgba(255,255,255,0.08); 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }

        /* Hover Cards */
        .kpi-card { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.05);
            background: white;
            position: relative;
            overflow: hidden;
        }
        .dark .kpi-card {
            background: #1e293b;
            border-color: rgba(255,255,255,0.05);
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.4), transparent);
            transform: skewX(-20deg);
            transition: 0.5s;
        }
        .dark .kpi-card::before {
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.05), transparent);
        }
        .kpi-card:hover::before {
            left: 150%;
        }
        .kpi-card:hover { 
            transform: translateY(-4px) scale(1.01); 
            box-shadow: var(--tw-shadow-official-hover); 
            border-color: rgba(0, 84, 164, 0.2);
        }
        .dark .kpi-card:hover {
            border-color: rgba(102, 165, 225, 0.3);
        }

        /* Gradient Text */
        .text-gradient-navy {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-image: linear-gradient(to right, #003f7b, #0069cd);
        }
        .dark .text-gradient-navy {
            background-image: linear-gradient(to right, #66a5e1, #99c3eb);
        }

        /* Modern Form Inputs */
        :where(.input-modern, input[type="text"], input[type="password"], input[type="email"], input[type="number"], input[type="datetime-local"], input[type="date"], textarea, select) {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            padding: 0.625rem 1rem;
            background-color: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: #334155;
            font-size: 0.875rem;
            width: 100%;
        }
        :where(.dark .input-modern, .dark input[type="text"], .dark input[type="password"], .dark input[type="email"], .dark input[type="number"], .dark input[type="datetime-local"], .dark input[type="date"], .dark textarea, .dark select) {
            border-color: #334155;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        :where(.input-modern:focus, input[type="text"]:focus, input[type="password"]:focus, input[type="email"]:focus, input[type="number"]:focus, input[type="datetime-local"]:focus, input[type="date"]:focus, textarea:focus, select:focus) {
            outline: none !important;
            border-color: #0054a4 !important;
            box-shadow: 0 0 0 3px rgba(0, 84, 164, 0.2) !important;
            background-color: #ffffff !important;
        }
        :where(.dark .input-modern:focus, .dark input[type="text"]:focus, .dark input[type="password"]:focus, .dark input[type="email"]:focus, .dark input[type="number"]:focus, .dark input[type="datetime-local"]:focus, .dark input[type="date"]:focus, .dark textarea:focus, .dark select:focus) {
            border-color: #3387d7 !important;
            box-shadow: 0 0 0 3px rgba(51, 135, 215, 0.2) !important;
            background-color: #1e293b !important;
        }

        /* Premium Buttons - Vibrant Colors */
        .btn-modern {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            border: none;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .btn-modern:active { transform: scale(0.95); }
        
        .btn-primary {
            background: linear-gradient(135deg, #0054a4, #003f7b);
            box-shadow: 0 4px 10px rgba(0, 84, 164, 0.4);
        }
        .btn-primary:hover { box-shadow: 0 0 20px rgba(0, 84, 164, 0.6); transform: translateY(-2px); }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #047857); /* Emerald green */
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
        }
        .btn-success:hover { box-shadow: 0 0 20px rgba(16, 185, 129, 0.6); transform: translateY(-2px); }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #b45309); /* Amber/Saffron */
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.4);
        }
        .btn-warning:hover { box-shadow: 0 0 20px rgba(245, 158, 11, 0.6); transform: translateY(-2px); }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #b91c1c); /* Red */
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        }
        .btn-danger:hover { box-shadow: 0 0 20px rgba(239, 68, 68, 0.6); transform: translateY(-2px); }

        /* Colorful Checkboxes & Radios */
        input[type="checkbox"], input[type="radio"] {
            appearance: none;
            background-color: #f8fafc;
            margin: 0;
            font: inherit;
            color: currentColor;
            width: 1.25em;
            height: 1.25em;
            border: 2px solid #cbd5e1;
            display: grid;
            place-content: center;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        input[type="checkbox"] { border-radius: 0.35em; }
        input[type="radio"] { border-radius: 50%; }

        input[type="checkbox"]::before {
            content: "";
            width: 0.65em;
            height: 0.65em;
            transform: scale(0);
            transition: 120ms transform ease-in-out;
            box-shadow: inset 1em 1em white;
            background-color: white;
            transform-origin: center;
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
        }
        input[type="radio"]::before {
            content: "";
            width: 0.65em;
            height: 0.65em;
            border-radius: 50%;
            transform: scale(0);
            transition: 120ms transform ease-in-out;
            box-shadow: inset 1em 1em white;
            background-color: white;
        }
        
        input[type="checkbox"]:checked, input[type="radio"]:checked {
            background: linear-gradient(135deg, #0054a4, #003f7b);
            border-color: transparent;
            box-shadow: 0 0 8px rgba(0, 84, 164, 0.5);
        }
        input[type="checkbox"]:checked::before, input[type="radio"]:checked::before {
            transform: scale(1);
        }

        /* Animated Skeleton */
        .skeleton-block {
            background: linear-gradient(90deg, #e2e8f0 25%, #f8fafc 50%, #e2e8f0 75%);
            background-size: 200% 100%;
            border-radius: 0.375rem;
            animation: skeleton-slide 2s infinite linear;
        }
        .dark .skeleton-block {
            background: linear-gradient(90deg, #334155 25%, #475569 50%, #334155 75%);
            background-size: 200% 100%;
        }
        @keyframes skeleton-slide {
            from { background-position: 200% 0; }
            to { background-position: -200% 0; }
        }

        /* Table Row Hover Globally */
        .table-row-modern,
        tbody tr { 
            transition: background-color 0.2s ease, transform 0.2s ease; 
        }
        .table-row-modern:hover,
        tbody tr:hover {
            background-color: #f1f5f9;
        }
        .dark .table-row-modern:hover,
        .dark tbody tr:hover {
            background-color: #1e293b;
        }

        /* Navigation Sidebar */
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            margin: 0.25rem 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            color: #475569; /* slate-600 */
            position: relative;
        }
        .dark .nav-item { color: #94a3b8; /* slate-400 */ }
        .nav-item:hover { 
            background: linear-gradient(to right, #00255c, #004094) !important; 
            color: #ffffff !important;
            transform: translateX(4px);
        }
        .nav-item:hover i {
            color: #ffffff !important;
        }
        .dark .nav-item:hover { 
            background: linear-gradient(to right, #00255c, #004094) !important; 
            color: #ffffff !important;
        }
        
        .nav-active { 
            background: linear-gradient(to right, #00255c, #004094) !important; 
            color: #ffffff !important;
            font-weight: 600;
        }
        .dark .nav-active { 
            background: linear-gradient(to right, #00255c, #004094) !important; 
            color: #ffffff !important;
        }
        .nav-active::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 15%;
            height: 70%;
            width: 4px;
            background-color: #e19022 !important; /* Gold line indicator */
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px rgba(225, 144, 34, 0.6);
        }
        .dark .nav-active::before {
            background-color: #e19022 !important;
            box-shadow: 0 0 8px rgba(225, 144, 34, 0.6);
        }
        .nav-active i { color: #ffffff !important; }
        .dark .nav-active i { color: #ffffff !important; }

        /* Hide page-specific old headers */
        header.glass-panel {
            display: none !important;
        }

        <?php if (!$is_auth_page): ?>
        /* Dynamic Grid Layout to match screenshot */
        body {
            display: grid !important;
            grid-template-rows: auto 1fr !important;
            grid-template-columns: auto 1fr !important;
            grid-template-areas: 
                "banner banner"
                "sidebar main" !important;
            height: 100vh !important;
            overflow: hidden !important;
            margin: 0 !important;
        }

        #topBannerWrap {
            grid-area: banner !important;
            z-index: 45 !important;
            background: linear-gradient(to right, #00255c, #004094, #001c48) !important;
            border-bottom: 2px solid #e19022 !important; /* Gold line separator */
        }

        #sidebar {
            grid-area: sidebar !important;
            z-index: 40 !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .flex-1.flex.flex-col.overflow-hidden,
        div.flex-1.flex.flex-col.overflow-hidden {
            grid-area: main !important;
            height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        footer.custom-page-footer {
            z-index: 10 !important;
            width: auto !important;
            margin-left: -1.5rem !important;
            margin-right: -1.5rem !important;
            margin-bottom: -1.5rem !important;
            margin-top: 3rem !important;
            background: linear-gradient(135deg, #0b1528, #111e38) !important;
            color: #f8fafc !important;
            border-top: 2px solid #e19022 !important; /* Gold line separator */
        }
        @media (min-width: 640px) {
            footer.custom-page-footer {
                margin-left: -2rem !important;
                margin-right: -2rem !important;
                margin-bottom: -2rem !important;
            }
        }

        footer.custom-page-footer a {
            color: #66a5e1 !important;
        }
        footer.custom-page-footer a:hover {
            color: #ffffff !important;
        }

        /* Responsive Mobile Layout Override */
        @media (max-width: 1023px) {
            body {
                grid-template-areas: 
                    "banner banner"
                    "main main" !important;
            }
            #sidebar {
                position: fixed !important;
                top: 0 !important;
                bottom: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                z-index: 50 !important;
            }
        }
        @media (min-width: 1024px) {
            #sidebar {
                position: static !important;
                transform: none !important;
                height: 100% !important;
            }
        }
        <?php endif; ?>
    </style>
    <?= $extraHead ?? '' ?>
</head>
<?php $bodyClass = $bodyClass ?? 'h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
    <?php if (!$is_auth_page): ?>
    <!-- Top Government Banner / Merged Unified Header -->
    <div id="topBannerWrap" class="h-20 bg-gradient-to-r from-navy-950 via-navy-850 to-navy-900 border-b border-navy-800 text-white flex items-center justify-between px-6 select-none relative overflow-visible flex-shrink-0">
        <!-- Background decorative pattern -->
        <div class="absolute inset-0 bg-[url('assets/images/gov_bg.png')] opacity-15 bg-cover bg-center mix-blend-overlay"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-navy-950/80 via-transparent to-navy-950/80 pointer-events-none"></div>

        <!-- Left Side: Sidebar Toggle + Emblem of India + Amravati Connect Title -->
        <div class="flex items-center space-x-3.5 relative z-10">
            <!-- Sidebar toggle icon button -->
            <button id="sidebarToggle" class="p-2 mr-1 rounded-lg text-slate-350 hover:text-white hover:bg-white/10 transition-colors focus:outline-none sidebar-toggle" title="Toggle Sidebar">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            
            <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/Emblem_of_India.svg" alt="Emblem of India" class="h-14 w-auto" style="filter: invert(79%) sepia(50%) saturate(1000%) hue-rotate(350deg) brightness(105%) contrast(105%) drop-shadow(0 2px 6px rgba(0,0,0,0.3));">
            <div class="flex flex-col">
                <h1 class="text-base font-extrabold tracking-wide text-white leading-tight font-formal">Amravati Connect</h1>
                <p class="text-[10px] text-slate-350 font-medium">Amravati District Administration</p>
                <p class="text-[9px] text-amber-400 font-extrabold tracking-wide mt-0.5">जनसेवा हीच आमची सेवा</p>
            </div>
        </div>

        <!-- Center Search Box -->
        <div class="hidden lg:flex items-center flex-1 max-w-md mx-8 relative z-10">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
            </div>
            <?php
            $search_placeholder = $t['search_placeholder'] ?? ($lang === 'en' ? "Search tasks, officers, or circulars (Press '/')" : "कार्ये, अधिकारी किंवा परिपत्रके शोधा (दाबा '/')");
            ?>
            <input id="globalSearch" type="text"
                   placeholder="<?= htmlspecialchars($search_placeholder) ?>"
                   class="block w-full pl-9 pr-3 py-1.5 border border-white/20 rounded-lg text-sm bg-white/10 text-white placeholder-slate-400 focus:outline-none focus:bg-white focus:text-slate-900 focus:border-navy-500 focus:ring-1 focus:ring-navy-500 transition-all">
        </div>

        <!-- Right Side: Actions (Language, Theme, Notifications, Profile, Seal) -->
        <div class="flex items-center space-x-3.5 relative z-10 text-right">
            <!-- Language Switcher -->
            <?php
            $queryParams = $_GET;
            $queryParams['lang'] = ($lang === 'en' ? 'mr' : 'en');
            $lang_switch_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($queryParams);
            ?>
            <a href="<?php echo htmlspecialchars($lang_switch_url); ?>" 
               class="flex items-center text-xs font-semibold text-slate-200 hover:text-white hover:bg-white/10 px-2.5 py-1.5 rounded-lg transition-colors border border-white/20" style="text-decoration: none;">
                <i data-lucide="languages" class="w-4 h-4 mr-1.5 text-slate-300"></i>
                <span class="hidden sm:inline"><?php echo $lang === 'en' ? 'मराठी' : 'English'; ?></span>
            </a>

            <!-- Theme Toggle -->
            <button id="themeToggle"
                    class="p-2 text-slate-350 hover:text-white rounded-lg hover:bg-white/10 transition-colors focus:outline-none">
                <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                <i data-lucide="sun"  class="w-5 h-5 hidden dark:block"></i>
            </button>

            <!-- Notifications Bell -->
            <div class="relative">
                <button id="notificationBtn" class="relative p-2 text-slate-350 hover:text-white rounded-lg hover:bg-white/10 transition-colors focus:outline-none">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span id="unreadCountBadge" style="display:none;" class="absolute top-1 right-1 flex items-center justify-center h-4 w-4 text-[9px] font-bold text-white rounded-full bg-saffron-500 ring-2 ring-navy-900">0</span>
                </button>
                <!-- Dropdown -->
                <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 z-50">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-t-xl">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white"><?= htmlspecialchars($t['menu_notifications'] ?? 'Notifications') ?></h3>
                        <button onclick="markAllAsRead()" class="text-xs text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 font-medium">
                            <?= $lang === 'en' ? 'Mark all as read' : 'सर्व वाचलेले म्हणून चिन्हांकित करा' ?>
                        </button>
                    </div>
                    <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                        <!-- Populated via AJAX -->
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-xl">
                        <a href="notifications.php?lang=<?= $lang ?>" class="block w-full text-center px-4 py-3 text-xs font-medium text-slate-500 hover:text-navy-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                            <?= $lang === 'en' ? 'View All Notifications' : 'सर्व सूचना पहा' ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown Container -->
            <div class="relative pl-3 border-l border-white/20 flex items-center">
                <button id="profileDropdownBtn" class="flex items-center space-x-3 cursor-pointer focus:outline-none px-2.5 py-1.5 rounded-xl hover:bg-white/10 transition-colors">
                    <div class="flex flex-col text-right hidden md:block">
                        <span class="text-xs font-semibold text-white"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span>
                        <span class="text-[9px] text-xs text-slate-300 leading-none mt-0.5">
                            <?= htmlspecialchars($_SESSION['user_role'] ?? 'Officer') ?>
                            <?= ' (' . htmlspecialchars($headerLocationDisplay) . ')' ?>
                        </span>
                    </div>
                    <?php
                    $initials = strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1));
                    ?>
                    <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white text-xs font-bold border border-white/30 shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                </button>
                <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-950/90 backdrop-blur-md z-50">
                    <!-- Javascript will dynamically rebuild this -->
                </div>
            </div>

            <!-- Maharashtra Seal -->
            <img src="assets/images/maharashtra_seal.jpg" alt="Seal of Maharashtra" class="h-16 w-auto hidden sm:block" style="filter: invert(1); mix-blend-mode: screen;">
        </div>
    </div>
    <?php endif; ?>
