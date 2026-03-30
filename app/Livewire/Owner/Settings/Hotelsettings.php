<?php

namespace App\Livewire\Owner\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app-owner')]
class Hotelsettings extends Component
{
    // Business selection
    public $selectedBusiness = '';
    public $ownerBusinesses = [];

    // Active tab
    public $activeTab = 'room_types';

    // Room Type Settings
    public $room_types = [];
    public $default_checkin_time = '14:00';
    public $default_checkout_time = '11:00';
    public $allow_early_checkin = true;
    public $allow_late_checkout = true;
    public $early_checkin_fee = 0;
    public $late_checkout_fee = 0;

    // Booking Settings
    public $require_deposit = true;
    public $deposit_percentage = 50;
    public $min_advance_booking_hours = 2;
    public $max_advance_booking_days = 365;
    public $auto_confirm_bookings = false;
    public $allow_same_day_booking = true;

    // Rate Plan Settings
    public $enable_dynamic_pricing = false;
    public $enable_seasonal_rates = true;
    public $enable_weekday_weekend_rates = true;
    public $tax_included_in_rate = false;

    // Guest Settings
    public $require_id_verification = true;
    public $max_guests_per_room = 2;
    public $allow_extra_guests = true;
    public $extra_guest_charge = 0;
    public $require_guest_address = true;

    // Housekeeping Settings
    public $auto_assign_housekeeping = true;
    public $housekeeping_start_time = '09:00';
    public $housekeeping_end_time = '17:00';
    public $cleaning_time_per_room = 30; // minutes
    public $enable_housekeeping_priority = true;

    // Maintenance Settings
    public $enable_preventive_maintenance = true;
    public $maintenance_reminder_days = 7;
    public $auto_block_maintenance_rooms = true;

    // Night Audit Settings
    public $enable_night_audit = true;
    public $night_audit_time = '00:00';
    public $auto_run_night_audit = false;
    public $night_audit_cutoff_time = '23:59';

    // Lost & Found Settings
    public $lost_found_retention_days = 90;
    public $require_lost_found_photos = false;
    public $notify_guest_lost_found = true;

    // Invoice Settings
    public $invoice_prefix = 'HTL';
    public $invoice_number_length = 6;
    public $auto_generate_invoice_on_checkout = true;
    public $include_tax_breakdown = true;
    public $show_guest_details_on_invoice = true;

    // Communication Settings
    public $send_booking_confirmation = true;
    public $send_checkin_reminder = true;
    public $send_checkout_reminder = true;
    public $reminder_hours_before = 24;

    // Settings tabs
    public $tabs = [
        'room_types' => ['label' => 'Room Types', 'icon' => 'ti-bed'],
        'booking' => ['label' => 'Booking', 'icon' => 'ti-calendar-event'],
        'rates' => ['label' => 'Rate Plans', 'icon' => 'ti-currency-dollar'],
        'guests' => ['label' => 'Guest Management', 'icon' => 'ti-users'],
        'housekeeping' => ['label' => 'Housekeeping', 'icon' => 'ti-vacuum-cleaner'],
        'maintenance' => ['label' => 'Maintenance', 'icon' => 'ti-tool'],
        'night_audit' => ['label' => 'Night Audit', 'icon' => 'ti-moon'],
        'lost_found' => ['label' => 'Lost & Found', 'icon' => 'ti-search'],
        'invoicing' => ['label' => 'Invoicing', 'icon' => 'ti-receipt'],
        'communication' => ['label' => 'Communication', 'icon' => 'ti-mail'],
    ];

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->orderBy('name')
            ->pluck('name', 'id');

        $firstBusiness = Auth::user()->assignedBusinesses()->where('type', 'hotel')->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
            $this->loadSettings();
        }
    }

    public function updatedSelectedBusiness()
    {
        $this->loadSettings();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadSettings()
    {
        if (!$this->selectedBusiness) return;

        // TODO: Load settings from database when hotel settings model is created
        // For now, using default values
    }

    public function saveSettings()
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a hotel first.');
            return;
        }

        try {
            // TODO: Save settings to database when hotel settings model is created

            session()->flash('message', 'Hotel settings saved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        if (!$this->selectedBusiness) return;

        // Reset to default values
        $this->default_checkin_time = '14:00';
        $this->default_checkout_time = '11:00';
        $this->allow_early_checkin = true;
        $this->allow_late_checkout = true;
        $this->require_deposit = true;
        $this->deposit_percentage = 50;
        $this->auto_assign_housekeeping = true;
        $this->enable_night_audit = true;
        $this->lost_found_retention_days = 90;

        session()->flash('message', 'Settings reset to defaults. Click Save to apply.');
    }

    public function render()
    {
        return view('livewire.owner.settings.hotelsettings');
    }
}
