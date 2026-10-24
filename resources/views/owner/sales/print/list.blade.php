@extends('owner.sales.print.layout')

@section('title', 'Sales Report')

@section('content')
    @php
        $settings = $business?->settings;
        $businessName = $settings?->business_name ?: ($business?->name ?? 'All businesses');
        $period = ($filters['from'] ?? null) || ($filters['to'] ?? null)
            ? (isset($filters['from']) ? \Carbon\Carbon::parse($filters['from'])->format('d M Y') : 'Start').' – '.(isset($filters['to']) ? \Carbon\Carbon::parse($filters['to'])->format('d M Y') : 'Today')
            : 'All dates';
        $applied = array_filter([
            'Sale status' => isset($filters['sale_status']) ? ucfirst($filters['sale_status']) : null,
            'Payment' => isset($filters['payment_status']) ? ucfirst($filters['payment_status']) : null,
            'Search' => $filters['search'] ?? null,
        ]);
    @endphp

    <div class="doc-header">
        <div>
            @if($settings?->show_logo_on_receipt && $business?->logo)
                <img src="{{ asset('storage/'.$business->logo) }}" alt="" class="logo">
            @endif
            <h1>{{ $businessName }}</h1>
            @if($settings?->business_address || $business?->address)
                <div class="muted">{{ $settings?->business_address ?: $business?->address }}</div>
            @endif
            @if($settings?->business_phone)
                <div class="muted">Tel: {{ $settings->business_phone }}</div>
            @endif
        </div>
        <div class="meta">
            <h1>Sales Report</h1>
            <div><strong>Period:</strong> {{ $period }}</div>
            @foreach($applied as $label => $value)
                <div><strong>{{ $label }}:</strong> {{ $value }}</div>
            @endforeach
        </div>
    </div>

    <div class="summary">
        <div><span class="muted">Transactions</span><strong>{{ number_format($totals['count']) }}</strong></div>
        <div><span class="muted">Total</span><strong>TZS {{ number_format($totals['total'], 2) }}</strong></div>
        <div><span class="muted">Paid</span><strong class="text-success">TZS {{ number_format($totals['paid'], 2) }}</strong></div>
        <div><span class="muted">Unpaid</span><strong class="text-danger">TZS {{ number_format($totals['unpaid'], 2) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 34px;">S/N</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th class="num">Items</th>
                <th class="num">Total</th>
                <th class="num">Paid</th>
                <th class="num">Unpaid</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                @php
                    $paid = (float) $sale->payments->sum('amount');
                    $unpaid = max(0, (float) $sale->total_amount - $paid);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->sale_date?->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td class="num">{{ $sale->items_count }}</td>
                    <td class="num">{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="num">{{ number_format($paid, 2) }}</td>
                    <td class="num {{ $unpaid > 0 ? 'text-danger' : '' }}">{{ number_format($unpaid, 2) }}</td>
                    <td>
                        <span class="badge {{ $sale->sale_status === 'canceled' ? 'canceled' : $sale->payment_status }}">
                            {{ $sale->sale_status === 'canceled' ? 'Canceled' : ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted" style="text-align: center; padding: 24px;">No sales match these filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($sales->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="5">Total ({{ number_format($totals['count']) }} {{ Str::plural('sale', $totals['count']) }})</td>
                    <td class="num">{{ number_format($totals['total'], 2) }}</td>
                    <td class="num">{{ number_format($totals['paid'], 2) }}</td>
                    <td class="num">{{ number_format($totals['unpaid'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endsection
