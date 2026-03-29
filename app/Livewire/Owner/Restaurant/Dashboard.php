<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PosOrder;
use App\Models\PosOutlet;
use App\Models\KitchenTicket;
use App\Models\MenuItem;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    // Business & Outlet Selection
    public $selectedBusiness = '';
    public $ownerBusinesses = [];
    public $selectedOutlet = '';
    public $availableOutlets = [];

    // Date Range
    public $dateRange = 'today'; // today, week, month, custom
    public $startDate;
    public $endDate;

    // Metrics
    public $todayStats = [];
    public $revenueData = [];
    public $topSellingItems = [];
    public $kitchenStats = [];
    public $recentOrders = [];

    public function mount()
    {
        $user = Auth::user();

        // Get owned businesses with restaurant outlets
        $ownedBusinessesWithOutlets = $user->ownedBusinesses()
            ->whereHas('outlets', function($query) {
                $query->where('type', 'restaurant')
                      ->orWhere('type', 'bar')
                      ->orWhere('type', 'cafe');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $ownedBusinessesByType = $user->ownedBusinesses()
            ->where('type', 'restaurant')
            ->orderBy('name')
            ->get(['id', 'name']);

        $ownedBusinesses = $ownedBusinessesWithOutlets->merge($ownedBusinessesByType)->unique('id');

        // Get assigned businesses via UserBusinessRole
        $assignedBusinessIds = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('business_id')
            ->unique();

        $assignedBusinesses = collect();
        if ($assignedBusinessIds->isNotEmpty()) {
            $assignedBusinesses = DB::table('businesses')
                ->whereIn('id', $assignedBusinessIds)
                ->where(function($query) {
                    $query->where('type', 'restaurant')
                          ->orWhereExists(function($subQuery) {
                              $subQuery->select(DB::raw(1))
                                  ->from('pos_outlets')
                                  ->whereColumn('pos_outlets.business_id', 'businesses.id')
                                  ->where(function($q) {
                                      $q->where('type', 'restaurant')
                                        ->orWhere('type', 'bar')
                                        ->orWhere('type', 'cafe');
                                  });
                          });
                })
                ->select('id', 'name')
                ->get();
        }

        $allBusinesses = $ownedBusinesses->merge($assignedBusinesses)->unique('id');
        $this->ownerBusinesses = $allBusinesses->pluck('name', 'id')->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->selectedBusiness = array_key_first($this->ownerBusinesses);
            $this->loadOutlets();
        }

        // Set default date range
        $this->startDate = now()->startOfDay()->format('Y-m-d');
        $this->endDate = now()->endOfDay()->format('Y-m-d');
    }

    public function updatedSelectedBusiness()
    {
        $this->loadOutlets();
        $this->loadDashboardData();
    }

    public function updatedSelectedOutlet()
    {
        $this->loadDashboardData();
    }

    public function updatedDateRange()
    {
        switch ($this->dateRange) {
            case 'today':
                $this->startDate = now()->startOfDay()->format('Y-m-d');
                $this->endDate = now()->endOfDay()->format('Y-m-d');
                break;
            case 'week':
                $this->startDate = now()->startOfWeek()->format('Y-m-d');
                $this->endDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
        }
        $this->loadDashboardData();
    }

    public function loadOutlets()
    {
        if (!$this->selectedBusiness) return;

        $user = Auth::user();

        $assignedOutletIds = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNotNull('outlet_id')
            ->pluck('outlet_id')
            ->toArray();

        $hasBusinessLevelAccess = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNull('outlet_id')
            ->exists();

        $ownsBusinesss = $user->ownedBusinesses()->where('id', $this->selectedBusiness)->exists();

        $query = PosOutlet::where('business_id', $this->selectedBusiness)
            ->where('status', 'active');

        if (!empty($assignedOutletIds) && !$ownsBusinesss && !$hasBusinessLevelAccess) {
            $query->whereIn('id', $assignedOutletIds);
        }

        $this->availableOutlets = $query->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Add "All Outlets" option
        $this->availableOutlets = ['all' => 'All Outlets'] + $this->availableOutlets;

        if (!$this->selectedOutlet) {
            $this->selectedOutlet = 'all';
            $this->loadDashboardData();
        }
    }

    public function loadDashboardData()
    {
        if (!$this->selectedBusiness) return;

        $this->loadTodayStats();
        $this->loadRevenueData();
        $this->loadTopSellingItems();
        $this->loadKitchenStats();
        $this->loadRecentOrders();
    }

    private function getOrderQuery()
    {
        $query = PosOrder::where('business_id', $this->selectedBusiness)
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        if ($this->selectedOutlet && $this->selectedOutlet !== 'all') {
            $query->where('outlet_id', $this->selectedOutlet);
        }

        return $query;
    }

    private function loadTodayStats()
    {
        $query = $this->getOrderQuery();

        $totalOrders = $query->count();
        $totalRevenue = $query->where('status', 'paid')->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalCovers = $query->sum('covers');

        $this->todayStats = [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'total_covers' => $totalCovers,
        ];
    }

    private function loadRevenueData()
    {
        // Get daily revenue for the selected period
        $query = $this->getOrderQuery();

        $revenueByDate = $query->where('status', 'paid')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $this->revenueData = $revenueByDate->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'revenue' => $item->revenue,
                'orders' => $item->orders,
            ];
        })->toArray();
    }

    private function loadTopSellingItems()
    {
        $query = $this->getOrderQuery();

        $topItems = DB::table('pos_order_items')
            ->join('pos_orders', 'pos_order_items.order_id', '=', 'pos_orders.id')
            ->join('menu_items', 'pos_order_items.menu_item_id', '=', 'menu_items.id')
            ->where('pos_orders.business_id', $this->selectedBusiness)
            ->whereBetween('pos_orders.created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ])
            ->whereNotIn('pos_order_items.status', ['voided']);

        if ($this->selectedOutlet && $this->selectedOutlet !== 'all') {
            $topItems->where('pos_orders.outlet_id', $this->selectedOutlet);
        }

        $topItems = $topItems->select(
                'menu_items.name',
                DB::raw('SUM(pos_order_items.quantity) as total_quantity'),
                DB::raw('SUM(pos_order_items.total) as total_revenue')
            )
            ->groupBy('menu_items.id', 'menu_items.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        $this->topSellingItems = $topItems->map(function($item) {
            return [
                'name' => $item->name,
                'quantity' => $item->total_quantity,
                'revenue' => $item->total_revenue,
            ];
        })->toArray();
    }

    private function loadKitchenStats()
    {
        $query = KitchenTicket::whereHas('order', function($q) {
            $q->where('business_id', $this->selectedBusiness)
              ->whereBetween('created_at', [
                  Carbon::parse($this->startDate)->startOfDay(),
                  Carbon::parse($this->endDate)->endOfDay()
              ]);
        });

        if ($this->selectedOutlet && $this->selectedOutlet !== 'all') {
            $query->where('outlet_id', $this->selectedOutlet);
        }

        $totalTickets = $query->count();
        $queuedTickets = (clone $query)->where('status', 'queued')->count();
        $preparingTickets = (clone $query)->where('status', 'preparing')->count();
        $readyTickets = (clone $query)->where('status', 'ready')->count();

        // Calculate average turnaround time for served tickets
        $avgTurnaround = (clone $query)
            ->where('status', 'served')
            ->whereNotNull('served_at')
            ->get()
            ->avg(function($ticket) {
                return $ticket->served_at->diffInMinutes($ticket->received_at);
            });

        $this->kitchenStats = [
            'total_tickets' => $totalTickets,
            'queued' => $queuedTickets,
            'preparing' => $preparingTickets,
            'ready' => $readyTickets,
            'avg_turnaround' => round($avgTurnaround ?? 0),
        ];
    }

    private function loadRecentOrders()
    {
        $query = $this->getOrderQuery();

        $orders = $query->with(['table', 'servedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $this->recentOrders = $orders->map(function($order) {
            return [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'table' => $order->table?->table_number ?? 'N/A',
                'order_type' => $order->order_type,
                'covers' => $order->covers,
                'total' => $order->total,
                'status' => $order->status,
                'waiter' => $order->servedBy?->name ?? 'N/A',
                'created_at' => $order->created_at->diffForHumans(),
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.owner.restaurant.dashboard');
    }
}
