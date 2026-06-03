<?php

namespace App\Livewire\Owner\Sales;

use App\Models\sales;
use App\Models\sales_item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    /** Months of history for the sales trend. */
    private const TREND_MONTHS = 6;

    /** Optional business filter (null = all of the user's businesses). */
    public ?string $selectedBusiness = null;

    public \Illuminate\Support\Collection $ownerBusinesses;

    public function mount(): void
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()
            ->orderBy('name')->get(['id', 'name', 'type']);
    }

    public function updatedSelectedBusiness(): void
    {
        // re-render only; render() reads $selectedBusiness
    }

    /** Business ids in scope for every query on this page. */
    protected function scopedBusinessIds(): \Illuminate\Support\Collection
    {
        if ($this->selectedBusiness) {
            return collect([$this->selectedBusiness])
                ->intersect($this->ownerBusinesses->pluck('id'))
                ->values();
        }

        return $this->ownerBusinesses->pluck('id');
    }

    public function render()
    {
        $businessIds = $this->scopedBusinessIds();
        $monthStart = now()->startOfMonth();
        $today = now()->toDateString();

        // Completed (non-void) sales only.
        $salesBase = fn () => sales::whereIn('business_id', $businessIds)
            ->where('sale_status', '!=', 'voided');

        $empty = $businessIds->isEmpty();

        // ─── Headline KPIs ───────────────────────────────────────
        $kpis = [
            'today_count' => $empty ? 0 : (clone $salesBase())->whereDate('sale_date', $today)->count(),
            'today_revenue' => $empty ? 0 : (float) (clone $salesBase())->whereDate('sale_date', $today)->sum('total_amount'),
            'month_count' => $empty ? 0 : (clone $salesBase())->where('sale_date', '>=', $monthStart)->count(),
            'month_revenue' => $empty ? 0 : (float) (clone $salesBase())->where('sale_date', '>=', $monthStart)->sum('total_amount'),
            'all_count' => $empty ? 0 : (clone $salesBase())->count(),
            'all_revenue' => $empty ? 0 : (float) (clone $salesBase())->sum('total_amount'),
        ];

        // ─── Sales by channel (sale_type) this month ─────────────
        $byChannel = $empty ? collect() : (clone $salesBase())
            ->where('sale_date', '>=', $monthStart)
            ->selectRaw('sale_type, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('sale_type')
            ->orderByDesc('revenue')
            ->get();

        // ─── Revenue trend (last N months) ───────────────────────
        $trend = collect();
        for ($i = self::TREND_MONTHS - 1; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $amount = $empty ? 0 : (float) (clone $salesBase())
                ->whereBetween('sale_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->sum('total_amount');
            $trend->push(['label' => $m->format('M'), 'amount' => $amount]);
        }
        $trendMax = (float) $trend->max('amount') ?: 1.0;

        $thisMonthRev = $trend->last()['amount'] ?? 0;
        $lastMonthRev = $trend->count() > 1 ? $trend[$trend->count() - 2]['amount'] : 0;
        $momChange = $lastMonthRev > 0
            ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1)
            : ($thisMonthRev > 0 ? 100.0 : 0.0);

        // ─── Top items sold (this month, by quantity & revenue) ──
        $topItems = $empty ? collect() : sales_item::query()
            ->whereHas('sale', fn ($q) => $q->whereIn('business_id', $businessIds)
                ->where('sale_status', '!=', 'voided')
                ->where('sale_date', '>=', $monthStart))
            ->selectRaw('item_id, SUM(quantity) as qty, SUM(total) as revenue')
            ->groupBy('item_id')
            ->orderByDesc('revenue')
            ->with('item:id,name')
            ->limit(10)
            ->get();

        // ─── Recent sales ────────────────────────────────────────
        $recentSales = $empty ? collect() : (clone $salesBase())
            ->with(['business:id,name', 'customer:id,name'])
            ->withCount('items')
            ->latest('sale_date')
            ->limit(8)
            ->get();

        return view('livewire.owner.sales.dashboard', [
            'kpis' => $kpis,
            'byChannel' => $byChannel,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'momChange' => $momChange,
            'topItems' => $topItems,
            'recentSales' => $recentSales,
            'businesses' => $this->ownerBusinesses,
            'trendMonths' => self::TREND_MONTHS,
        ]);
    }
}
