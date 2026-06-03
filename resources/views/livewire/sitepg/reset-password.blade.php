<div>
    <section class="hero-section" style="min-height: 100vh; display: flex; align-items: center;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="auth-card">
                        <div class="p-4 p-lg-5">
                            <div class="text-center mb-4">
                                <a href="{{ route('site.home') }}" class="d-inline-flex align-items-center text-decoration-none mb-4">
                                    <i class="fas fa-car-side text-info me-2 fs-4"></i>
                                    <span class="navbar-brand-text">CAMS</span>
                                </a>
                                <div class="mb-3">
                                    <i class="fas fa-lock-open text-info" style="font-size: 3rem;"></i>
                                </div>
                                <h3 class="fw-bold mb-2">Reset Password</h3>
                                <p class="text-muted">Choose a new password for your account.</p>
                            </div>

                            <form wire:submit.prevent="resetPassword" x-data="{ showPassword: false }">
                                <div class="mb-4">
                                    <label for="rp-email" class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email"
                                               id="rp-email"
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

                                <div class="mb-4">
                                    <label for="rp-password" class="form-label fw-semibold">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input :type="showPassword ? 'text' : 'password'"
                                               id="rp-password"
                                               name="password"
                                               autocomplete="new-password"
                                               wire:model.blur="password"
                                               class="form-control form-control-site border-start-0 ps-0 @error('password') is-invalid @enderror"
                                               placeholder="At least 8 characters">
                                        <button type="button"
                                                class="input-group-text bg-light border-start-0"
                                                style="cursor: pointer;"
                                                @click="showPassword = !showPassword"
                                                tabindex="-1">
                                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>
                                    @error('password')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="rp-password-confirm" class="form-label fw-semibold">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                        <input :type="showPassword ? 'text' : 'password'"
                                               id="rp-password-confirm"
                                               name="password_confirmation"
                                               autocomplete="new-password"
                                               wire:model.blur="password_confirmation"
                                               class="form-control form-control-site border-start-0 ps-0"
                                               placeholder="Re-enter your new password">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-cams btn-lg w-100 mb-4"
                                        wire:loading.attr="disabled" wire:target="resetPassword">
                                    <span wire:loading.remove wire:target="resetPassword">
                                        <i class="fas fa-check me-2"></i>Reset Password
                                    </span>
                                    <span wire:loading wire:target="resetPassword">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Resetting...
                                    </span>
                                </button>

                                <div class="text-center">
                                    <a href="{{ route('site.login') }}" class="text-info fw-semibold text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Sign In
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
