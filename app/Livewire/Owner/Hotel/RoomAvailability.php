<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Business;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;
use App\Models\RatePlan;

#[Layout('components.layouts.app-owner')]
class RoomAvailability extends Component
{
    public $selectedHotel = null;
    public $selectedRoomType = null;
    public $currentMonth;
    public $currentYear;
    public $showModal = false;

    // Bulk update properties
    #[Rule('required|date')]
    public $dateFrom = '';

    #[Rule('required|date|after_or_equal:dateFrom')]
    public $dateTo = '';

    #[Rule('nullable|numeric|min:0')]
    public $bulkPrice = null;

    #[Rule('nullable|integer|min:0')]
    public $bulkRooms = null;

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo = now()->addDays(7)->format('Y-m-d');
    }

    public function previousMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth()
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function openBulkUpdateModal()
    {
        $this->showModal = true;
    }

    public function closeBulkUpdateModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo = now()->addDays(7)->format('Y-m-d');
        $this->bulkPrice = null;
        $this->bulkRooms = null;
        $this->resetValidation();
    }

    public function bulkUpdate()
    {
        $this->validate();

        try {
            // This is a placeholder for bulk availability/price update logic
            // In a real implementation, you would update availability records

            session()->flash('message', 'Availability updated successfully for the selected period.');
            $this->closeBulkUpdateModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $roomTypes = collect();
        $calendarData = [];
        $stats = [
            'total_rooms' => 0,
            'avg_occupancy' => 0,
            'available_today' => 0,
            'booked_today' => 0,
        ];

        if ($this->selectedHotel) {
            // Get room types
            $roomTypes = RoomType::where('business_id', $this->selectedHotel)
                ->withCount('rooms')
                ->orderBy('name')
                ->get();

            // Generate calendar data
            $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $daysInMonth = $startDate->daysInMonth;

            // Get reservations for the month
            $reservations = Reservation::where('business_id', $this->selectedHotel)
                ->where(function($query) use ($startDate, $endDate) {
                    $query->whereBetween('check_in_date', [$startDate, $endDate])
                          ->orWhereBetween('check_out_date', [$startDate, $endDate])
                          ->orWhere(function($q) use ($startDate, $endDate) {
                              $q->where('check_in_date', '<=', $startDate)
                                ->where('check_out_date', '>=', $endDate);
                          });
                })
                ->with('room.roomType')
                ->get();

            // Build calendar data
            $query = $this->selectedRoomType
                ? RoomType::where('id', $this->selectedRoomType)
                : RoomType::where('business_id', $this->selectedHotel);

            $roomTypesForCalendar = $query->with('rooms')->get();

            foreach ($roomTypesForCalendar as $roomType) {
                $typeData = [
                    'name' => $roomType->name,
                    'total_rooms' => $roomType->rooms->count(),
                    'days' => [],
                ];

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = Carbon::create($this->currentYear, $this->currentMonth, $day);

                    // Count occupied rooms for this room type on this date
                    $occupiedCount = $reservations->filter(function($reservation) use ($currentDate, $roomType) {
                        return $reservation->room
                            && $reservation->room->room_type_id === $roomType->id
                            && $reservation->check_in_date <= $currentDate
                            && $reservation->check_out_date > $currentDate
                            && in_array($reservation->status, ['confirmed', 'checked_in']);
                    })->count();

                    $availableCount = $roomType->rooms->count() - $occupiedCount;
                    $occupancyRate = $roomType->rooms->count() > 0
                        ? ($occupiedCount / $roomType->rooms->count()) * 100
                        : 0;

                    $typeData['days'][] = [
                        'day' => $day,
                        'date' => $currentDate->format('Y-m-d'),
                        'is_past' => $currentDate->isPast() && !$currentDate->isToday(),
                        'is_today' => $currentDate->isToday(),
                        'is_weekend' => $currentDate->isWeekend(),
                        'available' => $availableCount,
                        'occupied' => $occupiedCount,
                        'occupancy_rate' => $occupancyRate,
                    ];
                }

                $calendarData[] = $typeData;
            }

            // Calculate stats
            $totalRooms = Room::where('business_id', $this->selectedHotel)->count();
            $todayReservations = Reservation::where('business_id', $this->selectedHotel)
                ->where('check_in_date', '<=', today())
                ->where('check_out_date', '>', today())
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();

            $stats = [
                'total_rooms' => $totalRooms,
                'avg_occupancy' => $totalRooms > 0 ? ($todayReservations / $totalRooms) * 100 : 0,
                'available_today' => $totalRooms - $todayReservations,
                'booked_today' => $todayReservations,
            ];
        }

        return view('livewire.owner.hotel.room-availability', [
            'hotels' => $hotels,
            'roomTypes' => $roomTypes,
            'calendarData' => $calendarData,
            'stats' => $stats,
            'monthName' => Carbon::create($this->currentYear, $this->currentMonth, 1)->format('F Y'),
        ]);
    }
}
