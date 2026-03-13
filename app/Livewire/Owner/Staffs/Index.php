<?php

namespace App\Livewire\Owner\Staffs;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\staffs;

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

    // Summary stats
    public $totalStaff = 0;
    public $activeStaff = 0;
    public $inactiveStaff = 0;

    public function mount()
    {
        $firstBusiness = Auth::user()->ownedBusinesses()->first();
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
        $this->resetValidation();
    }

    public function edit($id)
    {
        $staff = staffs::find($id);
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
            'payment_mode' => 'required|in:salary,hourly,commission',
            'commission_type' => 'required|in:fixed,percentage',
        ]);

        try {
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
                    $staff->update($data);
                    session()->flash('message', 'Staff updated successfully.');
                }
            } else {
                staffs::create($data);
                session()->flash('message', 'Staff added successfully.');
            }

            $this->closeModal();
            $this->loadStats();
        } catch (\Exception $e) {
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

        $businesses = Auth::user()->ownedBusinesses()->orderBy('name')->pluck('name', 'id');

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
            ->with('business')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.owner.staffs.index', [
            'staffs' => $staffs,
            'businesses' => $businesses
        ]);
    }
}
