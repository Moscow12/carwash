<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Stocktaking</h3>
            <p class="text-muted mb-0">Count your products and approve to update item balances</p>
        </div>
        <button wire:click="startCount" class="btn btn-primary" @disabled(!$selectedBusiness)>
            <i class="ti ti-clipboard-plus me-1"></i>
            {{ $stats['drafts'] > 0 && $selectedBusiness ? 'Continue Draft Count' : 'New Stock Count' }}
        </button>
    </div>

    {{-- Stats Cards --}}
    @php
        $cards = [
            ['label' => 'Total Counts', 'value' => number_format($stats['total']), 'icon' => 'clipboard-list', 'color' => 'primary'],
            ['label' => 'Drafts', 'value' => number_format($stats['drafts']), 'icon' => 'file-pencil', 'color' => 'warning'],
            ['label' => 'Approved', 'value' => number_format($stats['approved']), 'icon' => 'circle-check', 'color' => 'success'],
            ['label' => 'Last Approved', 'value' => $stats['last_approved_at'] ? \Illuminate\Support\Carbon::parse($stats['last_approved_at'])->format('d M Y') : '-', 'icon' => 'calendar-check', 'color' => 'info'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($cards as $card)
            <div class="col-md-3" wire:key="stat-{{ $card['label'] }}">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }} rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ti ti-{{ $card['icon'] }} fs-4"></i>
                            </div>
                            <div>
                                <div class="text-muted small">{{ $card['label'] }}</div>
                                <div class="h4 mb-0">{{ $card['value'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search by reference...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="selectedBusiness">
                        @foreach($ownerBusinesses as $business)
                            <option value="{{ $business->id }}" wire:key="business-{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="approved">Approved</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <span class="text-muted small">{{ $stocks->total() }} count(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock Counts Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0" style="width: 50px;">#</th>
                        <th class="border-0">Reference</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 text-end">Items Counted</th>
                        <th class="border-0 text-end">Net Variance</th>
                        <th class="border-0">Counted By</th>
                        <th class="border-0">Approved By</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $num=1;
                    @endphp
                    @forelse($stocks as $stock)
                        @php($netVariance = (float) ($stock->items_sum_variance ?? 0))
                        <tr wire:key="stock-{{ $stock->id }}">
                            <td class="text-center">{{ $num++ }}</td>
                            <td>
                                <div class="fw-medium">{{ $stock->reference_no }}</div>
                                @if($stock->notes)
                                    <small class="text-muted">{{ Str::limit($stock->notes, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium">{{ $stock->stock_date->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $stock->business?->name }}</small>
                            </td>
                            <td class="text-end fw-medium">{{ number_format($stock->items_count) }}</td>
                            <td class="text-end fw-medium {{ $netVariance > 0 ? 'text-success' : ($netVariance < 0 ? 'text-danger' : 'text-muted') }}">
                                {{ $netVariance > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($netVariance, 3), '0'), '.') }}
                            </td>
                            <td>{{ $stock->createdBy?->name ?? '-' }}</td>
                            <td>
                                {{ $stock->approvedBy?->name ?? '-' }}
                                @if($stock->approved_at)
                                    <small class="text-muted d-block">{{ $stock->approved_at->format('M d, Y h:i A') }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($stock->isDraft())
                                    <span class="badge bg-warning-subtle text-warning">Draft</span>
                                @else
                                    <span class="badge bg-success-subtle text-success">Approved</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('owner.stocktaking.sheet', $stock) }}" class="btn btn-outline-primary" title="{{ $stock->isDraft() ? 'Continue' : 'View' }}">
                                        <i class="ti ti-{{ $stock->isDraft() ? 'edit' : 'eye' }}"></i>
                                    </a>
                                    @if($stock->isDraft())
                                        <button wire:click="deleteDraft('{{ $stock->id }}')" wire:confirm="Delete this draft stock count?" class="btn btn-outline-danger" title="Delete draft">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                    <i class="ti ti-clipboard-list fs-1"></i>
                                </div>
                                <h6 class="text-muted">No stock counts found</h6>
                                <p class="text-muted small mb-3">Start a stock count to record the current balance of your products</p>
                                <button wire:click="startCount" class="btn btn-primary btn-sm" @disabled(!$selectedBusiness)>
                                    <i class="ti ti-clipboard-plus me-1"></i> New Stock Count
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocks->hasPages())
            <div class="card-footer bg-transparent">
                {{ $stocks->links() }}
            </div>
        @endif
    </div>
</div>
