<?php

namespace App\Livewire\Customer\Businesses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Business;
use App\Models\regions;

#[Layout('components.layouts.app-customer')]
class Browse extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedRegion = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::where('status', 'active')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedRegion, function ($query) {
                $query->where('region_id', $this->selectedRegion);
            })
            ->with(['regions', 'districts', 'wards'])
            ->paginate(12);

        $regions = regions::where('status', 'active')->get();

        return view('livewire.customer.businesses.browse', [
            'businesses' => $businesses,
            'regions' => $regions
        ]);
    }
}
