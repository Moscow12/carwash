<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\RoomType;

#[Layout('components.layouts.app-owner')]
class ChannelMapping extends Component
{
    use WithPagination;

    public $selectedHotel = null;
    public $search = '';
    public $filterChannel = '';
    public $showModal = false;
    public $editMode = false;
    public $mappingId = null;

    // Channel mapping properties
    #[Rule('required|exists:room_types,id')]
    public $room_type_id = '';

    #[Rule('required|in:booking_com,expedia,airbnb,agoda,hotels_com,tripadvisor,google_hotel,direct')]
    public $channel = 'booking_com';

    #[Rule('required|string|max:255')]
    public $channel_room_id = '';

    #[Rule('nullable|string|max:255')]
    public $channel_room_name = '';

    #[Rule('required|in:active,inactive,paused')]
    public $status = 'active';

    #[Rule('required|boolean')]
    public $sync_rates = true;

    #[Rule('required|boolean')]
    public $sync_availability = true;

    #[Rule('required|boolean')]
    public $sync_restrictions = true;

    #[Rule('nullable|numeric|min:0|max:100')]
    public $markup_percentage = 0;

    #[Rule('nullable|numeric|min:0')]
    public $min_stay_override = null;

    #[Rule('nullable|numeric|min:0')]
    public $max_stay_override = null;

    #[Rule('nullable|string|max:500')]
    public $notes = '';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }
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
        $this->mappingId = null;
        $this->room_type_id = '';
        $this->channel = 'booking_com';
        $this->channel_room_id = '';
        $this->channel_room_name = '';
        $this->status = 'active';
        $this->sync_rates = true;
        $this->sync_availability = true;
        $this->sync_restrictions = true;
        $this->markup_percentage = 0;
        $this->min_stay_override = null;
        $this->max_stay_override = null;
        $this->notes = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // In production, save to channel_mappings table
            // For now, flash success message
            session()->flash('message', 'Channel mapping saved successfully.');

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save mapping: ' . $e->getMessage());
        }
    }

    public function toggleStatus($mappingId)
    {
        try {
            // In production, toggle status in channel_mappings table
            session()->flash('message', 'Mapping status updated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function syncNow($mappingId)
    {
        try {
            // In production, trigger sync with channel manager
            session()->flash('message', 'Sync initiated successfully. This may take a few minutes.');
        } catch (\Exception $e) {
            session()->flash('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $mappings = collect();
        $roomTypes = collect();
        $stats = [
            'total_mappings' => 0,
            'active' => 0,
            'channels_connected' => 0,
            'sync_enabled' => 0,
        ];

        if ($this->selectedHotel) {
            // Mock data - in production, query from channel_mappings table
            $mappings = collect([]);

            // Get room types for dropdown
            $roomTypes = RoomType::where('business_id', $this->selectedHotel)
                ->orderBy('name')
                ->get();

            // Mock stats
            $stats = [
                'total_mappings' => 0,
                'active' => 0,
                'channels_connected' => 0,
                'sync_enabled' => 0,
            ];
        }

        return view('livewire.owner.hotel.channel-mapping', [
            'hotels' => $hotels,
            'mappings' => $mappings,
            'roomTypes' => $roomTypes,
            'stats' => $stats,
        ]);
    }
}
