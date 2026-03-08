<div>
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Module Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active">Modules</li>
                </ol>
            </nav>
        </div>
        <div>
            <button wire:click="scanModules" class="btn btn-outline-primary" wire:confirm="This will scan for new modules and regenerate autoload. Continue?">
                <span wire:loading.remove wire:target="scanModules">
                    <i class="ti ti-refresh me-1"></i>Scan Modules
                </span>
                <span wire:loading wire:target="scanModules">
                    <span class="spinner-border spinner-border-sm me-1"></span>Scanning...
                </span>
            </button>
            <button wire:click="openCreateModal" class="btn btn-primary ms-2">
                <i class="ti ti-plus me-1"></i>Create Module
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
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

    <!-- Info Card -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-start">
            <i class="ti ti-info-circle me-2 fs-4"></i>
            <div>
                <h6 class="mb-1">About Modules</h6>
                <p class="mb-0 small">Modules allow you to extend your application's functionality. You can enable or disable modules as needed. Each module is a self-contained package with its own routes, controllers, views, and migrations.</p>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="card-title mb-0">All Modules</h5>
                </div>
                <div class="col-auto">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-transparent"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search modules...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if (count($modules) > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Module</th>
                                <th class="py-3">Description</th>
                                <th class="py-3">Version</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-end px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $module)
                                <tr>
                                    <td class="px-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                <i class="ti ti-puzzle"></i>
                                            </div>
                                            <div>
                                                <span class="fw-semibold">{{ $module->getName() }}</span>
                                                <small class="d-block text-muted">{{ strtolower($module->getName()) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $module->getDescription() ?: 'No description available' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">v{{ $module->get('version', '1.0.0') }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($module->isEnabled())
                                            <span class="badge bg-success">
                                                <i class="ti ti-check me-1"></i>Enabled
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="ti ti-x me-1"></i>Disabled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end px-4">
                                        <button
                                            wire:click="toggleModule('{{ $module->getName() }}')"
                                            class="btn btn-sm btn-outline-{{ $module->isEnabled() ? 'warning' : 'success' }} me-1"
                                            wire:confirm="Are you sure you want to {{ $module->isEnabled() ? 'disable' : 'enable' }} this module?">
                                            <i class="ti ti-{{ $module->isEnabled() ? 'toggle-right' : 'toggle-left' }}"></i>
                                            {{ $module->isEnabled() ? 'Disable' : 'Enable' }}
                                        </button>
                                        <button
                                            wire:click="deleteModule('{{ $module->getName() }}')"
                                            wire:confirm="Are you sure you want to delete this module? This action cannot be undone."
                                            class="btn btn-sm btn-outline-danger">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="ti ti-puzzle-off fs-1 d-block mb-3 text-muted opacity-50"></i>
                    <h5 class="text-muted">No modules found</h5>
                    <p class="text-muted mb-3">Create your first module to extend application functionality</p>
                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Create Your First Module
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Module Statistics -->
    @if (count($modules) > 0)
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-puzzle fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ count($modules) }}</h3>
                                <p class="text-muted mb-0 small">Total Modules</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-check fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ collect($modules)->filter(fn($m) => $m->isEnabled())->count() }}</h3>
                                <p class="text-muted mb-0 small">Enabled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-secondary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-x fs-2 text-secondary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ collect($modules)->filter(fn($m) => !$m->isEnabled())->count() }}</h3>
                                <p class="text-muted mb-0 small">Disabled</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="ti ti-code fs-2 text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h3 class="mb-0">{{ config('modules.namespace', 'Modules') }}</h3>
                                <p class="text-muted mb-0 small">Namespace</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Module Modal -->
    @if ($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-puzzle me-2"></i>Create New Module
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                    </div>
                    <form wire:submit.prevent="createModule">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <small>Module name will be converted to StudlyCase (e.g., "my-module" becomes "MyModule")</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Module Name <span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    wire:model="newModuleName"
                                    class="form-control @error('newModuleName') is-invalid @enderror"
                                    placeholder="e.g., Blog, ECommerce, CustomerPortal"
                                    autofocus>
                                @error('newModuleName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Use letters, numbers, dashes, or underscores only.</small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <small><strong>Note:</strong> This will create a new module structure with routes, controllers, views, migrations, and more.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="createModule">
                                    <i class="ti ti-plus me-1"></i>Create Module
                                </span>
                                <span wire:loading wire:target="createModule">
                                    <span class="spinner-border spinner-border-sm me-1"></span>Creating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
