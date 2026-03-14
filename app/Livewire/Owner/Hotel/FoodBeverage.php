<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\PosTable;
use App\Models\PosOrder;
use App\Models\PosSession;

#[Layout('components.layouts.app-owner')]
class FoodBeverage extends Component
{
    use WithPagination;

    public $activeTab = 'outlets';
    public $search = '';
    public $selectedHotel = null;
    public $showModal = false;
    public $editMode = false;

    // Outlet Properties
    public $outletId = null;
    #[Rule('required|string|max:100')]
    public $outlet_name = '';

    #[Rule('required|in:restaurant,bar,cafe,room_service,pool_bar,takeaway')]
    public $outlet_type = 'restaurant';

    #[Rule('nullable|date_format:H:i')]
    public $open_time = '';

    #[Rule('nullable|date_format:H:i')]
    public $close_time = '';

    #[Rule('required|in:active,inactive')]
    public $outlet_status = 'active';

    // Table Properties
    public $tableId = null;
    public $selectedOutlet = null;

    #[Rule('required|string|max:20')]
    public $table_number = '';

    #[Rule('required|integer|min:1|max:20')]
    public $table_capacity = 4;

    #[Rule('required|in:available,occupied,reserved,cleaning')]
    public $table_status = 'available';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        if (request()->has('tab')) {
            $this->activeTab = request()->get('tab');
        }
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
        $this->outletId = null;
        $this->outlet_name = '';
        $this->outlet_type = 'restaurant';
        $this->open_time = '';
        $this->close_time = '';
        $this->outlet_status = 'active';

        $this->tableId = null;
        $this->table_number = '';
        $this->table_capacity = 4;
        $this->table_status = 'available';

        $this->resetValidation();
    }

    // Outlet Methods
    public function saveOutlet()
    {
        $this->validate([
            'outlet_name' => 'required|string|max:100',
            'outlet_type' => 'required|in:restaurant,bar,cafe,room_service,pool_bar,takeaway',
            'open_time' => 'nullable|date_format:H:i',
            'close_time' => 'nullable|date_format:H:i',
            'outlet_status' => 'required|in:active,inactive',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedHotel,
                'name' => $this->outlet_name,
                'type' => $this->outlet_type,
                'open_time' => $this->open_time,
                'close_time' => $this->close_time,
                'status' => $this->outlet_status,
            ];

            if ($this->editMode && $this->outletId) {
                PosOutlet::findOrFail($this->outletId)->update($data);
                session()->flash('message', 'Outlet updated successfully.');
            } else {
                PosOutlet::create($data);
                session()->flash('message', 'Outlet created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editOutlet($id)
    {
        $outlet = PosOutlet::findOrFail($id);

        $this->editMode = true;
        $this->outletId = $outlet->id;
        $this->outlet_name = $outlet->name;
        $this->outlet_type = $outlet->type;
        $this->open_time = $outlet->open_time;
        $this->close_time = $outlet->close_time;
        $this->outlet_status = $outlet->status;

        $this->showModal = true;
    }

    public function deleteOutlet($id)
    {
        try {
            PosOutlet::findOrFail($id)->delete();
            session()->flash('message', 'Outlet deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    // Table Methods
    public function saveTable()
    {
        $this->validate([
            'selectedOutlet' => 'required|exists:pos_outlets,id',
            'table_number' => 'required|string|max:20',
            'table_capacity' => 'required|integer|min:1|max:20',
            'table_status' => 'required|in:available,occupied,reserved,cleaning',
        ]);

        try {
            $data = [
                'outlet_id' => $this->selectedOutlet,
                'table_number' => $this->table_number,
                'capacity' => $this->table_capacity,
                'status' => $this->table_status,
            ];

            if ($this->editMode && $this->tableId) {
                PosTable::findOrFail($this->tableId)->update($data);
                session()->flash('message', 'Table updated successfully.');
            } else {
                PosTable::create($data);
                session()->flash('message', 'Table created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editTable($id)
    {
        $table = PosTable::findOrFail($id);

        $this->editMode = true;
        $this->tableId = $table->id;
        $this->selectedOutlet = $table->outlet_id;
        $this->table_number = $table->table_number;
        $this->table_capacity = $table->capacity;
        $this->table_status = $table->status;

        $this->showModal = true;
    }

    public function deleteTable($id)
    {
        try {
            PosTable::findOrFail($id)->delete();
            session()->flash('message', 'Table deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function changeTableStatus($id, $status)
    {
        try {
            PosTable::findOrFail($id)->update(['status' => $status]);
            session()->flash('message', 'Table status updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $outlets = collect();
        $tables = collect();
        $orders = collect();
        $sessions = collect();
        $stats = [
            'active_outlets' => 0,
            'total_tables' => 0,
            'occupied_tables' => 0,
            'orders_today' => 0,
        ];

        if ($this->selectedHotel) {
            // Outlets
            $outletQuery = PosOutlet::where('business_id', $this->selectedHotel);

            if ($this->search && $this->activeTab === 'outlets') {
                $outletQuery->where('name', 'like', '%' . $this->search . '%');
            }

            $outlets = $outletQuery->withCount(['tables', 'orders'])->latest()->paginate(15);

            // Tables
            $tableQuery = PosTable::whereHas('outlet', function($q) {
                $q->where('business_id', $this->selectedHotel);
            })->with('outlet');

            if ($this->search && $this->activeTab === 'tables') {
                $tableQuery->where('table_number', 'like', '%' . $this->search . '%');
            }

            $tables = $tableQuery->latest()->paginate(15);

            // Orders
            $orders = PosOrder::whereHas('outlet', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->with(['outlet', 'table'])
                ->latest()
                ->paginate(15);

            // Sessions
            $sessions = PosSession::whereHas('outlet', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->with('outlet')
                ->latest()
                ->paginate(15);

            // Statistics
            $stats['active_outlets'] = PosOutlet::where('business_id', $this->selectedHotel)
                ->where('status', 'active')->count();
            $stats['total_tables'] = PosTable::whereHas('outlet', function($q) {
                $q->where('business_id', $this->selectedHotel);
            })->count();
            $stats['occupied_tables'] = PosTable::whereHas('outlet', function($q) {
                $q->where('business_id', $this->selectedHotel);
            })->where('status', 'occupied')->count();
            $stats['orders_today'] = PosOrder::whereHas('outlet', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->whereDate('created_at', today())->count();
        }

        return view('livewire.owner.hotel.food-beverage', [
            'hotels' => $hotels,
            'outlets' => $outlets,
            'tables' => $tables,
            'orders' => $orders,
            'sessions' => $sessions,
            'stats' => $stats,
        ]);
    }
}
