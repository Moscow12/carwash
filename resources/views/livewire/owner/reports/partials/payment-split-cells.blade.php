{{-- Cash, credit and total cells for a grouped sales row, with a cash/credit split bar under the total. --}}
@php
    $cashAmount = (float) $row->cash_amount;
    $creditAmount = (float) $row->credit_amount;
    $rowTotal = $cashAmount + $creditAmount;
    $cashShare = $rowTotal > 0 ? round($cashAmount / $rowTotal * 100, 1) : 0;
    $creditShare = $rowTotal > 0 ? round(100 - $cashShare, 1) : 0;
@endphp
<td class="text-end {{ $cashAmount > 0 ? 'text-success-emphasis' : 'text-muted' }}">TSh {{ number_format($cashAmount, 2) }}</td>
<td class="text-end {{ $creditAmount > 0 ? 'text-warning-emphasis fw-medium' : 'text-muted' }}">TSh {{ number_format($creditAmount, 2) }}</td>
<td class="text-end" style="min-width: 160px;">
    <div class="text-success fw-bold">TSh {{ number_format($row->total_amount, 2) }}</div>
    <div class="progress ms-auto mt-1" style="height: 6px; max-width: 140px;" role="img" aria-label="Cash {{ $cashShare }}%, credit {{ $creditShare }}%" title="Cash {{ $cashShare }}% · Credit {{ $creditShare }}%">
        <div class="progress-bar bg-success" style="width: {{ $cashShare }}%"></div>
        <div class="progress-bar bg-warning" style="width: {{ $creditShare }}%"></div>
    </div>
</td>
