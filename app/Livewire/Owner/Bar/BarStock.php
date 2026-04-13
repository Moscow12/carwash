<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\MenuItem;
use App\Models\items;

#[Layout('components.layouts.app-owner')]
class BarStock extends Component
{
    use WithPagination;

    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $search = '';
    public $stockFilter = 'all'; // all, low, out

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            // Get first bar outlet
            $barOutlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->first();

            if ($barOutlet) {
                $this->selectedOutlet = $barOutlet->id;
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStockFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $stockItems = collect();
        $stats = [
            'total_items' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'total_value' => 0,
        ];

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        if ($this->selectedOutlet) {
            // Get bar menu items with their linked stock items
            $query = MenuItem::where('outlet_id', $this->selectedOutlet)
                ->whereNotNull('item_id')
                ->with(['item.unit', 'category']);

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('item', function($subQ) {
                          $subQ->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            $stockItems = $query->get()->map(function($menuItem) {
                $item = $menuItem->item;
                if (!$item) return null;

                $currentStock = $item->qty ?? 0;
                $reorderLevel = $item->reorder_level ?? 10;

                $status = 'ok';
                if ($currentStock <= 0) {
                    $status = 'out';
                } elseif ($currentStock <= $reorderLevel) {
                    $status = 'low';
                }

                return (object) [
                    'menu_item_id' => $menuItem->id,
                    'menu_item_name' => $menuItem->name,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'category' => $menuItem->category ?->name,
                    'current_stock' => $currentStock,
                    'unit' => $item->unit?->name ?? 'pcs',
                    'reorder_level' => $reorderLevel,
                    'cost_price' => $item->purchase_price ?? 0,
                    'selling_price' => $menuItem->price,
                    'status' => $status,
                    'stock_value' => $currentStock * ($item->purchase_price ?? 0),
                ];
            })->filter();

            // Apply stock filter
            if ($this->stockFilter === 'low') {
                $stockItems = $stockItems->where('status', 'low');
            } elseif ($this->stockFilter === 'out') {
                $stockItems = $stockItems->where('status', 'out');
            }

            // Calculate statistics
            $allItems = MenuItem::where('outlet_id', $this->selectedOutlet)
                ->whereNotNull('item_id')
                ->with('item')
                ->get();

            $stats['total_items'] = $allItems->count();
            $stats['low_stock'] = $allItems->filter(function($mi) {
                $item = $mi->item;
                return $item && $item->qty > 0 && $item->qty <= ($item->reorder_level ?? 10);
            })->count();
            $stats['out_of_stock'] = $allItems->filter(function($mi) {
                $item = $mi->item;
                return $item && $item->qty <= 0;
            })->count();
            $stats['total_value'] = $allItems->sum(function($mi) {
                $item = $mi->item;
                return $item ? ($item->qty * ($item->purchase_price ?? 0)) : 0;
            });

            // Paginate manually
            $page = $this->getPage();
            $perPage = 20;
            $stockItems = collect($stockItems->forPage($page, $perPage)->values());
        }

        return view('livewire.owner.bar.bar-stock', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'stockItems' => $stockItems,
            'stats' => $stats,
        ]);
    }
}
