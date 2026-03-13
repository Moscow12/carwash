<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\sales;
use App\Models\staffs;
use App\Models\customers;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    public $totalBusinesses = 0;
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $totalStaff = 0;
    public $totalCustomers = 0;
    public $todaySales = 0;
    public $todayRevenue = 0;
    public $recentSales = [];

    public function mount()
    {
        $owner = Auth::user();
        $businessIds = $owner->ownedBusinesses()->pluck('id');

        $this->totalBusinesses = $businessIds->count();
        $this->totalSales = sales::whereIn('business_id', $businessIds)->count();
        $this->totalRevenue = sales::whereIn('business_id', $businessIds)->sum('total_amount');
        $this->totalStaff = staffs::whereIn('business_id', $businessIds)->count();
        $this->totalCustomers = customers::whereIn('business_id', $businessIds)->count();

        // Today's stats
        $this->todaySales = sales::whereIn('business_id', $businessIds)
            ->whereDate('sale_date', today())
            ->count();
        $this->todayRevenue = sales::whereIn('business_id', $businessIds)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $this->recentSales = sales::whereIn('business_id', $businessIds)
            ->with(['items.item', 'business', 'customer'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
