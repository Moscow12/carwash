<?php

namespace App\Livewire\Owner\Suppliers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use App\Models\suplier;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $statusFilter = '';

    // Modal states
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $supplierId = null;

    // Form fields
    #[Rule('required|min:2|max:255')]
    public $name = '';

    #[Rule('required|min:10|max:20')]
    public $phone = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('nullable|max:500')]
    public $address = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    // View modal data
    public $viewSupplier = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // Modal actions
    public function openAddModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $supplier = suplier::find($id);
        if (!$supplier) return;

        $this->supplierId = $id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->status = $supplier->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal($id)
    {
        $this->viewSupplier = suplier::withCount('purchases')
            ->find($id);

        if ($this->viewSupplier) {
            $this->showViewModal = true;
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewSupplier = null;
    }

    public function saveSupplier()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'phone' => 'required|min:10|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|max:500',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            if ($this->editMode && $this->supplierId) {
                $supplier = suplier::find($this->supplierId);
                $supplier->update([
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email ?: null,
                    'address' => $this->address ?: null,
                    'status' => $this->status,
                ]);
                session()->flash('message', 'Supplier updated successfully.');
            } else {
                suplier::create([
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email ?: null,
                    'address' => $this->address ?: null,
                    'status' => $this->status,
                ]);
                session()->flash('message', 'Supplier created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving supplier: ' . $e->getMessage());
        }
    }

    public function deleteSupplier($id)
    {
        $supplier = suplier::find($id);
        if (!$supplier) return;

        // Check if supplier has purchases
        if ($supplier->purchases()->count() > 0) {
            session()->flash('error', 'Cannot delete supplier with existing purchases. Deactivate instead.');
            return;
        }

        try {
            $supplier->delete();
            session()->flash('message', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting supplier.');
        }
    }

    public function toggleStatus($id)
    {
        $supplier = suplier::find($id);
        if (!$supplier) return;

        $supplier->update([
            'status' => $supplier->status === 'active' ? 'inactive' : 'active'
        ]);

        session()->flash('message', 'Supplier status updated.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'phone', 'email', 'address', 'supplierId', 'editMode']);
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $suppliers = suplier::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->withCount('purchases')
            ->latest()
            ->paginate(10);

        // Stats
        $stats = [
            'total' => suplier::count(),
            'active' => suplier::active()->count(),
            'inactive' => suplier::inactive()->count(),
        ];

        return view('livewire.owner.suppliers.index', [
            'suppliers' => $suppliers,
            'stats' => $stats,
        ]);
    }
}
