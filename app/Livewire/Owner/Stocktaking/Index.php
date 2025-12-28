<?php

namespace App\Livewire\Owner\Stocktaking;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\stocktaking;

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

        $stocktakings = stocktaking::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, function ($query) {
                $query->where('carwash_id', $this->selectedCarwash);
            })
            ->with(['carwash', 'item', 'user'])
            ->latest()
            ->paginate(10);

        $carwashes = Auth::user()->ownedCarwashes;

        return view('livewire.owner.stocktaking.index', [
            'stocktakings' => $stocktakings,
            'carwashes' => $carwashes
        ]);
    }
}
