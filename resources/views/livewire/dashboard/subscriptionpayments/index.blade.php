<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Subscription Payments</h3>
        <a href="{{ route('admin.business-subscriptions') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Manage Subscriptions
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Completed Payments</div>
                    <div class="fs-4 fw-bold">{{ number_format($totals['completed'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Transactions</div>
                    <div class="fs-4 fw-bold">{{ $totals['count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by business or reference...">
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All statuses</option>
                        <option value="pending">Pending</option>
                        <option value="submitted">Submitted</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="reversed">Reversed</option>
                        <option value="invalid">Invalid</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="environmentFilter" class="form-select">
                        <option value="">All types</option>
                        <option value="test">Pesapal (Test)</option>
                        <option value="live">Pesapal (Live)</option>
                        <option value="manual">Manual</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Business</th>
                            <th>Plan</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td class="small font-monospace">{{ $txn->merchant_reference }}</td>
                            <td>{{ $txn->business->name ?? '—' }}</td>
                            <td>{{ $txn->plan->name ?? '—' }}</td>
                            <td>{{ number_format($txn->amount, 2) }} {{ $txn->currency }}</td>
                            <td>
                                <span class="badge bg-{{ $txn->environment === 'manual' ? 'info' : ($txn->environment === 'live' ? 'dark' : 'light text-dark border') }}">
                                    {{ $txn->environment === 'manual' ? 'Manual' : ucfirst($txn->environment) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ match($txn->status) { 'completed' => 'success', 'failed', 'invalid' => 'danger', 'reversed' => 'warning', default => 'secondary' } }}">
                                    {{ ucfirst($txn->status) }}
                                </span>
                            </td>
                            <td>{{ $txn->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $transactions->links() }}</div>
    </div>
</div>
