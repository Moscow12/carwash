<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\items;
use App\Models\carwashes;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCarwash = '';

    public function mount()
    {
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');

        $items = items::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, function ($query) {
                $query->where('carwash_id', $this->selectedCarwash);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['carwash', 'unit'])
            ->paginate(10);

        $carwashes = Auth::user()->ownedCarwashes;

        return view('livewire.owner.items.index', [
            'items' => $items,
            'carwashes' => $carwashes
        ]);
    }
}
