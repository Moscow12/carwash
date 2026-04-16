<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\LaundryOrder;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Folio;

#[Layout('components.layouts.app-owner')]
class LaundryManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = null;
    public $filterStatus = 'all';
    public $showModal = false;
    public $editMode = false;

    // Laundry Order Properties
    public $orderId = null;

    #[Rule('nullable|exists:reservations,id')]
    public $reservation_id = null;

    #[Rule('nullable|exists:rooms,id')]
    public $room_id = null;

    #[Rule('required|exists:guests,id')]
    public $guest_id = null;

    #[Rule('nullable|exists:folios,id')]
    public $folio_id = null;

    #[Rule('required|string|max:100')]
    public $item_type = '';

    #[Rule('required|integer|min:1')]
    public $quantity = 1;

    #[Rule('required|in:regular,express')]
    public $service_type = 'regular';

    #[Rule('nullable|numeric|min:0')]
    public $charge_amount = 0;

    #[Rule('nullable|date')]
    public $expected_completion = null;

    #[Rule('nullable|string')]
    public $special_instructions = '';

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->orderId = null;
        $this->reservation_id = null;
        $this->room_id = null;
        $this->guest_id = null;
        $this->folio_id = null;
        $this->item_type = '';
        $this->quantity = 1;
        $this->service_type = 'regular';
        $this->charge_amount = 0;
        $this->expected_completion = null;
        $this->special_instructions = '';
        $this->resetValidation();
    }

    public function saveOrder()
    {
        $this->validate([
            'guest_id' => 'required|exists:guests,id',
            'item_type' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'service_type' => 'required|in:regular,express',
            'charge_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'reservation_id' => $this->reservation_id,
                'room_id' => $this->room_id,
                'guest_id' => $this->guest_id,
                'folio_id' => $this->folio_id,
                'item_type' => $this->item_type,
                'quantity' => $this->quantity,
                'service_type' => $this->service_type,
                'charge_amount' => $this->charge_amount ?? 0,
                'expected_completion' => $this->expected_completion,
                'special_instructions' => $this->special_instructions,
                'status' => 'pending',
                'received_at' => now(),
            ];

            if ($this->editMode && $this->orderId) {
                LaundryOrder::findOrFail($this->orderId)->update($data);
                session()->flash('message', 'Laundry order updated successfully.');
            } else {
                // Auto-generate order number
                $lastOrder = LaundryOrder::where('business_id', $this->selectedBusiness)
                    ->whereNotNull('order_no')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $nextNumber = $lastOrder ? (int) substr($lastOrder->order_no, 3) + 1 : 1;
                $data['order_no'] = 'LO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                LaundryOrder::create($data);
                session()->flash('message', 'Laundry order created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editOrder($id)
    {
        $order = LaundryOrder::findOrFail($id);

        $this->editMode = true;
        $this->orderId = $order->id;
        $this->reservation_id = $order->reservation_id;
        $this->room_id = $order->room_id;
        $this->guest_id = $order->guest_id;
        $this->folio_id = $order->folio_id;
        $this->item_type = $order->item_type;
        $this->quantity = $order->quantity;
        $this->service_type = $order->service_type;
        $this->charge_amount = $order->charge_amount;
        $this->expected_completion = $order->expected_completion;
        $this->special_instructions = $order->special_instructions;

        $this->showModal = true;
    }

    public function markInProgress($id)
    {
        try {
            LaundryOrder::findOrFail($id)->update(['status' => 'in_progress']);
            session()->flash('message', 'Order marked as in progress.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function markCompleted($id)
    {
        try {
            LaundryOrder::findOrFail($id)->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            session()->flash('message', 'Order marked as completed.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function markDelivered($id)
    {
        try {
            LaundryOrder::findOrFail($id)->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => Auth::user()->staff->id ?? null,
            ]);
            session()->flash('message', 'Order marked as delivered.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delivery failed: ' . $e->getMessage());
        }
    }

    public function cancelOrder($id)
    {
        try {
            LaundryOrder::findOrFail($id)->update(['status' => 'cancelled']);
            session()->flash('message', 'Order cancelled.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cancel failed: ' . $e->getMessage());
        }
    }

    public function deleteOrder($id)
    {
        try {
            LaundryOrder::findOrFail($id)->delete();
            session()->flash('message', 'Order deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $orders = collect();
        $stats = [
            'pending' => 0,
            'in_progress' => 0,
            'completed_today' => 0,
            'total_revenue' => 0,
        ];

        if ($this->selectedBusiness) {
            $query = LaundryOrder::where('business_id', $this->selectedBusiness);

            // Filter by status
            if ($this->filterStatus !== 'all') {
                $query->where('status', $this->filterStatus);
            }

            // Search
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('item_type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('guest', function($gq) {
                          $gq->where('full_name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('room', function($rq) {
                          $rq->where('room_number', 'like', '%' . $this->search . '%');
                      });
                });
            }

            $orders = $query->with(['guest', 'room', 'reservation', 'folio', 'deliveredBy'])
                ->latest('received_at')
                ->paginate(15);

            // Statistics
            $stats['pending'] = LaundryOrder::where('business_id', $this->selectedBusiness)
                ->where('status', 'pending')->count();

            $stats['in_progress'] = LaundryOrder::where('business_id', $this->selectedBusiness)
                ->where('status', 'in_progress')->count();

            $stats['completed_today'] = LaundryOrder::where('business_id', $this->selectedBusiness)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())->count();

            $stats['total_revenue'] = LaundryOrder::where('business_id', $this->selectedBusiness)
                ->whereDate('received_at', today())
                ->sum('charge_amount');
        }

        // Get guests for dropdown
        $guests = Guest::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->get();

        // Get rooms for dropdown
        $rooms = Room::where('business_id', $this->selectedBusiness)->get();

        // Get active reservations for dropdown
        $reservations = Reservation::where('business_id', $this->selectedBusiness)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with('guest')
            ->get();

        return view('livewire.owner.hotel.laundry-management', [
            'businesses' => $businesses,
            'orders' => $orders,
            'stats' => $stats,
            'guests' => $guests,
            'rooms' => $rooms,
            'reservations' => $reservations,
        ]);
    }
}
