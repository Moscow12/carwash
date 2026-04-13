<div class="kitchen-screen-container" wire:poll.5s="loadOrders">
    <style>
        :root {
            --kds-queued: #6c757d;
            --kds-preparing: #ffc107;
            --kds-ready: #198754;
            --kds-urgent: #dc3545;
            --kds-bg: #1a1d20;
            --kds-card: #2d3238;
            --kds-text: #ffffff;
        }

        .kitchen-screen-container {
            min-height: 100vh;
            background: var(--kds-bg);
            color: var(--kds-text);
        }

        /* Header */
        .kds-header {
            background: var(--kds-card);
            padding: 15px 20px;
            border-bottom: 3px solid #000;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Filter Tabs */
        .kds-filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 0;
            flex-wrap: wrap;
        }

        .kds-tab {
            padding: 10px 20px;
            border: 2px solid #495057;
            background: var(--kds-card);
            color: var(--kds-text);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
            font-size: 16px;
        }

        .kds-tab:hover {
            background: #495057;
        }

        .kds-tab.active {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
        }

        /* Orders Grid */
        .kds-orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .kds-orders-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Order Card */
        .kds-order-card {
            background: var(--kds-card);
            border-radius: 12px;
            border: 3px solid #495057;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: all 0.3s;
        }

        .kds-order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }

        .kds-order-card.urgent {
            border-color: var(--kds-urgent);
            animation: pulse-urgent 2s infinite;
        }

        @keyframes pulse-urgent {
            0%, 100% { border-color: var(--kds-urgent); }
            50% { border-color: #ff6b6b; }
        }

        .kds-card-header {
            background: #343a40;
            padding: 15px;
            border-bottom: 2px solid #495057;
        }

        .kds-order-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .kds-order-number {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
        }

        .kds-time-badge {
            font-size: 18px;
            font-weight: bold;
            padding: 6px 12px;
            border-radius: 6px;
            background: var(--kds-queued);
        }

        .kds-time-badge.urgent {
            background: var(--kds-urgent);
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 50%, 100% { opacity: 1; }
            25%, 75% { opacity: 0.7; }
        }

        .kds-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #adb5bd;
        }

        .kds-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .kds-card-body {
            padding: 15px;
        }

        /* Item Row */
        .kds-item {
            background: #1a1d20;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid var(--kds-queued);
        }

        .kds-item.preparing {
            border-left-color: var(--kds-preparing);
        }

        .kds-item.ready {
            border-left-color: var(--kds-ready);
        }

        .kds-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .kds-item-name {
            font-size: 18px;
            font-weight: 600;
        }

        .kds-item-qty {
            font-size: 20px;
            font-weight: bold;
            color: #0dcaf0;
        }

        .kds-item-notes {
            background: #ffc107;
            color: #000;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .kds-item-station {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        }

        .kds-item-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .kds-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .kds-btn:hover {
            transform: scale(1.05);
        }

        .kds-btn:active {
            transform: scale(0.95);
        }

        .kds-btn-start {
            background: var(--kds-preparing);
            color: #000;
        }

        .kds-btn-ready {
            background: var(--kds-ready);
            color: #fff;
        }

        .kds-btn-served {
            background: #0d6efd;
            color: #fff;
        }

        .kds-btn-cancel {
            background: var(--kds-urgent);
            color: #fff;
            flex: 0 0 48px;
        }

        /* Status Badge */
        .kds-status-badge {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }

        .kds-status-badge.queued {
            background: var(--kds-queued);
        }

        .kds-status-badge.preparing {
            background: var(--kds-preparing);
            color: #000;
        }

        .kds-status-badge.ready {
            background: var(--kds-ready);
        }

        /* Empty State */
        .kds-empty {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .kds-empty i {
            font-size: 80px;
            margin-bottom: 20px;
            display: block;
        }

        /* Selectors */
        .kds-select {
            background: var(--kds-card);
            color: var(--kds-text);
            border: 2px solid #495057;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
        }

        .kds-select:focus {
            border-color: #0d6efd;
            outline: none;
        }
    </style>

    {{-- Header --}}
    <div class="kds-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-0">
                    <i class="ti ti-tools-kitchen-2 me-2"></i>
                    Kitchen Display System
                </h4>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Business Selection --}}
                @if(!empty($ownerBusinesses))
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0" style="white-space: nowrap;">
                        <i class="ti ti-building-store"></i> Business:
                    </label>
                    @if(count($ownerBusinesses) > 1)
                        <select wire:model.live="selectedBusiness" class="kds-select">
                            @foreach($ownerBusinesses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-primary">{{ reset($ownerBusinesses) }}</span>
                    @endif
                </div>
                @endif

                {{-- Outlet Selection --}}
                @if(!empty($availableOutlets))
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0" style="white-space: nowrap;">
                        <i class="ti ti-layout-grid"></i> Outlet:
                    </label>
                    @if(count($availableOutlets) > 1)
                        <select wire:model.live="selectedOutlet" class="kds-select">
                            @foreach($availableOutlets as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-info">{{ reset($availableOutlets) }}</span>
                    @endif
                </div>
                @endif

                <a href="{{ route('owner.dashboard') }}" class="btn btn-sm btn-outline-light">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="kds-filter-tabs">
                <button wire:click="$set('statusFilter', 'active')"
                        class="kds-tab {{ $statusFilter === 'active' ? 'active' : '' }}">
                    <i class="ti ti-flame me-1"></i> Active
                </button>
                <button wire:click="$set('statusFilter', 'queued')"
                        class="kds-tab {{ $statusFilter === 'queued' ? 'active' : '' }}">
                    <i class="ti ti-clock me-1"></i> Queued
                </button>
                <button wire:click="$set('statusFilter', 'preparing')"
                        class="kds-tab {{ $statusFilter === 'preparing' ? 'active' : '' }}">
                    <i class="ti ti-tool-kitchen me-1"></i> Preparing
                </button>
                <button wire:click="$set('statusFilter', 'ready')"
                        class="kds-tab {{ $statusFilter === 'ready' ? 'active' : '' }}">
                    <i class="ti ti-check me-1"></i> Ready
                </button>
                <button wire:click="$set('statusFilter', 'all')"
                        class="kds-tab {{ $statusFilter === 'all' ? 'active' : '' }}">
                    <i class="ti ti-list me-1"></i> All
                </button>
            </div>

            @if(!empty($availableStations))
            <div>
                <select wire:model.live="stationFilter" class="kds-select">
                    <option value="">All Stations</option>
                    @foreach($availableStations as $station)
                        <option value="{{ $station }}">{{ $station }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- No Business/Outlet Warning --}}
    @if(empty($ownerBusinesses))
        <div class="kds-empty">
            <i class="ti ti-alert-triangle"></i>
            <h5>No Businesses Assigned</h5>
            <p>You don't have access to any restaurant businesses. Please contact your administrator.</p>
        </div>
    @elseif(empty($availableOutlets))
        <div class="kds-empty">
            <i class="ti ti-layout-grid"></i>
            <h5>No Outlets Available</h5>
            <p>No outlets found for the selected business.</p>
        </div>
    @else
        {{-- Orders Grid --}}
        <div class="kds-orders-grid">
            @forelse($orders as $order)
                @php
                    $receivedAt = \Carbon\Carbon::parse($order['received_at']);
                    $elapsedSeconds = abs(now()->diffInSeconds($receivedAt));
                    $elapsedMinutes = floor($elapsedSeconds / 60);
                    $elapsedHours = floor($elapsedMinutes / 60);

                    if ($elapsedHours > 0) {
                        $timeDisplay = $elapsedHours . 'h ' . ($elapsedMinutes % 60) . 'm';
                        $isUrgent = true;
                    } elseif ($elapsedMinutes > 0) {
                        $remainingSeconds = $elapsedSeconds % 60;
                        $timeDisplay = $elapsedMinutes . 'm ' . $remainingSeconds . 's';
                        $isUrgent = $elapsedMinutes > 15;
                    } else {
                        $timeDisplay = $elapsedSeconds . 's';
                        $isUrgent = false;
                    }
                @endphp
                <div class="kds-order-card {{ $isUrgent ? 'urgent' : '' }}">
                    <div class="kds-card-header">
                        <div class="kds-order-info">
                            <div class="kds-order-number">
                                {{ $order['order_no'] }}
                            </div>
                            <div class="kds-time-badge {{ $isUrgent ? 'urgent' : '' }}">
                                {{ $timeDisplay }}
                            </div>
                        </div>
                        <div class="kds-meta">
                            @if($order['order_type'] === 'dine_in')
                            <div class="kds-meta-item">
                                <i class="ti ti-armchair"></i>
                                Table {{ $order['table_number'] }}
                            </div>
                            @else
                            <div class="kds-meta-item">
                                <i class="ti ti-package"></i>
                                {{ ucfirst($order['order_type']) }}
                            </div>
                            @endif
                            <div class="kds-meta-item">
                                <i class="ti ti-users"></i>
                                {{ $order['covers'] }} pax
                            </div>
                            <div class="kds-meta-item">
                                <i class="ti ti-user"></i>
                                {{ $order['waiter'] }}
                            </div>
                        </div>
                    </div>
                    <div class="kds-card-body">
                        @foreach($order['items'] as $item)
                            @php
                                // Calculate item elapsed time or turnaround time
                                if ($item['status'] === 'served' && $item['served_at']) {
                                    // Show turnaround time (received to served)
                                    $itemReceivedAt = \Carbon\Carbon::parse($item['received_at']);
                                    $itemServedAt = \Carbon\Carbon::parse($item['served_at']);
                                    $itemElapsedSeconds = abs($itemServedAt->diffInSeconds($itemReceivedAt));
                                } else {
                                    // Show current elapsed time
                                    $itemReceivedAt = \Carbon\Carbon::parse($item['received_at']);
                                    $itemElapsedSeconds = abs(now()->diffInSeconds($itemReceivedAt));
                                }

                                $itemElapsedMinutes = floor($itemElapsedSeconds / 60);
                                $itemElapsedHours = floor($itemElapsedMinutes / 60);

                                if ($itemElapsedHours > 0) {
                                    $itemTimeDisplay = $itemElapsedHours . 'h ' . ($itemElapsedMinutes % 60) . 'm';
                                } elseif ($itemElapsedMinutes > 0) {
                                    $itemRemainingSeconds = $itemElapsedSeconds % 60;
                                    $itemTimeDisplay = $itemElapsedMinutes . 'm ' . $itemRemainingSeconds . 's';
                                } else {
                                    $itemTimeDisplay = $itemElapsedSeconds . 's';
                                }
                            @endphp
                            <div class="kds-item {{ $item['status'] }}">
                                <div class="kds-item-header">
                                    <div class="kds-item-name">{{ $item['item_name'] }}</div>
                                    <div class="kds-item-qty">{{ $item['quantity'] }}x</div>
                                </div>

                                @if($item['kitchen_notes'])
                                <div class="kds-item-notes">
                                    <i class="ti ti-note me-1"></i>{{ $item['kitchen_notes'] }}
                                </div>
                                @endif

                                <div class="kds-item-station">
                                    <i class="ti ti-map-pin me-1"></i>{{ $item['station'] }}
                                    @if($item['status'] === 'served')
                                        <span class="badge bg-success ms-2">
                                            <i class="ti ti-clock-check"></i> TAT: {{ $itemTimeDisplay }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary ms-2">
                                            <i class="ti ti-clock"></i> {{ $itemTimeDisplay }}
                                        </span>
                                    @endif
                                </div>

                                <div class="kds-item-actions">
                                    @if($item['status'] === 'queued')
                                        <button wire:click="startPreparing('{{ $item['ticket_id'] }}')"
                                                class="kds-btn kds-btn-start">
                                            <i class="ti ti-tool-kitchen"></i> Start Preparing
                                        </button>
                                        <button wire:click="cancelTicket('{{ $item['ticket_id'] }}')"
                                                class="kds-btn kds-btn-cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    @elseif($item['status'] === 'preparing')
                                        <button wire:click="markReady('{{ $item['ticket_id'] }}')"
                                                class="kds-btn kds-btn-ready">
                                            <i class="ti ti-check"></i> Mark Ready
                                        </button>
                                        <button wire:click="cancelTicket('{{ $item['ticket_id'] }}')"
                                                class="kds-btn kds-btn-cancel">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    @elseif($item['status'] === 'ready')
                                        <button wire:click="markServed('{{ $item['ticket_id'] }}')"
                                                class="kds-btn kds-btn-served">
                                            <i class="ti ti-check-circle"></i> Mark Served
                                        </button>
                                    @else
                                        <span class="kds-status-badge {{ $item['status'] }}">
                                            {{ ucfirst($item['status']) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="kds-empty" style="grid-column: 1 / -1;">
                    <i class="ti ti-checklist"></i>
                    <h5>No Orders</h5>
                    <p>All clear! No pending orders in the kitchen.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
