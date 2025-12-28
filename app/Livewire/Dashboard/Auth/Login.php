<?php

namespace App\Livewire\Dashboard\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public function render()
    {
        return view('livewire.dashboard.auth.login');
    }
}
