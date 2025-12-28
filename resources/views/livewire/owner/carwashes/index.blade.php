<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">My Carwashes</h3>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search carwashes...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Region</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carwashes as $carwash)
                        <tr>
                            <td>{{ $carwash->name }}</td>
                            <td>{{ $carwash->address }}</td>
                            <td>{{ $carwash->regions->name ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $carwash->status === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($carwash->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No carwashes found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-transparent">
            {{ $carwashes->links() }}
        </div>
    </div>
</div>
