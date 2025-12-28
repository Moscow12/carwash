<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\districts;
use App\Models\regions;
use Livewire\Component;
use Livewire\WithPagination;

class CDistricts extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $districtId = null;

    public $name = '';
    public $region_id = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|min:2',
        'region_id' => 'required|exists:regions,id',
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
        $this->districtId = null;
        $this->name = '';
        $this->region_id = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $district = districts::findOrFail($id);
        $this->editMode = true;
        $this->districtId = $id;
        $this->name = $district->name;
        $this->region_id = $district->region_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'region_id' => $this->region_id,
        ];

        if ($this->editMode) {
            districts::find($this->districtId)->update($data);
            session()->flash('success', 'District updated successfully.');
        } else {
            districts::create($data);
            session()->flash('success', 'District created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        districts::find($id)->delete();
        session()->flash('success', 'District deleted successfully.');
    }

    public function render()
    {
        $districts = districts::with('regions')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        $regions = regions::orderBy('name')->get();

        return view('livewire.dashboard.locations.districts', compact('districts', 'regions'));
    }
}
