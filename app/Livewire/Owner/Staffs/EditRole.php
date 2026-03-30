<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

#[Layout('components.layouts.app-owner')]
class EditRole extends Component
{
    public $roleId;
    public $name = '';
    public $display_name = '';
    public $description = '';
    public $business_id = '';
    public $selectedPermissions = [];

    public $permissionsByCategory = [];
    public $ownerBusinesses = [];
    public $role;

    public function mount($roleId)
    {
        $this->roleId = $roleId;
        $this->role = Role::with('permissions')->findOrFail($roleId);

        if ($this->role->is_system) {
            session()->flash('error', 'System roles cannot be edited.');
            return redirect()->route('owner.people.roles');
        }

        $this->name = $this->role->name;
        $this->display_name = $this->role->display_name;
        $this->description = $this->role->description;
        $this->business_id = $this->role->business_id;
        $this->selectedPermissions = $this->role->permissions->pluck('id')->toArray();

        $this->ownerBusinesses = Auth::user()->assignedBusinesses()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

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
            'name' => 'required|alpha_dash|unique:roles,name,' . $this->roleId,
            'display_name' => 'required|min:2',
            'business_id' => 'nullable|exists:businesses,id',
        ]);

        try {
            DB::beginTransaction();

            $this->role->update([
                'name' => $this->name,
                'display_name' => $this->display_name,
                'description' => $this->description,
                'business_id' => $this->business_id ?: null,
            ]);

            // Sync permissions
            $this->role->permissions()->sync($this->selectedPermissions);

            DB::commit();

            session()->flash('message', 'Role updated successfully.');
            return redirect()->route('owner.people.roles');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating role: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('owner.people.roles');
    }

    public function render()
    {
        return view('livewire.owner.staffs.edit-role');
    }
}
