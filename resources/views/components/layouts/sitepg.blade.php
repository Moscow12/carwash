<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="CAMS - Carwash Management System by TechScales">
    <meta name="author" content="TechScales Company Limited">
    <title>{{ $title ?? 'CAMS - Carwash Management System' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}">

    <!-- Custom Site CSS -->
    <style>
        :root {
            --cams-primary: #0d6efd;
            --cams-secondary: #6c757d;
            --cams-accent: #00b4d8;
            --cams-dark: #1a1a2e;
            --cams-light: #f8f9fa;
        }

        body {
            font-family: 'Public Sans', sans-serif;
        }

        /* Navbar Styles */
        .navbar-site {
            background: linear-gradient(135deg, var(--cams-dark) 0%, #16213e 100%);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-site.scrolled {
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }

        .navbar-brand-text {
            font-weight: 800;
            font-size: 1.5rem;
            background: linear-gradient(135deg, var(--cams-accent) 0%, #48cae4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link-site {
            color: rgba(255,255,255,0.85) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link-site:hover,
        .nav-link-site.active {
            color: var(--cams-accent) !important;
        }

        .nav-link-site::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: var(--cams-accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link-site:hover::after,
        .nav-link-site.active::after {
            width: 80%;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--cams-dark) 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2300b4d8' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 1.5rem;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 2rem;
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--cams-accent) 0%, #48cae4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Buttons */
        .btn-cams {
            background: linear-gradient(135deg, var(--cams-accent) 0%, #0096c7 100%);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            color: #fff;
            transition: all 0.3s ease;
        }

        .btn-cams:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 180, 216, 0.3);
            color: #fff;
        }

        .btn-outline-cams {
            border: 2px solid var(--cams-accent);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
            color: var(--cams-accent);
            background: transparent;
            transition: all 0.3s ease;
        }

        .btn-outline-cams:hover {
            background: var(--cams-accent);
            color: #fff;
            transform: translateY(-2px);
        }

        /* Cards */
        .feature-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, var(--cams-accent) 0%, #48cae4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-icon i {
            font-size: 2rem;
            color: #fff;
        }

        /* Section Styles */
        .section-padding {
            padding: 100px 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--cams-dark);
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--cams-secondary);
            margin-bottom: 3rem;
        }

        /* Service Cards */
        .service-card {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .service-card-img {
            height: 200px;
            background: linear-gradient(135deg, var(--cams-dark) 0%, #16213e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .service-card-img i {
            font-size: 4rem;
            color: var(--cams-accent);
        }

        /* Footer */
        .footer-site {
            background: linear-gradient(135deg, var(--cams-dark) 0%, #16213e 100%);
            color: #fff;
            padding: 80px 0 30px;
        }

        .footer-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--cams-accent);
        }

        .footer-link {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: block;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: var(--cams-accent);
            padding-left: 5px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
            color: #fff;
        }

        .social-link:hover {
            background: var(--cams-accent);
            transform: translateY(-3px);
            color: #fff;
        }

        /* Form Styles */
        .form-control-site {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.875rem 1.25rem;
            transition: all 0.3s ease;
        }

        .form-control-site:focus {
            border-color: var(--cams-accent);
            box-shadow: 0 0 0 4px rgba(0, 180, 216, 0.1);
        }

        /* Auth Card */
        .auth-card {
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .auth-sidebar {
            background: linear-gradient(135deg, var(--cams-dark) 0%, #16213e 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 500px;
        }

        /* Stats */
        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--cams-accent);
        }

        .stat-label {
            color: var(--cams-secondary);
            font-weight: 500;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .section-padding {
                padding: 60px 0;
            }
        }
    </style>

    @livewireStyles
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-site fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('site.home') }}">
                <i class="fas fa-car-side me-2 text-info fs-4"></i>
                <span class="navbar-brand-text">CAMS</span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link nav-link-site {{ request()->routeIs('site.home') ? 'active' : '' }}" href="{{ route('site.home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-site {{ request()->routeIs('site.about') ? 'active' : '' }}" href="{{ route('site.about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-site {{ request()->routeIs('site.services') ? 'active' : '' }}" href="{{ route('site.services') }}">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-site {{ request()->routeIs('site.contact') ? 'active' : '' }}" href="{{ route('site.contact') }}">Contact Us</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-cams btn-sm" href="{{ route('site.login') }}">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-cams btn-sm" href="{{ route('site.register') }}">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    {{ $slot }}

    <!-- Footer -->
    <footer class="footer-site">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-car-side me-2 text-info fs-4"></i>
                        <span class="navbar-brand-text">CAMS</span>
                    </div>
                    <p class="text-white-50 mb-4">
                        Carwash Management System - A comprehensive solution to manage all carwash activities and services in one powerful system.
                    </p>
                    <div class="d-flex">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="footer-title">Quick Links</h5>
                    <a href="{{ route('site.home') }}" class="footer-link">Home</a>
                    <a href="{{ route('site.about') }}" class="footer-link">About Us</a>
                    <a href="{{ route('site.services') }}" class="footer-link">Services</a>
                    <a href="{{ route('site.contact') }}" class="footer-link">Contact</a>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Services</h5>
                    <a href="#" class="footer-link">Full Car Wash</a>
                    <a href="#" class="footer-link">Interior Cleaning</a>
                    <a href="#" class="footer-link">Exterior Polish</a>
                    <a href="#" class="footer-link">Engine Cleaning</a>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Info</h5>
                    <p class="text-white-50 mb-2">
                        <i class="fas fa-map-marker-alt me-2 text-info"></i> Dodoma, Tanzania
                    </p>
                    <p class="text-white-50 mb-2">
                        <i class="fas fa-phone me-2 text-info"></i> +255 659 811 966
                    </p>
                    <p class="text-white-50 mb-2">
                        <i class="fas fa-envelope me-2 text-info"></i> info@techscales.co.tz
                    </p>
                </div>
            </div>

            <hr class="my-4 border-secondary">

            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-white-50 mb-0">
                        &copy; {{ date('Y') }} CAMS. Developed by <strong class="text-info">TechScales Company Limited</strong>
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50 text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('mainNav');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
    @livewireScripts
</body>

</html>
