<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use App\Models\street;

#[Layout('components.layouts.app-owner')]
class Addhotel extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $hotelId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|max:255')]
    public $address = '';

    #[Rule('nullable|string|max:500')]
    public $description = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    #[Rule('nullable|string|max:50')]
    public $whatsapp = '';

    #[Rule('nullable|string|max:100')]
    public $instagram = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('nullable|url|max:255')]
    public $website = '';

    #[Rule('nullable|string|max:100')]
    public $operating_hours = '';

    #[Rule('required|string|max:255')]
    public $resentative_name = '';

    #[Rule('required|string|max:50')]
    public $resentative_phone = '';

    #[Rule('required|exists:regions,id')]
    public $region_id = '';

    #[Rule('required|exists:districts,id')]
    public $district_id = '';

    #[Rule('required|exists:wards,id')]
    public $ward_id = '';

    #[Rule('nullable|exists:streets,id')]
    public $street_id = '';

    public $allRegions = [];
    public $allDistricts = [];
    public $allWards = [];
    public $allStreets = [];

    // Summary stats
    public $totalHotels = 0;
    public $activeHotels = 0;
    public $inactiveHotels = 0;

    public function mount()
    {
        $this->allRegions = regions::orderBy('name')->get();
    }

    public function updatedRegionId($value)
    {
        $this->allDistricts = $value ? districts::where('region_id', $value)->orderBy('name')->get() : [];
        $this->district_id = '';
        $this->ward_id = '';
        $this->street_id = '';
        $this->allWards = [];
        $this->allStreets = [];
    }

    public function updatedDistrictId($value)
    {
        $this->allWards = $value ? wards::where('district_id', $value)->orderBy('name')->get() : [];
        $this->ward_id = '';
        $this->street_id = '';
        $this->allStreets = [];
    }

    public function updatedWardId($value)
    {
        $this->allStreets = $value ? street::where('ward_id', $value)->orderBy('name')->get() : [];
        $this->street_id = '';
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

    public function editHotel($id)
    {
        $hotel = Business::where('type', 'hotel')->findOrFail($id);

        $this->hotelId = $hotel->id;
        $this->name = $hotel->name;
        $this->address = $hotel->address;
        $this->description = $hotel->description ?? '';
        $this->status = $hotel->status;
        $this->whatsapp = $hotel->whatsapp ?? '';
        $this->instagram = $hotel->instagram ?? '';
        $this->email = $hotel->email ?? '';
        $this->website = $hotel->website ?? '';
        $this->operating_hours = $hotel->operating_hours ?? '';
        $this->resentative_name = $hotel->resentative_name;
        $this->resentative_phone = $hotel->resentative_phone;
        $this->region_id = $hotel->region_id;

        $this->allDistricts = districts::where('region_id', $hotel->region_id)->orderBy('name')->get();
        $this->district_id = $hotel->district_id;

        $this->allWards = wards::where('district_id', $hotel->district_id)->orderBy('name')->get();
        $this->ward_id = $hotel->ward_id;

        $this->allStreets = street::where('ward_id', $hotel->ward_id)->orderBy('name')->get();
        $this->street_id = $hotel->street_id ?? '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'type' => 'hotel', // Always set type to hotel
            'whatsapp' => $this->whatsapp ?: null,
            'instagram' => $this->instagram ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'operating_hours' => $this->operating_hours ?: null,
            'resentative_name' => $this->resentative_name,
            'resentative_phone' => $this->resentative_phone,
            'region_id' => $this->region_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,
            'street_id' => $this->street_id ?: null,
        ];

        if ($this->editMode) {
            $hotel = Business::findOrFail($this->hotelId);
            $hotel->update($data);
            session()->flash('message', 'Hotel updated successfully.');
        } else {
            $data['owner_id'] = Auth::id();
            Business::create($data);
            session()->flash('message', 'Hotel created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        try {
            $hotel = Business::where('type', 'hotel')->findOrFail($id);

            // Check for related data
            $hasRooms = $hotel->hotelBranches()->exists() ||
                        DB::table('rooms')->where('business_id', $id)->exists();
            $hasReservations = $hotel->reservations()->exists();
            $hasGuests = $hotel->guests()->exists();
            $hasFolios = $hotel->folios()->exists();
            $hasInvoices = $hotel->hotelInvoices()->exists();
            $hasPosOutlets = $hotel->posOutlets()->exists();

            if ($hasRooms || $hasReservations || $hasGuests || $hasFolios || $hasInvoices || $hasPosOutlets) {
                $relatedData = [];
                if ($hasRooms) $relatedData[] = 'rooms';
                if ($hasReservations) $relatedData[] = 'reservations';
                if ($hasGuests) $relatedData[] = 'guests';
                if ($hasFolios) $relatedData[] = 'folios';
                if ($hasInvoices) $relatedData[] = 'invoices';
                if ($hasPosOutlets) $relatedData[] = 'POS outlets';

                session()->flash('error', 'Cannot delete hotel with existing ' . implode(', ', $relatedData) . '. Please deactivate instead.');
                return;
            }

            $hotel->delete();
            session()->flash('message', 'Hotel deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting hotel: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $hotel = Business::where('type', 'hotel')->findOrFail($id);
        $hotel->update([
            'status' => $hotel->status === 'active' ? 'inactive' : 'active',
        ]);
        session()->flash('message', 'Hotel status updated successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'hotelId', 'name', 'address', 'description', 'status',
            'whatsapp', 'instagram', 'email', 'website', 'operating_hours',
            'resentative_name', 'resentative_phone', 'region_id',
            'district_id', 'ward_id', 'street_id'
        ]);
        $this->status = 'active';
        $this->allDistricts = [];
        $this->allWards = [];
        $this->allStreets = [];
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('address', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with(['regions', 'districts', 'wards'])
            ->withCount(['hotelBranches', 'reservations', 'guests'])
            ->latest()
            ->paginate(10);

        // Calculate stats
        $this->totalHotels = Auth::user()->assignedBusinesses()->where('type', 'hotel')->count();
        $this->activeHotels = Auth::user()->assignedBusinesses()->where('type', 'hotel')->where('status', 'active')->count();
        $this->inactiveHotels = $this->totalHotels - $this->activeHotels;

        return view('livewire.owner.hotel.addhotel', [
            'hotels' => $hotels,
        ]);
    }
}
