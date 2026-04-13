<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Guest Documents</h4>
            <p class="text-muted mb-0">Manage guest identification and document uploads</p>
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
                                <i class="ti ti-users"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Guests</h6>
                                <h3 class="mb-0">{{ $stats['total_guests'] }}</h3>
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
                                <i class="ti ti-file-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">With Documents</h6>
                                <h3 class="mb-0">{{ $stats['with_documents'] }}</h3>
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
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="mb-0">{{ $stats['pending_documents'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-danger text-white me-3">
                                <i class="ti ti-alert-triangle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Expiring Soon</h6>
                                <h3 class="mb-0">{{ $stats['expiring_soon'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, email, or document number...">
            </div>
        </div>

        <!-- Recent Check-ins Needing Documents -->
        @if($reservations->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-white">
                    <h6 class="mb-0">
                        <i class="ti ti-alert-circle me-2"></i>
                        Recent Reservations - Document Upload Needed
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Document Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reservations as $reservation)
                                    <tr>
                                        <td>
                                            <div>{{ $reservation->guest->full_name }}</div>
                                            <small class="text-muted">{{ $reservation->guest->email }}</small>
                                        </td>
                                        <td>
                                            @if($reservation->room)
                                                <span class="badge bg-primary">Room {{ $reservation->room->number }}</span>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td>{{ $reservation->check_in_date->format('M d, Y') }}</td>
                                        <td>{{ $reservation->check_out_date->format('M d, Y') }}</td>
                                        <td>
                                            @if($reservation->guest->id_number)
                                                <span class="badge bg-success">
                                                    <i class="ti ti-check me-1"></i>Uploaded
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="ti ti-clock me-1"></i>Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <button wire:click="openModal('{{ $reservation->guest->id }}', '{{ $reservation->id }}')"
                                                    class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-upload"></i> Upload
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- All Guests with Documents -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Guest Documents Directory</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Contact</th>
                                <th>Nationality</th>
                                <th>Document Type</th>
                                <th>Document Number</th>
                                <th>Stays</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guests as $guest)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary me-2">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $guest->full_name }}</div>
                                                @if($guest->vip_level !== 'regular')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ti ti-crown"></i> {{ ucfirst($guest->vip_level) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="ti ti-mail me-1"></i>{{ $guest->email }}</div>
                                        <div><i class="ti ti-phone me-1"></i>{{ $guest->phone }}</div>
                                    </td>
                                    <td>
                                        @if($guest->nationality)
                                            <span class="badge bg-light text-dark">{{ $guest->nationality }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($guest->id_type)
                                            @php
                                                $typeColors = [
                                                    'passport' => 'primary',
                                                    'national_id' => 'info',
                                                    'drivers_license' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $typeColors[$guest->id_type] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $guest->id_type)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">Not Set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($guest->id_number)
                                            <code>{{ $guest->id_number }}</code>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $guest->reservations_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button wire:click="openModal('{{ $guest->id }}')"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Upload/Update Document">
                                                <i class="ti ti-upload"></i>
                                            </button>
                                            @if($guest->id_number)
                                                <button class="btn btn-sm btn-outline-success" title="View Document">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="ti ti-file-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No guests found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $guests->links() }}
                </div>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Document Management Guidelines</h6>
                <ul class="mb-0">
                    <li>All guests must provide valid identification documents upon check-in</li>
                    <li>Accepted document types: Passport, National ID, Driver's License, Visa</li>
                    <li>Documents must be valid and not expired</li>
                    <li>Uploaded files are stored securely and encrypted</li>
                    <li>Maximum file size: 5MB (PDF, JPG, JPEG, PNG formats)</li>
                    <li>Keep track of document expiry dates for international guests</li>
                </ul>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage guest documents</p>
            </div>
        </div>
    @endif

    <!-- Upload Document Modal -->
    @if($showModal && $selectedGuest)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-file-upload me-2"></i>
                            Upload Document - {{ $selectedGuest->full_name }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Guest Info -->
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Email:</strong> {{ $selectedGuest->email }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Phone:</strong> {{ $selectedGuest->phone }}
                                </div>
                            </div>
                        </div>

                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select wire:model="document_type" class="form-select @error('document_type') is-invalid @enderror">
                                        <option value="passport">Passport</option>
                                        <option value="national_id">National ID</option>
                                        <option value="drivers_license">Driver's License</option>
                                        <option value="visa">Visa</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('document_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Document Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="document_number" class="form-control @error('document_number') is-invalid @enderror" placeholder="e.g., A12345678">
                                    @error('document_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Issue Date</label>
                                    <input type="date" wire:model="issue_date" class="form-control @error('issue_date') is-invalid @enderror">
                                    @error('issue_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" wire:model="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror">
                                    @error('expiry_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Issuing Country</label>
                                    <input type="text" wire:model="issuing_country" class="form-control" placeholder="e.g., USA">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Upload Document Scan</label>
                                    <input type="file" wire:model="document_file" class="form-control @error('document_file') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('document_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Accepted formats: PDF, JPG, JPEG, PNG (Max: 5MB)</small>

                                    @if($document_file)
                                        <div class="mt-2 alert alert-success">
                                            <i class="ti ti-check me-2"></i>
                                            File selected: {{ $document_file->getClientOriginalName() }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any additional notes about the document..."></textarea>
                                </div>
                            </div>

                            <!-- Current Document Info -->
                            @if($selectedGuest->id_type && $selectedGuest->id_number)
                                <div class="alert alert-warning mt-3">
                                    <strong>Current Document on File:</strong><br>
                                    Type: {{ ucfirst(str_replace('_', ' ', $selectedGuest->id_type)) }}<br>
                                    Number: {{ $selectedGuest->id_number }}
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-upload me-1"></i> Upload Document
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
