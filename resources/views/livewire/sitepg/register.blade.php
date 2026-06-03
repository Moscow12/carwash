<div>
    <section class="hero-section" style="min-height: 100vh; display: flex; align-items: center; padding: 100px 0;">
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
                                        <h2 class="text-white fw-bold mb-3">Join CAMS Today!</h2>
                                        <p class="text-white-50 mb-4">
                                            Create your account and start managing your carwash business like a pro.
                                        </p>

                                        <div class="mt-5">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">Free trial available</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">No credit card required</span>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">24/7 customer support</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle text-info me-3"></i>
                                                <span class="text-white-50">Cancel anytime</span>
                                            </div>
                                        </div>

                                        <div class="mt-5 pt-4">
                                            <p class="text-white-50 small mb-2">Trusted by</p>
                                            <h3 class="text-info fw-bold">500+ Businesses</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side - Register Form -->
                            <div class="col-lg-7">
                                <div class="p-4 p-lg-5">
                                    <div class="text-center mb-4">
                                        <a href="{{ route('site.home') }}" class="d-inline-flex align-items-center text-decoration-none mb-4">
                                            <i class="fas fa-car-side text-info me-2 fs-4"></i>
                                            <span class="navbar-brand-text">CAMS</span>
                                        </a>
                                        <h3 class="fw-bold mb-2">Create Account</h3>
                                        <p class="text-muted">Fill in your details to get started</p>
                                    </div>

                                    @if (session('success'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session('success') }}
                                        </div>
                                    @endif

                                    <form wire:submit.prevent="register" novalidate>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="reg-name" class="form-label fw-semibold">Full Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </span>
                                                    <input type="text"
                                                           id="reg-name"
                                                           name="name"
                                                           autocomplete="name"
                                                           wire:model.blur="name"
                                                           class="form-control form-control-site border-start-0 ps-0 @error('name') is-invalid @enderror"
                                                           placeholder="Enter your full name">
                                                </div>
                                                @error('name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reg-email" class="form-label fw-semibold">Email Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-envelope text-muted"></i>
                                                    </span>
                                                    <input type="email"
                                                           id="reg-email"
                                                           name="email"
                                                           autocomplete="email"
                                                           wire:model.blur="email"
                                                           class="form-control form-control-site border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                           placeholder="Enter your email">
                                                </div>
                                                @error('email')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reg-phone" class="form-label fw-semibold">Phone Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-phone text-muted"></i>
                                                    </span>
                                                    <input type="tel"
                                                           id="reg-phone"
                                                           name="phone"
                                                           autocomplete="tel"
                                                           wire:model.blur="phone"
                                                           class="form-control form-control-site border-start-0 ps-0 @error('phone') is-invalid @enderror"
                                                           placeholder="e.g. +255 700 000 000">
                                                </div>
                                                @error('phone')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reg-password" class="form-label fw-semibold">Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-lock text-muted"></i>
                                                    </span>
                                                    <input type="password"
                                                           id="reg-password"
                                                           name="password"
                                                           autocomplete="new-password"
                                                           wire:model.blur="password"
                                                           class="form-control form-control-site border-start-0 ps-0 @error('password') is-invalid @enderror"
                                                           placeholder="At least 8 characters">
                                                </div>
                                                @error('password')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reg-password-confirm" class="form-label fw-semibold">Confirm Password</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-lock text-muted"></i>
                                                    </span>
                                                    <input type="password"
                                                           id="reg-password-confirm"
                                                           name="password_confirmation"
                                                           autocomplete="new-password"
                                                           wire:model.blur="password_confirmation"
                                                           class="form-control form-control-site border-start-0 ps-0"
                                                           placeholder="Re-enter your password">
                                                </div>
                                            </div>

                                            <!-- Register a business toggle -->
                                            <div class="col-12">
                                                <div class="form-check form-switch p-3 rounded border bg-light d-flex align-items-center" style="padding-left: 3.5rem !important;">
                                                    <input type="checkbox"
                                                           class="form-check-input mt-0"
                                                           id="registerBusiness"
                                                           role="switch"
                                                           style="cursor: pointer; width: 2.75em; height: 1.5em;"
                                                           wire:model.live="registerBusiness">
                                                    <label class="form-check-label fw-semibold ms-2 mb-0" for="registerBusiness" style="cursor: pointer;">
                                                        <i class="fas fa-store text-info me-1"></i>
                                                        I want to register my business
                                                        <small class="d-block text-muted fw-normal">Set up a business account and access the owner dashboard.</small>
                                                    </label>
                                                </div>
                                            </div>

                                            @if($registerBusiness)
                                            <div class="col-md-6">
                                                <label for="reg-business-name" class="form-label fw-semibold">Business Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-briefcase text-muted"></i>
                                                    </span>
                                                    <input type="text"
                                                           id="reg-business-name"
                                                           wire:model.blur="business_name"
                                                           class="form-control form-control-site border-start-0 ps-0 @error('business_name') is-invalid @enderror"
                                                           placeholder="e.g. Sparkle Carwash">
                                                </div>
                                                @error('business_name')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label for="reg-business-type" class="form-label fw-semibold">Business Type</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-tags text-muted"></i>
                                                    </span>
                                                    <select id="reg-business-type"
                                                            wire:model="business_type"
                                                            class="form-select form-control-site border-start-0 ps-0 @error('business_type') is-invalid @enderror">
                                                        <option value="">Choose business type...</option>
                                                        @foreach($businessTypes as $value => $label)
                                                            <option value="{{ $value }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                @error('business_type')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            @endif

                                            <div class="col-12">
                                                <div class="form-check d-flex align-items-center">
                                                    <input type="checkbox"
                                                           class="form-check-input @error('terms') is-invalid @enderror"
                                                           id="terms"
                                                           style="cursor: pointer; width: 1.25em; height: 1.25em;"
                                                           wire:model="terms">
                                                    <label class="form-check-label text-muted ms-2 mb-0" for="terms" style="cursor: pointer;">
                                                        I agree to the <a href="#" class="text-info">Terms of Service</a>
                                                        and <a href="#" class="text-info">Privacy Policy</a>
                                                    </label>
                                                </div>
                                                @error('terms')
                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12">
                                                <button type="submit"
                                                        class="btn btn-cams btn-lg w-100"
                                                        wire:loading.attr="disabled"
                                                        wire:target="register">
                                                    <span wire:loading.remove wire:target="register">
                                                        <i class="fas fa-user-plus me-2"></i>{{ $registerBusiness ? 'Create Business Account' : 'Create Account' }}
                                                    </span>
                                                    <span wire:loading wire:target="register">
                                                        <i class="fas fa-spinner fa-spin me-2"></i>Creating account...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="text-center mt-4">
                                        <p class="text-muted mb-0">
                                            Already have an account?
                                            <a href="{{ route('site.login') }}" class="text-info fw-semibold text-decoration-none">
                                                Sign in
                                            </a>
                                        </p>
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
