<div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">My Bookings</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Carwash</th>
                            <th>Service</th>
                            <th>Booking Date</th>
                            <th>Plate Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->carwash->name ?? '-' }}</td>
                            <td>{{ $booking->item->name ?? '-' }}</td>
                            <td>{{ $booking->booking_date->format('M d, Y H:i') }}</td>
                            <td>{{ $booking->plate_number ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'pending' ? 'warning' : ($booking->status === 'confirmed' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td>
                                @if($booking->status === 'pending')
                                <button wire:click="cancelBooking('{{ $booking->id }}')"
                                        wire:confirm="Are you sure you want to cancel this booking?"
                                        class="btn btn-sm btn-outline-danger">
                                    Cancel
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No bookings found. <a href="{{ route('customer.carwashes') }}">Browse carwashes</a> to make a booking.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $bookings->links() }}
        </div>
    </div>
</div>
