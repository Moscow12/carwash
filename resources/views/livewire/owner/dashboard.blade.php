<div class="owner-dashboard">
    <style>
        /* Chart colours: validated categorical slots 1-2 (blue, orange) for light and dark surfaces */
        .owner-dashboard {
            --series-revenue: #2a78d6;
            --series-costs: #eb6834;
        }
        @media (prefers-color-scheme: dark) {
            :root:where(:not([data-bs-theme="light"])) .owner-dashboard { --series-revenue: #3987e5; --series-costs: #d95926; }
        }
        :root[data-bs-theme="dark"] .owner-dashboard { --series-revenue: #3987e5; --series-costs: #d95926; }

        .owner-dashboard .kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .owner-dashboard .kpi-value { font-size: 1.45rem; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
        .owner-dashboard .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
        .owner-dashboard .legend-swatch { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }

        /* Trend chart */
        .owner-dashboard .trend { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; height: 220px; align-items: end; border-bottom: 1px solid var(--bs-border-color); }
        .owner-dashboard .trend-month { position: relative; height: 100%; display: flex; align-items: flex-end; justify-content: center; gap: 2px; cursor: pointer; border-radius: 8px 8px 0 0; padding: 0 6px; background: transparent; border: 0; }
        .owner-dashboard .trend-month:hover, .owner-dashboard .trend-month:focus-visible { background: var(--bs-tertiary-bg); outline: none; }
        .owner-dashboard .trend-month.selected { background: var(--bs-secondary-bg); }
        .owner-dashboard .trend-bar { width: min(22px, 40%); border-radius: 4px 4px 0 0; min-height: 2px; transition: height .3s ease; }
        .owner-dashboard .trend-bar.revenue { background: var(--series-revenue); }
        .owner-dashboard .trend-bar.costs { background: var(--series-costs); }
        .owner-dashboard .trend-tooltip { display: none; position: absolute; bottom: calc(100% - 8px); left: 50%; transform: translateX(-50%); z-index: 5; min-width: 180px; padding: 8px 10px; border-radius: 8px; background: var(--bs-body-bg); border: 1px solid var(--bs-border-color); box-shadow: 0 6px 18px rgba(0, 0, 0, .12); font-size: .78rem; text-align: left; color: var(--bs-body-color); pointer-events: none; }
        .owner-dashboard .trend-month:hover .trend-tooltip, .owner-dashboard .trend-month:focus-visible .trend-tooltip { display: block; }
        .owner-dashboard .trend-labels { display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px; text-align: center; }

        /* Month at a glance & locations */
        .owner-dashboard .meter { height: 8px; border-radius: 999px; background: var(--bs-tertiary-bg); overflow: hidden; }
        .owner-dashboard .meter > span { display: block; height: 100%; border-radius: 999px; }
        .owner-dashboard .location-icon { width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .owner-dashboard .table thead th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: var(--bs-secondary-color); white-space: nowrap; }
    </style>

    @if (session()->has('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    @php
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = Str::of(auth()->user()->name)->explode(' ')->first();
        $money = fn (float $amount) => 'TZS '.number_format($amount, 0);
    @endphp
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">{{ $greeting }}, {{ $firstName }}</h3>
            <p class="text-muted mb-0">
                {{ $businessCount }} business {{ Str::plural('location', $businessCount) }} · results for <strong>{{ $selected->format('F Y') }}</strong>{{ $isCurrentMonth ? ' so far' : '' }}
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="btn-group" role="group" aria-label="Choose month">
                <button type="button" wire:click="previousMonth" class="btn btn-outline-secondary" title="Previous month" aria-label="Previous month">
                    <i class="ti ti-chevron-left"></i>
                </button>
                <input type="month" wire:model.live="month" max="{{ now()->format('Y-m') }}" class="form-control rounded-0 border-secondary-subtle text-center fw-semibold" style="width: 170px;" aria-label="Month">
                <button type="button" wire:click="nextMonth" class="btn btn-outline-secondary" title="Next month" aria-label="Next month" @disabled($isCurrentMonth)>
                    <i class="ti ti-chevron-right"></i>
                </button>
            </div>
            @unless($isCurrentMonth)
                <button type="button" wire:click="thisMonth" class="btn btn-link text-decoration-none">This month</button>
            @endunless
            <a href="{{ route('owner.businesses') }}" class="btn btn-outline-primary">
                <i class="ti ti-building-store me-1"></i> My Businesses
            </a>
        </div>
    </div>

    @if($businessCount === 0)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-building-store display-5 text-muted d-block mb-3"></i>
                <h5>No business yet</h5>
                <p class="text-muted">Create your first business to start tracking revenue, expenses and profit.</p>
                <a href="{{ route('owner.mybusiness') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Add a business</a>
            </div>
        </div>
    @else

    <div wire:loading.class="opacity-50" wire:target="previousMonth,nextMonth,thisMonth,month" style="transition: opacity .15s;">

    {{-- KPI cards --}}
    @php
        $kpiCards = [
            ['key' => 'revenue', 'label' => 'Revenue', 'icon' => 'ti-trending-up', 'tone' => 'primary', 'value' => $current['revenue'], 'goodWhenUp' => true,
             'note' => 'Sales and payments received'],
            ['key' => 'expenses', 'label' => 'Expenses', 'icon' => 'ti-receipt-2', 'tone' => 'warning', 'value' => $current['expenses'], 'goodWhenUp' => false,
             'note' => 'Bills, wages and running costs'],
            ['key' => 'purchases', 'label' => 'Purchases', 'icon' => 'ti-shopping-bag', 'tone' => 'info', 'value' => $current['purchases'], 'goodWhenUp' => false,
             'note' => 'Approved and received stock orders'],
            ['key' => 'profit', 'label' => 'Net Profit', 'icon' => 'ti-coin', 'tone' => $current['profit'] < 0 ? 'danger' : 'success', 'value' => $current['profit'], 'goodWhenUp' => true,
             'note' => $current['margin'] !== null ? $current['margin'].'% of revenue kept' : 'Revenue minus expenses and purchases'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($kpiCards as $card)
            @php
                $change = $changes[$card['key']];
                $isGood = $change === null ? null : (($change >= 0) === $card['goodWhenUp']);
            @endphp
            <div class="col-6 col-xl-3" wire:key="kpi-{{ $card['key'] }}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <span class="text-muted small fw-medium">{{ $card['label'] }}</span>
                            <span class="kpi-icon bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }}"><i class="ti {{ $card['icon'] }} fs-4"></i></span>
                        </div>
                        <div class="kpi-value {{ $card['key'] === 'profit' && $card['value'] < 0 ? 'text-danger' : '' }}">
                            {{ $card['value'] < 0 ? '−' : '' }}{{ $money(abs($card['value'])) }}
                        </div>
                        <div class="small mt-1">
                            @if($change === null)
                                <span class="text-muted">No data for {{ $selected->copy()->subMonth()->format('M') }} to compare</span>
                            @else
                                <span class="fw-semibold text-{{ $isGood ? 'success' : 'danger' }}">
                                    <i class="ti ti-arrow-{{ $change >= 0 ? 'up' : 'down' }}-right"></i>
                                    {{ $change >= 0 ? '+' : '' }}{{ $change }}%
                                </span>
                                <span class="text-muted">vs {{ $selected->copy()->subMonth()->format('M') }}</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">{{ $card['note'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        {{-- Trend --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-0">Revenue vs costs</h6>
                            <small class="text-muted">Last 6 months · costs = expenses + purchases · click a month to open it</small>
                        </div>
                        <div class="d-flex gap-3 small" aria-label="Legend">
                            <span><span class="legend-swatch me-1" style="background: var(--series-revenue)"></span>Revenue</span>
                            <span><span class="legend-swatch me-1" style="background: var(--series-costs)"></span>Costs</span>
                        </div>
                    </div>

                    <div class="trend" role="list" aria-label="Revenue and costs by month">
                        @foreach($trend as $point)
                            <button type="button" role="listitem"
                                    wire:key="trend-{{ $point['key'] }}"
                                    wire:click="$set('month', '{{ $point['key'] }}')"
                                    class="trend-month {{ $point['key'] === $selected->format('Y-m') ? 'selected' : '' }}"
                                    aria-label="{{ $point['long'] }}: revenue {{ $money($point['revenue']) }}, costs {{ $money($point['costs']) }}, profit {{ $money($point['profit']) }}">
                                <span class="trend-bar revenue" style="height: {{ round($point['revenue'] / $trendMax * 100, 1) }}%"></span>
                                <span class="trend-bar costs" style="height: {{ round($point['costs'] / $trendMax * 100, 1) }}%"></span>
                                <span class="trend-tooltip">
                                    <strong class="d-block mb-1">{{ $point['long'] }}</strong>
                                    <span class="d-flex justify-content-between gap-3"><span><span class="legend-swatch me-1" style="background: var(--series-revenue)"></span>Revenue</span><span class="num">{{ $money($point['revenue']) }}</span></span>
                                    <span class="d-flex justify-content-between gap-3"><span><span class="legend-swatch me-1" style="background: var(--series-costs)"></span>Expenses</span><span class="num">{{ $money($point['expenses']) }}</span></span>
                                    <span class="d-flex justify-content-between gap-3"><span><span class="legend-swatch me-1" style="background: var(--series-costs)"></span>Purchases</span><span class="num">{{ $money($point['purchases']) }}</span></span>
                                    <span class="d-flex justify-content-between gap-3 border-top mt-1 pt-1 fw-semibold"><span>Profit</span><span class="num">{{ $point['profit'] < 0 ? '−' : '' }}{{ $money(abs($point['profit'])) }}</span></span>
                                </span>
                            </button>
                        @endforeach
                    </div>
                    <div class="trend-labels mt-2">
                        @foreach($trend as $point)
                            <div wire:key="trend-label-{{ $point['key'] }}">
                                <div class="small {{ $point['key'] === $selected->format('Y-m') ? 'fw-bold' : 'text-muted' }}">{{ $point['label'] }}</div>
                                <div class="small num {{ $point['profit'] < 0 ? 'text-danger' : 'text-body-secondary' }}" title="Profit">
                                    {{ $point['profit'] < 0 ? '−' : ($point['profit'] > 0 ? '+' : '') }}{{ number_format(abs($point['profit']) / 1000, abs($point['profit']) >= 1000 ? 0 : 1) }}k
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Month at a glance --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-1">{{ $selected->format('F') }} at a glance</h6>
                    <small class="text-muted d-block mb-3">Out of every TZS 100 earned</small>
                    @php
                        $base = max($current['revenue'], 1);
                        $share = fn (float $amount) => min(100, round($amount / $base * 100));
                        $glance = [
                            ['label' => 'Expenses', 'amount' => $current['expenses'], 'color' => 'var(--series-costs)'],
                            ['label' => 'Purchases', 'amount' => $current['purchases'], 'color' => 'var(--series-costs)'],
                            ['label' => $current['profit'] < 0 ? 'Loss' : 'Profit kept', 'amount' => abs($current['profit']), 'color' => $current['profit'] < 0 ? 'var(--bs-danger)' : 'var(--series-revenue)'],
                        ];
                    @endphp
                    @if($current['revenue'] <= 0 && $current['costs'] <= 0)
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-chart-donut-off fs-1 d-block mb-2"></i>
                            Nothing recorded in {{ $selected->format('F') }} yet.
                        </div>
                    @else
                        <div class="d-flex flex-column gap-3">
                            @foreach($glance as $row)
                                <div wire:key="glance-{{ $row['label'] }}">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-medium">{{ $row['label'] }}</span>
                                        <span class="num">
                                            {{ $money($row['amount']) }}
                                            @if($current['revenue'] > 0)<span class="text-muted">· TZS {{ $share($row['amount']) }}</span>@endif
                                        </span>
                                    </div>
                                    <div class="meter"><span style="width: {{ $current['revenue'] > 0 ? $share($row['amount']) : 100 }}%; background: {{ $row['color'] }};"></span></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-top mt-4 pt-3 d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Profit margin</span>
                            <span class="fs-5 fw-bold {{ ($current['margin'] ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $current['margin'] !== null ? $current['margin'].'%' : '—' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Business locations --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <div>
                <h6 class="mb-0">Business locations</h6>
                <small class="text-muted">{{ $activeLocations }} of {{ $businessCount }} active in {{ $selected->format('F') }}</small>
            </div>
            <a href="{{ route('owner.reports.profitandloss') }}" class="btn btn-sm btn-outline-secondary">
                <i class="ti ti-scale me-1"></i> Profit & Loss report
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 50px;">S/N</th>
                        <th>Location</th>
                        <th class="text-end">Revenue</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-end">Purchases</th>
                        <th class="text-end pe-4">Net Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $location)
                        @php $revenueShare = $current['revenue'] > 0 ? round($location['revenue'] / $current['revenue'] * 100) : 0; @endphp
                        <tr wire:key="location-{{ $location['id'] }}">
                            <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="location-icon bg-primary-subtle text-primary"><i class="ti ti-building-store"></i></span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold">{{ $location['name'] }}</div>
                                        <small class="text-muted">
                                            {{ $location['type'] ? Str::of($location['type'])->replace('_', ' ')->title() : 'Business' }}
                                            @if($location['address']) · {{ Str::limit($location['address'], 30) }}@endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="num fw-semibold">{{ $money($location['revenue']) }}</div>
                                <div class="d-flex align-items-center justify-content-end gap-2" title="{{ $revenueShare }}% of all revenue">
                                    <div class="meter" style="width: 70px;"><span style="width: {{ $revenueShare }}%; background: var(--series-revenue);"></span></div>
                                    <small class="text-muted num" style="width: 32px;">{{ $revenueShare }}%</small>
                                </div>
                            </td>
                            <td class="text-end num {{ $location['expenses'] > 0 ? '' : 'text-muted' }}">{{ $money($location['expenses']) }}</td>
                            <td class="text-end num {{ $location['purchases'] > 0 ? '' : 'text-muted' }}">{{ $money($location['purchases']) }}</td>
                            <td class="text-end pe-4">
                                <div class="num fw-bold {{ $location['profit'] < 0 ? 'text-danger' : ($location['profit'] > 0 ? 'text-success' : 'text-muted') }}">
                                    {{ $location['profit'] < 0 ? '−' : '' }}{{ $money(abs($location['profit'])) }}
                                </div>
                                <small class="text-muted">{{ $location['margin'] !== null ? $location['margin'].'% margin' : 'No revenue' }}</small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold">
                        <td class="ps-4"></td>
                        <td>All locations</td>
                        <td class="text-end num">{{ $money($current['revenue']) }}</td>
                        <td class="text-end num">{{ $money($current['expenses']) }}</td>
                        <td class="text-end num">{{ $money($current['purchases']) }}</td>
                        <td class="text-end pe-4 num {{ $current['profit'] < 0 ? 'text-danger' : 'text-success' }}">{{ $current['profit'] < 0 ? '−' : '' }}{{ $money(abs($current['profit'])) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    </div>
    @endif
</div>
