<?php

namespace App\Livewire\Owner;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    /** How many months of history the revenue trend covers. */
    private const TREND_MONTHS = 6;

    public function render()
    {
        $user = Auth::user();

        // Businesses the user can see (owned, or — for staff — assigned).
        $businesses = $user->assignedBusinesses()->orderBy('name')->get(['id', 'name', 'type']);
        $businessIds = $businesses->pluck('id');

        // Base query over the unified payments ledger, completed only = real revenue.
        $revenueBase = fn () => Payment::whereIn('business_id', $businessIds)
            ->where('status', 'completed');

        $today = now();
        $monthStart = now()->startOfMonth();

        // ─── Headline KPIs ───────────────────────────────────────
        $kpis = [
            'businesses' => $businesses->count(),
            'revenue_total' => $businessIds->isEmpty() ? 0 : (float) $revenueBase()->sum('amount_local'),
            'revenue_month' => $businessIds->isEmpty() ? 0 : (float) $revenueBase()
                ->where('paid_at', '>=', $monthStart)->sum('amount_local'),
            'revenue_today' => $businessIds->isEmpty() ? 0 : (float) $revenueBase()
                ->whereDate('paid_at', $today->toDateString())->sum('amount_local'),
            'payments_month' => $businessIds->isEmpty() ? 0 : $revenueBase()
                ->where('paid_at', '>=', $monthStart)->count(),
        ];

        // ─── Month-over-month trend (last N months) ──────────────
        // One sum per calendar month so the view can draw a sparkline/bars.
        $trend = collect();
        for ($i = self::TREND_MONTHS - 1; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i);
            $amount = $businessIds->isEmpty() ? 0 : (float) $revenueBase()
                ->whereBetween('paid_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])
                ->sum('amount_local');

            $trend->push([
                'label' => $m->format('M'),
                'month' => $m->format('Y-m'),
                'amount' => $amount,
            ]);
        }

        // Percentage change this month vs last month
        $thisMonthRev = $trend->last()['amount'] ?? 0;
        $lastMonthRev = $trend->count() > 1 ? $trend[$trend->count() - 2]['amount'] : 0;
        $momChange = $lastMonthRev > 0
            ? round((($thisMonthRev - $lastMonthRev) / $lastMonthRev) * 100, 1)
            : ($thisMonthRev > 0 ? 100.0 : 0.0);

        $trendMax = (float) $trend->max('amount') ?: 1.0; // avoid /0 when drawing bars

        // ─── Per-business revenue breakdown (this month + all-time) ──
        $byBusiness = collect();
        if ($businessIds->isNotEmpty()) {
            $monthByBiz = $revenueBase()->where('paid_at', '>=', $monthStart)
                ->selectRaw('business_id, SUM(amount_local) as total')
                ->groupBy('business_id')->pluck('total', 'business_id');

            $allByBiz = $revenueBase()
                ->selectRaw('business_id, SUM(amount_local) as total')
                ->groupBy('business_id')->pluck('total', 'business_id');

            $byBusiness = $businesses->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'type' => $b->type,
                'revenue_month' => (float) ($monthByBiz[$b->id] ?? 0),
                'revenue_total' => (float) ($allByBiz[$b->id] ?? 0),
            ])->sortByDesc('revenue_month')->values();
        }

        // ─── Recent payments across all businesses ───────────────
        $recentPayments = $businessIds->isEmpty()
            ? collect()
            : Payment::whereIn('business_id', $businessIds)
                ->where('status', 'completed')
                ->with(['business:id,name,type', 'paymentMethod:id,name'])
                ->latest('paid_at')
                ->limit(8)
                ->get();

        return view('livewire.owner.dashboard', [
            'kpis' => $kpis,
            'trend' => $trend,
            'trendMax' => $trendMax,
            'momChange' => $momChange,
            'byBusiness' => $byBusiness,
            'recentPayments' => $recentPayments,
            'trendMonths' => self::TREND_MONTHS,
        ]);
    }
}
