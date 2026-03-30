<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\staffs;
use App\Models\User;
use App\Models\Role;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = '';
    public $statusFilter = '';

    // Modal state
    public $showModal = false;
    public $editingId = null;

    // Form fields
    public $name = '';
    public $position = '';
    public $phone = '';
    public $email = '';
    public $payment_mode = 'commission';
    public $commission_type = 'fixed';
    public $amount = '';
    public $status = 'active';
    public $business_id = '';

    // User account fields
    public $canLogin = false;
    public $password = '';
    public $selectedRoleId = '';

    // Available roles for dropdown
    public $availableRoles = [];

    // Summary stats
    public $totalStaff = 0;
    public $activeStaff = 0;
    public $inactiveStaff = 0;

    public function mount()
    {
        $firstBusiness = Auth::user()->assignedBusinesses()->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
            $this->business_id = $firstBusiness->id;
        }

        // Load available roles (system-wide and business-specific)
        $this->loadAvailableRoles();
    }

    public function loadAvailableRoles()
    {
        $user = Auth::user();
        $businessIds = $user->assignedBusinesses()->pluck('id')->toArray();

        $this->availableRoles = Role::where('is_active', true)
            ->where(function($q) use ($businessIds) {
                $q->whereNull('business_id') // System-wide roles
                  ->orWhereIn('business_id', $businessIds); // Business-specific roles
            })
            ->orderBy('display_name')
            ->pluck('display_name', 'id')
            ->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedBusiness()
    {
        $this->resetPage();
        $this->business_id = $this->selectedBusiness;
        $this->loadStats();
    }

    public function loadStats()
    {
        if (!$this->selectedBusiness) {
            $this->totalStaff = 0;
            $this->activeStaff = 0;
            $this->inactiveStaff = 0;
            return;
        }

        $baseQuery = staffs::where('business_id', $this->selectedBusiness);
        $this->totalStaff = (clone $baseQuery)->count();
        $this->activeStaff = (clone $baseQuery)->where('status', 'active')->count();
        $this->inactiveStaff = (clone $baseQuery)->where('status', 'inactive')->count();
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
        $this->name = '';
        $this->position = '';
        $this->phone = '';
        $this->email = '';
        $this->payment_mode = 'commission';
        $this->commission_type = 'fixed';
        $this->amount = '';
        $this->status = 'active';
        $this->business_id = $this->selectedBusiness;
        $this->canLogin = false;
        $this->password = '';
        $this->selectedRoleId = '';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $staff = staffs::with('user')->find($id);
        if ($staff) {
            $this->editingId = $id;
            $this->name = $staff->name;
            $this->position = $staff->position ?? '';
            $this->phone = $staff->phone;
            $this->email = $staff->email ?? '';
            $this->payment_mode = $staff->payment_mode ?? 'commission';
            $this->commission_type = $staff->commission_type ?? 'fixed';
            $this->amount = $staff->amount ?? '';
            $this->status = $staff->status;
            $this->business_id = $staff->business_id;

            // Load user account info if exists
            if ($staff->user) {
                $this->canLogin = true;
                $this->selectedRoleId = $staff->user->role_id ?? '';
                $this->password = ''; // Don't load password
            }

            $this->showModal = true;
        }
    }

    public function save()
    {
        // Build validation rules
        $rules = [
            'name' => 'required|min:2',
            'phone' => 'required|min:10',
            'email' => $this->canLogin ? 'required|email' : 'nullable|email',
            'business_id' => 'required',
            'payment_mode' => 'required|in:salary,hourly,commission',
            'commission_type' => 'required|in:fixed,percentage',
        ];

        // Add user account validation if canLogin is enabled
        if ($this->canLogin) {
            $rules['selectedRoleId'] = 'required|exists:roles,id';

            // Check if this is a new user account (creating staff or enabling login for existing staff without user)
            $isNewUserAccount = !$this->editingId;
            if ($this->editingId) {
                $existingStaff = staffs::find($this->editingId);
                $isNewUserAccount = !$existingStaff || !$existingStaff->user_id;
            }

            // Password is required for new user accounts, optional for existing ones
            if ($isNewUserAccount) {
                $rules['password'] = 'required|min:8';
            } elseif (!empty($this->password)) {
                $rules['password'] = 'min:8';
            }
        }

        $this->validate($rules);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $this->name,
                'position' => $this->position ?: null,
                'phone' => $this->phone,
                'email' => $this->email ?: null,
                'payment_mode' => $this->payment_mode,
                'commission_type' => $this->commission_type,
                'amount' => $this->amount ?: null,
                'status' => $this->status,
                'business_id' => $this->business_id,
            ];

            if ($this->editingId) {
                $staff = staffs::find($this->editingId);
                if ($staff) {
                    // Handle user account for existing staff
                    if ($this->canLogin) {
                        $user = $staff->user;

                        if ($user) {
                            // Update existing user
                            $userData = [
                                'name' => $this->name,
                                'email' => $this->email,
                                'phone' => $this->phone,
                                'role' => 'staff',
                                'role_id' => $this->selectedRoleId,
                            ];

                            // Only update password if provided
                            if (!empty($this->password)) {
                                $userData['password'] = Hash::make($this->password);
                            }

                            $user->update($userData);
                        } else {
                            // Create new user for existing staff
                            $user = User::create([
                                'name' => $this->name,
                                'email' => $this->email,
                                'phone' => $this->phone,
                                'password' => Hash::make($this->password),
                                'role' => 'staff',
                                'role_id' => $this->selectedRoleId,
                                'status' => 'active',
                            ]);

                            $data['user_id'] = $user->id;
                        }
                    } else {
                        // If canLogin is disabled and user exists, optionally deactivate user
                        if ($staff->user) {
                            $staff->user->update(['status' => 'inactive']);
                        }
                    }

                    $staff->update($data);
                    session()->flash('message', 'Staff updated successfully.');
                }
            } else {
                // Creating new staff
                $userId = null;

                if ($this->canLogin) {
                    // Create user account
                    $user = User::create([
                        'name' => $this->name,
                        'email' => $this->email,
                        'phone' => $this->phone,
                        'password' => Hash::make($this->password),
                        'role' => 'staff',
                        'role_id' => $this->selectedRoleId,
                        'status' => 'active',
                    ]);

                    $userId = $user->id;
                }

                $data['user_id'] = $userId;
                staffs::create($data);
                session()->flash('message', 'Staff added successfully.');
            }

            DB::commit();
            $this->closeModal();
            $this->loadStats();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            // Check if it's a unique constraint violation for email
            if ($e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'users_email_unique')) {
                session()->flash('error', 'This email is already registered to another user account. Please use a different email.');
            } else {
                session()->flash('error', 'Database error: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving staff: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $staff = staffs::find($id);
        if ($staff) {
            $staff->update([
                'status' => $staff->status === 'active' ? 'inactive' : 'active',
            ]);
            $this->loadStats();
            session()->flash('message', 'Staff status updated.');
        }
    }

    public function delete($id)
    {
        try {
            $staff = staffs::find($id);
            if ($staff) {
                // Check if staff has sales
                if ($staff->salesItems()->count() > 0) {
                    session()->flash('error', 'Cannot delete staff with sales history. Deactivate instead.');
                    return;
                }
                $staff->delete();
                $this->loadStats();
                session()->flash('message', 'Staff deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete staff. They may have associated records.');
        }
    }

    public function render()
    {
        $this->loadStats();

        $businesses = Auth::user()->assignedBusinesses()->orderBy('name')->pluck('name', 'id');

        $staffs = staffs::query()
            ->when($this->selectedBusiness, fn($q) => $q->where('business_id', $this->selectedBusiness))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('position', 'like', "%{$this->search}%");
                });
            })
            ->with(['business', 'user.assignedRole'])
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.owner.staffs.index', [
            'staffs' => $staffs,
            'businesses' => $businesses
        ]);
    }
}
