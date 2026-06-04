<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Business;
use App\Models\RoomType;

#[Layout('components.layouts.app-owner')]
class RoomTypes extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $selectedHotel = null;
    public $showModal = false;
    public $editMode = false;
    public $roomTypeId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('nullable|string|max:1000')]
    public $description = '';

    #[Rule('required|integer|min:1|max:10')]
    public $max_adults = 2;

    #[Rule('required|integer|min:0|max:10')]
    public $max_children = 2;

    #[Rule('required|numeric|min:0')]
    public $base_price = 0;

    #[Rule('nullable|numeric|min:0')]
    public $weekend_price = 0;

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    #[Rule('boolean')]
    public $is_published = false;

    #[Rule('nullable')]
    public $newImages = [];

    public $existingImages = [];

    public $selectedAmenities = [];
    public $availableAmenities = [
        'wifi' => 'Free WiFi',
        'tv' => 'Flat Screen TV',
        'ac' => 'Air Conditioning',
        'minibar' => 'Mini Bar',
        'safe' => 'In-room Safe',
        'coffee_maker' => 'Coffee Maker',
        'balcony' => 'Balcony',
        'bath_tub' => 'Bath Tub',
        'shower' => 'Shower',
        'hairdryer' => 'Hair Dryer',
        'iron' => 'Iron & Ironing Board',
        'phone' => 'Telephone',
        'workspace' => 'Work Desk',
        'sofa' => 'Sofa/Seating Area',
    ];

    public function mount()
    {
        $hotel = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
        }
    }

    public function updatedSelectedHotel()
    {
        $this->resetPage();
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

    public function editRoomType($id)
    {
        $roomType = RoomType::findOrFail($id);

        $this->roomTypeId = $roomType->id;
        $this->name = $roomType->name;
        $this->description = $roomType->description ?? '';
        $this->max_adults = $roomType->max_adults;
        $this->max_children = $roomType->max_children;
        $this->base_price = $roomType->base_price;
        $this->weekend_price = $roomType->weekend_price ?? 0;
        $this->status = $roomType->status;
        $this->is_published = (bool) $roomType->is_published;
        $this->selectedAmenities = $roomType->amenities ?? [];
        $this->existingImages = $roomType->images ?? [];
        $this->newImages = [];

        $this->editMode = true;
        $this->showModal = true;
    }

    public function removeExistingImage($path)
    {
        Storage::disk('public')->delete($path);
        $this->existingImages = array_values(array_filter(
            $this->existingImages,
            fn ($p) => $p !== $path
        ));

        // Persist immediately when editing an existing room type.
        if ($this->editMode && $this->roomTypeId) {
            RoomType::whereKey($this->roomTypeId)->update(['images' => $this->existingImages]);
        }
    }

    public function save()
    {
        $this->validate([
            'newImages.*' => 'nullable|image|max:4096',
        ]);

        // Store any newly uploaded photos and merge with the kept existing ones.
        $imagePaths = $this->existingImages;
        foreach ($this->newImages as $img) {
            if ($img) {
                $imagePaths[] = $img->store('room-types', 'public');
            }
        }

        $data = [
            'business_id' => $this->selectedHotel,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'max_adults' => $this->max_adults,
            'max_children' => $this->max_children,
            'base_price' => $this->base_price,
            'weekend_price' => $this->weekend_price ?: null,
            'status' => $this->status,
            'is_published' => (bool) $this->is_published,
            'amenities' => $this->selectedAmenities,
            'images' => array_values($imagePaths),
        ];

        if ($this->editMode) {
            $roomType = RoomType::findOrFail($this->roomTypeId);
            $roomType->update($data);
            session()->flash('message', 'Room type updated successfully.');
        } else {
            RoomType::create($data);
            session()->flash('message', 'Room type created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $roomType = RoomType::findOrFail($id);

        // Check if room type has rooms
        if ($roomType->rooms()->exists()) {
            session()->flash('error', 'Cannot delete room type with existing rooms. Please delete or reassign rooms first.');
            return;
        }

        $roomType->delete();
        session()->flash('message', 'Room type deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $roomType = RoomType::findOrFail($id);
        $roomType->update([
            'status' => $roomType->status === 'active' ? 'inactive' : 'active',
        ]);
        session()->flash('message', 'Room type status updated successfully.');
    }

    public function togglePublish($id)
    {
        $roomType = RoomType::findOrFail($id);
        $roomType->update(['is_published' => ! $roomType->is_published]);
        session()->flash('message', $roomType->is_published
            ? 'Room type published to the marketplace.'
            : 'Room type unpublished from the marketplace.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'roomTypeId', 'name', 'description', 'max_adults', 'max_children',
            'base_price', 'weekend_price', 'status', 'is_published',
            'selectedAmenities', 'newImages', 'existingImages',
        ]);
        $this->max_adults = 2;
        $this->max_children = 2;
        $this->status = 'active';
        $this->is_published = false;
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $query = RoomType::where('business_id', $this->selectedHotel)
            ->withCount('rooms');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $roomTypes = $query->latest()->paginate(10);

        return view('livewire.owner.hotel.room-types', [
            'hotels' => $hotels,
            'roomTypes' => $roomTypes,
        ]);
    }
}
