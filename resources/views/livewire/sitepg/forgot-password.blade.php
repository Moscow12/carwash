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
                                    <i class="fas fa-key text-info" style="font-size: 3rem;"></i>
                                </div>
                                <h3 class="fw-bold mb-2">Forgot Password?</h3>
                                <p class="text-muted">Enter your email and we'll send you a link to reset your password.</p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                                </div>
                            @endif

                            <form wire:submit.prevent="sendResetLink">
                                <div class="mb-4">
                                    <label for="fp-email" class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-muted"></i>
                                        </span>
                                        <input type="email"
                                               id="fp-email"
                                               name="email"
                                               autocomplete="email"
                                               autofocus
                                               wire:model.blur="email"
                                               class="form-control form-control-site border-start-0 ps-0 @error('email') is-invalid @enderror"
                                               placeholder="Enter your email">
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-cams btn-lg w-100 mb-4"
                                        wire:loading.attr="disabled" wire:target="sendResetLink">
                                    <span wire:loading.remove wire:target="sendResetLink">
                                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                                    </span>
                                    <span wire:loading wire:target="sendResetLink">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Sending...
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
