<?php

namespace App\Livewire\Sitepg;

use App\Support\ListingQuery;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        // A handful of published listings to feature on the landing page.
        $featured = ListingQuery::published()->take(8);

        return view('livewire.sitepg.home', [
            'featured' => $featured,
        ])->layout('components.layouts.sitepg', ['title' => 'Home - CAMS']);
    }
}
