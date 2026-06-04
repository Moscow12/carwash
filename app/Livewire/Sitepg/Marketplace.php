<?php

namespace App\Livewire\Sitepg;

use App\Models\Business;
use App\Models\category;
use App\Support\ListingQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;
use Livewire\WithPagination;

class Marketplace extends Component
{
    use WithPagination;

    public string $search = '';
    public string $businessType = '';
    public string $listingType = '';
    public string $category = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingBusinessType(): void { $this->resetPage(); }
    public function updatingListingType(): void { $this->resetPage(); }
    public function updatingCategory(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset(['search', 'businessType', 'listingType', 'category']);
        $this->resetPage();
    }

    public function render()
    {
        $cards = ListingQuery::published([
            'search' => $this->search,
            'businessType' => $this->businessType,
            'listingType' => $this->listingType,
            'category' => $this->category,
        ]);

        // Manual pagination over the merged, heterogeneous collection.
        $perPage = 12;
        $page = Paginator::resolveCurrentPage('page');
        $listings = new LengthAwarePaginator(
            $cards->forPage($page, $perPage)->values(),
            $cards->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        // Filter dropdown sources.
        $businessTypes = Business::query()
            ->where('status', 'active')
            ->select('type')->distinct()->orderBy('type')->pluck('type')->filter()->values();

        $categories = category::query()
            ->where('status', 'active')
            ->orderBy('name')->get(['id', 'name']);

        return view('livewire.sitepg.marketplace', [
            'listings' => $listings,
            'businessTypes' => $businessTypes,
            'categories' => $categories,
            'listingTypes' => ListingQuery::TYPES,
        ])->layout('components.layouts.sitepg', ['title' => 'Marketplace - CAMS']);
    }
}
