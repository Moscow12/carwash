<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Dashboard</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="text-muted small">
            <i class="ti ti-calendar me-1"></i>{{ date('l, F j, Y') }}
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-primary bg-opacity-10 text-primary rounded-3">
                                <i class="ti ti-users fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $usersCount }}</h3>
                            <p class="text-muted mb-0 small">Users</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-success bg-opacity-10 text-success rounded-3">
                                <i class="ti ti-world fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $countriesCount }}</h3>
                            <p class="text-muted mb-0 small">Countries</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-info bg-opacity-10 text-info rounded-3">
                                <i class="ti ti-map-2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $regionsCount }}</h3>
                            <p class="text-muted mb-0 small">Regions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-warning bg-opacity-10 text-warning rounded-3">
                                <i class="ti ti-building-community fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $districtsCount }}</h3>
                            <p class="text-muted mb-0 small">Districts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-danger bg-opacity-10 text-danger rounded-3">
                                <i class="ti ti-home-2 fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $wardsCount }}</h3>
                            <p class="text-muted mb-0 small">Wards</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-secondary bg-opacity-10 text-secondary rounded-3">
                                <i class="ti ti-road fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-0 fw-bold">{{ $streetsCount }}</h3>
                            <p class="text-muted mb-0 small">Streets</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.users') }}" class="card bg-primary bg-opacity-10 border-0 text-decoration-none h-100">
                                <div class="card-body text-center py-4">
                                    <i class="ti ti-user-plus text-primary fs-1 mb-2"></i>
                                    <h6 class="text-primary mb-0">Manage Users</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.countries') }}" class="card bg-success bg-opacity-10 border-0 text-decoration-none h-100">
                                <div class="card-body text-center py-4">
                                    <i class="ti ti-world text-success fs-1 mb-2"></i>
                                    <h6 class="text-success mb-0">Manage Locations</h6>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.settings') }}" class="card bg-info bg-opacity-10 border-0 text-decoration-none h-100">
                                <div class="card-body text-center py-4">
                                    <i class="ti ti-settings text-info fs-1 mb-2"></i>
                                    <h6 class="text-info mb-0">System Settings</h6>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Info</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">App Name</span>
                            <span class="fw-semibold">CAMS</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Version</span>
                            <span class="fw-semibold">1.0.0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Developer</span>
                            <span class="fw-semibold">TechScales</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">PHP Version</span>
                            <span class="fw-semibold">{{ PHP_VERSION }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted">Laravel</span>
                            <span class="fw-semibold">{{ app()->version() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Hierarchy -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Location Hierarchy</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col">
                    <div class="border rounded p-3">
                        <i class="ti ti-world text-primary fs-2 mb-2"></i>
                        <h5 class="fw-bold">{{ $countriesCount }}</h5>
                        <p class="text-muted mb-0 small">Countries</p>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <i class="ti ti-arrow-right text-muted fs-4"></i>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <i class="ti ti-map-2 text-info fs-2 mb-2"></i>
                        <h5 class="fw-bold">{{ $regionsCount }}</h5>
                        <p class="text-muted mb-0 small">Regions</p>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <i class="ti ti-arrow-right text-muted fs-4"></i>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <i class="ti ti-building-community text-warning fs-2 mb-2"></i>
                        <h5 class="fw-bold">{{ $districtsCount }}</h5>
                        <p class="text-muted mb-0 small">Districts</p>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <i class="ti ti-arrow-right text-muted fs-4"></i>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <i class="ti ti-home-2 text-danger fs-2 mb-2"></i>
                        <h5 class="fw-bold">{{ $wardsCount }}</h5>
                        <p class="text-muted mb-0 small">Wards</p>
                    </div>
                </div>
                <div class="col-auto d-flex align-items-center">
                    <i class="ti ti-arrow-right text-muted fs-4"></i>
                </div>
                <div class="col">
                    <div class="border rounded p-3">
                        <i class="ti ti-road text-secondary fs-2 mb-2"></i>
                        <h5 class="fw-bold">{{ $streetsCount }}</h5>
                        <p class="text-muted mb-0 small">Streets</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
