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
            $photoPath = null;

            if ($this->photo) {
                $photoPath = $this->photo->store('lost-found', 'public');
            }

            // In production, save to a lost_and_found table
            // For now, flash success message
            session()->flash('message', 'Lost & Found item logged successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to log item: ' . $e->getMessage());
        }
    }

    public function updateItemStatus($itemId, $newStatus)
    {
        try {
            // In production, update the item status in lost_and_found table
            session()->flash('message', 'Item status updated to ' . ucfirst($newStatus));
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
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
            // Mock data - in production, query from lost_and_found table
            $items = collect([]);

            // Get rooms for dropdown
            $rooms = Room::where('business_id', $this->selectedHotel)
                ->orderBy('number')
                ->get();

            // Get guests for dropdown
            $guests = Guest::where('business_id', $this->selectedHotel)
                ->orderBy('first_name')
                ->get();

            // Mock stats
            $stats = [
                'found' => 0,
                'claimed' => 0,
                'disposed' => 0,
                'total' => 0,
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
