<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Sales Dashboard</h3>
            <p class="text-muted mb-0">{{ now()->format('l, F j, Y') }} · POS &amp; item sales overview</p>
        </div>
        <a href="{{ route('owner.posscreen') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Sale
        </a>
    </div>

    {{-- Business filter --}}
    @if($businesses->count() > 1)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label mb-1 small text-muted">Business</label>
                    <select wire:model.live="selectedBusiness" class="form-select">
                        <option value="">All businesses</option>
                        @foreach($businesses as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($businesses->isEmpty())
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            You don't have any businesses yet. Create one under
            <a href="{{ route('owner.businesses') }}" class="alert-link">My Businesses</a> to start selling.
        </div>
    @else

    {{-- Today / this-month headline --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Today's Revenue</p>
                            <h2 class="mb-0">TZS {{ number_format($kpis['today_revenue'], 0) }}</h2>
                            <small class="opacity-75">{{ number_format($kpis['today_count']) }} sale(s)</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-receipt fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">This Month</p>
                            <h2 class="mb-0">TZS {{ number_format($kpis['month_revenue'], 0) }}</h2>
                            <small class="opacity-75">
                                {{ number_format($kpis['month_count']) }} sale(s) ·
                                <i class="ti ti-trending-{{ $momChange >= 0 ? 'up' : 'down' }}"></i>
                                {{ $momChange >= 0 ? '+' : '' }}{{ $momChange }}% vs last month
                            </small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-currency-dollar fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- All-time + channel split --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted mb-1">All-time Sales Revenue</h6>
                    <h3 class="mb-0 text-success">TZS {{ number_format($kpis['all_revenue'], 0) }}</h3>
                    <small class="text-muted">{{ number_format($kpis['all_count']) }} sales recorded</small>

                    <hr>
                    <h6 class="mb-2"><i class="ti ti-versions text-primary me-2"></i>By Channel (this month)</h6>
                    @forelse($byChannel as $ch)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-capitalize">{{ str_replace(['_','-'],' ', $ch->sale_type ?? 'Unknown') }}
                            <span class="text-muted">({{ $ch->orders }})</span>
                        </span>
                        <span class="fw-medium">TZS {{ number_format($ch->revenue, 0) }}</span>
                    </div>
                    @empty
                    <div class="text-muted small">No sales this month.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Revenue trend --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-3"><i class="ti ti-chart-bar text-primary me-2"></i>Revenue Trend ({{ $trendMonths }} months)</h6>
                    <div class="d-flex align-items-end justify-content-between gap-2" style="height:200px;">
                        @foreach($trend as $point)
                        @php $heightPct = $trendMax > 0 ? round(($point['amount'] / $trendMax) * 100) : 0; @endphp
                        <div class="d-flex flex-column align-items-center flex-fill h-100 justify-content-end" title="{{ $point['label'] }}: TZS {{ number_format($point['amount'], 0) }}">
                            <small class="text-muted mb-1" style="font-size:.7rem;">{{ $point['amount'] > 0 ? number_format($point['amount'] / 1000, 0) . 'k' : '' }}</small>
                            <div class="w-100 rounded-top {{ $loop->last ? 'bg-primary' : 'bg-primary-subtle' }}"
                                 style="height: {{ max(2, $heightPct) }}%; min-height:2px;"></div>
                            <small class="text-muted mt-2">{{ $point['label'] }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top items + recent sales --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-3"><i class="ti ti-trophy text-warning me-2"></i>Top Items (this month)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="text-muted small">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-end">Qty Sold</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topItems as $row)
                                <tr wire:key="topitem-{{ $row->item_id }}">
                                    <td class="text-muted">{{ $loop->iteration }}</td>
                                    <td class="fw-medium">{{ $row->item?->name ?? 'Unknown item' }}</td>
                                    <td class="text-end">{{ number_format($row->qty, 0) }}</td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($row->revenue, 0) }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4 small">No items sold this month.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="ti ti-receipt text-success me-2"></i>Recent Sales</h6>
                        <a href="{{ route('owner.sales') }}" class="small text-decoration-none">View all <i class="ti ti-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="text-muted small">
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                <tr wire:key="sale-{{ $sale->id }}">
                                    <td><span class="fw-medium text-primary">INV-{{ strtoupper(substr($sale->id, 0, 8)) }}</span></td>
                                    <td><small>{{ $sale->customer?->name ?? 'Walk-in' }}</small></td>
                                    <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ $sale->items_count }}</span></td>
                                    <td class="text-end fw-bold">TZS {{ number_format($sale->total_amount, 0) }}</td>
                                    <td><small>{{ $sale->sale_date?->format('M d, Y') }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted small">
                                        <i class="ti ti-receipt-off fs-3 d-block mb-1"></i>
                                        No sales yet.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @endif
</div>
