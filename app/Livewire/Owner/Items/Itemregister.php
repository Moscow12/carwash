<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\items;
use App\Models\carwashes;
use App\Models\category;
use App\Models\unit;

#[Layout('components.layouts.app-owner')]
class Itemregister extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $filterCarwash = '';
    public $filterCategory = '';
    public $filterType = '';
    public $filterStatus = '';

    public $showModal = false;
    public $showDeleteModal = false;
    public $editMode = false;
    public $itemId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:255')]
    public $barcode = '';

    public $showScannerModal = false;

    #[Rule('required|string|max:500')]
    public $description = '';

    #[Rule('required|numeric|min:0')]
    public $cost_price = '';

    #[Rule('required|numeric|min:0')]
    public $selling_price = '';

    #[Rule('nullable|numeric|min:0')]
    public $market_price = '';

    #[Rule('required|in:Service,product')]
    public $type = 'Service';

    #[Rule('required|in:yes,no')]
    public $product_stock = 'no';

    #[Rule('required|in:yes,no')]
    public $require_plate_number = 'no';

    #[Rule('nullable|numeric|min:0')]
    public $commission = '';

    #[Rule('nullable|in:fixed,percentage')]
    public $commission_type = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    #[Rule('required|exists:carwashes,id')]
    public $carwash_id = '';

    #[Rule('required|exists:categories,id')]
    public $category_id = '';

    #[Rule('required|exists:units,id')]
    public $unit_id = '';

    #[Rule('nullable|image|max:2048')]
    public $image;

    public $existingImage = null;

    public $ownerCarwashes = [];
    public $availableCategories = [];
    public $availableUnits = [];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();
        $this->availableUnits = unit::where('status', 'active')->orderBy('name')->get();

        if ($this->ownerCarwashes->count() === 1) {
            $this->carwash_id = $this->ownerCarwashes->first()->id;
            $this->filterCarwash = $this->carwash_id;
            $this->loadCategories();
        }
    }

    public function updatedCarwashId($value)
    {
        $this->loadCategories();
        $this->category_id = '';
    }

    public function updatedFilterCarwash($value)
    {
        $this->resetPage();
    }

    public function loadCategories()
    {
        if ($this->carwash_id) {
            $this->availableCategories = category::where('carwash_id', $this->carwash_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->availableCategories = [];
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editItem($id)
    {
        $item = items::findOrFail($id);

        $this->itemId = $item->id;
        $this->name = $item->name;
        $this->barcode = $item->barcode ?? '';
        $this->description = $item->description;
        $this->cost_price = $item->cost_price;
        $this->selling_price = $item->selling_price;
        $this->market_price = $item->market_price ?? '';
        $this->type = $item->type;
        $this->product_stock = $item->product_stock;
        $this->require_plate_number = $item->require_plate_number;
        $this->commission = $item->commission ?? '';
        $this->commission_type = $item->commission_type ?? '';
        $this->status = $item->status;
        $this->carwash_id = $item->carwash_id;
        $this->category_id = $item->category_id;
        $this->unit_id = $item->unit_id;
        $this->existingImage = $item->image;

        $this->loadCategories();

        $this->editMode = true;
        $this->showModal = true;
    }

    public function confirmDelete($id)
    {
        $this->itemId = $id;
        $this->showDeleteModal = true;
    }

    public function deleteItem()
    {
        $item = items::findOrFail($this->itemId);

        // Verify ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($item->carwash_id)) {
            session()->flash('error', 'Unauthorized action.');
            $this->showDeleteModal = false;
            return;
        }

        // Delete image if exists
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();
        session()->flash('message', 'Item deleted successfully.');
        $this->showDeleteModal = false;
        $this->itemId = null;
    }

    public function toggleStatus($id)
    {
        $item = items::findOrFail($id);

        // Verify ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($item->carwash_id)) {
            session()->flash('error', 'Unauthorized action.');
            return;
        }

        $item->update([
            'status' => $item->status === 'active' ? 'inactive' : 'active'
        ]);

        session()->flash('message', 'Item status updated successfully.');
    }

    public function save()
    {
        $this->validate();

        // Custom barcode uniqueness validation per carwash
        if ($this->barcode) {
            $query = items::where('barcode', $this->barcode)
                ->where('carwash_id', $this->carwash_id);

            if ($this->editMode && $this->itemId) {
                $query->where('id', '!=', $this->itemId);
            }

            if ($query->exists()) {
                $this->addError('barcode', 'This barcode is already used by another item in this carwash.');
                return;
            }
        }

        // Verify carwash ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($this->carwash_id)) {
            session()->flash('error', 'Invalid carwash selected.');
            return;
        }

        $data = [
            'name' => $this->name,
            'barcode' => $this->barcode ?: null,
            'description' => $this->description,
            'cost_price' => $this->cost_price,
            'selling_price' => $this->selling_price,
            'market_price' => $this->market_price ?: null,
            'type' => $this->type,
            'product_stock' => $this->product_stock,
            'require_plate_number' => $this->require_plate_number,
            'commission' => $this->commission ?: null,
            'commission_type' => $this->commission ? $this->commission_type : null,
            'status' => $this->status,
            'carwash_id' => $this->carwash_id,
            'category_id' => $this->category_id,
            'unit_id' => $this->unit_id,
        ];

        // Handle image upload
        if ($this->image) {
            if ($this->editMode && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('items', 'public');
        }

        if ($this->editMode) {
            $item = items::findOrFail($this->itemId);
            $item->update($data);
            session()->flash('message', 'Item updated successfully.');
        } else {
            items::create($data);
            session()->flash('message', 'Item created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->itemId = null;
    }

    public function openScannerModal()
    {
        $this->showScannerModal = true;
    }

    public function closeScannerModal()
    {
        $this->showScannerModal = false;
    }

    public function setBarcodeFromScanner($barcode)
    {
        $this->barcode = $barcode;
        $this->showScannerModal = false;
    }

    public function resetForm()
    {
        $this->reset([
            'itemId', 'name', 'barcode', 'description', 'cost_price', 'selling_price',
            'market_price', 'type', 'product_stock', 'require_plate_number',
            'commission', 'commission_type', 'status', 'category_id', 'unit_id',
            'image', 'existingImage', 'showScannerModal'
        ]);

        $this->type = 'Service';
        $this->product_stock = 'no';
        $this->require_plate_number = 'no';
        $this->status = 'active';

        if ($this->ownerCarwashes->count() === 1) {
            $this->carwash_id = $this->ownerCarwashes->first()->id;
            $this->loadCategories();
        } else {
            $this->carwash_id = $this->filterCarwash ?: '';
            if ($this->carwash_id) {
                $this->loadCategories();
            } else {
                $this->availableCategories = [];
            }
        }

        $this->resetValidation();
    }

    public function render()
    {
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');

        $items = items::whereIn('carwash_id', $carwashIds)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterCarwash, function ($query) {
                $query->where('carwash_id', $this->filterCarwash);
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category_id', $this->filterCategory);
            })
            ->when($this->filterType, function ($query) {
                $query->where('type', $this->filterType);
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->with(['carwash', 'category', 'unit'])
            ->latest()
            ->paginate(10);

        // Get categories for filter based on selected carwash
        $filterCategories = $this->filterCarwash
            ? category::where('carwash_id', $this->filterCarwash)->where('status', 'active')->get()
            : category::whereIn('carwash_id', $carwashIds)->where('status', 'active')->get();

        return view('livewire.owner.items.itemregister', [
            'items' => $items,
            'filterCategories' => $filterCategories,
        ]);
    }
}
