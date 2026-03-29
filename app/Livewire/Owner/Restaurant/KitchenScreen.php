<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\KitchenTicket;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;

#[Layout('components.layouts.app-owner')]
class KitchenScreen extends Component
{
    // Business & Outlet Selection
    public $selectedBusiness = '';
    public $ownerBusinesses = [];
    public $selectedOutlet = '';
    public $availableOutlets = [];

    // Filters
    public $statusFilter = 'active'; // active, queued, preparing, ready, all
    public $stationFilter = '';
    public $availableStations = [];

    // Data
    public $orders = [];

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

        // Also include businesses explicitly marked as restaurant type
        $ownedBusinessesByType = $user->ownedBusinesses()
            ->where('type', 'restaurant')
            ->orderBy('name')
            ->get(['id', 'name']);

        // Merge owned businesses
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

        // Merge and deduplicate
        $allBusinesses = $ownedBusinesses->merge($assignedBusinesses)->unique('id');

        $this->ownerBusinesses = $allBusinesses->pluck('name', 'id')->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->selectedBusiness = array_key_first($this->ownerBusinesses);
            $this->loadOutlets();
        }
    }

    public function updatedSelectedBusiness()
    {
        $this->loadOutlets();
        $this->loadOrders();
    }

    public function updatedSelectedOutlet()
    {
        $this->loadStations();
        $this->loadOrders();
    }

    public function updatedStatusFilter()
    {
        $this->loadOrders();
    }

    public function updatedStationFilter()
    {
        $this->loadOrders();
    }

    public function loadOutlets()
    {
        if (!$this->selectedBusiness) return;

        $user = Auth::user();

        // Check if user has outlet-specific assignments
        $assignedOutletIds = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNotNull('outlet_id')
            ->pluck('outlet_id')
            ->toArray();

        // Check if user has business-level access
        $hasBusinessLevelAccess = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNull('outlet_id')
            ->exists();

        // Check if user owns this business
        $ownsBusinesss = $user->ownedBusinesses()->where('id', $this->selectedBusiness)->exists();

        // Build query
        $query = PosOutlet::where('business_id', $this->selectedBusiness)
            ->where('status', 'active');

        // Filter by outlet access
        if (!empty($assignedOutletIds) && !$ownsBusinesss && !$hasBusinessLevelAccess) {
            $query->whereIn('id', $assignedOutletIds);
        }

        $this->availableOutlets = $query->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->availableOutlets) && !$this->selectedOutlet) {
            $this->selectedOutlet = array_key_first($this->availableOutlets);
            $this->loadStations();
            $this->loadOrders();
        }
    }

    public function loadStations()
    {
        if (!$this->selectedOutlet) return;

        $this->availableStations = KitchenTicket::where('outlet_id', $this->selectedOutlet)
            ->whereIn('status', ['queued', 'preparing', 'ready'])
            ->distinct()
            ->pluck('station')
            ->toArray();
    }

    public function loadOrders()
    {
        if (!$this->selectedOutlet) {
            $this->orders = [];
            return;
        }

        $query = KitchenTicket::where('outlet_id', $this->selectedOutlet)
            ->with([
                'order.table',
                'order.servedBy',
                'orderItem.menuItem'
            ]);

        // Apply status filter
        if ($this->statusFilter === 'active') {
            $query->whereIn('status', ['queued', 'preparing', 'ready']);
        } elseif (in_array($this->statusFilter, ['queued', 'preparing', 'ready'])) {
            $query->where('status', $this->statusFilter);
        }

        // Apply station filter
        if ($this->stationFilter) {
            $query->where('station', $this->stationFilter);
        }

        $tickets = $query->orderBy('received_at', 'asc')->get();

        // Group tickets by order
        $this->orders = $tickets->groupBy('order_id')->map(function ($orderTickets) {
            $order = $orderTickets->first()->order;

            return [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'table_number' => $order->table?->table_number ?? 'N/A',
                'order_type' => $order->order_type,
                'covers' => $order->covers,
                'waiter' => $order->servedBy?->name ?? 'N/A',
                'received_at' => $orderTickets->min('received_at'),
                'items' => $orderTickets->map(function ($ticket) {
                    return [
                        'ticket_id' => $ticket->id,
                        'item_name' => $ticket->orderItem?->menuItem?->name ?? 'Unknown Item',
                        'quantity' => $ticket->orderItem?->quantity ?? 1,
                        'kitchen_notes' => $ticket->orderItem?->kitchen_notes,
                        'station' => $ticket->station,
                        'status' => $ticket->status,
                        'received_at' => $ticket->received_at,
                        'started_at' => $ticket->started_at,
                        'ready_at' => $ticket->ready_at,
                        'served_at' => $ticket->served_at,
                    ];
                })->toArray()
            ];
        })->values()->toArray();
    }

    public function startPreparing($ticketId)
    {
        $ticket = KitchenTicket::find($ticketId);
        if ($ticket && $ticket->status === 'queued') {
            $ticket->update([
                'status' => 'preparing',
                'started_at' => now(),
            ]);

            // Update order item status
            $ticket->orderItem->update(['status' => 'preparing']);

            $this->loadOrders();
            session()->flash('message', 'Item started preparing.');
        }
    }

    public function markReady($ticketId)
    {
        $ticket = KitchenTicket::find($ticketId);
        if ($ticket && $ticket->status === 'preparing') {
            $ticket->update([
                'status' => 'ready',
                'ready_at' => now(),
            ]);

            // Update order item status
            $ticket->orderItem->update(['status' => 'ready']);

            // Check if all items in order are ready
            $order = $ticket->order;
            $allReady = $order->kitchenTickets()
                ->whereIn('status', ['queued', 'preparing'])
                ->count() === 0;

            if ($allReady) {
                $order->update(['status' => 'ready']);
            }

            $this->loadOrders();
            session()->flash('message', 'Item marked as ready.');
        }
    }

    public function markServed($ticketId)
    {
        $ticket = KitchenTicket::find($ticketId);
        if ($ticket && $ticket->status === 'ready') {
            $ticket->update([
                'status' => 'served',
                'served_at' => now()
            ]);

            // Update order item status
            $ticket->orderItem->update(['status' => 'served']);

            // Check if all items in order are served
            $order = $ticket->order;
            $allServed = $order->kitchenTickets()
                ->whereNotIn('status', ['served', 'cancelled'])
                ->count() === 0;

            if ($allServed) {
                $order->update(['status' => 'served']);
            }

            $this->loadOrders();
            session()->flash('message', 'Item marked as served.');
        }
    }

    public function cancelTicket($ticketId)
    {
        $ticket = KitchenTicket::find($ticketId);
        if ($ticket) {
            $ticket->update(['status' => 'cancelled']);

            $this->loadOrders();
            session()->flash('message', 'Ticket cancelled.');
        }
    }

    public function render()
    {
        return view('livewire.owner.restaurant.kitchen-screen');
    }
}
