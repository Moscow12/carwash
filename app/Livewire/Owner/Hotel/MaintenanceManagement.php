<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\staffs;

#[Layout('components.layouts.app-owner')]
class MaintenanceManagement extends Component
{
    use WithPagination;

    public $activeTab = 'requests';
    public $search = '';
    public $selectedHotel = null;
    public $categoryFilter = '';
    public $priorityFilter = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editMode = false;
    public $requestId = null;

    #[Rule('nullable|exists:rooms,id')]
    public $room_id = null;

    #[Rule('required|in:plumbing,electrical,AC,furniture,network,other')]
    public $category = 'other';

    #[Rule('required|string|max:1000')]
    public $description = '';

    #[Rule('required|in:low,normal,high,urgent')]
    public $priority = 'normal';

    #[Rule('nullable|exists:staffs,id')]
    public $assigned_to = null;

    #[Rule('nullable|numeric|min:0')]
    public $estimated_cost = 0;

    #[Rule('nullable|numeric|min:0')]
    public $actual_cost = 0;

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        // Check for tab parameter in URL
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
        $this->requestId = null;
        $this->room_id = null;
        $this->category = 'other';
        $this->description = '';
        $this->priority = 'normal';
        $this->assigned_to = null;
        $this->estimated_cost = 0;
        $this->actual_cost = 0;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            $data = [
                'business_id' => $this->selectedHotel,
                'room_id' => $this->room_id,
                'category' => $this->category,
                'description' => $this->description,
                'priority' => $this->priority,
                'assigned_to' => $this->assigned_to,
                'estimated_cost' => $this->estimated_cost,
                'actual_cost' => $this->actual_cost,
            ];

            if ($this->editMode) {
                $request = MaintenanceRequest::findOrFail($this->requestId);
                $request->update($data);
                session()->flash('message', 'Maintenance request updated successfully.');
            } else {
                $data['status'] = 'open';
                MaintenanceRequest::create($data);
                session()->flash('message', 'Maintenance request created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editRequest($id)
    {
        $request = MaintenanceRequest::findOrFail($id);

        $this->editMode = true;
        $this->requestId = $request->id;
        $this->room_id = $request->room_id;
        $this->category = $request->category;
        $this->description = $request->description;
        $this->priority = $request->priority;
        $this->assigned_to = $request->assigned_to;
        $this->estimated_cost = $request->estimated_cost;
        $this->actual_cost = $request->actual_cost;

        $this->showModal = true;
    }

    public function updateStatus($id, $status)
    {
        try {
            $request = MaintenanceRequest::findOrFail($id);
            $data = ['status' => $status];

            if ($status === 'resolved' || $status === 'closed') {
                $data['resolved_at'] = now();
            }

            $request->update($data);
            session()->flash('message', 'Status updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            MaintenanceRequest::findOrFail($id)->delete();
            session()->flash('message', 'Maintenance request deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $requests = collect();
        $stats = [
            'open' => 0,
            'in_progress' => 0,
            'urgent' => 0,
            'total_cost' => 0,
        ];

        $rooms = collect();
        $staff = collect();

        if ($this->selectedHotel) {
            $query = MaintenanceRequest::where('business_id', $this->selectedHotel)
                ->with(['room', 'assignedTo']);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('room', function ($roomQuery) {
                          $roomQuery->where('number', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->categoryFilter) {
                $query->where('category', $this->categoryFilter);
            }

            if ($this->priorityFilter) {
                $query->where('priority', $this->priorityFilter);
            }

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $requests = $query->latest()->paginate(15);

            // Statistics
            $stats['open'] = MaintenanceRequest::where('business_id', $this->selectedHotel)
                ->where('status', 'open')->count();
            $stats['in_progress'] = MaintenanceRequest::where('business_id', $this->selectedHotel)
                ->where('status', 'in_progress')->count();
            $stats['urgent'] = MaintenanceRequest::where('business_id', $this->selectedHotel)
                ->where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count();
            $stats['total_cost'] = MaintenanceRequest::where('business_id', $this->selectedHotel)
                ->sum('actual_cost');

            // Get rooms and staff for dropdowns
            $rooms = Room::where('business_id', $this->selectedHotel)
                ->orderBy('number')
                ->get();

            $staff = staffs::where('business_id', $this->selectedHotel)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.owner.hotel.maintenance-management', [
            'hotels' => $hotels,
            'requests' => $requests,
            'stats' => $stats,
            'rooms' => $rooms,
            'staff' => $staff,
        ]);
    }
}
