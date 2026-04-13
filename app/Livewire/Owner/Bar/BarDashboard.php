<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\BarTab;
use App\Models\BarHappyHourPrice;
use App\Models\MenuItem;

#[Layout('components.layouts.app-owner')]
class BarDashboard extends Component
{
    public $selectedBusiness = null;
    public $selectedOutlet = null;

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            // Get first bar outlet
            $barOutlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->first();

            if ($barOutlet) {
                $this->selectedOutlet = $barOutlet->id;
            }
        }
    }

    private function calculateBarStatistics()
    {
        // Today's date range
        $today = today();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();

        // Orders from POS
        $ordersToday = \App\Models\PosOrder::where('outlet_id', $this->selectedOutlet)
            ->whereDate('created_at', $today)
            ->where('order_type', 'bar');

        $ordersWeek = \App\Models\PosOrder::where('outlet_id', $this->selectedOutlet)
            ->where('created_at', '>=', $startOfWeek)
            ->where('order_type', 'bar');

        $ordersMonth = \App\Models\PosOrder::where('outlet_id', $this->selectedOutlet)
            ->where('created_at', '>=', $startOfMonth)
            ->where('order_type', 'bar');

        // Revenue calculations
        $revenueToday = (clone $ordersToday)->sum('total');
        $revenueWeek = (clone $ordersWeek)->sum('total');
        $revenueMonth = (clone $ordersMonth)->sum('total');

        // Order counts
        $ordersCountToday = (clone $ordersToday)->count();
        $ordersCountWeek = (clone $ordersWeek)->count();
        $ordersCountMonth = (clone $ordersMonth)->count();

        // Tabs statistics
        $openTabs = BarTab::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'open')
            ->count();

        $tabsToday = BarTab::where('outlet_id', $this->selectedOutlet)
            ->whereDate('created_at', $today)
            ->count();

        $totalTabBalance = BarTab::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'open')
            ->sum('balance');

        // Active sessions
        $activeSession = \App\Models\PosSession::where('outlet_id', $this->selectedOutlet)
            ->whereNull('closed_at')
            ->first();

        // Happy hours
        $activeHappyHours = BarHappyHourPrice::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->count();

        // Top selling items today
        $topItems = \Illuminate\Support\Facades\DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.order_id', '=', 'pos_orders.id')
            ->where('pos_orders.outlet_id', $this->selectedOutlet)
            ->whereDate('pos_orders.created_at', $today)
            ->where('pos_orders.order_type', 'bar')
            ->whereNull('pos_orders.deleted_at')
            ->select('pos_order_items.menu_item_id')
            ->selectRaw('SUM(pos_order_items.quantity) as total_qty')
            ->selectRaw('SUM(pos_order_items.total) as total_sales')
            ->groupBy('pos_order_items.menu_item_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $item->menuItem = \App\Models\MenuItem::find($item->menu_item_id);
                return $item;
            });

        // Average order value
        $avgOrderValue = $ordersCountToday > 0 ? $revenueToday / $ordersCountToday : 0;

        // Calculate yesterday's revenue for comparison
        $revenueYesterday = \App\Models\PosOrder::where('outlet_id', $this->selectedOutlet)
            ->whereDate('created_at', $today->copy()->subDay())
            ->where('order_type', 'bar')
            ->sum('total');

        $revenueGrowth = 0;
        if ($revenueYesterday > 0) {
            $revenueGrowth = (($revenueToday - $revenueYesterday) / $revenueYesterday) * 100;
        }

        return [
            // Revenue metrics
            'revenue_today' => $revenueToday,
            'revenue_week' => $revenueWeek,
            'revenue_month' => $revenueMonth,
            'revenue_yesterday' => $revenueYesterday,
            'revenue_growth' => $revenueGrowth,
            'avg_order_value' => $avgOrderValue,

            // Order metrics
            'orders_today' => $ordersCountToday,
            'orders_week' => $ordersCountWeek,
            'orders_month' => $ordersCountMonth,

            // Tab metrics
            'open_tabs' => $openTabs,
            'tabs_today' => $tabsToday,
            'total_tab_balance' => $totalTabBalance,

            // Session info
            'active_session' => $activeSession,
            'session_open' => $activeSession !== null,

            // Happy hours
            'active_happy_hours' => $activeHappyHours,

            // Top items
            'top_items' => $topItems,
        ];
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $stats = [
            'open_tabs' => 0,
            'tabs_today' => 0,
            'revenue_today' => 0,
            'active_happy_hours' => 0,
        ];

        if ($this->selectedOutlet) {
            // Statistics
            $stats = $this->calculateBarStatistics();
        }

        // Get all bar outlets for dropdown
        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        return view('livewire.owner.bar.bar-dashboard', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'stats' => $stats,
        ]);
    }
}
