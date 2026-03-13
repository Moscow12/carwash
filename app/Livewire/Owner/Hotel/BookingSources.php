<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\BookingSource;

#[Layout('components.layouts.app-owner')]
class BookingSources extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $showModal = false;
    public $editMode = false;
    public $sourceId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|in:direct,ota,corporate,travel_agent,walk_in')]
    public $type = 'direct';

    #[Rule('nullable|numeric|min:0|max:100')]
    public $commission_pct = 0;

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    public function mount()
    {
        $hotel = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editSource($id)
    {
        $source = BookingSource::findOrFail($id);

        $this->sourceId = $source->id;
        $this->name = $source->name;
        $this->type = $source->type;
        $this->commission_pct = $source->commission_pct ?? 0;
        $this->status = $source->status;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'business_id' => $this->selectedHotel,
            'name' => $this->name,
            'type' => $this->type,
            'commission_pct' => $this->commission_pct ?: 0,
            'status' => $this->status,
        ];

        if ($this->editMode) {
            BookingSource::findOrFail($this->sourceId)->update($data);
            session()->flash('message', 'Booking source updated successfully.');
        } else {
            BookingSource::create($data);
            session()->flash('message', 'Booking source created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $source = BookingSource::findOrFail($id);

        if ($source->reservations()->exists()) {
            session()->flash('error', 'Cannot delete booking source with existing reservations.');
            return;
        }

        $source->delete();
        session()->flash('message', 'Booking source deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['sourceId', 'name', 'type', 'commission_pct', 'status']);
        $this->type = 'direct';
        $this->commission_pct = 0;
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $query = BookingSource::withCount('reservations')
            ->where('business_id', $this->selectedHotel);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $sources = $query->latest()->paginate(15);

        return view('livewire.owner.hotel.booking-sources', [
            'hotels' => $hotels,
            'sources' => $sources,
        ]);
    }
}
