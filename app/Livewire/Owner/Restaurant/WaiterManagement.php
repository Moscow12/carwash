<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\WaiterAssignment;
use App\Models\PosTable;
use App\Models\PosSession;
use App\Models\staffs;

#[Layout('components.layouts.app-owner')]
class WaiterManagement extends Component
{
    use WithPagination;

    public $activeTab = 'assignments';
    public $search = '';
    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $selectedSession = null;
    public $showModal = false;
    public $editMode = false;

    // Assignment Properties
    public $assignmentId = null;

    #[Rule('required|exists:staffs,id')]
    public $staff_id = null;

    #[Rule('required|exists:pos_tables,id')]
    public $table_id = null;

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            $outlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->first();

            if ($outlet) {
                $this->selectedOutlet = $outlet->id;

                // Get active session (open sessions have null closed_at)
                $session = PosSession::where('outlet_id', $outlet->id)
                    ->whereNull('closed_at')
                    ->latest('opened_at')
                    ->first();

                if ($session) {
                    $this->selectedSession = $session->id;
                }
            }
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
        $this->assignmentId = null;
        $this->staff_id = null;
        $this->table_id = null;
        $this->resetValidation();
    }

    public function assignWaiter()
    {
        $this->validate([
            'staff_id' => 'required|exists:staffs,id',
            'table_id' => 'required|exists:pos_tables,id',
        ]);

        try {
            // Check if table already assigned
            $existing = WaiterAssignment::where('session_id', $this->selectedSession)
                ->where('table_id', $this->table_id)
                ->whereNull('released_at')
                ->first();

            if ($existing) {
                session()->flash('error', 'Table is already assigned to a waiter.');
                return;
            }

            WaiterAssignment::create([
                'session_id' => $this->selectedSession,
                'outlet_id' => $this->selectedOutlet,
                'table_id' => $this->table_id,
                'staff_id' => $this->staff_id,
                'assigned_at' => now(),
            ]);

            // Update table status to occupied
            PosTable::find($this->table_id)->update(['status' => 'occupied']);

            session()->flash('message', 'Waiter assigned successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Assignment failed: ' . $e->getMessage());
        }
    }

    public function releaseAssignment($id)
    {
        try {
            $assignment = WaiterAssignment::findOrFail($id);
            $assignment->update(['released_at' => now()]);

            // Update table status to available
            PosTable::find($assignment->table_id)->update(['status' => 'available']);

            session()->flash('message', 'Assignment released successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Release failed: ' . $e->getMessage());
        }
    }

    public function reassignWaiter($assignmentId, $newStaffId)
    {
        try {
            $assignment = WaiterAssignment::findOrFail($assignmentId);
            $assignment->update(['staff_id' => $newStaffId]);

            session()->flash('message', 'Waiter reassigned successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Reassignment failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $sessions = collect();
        $assignments = collect();
        $stats = [
            'active_waiters' => 0,
            'assigned_tables' => 0,
            'unassigned_tables' => 0,
            'total_tables' => 0,
        ];

        if ($this->selectedOutlet) {
            // Get sessions for dropdown (open sessions have null closed_at)
            $sessions = PosSession::where('outlet_id', $this->selectedOutlet)
                ->whereNull('closed_at')
                ->latest('opened_at')
                ->take(5)
                ->get();

            if ($this->selectedSession) {
                // Assignments
                $query = WaiterAssignment::where('session_id', $this->selectedSession)
                    ->whereNull('released_at');

                if ($this->search) {
                    $query->whereHas('staff', function($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    })->orWhereHas('table', function($q) {
                        $q->where('table_number', 'like', '%' . $this->search . '%');
                    });
                }

                $assignments = $query->with(['staff', 'table', 'outlet'])
                    ->latest('assigned_at')
                    ->paginate(15);

                // Statistics
                $stats['active_waiters'] = WaiterAssignment::where('session_id', $this->selectedSession)
                    ->whereNull('released_at')
                    ->distinct('staff_id')
                    ->count('staff_id');

                $stats['assigned_tables'] = WaiterAssignment::where('session_id', $this->selectedSession)
                    ->whereNull('released_at')
                    ->count();

                $stats['total_tables'] = PosTable::where('outlet_id', $this->selectedOutlet)
                    ->where('is_active', true)
                    ->count();

                $stats['unassigned_tables'] = $stats['total_tables'] - $stats['assigned_tables'];
            }
        }

        // Get outlets for dropdown
        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->get();
        }

        // Get available tables
        $availableTables = PosTable::where('outlet_id', $this->selectedOutlet)
            ->where('is_active', true)
            ->whereDoesntHave('waiterAssignments', function($q) {
                $q->where('session_id', $this->selectedSession)
                  ->whereNull('released_at');
            })
            ->get();

        // Get waiters/staff
        $waiters = staffs::where('business_id', $this->selectedBusiness)
            ->whereIn('position', ['waiter', 'server', 'bartender'])
            ->where('status', 'active')
            ->get();

        return view('livewire.owner.restaurant.waiter-management', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'sessions' => $sessions,
            'assignments' => $assignments,
            'stats' => $stats,
            'availableTables' => $availableTables,
            'waiters' => $waiters,
        ]);
    }
}
