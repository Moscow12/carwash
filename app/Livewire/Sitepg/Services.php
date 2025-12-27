<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;

class Services extends Component
{
    public function render()
    {
        return view('livewire.sitepg.services')
            ->layout('components.layouts.sitepg', ['title' => 'Our Services - CAMS']);
    }
}
