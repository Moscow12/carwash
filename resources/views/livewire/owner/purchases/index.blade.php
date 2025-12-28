<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Purchases</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($carwashes as $carwash)
                        <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Supplier</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                            <td>{{ $purchase->item->name ?? '-' }}</td>
                            <td>{{ $purchase->supplier->name ?? '-' }}</td>
                            <td>{{ $purchase->quantity }}</td>
                            <td>{{ number_format($purchase->price) }}</td>
                            <td>
                                <span class="badge bg-{{ $purchase->purchase_status === 'received' ? 'success' : ($purchase->purchase_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($purchase->purchase_status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $purchases->links() }}</div>
    </div>
</div>
