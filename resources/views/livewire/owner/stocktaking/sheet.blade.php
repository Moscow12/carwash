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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">
                Stock Count
                @if($referenceNo)
                    <span class="text-muted fs-5">{{ $referenceNo }}</span>
                @endif
            </h3>
            <p class="text-muted mb-0">
                @if($isReadOnly)
                    <span class="badge bg-success-subtle text-success me-1">Approved</span>
                    Approved {{ $stock?->approved_at?->format('d M Y H:i') }} by {{ $stock?->approvedBy?->name ?? '-' }}
                @elseif($stockId)
                    <span class="badge bg-warning-subtle text-warning me-1">Draft</span>
                    Balances update only after you approve this count.
                @else
                    Count your products and enter the current balance. Save a draft any time.
                @endif
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.stocktaking') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back
            </a>
            @unless($isReadOnly)
                <button wire:click="saveDraft" wire:loading.attr="disabled" class="btn btn-outline-primary">
                    <span wire:loading.remove wire:target="saveDraft"><i class="ti ti-device-floppy me-1"></i> Save Draft</span>
                    <span wire:loading wire:target="saveDraft"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                </button>
                <button wire:click="approve"
                        wire:confirm="Approve this stock count? Item balances will be adjusted to the counted quantities and the count can no longer be edited."
                        wire:loading.attr="disabled"
                        class="btn btn-success">
                    <span wire:loading.remove wire:target="approve"><i class="ti ti-circle-check me-1"></i> Approve & Post</span>
                    <span wire:loading wire:target="approve"><span class="spinner-border spinner-border-sm me-1"></span> Posting...</span>
                </button>
            @endunless
        </div>
    </div>

    {{-- Filters & Summary --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <select wire:model.live="business_id" class="form-select" @disabled($isReadOnly || $stockId)>
                        @foreach($ownerBusinesses as $business)
                            <option value="{{ $business->id }}" wire:key="business-{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search products...">
                    </div>
                </div>
                <div class="col-md-4 d-flex flex-wrap gap-2 justify-content-md-end">
                    <span class="badge bg-primary-subtle text-primary py-2 px-3">{{ $summary['total'] }} products</span>
                    <span class="badge bg-success-subtle text-success py-2 px-3">{{ $summary['counted'] }} counted</span>
                    <span class="badge bg-secondary-subtle text-secondary py-2 px-3">{{ $summary['remaining'] }} not counted</span>
                </div>
            </div>
        </div>
    </div>

    @error('counts.*')
        <div class="alert alert-danger py-2">{{ $message }}</div>
    @enderror

    {{-- Count Sheet --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th class="border-0" style="width: 50px;">#</th>
                        <th class="border-0">
                            <button type="button" wire:click="toggleSort" class="btn btn-link p-0 text-reset fw-semibold text-decoration-none">
                                Item
                                <i class="ti ti-sort-{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }}-letters ms-1"></i>
                                <small class="text-muted fw-normal">{{ $sortDirection === 'asc' ? 'A–Z' : 'Z–A' }}</small>
                            </button>
                        </th>
                        <th class="border-0">Unit</th>
                        <th class="border-0 text-end">Previous Balance</th>
                        <th class="border-0 text-end" style="width: 180px;">Current Balance</th>
                        <th class="border-0 text-end">Variance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $previous = $balances[$product->id] ?? 0;
                            $count = $counts[$product->id] ?? null;
                            $isCounted = $count !== null && trim((string) $count) !== '' && is_numeric($count);
                            $variance = $isCounted ? (float) $count - $previous : null;
                        @endphp
                        <tr wire:key="stock-row-{{ $product->id }}" class="{{ $isCounted ? '' : 'text-muted' }}">
                            <td><span class="badge bg-light text-dark">{{ $loop->iteration }}</span></td>
                            <td>
                                <div class="fw-medium text-body">{{ $product->name }}</div>
                                <small class="text-muted">{{ $product->category?->name ?? '-' }}</small>
                            </td>
                            <td>{{ $product->unit?->name ?? '-' }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($previous, 3), '0'), '.') }}</td>
                            <td class="text-end">
                                <input type="number"
                                       step="any"
                                       min="0"
                                       inputmode="decimal"
                                       wire:model.blur="counts.{{ $product->id }}"
                                       class="form-control form-control-sm text-end @error('counts.'.$product->id) is-invalid @enderror"
                                       placeholder="Enter count"
                                       @disabled($isReadOnly)>
                            </td>
                            <td class="text-end fw-medium">
                                @if($variance === null)
                                    <span class="text-muted">-</span>
                                @elseif($variance > 0)
                                    <span class="text-success">+{{ rtrim(rtrim(number_format($variance, 3), '0'), '.') }}</span>
                                @elseif($variance < 0)
                                    <span class="text-danger">{{ rtrim(rtrim(number_format($variance, 3), '0'), '.') }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                {{ $search ? 'No products match your search.' : 'No active products found for this business.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Notes --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <label class="form-label fw-medium">Notes</label>
            <textarea wire:model.blur="notes" rows="2" class="form-control @error('notes') is-invalid @enderror" placeholder="Optional notes about this stock count" @disabled($isReadOnly)></textarea>
            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @unless($isReadOnly)
                <small class="text-muted d-block mt-2">
                    <i class="ti ti-info-circle me-1"></i>
                    Leave a product blank to skip it. Only counted products are adjusted when the count is approved.
                </small>
            @endunless
        </div>
    </div>
</div>
