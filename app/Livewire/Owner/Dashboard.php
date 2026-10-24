<?php

namespace App\Livewire\Owner;

use App\Models\expenses;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\sales;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    /** How many months the trend chart covers, ending with the selected month. */
    private const TREND_MONTHS = 6;

    /** Purchase orders that commit money; drafts, submitted and cancelled orders are left out. */
    private const COUNTED_PURCHASE_STATUSES = ['approved', 'partially_received', 'received'];

    /** Selected month as Y-m. */
    #[Url]
    public string $month = '';

    public function mount(): void
    {
        $this->month = $this->normaliseMonth($this->month);
    }

    public function previousMonth(): void
    {
        $this->month = $this->selectedMonth()->subMonth()->format('Y-m');
    }

    public function nextMonth(): void
    {
        $this->month = $this->normaliseMonth($this->selectedMonth()->addMonth()->format('Y-m'));
    }

    public function thisMonth(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function updatedMonth(): void
    {
        $this->month = $this->normaliseMonth($this->month);
    }

    /**
     * A valid Y-m that is not in the future; anything else becomes the current month.
     */
    private function normaliseMonth(string $month): string
    {
        $parsed = preg_match('/^\d{4}-\d{2}$/', $month) ? Carbon::createFromFormat('!Y-m', $month) : null;

        if (! $parsed || $parsed->greaterThan(now()->startOfMonth())) {
            return now()->format('Y-m');
        }

        return $parsed->format('Y-m');
    }

    private function selectedMonth(): Carbon
    {
        return Carbon::createFromFormat('!Y-m', $this->normaliseMonth($this->month));
    }

    /**
     * SQL that turns a date column into "YYYY-MM", per database driver.
     */
    private function monthKey(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    /**
     * Revenue, expenses and purchases per business per month, in four grouped queries.
     *
     * Revenue = retail POS sales (sales table, not cancelled) + completed payments on the shared ledger
     * (restaurant/bar orders, hotel, rentals). Retail sales never post to the ledger, so nothing is counted twice.
     *
     * @param  Collection<int, string>  $businessIds
     * @return array<string, array<string, array{revenue: float, expenses: float, purchases: float}>> business id => month => totals
     */
    private function monthlyTotals(Collection $businessIds, Carbon $from, Carbon $to): array
    {
        $totals = [];
        $add = function (iterable $rows, string $measure) use (&$totals) {
            foreach ($rows as $row) {
                $totals[$row->business_id][$row->ym] ??= ['revenue' => 0.0, 'expenses' => 0.0, 'purchases' => 0.0];
                $totals[$row->business_id][$row->ym][$measure] += (float) $row->total;
            }
        };

        $add(sales::whereIn('business_id', $businessIds)
            ->where('sale_status', '!=', 'canceled')
            ->whereBetween('sale_date', [$from, $to])
            ->selectRaw("business_id, {$this->monthKey('sale_date')} as ym, SUM(total_amount) as total")
            ->groupBy('business_id', 'ym')
            ->toBase()
            ->get(), 'revenue');

        $add(Payment::whereIn('business_id', $businessIds)
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$from, $to])
            ->selectRaw("business_id, {$this->monthKey('paid_at')} as ym, SUM(amount_local) as total")
            ->groupBy('business_id', 'ym')
            ->toBase()
            ->get(), 'revenue');

        $add(expenses::whereIn('business_id', $businessIds)
            ->where('status', 'active')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("business_id, {$this->monthKey('expense_date')} as ym, SUM(total_amount) as total")
            ->groupBy('business_id', 'ym')
            ->toBase()
            ->get(), 'expenses');

        $add(PurchaseOrder::whereIn('business_id', $businessIds)
            ->whereIn('status', self::COUNTED_PURCHASE_STATUSES)
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("business_id, {$this->monthKey('order_date')} as ym, SUM(total_amount) as total")
            ->groupBy('business_id', 'ym')
            ->toBase()
            ->get(), 'purchases');

        return $totals;
    }

    /**
     * @param  array{revenue: float, expenses: float, purchases: float}  $totals
     * @return array{revenue: float, expenses: float, purchases: float, costs: float, profit: float, margin: ?float}
     */
    private function withProfit(array $totals): array
    {
        $costs = $totals['expenses'] + $totals['purchases'];
        $profit = $totals['revenue'] - $costs;

        return $totals + [
            'costs' => $costs,
            'profit' => $profit,
            'margin' => $totals['revenue'] > 0 ? round($profit / $totals['revenue'] * 100, 1) : null,
        ];
    }

    /**
     * Percentage change from $previous to $current, or null when there is nothing to compare with.
     */
    private function change(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    public function render()
    {
        $businesses = Auth::user()->assignedBusinesses()->orderBy('name')->get(['businesses.id', 'businesses.name', 'businesses.type', 'businesses.address']);
        $businessIds = $businesses->pluck('id');

        $selected = $this->selectedMonth();
        $windowStart = $selected->copy()->subMonths(self::TREND_MONTHS - 1)->startOfMonth();
        $windowEnd = $selected->copy()->endOfMonth();

        $perBusiness = $businessIds->isEmpty() ? [] : $this->monthlyTotals($businessIds, $windowStart, $windowEnd);

        $empty = ['revenue' => 0.0, 'expenses' => 0.0, 'purchases' => 0.0];
        $sumMonth = fn (string $ym) => collect($perBusiness)->reduce(function (array $carry, array $months) use ($ym) {
            foreach ($months[$ym] ?? [] as $measure => $value) {
                $carry[$measure] += $value;
            }

            return $carry;
        }, $empty);

        $currentKey = $selected->format('Y-m');
        $previousKey = $selected->copy()->subMonth()->format('Y-m');

        $current = $this->withProfit($sumMonth($currentKey));
        $previous = $this->withProfit($sumMonth($previousKey));

        $changes = [
            'revenue' => $this->change($current['revenue'], $previous['revenue']),
            'expenses' => $this->change($current['expenses'], $previous['expenses']),
            'purchases' => $this->change($current['purchases'], $previous['purchases']),
            'profit' => $this->change($current['profit'], $previous['profit']),
        ];

        $trend = collect(range(self::TREND_MONTHS - 1, 0))->map(function (int $monthsBack) use ($selected, $sumMonth) {
            $month = $selected->copy()->subMonths($monthsBack);

            return ['key' => $month->format('Y-m'), 'label' => $month->format('M'), 'long' => $month->format('F Y')]
                + $this->withProfit($sumMonth($month->format('Y-m')));
        });
        $trendMax = max(1.0, (float) $trend->max('revenue'), (float) $trend->max('costs'));

        $locations = $businesses->map(fn ($business) => [
            'id' => $business->id,
            'name' => $business->name,
            'type' => $business->type,
            'address' => $business->address,
        ] + $this->withProfit($perBusiness[$business->id][$currentKey] ?? $empty))
            ->sortByDesc('revenue')
            ->values();

        return view('livewire.owner.dashboard', [
            'selected' => $selected,
            'isCurrentMonth' => $currentKey === now()->format('Y-m'),
            'businessCount' => $businesses->count(),
            'current' => $current,
            'changes' => $changes,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'locations' => $locations,
            'activeLocations' => $locations->filter(fn ($location) => $location['revenue'] > 0 || $location['costs'] > 0)->count(),
        ]);
    }
}
