<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\items;
use App\Models\Business;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = '';

    public function mount()
    {
        $firstBusiness = Auth::user()->ownedBusinesses()->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $businessIds = Auth::user()->ownedBusinesses()->pluck('id');

        $items = items::whereIn('business_id', $businessIds)
            ->when($this->selectedBusiness, function ($query) {
                $query->where('business_id', $this->selectedBusiness);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['business', 'unit'])
            ->paginate(10);

        $businesses = Auth::user()->ownedBusinesses;

        return view('livewire.owner.items.index', [
            'items' => $items,
            'businesses' => $businesses
        ]);
    }
}
