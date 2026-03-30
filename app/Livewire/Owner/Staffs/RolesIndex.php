<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

#[Layout('components.layouts.app-owner')]
class RolesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                session()->flash('error', 'Role not found.');
                return;
            }

            if ($role->is_system) {
                session()->flash('error', 'Cannot delete system roles.');
                return;
            }

            $role->delete();
            session()->flash('message', 'Role deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting role: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $role = Role::find($id);
        if ($role) {
            $role->update([
                'is_active' => !$role->is_active,
            ]);
            session()->flash('message', 'Role status updated.');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Role::with(['permissions', 'business'])
            ->where(function($q) use ($user) {
                // Show system-wide roles or roles for businesses owned by user
                $q->whereNull('business_id')
                  ->orWhereIn('business_id', $user->ownedBusinesses()->pluck('id'));
            });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('display_name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.owner.staffs.roles-index', [
            'roles' => $roles,
        ]);
    }
}
