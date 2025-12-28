<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Items & Services</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search items...">
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
                            <th>Name</th>
                            <th>Type</th>
                            <th>Selling Price</th>
                            <th>Carwash</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td><span class="badge bg-info">{{ $item->type }}</span></td>
                            <td>{{ number_format($item->selling_price) }}</td>
                            <td>{{ $item->carwash->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"><i class="ti ti-eye"></i></button>
                                <button class="btn btn-sm btn-outline-secondary"><i class="ti ti-edit"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No items found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">{{ $items->links() }}</div>
    </div>
</div>
