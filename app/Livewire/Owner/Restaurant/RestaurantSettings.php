<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\PosOutlet;
use App\Models\PosTable;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\items;
use App\Models\TaxRate;

#[Layout('components.layouts.app-owner')]
class RestaurantSettings extends Component
{
    use WithFileUploads;

    // Business selection
    public $selectedBusiness = '';
    public $ownerBusinesses = [];

    // Active tab
    public $activeTab = 'outlets';

    // Modals
    public $showOutletModal = false;
    public $showTableModal = false;
    public $showCategoryModal = false;
    public $showMenuItemModal = false;

    // Outlets data
    public $outlets = [];
    public $editingOutletId = null;
    public $outletName = '';
    public $outletType = 'restaurant';
    public $outletOpenTime = '08:00';
    public $outletCloseTime = '22:00';
    public $outletStatus = 'active';
    public $selectedOutlet = '';

    // Tables data
    public $tables = [];
    public $editingTableId = null;
    public $tableNumber = '';
    public $tableCapacity = 4;
    public $tableSection = '';
    public $tableStatus = 'available';
    public $tableIsActive = true;

    // Categories data
    public $categories = [];
    public $editingCategoryId = null;
    public $categoryName = '';
    public $categoryDescription = '';
    public $categoryImage;
    public $categorySortOrder = 0;
    public $categoryStatus = 'active';

    // Menu Items data
    public $menuItems = [];
    public $menuItemSearch = '';
    public $editingMenuItemId = null;
    public $menuItemName = '';
    public $menuItemDescription = '';
    public $menuItemCategoryId = '';
    public $menuItemPrice = 0;
    public $menuItemCostPrice = 0;
    public $menuItemSku = '';
    public $menuItemImage;
    public $menuItemAllergens = '';
    public $menuItemIsVegetarian = false;
    public $menuItemIsVegan = false;
    public $menuItemIsAvailable = true;
    public $menuItemPrepTime = 15;
    public $menuItemStatus = 'active';
    public $menuItemPrinterStation = 'kitchen';

    // Available options
    public $availableCategories = [];
    public $availableInventoryItems = [];
    public $availableTaxRates = [];

