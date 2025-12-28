<div class="login-card card border-0 mx-auto">
    <div class="card-body p-5">
        <div class="text-center mb-4">
            <div class="brand-logo">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M17 17m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                    <path d="M5 17h-2v-6l2 -5h9l4 5h1a2 2 0 0 1 2 2v4h-2m-4 0h-6m-6 -6h15m-6 0v-5"></path>
                </svg>
            </div>
            <h3 class="fw-bold mb-2">CAMS</h3>
            <p class="text-muted mb-0">Carwash Management System</p>
        </div>

        <form method="POST" action="{{ route('admin.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email Address</label>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-control form-control-lg @error('email') is-invalid @enderror"
                       placeholder="admin@example.com"
                       autofocus
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password"
                       name="password"
                       class="form-control form-control-lg @error('password') is-invalid @enderror"
                       placeholder="Enter your password"
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox"
                           name="remember"
                           class="form-check-input"
                           id="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                Sign In
            </button>
        </form>

        <hr class="my-4">

        <div class="text-center text-muted small">
            <p class="mb-1"><strong>TechScales Company Limited</strong></p>
            <p class="mb-0">+255 659 811 966 | info@techscales.co.tz</p>
        </div>
    </div>
</div>
