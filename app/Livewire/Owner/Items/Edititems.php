<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\items;
use App\Models\category;
use App\Models\unit;

#[Layout('components.layouts.app-owner')]
class Edititems extends Component
{
    use WithFileUploads;

    public $itemId;
    public $item;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:255')]
    public $barcode = '';

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

    public $showScannerModal = false;

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();
        $this->availableUnits = unit::where('status', 'active')->orderBy('name')->get();

        $this->loadItem();
    }

    public function loadItem()
    {
        $this->item = items::with(['category', 'unit', 'carwash'])->find($this->itemId);

        if (!$this->item) {
            session()->flash('error', 'Item not found.');
            return redirect()->route('owner.list-items');
        }

        // Verify ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($this->item->carwash_id)) {
            session()->flash('error', 'Unauthorized access.');
            return redirect()->route('owner.list-items');
        }

        // Populate form fields
        $this->name = $this->item->name;
        $this->barcode = $this->item->barcode ?? '';
        $this->description = $this->item->description ?? '';
        $this->cost_price = $this->item->cost_price;
        $this->selling_price = $this->item->selling_price;
        $this->market_price = $this->item->market_price ?? '';
        $this->type = $this->item->type;
        $this->product_stock = $this->item->product_stock ?? 'no';
        $this->require_plate_number = $this->item->require_plate_number ?? 'no';
        $this->commission = $this->item->commission ?? '';
        $this->commission_type = $this->item->commission_type ?? '';
        $this->status = $this->item->status;
        $this->carwash_id = $this->item->carwash_id;
        $this->category_id = $this->item->category_id;
        $this->unit_id = $this->item->unit_id;
        $this->existingImage = $this->item->image;

        $this->loadCategories();
    }

    public function updatedCarwashId($value)
    {
        $this->loadCategories();
        $this->category_id = '';
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

    public function removeImage()
    {
        if ($this->existingImage) {
            Storage::disk('public')->delete($this->existingImage);
            $this->item->update(['image' => null]);
            $this->existingImage = null;
            session()->flash('message', 'Image removed successfully.');
        }
    }

    public function save()
    {
        $this->validate();

        // Custom barcode uniqueness validation per carwash
        if ($this->barcode) {
            $exists = items::where('barcode', $this->barcode)
                ->where('carwash_id', $this->carwash_id)
                ->where('id', '!=', $this->itemId)
                ->exists();

            if ($exists) {
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
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['image'] = $this->image->store('items', 'public');
        }

        $this->item->update($data);

        session()->flash('message', 'Item updated successfully.');

        // Reload item data
        $this->loadItem();
    }

    public function render()
    {
        return view('livewire.owner.items.edititems');
    }
}
