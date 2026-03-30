<?php

namespace App\Livewire\Dashboard\Businesses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Business;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['owner', 'regions', 'districts', 'wards'])
            ->paginate(10);

        return view('livewire.dashboard.businesses.index', [
            'businesses' => $businesses
        ]);
    }
}
