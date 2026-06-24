<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
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

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map { height: 100%; width: 100%; border-radius: 0.5rem; z-index: 1; }
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
                <a href="#" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
                    Executive Dashboard
                </a>
                <a href="announcements.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="megaphone" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Announcement Center
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
                <a href="reports.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i data-lucide="pie-chart" class="w-5 h-5 mr-3 text-slate-400"></i>
                    Reports & Analytics
                </a>
                <a href="graph.php" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-md bg-navy-50 text-navy-700 dark:bg-slate-800 dark:text-white">
                    <i data-lucide="map" class="w-5 h-5 mr-3 text-navy-600 dark:text-blue-400"></i>
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
                <div class="relative">
                    <button id="notificationBtn" class="relative p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span id="unreadCountBadge" style="display:none;" class="absolute top-0 right-0 flex items-center justify-center h-4 w-4 text-[10px] font-bold text-white rounded-full bg-saffron-500 ring-2 ring-white dark:ring-slate-900">0</span>
                    </button>
                    <!-- Dropdown -->
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-lg shadow-xl border border-slate-200 dark:border-slate-700 z-50">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-t-lg">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Notifications</h3>
                            <button onclick="markAllAsRead()" class="text-xs text-navy-600 dark:text-blue-400 hover:text-navy-800 dark:hover:text-blue-300 font-medium">Mark all as read</button>
                        </div>
                        <div id="notificationList" class="max-h-80 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                            <!-- Populated via AJAX -->
                        </div>
                        <div class="border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 rounded-b-lg">
                            <a href="notifications.php" class="block w-full text-center px-4 py-3 text-xs font-medium text-slate-500 hover:text-navy-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
                                View All Notifications
                            </a>
                        </div>
                    </div>
                </div>

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
        <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-900 p-6 sm:p-8 flex flex-col">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">GIS Map View</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Interactive geographical view of Amravati District operations.</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button class="inline-flex items-center px-4 py-2 border border-slate-300 dark:border-slate-600 shadow-sm text-sm font-medium rounded-md text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                        <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                        Filter Layers
                    </button>
                </div>
            </div>

            <!-- Map Container -->
            <div class="flex-1 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden relative min-h-[500px]">
                <div id="map"></div>
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

        
        // Initialize Map
        const map = L.map('map').setView([20.9320, 77.7523], 10); // Amravati coordinates
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Add some dummy markers for Amravati talukas
        const locations = [
            { name: "Amravati", lat: 20.9320, lng: 77.7523, tasks: 45, status: 'warning' },
            { name: "Achalpur", lat: 21.2573, lng: 77.5086, tasks: 32, status: 'ok' },
            { name: "Chandur Railway", lat: 20.8166, lng: 77.9833, tasks: 18, status: 'ok' },
            { name: "Daryapur", lat: 20.9333, lng: 77.3167, tasks: 25, status: 'critical' }
        ];

        locations.forEach(loc => {
            let color = '#2e7d32'; // govgreen
            if (loc.status === 'warning') color = '#f57c00'; // saffron
            if (loc.status === 'critical') color = '#dc2626'; // red

            const circleMarker = L.circleMarker([loc.lat, loc.lng], {
                color: color,
                fillColor: color,
                fillOpacity: 0.6,
                radius: 12
            }).addTo(map);
            circleMarker.bindPopup(`<b>${loc.name}</b><br>Active Tasks: ${loc.tasks}`);
        });
        
        // Handle Map resizing when sidebar toggles
        const mySidebarToggle = document.getElementById('sidebarToggle');
        if(mySidebarToggle) {
            mySidebarToggle.addEventListener('click', () => {
                setTimeout(() => {
                    map.invalidateSize();
                }, 350);
            });
        }

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

        // Notification System
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const unreadCountBadge = document.getElementById('unreadCountBadge');
        const notificationList = document.getElementById('notificationList');

        notificationBtn.addEventListener('click', () => {
            notificationDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });

        function fetchNotifications() {
            fetch('api/get_notifications.php')
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update Badge
                        if (data.unread_count > 0) {
                            unreadCountBadge.style.display = 'flex';
                            unreadCountBadge.innerText = data.unread_count > 99 ? '99+' : data.unread_count;
                        } else {
                            unreadCountBadge.style.display = 'none';
                        }

                        // Update List
                        notificationList.innerHTML = '';
                        if (data.notifications.length === 0) {
                            notificationList.innerHTML = `
                                <div class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                    No new notifications
                                </div>
                            `;
                        } else {
                            data.notifications.forEach(n => {
                                const readClass = n.is_read == 0 ? 'bg-slate-50 dark:bg-slate-800' : 'opacity-70';
                                const item = document.createElement('div');
                                item.className = `px-4 py-3 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer transition-colors ${readClass}`;
                                item.innerHTML = `
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mt-0.5 ${n.badge_color}">
                                            <i data-lucide="bell" class="w-4 h-4"></i>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <p class="text-sm font-medium text-slate-900 dark:text-white">${n.title}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">${n.message}</p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">${n.time_elapsed}</p>
                                        </div>
                                    </div>
                                `;
                                item.onclick = () => markAsRead(n.id);
                                notificationList.appendChild(item);
                            });
                            lucide.createIcons();
                        }
                    }
                })
                .catch(err => console.error('Error fetching notifications:', err));
        }

        function markAsRead(id) {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: id })
            }).then(() => fetchNotifications());
        }

        function markAllAsRead() {
            fetch('api/mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mark_all: true })
            }).then(() => fetchNotifications());
        }

        // Poll every 60 seconds
        setInterval(fetchNotifications, 60000);
        // Initial fetch
        fetchNotifications();

    </script>
</body>
</html>
