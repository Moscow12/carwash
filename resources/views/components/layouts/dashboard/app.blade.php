<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="TechScales">
    <title>{{ $title ?? 'Dashboard' }} | CAMS Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('dashertheme/assets/images/favicon/favicon-32x32.png') }}" />

    <!-- Color modes -->
    <script src="{{ asset('dashertheme/assets/js/vendors/color-modes.js') }}"></script>
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') {
            document.documentElement.classList.add('collapsed');
            document.documentElement.classList.remove('expanded');
        } else {
            document.documentElement.classList.remove('collapsed');
            document.documentElement.classList.add('expanded');
        }
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('dashertheme/assets/libs/simplebar/dist/simplebar.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashertheme/assets/libs/@tabler/icons-webfont/tabler-icons.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('dashertheme/assets/css/theme.min.css') }}">

    <style>
        .sidebar-brand {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
        }
        .nav-link.active {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd !important;
            border-left: 3px solid #0d6efd;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            border: none;
        }
        .table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .badge-status {
            padding: 0.35rem 0.65rem;
            font-weight: 500;
        }
    </style>

    @livewireStyles
</head>

<body>
    <!-- Sidebar -->
    <div>
        <div id="miniSidebar">
            <div class="brand-logo">
                <a class='d-none d-md-flex align-items-center gap-2' href='{{ route("admin.dashboard") }}'>
                    <i class="ti ti-car text-primary fs-3"></i>
                    <span class="fw-bold fs-4 site-logo-text">CAMS</span>
                </a>
            </div>

            <ul class="navbar-nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.dashboard") ? "active" : "" }}' href='{{ route("admin.dashboard") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-dashboard fs-5"></i>
                        </span>
                        <span class="text">Dashboard</span>
                    </a>
                </li>

                <!-- User Management -->
                <li class="nav-item">
                    <div class="nav-heading">User Management</div>
                    <hr class="mx-5 nav-line mb-1" />
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.users*") ? "active" : "" }}' href='{{ route("admin.users") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-users fs-5"></i>
                        </span>
                        <span class="text">Users</span>
                    </a>
                </li>

                <!-- Location Management -->
                <li class="nav-item">
                    <div class="nav-heading">Locations</div>
                    <hr class="mx-5 nav-line mb-1" />
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.countries*") ? "active" : "" }}' href='{{ route("admin.countries") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-world fs-5"></i>
                        </span>
                        <span class="text">Countries</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.regions*") ? "active" : "" }}' href='{{ route("admin.regions") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-map-2 fs-5"></i>
                        </span>
                        <span class="text">Regions</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.districts*") ? "active" : "" }}' href='{{ route("admin.districts") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-building-community fs-5"></i>
                        </span>
                        <span class="text">Districts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.wards*") ? "active" : "" }}' href='{{ route("admin.wards") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-home-2 fs-5"></i>
                        </span>
                        <span class="text">Wards</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.streets*") ? "active" : "" }}' href='{{ route("admin.streets") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-road fs-5"></i>
                        </span>
                        <span class="text">Streets</span>
                    </a>
                </li>

                <!-- Settings -->
                <li class="nav-item">
                    <div class="nav-heading">System</div>
                    <hr class="mx-5 nav-line mb-1" />
                </li>
                <li class="nav-item">
                    <a class='nav-link {{ request()->routeIs("admin.settings*") ? "active" : "" }}' href='{{ route("admin.settings") }}'>
                        <span class="nav-icon">
                            <i class="ti ti-settings fs-5"></i>
                        </span>
                        <span class="text">Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-2">
                <div class="container-fluid">
                    <button class="btn btn-link text-dark p-0 me-3 d-none d-md-block" id="sidebarToggle">
                        <i class="ti ti-menu-2 fs-4"></i>
                    </button>

                    <div class="d-flex align-items-center ms-auto">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <a href="#" class="text-muted position-relative" data-bs-toggle="dropdown">
                                <i class="ti ti-bell fs-4"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    3
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0" style="width: 300px;">
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-0">Notifications</h6>
                                </div>
                                <div class="p-3 text-center text-muted">
                                    <small>No new notifications</small>
                                </div>
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                    <i class="ti ti-user"></i>
                                </div>
                                <span class="d-none d-md-block text-dark">{{ auth()->user()->name ?? 'Admin' }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="ti ti-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.settings') }}"><i class="ti ti-settings me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="ti ti-logout me-2"></i>Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="p-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            <footer class="footer mt-auto py-3 bg-white border-top">
                <div class="container-fluid px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            &copy; {{ date('Y') }} CAMS. Developed by <strong>TechScales Company Limited</strong>
                        </span>
                        <span class="text-muted small">
                            <i class="ti ti-phone me-1"></i>+255 659 811 966
                        </span>
                    </div>
                </div>
            </footer>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('dashertheme/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dashertheme/assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('dashertheme/assets/js/theme.min.js') }}"></script>

    <script>
        // Sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.documentElement.classList.toggle('collapsed');
            document.documentElement.classList.toggle('expanded');
            localStorage.setItem('sidebarExpanded', document.documentElement.classList.contains('expanded'));
        });
    </script>

    @livewireScripts
</body>

</html>
