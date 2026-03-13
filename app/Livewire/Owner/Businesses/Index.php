<?php

namespace App\Livewire\Owner\Businesses;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Business;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use App\Models\street;
use App\Models\sales;
use App\Models\customers;
use App\Models\Booking;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $businessId = null;

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

    public function editBusiness($id)
    {
        $business = Business::findOrFail($id);

        $this->businessId = $business->id;
        $this->name = $business->name;
        $this->address = $business->address;
        $this->description = $business->description ?? '';
        $this->status = $business->status;
        $this->whatsapp = $business->whatsapp ?? '';
        $this->instagram = $business->instagram ?? '';
        $this->email = $business->email ?? '';
        $this->website = $business->website ?? '';
        $this->operating_hours = $business->operating_hours ?? '';
        $this->resentative_name = $business->resentative_name;
        $this->resentative_phone = $business->resentative_phone;
        $this->region_id = $business->region_id;

        $this->allDistricts = districts::where('region_id', $business->region_id)->orderBy('name')->get();
        $this->district_id = $business->district_id;

        $this->allWards = wards::where('district_id', $business->district_id)->orderBy('name')->get();
        $this->ward_id = $business->ward_id;

        $this->allStreets = street::where('ward_id', $business->ward_id)->orderBy('name')->get();
        $this->street_id = $business->street_id ?? '';

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
            $business = Business::findOrFail($this->businessId);
            $business->update($data);
            session()->flash('message', 'Business updated successfully.');
        } else {
            $data['owner_id'] = Auth::id();
            Business::create($data);
            session()->flash('message', 'Business created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'businessId', 'name', 'address', 'description', 'status',
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
        $businessIds = Auth::user()->ownedBusinesses()->pluck('id');

        $businesses = Auth::user()->ownedBusinesses()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['regions', 'districts', 'wards'])
            ->withCount(['sales', 'customers', 'bookings', 'staffs', 'items'])
            ->latest()
            ->paginate(10);

        // Performance metrics
        $metrics = $this->calculateMetrics($businessIds);

        return view('livewire.owner.businesses.index', [
            'businesses' => $businesses,
            'metrics' => $metrics,
        ]);
    }

    private function calculateMetrics($businessIds): array
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        // Total counts
        $totalBusinesses = $businessIds->count();
        $activeBusinesses = Business::whereIn('id', $businessIds)->where('status', 'active')->count();

        // Sales metrics
        $totalRevenue = sales::whereIn('business_id', $businessIds)
            ->where('payment_status', 'paid')
            ->sum(DB::raw('CAST(total_amount AS DECIMAL(10,2))'));

        $thisMonthRevenue = sales::whereIn('business_id', $businessIds)
            ->where('payment_status', 'paid')
            ->where('sale_date', '>=', $startOfMonth)
            ->sum(DB::raw('CAST(total_amount AS DECIMAL(10,2))'));

        $lastMonthRevenue = sales::whereIn('business_id', $businessIds)
            ->where('payment_status', 'paid')
            ->whereBetween('sale_date', [$startOfLastMonth, $endOfLastMonth])
            ->sum(DB::raw('CAST(total_amount AS DECIMAL(10,2))'));

        $totalSales = sales::whereIn('business_id', $businessIds)->count();
        $thisMonthSales = sales::whereIn('business_id', $businessIds)
            ->where('sale_date', '>=', $startOfMonth)
            ->count();

        // Customer metrics
        $totalCustomers = customers::whereIn('business_id', $businessIds)->count();
        $newCustomersThisMonth = customers::whereIn('business_id', $businessIds)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Booking metrics
        $totalBookings = Booking::whereIn('business_id', $businessIds)->count();
        $pendingBookings = Booking::whereIn('business_id', $businessIds)
            ->where('status', 'pending')
            ->count();
        $completedBookings = Booking::whereIn('business_id', $businessIds)
            ->where('status', 'completed')
            ->count();

        // Calculate growth percentage
        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($thisMonthRevenue > 0 ? 100 : 0);

        return [
            'totalBusinesses' => $totalBusinesses,
            'activeBusinesses' => $activeBusinesses,
            'totalRevenue' => $totalRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'revenueGrowth' => $revenueGrowth,
            'totalSales' => $totalSales,
            'thisMonthSales' => $thisMonthSales,
            'totalCustomers' => $totalCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
            'totalBookings' => $totalBookings,
            'pendingBookings' => $pendingBookings,
            'completedBookings' => $completedBookings,
        ];
    }
}
