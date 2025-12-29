<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Expenses</h4>
            <p class="text-muted mb-0">Manage your business expenses</p>
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

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Expenses</p>
                            <h3 class="mb-0">{{ number_format($totalExpenses) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Amount</p>
                            <h3 class="mb-0">TZS {{ number_format($totalAmount) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Paid</p>
                            <h3 class="mb-0">TZS {{ number_format($totalPaid) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Pending</p>
                            <h3 class="mb-0">TZS {{ number_format($totalPending) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h6 class="mb-0 text-primary"><i class="ti ti-filter me-2"></i>Filters</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Business Location:</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All locations</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Expense for:</label>
                    <select wire:model.live="expenseForFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($staffs as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Contact:</label>
                    <select wire:model.live="contactFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Expense Category:</label>
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Sub category:</label>
                    <select wire:model.live="subcategoryFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($subcategories as $subcategory)
                            <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Date Range:</label>
                    <div class="input-group">
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                        <span class="input-group-text">-</span>
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Payment Status:</label>
                    <select wire:model.live="paymentStatusFilter" class="form-select">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All expenses</h5>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add
            </button>
        </div>
        <div class="card-body">
            <!-- Table Controls -->
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Show</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Action</th>
                            <th>Date</th>
                            <th>Reference No</th>
                            <th>Recurring</th>
                            <th>Category</th>
                            <th>Sub category</th>
                            <th>Location</th>
                            <th>Payment Status</th>
                            <th>Tax</th>
                            <th class="text-end">Total amount</th>
                            <th class="text-end">Payment due</th>
                            <th>Expense for</th>
                            <th>Contact</th>
                            <th>Note</th>
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button class="dropdown-item" wire:click="edit('{{ $expense->id }}')">
                                                    <i class="ti ti-edit me-2"></i> Edit
                                                </button>
                                            </li>
                                            @if($expense->payment_status !== 'paid')
                                                <li>
                                                    <button class="dropdown-item text-success" wire:click="markAsPaid('{{ $expense->id }}')">
                                                        <i class="ti ti-check me-2"></i> Mark as Paid
                                                    </button>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" wire:click="delete('{{ $expense->id }}')" wire:confirm="Are you sure you want to delete this expense?">
                                                    <i class="ti ti-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                                <td>{{ $expense->expense_date->format('m/d/Y') }}<br><small class="text-muted">{{ $expense->created_at->format('H:i') }}</small></td>
                                <td><span class="fw-medium">{{ $expense->reference_no }}</span></td>
                                <td>{{ $expense->recurring_display }}</td>
                                <td>{{ $expense->category?->name ?? '-' }}</td>
                                <td>{{ $expense->subcategory?->name ?? '-' }}</td>
                                <td>{{ $expense->carwash?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $expense->payment_status_badge_class }}">
                                        {{ ucfirst($expense->payment_status) }}
                                    </span>
                                </td>
                                <td>TZS {{ number_format($expense->tax_amount, 2) }}</td>
                                <td class="text-end fw-bold">TZS {{ number_format($expense->total_amount, 2) }}</td>
                                <td class="text-end">TZS {{ number_format($expense->payment_due, 2) }}</td>
                                <td>{{ $expense->expense_for_display }}</td>
                                <td>{{ $expense->contact_display }}</td>
                                <td><small>{{ Str::limit($expense->expense_note, 30) }}</small></td>
                                <td>{{ $expense->added_by_display }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-receipt-off fs-1 d-block mb-2"></i>
                                        No expenses found
                                        <br>
                                        <button wire:click="openModal" class="btn btn-primary btn-sm mt-2">
                                            <i class="ti ti-plus me-1"></i> Add Expense
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($expenses instanceof \Illuminate\Pagination\LengthAwarePaginator && $expenses->count() > 0)
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="7" class="text-end fw-bold">Total:</td>
                                <td>Paid - {{ $paidCount }}</td>
                                <td></td>
                                <td class="text-end fw-bold">TZS {{ number_format($grandTotal, 2) }}</td>
                                <td class="text-end fw-bold">TZS {{ number_format($grandPaymentDue, 2) }}</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($expenses instanceof \Illuminate\Pagination\LengthAwarePaginator && $expenses->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} entries
                </div>
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Expense Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-receipt me-2"></i>
                            {{ $editingId ? 'Edit Expense' : 'Add Expense' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="expense_date" class="form-control @error('expense_date') is-invalid @enderror">
                                @error('expense_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference No</label>
                                <input type="text" wire:model="reference_no" class="form-control" placeholder="Auto-generated if empty">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expense Category</label>
                                <select wire:model.live="category_id" class="form-select">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sub Category</label>
                                <select wire:model="subcategory_id" class="form-select">
                                    <option value="">Select Sub Category</option>
                                    @foreach($subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="total_amount" class="form-control @error('total_amount') is-invalid @enderror" placeholder="0.00" step="0.01">
                                </div>
                                @error('total_amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tax Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="tax_amount" class="form-control" placeholder="0.00" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                                <select wire:model="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                    <option value="pending">Pending</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                </select>
                                @error('payment_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expense For (Staff)</label>
                                <select wire:model="expense_for_id" class="form-select">
                                    <option value="">Select Staff</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" wire:model="expense_for" class="form-control mt-2" placeholder="Or enter name manually">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact (Supplier)</label>
                                <select wire:model="contact_id" class="form-select">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" wire:model="contact" class="form-control mt-2" placeholder="Or enter contact manually">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Expense Note</label>
                                <textarea wire:model="expense_note" class="form-control" rows="2" placeholder="Enter expense details..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" wire:model.live="is_recurring" class="form-check-input" id="is_recurring">
                                    <label class="form-check-label" for="is_recurring">This is a recurring expense</label>
                                </div>
                            </div>
                            @if($is_recurring)
                                <div class="col-md-6">
                                    <label class="form-label">Recurring Interval</label>
                                    <select wire:model="recurring_interval" class="form-select">
                                        <option value="">Select Interval</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="yearly">Yearly</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Occurrences</label>
                                    <input type="number" wire:model="recurring_count" class="form-control" placeholder="Leave empty for unlimited">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