    // Settings tabs
    public $tabs = [
        'outlets' => ['label' => 'Outlets', 'icon' => 'ti-building-store'],
        'tables' => ['label' => 'Tables', 'icon' => 'ti-armchair'],
        'categories' => ['label' => 'Menu Categories', 'icon' => 'ti-category'],
        'menu_items' => ['label' => 'Menu Items', 'icon' => 'ti-chef-hat'],
    ];

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->ownedBusinesses()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->selectedBusiness = array_key_first($this->ownerBusinesses);
            $this->loadData();
        }
    }

    public function updatedSelectedBusiness()
    {
        $this->loadData();
    }

    public function updatedSelectedOutlet()
    {
        $this->loadTables();
        $this->loadCategories();
        $this->loadMenuItems();
    }

    public function updatedMenuItemSearch()
    {
        $this->loadMenuItems();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;

        // Load data for the active tab
        if ($tab === 'outlets') {
            $this->loadOutlets();
        } elseif ($tab === 'tables') {
            $this->loadTables();
        } elseif ($tab === 'categories') {
            $this->loadCategories();
        } elseif ($tab === 'menu_items') {
            $this->loadMenuItems();
        }
    }

    public function loadData()
    {
        if (!$this->selectedBusiness) return;

        $this->loadOutlets();
        $this->loadAvailableOptions();
    }

    public function loadAvailableOptions()
    {
        if (!$this->selectedBusiness) return;

        // Load inventory items for linking
        $this->availableInventoryItems = items::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        // Load tax rates
        $this->availableTaxRates = TaxRate::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    // ============ OUTLETS ============
    public function loadOutlets()
    {
        if (!$this->selectedBusiness) {
            $this->outlets = [];
            return;
        }

        $this->outlets = PosOutlet::where('business_id', $this->selectedBusiness)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'open_time', 'close_time', 'status'])
            ->toArray();

        // Set first outlet as selected if none selected
        if (!$this->selectedOutlet && count($this->outlets) > 0) {
            $this->selectedOutlet = $this->outlets[0]['id'];
        }
    }

    public function openOutletModal()
    {
        $this->resetOutletForm();
        $this->showOutletModal = true;
    }

    public function closeOutletModal()
    {
        $this->showOutletModal = false;
        $this->resetOutletForm();
    }

    public function resetOutletForm()
    {
        $this->editingOutletId = null;
        $this->outletName = '';
        $this->outletType = 'restaurant';
        $this->outletOpenTime = '08:00';
        $this->outletCloseTime = '22:00';
        $this->outletStatus = 'active';
    }

    public function editOutlet($id)
    {
        $outlet = PosOutlet::find($id);
        if ($outlet) {
            $this->editingOutletId = $id;
            $this->outletName = $outlet->name;
            $this->outletType = $outlet->type;
            $this->outletOpenTime = $outlet->open_time;
            $this->outletCloseTime = $outlet->close_time;
            $this->outletStatus = $outlet->status;
            $this->showOutletModal = true;
        }
    }

    public function saveOutlet()
    {
        $this->validate([
            'outletName' => 'required|min:2',
            'outletOpenTime' => 'required',
            'outletCloseTime' => 'required',
        ]);

        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a business first.');
            return;
        }

        try {
            if ($this->editingOutletId) {
                // Update existing
                $outlet = PosOutlet::find($this->editingOutletId);
                if ($outlet) {
                    $outlet->update([
                        'name' => $this->outletName,
                        'type' => $this->outletType,
                        'open_time' => $this->outletOpenTime,
                        'close_time' => $this->outletCloseTime,
                        'status' => $this->outletStatus,
                    ]);
                    session()->flash('message', 'Outlet updated successfully.');
                }
            } else {
                // Create new
                PosOutlet::create([
                    'business_id' => $this->selectedBusiness,
                    'name' => $this->outletName,
                    'type' => $this->outletType,
                    'open_time' => $this->outletOpenTime,
                    'close_time' => $this->outletCloseTime,
                    'status' => $this->outletStatus,
                ]);
                session()->flash('message', 'Outlet created successfully.');
            }

            $this->closeOutletModal();
            $this->loadOutlets();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving outlet: ' . $e->getMessage());
        }
    }

    public function toggleOutletStatus($id)
    {
        $outlet = PosOutlet::find($id);
        if ($outlet) {
            $outlet->update([
                'status' => $outlet->status === 'active' ? 'inactive' : 'active',
            ]);
            $this->loadOutlets();
            session()->flash('message', 'Outlet status updated.');
        }
    }

    public function deleteOutlet($id)
    {
        try {
            $outlet = PosOutlet::find($id);
            if ($outlet) {
                $outlet->delete();
                $this->loadOutlets();
                session()->flash('message', 'Outlet deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete outlet. It may have associated data.');
        }
    }

    // ============ TABLES ============
    public function loadTables()
    {
        if (!$this->selectedOutlet) {
            $this->tables = [];
            return;
        }

        $this->tables = PosTable::where('outlet_id', $this->selectedOutlet)
            ->orderBy('table_number')
            ->get(['id', 'table_number', 'capacity', 'section', 'status', 'is_active'])
            ->toArray();
    }

    public function openTableModal()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }
        $this->resetTableForm();
        $this->showTableModal = true;
    }

    public function closeTableModal()
    {
        $this->showTableModal = false;
        $this->resetTableForm();
    }

    public function resetTableForm()
    {
        $this->editingTableId = null;
        $this->tableNumber = '';
        $this->tableCapacity = 4;
        $this->tableSection = '';
        $this->tableStatus = 'available';
        $this->tableIsActive = true;
    }

    public function editTable($id)
    {
        $table = PosTable::find($id);
        if ($table) {
            $this->editingTableId = $id;
            $this->tableNumber = $table->table_number;
            $this->tableCapacity = $table->capacity;
            $this->tableSection = $table->section;
            $this->tableStatus = $table->status;
            $this->tableIsActive = $table->is_active;
            $this->showTableModal = true;
        }
    }

    public function saveTable()
    {
        $this->validate([
            'tableNumber' => 'required',
            'tableCapacity' => 'required|numeric|min:1',
        ]);

        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }

        try {
            if ($this->editingTableId) {
                // Update existing
                $table = PosTable::find($this->editingTableId);
                if ($table) {
                    $table->update([
                        'table_number' => $this->tableNumber,
                        'capacity' => $this->tableCapacity,
                        'section' => $this->tableSection,
                        'status' => $this->tableStatus,
                        'is_active' => $this->tableIsActive,
                    ]);
                    session()->flash('message', 'Table updated successfully.');
                }
            } else {
                // Create new
                PosTable::create([
                    'outlet_id' => $this->selectedOutlet,
                    'table_number' => $this->tableNumber,
                    'capacity' => $this->tableCapacity,
                    'section' => $this->tableSection,
                    'status' => $this->tableStatus,
                    'is_active' => $this->tableIsActive,
                ]);
                session()->flash('message', 'Table created successfully.');
            }

            $this->closeTableModal();
            $this->loadTables();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving table: ' . $e->getMessage());
        }
    }

    public function toggleTableStatus($id)
    {
        $table = PosTable::find($id);
        if ($table) {
            $table->update([
                'is_active' => !$table->is_active,
            ]);
            $this->loadTables();
            session()->flash('message', 'Table status updated.');
        }
    }

    public function deleteTable($id)
    {
        try {
            $table = PosTable::find($id);
            if ($table) {
                $table->delete();
                $this->loadTables();
                session()->flash('message', 'Table deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete table. It may be in use.');
        }
    }

    // ============ CATEGORIES ============
    public function loadCategories()
    {
        if (!$this->selectedOutlet) {
            $this->categories = [];
            $this->availableCategories = [];
            return;
        }

        $this->categories = MenuCategory::where('outlet_id', $this->selectedOutlet)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'description', 'sort_order', 'status'])
            ->toArray();

        $this->availableCategories = MenuCategory::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function openCategoryModal()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }
        $this->resetCategoryForm();
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal()
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function resetCategoryForm()
    {
        $this->editingCategoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categoryImage = null;
        $this->categorySortOrder = 0;
        $this->categoryStatus = 'active';
    }

    public function editCategory($id)
    {
        $category = MenuCategory::find($id);
        if ($category) {
            $this->editingCategoryId = $id;
            $this->categoryName = $category->name;
            $this->categoryDescription = $category->description ?? '';
            $this->categorySortOrder = $category->sort_order;
            $this->categoryStatus = $category->status;
            $this->showCategoryModal = true;
        }
    }

    public function saveCategory()
    {
        $this->validate([
            'categoryName' => 'required|min:2',
        ]);

        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }

        try {
            $imagePath = null;
            if ($this->categoryImage && is_object($this->categoryImage)) {
                $imagePath = $this->categoryImage->store('menu/categories', 'public');
            }

            if ($this->editingCategoryId) {
                // Update existing
                $category = MenuCategory::find($this->editingCategoryId);
                if ($category) {
                    $updateData = [
                        'name' => $this->categoryName,
                        'description' => $this->categoryDescription ?: null,
                        'sort_order' => $this->categorySortOrder,
                        'status' => $this->categoryStatus,
                    ];
                    if ($imagePath) {
                        $updateData['image'] = $imagePath;
                    }
                    $category->update($updateData);
                    session()->flash('message', 'Category updated successfully.');
                }
            } else {
                // Create new
                MenuCategory::create([
                    'outlet_id' => $this->selectedOutlet,
                    'name' => $this->categoryName,
                    'description' => $this->categoryDescription ?: null,
                    'image' => $imagePath,
                    'sort_order' => $this->categorySortOrder,
                    'status' => $this->categoryStatus,
                ]);
                session()->flash('message', 'Category created successfully.');
            }

            $this->closeCategoryModal();
            $this->loadCategories();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving category: ' . $e->getMessage());
        }
    }

    public function toggleCategoryStatus($id)
    {
        $category = MenuCategory::find($id);
        if ($category) {
            $category->update([
                'status' => $category->status === 'active' ? 'inactive' : 'active',
            ]);
            $this->loadCategories();
            session()->flash('message', 'Category status updated.');
        }
    }

    public function deleteCategory($id)
    {
        try {
            $category = MenuCategory::find($id);
            if ($category) {
                $category->delete();
                $this->loadCategories();
                session()->flash('message', 'Category deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete category. It may have menu items.');
        }
    }

    // ============ MENU ITEMS ============
    public function loadMenuItems()
    {
        if (!$this->selectedOutlet) {
            $this->menuItems = [];
            return;
        }

        $query = MenuItem::where('outlet_id', $this->selectedOutlet)
            ->with('category');

        // Apply search filter
        if (!empty($this->menuItemSearch)) {
            $search = $this->menuItemSearch;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $this->menuItems = $query->orderBy('name')
            ->get(['id', 'category_id', 'name', 'description', 'price', 'is_available', 'status'])
            ->toArray();
    }

    public function openMenuItemModal()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }
        if (empty($this->availableCategories)) {
            session()->flash('error', 'Please create at least one category first.');
            return;
        }
        $this->resetMenuItemForm();
        $this->showMenuItemModal = true;
    }

    public function closeMenuItemModal()
    {
        $this->showMenuItemModal = false;
        $this->resetMenuItemForm();
    }

    public function resetMenuItemForm()
    {
        $this->editingMenuItemId = null;
        $this->menuItemName = '';
        $this->menuItemDescription = '';
        $this->menuItemCategoryId = '';
        $this->menuItemPrice = 0;
        $this->menuItemCostPrice = 0;
        $this->menuItemSku = '';
        $this->menuItemImage = null;
        $this->menuItemAllergens = '';
        $this->menuItemIsVegetarian = false;
        $this->menuItemIsVegan = false;
        $this->menuItemIsAvailable = true;
        $this->menuItemPrepTime = 15;
        $this->menuItemStatus = 'active';
        $this->menuItemPrinterStation = 'kitchen';
    }

    public function editMenuItem($id)
    {
        $item = MenuItem::find($id);
        if ($item) {
            $this->editingMenuItemId = $id;
            $this->menuItemName = $item->name;
            $this->menuItemDescription = $item->description ?? '';
            $this->menuItemCategoryId = $item->category_id;
            $this->menuItemPrice = $item->price;
            $this->menuItemCostPrice = $item->cost_price ?? 0;
            $this->menuItemSku = $item->sku ?? '';
            $this->menuItemAllergens = is_array($item->allergens) ? implode(', ', $item->allergens) : '';
            $this->menuItemIsVegetarian = $item->is_vegetarian;
            $this->menuItemIsVegan = $item->is_vegan;
            $this->menuItemIsAvailable = $item->is_available;
            $this->menuItemPrepTime = $item->prep_time_mins;
            $this->menuItemStatus = $item->status;
            $this->menuItemPrinterStation = $item->printer_station ?? 'kitchen';
            $this->showMenuItemModal = true;
        }
    }

    public function saveMenuItem()
    {
        $this->validate([
            'menuItemName' => 'required|min:2',
            'menuItemCategoryId' => 'required',
            'menuItemPrice' => 'required|numeric|min:0',
        ]);

        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }

        try {
            $imagePath = null;
            if ($this->menuItemImage && is_object($this->menuItemImage)) {
                $imagePath = $this->menuItemImage->store('menu/items', 'public');
            }

            // Convert allergens string to array
            $allergensArray = $this->menuItemAllergens
                ? array_map('trim', explode(',', $this->menuItemAllergens))
                : null;

            if ($this->editingMenuItemId) {
                // Update existing
                $item = MenuItem::find($this->editingMenuItemId);
                if ($item) {
                    $updateData = [
                        'category_id' => $this->menuItemCategoryId,
                        'name' => $this->menuItemName,
                        'description' => $this->menuItemDescription ?: null,
                        'price' => $this->menuItemPrice,
                        'cost_price' => $this->menuItemCostPrice ?: null,
                        'sku' => $this->menuItemSku ?: null,
                        'allergens' => $allergensArray,
                        'is_vegetarian' => $this->menuItemIsVegetarian,
                        'is_vegan' => $this->menuItemIsVegan,
                        'is_available' => $this->menuItemIsAvailable,
                        'prep_time_mins' => $this->menuItemPrepTime,
                        'status' => $this->menuItemStatus,
                        'printer_station' => $this->menuItemPrinterStation,
                    ];
                    if ($imagePath) {
                        $updateData['image'] = $imagePath;
                    }
                    $item->update($updateData);
                    session()->flash('message', 'Menu item updated successfully.');
                }
            } else {
                // Create new
                MenuItem::create([
                    'outlet_id' => $this->selectedOutlet,
                    'category_id' => $this->menuItemCategoryId,
                    'name' => $this->menuItemName,
                    'description' => $this->menuItemDescription ?: null,
                    'price' => $this->menuItemPrice,
                    'cost_price' => $this->menuItemCostPrice ?: null,
                    'sku' => $this->menuItemSku ?: null,
                    'image' => $imagePath,
                    'allergens' => $allergensArray,
                    'is_vegetarian' => $this->menuItemIsVegetarian,
                    'is_vegan' => $this->menuItemIsVegan,
                    'is_available' => $this->menuItemIsAvailable,
                    'prep_time_mins' => $this->menuItemPrepTime,
                    'status' => $this->menuItemStatus,
                    'printer_station' => $this->menuItemPrinterStation,
                ]);
                session()->flash('message', 'Menu item created successfully.');
            }

            $this->closeMenuItemModal();
            $this->loadMenuItems();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving menu item: ' . $e->getMessage());
        }
    }

    public function toggleMenuItemAvailability($id)
    {
        $item = MenuItem::find($id);
        if ($item) {
            $item->update([
                'is_available' => !$item->is_available,
            ]);
            $this->loadMenuItems();
            session()->flash('message', 'Menu item availability updated.');
        }
    }

    public function deleteMenuItem($id)
    {
        try {
            $item = MenuItem::find($id);
            if ($item) {
                $item->delete();
                $this->loadMenuItems();
                session()->flash('message', 'Menu item deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete menu item. It may be in use.');
        }
    }

    public function render()
    {
        return view('livewire.owner.restaurant.restaurant-settings');
    }
}
