<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Sales</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search by plate number...">
                </div>
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
                            <th>Carwash</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ $sale->carwash->name ?? '-' }}</td>
                            <td>{{ $sale->item->name ?? '-' }}</td>
                            <td>{{ $sale->customer->name ?? '-' }}</td>
                            <td>{{ number_format($sale->price) }}</td>
                            <td>
                                <span class="badge bg-{{ $sale->payment_status === 'paid' ? 'success' : ($sale->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale->payment_status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $sales->links() }}</div>
    </div>
</div>
