<?php

namespace App\Livewire\Sitepg;

use App\Models\Business;
use App\Support\ListingQuery;
use Livewire\Component;

class ShopDetails extends Component
{
    // Store only the id in Livewire state; the model is re-resolved each render.
    public string $businessId = '';

    public function mount(string $business): void
    {
        // Only active businesses have a public storefront.
        $model = Business::query()
            ->where('status', 'active')
            ->findOrFail($business);

        $this->businessId = $model->id;
    }

    public function render()
    {
        $business = Business::with(['regions', 'districts', 'wards', 'streets'])
            ->findOrFail($this->businessId);

        $cards = ListingQuery::published([], $business->id);

        // Group by type for sectioned display (Shop / Rental / Hotel Room / Menu / Services).
        $grouped = $cards->groupBy('type_label');

        return view('livewire.sitepg.shop-details', [
            'business' => $business,
            'grouped' => $grouped,
            'total' => $cards->count(),
        ])->layout('components.layouts.sitepg', ['title' => $business->name . ' - CAMS']);
    }
}
