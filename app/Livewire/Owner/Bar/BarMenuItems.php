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
use App\Models\MenuCategory;
use App\Models\items;
use App\Models\PosOrderItem;

#[Layout('components.layouts.app-owner')]
class BarMenuItems extends Component
{
    use WithPagination;

    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $search = '';
    public $selectedCategory = '';

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showLinkStockModal = false;
    public $showCreateStockModal = false;
    public $showUpdateStockModal = false;
    public $editingItem = null;
    public $deletingItem = null;
    public $linkingItem = null;
    public $updatingStockItem = null;
    public $transactionCount = 0;

    // Link stock properties
    public $linkStockItemId = '';

    // Create stock item properties
    public $newStockName = '';
    public $newStockQty = 0;
    public $newStockUnitId = '';
    public $newStockCostPrice = 0;
    public $newStockReorderLevel = 10;

    // Update stock properties
    public $updateStockQty = 0;
    public $updateStockAction = 'add'; // add or subtract
    public $updateStockNotes = '';

    // Create form properties
    public $createName = '';
    public $createDescription = '';
    public $createCategoryId = '';
    public $createPrice = 0;
    public $createCostPrice = 0;
    public $createItemId = '';
    public $createIsAvailable = true;
    public $createIsVegetarian = false;
    public $createIsVegan = false;
    public $createPrepTimeMins = null;

