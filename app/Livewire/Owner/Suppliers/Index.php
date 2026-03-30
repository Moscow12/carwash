<?php

namespace App\Livewire\Owner\Suppliers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\suplier;
use App\Models\Business;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Business Selection
    public $selectedBusiness = null;
    public $ownerBusinesses = [];

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

    #[Rule('nullable|max:50')]
    public $tin_number = '';

    #[Rule('nullable|max:50')]
    public $vrn_number = '';

    #[Rule('nullable|max:100')]
    public $contact_person = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    // View modal data
    public $viewSupplier = null;

    public function mount()
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())->orderBy('name')->get();

        if ($this->ownerBusinesses->count() > 0) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectedBusiness()
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
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $supplier = suplier::where('business_id', $this->selectedBusiness)
            ->find($id);

        if (!$supplier) return;

        $this->supplierId = $id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->tin_number = $supplier->tin_number;
        $this->vrn_number = $supplier->vrn_number;
        $this->contact_person = $supplier->contact_person;
        $this->status = $supplier->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal($id)
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $this->viewSupplier = suplier::where('business_id', $this->selectedBusiness)
            ->withCount('purchases')
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
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $this->validate([
            'name' => 'required|min:2|max:255',
            'phone' => 'required|min:10|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|max:500',
            'tin_number' => 'nullable|max:50',
            'vrn_number' => 'nullable|max:50',
            'contact_person' => 'nullable|max:100',
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
                    'tin_number' => $this->tin_number ?: null,
                    'vrn_number' => $this->vrn_number ?: null,
                    'contact_person' => $this->contact_person ?: null,
                    'status' => $this->status,
                ]);
                session()->flash('message', 'Supplier updated successfully.');
            } else {
                suplier::create([
                    'business_id' => $this->selectedBusiness,
                    'name' => $this->name,
                    'phone' => $this->phone,
                    'email' => $this->email ?: null,
                    'address' => $this->address ?: null,
                    'tin_number' => $this->tin_number ?: null,
                    'vrn_number' => $this->vrn_number ?: null,
                    'contact_person' => $this->contact_person ?: null,
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
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $supplier = suplier::where('business_id', $this->selectedBusiness)
            ->find($id);

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
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $supplier = suplier::where('business_id', $this->selectedBusiness)
            ->find($id);

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
        $this->reset(['name', 'phone', 'email', 'address', 'tin_number', 'vrn_number', 'contact_person', 'supplierId', 'editMode']);
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        if (!$this->selectedBusiness) {
            // Return empty when no business selected
            $suppliers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $stats = ['total' => 0, 'active' => 0, 'inactive' => 0];
        } else {
            $suppliers = suplier::where('business_id', $this->selectedBusiness)
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

            // Stats filtered by selected business
            $stats = [
                'total' => suplier::where('business_id', $this->selectedBusiness)->count(),
                'active' => suplier::where('business_id', $this->selectedBusiness)->active()->count(),
                'inactive' => suplier::where('business_id', $this->selectedBusiness)->inactive()->count(),
            ];
        }

        return view('livewire.owner.suppliers.index', [
            'suppliers' => $suppliers,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
        ]);
    }
}
