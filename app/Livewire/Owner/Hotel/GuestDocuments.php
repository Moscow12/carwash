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
use App\Models\Guest;
use App\Models\Reservation;

#[Layout('components.layouts.app-owner')]
class GuestDocuments extends Component
{
    use WithPagination, WithFileUploads;

    public $selectedHotel = null;
    public $search = '';
    public $showModal = false;
    public $selectedGuest = null;
    public $selectedReservation = null;

    // Document properties
    #[Rule('required|in:passport,nida,driving_license,voter_id')]
    public $document_type = 'passport';

    #[Rule('required|string|max:100')]
    public $document_number = '';

    #[Rule('nullable|date')]
    public $issue_date = '';

    #[Rule('nullable|date|after:issue_date')]
    public $expiry_date = '';

    #[Rule('nullable|string|max:100')]
    public $issuing_country = '';

    #[Rule('nullable|file|mimes:jpg,jpeg,png,pdf|max:5120')]
    public $document_file = null;

    #[Rule('nullable|string|max:500')]
    public $notes = '';

    public $uploadProgress = 0;

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }
    }

    public function openModal($guestId = null, $reservationId = null)
    {
        $this->selectedGuest = $guestId ? Guest::findOrFail($guestId) : null;
        $this->selectedReservation = $reservationId;
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedGuest = null;
        $this->selectedReservation = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->document_type = 'passport';
        $this->document_number = '';
        $this->issue_date = '';
        $this->expiry_date = '';
        $this->issuing_country = '';
        $this->document_file = null;
        $this->notes = '';
        $this->uploadProgress = 0;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            $filePath = null;

            if ($this->document_file) {
                // Store the file
                $filePath = $this->document_file->store('guest-documents', 'public');
            }

            // In a real implementation, you would save to a guest_documents table
            // For now, we'll update the guest's ID information
            if ($this->selectedGuest) {
                $this->selectedGuest->update([
                    'id_type' => $this->document_type,
                    'id_number' => $this->document_number,
                ]);
            }

            session()->flash('message', 'Document uploaded successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $guests = collect();
        $reservations = collect();
        $stats = [
            'total_guests' => 0,
            'with_documents' => 0,
            'pending_documents' => 0,
            'expiring_soon' => 0,
        ];

        if ($this->selectedHotel) {
            // Get guests with reservations
            $guestQuery = Guest::where('business_id', $this->selectedHotel)
                ->withCount('reservations');

            if ($this->search) {
                $guestQuery->where(function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('id_number', 'like', '%' . $this->search . '%');
                });
            }

            $guests = $guestQuery->latest()->paginate(15);

            // Get recent reservations needing documents
            $reservations = Reservation::where('business_id', $this->selectedHotel)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->with(['guest', 'room'])
                ->latest()
                ->limit(10)
                ->get();

            // Calculate stats
            $allGuests = Guest::where('business_id', $this->selectedHotel)->get();
            $stats = [
                'total_guests' => $allGuests->count(),
                'with_documents' => $allGuests->whereNotNull('id_number')->count(),
                'pending_documents' => $allGuests->whereNull('id_number')->count(),
                'expiring_soon' => 0, // Would need separate documents table to track expiry
            ];
        }

        return view('livewire.owner.hotel.guest-documents', [
            'hotels' => $hotels,
            'guests' => $guests,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
