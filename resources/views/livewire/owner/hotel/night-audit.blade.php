<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Night Audit</h4>
            <p class="text-muted mb-0">End-of-day financial reconciliation and reporting</p>
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
        <!-- Today's Live Metrics -->
        <div class="card shadow-sm mb-4 border-start border-primary border-4">
            <div class="card-body">
                <h5 class="text-primary mb-3"><i class="ti ti-chart-line me-2"></i>Today's Live Metrics</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h2 class="mb-1">{{ $todayMetrics['occupied_rooms'] }}</h2>
                            <p class="text-muted mb-0">Occupied Rooms</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h2 class="mb-1">{{ number_format($todayMetrics['occupancy_rate'], 1) }}%</h2>
                            <p class="text-muted mb-0">Occupancy Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h2 class="mb-1">{{ number_format($todayMetrics['total_revenue'], 2) }}</h2>
                            <p class="text-muted mb-0">Total Revenue</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h2 class="mb-1">{{ number_format($todayMetrics['adr'], 2) }}</h2>
                            <p class="text-muted mb-0">ADR</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Run Night Audit -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-3"><i class="ti ti-calendar-event me-2"></i>Run Night Audit</h5>
                <div class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label">Select Audit Date</label>
                        <input type="date" wire:model="selectedDate" class="form-control">
                        <small class="text-muted">Select the date for which you want to run the night audit</small>
                    </div>
                    <div class="col-md-4">
                        <button wire:click="runNightAudit" class="btn btn-primary w-100">
                            <i class="ti ti-player-play me-1"></i> Run Audit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit History -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-history me-2"></i>Audit History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Occupancy</th>
                                <th>Room Revenue</th>
                                <th>F&B Revenue</th>
                                <th>Total Revenue</th>
                                <th>ADR</th>
                                <th>RevPAR</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audits as $audit)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">{{ $audit->audit_date->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $audit->occupied_rooms }}</strong> / {{ $audit->total_rooms }}
                                        </div>
                                        <small class="text-muted">{{ number_format($audit->occupancy_pct ?? 0, 1) }}%</small>
                                    </td>
                                    <td>{{ number_format($audit->room_revenue, 2) }}</td>
                                    <td>{{ number_format($audit->fb_revenue, 2) }}</td>
                                    <td><strong class="text-success">{{ number_format($audit->total_revenue, 2) }}</strong></td>
                                    <td>{{ number_format($audit->adr, 2) }}</td>
                                    <td>{{ number_format($audit->revpar, 2) }}</td>
                                    <td>
                                        <button wire:click="viewAudit('{{ $audit->id }}')"
                                                class="btn btn-sm btn-outline-primary"
                                                title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="ti ti-calendar-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No audit history found. Run your first night audit above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $audits->links() }}
                </div>
            </div>
        </div>

        <!-- Information Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary"><i class="ti ti-info-circle me-2"></i>About Night Audit</h6>
                <p class="mb-2"><strong>What is Night Audit?</strong></p>
                <p class="mb-3">Night audit is the end-of-day process that reconciles all financial transactions, room occupancy, and operational metrics for the hotel.</p>

                <p class="mb-2"><strong>Key Metrics:</strong></p>
                <ul class="mb-0">
                    <li><strong>ADR (Average Daily Rate):</strong> Room Revenue ÷ Occupied Rooms</li>
                    <li><strong>RevPAR (Revenue Per Available Room):</strong> Room Revenue ÷ Total Rooms</li>
                    <li><strong>Occupancy Rate:</strong> (Occupied Rooms ÷ Total Rooms) × 100</li>
                </ul>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to run night audit</p>
            </div>
        </div>
    @endif

    <!-- Audit Details Modal -->
    @if($showModal && !empty($auditData))
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-file-analytics me-2"></i>
                            Night Audit Details - {{ \Carbon\Carbon::parse($auditData['audit_date'])->format('M d, Y') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Occupancy Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Occupancy Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Total Rooms:</strong></p>
                                        <h4>{{ $auditData['total_rooms'] }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Occupied Rooms:</strong></p>
                                        <h4>{{ $auditData['occupied_rooms'] }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Occupancy Rate:</strong></p>
                                        <h4>{{ number_format($auditData['occupancy_pct'] ?? 0, 1) }}%</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Arrivals / Departures:</strong></p>
                                        <h4>{{ $auditData['new_arrivals'] ?? 0 }} / {{ $auditData['departures'] ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Section -->
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Revenue Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Room Revenue:</strong></p>
                                        <h4 class="text-primary">{{ number_format($auditData['room_revenue'], 2) }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>F&B Revenue:</strong></p>
                                        <h4 class="text-info">{{ number_format($auditData['fb_revenue'], 2) }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Other Revenue:</strong></p>
                                        <h4 class="text-warning">{{ number_format(($auditData['total_revenue'] ?? 0) - ($auditData['room_revenue'] ?? 0) - ($auditData['fb_revenue'] ?? 0), 2) }}</h4>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-1"><strong>Total Revenue:</strong></p>
                                        <h4 class="text-success">{{ number_format($auditData['total_revenue'], 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Metrics -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Financial Metrics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>ADR:</strong></p>
                                        <h4 class="text-primary">{{ number_format($auditData['adr'], 2) }}</h4>
                                        <small class="text-muted">Average Daily Rate</small>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>RevPAR:</strong></p>
                                        <h4 class="text-info">{{ number_format($auditData['revpar'], 2) }}</h4>
                                        <small class="text-muted">Revenue Per Available Room</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Placeholder -->
                        <div class="card">
                            <div class="card-body text-center py-4">
                                <i class="ti ti-chart-bar fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Revenue charts and graphs can be added here</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {
            /* Hide everything except modal content when printing */
            body * {
                visibility: hidden;
            }

            .modal-content, .modal-content * {
                visibility: visible;
            }

            .modal {
                position: absolute;
                left: 0;
                top: 0;
                margin: 0;
                padding: 0;
                min-height: 100vh;
                background: white !important;
            }

            .modal-dialog {
                max-width: 100% !important;
                margin: 0;
            }

            .modal-content {
                position: absolute;
                left: 0;
                top: 0;
                border: none !important;
                box-shadow: none !important;
            }

            /* Hide modal footer buttons when printing */
            .modal-footer {
                display: none !important;
            }

            /* Hide page header, sidebar, navbar */
            header, nav, .sidebar, .navbar, aside {
                display: none !important;
            }

            /* Optimize for print */
            .card {
                page-break-inside: avoid;
                border: 1px solid #dee2e6 !important;
            }

            /* Remove shadows and backgrounds for print */
            .shadow-sm {
                box-shadow: none !important;
            }
        }
    </style>
</div>
