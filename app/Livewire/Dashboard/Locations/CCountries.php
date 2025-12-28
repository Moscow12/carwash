<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\countries;
use Livewire\Component;
use Livewire\WithPagination;

class CCountries extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $countryId = null;

    public $name = '';
    public $code = '';
    public $shortcode = '';

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'name' => 'required|min:2',
        'code' => 'required|min:2|max:10',
        'shortcode' => 'required|min:2|max:5',
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
        $this->countryId = null;
        $this->name = '';
        $this->code = '';
        $this->shortcode = '';
        $this->resetErrorBag();
    }

    public function edit($id)
    {
        $country = countries::findOrFail($id);
        $this->editMode = true;
        $this->countryId = $id;
        $this->name = $country->name;
        $this->code = $country->code;
        $this->shortcode = $country->shortcode;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'shortcode' => $this->shortcode,
        ];

        if ($this->editMode) {
            countries::find($this->countryId)->update($data);
            session()->flash('success', 'Country updated successfully.');
        } else {
            countries::create($data);
            session()->flash('success', 'Country created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        countries::find($id)->delete();
        session()->flash('success', 'Country deleted successfully.');
    }

    public function render()
    {
        $countries = countries::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.dashboard.locations.countries', compact('countries'));
    }
}
