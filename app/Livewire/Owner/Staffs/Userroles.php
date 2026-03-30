<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserBusinessRole;
use App\Models\User;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\staffs;

#[Layout('components.layouts.app-owner')]
class Userroles extends Component
{
    use WithPagination;

    // Filters
    public $selectedBusiness = '';
    public $selectedRole = '';
    public $search = '';

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $user_id = '';
    public $business_id = '';
    public $outlet_id = '';
    public $role = 'cashier';
    public $is_active = true;

    // Data
    public $ownerBusinesses = [];
    public $availableOutlets = [];
    public $availableUsers = [];
    public $availableRoles = [];

    // Stats
    public $roleStats = [];

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->selectedBusiness = array_key_first($this->ownerBusinesses);
            $this->business_id = $this->selectedBusiness;
            $this->loadOutlets();
            $this->loadUsers();
        }

        // Define available roles
        $this->availableRoles = [
            'owner' => ['name' => 'Owner', 'description' => 'Full access to all business features', 'color' => 'danger'],
            'admin' => ['name' => 'Admin', 'description' => 'Manage settings, users, and operations', 'color' => 'primary'],
            'manager' => ['name' => 'Manager', 'description' => 'Supervise operations and staff', 'color' => 'info'],
            'cashier' => ['name' => 'Cashier', 'description' => 'Handle sales and payments', 'color' => 'success'],
            'waiter' => ['name' => 'Waiter', 'description' => 'Take orders and serve customers', 'color' => 'warning'],
            'bartender' => ['name' => 'Bartender', 'description' => 'Prepare and serve beverages', 'color' => 'dark'],
            'receptionist' => ['name' => 'Receptionist', 'description' => 'Handle front desk operations', 'color' => 'secondary'],
            'housekeeping' => ['name' => 'Housekeeping', 'description' => 'Room cleaning and maintenance', 'color' => 'info'],
            'kitchen' => ['name' => 'Kitchen', 'description' => 'Food preparation and cooking', 'color' => 'danger'],
            'accountant' => ['name' => 'Accountant', 'description' => 'Financial management and reporting', 'color' => 'primary'],
            'viewer' => ['name' => 'Viewer', 'description' => 'View-only access to reports', 'color' => 'secondary'],
        ];
    }

    public function updatedSelectedBusiness()
    {
        $this->business_id = $this->selectedBusiness;
        $this->resetPage();
        $this->loadOutlets();
        $this->loadUsers();
        $this->loadStats();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedRole()
    {
        $this->resetPage();
    }

    public function loadOutlets()
    {
        if (!$this->selectedBusiness) return;

        $this->availableOutlets = PosOutlet::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadUsers()
    {
        if (!$this->selectedBusiness) return;

        // Get staff members and users
        $this->availableUsers = User::where('role', 'staff')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->name . ' (' . $user->email . ')'];
            })
            ->toArray();
    }

    public function loadStats()
    {
        if (!$this->selectedBusiness) {
            $this->roleStats = [];
            return;
        }

        $stats = UserBusinessRole::where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        $this->roleStats = $stats;
    }

    public function openModal()
    {
        $this->resetForm();
        $this->business_id = $this->selectedBusiness;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->user_id = '';
        $this->business_id = $this->selectedBusiness;
        $this->outlet_id = '';
        $this->role = 'cashier';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $roleAssignment = UserBusinessRole::with(['user', 'outlet'])->find($id);
        if ($roleAssignment) {
            $this->editingId = $id;
            $this->user_id = $roleAssignment->user_id;
            $this->business_id = $roleAssignment->business_id;
            $this->outlet_id = $roleAssignment->outlet_id;
            $this->role = $roleAssignment->role;
            $this->is_active = $roleAssignment->is_active;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'business_id' => 'required|exists:businesses,id',
            'outlet_id' => 'nullable|exists:pos_outlets,id',
            'role' => 'required|in:owner,admin,manager,cashier,waiter,bartender,receptionist,housekeeping,kitchen,accountant,viewer',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $this->user_id,
                'business_id' => $this->business_id,
                'outlet_id' => $this->outlet_id ?: null,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ];

            if ($this->editingId) {
                $roleAssignment = UserBusinessRole::find($this->editingId);
                if ($roleAssignment) {
                    $roleAssignment->update($data);
                    session()->flash('message', 'User role updated successfully.');
                }
            } else {
                // Check for duplicate
                $exists = UserBusinessRole::where('user_id', $this->user_id)
                    ->where('business_id', $this->business_id)
                    ->where('outlet_id', $this->outlet_id)
                    ->where('role', $this->role)
                    ->exists();

                if ($exists) {
                    session()->flash('error', 'This user already has this role for the selected business/outlet.');
                    DB::rollBack();
                    return;
                }

                UserBusinessRole::create($data);
                session()->flash('message', 'User role assigned successfully.');
            }

            DB::commit();
            $this->closeModal();
            $this->loadStats();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving user role: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $roleAssignment = UserBusinessRole::find($id);
        if ($roleAssignment) {
            $roleAssignment->update([
                'is_active' => !$roleAssignment->is_active,
            ]);
            $this->loadStats();
            session()->flash('message', 'User role status updated.');
        }
    }

    public function delete($id)
    {
        try {
            $roleAssignment = UserBusinessRole::find($id);
            if ($roleAssignment) {
                $roleAssignment->delete();
                $this->loadStats();
                session()->flash('message', 'User role removed successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting user role: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $this->loadStats();

        $query = UserBusinessRole::with(['user', 'business', 'outlet'])
            ->where('business_id', $this->selectedBusiness);

        if ($this->selectedRole) {
            $query->where('role', $this->selectedRole);
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        $userRoles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.owner.staffs.userroles', [
            'userRoles' => $userRoles,
        ]);
    }
}
