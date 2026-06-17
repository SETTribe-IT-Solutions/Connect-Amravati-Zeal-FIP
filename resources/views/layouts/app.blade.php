<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connect Amravati - Govt Task System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Outfit Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f3f6fa;
            color: #1e293b;
        }
        .navbar-gov {
            background-color: #0a2540;
            border-bottom: 4px solid #c5a880;
        }
        .navbar-gov .navbar-brand, .navbar-gov .nav-link {
            color: #ffffff !important;
        }
        .navbar-gov .nav-link:hover {
            color: #c5a880 !important;
        }
        .footer-gov {
            background-color: #0a2540;
            color: #ffffff;
            border-top: 4px solid #c5a880;
            padding: 20px 0;
            margin-top: 40px;
        }
        .sidebar {
            background-color: #ffffff;
            border-right: 1px solid rgba(0,0,0,0.06);
            min-height: calc(100vh - 70px);
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #475569;
            text-decoration: none;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background-color: rgba(10, 37, 64, 0.05);
            color: #0a2540;
            border-left-color: #c5a880;
        }
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-gov py-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <i class="fa-solid fa-landmark text-warning"></i>
                <strong>CONNECT AMRAVATI</strong>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navContent">
                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <!-- Language Toggler -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-sm btn-outline-light px-3" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-language"></i> {{ app()->getLocale() == 'en' ? 'English' : 'मराठी' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'en') }}">English</a></li>
                            <li><a class="dropdown-item" href="{{ route('lang.switch', 'mr') }}">मराठी</a></li>
                        </ul>
                    </li>
                    
                    <!-- Live Notifications Dropdown -->
                    <li class="nav-item dropdown">
                        <button class="nav-link btn btn-link position-relative" type="button" id="notifDropdown" data-bs-toggle="dropdown">
                            <i class="fa-regular fa-bell text-white fs-5"></i>
                            <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" id="notifCount">0</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg" id="notifList" style="width: 340px;">
                            <li><h6 class="dropdown-header border-bottom pb-2">Live Notifications</h6></li>
                            <div id="notifItems" style="max-height: 280px; overflow-y: auto;">
                                <li><span class="dropdown-item text-muted text-center py-3">No new notifications</span></li>
                            </div>
                        </ul>
                    </li>
                    
                    <!-- User Details & Sign Out -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-circle fs-5"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text text-muted small">{{ Auth::user()->designation }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Layout -->
    <div class="container-fluid">
        <div class="row">
            <aside class="col-md-3 col-lg-2 p-0 sidebar">
                <div class="py-4">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-pie"></i> Dashboard
                    </a>
                    
                    @if(in_array(Auth::user()->roles->first()->name ?? '', ['Collector', 'Additional Collector', 'Deputy Collector', 'SDO', 'Tehsildar']) || Auth::user()->designation === 'Super Admin')
                        <a href="{{ route('tasks.create') }}" class="sidebar-link {{ request()->routeIs('tasks.create') ? 'active' : '' }}">
                            <i class="fa-solid fa-folder-plus"></i> Task Allocation
                        </a>
                    @endif
                    
                    <a href="{{ route('tasks.index') }}" class="sidebar-link {{ request()->routeIs('tasks.index') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i> Task Records
                    </a>
                    
                    @if(Auth::user()->designation === 'Super Admin' || in_array(Auth::user()->roles->first()->name ?? '', ['Collector', 'Additional Collector', 'Deputy Collector']))
                        <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-file-invoice"></i> Reports Center
                        </a>
                    @endif
                </div>
            </aside>
            
            <main class="col-md-9 col-lg-10 py-4 px-md-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer-gov text-center">
        <div class="container">
            <p class="mb-1">National Informatics Centre (NIC) - connect-amravati-govt.in</p>
            <p class="small text-muted mb-0">Designed and Developed by Amravati District IT Cell. © 2026 All Rights Reserved.</p>
        </div>
    </footer>

    <!-- jQuery, Bootstrap 5 and Live Polling Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fetchLiveNotifications() {
            $.ajax({
                url: '/api/notifications/unread',
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + '{{ Auth::user()->createToken("temp_token")->plainTextToken }}'
                },
                success: function(response) {
                    $('#notifCount').text(response.count);
                    let html = '';
                    if(response.items.length > 0) {
                        response.items.forEach(item => {
                            html += `<li><a class="dropdown-item py-2 border-bottom" href="/tasks/${item.data.task_id}">
                                <div class="d-flex justify-content-between font-weight-bold">
                                    <strong>${item.data.title}</strong>
                                </div>
                                <span class="text-muted small">Assigned By: ${item.data.assigned_by}</span>
                            </a></li>`;
                        });
                    } else {
                        html = '<li><span class="dropdown-item text-muted text-center py-3">No new notifications</span></li>';
                    }
                    $('#notifItems').html(html);
                }
            });
        }

        // Poll every 8 seconds for live updates
        fetchLiveNotifications();
        setInterval(fetchLiveNotifications, 8000);
    </script>
</body>
</html>
