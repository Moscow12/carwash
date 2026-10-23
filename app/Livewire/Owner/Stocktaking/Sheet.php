<?php

namespace App\Livewire\Owner\Stocktaking;

use App\Models\item_balance;
use App\Models\items;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class Sheet extends Component
{
    public ?string $stockId = null;

    #[Url(as: 'business')]
    public $business_id = '';

    public $ownerBusinesses = [];

    public $search = '';

    public $sortDirection = 'asc';

    /** @var array<string, string|null> item_id => counted quantity */
    public $counts = [];

    public $notes = '';

    public bool $isReadOnly = false;

    public ?string $referenceNo = null;

    public function mount(?Stock $stock = null): void
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();
        $businessIds = $this->ownerBusinesses->pluck('id');

        if ($stock?->exists) {
            abort_unless($businessIds->contains($stock->business_id), 403);

            $this->business_id = $stock->business_id;
            $this->loadStock($stock);

            return;
        }

        if (! $businessIds->contains($this->business_id)) {
            $this->business_id = $businessIds->first() ?? '';
        }

        $this->loadOpenDraft();
    }

    public function updatedBusinessId(): void
    {
        if ($this->isReadOnly || ! $this->ownerBusinesses->pluck('id')->contains($this->business_id)) {
            return;
        }

        $this->reset(['stockId', 'counts', 'notes', 'referenceNo']);
        $this->loadOpenDraft();
    }

    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function saveDraft(): void
    {
        if (! $this->ensureEditable()) {
            return;
        }

        $this->validateCounts();

        $isNew = $this->stockId === null;
        $stock = DB::transaction(fn () => $this->persistDraft());

        session()->flash('message', 'Draft saved. Item balances will update once the stock count is approved.');

        if ($isNew) {
            $this->redirectRoute('owner.stocktaking.sheet', ['stock' => $stock->id]);
        }
    }

    public function approve(): void
    {
        if (! $this->ensureEditable()) {
            return;
        }

        $this->validateCounts();

        if ($this->enteredCounts()->isEmpty()) {
            session()->flash('error', 'Enter at least one counted quantity before approving.');

            return;
        }

        DB::transaction(function () {
            $stock = $this->persistDraft();
            $stock->load('items');

            $liveBalances = $this->latestBalances($stock->items->pluck('item_id')->all());

            foreach ($stock->items as $line) {
                $previous = $liveBalances[$line->item_id] ?? 0.0;
                $current = (float) $line->current_balance;
                $variance = round($current - $previous, 3);

                $line->update([
                    'previous_balance' => $previous,
                    'variance' => $variance,
                ]);

                if ($variance == 0) {
                    continue;
                }

                item_balance::create([
                    'item_id' => $line->item_id,
                    'user_id' => Auth::id(),
                    'business_id' => $stock->business_id,
                    'previous_balance' => $previous,
                    'current_balance' => $current,
                    'quantity_changed' => $variance,
                    'stock_type' => $variance > 0 ? 'in' : 'out',
                    'stransaction_type' => 'adjustment',
                    'movement_reason' => 'closing_count',
                    'invoice_number' => $stock->reference_no,
                ]);
            }

            $stock->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
        });

        session()->flash('message', 'Stock count approved and item balances updated.');
        $this->redirectRoute('owner.stocktaking');
    }

    private function loadStock(Stock $stock): void
    {
        $this->stockId = $stock->id;
        $this->referenceNo = $stock->reference_no;
        $this->notes = $stock->notes ?? '';
        $this->isReadOnly = ! $stock->isDraft();
        $this->counts = $stock->items()
            ->pluck('current_balance', 'item_id')
            ->map(fn ($value) => $this->formatQuantity((float) $value))
            ->all();
    }

    private function loadOpenDraft(): void
    {
        if (! $this->business_id) {
            return;
        }

        $draft = Stock::draft()->where('business_id', $this->business_id)->latest()->first();

        if ($draft) {
            $this->loadStock($draft);
        }
    }

    private function ensureEditable(): bool
    {
        if (! $this->ownerBusinesses->pluck('id')->contains($this->business_id)) {
            session()->flash('error', 'Invalid business selected.');

            return false;
        }

        if ($this->isReadOnly || ($this->stockId && ! Stock::draft()->whereKey($this->stockId)->exists())) {
            session()->flash('error', 'This stock count has already been approved and can no longer be changed.');

            return false;
        }

        return true;
    }

    private function validateCounts(): void
    {
        $this->validate([
            'counts.*' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'counts.*.numeric' => 'Counted quantity must be a number.',
            'counts.*.min' => 'Counted quantity cannot be negative.',
        ]);
    }

    /**
     * Counts the user actually filled in, limited to this business's active products.
     *
     * @return Collection<string, float>
     */
    private function enteredCounts(): Collection
    {
        $entered = collect($this->counts)
            ->reject(fn ($value) => $value === null || trim((string) $value) === '');

        $validIds = $this->productsQuery()->whereIn('id', $entered->keys())->pluck('id');

        return $entered->only($validIds->all())->map(fn ($value) => (float) $value);
    }

    /**
     * Create or update the draft and sync its lines with the entered counts.
     */
    private function persistDraft(): Stock
    {
        $stock = $this->stockId
            ? Stock::draft()->whereKey($this->stockId)->lockForUpdate()->firstOrFail()
            : Stock::create([
                'business_id' => $this->business_id,
                'reference_no' => Stock::generateReferenceNo($this->business_id),
                'stock_date' => now()->toDateString(),
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);

        $stock->update(['notes' => $this->notes !== '' ? $this->notes : null]);

        $entered = $this->enteredCounts();
        $balances = $this->latestBalances($entered->keys()->all());

        foreach ($entered as $itemId => $current) {
            $previous = $balances[$itemId] ?? 0.0;

            $stock->items()->updateOrCreate(
                ['item_id' => $itemId],
                [
                    'previous_balance' => $previous,
                    'current_balance' => $current,
                    'variance' => round($current - $previous, 3),
                ]
            );
        }

        $stock->items()->whereNotIn('item_id', $entered->keys()->all())->delete();

        $this->stockId = $stock->id;
        $this->referenceNo = $stock->reference_no;

        return $stock;
    }

    /**
     * Latest ledger balance per item for the selected business.
     *
     * @param  array<int, string>  $itemIds
     * @return array<string, float>
     */
    private function latestBalances(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        return item_balance::whereIn('item_id', $itemIds)
            ->where('business_id', $this->business_id)
            ->orderBy('created_at', 'desc')
            ->get(['item_id', 'current_balance', 'created_at'])
            ->unique('item_id')
            ->mapWithKeys(fn ($balance) => [$balance->item_id => (float) $balance->current_balance])
            ->all();
    }

    private function productsQuery()
    {
        return items::query()
            ->active()
            ->products()
            ->where('business_id', $this->business_id);
    }

    private function formatQuantity(float $value): string
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    public function render()
    {
        $products = $this->business_id
            ? $this->productsQuery()
                ->with(['category:id,name', 'unit:id,name'])
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name', $this->sortDirection === 'desc' ? 'desc' : 'asc')
                ->get(['id', 'name', 'category_id', 'unit_id'])
            : collect();

        $stock = $this->stockId ? Stock::with('items')->find($this->stockId) : null;

        // Approved counts show the balance snapshot taken at approval; drafts show the live balance.
        $balances = $this->isReadOnly && $stock
            ? $stock->items->mapWithKeys(fn ($line) => [$line->item_id => (float) $line->previous_balance])->all()
            : $this->latestBalances($products->pluck('id')->all());

        $enteredIds = collect($this->counts)->reject(fn ($value) => $value === null || trim((string) $value) === '')->keys();
        $totalProducts = $this->business_id ? $this->productsQuery()->count() : 0;

        return view('livewire.owner.stocktaking.sheet', [
            'products' => $products,
            'balances' => $balances,
            'stock' => $stock,
            'summary' => [
                'total' => $totalProducts,
                'counted' => $enteredIds->count(),
                'remaining' => max($totalProducts - $enteredIds->count(), 0),
            ],
        ]);
    }
}
