<div>
    <div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden py-4">
        {{-- Background Image with Overlay --}}
        <div class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 0;">
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2000"
                 alt="Background"
                 class="w-100 h-100 object-fit-cover">
            <div class="position-absolute top-0 start-0 w-100 h-100"
                 style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.85) 0%, rgba(108, 117, 125, 0.85) 100%);"></div>
        </div>

        {{-- Login Card --}}
        <div class="container position-relative px-3" style="z-index: 1;">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                    <div class="card border-0 shadow-lg" style="backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95); border-radius: 20px;">
                    <div class="card-body p-4 p-sm-5">
                        {{-- Logo & Branding --}}
                        <div class="text-center mb-4 mb-sm-5">
                            <div class="d-inline-flex align-items-center justify-content-center mb-3"
                                 style="width: 70px; height: 70px; background: linear-gradient(135deg, #0d6efd 0%, #6c757d 100%); border-radius: 16px; box-shadow: 0 8px 16px rgba(13, 110, 253, 0.3);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                                </svg>
                            </div>
                            <h2 class="fw-bold mb-2" style="color: #212529; font-size: clamp(1.5rem, 4vw, 2rem);">Welcome Back</h2>
                            <p class="text-muted mb-1" style="font-size: clamp(0.95rem, 2.5vw, 1.1rem);">Sign in to your account</p>
                            <p class="text-primary fw-semibold mb-0" style="font-size: clamp(0.85rem, 2vw, 0.95rem);">Business Management System</p>
                        </div>

                        {{-- Error Messages --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
                                <div class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                    <div>
                                        @foreach ($errors->all() as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- Login Form --}}
                        <form method="POST" action="{{ route('admin.login.post') }}">
                            @csrf

                            {{-- Email Input --}}
                            <div class="mb-3 mb-sm-4">
                                <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: clamp(0.875rem, 2vw, 0.95rem);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    Email Address
                                </label>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                                       placeholder="Enter your email"
                                       style="border-radius: 12px; border: 2px solid #e9ecef; padding: 0.75rem 1rem; font-size: clamp(0.95rem, 2.5vw, 1rem); transition: all 0.3s;"
                                       onfocus="this.style.borderColor='#0d6efd'; this.style.boxShadow='0 0 0 0.2rem rgba(13, 110, 253, 0.15)'"
                                       onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                                       autofocus
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password Input --}}
                            <div class="mb-3 mb-sm-4">
                                <label class="form-label fw-semibold mb-2" style="color: #495057; font-size: clamp(0.875rem, 2vw, 0.95rem);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                    Password
                                </label>
                                <input type="password"
                                       name="password"
                                       class="form-control form-control-lg @error('password') is-invalid @enderror"
                                       placeholder="Enter your password"
                                       style="border-radius: 12px; border: 2px solid #e9ecef; padding: 0.75rem 1rem; font-size: clamp(0.95rem, 2.5vw, 1rem); transition: all 0.3s;"
                                       onfocus="this.style.borderColor='#0d6efd'; this.style.boxShadow='0 0 0 0.2rem rgba(13, 110, 253, 0.15)'"
                                       onblur="this.style.borderColor='#e9ecef'; this.style.boxShadow='none'"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Remember Me & Forgot Password --}}
                            <div class="mb-3 mb-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div class="form-check">
                                    <input type="checkbox"
                                           name="remember"
                                           class="form-check-input"
                                           id="remember"
                                           style="border-radius: 4px; width: 18px; height: 18px;">
                                    <label class="form-check-label ms-1" for="remember" style="color: #6c757d; font-size: clamp(0.875rem, 2vw, 0.95rem);">
                                        Remember me
                                    </label>
                                </div>
                                <a href="#" class="text-decoration-none" style="color: #0d6efd; font-size: clamp(0.875rem, 2vw, 0.95rem); font-weight: 500;">
                                    Forgot Password?
                                </a>
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit"
                                    class="btn btn-lg w-100 text-white fw-semibold position-relative overflow-hidden"
                                    style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); border: none; border-radius: 12px; padding: 0.875rem; font-size: clamp(1rem, 2.5vw, 1.1rem); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4); transition: all 0.3s; touch-action: manipulation;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(13, 110, 253, 0.5)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(13, 110, 253, 0.4)'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10 17 15 12 10 7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                                Sign In
                            </button>
                        </form>

                        {{-- Divider --}}
                        <div class="position-relative my-4">
                            <hr class="text-muted">
                            <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted" style="font-size: 0.85rem;">
                                Powered by
                            </span>
                        </div>

                        {{-- Company Info --}}
                        <div class="text-center">
                            <p class="mb-2 fw-bold" style="color: #212529; font-size: clamp(0.95rem, 2.5vw, 1rem);">TechScales Company Limited</p>
                            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-center gap-2 gap-sm-3 text-muted" style="font-size: clamp(0.8rem, 2vw, 0.9rem);">
                                <span class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                    </svg>
                                    +255 756 077 558
                                </span>
                                <span class="text-muted d-none d-sm-inline">|</span>
                                <span class="d-flex align-items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                        <polyline points="22,6 12,13 2,6"></polyline>
                                    </svg>
                                    info@techscales.co.tz
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                    {{-- Additional Info --}}
                    <div class="text-center mt-3 mt-sm-4">
                        <p class="text-white mb-0" style="font-size: clamp(0.8rem, 2vw, 0.9rem); text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            © {{ date('Y') }} TechScales. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        -webkit-text-size-adjust: 100%;
        -webkit-tap-highlight-color: transparent;
    }

    .object-fit-cover {
        object-fit: cover;
    }

    /* Mobile-specific adjustments */
    @media (max-width: 576px) {
        .card {
            border-radius: 16px !important;
        }

        .btn-lg {
            min-height: 48px;
        }

        .form-control {
            min-height: 48px;
        }

        .form-check-input {
            width: 20px !important;
            height: 20px !important;
            margin-top: 0.1rem;
        }
    }

    /* Input focus animation */
    .form-control:focus {
        transition: all 0.3s ease;
    }

    /* Button ripple effect */
    .btn-lg {
        position: relative;
        overflow: hidden;
    }

    .btn-lg::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-lg:active::after {
        width: 300px;
        height: 300px;
    }

    /* Card entrance animation */
    .card {
        animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Smooth transitions */
    * {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    </style>
</div>
