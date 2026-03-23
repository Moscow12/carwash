<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="ti ti-receipt me-2"></i>Bar Tabs Management</h4>
                <div class="d-flex gap-2">
                    <select wire:model.live="selectedBusiness" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Select Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @if($outlets->count() > 0)
                        <select wire:model.live="selectedOutlet" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Select Outlet</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Open Tabs</h6>
                        <h3 class="mb-0">{{ $stats['open_tabs'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Total Balance</h6>
                        <h3 class="mb-0">TSh {{ number_format($stats['total_balance'], 0) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Closed Today</h6>
                        <h3 class="mb-0">{{ $stats['closed_today'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Avg Tab Value</h6>
                        <h3 class="mb-0">TSh {{ number_format($stats['avg_tab_value'], 0) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search by tab number or customer...">
            </div>
            <div class="col-md-4">
                <select wire:model.live="statusFilter" class="form-select">
                    <option value="all">All Tabs</option>
                    <option value="open">Open Tabs</option>
                    <option value="closed">Closed Tabs</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tab Number</th>
                                        <th>Customer</th>
                                        <th>Room</th>
                                        <th>Balance</th>
                                        <th>Paid</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tabs as $tab)
                                        <tr>
                                            <td><strong>{{ $tab->tab_number }}</strong></td>
                                            <td>
                                                @if($tab->guest)
                                                    {{ $tab->guest->first_name }} {{ $tab->guest->last_name }}
                                                @elseif($tab->customer_name)
                                                    {{ $tab->customer_name }}
                                                @else
                                                    Walk-in Customer
                                                @endif
                                            </td>
                                            <td>
                                                @if($tab->guest && $tab->guest->reservations->count() > 0)
                                                    @php
                                                        $activeReservation = $tab->guest->reservations->where('status', 'checked_in')->first()
                                                            ?? $tab->guest->reservations->first();
                                                    @endphp
                                                    {{ $activeReservation?->room?->room_number ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td><strong>TSh {{ number_format($tab->balance, 0) }}</strong></td>
                                            <td>TSh {{ number_format($tab->paid_amount ?? 0, 0) }}</td>
                                            <td>
                                                @if($tab->status === 'open')
                                                    <span class="badge bg-warning">Open</span>
                                                @elseif($tab->status === 'closed')
                                                    <span class="badge bg-success">Closed</span>
                                                @elseif($tab->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small>{{ $tab->created_at->format('M d, Y h:i A') }}</small>
                                            </td>
                                            <td>
                                                @if($tab->status === 'open')
                                                    <a href="{{ route('owner.bar.pos') }}?tab={{ $tab->id }}" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-plus"></i> Add Items
                                                    </a>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="ti ti-eye"></i> View
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="ti ti-inbox fs-3 d-block mb-2"></i>
                                                No tabs found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($tabs->hasPages())
                            <div class="mt-3">
                                {{ $tabs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($stats['open_tabs'] > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        You have <strong>{{ $stats['open_tabs'] }}</strong> open tab(s) with a total balance of <strong>TSh {{ number_format($stats['total_balance'], 0) }}</strong>
                    </div>
                </div>
            </div>
        @endif

    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please select a business and outlet to view tabs.
                </div>
            </div>
        </div>
    @endif
</div>
