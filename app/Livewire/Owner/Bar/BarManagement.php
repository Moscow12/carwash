<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\BarTab;
use App\Models\BarBottleService;
use App\Models\BarHappyHourPrice;
use App\Models\MenuItem;
use App\Models\Guest;

#[Layout('components.layouts.app-owner')]
class BarManagement extends Component
{
    use WithPagination;

    public $activeTab = 'tabs';
    public $search = '';
    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $showModal = false;
    public $editMode = false;

    // Bar Tab Properties
    public $tabId = null;

    #[Rule('required|string|max:50')]
    public $tab_name = '';

    #[Rule('nullable|exists:guests,id')]
    public $guest_id = null;

    #[Rule('nullable|exists:pos_tables,id')]
    public $table_id = null;

    #[Rule('nullable|exists:folios,id')]
    public $folio_id = null;

    // Happy Hour Properties
    public $happyHourId = null;

    #[Rule('required|exists:menu_items,id')]
    public $menu_item_id = null;

    #[Rule('required|in:percentage,fixed_price,fixed_discount')]
    public $discount_type = 'fixed_price';

    #[Rule('required|numeric|min:0')]
    public $discount_value = 0;

    #[Rule('required|date_format:H:i')]
    public $start_time = '';

    #[Rule('required|date_format:H:i')]
    public $end_time = '';

    #[Rule('nullable')]
    public $override_days = '';

    #[Rule('required|in:active,inactive')]
    public $happy_hour_status = 'active';

    // Bottle Service Properties
    public $bottleServiceId = null;

    #[Rule('required|exists:guests,id')]
    public $bottle_guest_id = null;

    #[Rule('required|numeric|min:0')]
    public $bottle_charge = 0;

    #[Rule('required|numeric|min:0|max:100')]
    public $consumption_percentage = 0;

    #[Rule('required|in:pending,delivered,consumed,returned')]
    public $bottle_status = 'pending';

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

        if (request()->has('tab')) {
            $this->activeTab = request()->get('tab');
        }
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->tabId = null;
        $this->tab_name = '';
        $this->guest_id = null;
        $this->table_id = null;
        $this->folio_id = null;

        $this->happyHourId = null;
        $this->menu_item_id = null;
        $this->discount_type = 'fixed_price';
        $this->discount_value = 0;
        $this->start_time = '';
        $this->end_time = '';
        $this->override_days = '';
        $this->happy_hour_status = 'active';

        $this->bottleServiceId = null;
        $this->bottle_guest_id = null;
        $this->bottle_charge = 0;
        $this->consumption_percentage = 0;
        $this->bottle_status = 'pending';

