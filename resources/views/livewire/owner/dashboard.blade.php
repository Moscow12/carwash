<div>
    @if (session()->has('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Owner Dashboard</h3>
            <p class="text-muted mb-0">{{ now()->format('l, F j, Y') }} · Revenue across all your businesses</p>
        </div>
        <a href="{{ route('owner.businesses') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-building me-1"></i> My Businesses
        </a>
    </div>

    @if($kpis['businesses'] === 0)
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            You don't have any businesses yet. Create one under
            <a href="{{ route('owner.businesses') }}" class="alert-link">My Businesses</a> to start tracking revenue.
        </div>
    @else

    {{-- KPI strip --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-building-store fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Businesses</div>
                        <div class="h4 mb-0">{{ number_format($kpis['businesses']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">This Month</div>
                        <div class="h5 mb-0">TZS {{ number_format($kpis['revenue_month'], 0) }}</div>
                        <small class="text-{{ $momChange >= 0 ? 'success' : 'danger' }}">
                            <i class="ti ti-trending-{{ $momChange >= 0 ? 'up' : 'down' }}"></i>
                            {{ $momChange >= 0 ? '+' : '' }}{{ $momChange }}% vs last month
                        </small>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-calendar-dollar fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Today</div>
                        <div class="h5 mb-0">TZS {{ number_format($kpis['revenue_today'], 0) }}</div>
                        <small class="text-muted">{{ number_format($kpis['payments_month']) }} payment(s) this month</small>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-receipt-2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">All-time Revenue</div>
                        <div class="h5 mb-0">TZS {{ number_format($kpis['revenue_total'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Revenue trend + per-business breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="ti ti-chart-bar text-primary me-2"></i>Revenue Trend ({{ $trendMonths }} months)</h6>
                    </div>

                    {{-- CSS bar chart --}}
                    <div class="d-flex align-items-end justify-content-between gap-2" style="height:200px;">
                        @foreach($trend as $point)
                        @php
                            $heightPct = $trendMax > 0 ? round(($point['amount'] / $trendMax) * 100) : 0;
                            $isCurrent = $loop->last;
                        @endphp
                        <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end" title="{{ $point['label'] }}: TZS {{ number_format($point['amount'], 0) }}">
                            <small class="text-muted mb-1" style="font-size:.7rem;">{{ $point['amount'] > 0 ? number_format($point['amount'] / 1000, 0) . 'k' : '' }}</small>
                            <div class="w-100 rounded-top {{ $isCurrent ? 'bg-primary' : 'bg-primary-subtle' }}"
                                 style="height: {{ max(2, $heightPct) }}%; min-height:2px; transition:height .3s;"></div>
                            <small class="text-muted mt-2">{{ $point['label'] }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-3"><i class="ti ti-building text-primary me-2"></i>By Business (this month)</h6>

                    @forelse($byBusiness as $b)
                    @php
                        $share = $kpis['revenue_month'] > 0 ? round(($b['revenue_month'] / $kpis['revenue_month']) * 100) : 0;
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="fw-medium">
                                {{ $b['name'] }}
                                <span class="badge bg-light text-muted border ms-1">{{ ucfirst($b['type']) }}</span>
                            </span>
                            <span class="fw-bold">TZS {{ number_format($b['revenue_month'], 0) }}</span>
                        </div>
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar bg-success" style="width:{{ $share }}%;"></div>
                        </div>
                        <small class="text-muted">All-time: TZS {{ number_format($b['revenue_total'], 0) }}</small>
                    </div>
                    @empty
                    <div class="text-center text-muted small py-4">No revenue recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent payments --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="ti ti-receipt text-success me-2"></i>Recent Payments</h6>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Date</th>
                            <th>Business</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPayments as $p)
                        <tr wire:key="pay-{{ $p->id }}">
                            <td><small>{{ $p->paid_at?->format('M d, Y') ?? $p->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <span class="fw-medium small">{{ $p->business?->name ?? '—' }}</span>
                                <span class="badge bg-light text-muted border ms-1">{{ ucfirst($p->business?->type) }}</span>
                            </td>
                            <td><small class="text-muted">{{ $p->paymentMethod?->name ?? '—' }}</small></td>
                            <td class="text-end fw-bold text-success">TZS {{ number_format($p->amount_local, 0) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4 small">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif
</div>
