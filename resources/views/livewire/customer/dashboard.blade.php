<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Welcome, {{ Auth::user()->name }}</h3>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="ti ti-calendar fs-3 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Bookings</h6>
                            <h3 class="mb-0">{{ $totalBookings }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="ti ti-clock fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">{{ $pendingBookings }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="ti ti-check fs-3 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0">{{ $completedBookings }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <a href="{{ route('customer.carwashes') }}" class="card border-0 shadow-sm text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="ti ti-car-garage fs-1 text-primary mb-3"></i>
                    <h5>Browse Carwashes</h5>
                    <p class="text-muted mb-0">Find and book services at nearby carwashes</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('customer.bookings') }}" class="card border-0 shadow-sm text-decoration-none">
                <div class="card-body text-center py-4">
                    <i class="ti ti-calendar-event fs-1 text-success mb-3"></i>
                    <h5>My Bookings</h5>
                    <p class="text-muted mb-0">View and manage your bookings</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Recent Bookings</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Carwash</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                        <tr>
                            <td>{{ $booking->carwash->name ?? '-' }}</td>
                            <td>{{ $booking->item->name ?? '-' }}</td>
                            <td>{{ $booking->booking_date->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $booking->status === 'completed' ? 'success' : ($booking->status === 'pending' ? 'warning' : ($booking->status === 'confirmed' ? 'info' : 'danger')) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No bookings yet. <a href="{{ route('customer.carwashes') }}">Browse carwashes</a> to make your first booking!</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
