<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">All Businesses</h3>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search businesses...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Owner</th>
                            <th>Type</th>
                            <th>Modules</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businesses as $business)
                        <tr>
                            <td class="fw-semibold">{{ $business->name }}</td>
                            <td>{{ $business->owner->name ?? 'No Owner' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst(str_replace('_',' ', $business->type ?? '—')) }}</span></td>
                            <td>
                                @forelse($business->modules->filter(fn($m) => $m->pivot->is_active ?? true) as $module)
                                    <span class="badge bg-primary-subtle text-primary me-1 mb-1">
                                        <i class="{{ $module->icon ?? 'ti ti-puzzle' }} me-1"></i>{{ $module->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small">No modules</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="badge bg-{{ $business->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($business->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button wire:click="openModules('{{ $business->id }}')" class="btn btn-sm btn-outline-primary" title="Manage modules">
                                    <i class="ti ti-puzzle me-1"></i> Modules
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No businesses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $businesses->links() }}</div>
    </div>

    {{-- Manage Modules Modal --}}
    @if($showModulesModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-puzzle me-2"></i>Modules — {{ $manageBusinessName }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModules"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Choose which modules this business can access. The module matching the business
                        type is assigned automatically at registration; you can grant or revoke others here.
                    </p>
                    <div class="row g-2">
                        @foreach($allModules as $module)
                        <div class="col-12">
                            <div class="form-check form-switch p-2 rounded border d-flex align-items-center" style="padding-left: 3rem !important;">
                                <input type="checkbox"
                                       class="form-check-input mt-0"
                                       id="mod-{{ $module->id }}"
                                       style="cursor:pointer; width:2.5em; height:1.4em;"
                                       wire:model="selectedModules.{{ $module->id }}">
                                <label class="form-check-label ms-2 mb-0 fw-medium" for="mod-{{ $module->id }}" style="cursor:pointer;">
                                    <i class="{{ $module->icon ?? 'ti ti-puzzle' }} me-1 text-primary"></i>{{ $module->name }}
                                    <small class="d-block text-muted fw-normal">{{ $module->key }}</small>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModules">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveModules">
                        <span wire:loading.remove wire:target="saveModules"><i class="ti ti-check me-1"></i>Save Modules</span>
                        <span wire:loading wire:target="saveModules">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
