<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\regions;
use App\Models\countries;
use Livewire\Component;
use Livewire\WithPagination;

class CRegions extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $regionId = null;

    public $name = '';
    public $country_id = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|min:2',
        'country_id' => 'required|exists:countries,id',
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
        $this->regionId = null;
        $this->name = '';
        $this->country_id = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $region = regions::findOrFail($id);
        $this->editMode = true;
        $this->regionId = $id;
        $this->name = $region->name;
        $this->country_id = $region->country_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'country_id' => $this->country_id,
        ];

        if ($this->editMode) {
            regions::find($this->regionId)->update($data);
            session()->flash('success', 'Region updated successfully.');
        } else {
            regions::create($data);
            session()->flash('success', 'Region created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        regions::find($id)->delete();
        session()->flash('success', 'Region deleted successfully.');
    }

    public function render()
    {
        $regions = regions::with('country')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        $countries = countries::orderBy('name')->get();

        return view('livewire.dashboard.locations.regions', compact('regions', 'countries'));
    }
}
