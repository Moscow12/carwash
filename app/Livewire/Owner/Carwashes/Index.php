<?php

namespace App\Livewire\Owner\Carwashes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\carwashes;

#[Layout('components.layouts.app-owner')]
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
        $carwashes = Auth::user()->ownedCarwashes()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['regions', 'districts', 'wards'])
            ->paginate(10);

        return view('livewire.owner.carwashes.index', [
            'carwashes' => $carwashes
        ]);
    }
}
