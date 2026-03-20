<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\TableReservation;
use App\Models\PosTable;
use App\Models\customers;
use Illuminate\Support\Str;

#[Layout('components.layouts.app-owner')]
class TableReservations extends Component
{
    use WithPagination;

    public $activeTab = 'all';
    public $search = '';
    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $showModal = false;
    public $editMode = false;

    // Reservation Properties
    public $reservationId = null;

    #[Rule('required|string|max:100')]
    public $guest_name = '';

    #[Rule('nullable|string|max:25')]
    public $guest_phone = '';

    #[Rule('nullable|exists:customers,id')]
    public $customer_id = null;

    #[Rule('nullable|exists:pos_tables,id')]
    public $table_id = null;

    #[Rule('nullable|string|max:40')]
    public $section = '';

    #[Rule('required|integer|min:1')]
    public $covers = 1;

    #[Rule('required|date')]
    public $reservation_date = '';

    #[Rule('required')]
    public $reservation_time = '';

    #[Rule('required|integer|min:30|max:480')]
    public $duration_mins = 90;

    #[Rule('nullable|string|max:100')]
    public $occasion = '';

    #[Rule('nullable|numeric|min:0')]
    public $deposit_amount = 0;

    #[Rule('nullable')]
    public $notes = '';

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            // Get first restaurant outlet
            $outlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->first();

            if ($outlet) {
                $this->selectedOutlet = $outlet->id;
            }
        }

        $this->reservation_date = now()->format('Y-m-d');
        $this->reservation_time = now()->addHour()->format('H:i');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
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
        $this->reservationId = null;
        $this->guest_name = '';
        $this->guest_phone = '';
        $this->customer_id = null;
        $this->table_id = null;
        $this->section = '';
        $this->covers = 1;
        $this->reservation_date = now()->format('Y-m-d');
        $this->reservation_time = now()->addHour()->format('H:i');
        $this->duration_mins = 90;
        $this->occasion = '';
        $this->deposit_amount = 0;
        $this->notes = '';

        $this->resetValidation();
    }

    public function saveReservation()
    {
        $this->validate([
            'guest_name' => 'required|string|max:100',
            'guest_phone' => 'nullable|string|max:25',
            'customer_id' => 'nullable|exists:customers,id',
            'table_id' => 'nullable|exists:pos_tables,id',
            'covers' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'duration_mins' => 'required|integer|min:30|max:480',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'guest_name' => $this->guest_name,
                'guest_phone' => $this->guest_phone,
                'customer_id' => $this->customer_id,
                'table_id' => $this->table_id,
                'section' => $this->section,
                'covers' => $this->covers,
                'reservation_date' => $this->reservation_date,
                'reservation_time' => $this->reservation_time,
                'duration_mins' => $this->duration_mins,
                'occasion' => $this->occasion,
                'deposit_amount' => $this->deposit_amount ?? 0,
                'deposit_paid' => false,
                'notes' => $this->notes,
                'status' => 'pending',
                'created_by' => Auth::id(),
            ];

            if ($this->editMode && $this->reservationId) {
                TableReservation::findOrFail($this->reservationId)->update($data);
                session()->flash('message', 'Reservation updated successfully.');
            } else {
                $data['reservation_no'] = $this->generateReservationNumber();
                TableReservation::create($data);
                session()->flash('message', 'Reservation created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    private function generateReservationNumber()
    {
        $prefix = 'RSV';
        $date = now()->format('Ymd');

        // Get last reservation number for today
        $lastReservation = TableReservation::where('reservation_no', 'like', $prefix . $date . '%')
            ->orderBy('reservation_no', 'desc')
            ->first();

        if ($lastReservation) {
            $lastNumber = (int) substr($lastReservation->reservation_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    public function editReservation($id)
    {
        $reservation = TableReservation::findOrFail($id);

        $this->editMode = true;
        $this->reservationId = $reservation->id;
        $this->guest_name = $reservation->guest_name;
        $this->guest_phone = $reservation->guest_phone;
        $this->customer_id = $reservation->customer_id;
        $this->table_id = $reservation->table_id;
        $this->section = $reservation->section;
        $this->covers = $reservation->covers;
        $this->reservation_date = $reservation->reservation_date;
        $this->reservation_time = $reservation->reservation_time;
        $this->duration_mins = $reservation->duration_mins;
        $this->occasion = $reservation->occasion;
        $this->deposit_amount = $reservation->deposit_amount;
        $this->notes = $reservation->notes;

        $this->showModal = true;
    }

    public function confirmReservation($id)
    {
        try {
            $reservation = TableReservation::findOrFail($id);
            $reservation->update(['status' => 'confirmed']);
            session()->flash('message', 'Reservation confirmed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Confirm failed: ' . $e->getMessage());
        }
    }

    public function seatReservation($id)
    {
        try {
            $reservation = TableReservation::findOrFail($id);
            $reservation->update(['status' => 'seated']);
            session()->flash('message', 'Guests seated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Seat failed: ' . $e->getMessage());
        }
    }

    public function cancelReservation($id)
    {
        try {
            $reservation = TableReservation::findOrFail($id);
            $reservation->update(['status' => 'cancelled']);
            session()->flash('message', 'Reservation cancelled.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cancel failed: ' . $e->getMessage());
        }
    }

    public function markNoShow($id)
    {
        try {
            $reservation = TableReservation::findOrFail($id);
            $reservation->update(['status' => 'no_show']);
            session()->flash('message', 'Marked as no-show.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $reservations = collect();
        $stats = [
            'today' => 0,
            'confirmed' => 0,
            'pending' => 0,
            'total_covers' => 0,
        ];

        if ($this->selectedOutlet) {
            $query = TableReservation::where('outlet_id', $this->selectedOutlet);

            // Filter by status
            if ($this->activeTab !== 'all') {
                $query->where('status', $this->activeTab);
            }

            // Search
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('reservation_no', 'like', '%' . $this->search . '%')
                      ->orWhere('guest_name', 'like', '%' . $this->search . '%')
                      ->orWhere('guest_phone', 'like', '%' . $this->search . '%');
                });
            }

            $reservations = $query->with(['customer', 'table', 'createdBy'])
                ->latest('reservation_date')
                ->latest('reservation_time')
                ->paginate(15);

            // Statistics
            $stats['today'] = TableReservation::where('outlet_id', $this->selectedOutlet)
                ->whereDate('reservation_date', today())->count();
            $stats['confirmed'] = TableReservation::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'confirmed')->count();
            $stats['pending'] = TableReservation::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'pending')->count();
            $stats['total_covers'] = TableReservation::where('outlet_id', $this->selectedOutlet)
                ->whereDate('reservation_date', today())
                ->sum('covers');
        }

        // Get outlets for dropdown
        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->get();
        }

        // Get tables for reservation
        $tables = PosTable::where('outlet_id', $this->selectedOutlet)->get();

        // Get customers
        $customers = customers::where('business_id', $this->selectedBusiness)->get();

        return view('livewire.owner.restaurant.table-reservations', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'reservations' => $reservations,
            'stats' => $stats,
            'tables' => $tables,
            'customers' => $customers,
        ]);
    }
}
