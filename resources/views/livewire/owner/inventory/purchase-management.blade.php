<div class="container-fluid px-6 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-4 mb-4 d-flex align-items-center justify-content-between">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-1 h2 fw-bold">Purchase Orders</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Purchase Orders</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('owner.inventory.purchases.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus me-2"></i>New Purchase Order
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Business</label>
                    <select wire:model.live="selectedBusiness" class="form-select">
                        <option value="">Select Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Outlet</label>
                    <select wire:model.live="selectedOutlet" class="form-select">
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="submitted">Submitted</option>
                        <option value="approved">Approved</option>
                        <option value="partially_received">Partially Received</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="PO Number or Supplier...">
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Expected Date</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr wire:key="po-{{ $po->id }}">
                                <td>
                                    <strong>{{ $po->po_number }}</strong>
                                </td>
                                <td>{{ $po->supplier->name ?? 'N/A' }}</td>
                                <td>{{ $po->order_date ? $po->order_date->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $po->expected_date ? $po->expected_date->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $po->items->count() }} item(s)
                                    </span>
                                </td>
                                <td class="fw-semibold">TSh {{ number_format($po->total_amount, 2) }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'submitted' => 'warning',
                                            'approved' => 'info',
                                            'partially_received' => 'primary',
                                            'received' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $statusColors[$po->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $po->status)) }}</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" wire:click="viewPurchaseOrder('{{ $po->id }}')" class="btn btn-outline-primary" title="View">
                                            <i class="ti ti-eye"></i>
                                        </button>

                                        @if($po->status !== 'received' && $po->status !== 'cancelled')
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    @if($po->status === 'draft')
                                                        <li><a class="dropdown-item" wire:click="updatePurchaseOrderStatus('{{ $po->id }}', 'submitted')">Submit for Approval</a></li>
                                                    @endif
                                                    @if($po->status === 'submitted')
                                                        <li><a class="dropdown-item" wire:click="updatePurchaseOrderStatus('{{ $po->id }}', 'approved')">Approve</a></li>
                                                    @endif
                                                    @if($po->status === 'approved')
                                                        <li><a class="dropdown-item" wire:click="updatePurchaseOrderStatus('{{ $po->id }}', 'partially_received')">Mark as Partially Received</a></li>
                                                    @endif
                                                    @if($po->status === 'partially_received')
                                                        <li><a class="dropdown-item" wire:click="updatePurchaseOrderStatus('{{ $po->id }}', 'received')">Mark as Fully Received</a></li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" wire:click="deletePurchaseOrder('{{ $po->id }}')" wire:confirm="Are you sure you want to delete this purchase order?">Delete</a></li>
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ti ti-file-invoice fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No purchase orders found</p>
                                    <a href="{{ route('owner.inventory.purchases.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i class="ti ti-plus me-1"></i>Create First Purchase Order
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>

    <!-- View Purchase Order Modal -->
    @if($showViewModal && $viewingPurchaseOrder)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Purchase Order Details - {{ $viewingPurchaseOrder->po_number }}</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- PO Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Supplier Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $viewingPurchaseOrder->supplier->name }}</p>
                                <p class="mb-1"><strong>Contact:</strong> {{ $viewingPurchaseOrder->supplier->phone ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1"><strong>Order Date:</strong> {{ $viewingPurchaseOrder->order_date->format('M d, Y') }}</p>
                                <p class="mb-1"><strong>Expected Date:</strong> {{ $viewingPurchaseOrder->expected_date ? $viewingPurchaseOrder->expected_date->format('M d, Y') : 'N/A' }}</p>
                                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $viewingPurchaseOrder->status)) }}</span></p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <h6 class="fw-bold mb-3">Order Items ({{ $viewingPurchaseOrder->items->count() }})</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty Ordered</th>
                                        <th>Qty Received</th>
                                        <th>Unit Cost</th>
                                        <th>Tax</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingPurchaseOrder->items as $item)
                                        <tr>
                                            <td>{{ $item->item->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($item->quantity_ordered, 2) }}</td>
                                            <td>{{ number_format($item->quantity_received, 2) }}</td>
                                            <td>TSh {{ number_format($item->unit_cost, 2) }}</td>
                                            <td>TSh {{ number_format($item->tax_amount, 2) }}</td>
                                            <td>TSh {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="5" class="text-end">Subtotal:</th>
                                        <th>TSh {{ number_format($viewingPurchaseOrder->subtotal, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Tax:</th>
                                        <th>TSh {{ number_format($viewingPurchaseOrder->tax_amount, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="5" class="text-end">Total Amount:</th>
                                        <th class="text-primary">TSh {{ number_format($viewingPurchaseOrder->total_amount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($viewingPurchaseOrder->notes)
                            <div class="mt-3">
                                <h6 class="fw-bold">Notes</h6>
                                <p>{{ $viewingPurchaseOrder->notes }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
