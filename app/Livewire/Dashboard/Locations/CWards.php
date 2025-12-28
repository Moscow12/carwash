<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\wards;
use App\Models\districts;
use Livewire\Component;
use Livewire\WithPagination;

class CWards extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $wardId = null;

    public $name = '';
    public $district_id = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|min:2',
        'district_id' => 'required|exists:districts,id',
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
        $this->wardId = null;
        $this->name = '';
        $this->district_id = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $ward = wards::findOrFail($id);
        $this->editMode = true;
        $this->wardId = $id;
        $this->name = $ward->name;
        $this->district_id = $ward->district_id;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'district_id' => $this->district_id,
        ];

        if ($this->editMode) {
            wards::find($this->wardId)->update($data);
            session()->flash('success', 'Ward updated successfully.');
        } else {
            wards::create($data);
            session()->flash('success', 'Ward created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        wards::find($id)->delete();
        session()->flash('success', 'Ward deleted successfully.');
    }

    public function render()
    {
        $wards = wards::with('districts')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        $districts = districts::orderBy('name')->get();

        return view('livewire.dashboard.locations.wards', compact('wards', 'districts'));
    }
}
