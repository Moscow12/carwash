<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\MenuItemRecipe;
use App\Models\MenuItem;
use App\Models\items;
use App\Models\unit;

#[Layout('components.layouts.app-owner')]
class MenuRecipes extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $selectedMenuItem = null;
    public $showModal = false;
    public $editMode = false;

    // Recipe Properties
    public $recipeId = null;

    #[Rule('required|exists:items,id')]
    public $item_id = null;

    #[Rule('required|numeric|min:0')]
    public $quantity = 0;

    #[Rule('required|exists:units,id')]
    public $unit_id = null;

    #[Rule('nullable|boolean')]
    public $is_optional = false;

    #[Rule('nullable|string')]
    public $notes = '';

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            $outlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->first();

            if ($outlet) {
                $this->selectedOutlet = $outlet->id;
            }
        }
    }

    public function selectMenuItem($menuItemId)
    {
        $this->selectedMenuItem = $menuItemId;
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->recipeId = null;
        $this->item_id = null;
        $this->quantity = 0;
        $this->unit_id = null;
        $this->is_optional = false;
        $this->notes = '';
        $this->resetValidation();
    }

    public function saveRecipe()
    {
        $this->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
        ]);

        try {
            $data = [
                'menu_item_id' => $this->selectedMenuItem,
                'item_id' => $this->item_id,
                'quantity' => $this->quantity,
                'unit_id' => $this->unit_id,
                'is_optional' => $this->is_optional ?? false,
                'notes' => $this->notes,
            ];

            if ($this->editMode && $this->recipeId) {
                MenuItemRecipe::findOrFail($this->recipeId)->update($data);
                session()->flash('message', 'Recipe ingredient updated successfully.');
            } else {
                MenuItemRecipe::create($data);
                session()->flash('message', 'Recipe ingredient added successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editRecipe($id)
    {
        $recipe = MenuItemRecipe::findOrFail($id);

        $this->editMode = true;
        $this->recipeId = $recipe->id;
        $this->item_id = $recipe->item_id;
        $this->quantity = $recipe->quantity;
        $this->unit_id = $recipe->unit_id;
        $this->is_optional = $recipe->is_optional;
        $this->notes = $recipe->notes;

        $this->showModal = true;
    }

    public function deleteRecipe($id)
    {
        try {
            MenuItemRecipe::findOrFail($id)->delete();
            session()->flash('message', 'Recipe ingredient deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $menuItems = collect();
        $recipes = collect();
        $totalCost = 0;
        $stats = [
            'items_with_recipes' => 0,
            'total_ingredients' => 0,
            'avg_cost' => 0,
        ];

        if ($this->selectedOutlet) {
            // Get menu items
            $menuQuery = MenuItem::where('outlet_id', $this->selectedOutlet);

            if ($this->search) {
                $menuQuery->where('name', 'like', '%' . $this->search . '%');
            }

            $menuItems = $menuQuery->with('category')->paginate(10);

            // Get recipes for selected menu item
            if ($this->selectedMenuItem) {
                $recipes = MenuItemRecipe::where('menu_item_id', $this->selectedMenuItem)
                    ->with(['item', 'unit'])
                    ->get();

                // Calculate total cost
                foreach ($recipes as $recipe) {
                    if ($recipe->item && $recipe->item->cost_price) {
                        $totalCost += ($recipe->item->cost_price * $recipe->quantity);
                    }
                }
            }

            // Statistics
            $stats['items_with_recipes'] = MenuItem::where('outlet_id', $this->selectedOutlet)
                ->whereHas('recipes')
                ->count();

            $stats['total_ingredients'] = MenuItemRecipe::whereHas('menuItem', function($q) {
                $q->where('outlet_id', $this->selectedOutlet);
            })->count();

            if ($stats['items_with_recipes'] > 0) {
                $stats['avg_cost'] = MenuItemRecipe::whereHas('menuItem', function($q) {
                    $q->where('outlet_id', $this->selectedOutlet);
                })->count() / $stats['items_with_recipes'];
            }
        }

        // Get outlets for dropdown
        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->whereIn('type', ['restaurant', 'bar'])
                ->get();
        }

        // Get stock items for ingredients
        $stockItems = items::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->get();

        // Get units (units are global, not business-specific)
        $units = unit::where('status', 'active')->get();

        return view('livewire.owner.restaurant.menu-recipes', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'menuItems' => $menuItems,
            'recipes' => $recipes,
            'totalCost' => $totalCost,
            'stats' => $stats,
            'stockItems' => $stockItems,
            'units' => $units,
        ]);
    }
}
