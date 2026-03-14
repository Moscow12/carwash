<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\NightAuditSnapshot;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\FolioCharge;
use App\Models\HotelPayment;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class NightAudit extends Component
{
    use WithPagination;

    public $selectedHotel = null;
    public $selectedDate = null;
    public $showModal = false;
    public $auditData = [];

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        $this->selectedDate = now()->format('Y-m-d');
    }

    public function runNightAudit()
    {
        if (!$this->selectedHotel || !$this->selectedDate) {
            session()->flash('error', 'Please select a hotel and date.');
            return;
        }

        try {
            DB::beginTransaction();

            $auditDate = Carbon::parse($this->selectedDate);

            // Check if audit already exists for this date
            $existingAudit = NightAuditSnapshot::where('business_id', $this->selectedHotel)
                ->whereDate('audit_date', $auditDate)
                ->first();

            if ($existingAudit) {
                session()->flash('error', 'Night audit already exists for this date.');
                return;
            }

            // Calculate metrics
            $totalRooms = Room::where('business_id', $this->selectedHotel)->count();
            $occupiedRooms = Room::where('business_id', $this->selectedHotel)
                ->where('status', 'occupied')->count();

            $occupancyRate = $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0;

            // Room revenue
            $roomRevenue = FolioCharge::whereHas('folio', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->where('charge_type', 'room')
                ->whereDate('posted_at', $auditDate)
                ->sum('amount');

            // F&B revenue
            $fbRevenue = FolioCharge::whereHas('folio', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->whereIn('charge_type', ['restaurant', 'bar'])
                ->whereDate('posted_at', $auditDate)
                ->sum('amount');

            // Other revenue
            $otherRevenue = FolioCharge::whereHas('folio', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->whereNotIn('charge_type', ['room', 'restaurant', 'bar'])
                ->whereDate('posted_at', $auditDate)
                ->sum('amount');

            $totalRevenue = $roomRevenue + $fbRevenue + $otherRevenue;

            // Payments collected
            $paymentsCollected = HotelPayment::where('business_id', $this->selectedHotel)
                ->whereDate('paid_at', $auditDate)
                ->sum('amount');

            // Arrivals and departures
            $arrivals = Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_in_date', $auditDate)
                ->where('status', 'checked_in')
                ->count();

            $departures = Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_out_date', $auditDate)
                ->where('status', 'checked_out')
                ->count();

            // Average Daily Rate (ADR)
            $adr = $occupiedRooms > 0 ? $roomRevenue / $occupiedRooms : 0;

            // Revenue Per Available Room (RevPAR)
            $revpar = $totalRooms > 0 ? $roomRevenue / $totalRooms : 0;

            // Get the first hotel branch for this business, or null
            $branchId = DB::table('hotel_branches')
                ->where('business_id', $this->selectedHotel)
                ->value('id');

            // Create snapshot
            NightAuditSnapshot::create([
                'business_id' => $this->selectedHotel,
                'branch_id' => $branchId,
                'audit_date' => $auditDate,
                'total_rooms' => $totalRooms,
                'occupied_rooms' => $occupiedRooms,
                'occupancy_pct' => $occupancyRate,
                'room_revenue' => $roomRevenue,
                'fb_revenue' => $fbRevenue,
                'total_revenue' => $totalRevenue,
                'new_arrivals' => $arrivals,
                'departures' => $departures,
                'adr' => $adr,
                'revpar' => $revpar,
                'run_by' => Auth::id(),
                'run_at' => now(),
            ]);

            DB::commit();
            session()->flash('message', 'Night audit completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Night audit failed: ' . $e->getMessage());
        }
    }

    public function viewAudit($id)
    {
        $audit = NightAuditSnapshot::findOrFail($id);
        $this->auditData = $audit->toArray();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->auditData = [];
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $audits = collect();
        $todayMetrics = [
            'occupied_rooms' => 0,
            'occupancy_rate' => 0,
            'total_revenue' => 0,
            'adr' => 0,
        ];

        if ($this->selectedHotel) {
            $audits = NightAuditSnapshot::where('business_id', $this->selectedHotel)
                ->orderBy('audit_date', 'desc')
                ->paginate(15);

            // Today's live metrics
            $totalRooms = Room::where('business_id', $this->selectedHotel)->count();
            $occupiedRooms = Room::where('business_id', $this->selectedHotel)
                ->where('status', 'occupied')->count();

            $todayRevenue = FolioCharge::whereHas('folio', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->whereDate('posted_at', today())
                ->sum('amount');

            $todayMetrics = [
                'occupied_rooms' => $occupiedRooms,
                'occupancy_rate' => $totalRooms > 0 ? ($occupiedRooms / $totalRooms) * 100 : 0,
                'total_revenue' => $todayRevenue,
                'adr' => $occupiedRooms > 0 ? $todayRevenue / $occupiedRooms : 0,
            ];
        }

        return view('livewire.owner.hotel.night-audit', [
            'hotels' => $hotels,
            'audits' => $audits,
            'todayMetrics' => $todayMetrics,
        ]);
    }
}
