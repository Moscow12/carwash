<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\street;
use App\Models\wards;
use Livewire\Component;
use Livewire\WithPagination;

class Streets extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $streetId = null;

    public $name = '';
    public $street_number = '';
    public $ward_id = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|min:2',
        'street_number' => 'nullable',
        'ward_id' => 'required|exists:wards,id',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->streetId = null;
        $this->name = '';
        $this->street_number = '';
        $this->ward_id = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $street = street::findOrFail($id);
        $this->editMode = true;
        $this->streetId = $id;
        $this->name = $street->name;
        $this->street_number = $street->street_number;
        $this->ward_id = $street->ward_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'street_number' => $this->street_number,
            'ward_id' => $this->ward_id,
        ];

        if ($this->editMode) {
            street::find($this->streetId)->update($data);
            session()->flash('success', 'Street updated successfully.');
        } else {
            street::create($data);
            session()->flash('success', 'Street created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        street::find($id)->delete();
        session()->flash('success', 'Street deleted successfully.');
    }

    public function render()
    {
        $streets = street::with('ward')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        $wards = wards::orderBy('name')->get();

        return view('livewire.dashboard.locations.streets', compact('streets', 'wards'));
    }
}
