<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Wakeup Calls</h4>
            <p class="text-muted mb-0">Schedule and manage guest wakeup calls</p>
        </div>
        @if($selectedBusiness)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Schedule Call
            </button>
        @endif
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

    <!-- Business Selection -->
    @if($businesses->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Select Hotel</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($selectedBusiness)
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-calendar-time"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Scheduled</h6>
                                <h3 class="mb-0">{{ $stats['scheduled'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Delivered Today</h6>
                                <h3 class="mb-0">{{ $stats['delivered_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-danger text-white me-3">
                                <i class="ti ti-alert-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Missed Today</h6>
                                <h3 class="mb-0">{{ $stats['missed_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pending Today</h6>
                                <h3 class="mb-0">{{ $stats['pending_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Guest name, room number...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="all">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="delivered">Delivered</option>
                            <option value="missed">Missed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" wire:model.live="filterDate" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Wakeup Calls Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Guest & Room</th>
                                <th>Scheduled Time</th>
                                <th>Repeat</th>
                                <th>Status</th>
                                <th>Delivered By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($calls as $call)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $call->guest->full_name }}</div>
                                        @if($call->room)
                                            <small class="text-muted">
                                                <i class="ti ti-door me-1"></i>Room {{ $call->room->room_number }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="ti ti-alarm me-1"></i>{{ \Carbon\Carbon::parse($call->scheduled_at)->format('M d, Y h:i A') }}</div>
                                        @if($call->notes)
                                            <small class="text-muted">{{ $call->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($call->repeat_daily)
                                            <span class="badge bg-info"><i class="ti ti-repeat me-1"></i>Daily</span>
                                        @else
                                            <span class="text-muted">One-time</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($call->status === 'scheduled')
                                            <span class="badge bg-primary">Scheduled</span>
                                        @elseif($call->status === 'delivered')
                                            <span class="badge bg-success">Delivered</span>
                                        @elseif($call->status === 'missed')
                                            <span class="badge bg-danger">Missed</span>
                                        @else
                                            <span class="badge bg-secondary">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($call->deliveredBy)
                                            <div>{{ $call->deliveredBy->name }}</div>
                                            <small class="text-muted">{{ $call->delivered_at?->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($call->status === 'scheduled')
                                                <button wire:click="markDelivered('{{ $call->id }}')" class="btn btn-success" title="Mark Delivered">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button wire:click="markMissed('{{ $call->id }}')" class="btn btn-danger" title="Mark Missed">
                                                    <i class="ti ti-alert-circle"></i>
                                                </button>
                                                <button wire:click="editCall('{{ $call->id }}')" class="btn btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button wire:click="cancelCall('{{ $call->id }}')" class="btn btn-secondary" title="Cancel">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="ti ti-alarm-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No wakeup calls found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $calls->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-building fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage wakeup calls</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-alarm me-2"></i>
                            {{ $editMode ? 'Edit Wakeup Call' : 'Schedule Wakeup Call' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveCall">
                            <div class="row g-3">
                                <!-- Guest Information -->
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-user me-2"></i>Guest Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guest <span class="text-danger">*</span></label>
                                    <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                        <option value="">-- Select Guest --</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('guest_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room</label>
                                    <select wire:model="room_id" class="form-select">
                                        <option value="">-- Select Room (Optional) --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Reservation</label>
                                    <select wire:model="reservation_id" class="form-select">
                                        <option value="">-- Select Reservation (Optional) --</option>
                                        @foreach($reservations as $reservation)
                                            <option value="{{ $reservation->id }}">
                                                {{ $reservation->reservation_no }} - {{ $reservation->guest->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Call Details -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-clock me-2"></i>Call Details</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Scheduled Date & Time <span class="text-danger">*</span></label>
                                    <input type="datetime-local" wire:model="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror">
                                    @error('scheduled_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="repeat_daily" class="form-check-input" id="repeatDaily">
                                        <label class="form-check-label" for="repeatDaily">
                                            <i class="ti ti-repeat me-1"></i>Repeat Daily
                                        </label>
                                    </div>
                                    <small class="text-muted">Auto-schedule for next day after delivery</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="3" placeholder="Special instructions or notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>{{ $editMode ? 'Update' : 'Schedule' }} Call
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
