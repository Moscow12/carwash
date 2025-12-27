<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.sitepg.home')
            ->layout('components.layouts.sitepg', ['title' => 'Home - CAMS']);
    }
}
