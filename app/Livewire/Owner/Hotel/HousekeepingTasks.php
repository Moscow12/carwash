<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\HousekeepingTask;
use App\Models\Room;
use App\Models\staffs;

#[Layout('components.layouts.app-owner')]
class HousekeepingTasks extends Component
{
    use WithPagination;

    public $selectedHotel = null;
    public $statusFilter = '';
    public $priorityFilter = '';
    public $showModal = false;
    public $taskId = null;
    public $room_id = '';
    public $assigned_to = '';
    public $task_type = 'cleaning';
    public $priority = 'normal';
    public $scheduled_date = '';
    public $notes = '';

    public $rooms = [];
    public $staff = [];

    public function mount()
    {
        $hotel = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->loadData();
        }
        $this->scheduled_date = today()->format('Y-m-d');
    }

    public function loadData()
    {
        if (!$this->selectedHotel) return;

        $this->rooms = Room::where('business_id', $this->selectedHotel)
            ->where('is_active', true)
            ->orderBy('number')
            ->get();

        $this->staff = staffs::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function createTask()
    {
        $this->validate([
            'room_id' => 'required|exists:rooms,id',
            'assigned_to' => 'nullable|exists:staffs,id',
            'task_type' => 'required|in:cleaning,deep_cleaning,turndown_service,special_request',
            'priority' => 'required|in:urgent,high,normal,low',
            'scheduled_date' => 'required|date',
        ]);

        HousekeepingTask::create([
            'business_id' => $this->selectedHotel,
            'room_id' => $this->room_id,
            'assigned_to' => $this->assigned_to ?: null,
            'task_type' => $this->task_type,
            'priority' => $this->priority,
            'status' => 'pending',
            'scheduled_date' => $this->scheduled_date,
            'notes' => $this->notes,
        ]);

        session()->flash('message', 'Task created successfully.');
        $this->closeModal();
    }

    public function updateTaskStatus($id, $status)
    {
        $task = HousekeepingTask::findOrFail($id);
        $data = ['status' => $status];

        if ($status === 'in_progress' && !$task->started_at) {
            $data['started_at'] = now();
        } elseif ($status === 'completed' && !$task->completed_at) {
            $data['completed_at'] = now();

            // Update room status
            if ($task->room) {
                $task->room->update(['status' => 'available']);
            }
        }

        $task->update($data);
        session()->flash('message', 'Task status updated.');
    }

    public function openModal()
    {
        $this->reset(['taskId', 'room_id', 'assigned_to', 'task_type', 'priority', 'notes']);
        $this->task_type = 'cleaning';
        $this->priority = 'normal';
        $this->scheduled_date = today()->format('Y-m-d');
        $this->showModal = true;
        $this->loadData();
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        $hotels = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $query = HousekeepingTask::with(['room', 'assignedTo'])
            ->where('business_id', $this->selectedHotel);

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        $tasks = $query->latest('scheduled_date')->paginate(20);

        $stats = [
            'pending' => HousekeepingTask::where('business_id', $this->selectedHotel)->where('status', 'pending')->count(),
            'in_progress' => HousekeepingTask::where('business_id', $this->selectedHotel)->where('status', 'in_progress')->count(),
            'completed_today' => HousekeepingTask::where('business_id', $this->selectedHotel)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
        ];

        return view('livewire.owner.hotel.housekeeping-tasks', [
            'hotels' => $hotels,
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }
}
