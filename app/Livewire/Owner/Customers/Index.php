<?php

namespace App\Livewire\Owner\Customers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\customers;

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
    public $phone = '';
    public $email = '';
    public $address = '';
    public $status = 'active';
    public $business_id = '';

    // Summary stats
    public $totalCustomers = 0;
    public $activeCustomers = 0;
    public $inactiveCustomers = 0;

    public function mount()
    {
        $firstBusiness = Auth::user()->assignedBusinesses()->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
            $this->business_id = $firstBusiness->id;
        }
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
            $this->totalCustomers = 0;
            $this->activeCustomers = 0;
            $this->inactiveCustomers = 0;
            return;
        }

        $baseQuery = customers::where('business_id', $this->selectedBusiness);
        $this->totalCustomers = (clone $baseQuery)->count();
        $this->activeCustomers = (clone $baseQuery)->where('status', 'active')->count();
        $this->inactiveCustomers = (clone $baseQuery)->where('status', 'inactive')->count();
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
        $this->phone = '';
        $this->email = '';
        $this->address = '';
        $this->status = 'active';
        $this->business_id = $this->selectedBusiness;
        $this->resetValidation();
    }

    public function edit($id)
    {
        $customer = customers::find($id);
        if ($customer) {
            $this->editingId = $id;
            $this->name = $customer->name;
            $this->phone = $customer->phone;
            $this->email = $customer->email ?? '';
            $this->address = $customer->address ?? '';
            $this->status = $customer->status;
            $this->business_id = $customer->business_id;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2',
            'phone' => 'required|min:10',
            'email' => 'nullable|email',
            'business_id' => 'required',
        ]);

        try {
            $data = [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email ?: null,
                'address' => $this->address ?: null,
                'status' => $this->status,
                'business_id' => $this->business_id,
                'user_id' => Auth::id(),
            ];

            if ($this->editingId) {
                $customer = customers::find($this->editingId);
                if ($customer) {
                    unset($data['user_id']); // Don't update user_id on edit
                    $customer->update($data);
                    session()->flash('message', 'Customer updated successfully.');
                }
            } else {
                customers::create($data);
                session()->flash('message', 'Customer added successfully.');
            }

            $this->closeModal();
            $this->loadStats();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving customer: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $customer = customers::find($id);
        if ($customer) {
            $customer->update([
                'status' => $customer->status === 'active' ? 'inactive' : 'active',
            ]);
            $this->loadStats();
            session()->flash('message', 'Customer status updated.');
        }
    }

    public function delete($id)
    {
        try {
            $customer = customers::find($id);
            if ($customer) {
                // Check if customer has sales
                if ($customer->sales()->count() > 0) {
                    session()->flash('error', 'Cannot delete customer with sales history. Deactivate instead.');
                    return;
                }
                $customer->delete();
                $this->loadStats();
                session()->flash('message', 'Customer deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete customer. They may have associated records.');
        }
    }

    public function render()
    {
        $this->loadStats();

        $businesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();

        $customers = customers::query()
            ->when($this->selectedBusiness, fn($q) => $q->where('business_id', $this->selectedBusiness))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->with('business')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.owner.customers.index', [
            'customers' => $customers,
            'businesses' => $businesses
        ]);
    }
}
