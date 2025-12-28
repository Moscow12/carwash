<?php

namespace App\Livewire\Owner\Customers;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\customers;

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

        $customers = customers::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, function ($query) {
                $query->where('carwash_id', $this->selectedCarwash);
            })
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->with('carwash')
            ->paginate(10);

        $carwashes = Auth::user()->ownedCarwashes;

        return view('livewire.owner.customers.index', [
            'customers' => $customers,
            'carwashes' => $carwashes
        ]);
    }
}
