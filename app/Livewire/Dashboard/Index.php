<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Models\User;
use App\Models\countries;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use App\Models\street;

class Index extends Component
{
    public function render()
    {
        return view('livewire.dashboard.index', [
            'usersCount' => User::count(),
            'countriesCount' => countries::count(),
            'regionsCount' => regions::count(),
            'districtsCount' => districts::count(),
            'wardsCount' => wards::count(),
            'streetsCount' => street::count(),
        ]);
    }
}
