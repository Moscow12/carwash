<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Subscription Plans</h3>
        <button wire:click="openCreate" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>New Plan
        </button>
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
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search plans...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Business Type</th>
                            <th>Fee</th>
                            <th>Interval</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                        <tr>
                            <td class="fw-semibold">{{ $plan->name }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $plan->business_type ? ucfirst(str_replace('_',' ', $plan->business_type)) : 'Any type' }}
                                </span>
                            </td>
                            <td>{{ number_format($plan->fee_amount, 2) }} {{ $plan->currency }}</td>
                            <td>{{ $plan->billing_interval_label }}</td>
                            <td>
                                <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button wire:click="openEdit('{{ $plan->id }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button wire:click="deletePlan('{{ $plan->id }}')" wire:confirm="Delete this plan? Businesses already subscribed will keep their history." class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No subscription plans found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $plans->links() }}</div>
    </div>

    @if($showPlanModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-file-invoice me-2"></i>{{ $editingPlanId ? 'Edit Plan' : 'New Plan' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Plan Name</label>
                        <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Business Type</label>
                        <select wire:model="business_type" class="form-select @error('business_type') is-invalid @enderror">
                            <option value="">Any type</option>
                            @foreach($businessTypes as $type)
                                <option value="{{ $type }}">{{ ucfirst(str_replace('_',' ', $type)) }}</option>
                            @endforeach
                        </select>
                        @error('business_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Fee Amount</label>
                            <input type="number" step="0.01" min="0" wire:model="fee_amount" class="form-control @error('fee_amount') is-invalid @enderror">
                            @error('fee_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <input type="text" maxlength="3" wire:model="currency" class="form-control @error('currency') is-invalid @enderror">
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Billing Interval</label>
                        <select wire:model="billing_interval" class="form-select @error('billing_interval') is-invalid @enderror">
                            @foreach($billingIntervals as $interval => $label)
                                <option value="{{ $interval }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('billing_interval')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="plan-is-active" wire:model="is_active">
                        <label class="form-check-label" for="plan-is-active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="savePlan">
                        <span wire:loading.remove wire:target="savePlan"><i class="ti ti-check me-1"></i>Save Plan</span>
                        <span wire:loading wire:target="savePlan">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
