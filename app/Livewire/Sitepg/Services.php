<?php

namespace App\Livewire\Sitepg;

use App\Support\ListingQuery;
use Livewire\Component;

class Services extends Component
{
    public function render()
    {
        // Published service-type listings from businesses on the platform.
        $services = ListingQuery::published(['listingType' => 'service'])->take(8);

        return view('livewire.sitepg.services', [
            'publishedServices' => $services,
        ])->layout('components.layouts.sitepg', ['title' => 'Our Services - CAMS']);
    }
}
