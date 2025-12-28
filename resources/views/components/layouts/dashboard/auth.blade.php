<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="author" content="TechScales">
    <title>{{ $title ?? 'Login' }} | CAMS Admin</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('dashertheme/assets/images/favicon/favicon-32x32.png') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/" />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />

    <!-- Libs CSS -->
    <link rel="stylesheet" href="{{ asset('dashertheme/assets/libs/@tabler/icons-webfont/tabler-icons.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('dashertheme/assets/css/theme.min.css') }}">

    <style>
        .auth-wrapper {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .auth-sidebar {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
            border: none;
            padding: 0.75rem 1.5rem;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }
    </style>

    @livewireStyles
</head>

<body>
    <div class="auth-wrapper d-flex align-items-center justify-content-center p-4">
        {{ $slot }}
    </div>

    <!-- Scripts -->
    <script src="{{ asset('dashertheme/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    @livewireScripts
</body>

</html>
