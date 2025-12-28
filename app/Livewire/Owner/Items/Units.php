<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\unit;

#[Layout('components.layouts.app-owner')]
class Units extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $unitId = null;

    // Form fields
    public $name = '';
    public $symbol = '';
    public $description = '';
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'symbol' => 'nullable|string|max:20',
        'description' => 'nullable|string|max:500',
        'status' => 'required|in:active,inactive',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name', 'symbol', 'description', 'status', 'unitId', 'editMode']);
        $this->status = 'active';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $unit = unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->symbol = $unit->symbol ?? '';
        $this->description = $unit->description ?? '';
        $this->status = $unit->status ?? 'active';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'symbol' => $this->symbol,
            'description' => $this->description,
            'status' => $this->status,
        ];

        if ($this->editMode && $this->unitId) {
            $unit = unit::findOrFail($this->unitId);
            $unit->update($data);
            session()->flash('success', 'Unit updated successfully!');
        } else {
            unit::create($data);
            session()->flash('success', 'Unit created successfully!');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->unitId = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->unitId = null;
    }

    public function delete()
    {
        if ($this->unitId) {
            $unit = unit::find($this->unitId);
            if ($unit) {
                $unit->delete();
                session()->flash('success', 'Unit deleted successfully!');
            }
        }
        $this->closeDeleteModal();
    }

    public function toggleStatus($id)
    {
        $unit = unit::findOrFail($id);
        $unit->update([
            'status' => $unit->status === 'active' ? 'inactive' : 'active'
        ]);
        session()->flash('success', 'Unit status updated!');
    }

    public function render()
    {
        $units = unit::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('symbol', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.owner.items.units', [
            'units' => $units
        ]);
    }
}
