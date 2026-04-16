<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Business;
use App\Models\Room;
use App\Models\Guest;
use App\Models\LostAndFound as LostAndFoundModel;

#[Layout('components.layouts.app-owner')]
class LostAndFound extends Component
{
    use WithPagination, WithFileUploads;

    public $selectedHotel = null;
    public $search = '';
    public $filterStatus = '';
    public $filterCategory = '';
    public $showModal = false;
    public $editMode = false;
    public $itemId = null;

    // Lost & Found item properties
    #[Rule('required|in:electronics,clothing,jewelry,documents,personal_items,luggage,other')]
    public $category = 'personal_items';

    #[Rule('required|string|max:255')]
    public $item_name = '';

    #[Rule('required|string')]
    public $description = '';

    #[Rule('required|date')]
    public $found_date = '';

    #[Rule('required|string|max:255')]
    public $found_location = '';

    #[Rule('nullable|exists:rooms,id')]
    public $room_id = '';

    #[Rule('nullable|exists:guests,id')]
    public $guest_id = '';

    #[Rule('required|in:found,claimed,disposed,donated')]
    public $status = 'found';

    #[Rule('nullable|string|max:500')]
    public $notes = '';

    #[Rule('nullable|file|mimes:jpg,jpeg,png,pdf|max:5120')]
    public $photo = null;

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        $this->found_date = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterCategory()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
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
        $this->itemId = null;
        $this->category = 'personal_items';
        $this->item_name = '';
        $this->description = '';
        $this->found_date = now()->format('Y-m-d');
        $this->found_location = '';
        $this->room_id = '';
        $this->guest_id = '';
        $this->status = 'found';
        $this->notes = '';
        $this->photo = null;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            if (!$this->selectedHotel) {
                session()->flash('error', 'Please select a hotel first.');
                return;
            }

            // Get default branch for the hotel
            $defaultBranch = \App\Models\HotelBranch::where('business_id', $this->selectedHotel)
                ->where('status', 'active')
                ->first();

            if (!$defaultBranch) {
                session()->flash('error', 'No active branch found for this hotel. Please create a branch first.');
                return;
            }

            $photoPath = null;

            if ($this->photo) {
                $photoPath = $this->photo->store('lost-found', 'public');
            }

            // Check if current user is a staff member
            $staffId = \App\Models\staffs::where('user_id', Auth::id())
                ->where('business_id', $this->selectedHotel)
                ->first()?->id;

            $data = [
                'business_id' => $this->selectedHotel,
                'branch_id' => $defaultBranch->id,
                'room_id' => $this->room_id ?: null,
                'category' => $this->category,
                'item_name' => $this->item_name,
                'item_description' => $this->description,
                'found_date' => $this->found_date,
                'found_location' => $this->found_location,
                'status' => $this->status,
                'found_by' => $staffId,
                'claimed_by_guest' => $this->guest_id ?: null,
                'photo_path' => $photoPath,
                'notes' => $this->notes ?: null,
            ];

            if ($this->editMode && $this->itemId) {
                $item = LostAndFoundModel::findOrFail($this->itemId);
                $item->update($data);
                session()->flash('message', 'Lost & Found item updated successfully.');
            } else {
                LostAndFoundModel::create($data);
                session()->flash('message', 'Lost & Found item logged successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to log item: ' . $e->getMessage());
        }
    }

    public function updateItemStatus($itemId, $newStatus)
    {
        try {
            $item = LostAndFoundModel::findOrFail($itemId);
            $item->update(['status' => $newStatus]);
            session()->flash('message', 'Item status updated to ' . ucfirst($newStatus));
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function editItem($id)
    {
        $item = LostAndFoundModel::findOrFail($id);

        $this->itemId = $item->id;
        $this->category = $item->category ?? 'personal_items';
        $this->item_name = $item->item_name;
        $this->description = $item->item_description;
        $this->found_date = $item->found_date->format('Y-m-d');
        $this->found_location = $item->found_location ?? '';
        $this->room_id = $item->room_id ?? '';
        $this->guest_id = $item->claimed_by_guest ?? '';
        $this->status = $item->status;
        $this->notes = $item->notes ?? '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function deleteItem($id)
    {
        try {
            LostAndFoundModel::findOrFail($id)->delete();
            session()->flash('message', 'Lost & Found item deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $items = collect();
        $rooms = collect();
        $guests = collect();
        $stats = [
            'found' => 0,
            'claimed' => 0,
            'disposed' => 0,
            'total' => 0,
        ];

        if ($this->selectedHotel) {
            // Query lost & found items
            $query = LostAndFoundModel::where('business_id', $this->selectedHotel)
                ->with(['room', 'claimedByGuest', 'foundBy']);

            // Apply filters
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('item_name', 'like', '%' . $this->search . '%')
                      ->orWhere('item_description', 'like', '%' . $this->search . '%')
                      ->orWhere('found_location', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->filterCategory) {
                $query->where('category', $this->filterCategory);
            }

            if ($this->filterStatus) {
                $query->where('status', $this->filterStatus);
            }

            $items = $query->latest('found_date')->paginate(15);

            // Get rooms for dropdown
            $rooms = Room::where('business_id', $this->selectedHotel)
                ->orderBy('number')
                ->get();

            // Get guests for dropdown
            $guests = Guest::where('business_id', $this->selectedHotel)
                ->orderBy('first_name')
                ->get();

            // Calculate stats
            $allItems = LostAndFoundModel::where('business_id', $this->selectedHotel);
            $stats = [
                'found' => (clone $allItems)->where('status', 'found')->count(),
                'claimed' => (clone $allItems)->where('status', 'claimed')->count(),
                'disposed' => (clone $allItems)->where('status', 'disposed')->count(),
                'total' => (clone $allItems)->count(),
            ];
        }

        return view('livewire.owner.hotel.lost-and-found', [
            'hotels' => $hotels,
            'items' => $items,
            'rooms' => $rooms,
            'guests' => $guests,
            'stats' => $stats,
        ]);
    }
}
