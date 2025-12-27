<div>
    <section class="hero-section" style="min-height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="auth-card">
                        <div class="row g-0">
                            <!-- Left Side - Branding -->
                            <div class="col-lg-5 d-none d-lg-block">
                                <div class="auth-sidebar h-100">
                                    <div class="text-center">
                                        <i class="fas fa-car-side text-info mb-4" style="font-size: 5rem;"></i>
                                        <h2 class="text-white fw-bold mb-3">Welcome Back!</h2>
                                        <p class="text-white-50 mb-4">
                                            Sign in to access your CAMS dashboard and manage your carwash business efficiently.
                                        </p>

                                        <div class="mt-5">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">Manage bookings</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">Track customers</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">View reports</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">Process payments</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side - Login Form -->
                            <div class="col-lg-7">
                                <div class="p-4 p-lg-5">
                                    <div class="text-center mb-4">
                                        <a href="{{ route('site.home') }}" class="d-inline-flex align-items-center text-decoration-none mb-4">
                                            <i class="fas fa-car-side text-info me-2 fs-4"></i>
                                            <span class="navbar-brand-text">CAMS</span>
                                        </a>
                                        <h3 class="fw-bold mb-2">Sign In</h3>
                                        <p class="text-muted">Enter your credentials to access your account</p>
                                    </div>

                                    <form wire:submit.prevent="login">
                                        <div class="mb-4">
                                            <label class="form-label fw-semibold">Email Address</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-muted"></i>
                                                </span>
                                                <input type="email"
                                                       wire:model="email"
                                                       class="form-control form-control-site border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                       placeholder="Enter your email">
                                            </div>
                                            @error('email')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label fw-semibold mb-0">Password</label>
                                                <a href="#" class="text-info text-decoration-none small">Forgot password?</a>
                                            </div>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-muted"></i>
                                                </span>
                                                <input type="password"
                                                       wire:model="password"
                                                       class="form-control form-control-site border-start-0 ps-0 @error('password') is-invalid @enderror"
                                                       placeholder="Enter your password">
                                            </div>
                                            @error('password')
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-4">
                                            <div class="form-check">
                                                <input type="checkbox"
                                                       wire:model="remember"
                                                       class="form-check-input"
                                                       id="remember">
                                                <label class="form-check-label text-muted" for="remember">
                                                    Remember me
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-cams btn-lg w-100 mb-4">
                                            <span wire:loading.remove wire:target="login">
                                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                            </span>
                                            <span wire:loading wire:target="login">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Signing in...
                                            </span>
                                        </button>

                                        <div class="text-center">
                                            <p class="text-muted mb-0">
                                                Don't have an account?
                                                <a href="{{ route('site.register') }}" class="text-info fw-semibold text-decoration-none">
                                                    Create one
                                                </a>
                                            </p>
                                        </div>
                                    </form>

                                    <hr class="my-4">

                                    <div class="text-center">
                                        <p class="text-muted small mb-3">Or continue with</p>
                                        <div class="d-flex justify-content-center gap-3">
                                            <a href="#" class="btn btn-outline-secondary px-4">
                                                <i class="fab fa-google"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-secondary px-4">
                                                <i class="fab fa-facebook-f"></i>
                                            </a>
                                            <a href="#" class="btn btn-outline-secondary px-4">
                                                <i class="fab fa-apple"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('site.home') }}" class="text-white-50 text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
