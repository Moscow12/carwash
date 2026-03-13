<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Hotel Management Settings</h4>
            <p class="text-muted mb-0">Configure your hotel operations and preferences</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if(count($ownerBusinesses) > 1)
                <div style="width: 200px;">
                    <x-forms.select2
                        name="selectedBusiness"
                        :options="collect($ownerBusinesses)"
                        wire:model.live="selectedBusiness"
                        wrapper="false"
                    />
                </div>
            @endif
            <button wire:click="resetToDefaults" class="btn btn-outline-secondary">
                <i class="ti ti-refresh me-1"></i> Reset to Defaults
            </button>
            <button wire:click="saveSettings" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Save Settings
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Sidebar - Tabs -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        @foreach($tabs as $key => $tab)
                            <li class="nav-item" role="presentation">
                                <button
                                    wire:click="setTab('{{ $key }}')"
                                    class="nav-link text-start w-100 {{ $activeTab === $key ? 'active' : '' }}"
                                    type="button"
                                >
                                    <i class="ti {{ $tab['icon'] }} me-2"></i>
                                    {{ $tab['label'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Room Types Settings -->
                    @if($activeTab === 'room_types')
                        <h5 class="card-title mb-4"><i class="ti ti-bed me-2"></i>Room Type Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Default Check-in Time</label>
                                <input type="time" wire:model="default_checkin_time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Check-out Time</label>
                                <input type="time" wire:model="default_checkout_time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="allow_early_checkin" id="allowEarlyCheckin">
                                    <label class="form-check-label" for="allowEarlyCheckin">Allow Early Check-in</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="allow_late_checkout" id="allowLateCheckout">
                                    <label class="form-check-label" for="allowLateCheckout">Allow Late Check-out</label>
                                </div>
                            </div>
                            @if($allow_early_checkin)
                                <div class="col-md-6">
                                    <label class="form-label">Early Check-in Fee (TZS)</label>
                                    <input type="number" wire:model="early_checkin_fee" class="form-control" min="0" step="1000">
                                </div>
                            @endif
                            @if($allow_late_checkout)
                                <div class="col-md-6">
                                    <label class="form-label">Late Check-out Fee (TZS)</label>
                                    <input type="number" wire:model="late_checkout_fee" class="form-control" min="0" step="1000">
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Booking Settings -->
                    @if($activeTab === 'booking')
                        <h5 class="card-title mb-4"><i class="ti ti-calendar-event me-2"></i>Booking Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="require_deposit" id="requireDeposit">
                                    <label class="form-check-label" for="requireDeposit">Require Deposit for Booking</label>
                                </div>
                            </div>
                            @if($require_deposit)
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Percentage (%)</label>
                                    <input type="number" wire:model="deposit_percentage" class="form-control" min="0" max="100">
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Minimum Advance Booking (Hours)</label>
                                <input type="number" wire:model="min_advance_booking_hours" class="form-control" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Advance Booking (Days)</label>
                                <input type="number" wire:model="max_advance_booking_days" class="form-control" min="1">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_confirm_bookings" id="autoConfirm">
                                    <label class="form-check-label" for="autoConfirm">Auto-Confirm Bookings</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="allow_same_day_booking" id="sameDayBooking">
                                    <label class="form-check-label" for="sameDayBooking">Allow Same-Day Booking</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Rate Plans Settings -->
                    @if($activeTab === 'rates')
                        <h5 class="card-title mb-4"><i class="ti ti-currency-dollar me-2"></i>Rate Plan Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_dynamic_pricing" id="dynamicPricing">
                                    <label class="form-check-label" for="dynamicPricing">Enable Dynamic Pricing</label>
                                </div>
                                <small class="text-muted">Automatically adjust prices based on demand</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_seasonal_rates" id="seasonalRates">
                                    <label class="form-check-label" for="seasonalRates">Enable Seasonal Rates</label>
                                </div>
                                <small class="text-muted">Different pricing for peak/off-peak seasons</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_weekday_weekend_rates" id="weekendRates">
                                    <label class="form-check-label" for="weekendRates">Weekday/Weekend Rates</label>
                                </div>
                                <small class="text-muted">Different pricing for weekdays and weekends</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="tax_included_in_rate" id="taxIncluded">
                                    <label class="form-check-label" for="taxIncluded">Tax Included in Rate</label>
                                </div>
                                <small class="text-muted">Show prices with tax included</small>
                            </div>
                        </div>
                    @endif

                    <!-- Guest Management Settings -->
                    @if($activeTab === 'guests')
                        <h5 class="card-title mb-4"><i class="ti ti-users me-2"></i>Guest Management Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="require_id_verification" id="idVerification">
                                    <label class="form-check-label" for="idVerification">Require ID Verification</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="require_guest_address" id="guestAddress">
                                    <label class="form-check-label" for="guestAddress">Require Guest Address</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Maximum Guests per Room</label>
                                <input type="number" wire:model="max_guests_per_room" class="form-control" min="1">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" wire:model="allow_extra_guests" id="extraGuests">
                                    <label class="form-check-label" for="extraGuests">Allow Extra Guests</label>
                                </div>
                            </div>
                            @if($allow_extra_guests)
                                <div class="col-md-6">
                                    <label class="form-label">Extra Guest Charge (TZS per night)</label>
                                    <input type="number" wire:model="extra_guest_charge" class="form-control" min="0" step="1000">
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Housekeeping Settings -->
                    @if($activeTab === 'housekeeping')
                        <h5 class="card-title mb-4"><i class="ti ti-vacuum-cleaner me-2"></i>Housekeeping Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_assign_housekeeping" id="autoAssign">
                                    <label class="form-check-label" for="autoAssign">Auto-Assign Housekeeping Tasks</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_housekeeping_priority" id="priorityEnabled">
                                    <label class="form-check-label" for="priorityEnabled">Enable Priority Levels</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Housekeeping Start Time</label>
                                <input type="time" wire:model="housekeeping_start_time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Housekeeping End Time</label>
                                <input type="time" wire:model="housekeeping_end_time" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cleaning Time per Room (minutes)</label>
                                <input type="number" wire:model="cleaning_time_per_room" class="form-control" min="10" max="120">
                            </div>
                        </div>
                    @endif

                    <!-- Maintenance Settings -->
                    @if($activeTab === 'maintenance')
                        <h5 class="card-title mb-4"><i class="ti ti-tool me-2"></i>Maintenance Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_preventive_maintenance" id="preventiveMaintenance">
                                    <label class="form-check-label" for="preventiveMaintenance">Enable Preventive Maintenance</label>
                                </div>
                                <small class="text-muted">Schedule regular maintenance tasks</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_block_maintenance_rooms" id="autoBlock">
                                    <label class="form-check-label" for="autoBlock">Auto-Block Rooms Under Maintenance</label>
                                </div>
                                <small class="text-muted">Prevent bookings for rooms under maintenance</small>
                            </div>
                            @if($enable_preventive_maintenance)
                                <div class="col-md-6">
                                    <label class="form-label">Maintenance Reminder (Days Before)</label>
                                    <input type="number" wire:model="maintenance_reminder_days" class="form-control" min="1" max="30">
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Night Audit Settings -->
                    @if($activeTab === 'night_audit')
                        <h5 class="card-title mb-4"><i class="ti ti-moon me-2"></i>Night Audit Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="enable_night_audit" id="enableNightAudit">
                                    <label class="form-check-label" for="enableNightAudit">Enable Night Audit</label>
                                </div>
                            </div>
                            @if($enable_night_audit)
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="auto_run_night_audit" id="autoRun">
                                        <label class="form-check-label" for="autoRun">Auto-Run Night Audit</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Night Audit Time</label>
                                    <input type="time" wire:model="night_audit_time" class="form-control">
                                    <small class="text-muted">Time to run the night audit process</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Audit Cutoff Time</label>
                                    <input type="time" wire:model="night_audit_cutoff_time" class="form-control">
                                    <small class="text-muted">Transactions after this time go to next day</small>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Lost & Found Settings -->
                    @if($activeTab === 'lost_found')
                        <h5 class="card-title mb-4"><i class="ti ti-search me-2"></i>Lost & Found Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Item Retention Period (Days)</label>
                                <input type="number" wire:model="lost_found_retention_days" class="form-control" min="30" max="365">
                                <small class="text-muted">How long to keep lost & found items</small>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" wire:model="require_lost_found_photos" id="requirePhotos">
                                    <label class="form-check-label" for="requirePhotos">Require Photos</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="notify_guest_lost_found" id="notifyGuest">
                                    <label class="form-check-label" for="notifyGuest">Notify Guest When Item Found</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Invoicing Settings -->
                    @if($activeTab === 'invoicing')
                        <h5 class="card-title mb-4"><i class="ti ti-receipt me-2"></i>Invoicing Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Prefix</label>
                                <input type="text" wire:model="invoice_prefix" class="form-control" maxlength="5">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number Length</label>
                                <input type="number" wire:model="invoice_number_length" class="form-control" min="4" max="10">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_generate_invoice_on_checkout" id="autoInvoice">
                                    <label class="form-check-label" for="autoInvoice">Auto-Generate Invoice on Check-out</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="include_tax_breakdown" id="taxBreakdown">
                                    <label class="form-check-label" for="taxBreakdown">Include Tax Breakdown</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="show_guest_details_on_invoice" id="guestDetails">
                                    <label class="form-check-label" for="guestDetails">Show Guest Details on Invoice</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Communication Settings -->
                    @if($activeTab === 'communication')
                        <h5 class="card-title mb-4"><i class="ti ti-mail me-2"></i>Communication Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="send_booking_confirmation" id="bookingConfirm">
                                    <label class="form-check-label" for="bookingConfirm">Send Booking Confirmation</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="send_checkin_reminder" id="checkinReminder">
                                    <label class="form-check-label" for="checkinReminder">Send Check-in Reminder</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="send_checkout_reminder" id="checkoutReminder">
                                    <label class="form-check-label" for="checkoutReminder">Send Check-out Reminder</label>
                                </div>
                            </div>
                            @if($send_checkin_reminder || $send_checkout_reminder)
                                <div class="col-md-6">
                                    <label class="form-label">Send Reminder (Hours Before)</label>
                                    <input type="number" wire:model="reminder_hours_before" class="form-control" min="1" max="72">
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
