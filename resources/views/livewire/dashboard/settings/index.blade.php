<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">System Settings</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Sidebar Tabs -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body p-0">
                    <div class="nav flex-column nav-pills">
                        <button wire:click="setTab('general')" class="nav-link text-start rounded-0 {{ $activeTab === 'general' ? 'active' : '' }}">
                            <i class="ti ti-settings me-2"></i>General Settings
                        </button>
                        <button wire:click="setTab('profile')" class="nav-link text-start rounded-0 {{ $activeTab === 'profile' ? 'active' : '' }}">
                            <i class="ti ti-user me-2"></i>Profile Settings
                        </button>
                        <button wire:click="setTab('password')" class="nav-link text-start rounded-0 {{ $activeTab === 'password' ? 'active' : '' }}">
                            <i class="ti ti-lock me-2"></i>Change Password
                        </button>
                        <button wire:click="setTab('tools')" class="nav-link text-start rounded-0 {{ $activeTab === 'tools' ? 'active' : '' }}">
                            <i class="ti ti-tool me-2"></i>System Tools
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="col-lg-9">
            <!-- General Settings -->
            @if ($activeTab === 'general')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">General Settings</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveGeneral">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" wire:model="siteName" class="form-control @error('siteName') is-invalid @enderror">
                                    @error('siteName')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Email</label>
                                    <input type="email" wire:model="siteEmail" class="form-control @error('siteEmail') is-invalid @enderror">
                                    @error('siteEmail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Phone</label>
                                    <input type="text" wire:model="sitePhone" class="form-control @error('sitePhone') is-invalid @enderror">
                                    @error('sitePhone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Site Address</label>
                                    <input type="text" wire:model="siteAddress" class="form-control @error('siteAddress') is-invalid @enderror">
                                    @error('siteAddress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <span wire:loading.remove wire:target="saveGeneral">
                                            <i class="ti ti-device-floppy me-1"></i>Save Changes
                                        </span>
                                        <span wire:loading wire:target="saveGeneral">Saving...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Profile Settings -->
            @if ($activeTab === 'profile')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Settings</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveProfile">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <span wire:loading.remove wire:target="saveProfile">
                                            <i class="ti ti-device-floppy me-1"></i>Update Profile
                                        </span>
                                        <span wire:loading wire:target="saveProfile">Saving...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Change Password -->
            @if ($activeTab === 'password')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="changePassword">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" wire:model="current_password" class="form-control @error('current_password') is-invalid @enderror">
                                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">New Password</label>
                                    <input type="password" wire:model="new_password" class="form-control @error('new_password') is-invalid @enderror">
                                    @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" wire:model="new_password_confirmation" class="form-control">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <span wire:loading.remove wire:target="changePassword">
                                            <i class="ti ti-lock me-1"></i>Change Password
                                        </span>
                                        <span wire:loading wire:target="changePassword">Changing...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- System Tools -->
            @if ($activeTab === 'tools')
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">System Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Log Viewer -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-md bg-primary-subtle text-primary rounded">
                                                    <i class="ti ti-file-text fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Log Viewer</h6>
                                                <p class="text-muted small mb-3">View and analyze application logs in real-time</p>
                                                <a href="/log-viewer" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="ti ti-external-link me-1"></i>Open Log Viewer
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Cache Management -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-md bg-info-subtle text-info rounded">
                                                    <i class="ti ti-refresh fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Cache Management</h6>
                                                <p class="text-muted small mb-3">Clear application and configuration cache</p>
                                                <button type="button" wire:click="clearCache" class="btn btn-sm btn-outline-info" wire:confirm="Are you sure you want to clear all caches?">
                                                    <span wire:loading.remove wire:target="clearCache">
                                                        <i class="ti ti-refresh me-1"></i>Clear Cache
                                                    </span>
                                                    <span wire:loading wire:target="clearCache">
                                                        <span class="spinner-border spinner-border-sm me-1"></span>Clearing...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Database Backup -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-md bg-success-subtle text-success rounded">
                                                    <i class="ti ti-database fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">Database Backup</h6>
                                                <p class="text-muted small mb-3">Create and manage database backups</p>
                                                <button type="button" wire:click="createBackup" class="btn btn-sm btn-outline-success" wire:confirm="This will create a database backup. Continue?">
                                                    <span wire:loading.remove wire:target="createBackup">
                                                        <i class="ti ti-download me-1"></i>Create Backup
                                                    </span>
                                                    <span wire:loading wire:target="createBackup">
                                                        <span class="spinner-border spinner-border-sm me-1"></span>Creating...
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- System Info -->
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-shrink-0">
                                                <div class="avatar avatar-md bg-warning-subtle text-warning rounded">
                                                    <i class="ti ti-info-circle fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">System Information</h6>
                                                <p class="text-muted small mb-3">View server and application details</p>
                                                <button type="button" wire:click="toggleSystemInfo" class="btn btn-sm btn-outline-warning">
                                                    <i class="ti ti-{{ $showSystemInfo ? 'eye-off' : 'info-circle' }} me-1"></i>
                                                    {{ $showSystemInfo ? 'Hide Info' : 'View Info' }}
                                                </button>
                                            </div>
                                        </div>

                                        @if ($showSystemInfo)
                                            <div class="mt-3 pt-3 border-top">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr>
                                                        <td class="text-muted" style="width: 50%;">PHP Version</td>
                                                        <td class="fw-semibold">{{ $systemInfo['php_version'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Laravel Version</td>
                                                        <td class="fw-semibold">{{ $systemInfo['laravel_version'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Server Software</td>
                                                        <td class="fw-semibold">{{ $systemInfo['server_software'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Database</td>
                                                        <td class="fw-semibold">{{ $systemInfo['database_type'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Environment</td>
                                                        <td>
                                                            <span class="badge bg-{{ $systemInfo['app_env'] === 'production' ? 'success' : 'warning' }}-subtle text-{{ $systemInfo['app_env'] === 'production' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($systemInfo['app_env']) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Debug Mode</td>
                                                        <td class="fw-semibold">{{ $systemInfo['app_debug'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Free Disk Space</td>
                                                        <td class="fw-semibold">{{ $systemInfo['disk_free_space'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Memory Limit</td>
                                                        <td class="fw-semibold">{{ $systemInfo['memory_limit'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Max Upload Size</td>
                                                        <td class="fw-semibold">{{ $systemInfo['max_upload_size'] }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">Timezone</td>
                                                        <td class="fw-semibold">{{ $systemInfo['timezone'] }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- System Info Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Developer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0">Company</td>
                                    <td class="fw-semibold">TechScales Company Limited</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Phone</td>
                                    <td class="fw-semibold">+255 659 811 966</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td class="text-muted ps-0">Email</td>
                                    <td class="fw-semibold">info@techscales.co.tz</td>
                                </tr>
                                <tr>
                                    <td class="text-muted ps-0">Location</td>
                                    <td class="fw-semibold">Dodoma, Tanzania</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
