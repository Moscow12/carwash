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
    public $selectedCarwash = '';
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
    public $carwash_id = '';

    // Summary stats
    public $totalStaff = 0;
    public $activeStaff = 0;
    public $inactiveStaff = 0;

    public function mount()
    {
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
            $this->carwash_id = $firstCarwash->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCarwash()
    {
        $this->resetPage();
        $this->carwash_id = $this->selectedCarwash;
        $this->loadStats();
    }

    public function loadStats()
    {
        if (!$this->selectedCarwash) {
            $this->totalStaff = 0;
            $this->activeStaff = 0;
            $this->inactiveStaff = 0;
            return;
        }

        $baseQuery = staffs::where('carwash_id', $this->selectedCarwash);
        $this->totalStaff = (clone $baseQuery)->count();
        $this->activeStaff = (clone $baseQuery)->where('status', 'active')->count();
        $this->inactiveStaff = (clone $baseQuery)->where('status', 'inactive')->count();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->carwash_id = $this->selectedCarwash;
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
        $this->carwash_id = $this->selectedCarwash;
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
            $this->carwash_id = $staff->carwash_id;
            $this->showModal = true;
        }
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|min:2',
            'phone' => 'required|min:10',
            'email' => 'nullable|email',
            'carwash_id' => 'required',
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
                'carwash_id' => $this->carwash_id,
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

        $carwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $staffs = staffs::query()
            ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('position', 'like', "%{$this->search}%");
                });
            })
            ->with('carwash')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.owner.staffs.index', [
            'staffs' => $staffs,
            'carwashes' => $carwashes
        ]);
    }
}
