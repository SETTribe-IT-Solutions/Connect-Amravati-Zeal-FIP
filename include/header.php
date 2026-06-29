<?php
// include/header.php
// Common HTML <head> and standard government UI styling

$lang = $lang ?? 'en';
$pageTitle = $pageTitle ?? 'Amravati Connect | Government Portal';
$pageDesc = $pageDesc ?? 'Official District Administration Portal';
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
        ::-webkit-scrollbar { width:6px; height:6px; }
        ::-webkit-scrollbar-track { background:transparent; }
        ::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:3px; }
        .dark ::-webkit-scrollbar-thumb { background:#475569; }

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
            background: rgba(241, 245, 249, 0.8); /* slate-100 */ 
            color: #0f172a;
            transform: translateX(4px);
        }
        .dark .nav-item:hover { 
            background: rgba(30, 41, 59, 0.8); /* slate-800 */ 
            color: #f8fafc;
        }
        
        .nav-active { 
            background: linear-gradient(to right, #e6f0fa, transparent) !important; /* navy-50 */
            color: #0054a4 !important; /* navy-600 */
            font-weight: 600;
        }
        .dark .nav-active { 
            background: linear-gradient(to right, rgba(0,84,164,0.2), transparent) !important; 
            color: #66a5e1 !important; 
        }
        .nav-active::before {
            content: '';
            position: absolute;
            left: -0.5rem;
            top: 15%;
            height: 70%;
            width: 4px;
            background-color: #0054a4;
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 8px rgba(0, 84, 164, 0.6);
        }
        .dark .nav-active::before {
            background-color: #66a5e1;
            box-shadow: 0 0 8px rgba(102, 165, 225, 0.6);
        }
        .nav-active i { color: #0054a4 !important; }
        .dark .nav-active i { color: #66a5e1 !important; }
    </style>
    <?= $extraHead ?? '' ?>
</head>
<?php $bodyClass = $bodyClass ?? 'h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200'; ?>
<body class="<?= htmlspecialchars($bodyClass) ?>">
