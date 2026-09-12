<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">My Subscription</h3>
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

    @if (!$business)
        <div class="alert alert-warning">No business found for your account.</div>
    @else
        @if ($subscription && $subscription->isExpiringSoon())
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>
                Your subscription expires
                {{ $subscription->daysUntilExpiry() === 0 ? 'today' : 'in ' . $subscription->daysUntilExpiry() . ' day' . ($subscription->daysUntilExpiry() === 1 ? '' : 's') }}
                ({{ $subscription->expires_at->format('M j, Y') }}). Renew below to avoid interruption.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Current Plan</h5>
                @if ($subscription && $subscription->plan)
                    <p class="mb-1"><strong>{{ $subscription->plan->name }}</strong> — {{ number_format($subscription->plan->fee_amount, 2) }} {{ $subscription->plan->currency }} / {{ $subscription->plan->billing_interval_label }}</p>
                    <p class="mb-0">
                        Status:
                        <span class="badge bg-{{ $subscription->isExpiringSoon() ? 'warning' : ($subscription->isActive() ? 'success' : 'secondary') }}">
                            {{ $subscription->isExpiringSoon() ? 'Expiring Soon' : ucfirst($subscription->status) }}
                        </span>
                        @if ($subscription->expires_at)
                            &mdash; expires {{ $subscription->expires_at->format('Y-m-d') }}
                        @endif
                    </p>
                @else
                    <p class="text-muted mb-0">No active subscription yet.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0">Available Plans</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Plan</th>
                                <th>Fee</th>
                                <th>Interval</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $plan)
                            <tr>
                                <td class="fw-semibold">{{ $plan->name }}</td>
                                <td>{{ number_format($plan->fee_amount, 2) }} {{ $plan->currency }}</td>
                                <td>{{ $plan->billing_interval_label }}</td>
                                <td class="text-end">
                                    <button wire:click="payNow('{{ $plan->id }}')" class="btn btn-sm btn-primary" wire:loading.attr="disabled" wire:target="payNow('{{ $plan->id }}')">
                                        <span wire:loading.remove wire:target="payNow('{{ $plan->id }}')"><i class="ti ti-credit-card me-1"></i>Pay Now</span>
                                        <span wire:loading wire:target="payNow('{{ $plan->id }}')">Redirecting...</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No plans available for your business type</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
