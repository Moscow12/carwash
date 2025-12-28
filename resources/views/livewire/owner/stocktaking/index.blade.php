<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Stocktaking</h3>
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
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Conducted By</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stocktakings as $stocktaking)
                        <tr>
                            <td>{{ $stocktaking->created_at->format('M d, Y') }}</td>
                            <td>{{ $stocktaking->item->name ?? '-' }}</td>
                            <td>{{ $stocktaking->quantity }}</td>
                            <td>{{ number_format($stocktaking->price) }}</td>
                            <td>{{ $stocktaking->user->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $stocktaking->stocktaking_status === 'received' ? 'success' : ($stocktaking->stocktaking_status === 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($stocktaking->stocktaking_status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No stocktaking records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $stocktakings->links() }}</div>
    </div>
</div>
