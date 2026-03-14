<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Communication Log</h4>
            <p class="text-muted mb-0">Track all guest communications (Email, SMS, Calls)</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-message-plus me-1"></i> Log Communication
        </button>
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

    <!-- Hotel Selection -->
    @if($hotels->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedHotel" class="form-select">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-message"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Communications</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-mail"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Emails</h6>
                                <h3 class="mb-0">{{ $stats['emails'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-message-2"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">SMS</h6>
                                <h3 class="mb-0">{{ $stats['sms'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-phone"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Phone Calls</h6>
                                <h3 class="mb-0">{{ $stats['calls'] }}</h3>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search guest name, subject...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Communication Type</label>
                        <select wire:model.live="filterType" class="form-select">
                            <option value="">All Types</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="phone_call">Phone Call</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="letter">Letter</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="delivered">Delivered</option>
                            <option value="read">Read</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Communications Timeline -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Communication History</h6>
            </div>
            <div class="card-body">
                @if($communications->count() > 0)
                    <div class="timeline">
                        @foreach($communications as $comm)
                            <div class="timeline-item">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <!-- Timeline content here -->
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-message-x fs-1 text-muted"></i>
                        <h5 class="mt-3">No Communications Found</h5>
                        <p class="text-muted">Start logging guest communications to track all interactions</p>
                        <button wire:click="openModal" class="btn btn-primary mt-2">
                            <i class="ti ti-message-plus me-1"></i> Log First Communication
                        </button>
                    </div>

                    <!-- Sample Communication Types -->
                    <div class="row g-3 mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Communication Types You Can Track:</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-info text-white me-3">
                                    <i class="ti ti-mail"></i>
                                </div>
                                <div>
                                    <h6>Email</h6>
                                    <small class="text-muted">Booking confirmations, thank you notes, promotions</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-success text-white me-3">
                                    <i class="ti ti-message-2"></i>
                                </div>
                                <div>
                                    <h6>SMS</h6>
                                    <small class="text-muted">Check-in reminders, room ready notifications</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-warning text-white me-3">
                                    <i class="ti ti-phone"></i>
                                </div>
                                <div>
                                    <h6>Phone Call</h6>
                                    <small class="text-muted">Reservation inquiries, complaint resolution</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-primary text-white me-3">
                                    <i class="ti ti-brand-whatsapp"></i>
                                </div>
                                <div>
                                    <h6>WhatsApp</h6>
                                    <small class="text-muted">Quick responses, image sharing</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-secondary text-white me-3">
                                    <i class="ti ti-mail-opened"></i>
                                </div>
                                <div>
                                    <h6>Letter</h6>
                                    <small class="text-muted">Formal correspondence, welcome letters</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-dark text-white me-3">
                                    <i class="ti ti-dots"></i>
                                </div>
                                <div>
                                    <h6>Other</h6>
                                    <small class="text-muted">In-person conversations, notes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Information Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Communication Log Benefits</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Track all guest interactions in one place</li>
                            <li>Maintain complete communication history</li>
                            <li>Improve guest service with context</li>
                            <li>Monitor response times and follow-ups</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Resolve disputes with documented evidence</li>
                            <li>Analyze communication patterns</li>
                            <li>Train staff with real examples</li>
                            <li>Ensure compliance and record-keeping</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view communication log</p>
            </div>
        </div>
    @endif

    <!-- Log Communication Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-message-plus me-2"></i>
                            Log Communication
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Guest <span class="text-danger">*</span></label>
                                    <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                        <option value="">-- Select Guest --</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }} - {{ $guest->email }}</option>
                                        @endforeach
                                    </select>
                                    @error('guest_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Reservation (Optional)</label>
                                    <select wire:model="reservation_id" class="form-select">
                                        <option value="">-- Not Related to Reservation --</option>
                                        @foreach($reservations as $reservation)
                                            <option value="{{ $reservation->id }}">
                                                {{ $reservation->reservation_no }} - {{ $reservation->guest->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Communication Type <span class="text-danger">*</span></label>
                                    <select wire:model="communication_type" class="form-select @error('communication_type') is-invalid @enderror">
                                        <option value="email">📧 Email</option>
                                        <option value="sms">📱 SMS</option>
                                        <option value="phone_call">📞 Phone Call</option>
                                        <option value="whatsapp">💬 WhatsApp</option>
                                        <option value="letter">✉️ Letter</option>
                                        <option value="other">📝 Other</option>
                                    </select>
                                    @error('communication_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Direction <span class="text-danger">*</span></label>
                                    <select wire:model="direction" class="form-select @error('direction') is-invalid @enderror">
                                        <option value="outbound">📤 Outbound (We sent)</option>
                                        <option value="inbound">📥 Inbound (Guest sent)</option>
                                    </select>
                                    @error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Subject / Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="subject" class="form-control @error('subject') is-invalid @enderror" placeholder="e.g., Booking Confirmation #123">
                                    @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Message / Content <span class="text-danger">*</span></label>
                                    <textarea wire:model="message" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="Enter the communication content or summary..."></textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="sent">Sent</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="read">Read</option>
                                        <option value="failed">Failed</option>
                                        <option value="pending">Pending</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Communication Date & Time</label>
                                    <input type="datetime-local" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Internal Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any internal notes about this communication..."></textarea>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="ti ti-info-circle me-2"></i>
                                <small><strong>Note:</strong> This communication will be logged and visible in the guest's communication history.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i> Log Communication
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
