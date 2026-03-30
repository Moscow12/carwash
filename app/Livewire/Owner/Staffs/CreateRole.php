<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

#[Layout('components.layouts.app-owner')]
class CreateRole extends Component
{
    public $name = '';
    public $display_name = '';
    public $description = '';
    public $business_id = '';
    public $selectedPermissions = [];

    public $permissionsByCategory = [];
    public $ownerBusinesses = [];

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->business_id = array_key_first($this->ownerBusinesses);
        }

        $this->loadPermissions();
    }

    public function loadPermissions()
    {
        $permissions = Permission::ordered()->get();
        $grouped = $permissions->groupBy('category');

        // Convert to array format that Livewire can serialize
        $this->permissionsByCategory = $grouped->map(function($categoryPerms) {
            return $categoryPerms->map(function($perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name,
                    'display_name' => $perm->display_name,
                ];
            })->toArray();
        })->toArray();
    }

    public function toggleCategoryPermissions($category)
    {
        $categoryPermissions = collect($this->permissionsByCategory[$category])->pluck('id')->toArray();

        // Check if all permissions in this category are selected
        $allSelected = count(array_intersect($categoryPermissions, $this->selectedPermissions)) === count($categoryPermissions);

        if ($allSelected) {
            // Deselect all
            $this->selectedPermissions = array_diff($this->selectedPermissions, $categoryPermissions);
        } else {
            // Select all
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $categoryPermissions));
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|unique:roles,name|alpha_dash',
            'display_name' => 'required|min:2',
            'business_id' => 'nullable|exists:businesses,id',
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'business_id' => $this->business_id ?: null,
                'is_system' => false,
                'is_active' => true,
            ]);

            // Attach permissions
            if (!empty($this->selectedPermissions)) {
                $role->permissions()->attach($this->selectedPermissions);
            }

            DB::commit();

            session()->flash('message', 'Role created successfully.');
            return redirect()->route('owner.people.roles');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating role: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('owner.people.roles');
    }

    public function render()
    {
        return view('livewire.owner.staffs.create-role');
    }
}
