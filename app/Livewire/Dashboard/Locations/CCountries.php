<?php

namespace App\Livewire\Dashboard\Locations;

use App\Models\countries;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

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

    public function syncFromAPI()
    {
        try {
            $response = Http::get('https://countriesnow.space/api/v0.1/countries/codes');

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data'])) {
                    $updated = 0;
                    $created = 0;

                    foreach ($data['data'] as $countryData) {
                        $name = $countryData['name'] ?? null;
                        $code = $countryData['code'] ?? null;
                        $dial_code = $countryData['dial_code'] ?? null;

                        if (!$name || !$code) {
                            continue;
                        }

                        // Extract shortcode from dial_code (e.g., "+255" -> "255")
                        $shortcode = $dial_code ? ltrim($dial_code, '+') : substr($code, 0, 2);

                        $country = countries::where('code', $code)->first();

                        if ($country) {
                            // Update if any field is missing or different
                            $needsUpdate = false;
                            if (!$country->name || $country->name !== $name) {
                                $needsUpdate = true;
                            }
                            if (!$country->shortcode || $country->shortcode !== $shortcode) {
                                $needsUpdate = true;
                            }

                            if ($needsUpdate) {
                                $country->update([
                                    'name' => $name,
                                    'shortcode' => $shortcode,
                                ]);
                                $updated++;
                            }
                        } else {
                            // Create new country
                            countries::create([
                                'name' => $name,
                                'code' => $code,
                                'shortcode' => $shortcode,
                            ]);
                            $created++;
                        }
                    }

                    session()->flash('success', "API Sync completed! Created: {$created}, Updated: {$updated}");
                } else {
                    session()->flash('error', 'Invalid API response format.');
                }
            } else {
                session()->flash('error', 'Failed to fetch data from API.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $countries = countries::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.dashboard.locations.countries', compact('countries'));
    }
}
