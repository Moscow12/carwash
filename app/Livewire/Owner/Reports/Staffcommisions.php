<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\staffs;
use App\Models\sales_item;
use App\Models\Business;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Staffcommisions extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    #[Url]
    public $business_id = '';

    #[Url]
    public $staff_id = '';

    #[Url]
    public $date_filter = 'month'; // day, week, month, year, custom

    #[Url]
    public $start_date = '';

    #[Url]
    public $end_date = '';

    public $perPage = 25;
    public $showFilters = true;

    // Data
    public $businesses = [];
    public $staffList = [];

    public function mount()
    {
        $owner = Auth::user();
        $businessCollection = $owner->assignedBusinesses()->get();

        $this->businesses = $businessCollection->map(function ($business) {
            return [
                'id' => $business->id,
                'name' => $business->name,
            ];
        })->toArray();

        if (count($this->businesses) > 0 && empty($this->business_id)) {
            $this->business_id = $this->businesses[0]['id'];
        }

        $this->setDateRange();
        $this->loadStaffList();
    }

    public function updatedBusinessId()
    {
        $this->loadStaffList();
        $this->staff_id = '';
        $this->resetPage();
    }

    public function updatedStaffId()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        $this->setDateRange();
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function setDateRange()
    {
        $now = Carbon::now();

        switch ($this->date_filter) {
            case 'day':
                $this->start_date = $now->format('Y-m-d');
                $this->end_date = $now->format('Y-m-d');
                break;
            case 'week':
                $this->start_date = $now->startOfWeek()->format('Y-m-d');
                $this->end_date = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->start_date = $now->startOfMonth()->format('Y-m-d');
                $this->end_date = $now->endOfMonth()->format('Y-m-d');
                break;
            case 'year':
                $this->start_date = $now->startOfYear()->format('Y-m-d');
                $this->end_date = $now->endOfYear()->format('Y-m-d');
                break;
            case 'custom':
                // Keep existing dates or set defaults
                if (empty($this->start_date)) {
                    $this->start_date = $now->startOfMonth()->format('Y-m-d');
                }
                if (empty($this->end_date)) {
                    $this->end_date = $now->endOfMonth()->format('Y-m-d');
                }
                break;
        }
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->staff_id = '';
        $this->date_filter = 'month';
        $this->setDateRange();
        $this->resetPage();
    }

    protected function loadStaffList()
    {
        if (empty($this->business_id)) {
            $this->staffList = [];
            return;
        }

        $this->staffList = staffs::where('business_id', $this->business_id)
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'position' => $s->position,
                'commission_type' => $s->commission_type,
                'amount' => $s->amount,
                'status' => $s->status,
            ])
            ->toArray();
    }

    protected function getDateTimeRange()
    {
        $startDateTime = Carbon::parse($this->start_date)->startOfDay();
        $endDateTime = Carbon::parse($this->end_date)->endOfDay();

        return [$startDateTime, $endDateTime];
    }

    public function getStaffPerformanceProperty()
    {
        if (empty($this->business_id)) {
            return collect([]);
        }

        [$startDateTime, $endDateTime] = $this->getDateTimeRange();

        $query = staffs::where('business_id', $this->business_id)
            ->when($this->staff_id, fn($q) => $q->where('id', $this->staff_id))
            ->with(['salesItems' => function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('date', [$startDateTime, $endDateTime])
                    ->with('item');
            }]);

        return $query->get()->map(function ($staff) {
            $salesItems = $staff->salesItems;

            $servicesCount = $salesItems->count();
            $totalAmount = $salesItems->sum(function ($item) {
                return ($item->price * ($item->quantity ?? 1)) - ($item->discount ?? 0);
            });

            // Calculate commission based on staff's commission settings
            $calculatedCommission = 0;
            if ($staff->commission_type === 'percentage' && $staff->amount > 0) {
                $calculatedCommission = $totalAmount * ($staff->amount / 100);
            } elseif ($staff->commission_type === 'fixed' && $staff->amount > 0) {
                $calculatedCommission = $servicesCount * $staff->amount;
            }

            // Also get the actual commission stored in sales_items
            $recordedCommission = $salesItems->sum('commission');

            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'position' => $staff->position,
                'phone' => $staff->phone,
                'status' => $staff->status,
                'commission_type' => $staff->commission_type,
                'commission_rate' => $staff->amount,
                'services_count' => $servicesCount,
                'total_amount' => $totalAmount,
                'calculated_commission' => $calculatedCommission,
                'recorded_commission' => $recordedCommission,
            ];
        });
    }

    public function getDetailedDataProperty()
    {
        if (empty($this->business_id)) {
            return null;
        }

        [$startDateTime, $endDateTime] = $this->getDateTimeRange();

        $query = sales_item::query()
            ->select(
                'sales_items.*',
                'staffs.name as staff_name',
                'staffs.commission_type as staff_commission_type',
                'staffs.amount as staff_commission_rate',
                'items.name as item_name',
                'items.type as item_type',
                'sales.sale_date'
            )
            ->join('staffs', 'sales_items.staff_id', '=', 'staffs.id')
            ->join('items', 'sales_items.item_id', '=', 'items.id')
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->where('staffs.business_id', $this->business_id)
            ->whereBetween('sales_items.date', [$startDateTime, $endDateTime]);

        if (!empty($this->staff_id)) {
            $query->where('sales_items.staff_id', $this->staff_id);
        }

        return $query->orderBy('sales_items.date', 'desc')
            ->paginate($this->perPage);
    }

    public function getSummaryProperty()
    {
        $performance = $this->staffPerformance;

        return [
            'total_staff' => $performance->count(),
            'total_services' => $performance->sum('services_count'),
            'total_amount' => $performance->sum('total_amount'),
            'total_commission' => $performance->sum('calculated_commission'),
            'total_recorded_commission' => $performance->sum('recorded_commission'),
        ];
    }

    public function render()
    {
        return view('livewire.owner.reports.staffcommisions', [
            'staffPerformance' => $this->staffPerformance,
            'detailedData' => $this->detailedData,
            'summary' => $this->summary,
        ]);
    }
}
