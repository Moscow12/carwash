@extends('owner.sales.print.layout')

@section('title', 'Invoice '.$sale->invoice_number)

@section('content')
    @php $settings = $sale->business?->settings; @endphp

    <x-print.header title="Invoice" :business="$sale->business" :meta="[
        'No' => $sale->invoice_number,
        'Date' => $sale->sale_date?->format('d M Y H:i'),
    ]">
        <div>
            <span class="badge {{ $sale->sale_status === 'canceled' ? 'canceled' : $sale->payment_status }}">
                {{ $sale->sale_status === 'canceled' ? 'Canceled' : ucfirst($sale->payment_status) }}
            </span>
        </div>
    </x-print.header>

    <div class="summary">
        <div>
            <span class="muted">Bill to</span>
            <strong>{{ $sale->customer?->name ?? 'Walk-in Customer' }}</strong>
            @if($sale->customer?->phone)<span class="muted">{{ $sale->customer->phone }}</span>@endif
        </div>
        <div>
            <span class="muted">Served by</span>
            <strong>{{ $sale->items->first()?->staff?->name ?? $sale->user?->name ?? '-' }}</strong>
            <span class="muted">Cashier: {{ $sale->user?->name ?? '-' }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 34px;">S/N</th>
                <th>Item</th>
                <th>Plate No.</th>
                <th class="num">Qty</th>
                <th class="num">Unit Price</th>
                <th class="num">Discount</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $line->item?->name ?? '-' }}</td>
                    <td>{{ $line->plate_number ?: '-' }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }}</td>
                    <td class="num">{{ number_format($line->price, 2) }}</td>
                    <td class="num">{{ number_format($line->discount ?? 0, 2) }}</td>
                    <td class="num">{{ number_format(($line->price * $line->quantity) - ($line->discount ?? 0), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total</td>
                <td class="num">TZS {{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table style="width: 55%; margin: 14px 0 0 auto;">
        <thead>
            <tr>
                <th>Payment</th>
                <th>Date</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sale->payments as $payment)
                <tr>
                    <td>{{ $payment->paymentMethod?->name ?? 'Payment' }}</td>
                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}</td>
                    <td class="num">{{ number_format($payment->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No payments yet</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Paid</td>
                <td class="num text-success">TZS {{ number_format($paid, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2">Balance due</td>
                <td class="num {{ $balance > 0 ? 'text-danger' : '' }}">TZS {{ number_format($balance, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if($settings?->receipt_footer)
        <p class="muted" style="margin-top: 18px; text-align: center;">{!! nl2br(e($settings->receipt_footer)) !!}</p>
    @else
        <p class="muted" style="margin-top: 18px; text-align: center;">Thank you for your business!</p>
    @endif
@endsection
