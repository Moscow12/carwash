<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;

class About extends Component
{
    public function render()
    {
        return view('livewire.sitepg.about')
            ->layout('components.layouts.sitepg', ['title' => 'About Us - CAMS']);
    }
}
