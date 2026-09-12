<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Manage Subscriptions</h3>
        <a href="{{ route('admin.subscription-payments') }}" class="btn btn-outline-secondary">
            <i class="ti ti-report-money me-1"></i>Payments Report
        </a>
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
                            <th>Business</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Expires</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($businesses as $business)
                        @php($sub = $business->subscription)
                        <tr>
                            <td class="fw-semibold">{{ $business->name }}</td>
                            <td>{{ $sub && $sub->plan ? $sub->plan->name : '—' }}</td>
                            <td>
                                @if ($sub)
                                    <span class="badge bg-{{ $sub->isExpiringSoon() ? 'warning' : ($sub->isActive() ? 'success' : 'secondary') }}">
                                        {{ $sub->isExpiringSoon() ? 'Expiring Soon' : ucfirst($sub->status) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">None</span>
                                @endif
                            </td>
                            <td>{{ $sub && $sub->expires_at ? $sub->expires_at->format('Y-m-d') : '—' }}</td>
                            <td class="text-end">
                                <button wire:click="openManage('{{ $business->id }}')" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-credit-card me-1"></i>Manage
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No businesses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $businesses->links() }}</div>
    </div>

    @if($showManageModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-credit-card me-2"></i>Manage Subscription — {{ $manageBusinessName }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeManage"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-pills nav-fill mb-3">
                        <li class="nav-item">
                            <button type="button" wire:click="$set('manageAction', 'assign')" class="nav-link {{ $manageAction === 'assign' ? 'active' : '' }}">Assign / Change</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" wire:click="$set('manageAction', 'extend')" class="nav-link {{ $manageAction === 'extend' ? 'active' : '' }}">Extend</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" wire:click="$set('manageAction', 'manual')" class="nav-link {{ $manageAction === 'manual' ? 'active' : '' }}">Manual Payment</button>
                        </li>
                        <li class="nav-item">
                            <button type="button" wire:click="$set('manageAction', 'pesapal')" class="nav-link {{ $manageAction === 'pesapal' ? 'active' : '' }}">Pesapal</button>
                        </li>
                    </ul>

                    @if ($manageAction === 'assign')
                        <p class="text-muted small">Directly assign or change this business's plan and activate it immediately. No payment is recorded — use this for admin overrides.</p>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select wire:model="selectedPlanId" class="form-select @error('selectedPlanId') is-invalid @enderror">
                                <option value="">Select a plan...</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} — {{ number_format($plan->fee_amount, 2) }} {{ $plan->currency }} / {{ $plan->billing_interval_label }}</option>
                                @endforeach
                            </select>
                            @error('selectedPlanId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="button" class="btn btn-primary" wire:click="assignPlan">
                            <span wire:loading.remove wire:target="assignPlan"><i class="ti ti-check me-1"></i>Assign & Activate</span>
                            <span wire:loading wire:target="assignPlan">Saving...</span>
                        </button>
                    @endif

                    @if ($manageAction === 'extend')
                        <p class="text-muted small">Extend the current plan's period. If still active, extension adds on top of the current expiry date; otherwise it starts from today.</p>
                        <div class="mb-3">
                            <label class="form-label">Number of billing cycles to add</label>
                            <input type="number" min="1" max="24" wire:model="extendCycles" class="form-control @error('extendCycles') is-invalid @enderror">
                            @error('extendCycles')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="button" class="btn btn-primary" wire:click="extendSubscription">
                            <span wire:loading.remove wire:target="extendSubscription"><i class="ti ti-calendar-plus me-1"></i>Extend</span>
                            <span wire:loading wire:target="extendSubscription">Saving...</span>
                        </button>
                    @endif

                    @if ($manageAction === 'manual')
                        <p class="text-muted small">Record an offline payment (cash, bank transfer, etc). This creates a completed transaction marked as <strong>manual</strong> in the payments report and activates the subscription immediately.</p>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select wire:model="selectedPlanId" class="form-select @error('selectedPlanId') is-invalid @enderror">
                                <option value="">Select a plan...</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} — {{ number_format($plan->fee_amount, 2) }} {{ $plan->currency }} / {{ $plan->billing_interval_label }}</option>
                                @endforeach
                            </select>
                            @error('selectedPlanId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="button" class="btn btn-success" wire:click="recordManualPayment" wire:confirm="Confirm the offline payment was received before marking this as paid.">
                            <span wire:loading.remove wire:target="recordManualPayment"><i class="ti ti-cash me-1"></i>Mark as Paid</span>
                            <span wire:loading wire:target="recordManualPayment">Saving...</span>
                        </button>
                    @endif

                    @if ($manageAction === 'pesapal')
                        <p class="text-muted small">Start a real Pesapal payment for this business. You will be redirected to Pesapal's hosted payment page to complete it.</p>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select wire:model="selectedPlanId" class="form-select @error('selectedPlanId') is-invalid @enderror">
                                <option value="">Select a plan...</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} — {{ number_format($plan->fee_amount, 2) }} {{ $plan->currency }} / {{ $plan->billing_interval_label }}</option>
                                @endforeach
                            </select>
                            @error('selectedPlanId')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="button" class="btn btn-primary" wire:click="payViaPesapal" wire:loading.attr="disabled" wire:target="payViaPesapal">
                            <span wire:loading.remove wire:target="payViaPesapal"><i class="ti ti-brand-google-analytics me-1"></i>Pay via Pesapal</span>
                            <span wire:loading wire:target="payViaPesapal">Redirecting...</span>
                        </button>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeManage">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