    // Edit form properties
    public $editName = '';
    public $editDescription = '';
    public $editCategoryId = '';
    public $editPrice = 0;
    public $editCostPrice = 0;
    public $editItemId = '';
    public $editIsAvailable = true;
    public $editIsVegetarian = false;
    public $editIsVegan = false;
    public $editPrepTimeMins = null;

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

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    // Create Menu Item Methods
    public function openCreateModal()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }

        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm()
    {
        $this->createName = '';
        $this->createDescription = '';
        $this->createCategoryId = '';
        $this->createPrice = 0;
        $this->createCostPrice = 0;
        $this->createItemId = '';
        $this->createIsAvailable = true;
        $this->createIsVegetarian = false;
        $this->createIsVegan = false;
        $this->createPrepTimeMins = null;
    }

    public function createMenuItem()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet.');
            return;
        }

        $this->validate([
            'createName' => 'required|max:150',
            'createCategoryId' => 'required|exists:menu_categories,id',
            'createPrice' => 'required|numeric|min:0',
            'createCostPrice' => 'nullable|numeric|min:0',
            'createItemId' => 'nullable|exists:items,id',
            'createPrepTimeMins' => 'nullable|integer|min:1|max:999',
        ], [
            'createName.required' => 'Menu item name is required.',
            'createCategoryId.required' => 'Category is required.',
            'createPrice.required' => 'Price is required.',
            'createPrice.min' => 'Price must be greater than or equal to 0.',
        ]);

        try {
            MenuItem::create([
                'outlet_id' => $this->selectedOutlet,
                'name' => $this->createName,
                'description' => $this->createDescription,
                'category_id' => $this->createCategoryId,
                'price' => $this->createPrice,
                'cost_price' => $this->createCostPrice ?: null,
                'item_id' => $this->createItemId ?: null,
                'is_available' => $this->createIsAvailable,
                'is_vegetarian' => $this->createIsVegetarian,
                'is_vegan' => $this->createIsVegan,
                'prep_time_mins' => $this->createPrepTimeMins,
                'status' => 'active', // New items are active by default
            ]);

            session()->flash('message', 'Menu item created successfully.');
            $this->closeCreateModal();
            $this->resetPage(); // Refresh the list
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating menu item: ' . $e->getMessage());
        }
    }

    // Edit Menu Item Methods
    public function openEditModal($itemId)
    {
        $item = MenuItem::with(['category', 'item'])->find($itemId);

        if (!$item) {
            session()->flash('error', 'Menu item not found.');
            return;
        }

        $this->editingItem = $item;
        $this->editName = $item->name;
        $this->editDescription = $item->description ?? '';
        $this->editCategoryId = $item->category_id;
        $this->editPrice = $item->price;
        $this->editCostPrice = $item->cost_price ?? 0;
        $this->editItemId = $item->item_id ?? '';
        $this->editIsAvailable = $item->is_available;
        $this->editIsVegetarian = $item->is_vegetarian;
        $this->editIsVegan = $item->is_vegan;
        $this->editPrepTimeMins = $item->prep_time_mins;

        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingItem = null;
        $this->reset([
            'editName',
            'editDescription',
            'editCategoryId',
            'editPrice',
            'editCostPrice',
            'editItemId',
            'editIsAvailable',
            'editIsVegetarian',
            'editIsVegan',
            'editPrepTimeMins',
        ]);
    }

    public function updateMenuItem()
    {
        $this->validate([
            'editName' => 'required|max:150',
            'editCategoryId' => 'required|exists:menu_categories,id',
            'editPrice' => 'required|numeric|min:0',
            'editCostPrice' => 'nullable|numeric|min:0',
            'editItemId' => 'nullable|exists:items,id',
            'editPrepTimeMins' => 'nullable|integer|min:1|max:999',
        ], [
            'editName.required' => 'Menu item name is required.',
            'editCategoryId.required' => 'Category is required.',
            'editPrice.required' => 'Price is required.',
            'editPrice.min' => 'Price must be greater than or equal to 0.',
        ]);

        try {
            $this->editingItem->update([
                'name' => $this->editName,
                'description' => $this->editDescription,
                'category_id' => $this->editCategoryId,
                'price' => $this->editPrice,
                'cost_price' => $this->editCostPrice ?: null,
                'item_id' => $this->editItemId ?: null,
                'is_available' => $this->editIsAvailable,
                'is_vegetarian' => $this->editIsVegetarian,
                'is_vegan' => $this->editIsVegan,
                'prep_time_mins' => $this->editPrepTimeMins,
            ]);

            session()->flash('message', 'Menu item updated successfully.');
            $this->closeEditModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating menu item: ' . $e->getMessage());
        }
    }

    public function toggleStatus($itemId)
    {
        try {
            $item = MenuItem::find($itemId);

            if (!$item) {
                session()->flash('error', 'Menu item not found.');
                return;
            }

            $newStatus = $item->status === 'active' ? 'inactive' : 'active';
            $item->update(['status' => $newStatus]);

            session()->flash('message', 'Menu item status updated to ' . $newStatus . '.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function openDeleteModal($itemId)
    {
        $item = MenuItem::find($itemId);

        if (!$item) {
            session()->flash('error', 'Menu item not found.');
            return;
        }

        // Check if item has been used in transactions
        $this->transactionCount = PosOrderItem::where('menu_item_id', $itemId)->count();
        $this->deletingItem = $item;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deletingItem = null;
        $this->transactionCount = 0;
    }

    public function deleteMenuItem()
    {
        if (!$this->deletingItem) {
            session()->flash('error', 'No item selected for deletion.');
            return;
        }

        // Double-check transactions
        if ($this->transactionCount > 0) {
            session()->flash('error', 'Cannot delete menu item with transaction history. Please deactivate it instead.');
            $this->closeDeleteModal();
            return;
        }

        try {
            $itemName = $this->deletingItem->name;
            $this->deletingItem->delete();

            session()->flash('message', "Menu item '{$itemName}' deleted successfully.");
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting menu item: ' . $e->getMessage());
        }
    }

    // Link Stock Methods
    public function openLinkStockModal($itemId)
    {
        $item = MenuItem::with('item')->find($itemId);

        if (!$item) {
            session()->flash('error', 'Menu item not found.');
            return;
        }

        $this->linkingItem = $item;
        $this->linkStockItemId = $item->item_id ?? '';
        $this->showLinkStockModal = true;
    }

    public function closeLinkStockModal()
    {
        $this->showLinkStockModal = false;
        $this->linkingItem = null;
        $this->linkStockItemId = '';
    }

    public function updateStockLink()
    {
        if (!$this->linkingItem) {
            session()->flash('error', 'No menu item selected.');
            return;
        }

        $this->validate([
            'linkStockItemId' => 'nullable|exists:items,id',
        ]);

        try {
            $this->linkingItem->update([
                'item_id' => $this->linkStockItemId ?: null,
            ]);

            if ($this->linkStockItemId) {
                $stockItem = items::find($this->linkStockItemId);
                session()->flash('message', "Menu item linked to '{$stockItem->name}' successfully.");
            } else {
                session()->flash('message', 'Stock link removed successfully.');
            }

            $this->closeLinkStockModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating stock link: ' . $e->getMessage());
        }
    }

    public function unlinkStock($itemId)
    {
        try {
            $item = MenuItem::find($itemId);

            if (!$item) {
                session()->flash('error', 'Menu item not found.');
                return;
            }

            $item->update(['item_id' => null]);
            session()->flash('message', 'Stock link removed successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error removing stock link: ' . $e->getMessage());
        }
    }

    // Create Stock Item Methods
    public function openCreateStockModal()
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        $this->resetCreateStockForm();
        $this->showCreateStockModal = true;
    }

    public function closeCreateStockModal()
    {
        $this->showCreateStockModal = false;
        $this->resetCreateStockForm();
    }

    private function resetCreateStockForm()
    {
        $this->newStockName = '';
        $this->newStockQty = 0;
        $this->newStockUnitId = '';
        $this->newStockCostPrice = 0;
        $this->newStockReorderLevel = 10;
    }

    public function createStockItem()
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business.');
            return;
        }

        $this->validate([
            'newStockName' => 'required|max:255',
            'newStockQty' => 'required|numeric|min:0',
            'newStockUnitId' => 'nullable|exists:units,id',
            'newStockCostPrice' => 'nullable|numeric|min:0',
            'newStockReorderLevel' => 'nullable|integer|min:0',
        ], [
            'newStockName.required' => 'Stock item name is required.',
            'newStockQty.required' => 'Quantity is required.',
            'newStockQty.min' => 'Quantity must be greater than or equal to 0.',
        ]);

        try {
            // Get first category for this business
            $categoryId = DB::table('categories')
                ->where('business_id', $this->selectedBusiness)
                ->value('id');

            if (!$categoryId) {
                session()->flash('error', 'No category found for this business. Please create a category first.');
                return;
            }

            // Get first unit if not selected
            if (!$this->newStockUnitId) {
                $this->newStockUnitId = DB::table('units')->value('id');
                if (!$this->newStockUnitId) {
                    session()->flash('error', 'No units found. Please create a unit first.');
                    return;
                }
            }

            $newItem = items::create([
                'business_id' => $this->selectedBusiness,
                'name' => $this->newStockName,
                'description' => $this->newStockName,
                'qty' => $this->newStockQty,
                'unit_id' => $this->newStockUnitId,
                'cost_price' => $this->newStockCostPrice ?: 0,
                'reorder_level' => $this->newStockReorderLevel,
                'category_id' => $categoryId,
            ]);

            // Auto-select the newly created item
            $this->linkStockItemId = $newItem->id;

            session()->flash('message', "Stock item '{$this->newStockName}' created successfully.");
            $this->closeCreateStockModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating stock item: ' . $e->getMessage());
        }
    }

    // Update Stock Methods
    public function openUpdateStockModal($itemId)
    {
        $item = MenuItem::with('item')->find($itemId);

        if (!$item) {
            session()->flash('error', 'Menu item not found.');
            return;
        }

        if (!$item->item_id) {
            session()->flash('error', 'This menu item is not linked to any stock item.');
            return;
        }

        $this->updatingStockItem = $item;
        $this->updateStockQty = 0;
        $this->updateStockAction = 'add';
        $this->updateStockNotes = '';
        $this->showUpdateStockModal = true;
    }

    public function closeUpdateStockModal()
    {
        $this->showUpdateStockModal = false;
        $this->updatingStockItem = null;
        $this->reset(['updateStockQty', 'updateStockAction', 'updateStockNotes']);
    }

    public function updateStock()
    {
        if (!$this->updatingStockItem || !$this->updatingStockItem->item) {
            session()->flash('error', 'No stock item selected.');
            return;
        }

        $this->validate([
            'updateStockQty' => 'required|numeric|min:0.01',
            'updateStockAction' => 'required|in:add,subtract',
        ], [
            'updateStockQty.required' => 'Quantity is required.',
            'updateStockQty.min' => 'Quantity must be greater than 0.',
        ]);

        DB::beginTransaction();
        try {
            $stockItem = $this->updatingStockItem->item;
            $currentQty = $stockItem->qty ?? 0;

            if ($this->updateStockAction === 'add') {
                $newQty = $currentQty + $this->updateStockQty;
            } else {
                $newQty = $currentQty - $this->updateStockQty;
                if ($newQty < 0) {
                    session()->flash('error', 'Cannot subtract more than current stock quantity.');
                    return;
                }
            }

            // Update stock item quantity
            $stockItem->update(['qty' => $newQty]);

            // Record in item_balance (notes column doesn't exist, movement_reason enum has limited values)
            DB::table('item_balances')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'item_id' => $stockItem->id,
                'user_id' => Auth::id(),
                'business_id' => $this->selectedBusiness,
                'outlet_id' => null,
                'order_id' => null,
                'order_item_id' => null,
                'previous_balance' => $currentQty,
                'current_balance' => $newQty,
                'quantity_changed' => $this->updateStockAction === 'add' ? $this->updateStockQty : -$this->updateStockQty,
                'quantity_ml' => null,
                'movement_reason' => 'normal',
                'stock_type' => $this->updateStockAction === 'add' ? 'in' : 'out',
                'stransaction_type' => 'adjustment',
                'invoice_number' => 'ADJ-' . date('YmdHis'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Refresh the stock item to show updated quantity
            $stockItem->refresh();

            $unitName = $stockItem->unit ? $stockItem->unit->name : 'pcs';
            session()->flash('message', "Stock updated successfully. New quantity: {$newQty} {$unitName}");
            $this->closeUpdateStockModal();

            // Reset the page to refresh the data
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating stock: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $menuItems = collect();
        $categories = collect();

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        if ($this->selectedOutlet) {
            // Load categories for filter
            $categories = MenuCategory::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'active')
                ->orderBy('display_order')
                ->orderBy('name')
                ->get();

            // Menu Items Query
            $menuItemQuery = MenuItem::where('outlet_id', $this->selectedOutlet);

            if ($this->search) {
                $menuItemQuery->where('name', 'like', '%' . $this->search . '%');
            }

            if ($this->selectedCategory) {
                $menuItemQuery->where('category_id', $this->selectedCategory);
            }

            $menuItems = $menuItemQuery->with(['category', 'item.unit'])
                ->orderBy('category_id')
                ->orderBy('name')
                ->paginate(20);
        }

        // Load stock items for dropdown
        $stockItems = collect();
        if ($this->selectedBusiness) {
            $stockItems = items::where('business_id', $this->selectedBusiness)
                ->with('unit')
                ->orderBy('name')
                ->get();
        }

        // Load units for create stock modal
        $units = collect();
        if ($this->selectedBusiness) {
            $units = DB::table('units')->orderBy('name')->get();
        }

        return view('livewire.owner.bar.bar-menu-items', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'menuItems' => $menuItems,
            'categories' => $categories,
            'stockItems' => $stockItems,
            'units' => $units,
        ]);
    }
}
