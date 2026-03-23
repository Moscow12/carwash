<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\BarTab;

#[Layout('components.layouts.app-owner')]
class BarTabs extends Component
{
    use WithPagination;

    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $search = '';
    public $statusFilter = 'all'; // all, open, closed

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            // Get first bar outlet
            $barOutlet = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->first();

            if ($barOutlet) {
                $this->selectedOutlet = $barOutlet->id;
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $tabs = collect();
        $stats = [
            'open_tabs' => 0,
            'total_balance' => 0,
            'closed_today' => 0,
            'avg_tab_value' => 0,
        ];

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        if ($this->selectedOutlet) {
            // Statistics
            $allTabs = BarTab::where('outlet_id', $this->selectedOutlet);

            $stats['open_tabs'] = (clone $allTabs)->where('status', 'open')->count();
            $stats['total_balance'] = (clone $allTabs)->where('status', 'open')->sum('balance');
            $stats['closed_today'] = (clone $allTabs)
                ->where('status', 'closed')
                ->whereDate('updated_at', today())
                ->count();

            $openTabsBalances = (clone $allTabs)->where('status', 'open')->pluck('balance');
            $stats['avg_tab_value'] = $openTabsBalances->count() > 0 ? $openTabsBalances->avg() : 0;

            // Tabs Query
            $tabsQuery = BarTab::where('outlet_id', $this->selectedOutlet)
                ->with(['guest.reservations.room']);

            if ($this->search) {
                $tabsQuery->where(function($q) {
                    $q->where('tab_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('guest', function($subQ) {
                          $subQ->where('first_name', 'like', '%' . $this->search . '%')
                               ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->statusFilter !== 'all') {
                $tabsQuery->where('status', $this->statusFilter);
            }

            $tabs = $tabsQuery->orderByDesc('created_at')->paginate(20);
        }

        return view('livewire.owner.bar.bar-tabs', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'tabs' => $tabs,
            'stats' => $stats,
        ]);
    }
}