        $this->resetValidation();
    }

    // Bar Tab Methods
    public function saveTab()
    {
        $this->validate([
            'tab_name' => 'required|string|max:50',
            'guest_id' => 'nullable|exists:guests,id',
            'table_id' => 'nullable|exists:pos_tables,id',
            'folio_id' => 'nullable|exists:folios,id',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'tab_name' => $this->tab_name,
                'guest_id' => $this->guest_id,
                'table_id' => $this->table_id,
                'folio_id' => $this->folio_id,
                'status' => 'open',
                'opened_at' => now(),
                'opened_by' => Auth::id(),
            ];

            if ($this->editMode && $this->tabId) {
                BarTab::findOrFail($this->tabId)->update($data);
                session()->flash('message', 'Tab updated successfully.');
            } else {
                // Generate unique tab number
                $data['tab_no'] = $this->generateTabNumber();
                BarTab::create($data);
                session()->flash('message', 'Tab opened successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    private function generateTabNumber()
    {
        $prefix = 'TAB';
        $date = now()->format('Ymd');

        // Get last tab number for today
        $lastTab = BarTab::where('tab_no', 'like', $prefix . $date . '%')
            ->orderBy('tab_no', 'desc')
            ->first();

        if ($lastTab) {
            $lastNumber = (int) substr($lastTab->tab_no, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    public function editTab($id)
    {
        $tab = BarTab::findOrFail($id);

        $this->editMode = true;
        $this->tabId = $tab->id;
        $this->tab_name = $tab->tab_name;
        $this->guest_id = $tab->guest_id;
        $this->table_id = $tab->table_id;
        $this->folio_id = $tab->folio_id;

        $this->showModal = true;
    }

    public function closeTab($id)
    {
        try {
            $tab = BarTab::findOrFail($id);

            // Calculate total
            $total = $tab->orders()->sum('total_amount');

            $tab->update([
                'status' => 'closed',
                'total_amount' => $total,
                'closed_at' => now(),
                'closed_by' => Auth::id(),
            ]);

            session()->flash('message', 'Tab closed successfully. Total: TZS ' . number_format($total, 2));
        } catch (\Exception $e) {
            session()->flash('error', 'Close failed: ' . $e->getMessage());
        }
    }

    public function voidTab($id)
    {
        try {
            $tab = BarTab::findOrFail($id);
            $tab->update([
                'status' => 'voided',
                'voided_at' => now(),
                'voided_by' => Auth::id(),
            ]);

            session()->flash('message', 'Tab voided successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Void failed: ' . $e->getMessage());
        }
    }

    // Happy Hour Methods
    public function saveHappyHour()
    {
        $this->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'discount_type' => 'required|in:percentage,fixed_price,fixed_discount',
            'discount_value' => 'required|numeric|min:0',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'happy_hour_status' => 'required|in:active,inactive',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'menu_item_id' => $this->menu_item_id,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'override_days' => $this->override_days ? json_encode(explode(',', $this->override_days)) : null,
                'status' => $this->happy_hour_status,
            ];

            if ($this->editMode && $this->happyHourId) {
                BarHappyHourPrice::findOrFail($this->happyHourId)->update($data);
                session()->flash('message', 'Happy hour price updated successfully.');
            } else {
                BarHappyHourPrice::create($data);
                session()->flash('message', 'Happy hour price created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editHappyHour($id)
    {
        $happyHour = BarHappyHourPrice::findOrFail($id);

        $this->editMode = true;
        $this->happyHourId = $happyHour->id;
        $this->menu_item_id = $happyHour->menu_item_id;
        $this->discount_type = $happyHour->discount_type;
        $this->discount_value = $happyHour->discount_value;
        $this->start_time = $happyHour->start_time;
        $this->end_time = $happyHour->end_time;
        $this->override_days = $happyHour->override_days ? implode(',', json_decode($happyHour->override_days, true)) : '';
        $this->happy_hour_status = $happyHour->status;

        $this->showModal = true;
    }

    public function deleteHappyHour($id)
    {
        try {
            BarHappyHourPrice::findOrFail($id)->delete();
            session()->flash('message', 'Happy hour price deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->whereIn('type', ['hotel', 'restaurant', 'bar'])
            ->get();

        $outlets = collect();
        $tabs = collect();
        $happyHours = collect();
        $bottleServices = collect();
        $stats = [
            'open_tabs' => 0,
            'tabs_today' => 0,
            'revenue_today' => 0,
            'active_happy_hours' => 0,
        ];

        if ($this->selectedOutlet) {
            // Tabs
            $tabQuery = BarTab::where('outlet_id', $this->selectedOutlet);

            if ($this->search && $this->activeTab === 'tabs') {
                $tabQuery->where('tab_name', 'like', '%' . $this->search . '%');
            }

            $tabs = $tabQuery->with(['guest', 'table', 'session'])
                ->latest()
                ->paginate(15);

            // Happy Hours
            $happyHourQuery = BarHappyHourPrice::where('outlet_id', $this->selectedOutlet);

            if ($this->search && $this->activeTab === 'happy-hours') {
                $happyHourQuery->whereHas('menuItem', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            }

            $happyHours = $happyHourQuery->with('menuItem')
                ->latest()
                ->paginate(15);

            // Bottle Services
            $bottleServices = BarBottleService::whereHas('menuItem', function($q) {
                    $q->where('outlet_id', $this->selectedOutlet);
                })
                ->with(['guest', 'menuItem'])
                ->latest()
                ->paginate(15);

            // Statistics
            $stats['open_tabs'] = BarTab::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'open')->count();
            $stats['tabs_today'] = BarTab::where('outlet_id', $this->selectedOutlet)
                ->whereDate('created_at', today())->count();
            $stats['revenue_today'] = BarTab::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'closed')
                ->whereDate('closed_at', today())
                ->sum('total_amount');
            $stats['active_happy_hours'] = BarHappyHourPrice::where('outlet_id', $this->selectedOutlet)
                ->where('status', 'active')->count();
        }

        // Get all bar outlets for dropdown
        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)
                ->where('type', 'bar')
                ->get();
        }

        // Get menu items for happy hour dropdown (beverages only)
        $menuItems = MenuItem::where('outlet_id', $this->selectedOutlet)
            ->whereHas('category', function($q) {
                $q->where('name', 'like', '%beverage%')
                  ->orWhere('name', 'like', '%drink%')
                  ->orWhere('name', 'like', '%spirits%')
                  ->orWhere('name', 'like', '%beer%')
                  ->orWhere('name', 'like', '%wine%')
                  ->orWhere('name', 'like', '%cocktail%');
            })
            ->get();

        // Get guests for tab assignment
        $guests = Guest::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->get();

        return view('livewire.owner.bar.bar-management', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'tabs' => $tabs,
            'happyHours' => $happyHours,
            'bottleServices' => $bottleServices,
            'stats' => $stats,
            'menuItems' => $menuItems,
            'guests' => $guests,
        ]);
    }
}
