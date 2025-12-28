<?php

namespace App\Livewire\Owner\Purchases;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\purchase;

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

        $purchases = purchase::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, function ($query) {
                $query->where('carwash_id', $this->selectedCarwash);
            })
            ->with(['carwash', 'item', 'supplier', 'user'])
            ->latest()
            ->paginate(10);

        $carwashes = Auth::user()->ownedCarwashes;

        return view('livewire.owner.purchases.index', [
            'purchases' => $purchases,
            'carwashes' => $carwashes
        ]);
    }
}
