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
use Illuminate\Validation\Rule;

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

    /**
     * Businesses the signed-in owner manages.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function businessIds(): \Illuminate\Support\Collection
    {
        return Auth::user()->assignedBusinesses()->pluck('id');
    }

    /**
     * Users who belong to a business: staff of that business with a login, or anyone already holding a role there.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function relatedUserIds(string $businessId): \Illuminate\Support\Collection
    {
        return staffs::where('business_id', $businessId)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->merge(UserBusinessRole::where('business_id', $businessId)->pluck('user_id'))
            ->unique()
            ->values();
    }

    public function loadUsers()
    {
        if (!$this->selectedBusiness || !$this->businessIds()->contains($this->selectedBusiness)) {
            $this->availableUsers = [];
            return;
        }

        // Only this business's people, never every staff login in the system
        $this->availableUsers = User::whereIn('id', $this->relatedUserIds($this->selectedBusiness))
            ->where('role', 'staff')
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) {
                return [$user->id => $user->name . ' (' . $user->email . ')'];
            })
            ->toArray();
    }

    /**
     * A role assignment in one of the owner's businesses, or null.
     */
    protected function findAssignment($id): ?UserBusinessRole
    {
        return UserBusinessRole::whereKey($id)->whereIn('business_id', $this->businessIds())->first();
    }

    public function loadStats()
    {
        if (!$this->selectedBusiness || !$this->businessIds()->contains($this->selectedBusiness)) {
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
        $this->loadUsers();
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
        $roleAssignment = $this->findAssignment($id)?->load(['user', 'outlet']);
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
        $existing = $this->editingId ? $this->findAssignment($this->editingId) : null;

        if ($this->editingId && ! $existing) {
            session()->flash('error', 'Role assignment not found.');
            $this->closeModal();
            return;
        }

        // The business can't be changed in the form; always use the one being managed
        $this->business_id = $existing?->business_id ?? $this->selectedBusiness;

        $this->validate([
            'business_id' => ['required', Rule::in($this->businessIds()->all())],
            'user_id' => ['required', Rule::in($existing ? [$existing->user_id] : $this->relatedUserIds((string) $this->business_id)->all())],
            'outlet_id' => ['nullable', Rule::exists('pos_outlets', 'id')->where('business_id', $this->business_id)],
            'role' => 'required|in:owner,admin,manager,cashier,waiter,bartender,receptionist,housekeeping,kitchen,accountant,viewer',
        ], [
            'business_id.in' => 'Choose one of your businesses.',
            'user_id.in' => 'Choose someone who works at this business. Add them on the Staff page with a login first.',
            'outlet_id.exists' => 'Choose an outlet of this business.',
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
                $roleAssignment = $existing;
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
        $roleAssignment = $this->findAssignment($id);
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
            $roleAssignment = $this->findAssignment($id);
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

        // Never list another tenant's assignments, even if selectedBusiness is tampered with
        $businessId = $this->businessIds()->contains($this->selectedBusiness) ? $this->selectedBusiness : null;

        $query = UserBusinessRole::with(['user', 'business', 'outlet'])
            ->where('business_id', $businessId);

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
