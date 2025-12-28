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
