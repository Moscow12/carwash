<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Guest;

#[Layout('components.layouts.app-owner')]
class Guests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $vipFilter = '';
    public $statusFilter = '';
    public $showModal = false;
    public $editMode = false;
    public $guestId = null;

    #[Rule('required|string|max:255')]
    public $first_name = '';

    #[Rule('required|string|max:255')]
    public $last_name = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('required|string|max:50')]
    public $phone = '';

    #[Rule('nullable|string|max:255')]
    public $nationality = '';

    #[Rule('nullable|string|max:255')]
    public $country = '';

    #[Rule('nullable|string|max:255')]
    public $coming_from = '';

    #[Rule('nullable|string|max:255')]
    public $going_to = '';

    #[Rule('nullable|in:passport,national_id,drivers_license')]
    public $id_type = '';

    #[Rule('nullable|string|max:100')]
    public $id_number = '';

    #[Rule('nullable|date')]
    public $date_of_birth = '';

    #[Rule('nullable|in:male,female,other')]
    public $gender = '';

    #[Rule('nullable|string|max:500')]
    public $address = '';

    #[Rule('nullable|in:regular,silver,gold,platinum')]
    public $vip_level = 'regular';

    #[Rule('required|integer|min:0')]
    public $loyalty_points = 0;

    #[Rule('required|boolean')]
    public $blacklisted = false;

    #[Rule('nullable|string|max:500')]
    public $blacklist_reason = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

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

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editGuest($id)
    {
        $guest = Guest::findOrFail($id);

        $this->guestId = $guest->id;
        $this->first_name = $guest->first_name;
        $this->last_name = $guest->last_name;
        $this->email = $guest->email ?? '';
        $this->phone = $guest->phone;
        $this->nationality = $guest->nationality ?? '';
        $this->country = $guest->country ?? '';
        $this->coming_from = $guest->coming_from ?? '';
        $this->going_to = $guest->going_to ?? '';
        $this->id_type = $guest->id_type ?? '';
        $this->id_number = $guest->id_number ?? '';
        $this->date_of_birth = $guest->date_of_birth?->format('Y-m-d') ?? '';
        $this->gender = $guest->gender ?? '';
        $this->address = $guest->address ?? '';
        $this->vip_level = $guest->vip_level ?? 'regular';
        $this->loyalty_points = $guest->loyalty_points;
        $this->blacklisted = $guest->blacklisted;
        $this->blacklist_reason = $guest->blacklist_reason ?? '';
        $this->status = $guest->status;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'business_id' => $this->selectedHotel,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email ?: null,
            'phone' => $this->phone,
            'nationality' => $this->nationality ?: null,
            'country' => $this->country ?: null,
            'coming_from' => $this->coming_from ?: null,
            'going_to' => $this->going_to ?: null,
            'id_type' => $this->id_type ?: null,
            'id_number' => $this->id_number ?: null,
            'date_of_birth' => $this->date_of_birth ?: null,
            'gender' => $this->gender ?: null,
            'address' => $this->address ?: null,
            'vip_level' => $this->vip_level,
            'loyalty_points' => $this->loyalty_points,
            'blacklisted' => $this->blacklisted,
            'blacklist_reason' => $this->blacklisted ? $this->blacklist_reason : null,
            'status' => $this->status,
        ];

        if ($this->editMode) {
            Guest::findOrFail($this->guestId)->update($data);
            session()->flash('message', 'Guest updated successfully.');
        } else {
            Guest::create($data);
            session()->flash('message', 'Guest created successfully.');
        }

        $this->closeModal();
    }

    public function toggleBlacklist($id)
    {
        $guest = Guest::findOrFail($id);
        $guest->update(['blacklisted' => !$guest->blacklisted]);
        session()->flash('message', 'Guest blacklist status updated.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'guestId', 'first_name', 'last_name', 'email', 'phone', 'nationality',
            'country', 'coming_from', 'going_to',
            'id_type', 'id_number', 'date_of_birth', 'gender', 'address',
            'vip_level', 'loyalty_points', 'blacklisted', 'blacklist_reason', 'status'
        ]);
        $this->vip_level = 'regular';
        $this->loyalty_points = 0;
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        try {
            $query = Guest::withCount(['reservations', 'folios'])
                ->where('business_id', $this->selectedHotel);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->vipFilter) {
                $query->where('vip_level', $this->vipFilter);
            }

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $guests = $query->latest()->paginate(15);
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to load guest data. Please contact support if this issue persists.');
            $guests = Guest::where('business_id', $this->selectedHotel)->paginate(15);
        }

        $stats = [
            'total' => Guest::where('business_id', $this->selectedHotel)->count(),
            'vip' => Guest::where('business_id', $this->selectedHotel)->whereIn('vip_level', ['silver', 'gold', 'platinum'])->count(),
            'blacklisted' => Guest::where('business_id', $this->selectedHotel)->where('blacklisted', true)->count(),
        ];

        // Get countries for dropdown
        $countries = DB::table('countries')
            ->orderBy('name')
            ->pluck('name', 'name');

        return view('livewire.owner.hotel.guests', [
            'hotels' => $hotels,
            'guests' => $guests,
            'countries' => $countries,
            'stats' => $stats,
        ]);
    }
}
