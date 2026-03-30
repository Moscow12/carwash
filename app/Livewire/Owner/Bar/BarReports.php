<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\PosOrder;
use App\Models\BarTab;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class BarReports extends Component
{
    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $reportType = 'daily'; // daily, weekly, monthly, custom
    public $dateFrom;
    public $dateTo;

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

        // Set default dates
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    private function getDateRange()
    {
        switch ($this->reportType) {
            case 'daily':
                return [now()->startOfDay(), now()->endOfDay()];
            case 'weekly':
                return [now()->startOfWeek(), now()->endOfWeek()];
            case 'monthly':
                return [now()->startOfMonth(), now()->endOfMonth()];
            case 'custom':
                return [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ];
            default:
                return [now()->startOfDay(), now()->endOfDay()];
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $salesData = [];
        $categoryData = [];
        $topItems = [];
        $paymentMethods = [];
        $hourlyData = [];

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        if ($this->selectedOutlet) {
            [$startDate, $endDate] = $this->getDateRange();

            // Sales Summary
            $orders = PosOrder::where('outlet_id', $this->selectedOutlet)
                ->where('order_type', 'bar')
                ->whereBetween('created_at', [$startDate, $endDate]);

            $salesData = [
                'total_orders' => (clone $orders)->count(),
                'total_revenue' => (clone $orders)->sum('total'),
                'avg_order_value' => (clone $orders)->avg('total') ?? 0,
                'completed_orders' => (clone $orders)->where('status', 'paid')->count(),
            ];

            // Category Performance
            $categoryData = DB::table('pos_order_items')
                ->join('pos_orders', 'pos_order_items.order_id', '=', 'pos_orders.id')
                ->join('menu_items', 'pos_order_items.menu_item_id', '=', 'menu_items.id')
                ->join('menu_categories', 'menu_items.category_id', '=', 'menu_categories.id')
                ->where('pos_orders.outlet_id', $this->selectedOutlet)
                ->where('pos_orders.order_type', 'bar')
                ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
                ->whereNull('pos_orders.deleted_at')
                ->select('menu_categories.name as category')
                ->selectRaw('SUM(pos_order_items.total) as revenue')
                ->selectRaw('SUM(pos_order_items.quantity) as quantity')
                ->groupBy('menu_categories.id', 'menu_categories.name')
                ->orderByDesc('revenue')
                ->get();

            // Top Selling Items
            $topItems = DB::table('pos_order_items')
                ->join('pos_orders', 'pos_order_items.order_id', '=', 'pos_orders.id')
                ->where('pos_orders.outlet_id', $this->selectedOutlet)
                ->where('pos_orders.order_type', 'bar')
                ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
                ->whereNull('pos_orders.deleted_at')
                ->select('pos_order_items.menu_item_id')
                ->selectRaw('SUM(pos_order_items.quantity) as total_qty')
                ->selectRaw('SUM(pos_order_items.total) as total_sales')
                ->groupBy('pos_order_items.menu_item_id')
                ->orderByDesc('total_sales')
                ->limit(10)
                ->get()
                ->map(function($item) {
                    $item->menuItem = \App\Models\MenuItem::find($item->menu_item_id);
                    return $item;
                });

            // Payment Methods
            $paymentMethods = DB::table('hotel_payments')
                ->join('pos_orders', 'hotel_payments.pos_order_id', '=', 'pos_orders.id')
                ->join('payment_methods', 'hotel_payments.payment_method_id', '=', 'payment_methods.id')
                ->where('pos_orders.outlet_id', $this->selectedOutlet)
                ->where('pos_orders.order_type', 'bar')
                ->whereBetween('pos_orders.created_at', [$startDate, $endDate])
                ->whereNull('pos_orders.deleted_at')
                ->select('payment_methods.name as method')
                ->selectRaw('COUNT(*) as count')
                ->selectRaw('SUM(hotel_payments.amount) as total')
                ->groupBy('payment_methods.id', 'payment_methods.name')
                ->orderByDesc('total')
                ->get();

            // Hourly Sales (for daily report)
            if ($this->reportType === 'daily') {
                $hourlyData = DB::table('pos_orders')
                    ->where('outlet_id', $this->selectedOutlet)
                    ->where('order_type', 'bar')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->whereNull('deleted_at')
                    ->selectRaw('HOUR(created_at) as hour')
                    ->selectRaw('COUNT(*) as orders')
                    ->selectRaw('SUM(total) as revenue')
                    ->groupBy('hour')
                    ->orderBy('hour')
                    ->get();
            }
        }

        return view('livewire.owner.bar.bar-reports', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'salesData' => $salesData,
            'categoryData' => $categoryData,
            'topItems' => $topItems,
            'paymentMethods' => $paymentMethods,
            'hourlyData' => $hourlyData,
        ]);
    }
}
