<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Room;
use App\Models\Reservation;
use App\Models\FolioCharge;
use App\Models\HotelPayment;
use App\Models\HousekeepingTask;
use App\Models\Guest;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class HotelReports extends Component
{
    public $activeTab = 'occupancy';
    public $selectedHotel = null;
    public $dateFrom = '';
    public $dateTo = '';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        if (request()->has('tab')) {
            $this->activeTab = request()->get('tab');
        }

        // Default to last 30 days
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function generateReport()
    {
        $this->validate([
            'dateFrom' => 'required|date',
            'dateTo' => 'required|date|after_or_equal:dateFrom',
        ]);

        session()->flash('message', 'Report generated successfully.');
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $reportData = [];

        if ($this->selectedHotel && $this->dateFrom && $this->dateTo) {
            $from = Carbon::parse($this->dateFrom);
            $to = Carbon::parse($this->dateTo);

            if ($this->activeTab === 'occupancy') {
                $totalRooms = Room::where('business_id', $this->selectedHotel)->count();
                $totalDays = $from->diffInDays($to) + 1;

                $occupiedRoomNights = Reservation::where('business_id', $this->selectedHotel)
                    ->where('status', 'checked_in')
                    ->whereBetween('check_in', [$from, $to])
                    ->count();

                $occupancyRate = $totalRooms > 0 && $totalDays > 0
                    ? ($occupiedRoomNights / ($totalRooms * $totalDays)) * 100
                    : 0;

                $reportData = [
                    'total_rooms' => $totalRooms,
                    'total_days' => $totalDays,
                    'occupied_room_nights' => $occupiedRoomNights,
                    'occupancy_rate' => $occupancyRate,
                    'by_room_type' => Room::where('business_id', $this->selectedHotel)
                        ->select('room_type_id')
                        ->with('roomType')
                        ->get()
                        ->groupBy('room_type_id')
                        ->map(function($rooms) {
                            return [
                                'name' => $rooms->first()->roomType->name ?? 'Unknown',
                                'count' => $rooms->count(),
                            ];
                        })
                        ->values(),
                ];
            }

            if ($this->activeTab === 'revenue') {
                $roomRevenue = FolioCharge::whereHas('folio', function($q) {
                        $q->where('business_id', $this->selectedHotel);
                    })
                    ->where('charge_type', 'room')
                    ->whereBetween('charge_date', [$from, $to])
                    ->sum('amount');

                $fbRevenue = FolioCharge::whereHas('folio', function($q) {
                        $q->where('business_id', $this->selectedHotel);
                    })
                    ->whereIn('charge_type', ['restaurant', 'bar'])
                    ->whereBetween('charge_date', [$from, $to])
                    ->sum('amount');

                $otherRevenue = FolioCharge::whereHas('folio', function($q) {
                        $q->where('business_id', $this->selectedHotel);
                    })
                    ->whereNotIn('charge_type', ['room', 'restaurant', 'bar'])
                    ->whereBetween('charge_date', [$from, $to])
                    ->sum('amount');

                $totalRevenue = $roomRevenue + $fbRevenue + $otherRevenue;

                $reportData = [
                    'room_revenue' => $roomRevenue,
                    'fb_revenue' => $fbRevenue,
                    'other_revenue' => $otherRevenue,
                    'total_revenue' => $totalRevenue,
                    'daily_avg' => $from->diffInDays($to) > 0
                        ? $totalRevenue / ($from->diffInDays($to) + 1)
                        : $totalRevenue,
                ];
            }

            if ($this->activeTab === 'reservations') {
                $totalReservations = Reservation::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

                $bySource = Reservation::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->select('booking_source_id')
                    ->with('bookingSource')
                    ->get()
                    ->groupBy('booking_source_id')
                    ->map(function($reservations) {
                        return [
                            'source' => $reservations->first()->bookingSource->name ?? 'Direct',
                            'count' => $reservations->count(),
                        ];
                    })
                    ->values();

                $byStatus = Reservation::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get();

                $reportData = [
                    'total_reservations' => $totalReservations,
                    'by_source' => $bySource,
                    'by_status' => $byStatus,
                ];
            }

            if ($this->activeTab === 'housekeeping') {
                $totalTasks = HousekeepingTask::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->count();

                $completedTasks = HousekeepingTask::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('status', 'completed')
                    ->count();

                $avgCompletionTime = HousekeepingTask::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->where('status', 'completed')
                    ->whereNotNull('started_at')
                    ->whereNotNull('completed_at')
                    ->get()
                    ->avg(function($task) {
                        return $task->started_at->diffInMinutes($task->completed_at);
                    });

                $byPriority = HousekeepingTask::where('business_id', $this->selectedHotel)
                    ->whereBetween('created_at', [$from, $to])
                    ->select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->get();

                $reportData = [
                    'total_tasks' => $totalTasks,
                    'completed_tasks' => $completedTasks,
                    'pending_tasks' => $totalTasks - $completedTasks,
                    'completion_rate' => $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0,
                    'avg_completion_time' => $avgCompletionTime,
                    'by_priority' => $byPriority,
                ];
            }

            if ($this->activeTab === 'guest-history') {
                $totalGuests = Guest::where('business_id', $this->selectedHotel)->count();

                $vipBreakdown = Guest::where('business_id', $this->selectedHotel)
                    ->select('vip_level', DB::raw('count(*) as count'))
                    ->groupBy('vip_level')
                    ->get();

                $topGuests = Guest::where('business_id', $this->selectedHotel)
                    ->withCount('reservations')
                    ->orderBy('reservations_count', 'desc')
                    ->limit(10)
                    ->get();

                $reportData = [
                    'total_guests' => $totalGuests,
                    'vip_breakdown' => $vipBreakdown,
                    'top_guests' => $topGuests,
                ];
            }
        }

        return view('livewire.owner.hotel.hotel-reports', [
            'hotels' => $hotels,
            'reportData' => $reportData,
        ]);
    }
}
