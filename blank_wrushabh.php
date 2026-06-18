<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amravati Connect - Government Workflow Platform</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Tailwind Config for Design System -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        border: "hsl(var(--border))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        navy: {
                            50: '#eef2f6',
                            100: '#d9e2ec',
                            500: '#1a365d',
                            600: '#152b4a',
                            700: '#0f1f38',
                            900: '#0a1424'
                        },
                        govgreen: {
                            50: '#edf7ed',
                            100: '#cce8cc',
                            500: '#2e7d32',
                            600: '#256428'
                        },
                        saffron: {
                            50: '#fff3e0',
                            100: '#ffe0b2',
                            500: '#f57c00',
                            600: '#e65100'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Base styles and ShadCN-like variables */
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --border: 214.3 31.8% 91.4%;
        }
        .dark {
            --background: 222.2 84% 4.9%;
            --foreground: 210 40% 98%;
            --border: 217.2 32.6% 17.5%;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="h-screen flex overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-all duration-300 z-20" id="sidebar">
        <!-- Sidebar Header -->
        <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
            <div class="w-8 h-8 rounded bg-navy-600 flex items-center justify-center mr-3">
                <i data-lucide="landmark" class="text-white w-5 h-5"></i>
            </div>
            <span class="font-bold text-lg text-navy-700 dark:text-white tracking-tight">Amravati Connect</span>
        </div>

        <!-- Sidebar Navigation -->
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-1 px-3">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-4">Main Modules</p>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-navy-50 text-navy-700 dark:bg-slate-800 dark:text-white">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                    Executive Dashboard
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="network" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Task Allocation
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell-ring" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Announcements
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="award" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Appreciation
                </a>
                
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Analytics & Data</p>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Reports & Analytics
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="map" class="w-5 h-5 mr-3 text-slate-400"></i>
                    GIS Map View
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="folder-open" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Document Management
                </a>

                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 mt-6">Administration</p>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="users" class="w-5 h-5 mr-3 text-slate-400"></i>
                    User Management
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="map-pin" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Location Hierarchy
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="shield-check" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Audit Logs
                </a>
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="settings" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Settings
                </a>
            </nav>
        </div>
        
        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            <button class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-navy-600 to-navy-500 hover:from-navy-700 hover:to-navy-600 focus:outline-none">
                <i data-lucide="bot" class="w-4 h-4 mr-2"></i>
                Ask Amravati AI
            </button>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- GLOBAL HEADER -->
        <header class="h-16 glass-panel border-b border-slate-200 dark:border-slate-800 flex items-center justify-between px-6 z-10 sticky top-0">
            <div class="flex items-center flex-1">
                <button class="mr-4 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 focus:outline-none hidden md:block" id="sidebarToggle">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                
                <!-- Global Search -->
                <div class="max-w-md w-full relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-slate-400"></i>
                    </div>
                    <input type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-700 rounded-md leading-5 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-navy-500 focus:border-navy-500 sm:text-sm transition-colors" placeholder="Search tasks, officers, or circulars (Press '/')">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-slate-400 text-xs border border-slate-300 dark:border-slate-700 rounded px-1.5 py-0.5">⌘K</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language Toggle -->
                <button class="flex items-center text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 px-3 py-1.5 rounded-md transition-colors border border-slate-200 dark:border-slate-700">
                    <i data-lucide="languages" class="w-4 h-4 mr-2 text-slate-500"></i>
                    EN / MR
                </button>

                <!-- Theme Switcher -->
                <button id="themeToggle" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="moon" class="w-5 h-5 dark:hidden"></i>
                    <i data-lucide="sun" class="w-5 h-5 hidden dark:block"></i>
                </button>

                <!-- Notifications -->
                <button class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    <span class="absolute top-1.5 right-1.5 block h-2 w-2 rounded-full bg-saffron-500 ring-2 ring-white dark:ring-slate-900"></span>
                </button>

                <!-- Profile Dropdown -->
                <div class="flex items-center space-x-3 border-l border-slate-200 dark:border-slate-700 pl-4 ml-2 cursor-pointer">
                    <div class="flex flex-col text-right hidden sm:block">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">Hon. Collector</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">Amravati District</span>
                    </div>
                    <div class="h-9 w-9 rounded-full bg-navy-600 flex items-center justify-center text-white font-bold border-2 border-white dark:border-slate-800 shadow-sm">
                        C
                    </div>
                </div>
            </div>
        </header>

        <!-- MAIN CONTENT SCROLL AREA -->
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8">
            
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">District Executive Dashboard</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Real-time overview of Amravati District operations and task hierarchy.</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                        <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                        Export Report
                    </button>
                    <button class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-navy-600 hover:bg-navy-700 focus:outline-none transition-colors">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Allocate Task
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Total Active Tasks</p>
                                <div class="mt-1 flex items-baseline">
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white">2,845</p>
                                    <p class="ml-2 flex items-baseline text-sm font-semibold text-govgreen-600">
                                        <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i> 12%
                                    </p>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <i data-lucide="layers" class="w-6 h-6 text-navy-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Pending Approvals</p>
                                <div class="mt-1 flex items-baseline">
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white">412</p>
                                    <p class="ml-2 flex items-baseline text-sm font-semibold text-saffron-600">
                                        <i data-lucide="trending-down" class="w-3 h-3 mr-1"></i> 4%
                                    </p>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-orange-50 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                <i data-lucide="clock" class="w-6 h-6 text-saffron-600 dark:text-orange-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Tasks Completed</p>
                                <div class="mt-1 flex items-baseline">
                                    <p class="text-3xl font-bold text-slate-900 dark:text-white">1,432</p>
                                    <p class="ml-2 flex items-baseline text-sm font-semibold text-govgreen-600">
                                        <i data-lucide="trending-up" class="w-3 h-3 mr-1"></i> 24%
                                    </p>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-green-50 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-6 h-6 text-govgreen-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 truncate">Escalated / Overdue</p>
                                <div class="mt-1 flex items-baseline">
                                    <p class="text-3xl font-bold text-red-600 dark:text-red-400">84</p>
                                    <p class="ml-2 flex items-baseline text-sm font-semibold text-red-600">
                                        <i data-lucide="alert-triangle" class="w-3 h-3 mr-1"></i> 12 Action Req
                                    </p>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-red-50 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                                <i data-lucide="alert-octagon" class="w-6 h-6 text-red-600 dark:text-red-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Line Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Task Completion Trend (District Wide)</h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="more-vertical" class="w-5 h-5"></i></button>
                    </div>
                    <div id="trendChart" class="h-72 w-full"></div>
                </div>

                <!-- Bar Chart -->
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Taluka Performance</h2>
                        <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="more-vertical" class="w-5 h-5"></i></button>
                    </div>
                    <div id="talukaChart" class="h-72 w-full"></div>
                </div>
            </div>

            <!-- Data Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-12">
                <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Hierarchical Task Allocation Pipeline</h2>
                    <div class="flex space-x-2">
                        <div class="relative">
                            <select class="block w-full pl-3 pr-10 py-2 text-sm border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white rounded-md focus:outline-none focus:ring-navy-500 focus:border-navy-500">
                                <option>All Talukas</option>
                                <option>Amravati</option>
                                <option>Achalpur</option>
                                <option>Chandur Railway</option>
                            </select>
                        </div>
                        <button class="p-2 border border-slate-300 dark:border-slate-600 rounded-md text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700"><i data-lucide="filter" class="w-4 h-4"></i></button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Task Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned To</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Location</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Due Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                            <!-- Row 1 -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-red-500 mr-3"></div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">Crop Damage Assessment Report</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">#TSK-2026-8941 • Revenue Dept</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white mr-3">SD</div>
                                        <div>
                                            <div class="text-sm text-slate-900 dark:text-white">Sanjay Deshmukh</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Tehsildar</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white">Chandur Railway</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Taluka Level</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-red-600 dark:text-red-400 font-medium">Tomorrow</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 border border-blue-200 dark:border-blue-800">
                                        In Progress
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 mr-3"><i data-lucide="eye" class="w-4 h-4"></i></button>
                                    <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                </td>
                            </tr>
                            
                            <!-- Row 2 -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-saffron-500 mr-3"></div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">Gram Panchayat Fund Utilization Audit</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">#TSK-2026-8902 • Zilla Parishad</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white mr-3">PR</div>
                                        <div>
                                            <div class="text-sm text-slate-900 dark:text-white">Priya Rathod</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">BDO</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white">Achalpur</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Taluka Level</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-slate-300">Oct 24, 2026</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-800">
                                        Pending
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 mr-3"><i data-lucide="eye" class="w-4 h-4"></i></button>
                                    <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-govgreen-500 mr-3"></div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900 dark:text-white">E-KYC Camp Verification Phase 1</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">#TSK-2026-8850 • UIDAI Cell</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-white mr-3">AP</div>
                                        <div>
                                            <div class="text-sm text-slate-900 dark:text-white">Anil Patil</div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">Gramsevak</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-white">Daryapur (Village)</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">Village Level</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-slate-900 dark:text-slate-300">Oct 15, 2026</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
                                        Completed
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button class="text-navy-600 dark:text-blue-400 hover:text-navy-900 dark:hover:text-blue-300 mr-3"><i data-lucide="eye" class="w-4 h-4"></i></button>
                                    <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"><i data-lucide="more-horizontal" class="w-4 h-4"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="bg-white dark:bg-slate-800 px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between sm:px-6">
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-slate-700 dark:text-slate-400">
                                Showing <span class="font-medium text-slate-900 dark:text-white">1</span> to <span class="font-medium text-slate-900 dark:text-white">3</span> of <span class="font-medium text-slate-900 dark:text-white">45</span> results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm font-medium text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-left" class="h-5 w-5"></i>
                                </a>
                                <a href="#" aria-current="page" class="z-10 bg-navy-50 dark:bg-navy-900 border-navy-500 dark:border-navy-400 text-navy-600 dark:text-blue-400 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    1
                                </a>
                                <a href="#" class="bg-white dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                    2
                                </a>
                                <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-sm font-medium text-slate-500 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600">
                                    <i data-lucide="chevron-right" class="h-5 w-5"></i>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
        </main>
    </div>

    <!-- AI Chatbot Floating Widget -->
    <div class="fixed bottom-6 right-6 z-50">
        <button class="w-14 h-14 bg-gradient-to-r from-navy-600 to-navy-500 rounded-full shadow-lg flex items-center justify-center text-white hover:scale-105 transition-transform shadow-navy-500/30">
            <i data-lucide="message-square-text" class="w-6 h-6"></i>
        </button>
    </div>

    <!-- Initialize Icons & Charts & Dark Mode Logic -->
    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Dark Mode Toggle Logic
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        function updateTheme(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark');
            } else {
                htmlElement.classList.remove('dark');
            }
            renderCharts(isDark);
        }

        themeToggle.addEventListener('click', () => {
            const isDark = !htmlElement.classList.contains('dark');
            updateTheme(isDark);
        });

        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('-translate-x-full') || sidebar.style.display === 'none') {
                sidebar.classList.remove('-translate-x-full');
                sidebar.style.display = 'flex';
            } else {
                sidebar.classList.add('-translate-x-full');
                setTimeout(() => sidebar.style.display = 'none', 300);
            }
        });

        // Chart Rendering Logic using ApexCharts
        let trendChartInstance = null;
        let talukaChartInstance = null;

        function renderCharts(isDark) {
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            // Trend Chart Options
            const trendOptions = {
                series: [{
                    name: 'Assigned Tasks',
                    data: [310, 400, 280, 510, 420, 609, 500]
                }, {
                    name: 'Completed Tasks',
                    data: [250, 320, 240, 480, 390, 580, 490]
                }],
                chart: {
                    height: 280,
                    type: 'area',
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                colors: ['#1a365d', '#2e7d32'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                xaxis: {
                    categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    labels: { style: { colors: textColor } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: { style: { colors: textColor } }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4,
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                legend: { position: 'top', horizontalAlign: 'right' }
            };

            // Taluka Performance Options
            const talukaOptions = {
                series: [{
                    name: 'Completion Rate %',
                    data: [92, 85, 78, 88, 72]
                }],
                chart: {
                    height: 280,
                    type: 'bar',
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    background: 'transparent'
                },
                colors: ['#f57c00'],
                plotOptions: {
                    bar: { borderRadius: 4, horizontal: true, }
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: ['Amravati', 'Achalpur', 'Chandur', 'Daryapur', 'Nandgaon'],
                    labels: { style: { colors: textColor } }
                },
                yaxis: {
                    labels: { style: { colors: textColor } }
                },
                grid: {
                    borderColor: gridColor,
                    strokeDashArray: 4,
                },
                theme: { mode: isDark ? 'dark' : 'light' }
            };

            // Destroy existing instances if updating theme
            if (trendChartInstance) trendChartInstance.destroy();
            if (talukaChartInstance) talukaChartInstance.destroy();

            trendChartInstance = new ApexCharts(document.querySelector("#trendChart"), trendOptions);
            talukaChartInstance = new ApexCharts(document.querySelector("#talukaChart"), talukaOptions);
            
            trendChartInstance.render();
            talukaChartInstance.render();
        }

        // Initial render
        renderCharts(htmlElement.classList.contains('dark'));

    </script>
</body>
</html>
