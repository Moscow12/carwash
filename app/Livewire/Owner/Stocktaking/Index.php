<?php

namespace App\Livewire\Owner\Stocktaking;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Models\stocktaking;
use App\Models\item_balance;
use App\Models\items;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $selectedCarwash = '';
    public $statusFilter = '';

    // Modal states
    public $showModal = false;
    public $showImportModal = false;
    public $editMode = false;
    public $stocktakingId = null;

    // Form fields for single stocktaking
    #[Rule('required|exists:items,id')]
    public $item_id = '';

    #[Rule('required|numeric|min:0.01')]
    public $quantity = '';

    #[Rule('required|numeric|min:0')]
    public $price = '';

    #[Rule('required|in:received,pending,canceled')]
    public $stocktaking_status = 'pending';

    public $notes = '';

    // Import properties
    public $file;
    public $parsedItems = [];
    public $parseErrors = [];
    public $successCount = 0;
    public $errorCount = 0;
    public $showPreview = false;
    public $importing = false;
    public $importComplete = false;

    // Data
    public $ownerCarwashes = [];
    public $availableItems = [];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $firstCarwash = $this->ownerCarwashes->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
            $this->loadItems();
        }
    }

    public function updatedSelectedCarwash()
    {
        $this->loadItems();
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function loadItems()
    {
        if ($this->selectedCarwash) {
            $this->availableItems = items::where('carwash_id', $this->selectedCarwash)
                ->where('type', 'product')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->availableItems = [];
        }
    }

    // Single Stocktaking Modal
    public function openAddModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $stocktaking = stocktaking::find($id);
        if (!$stocktaking) return;

        $this->stocktakingId = $id;
        $this->item_id = $stocktaking->item_id;
        $this->quantity = $stocktaking->quantity;
        $this->price = $stocktaking->price;
        $this->stocktaking_status = $stocktaking->stocktaking_status;
        $this->notes = $stocktaking->notes;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function saveStocktaking()
    {
        $this->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'stocktaking_status' => 'required|in:received,pending,canceled',
        ]);

        // Verify item belongs to selected carwash
        $item = items::where('id', $this->item_id)
            ->where('carwash_id', $this->selectedCarwash)
            ->first();

        if (!$item) {
            session()->flash('error', 'Invalid item selected.');
            return;
        }

        DB::beginTransaction();
        try {
            if ($this->editMode && $this->stocktakingId) {
                $stocktaking = stocktaking::find($this->stocktakingId);
                $previousStatus = $stocktaking->stocktaking_status;
                $previousQuantity = $stocktaking->quantity;

                $stocktaking->update([
                    'item_id' => $this->item_id,
                    'quantity' => $this->quantity,
                    'price' => $this->price,
                    'stocktaking_status' => $this->stocktaking_status,
                    'notes' => $this->notes,
                ]);

                // If status changed to received, update item balance
                if ($previousStatus !== 'received' && $this->stocktaking_status === 'received') {
                    $this->updateItemBalance($this->item_id, $this->quantity, 'restock');
                }
                // If status changed from received to something else, reverse the balance
                elseif ($previousStatus === 'received' && $this->stocktaking_status !== 'received') {
                    $this->updateItemBalance($this->item_id, -$previousQuantity, 'adjustment');
                }
                // If still received but quantity changed
                elseif ($previousStatus === 'received' && $this->stocktaking_status === 'received' && $previousQuantity != $this->quantity) {
                    $difference = $this->quantity - $previousQuantity;
                    $this->updateItemBalance($this->item_id, $difference, 'adjustment');
                }

                session()->flash('message', 'Stocktaking updated successfully.');
            } else {
                $stocktaking = stocktaking::create([
                    'item_id' => $this->item_id,
                    'user_id' => Auth::id(),
                    'carwash_id' => $this->selectedCarwash,
                    'quantity' => $this->quantity,
                    'price' => $this->price,
                    'stocktaking_status' => $this->stocktaking_status,
                    'notes' => $this->notes,
                ]);

                // If status is received, update item balance
                if ($this->stocktaking_status === 'received') {
                    $this->updateItemBalance($this->item_id, $this->quantity, 'restock');
                }

                session()->flash('message', 'Stocktaking created successfully.');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving stocktaking: ' . $e->getMessage());
        }
    }

    private function updateItemBalance($itemId, $quantity, $transactionType)
    {
        // Get current balance
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('carwash_id', $this->selectedCarwash)
            ->latest()
            ->first();

        $previousBalance = $lastBalance ? $lastBalance->current_balance : 0;
        $newBalance = $previousBalance + $quantity;

        item_balance::create([
            'item_id' => $itemId,
            'user_id' => Auth::id(),
            'carwash_id' => $this->selectedCarwash,
            'previous_balance' => $previousBalance,
            'current_balance' => $newBalance,
            'quantity_changed' => $quantity,
            'stock_type' => $quantity >= 0 ? 'in' : 'out',
            'stransaction_type' => $transactionType,
            'invoice_number' => item_balance::generateInvoiceNumber(),
        ]);
    }

    public function deleteStocktaking($id)
    {
        $stocktaking = stocktaking::find($id);
        if (!$stocktaking) return;

        DB::beginTransaction();
        try {
            // If was received, reverse the balance
            if ($stocktaking->stocktaking_status === 'received') {
                $this->updateItemBalance($stocktaking->item_id, -$stocktaking->quantity, 'adjustment');
            }

            $stocktaking->delete();
            DB::commit();
            session()->flash('message', 'Stocktaking deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error deleting stocktaking.');
        }
    }

    public function updateStatus($id, $status)
    {
        $stocktaking = stocktaking::find($id);
        if (!$stocktaking) return;

        $previousStatus = $stocktaking->stocktaking_status;

        DB::beginTransaction();
        try {
            $stocktaking->update(['stocktaking_status' => $status]);

            // Handle balance updates
            if ($previousStatus !== 'received' && $status === 'received') {
                $this->selectedCarwash = $stocktaking->carwash_id;
                $this->updateItemBalance($stocktaking->item_id, $stocktaking->quantity, 'restock');
            } elseif ($previousStatus === 'received' && $status !== 'received') {
                $this->selectedCarwash = $stocktaking->carwash_id;
                $this->updateItemBalance($stocktaking->item_id, -$stocktaking->quantity, 'adjustment');
            }

            DB::commit();
            session()->flash('message', 'Status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating status.');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['item_id', 'quantity', 'price', 'notes', 'stocktakingId', 'editMode']);
        $this->stocktaking_status = 'pending';
        $this->resetValidation();
    }

    // Import functionality
    public function openImportModal()
    {
        $this->resetImport();
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->resetImport();
    }

    public function resetImport()
    {
        $this->reset(['file', 'parsedItems', 'parseErrors', 'successCount', 'errorCount', 'showPreview', 'importComplete']);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="stocktaking_template.csv"',
        ];

        $columns = ['item_name', 'quantity', 'price', 'status', 'notes'];

        $exampleRow = ['Air Freshener', '50', '500', 'pending', 'Monthly stock count'];

        $callback = function() use ($columns, $exampleRow) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $exampleRow);
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function parseFile()
    {
        $this->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        if (!$this->selectedCarwash) {
            session()->flash('error', 'Please select a carwash first.');
            return;
        }

        $this->reset(['parsedItems', 'parseErrors', 'successCount', 'errorCount', 'importComplete']);

        $path = $this->file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            session()->flash('error', 'Unable to read the file.');
            return;
        }

        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) < 4) {
                $this->parseErrors[] = "Row {$rowNumber}: Incomplete data";
                continue;
            }

            $data = array_combine($header, array_slice($row, 0, count($header)));
            $rowErrors = $this->validateImportRow($data, $rowNumber);

            if (!empty($rowErrors)) {
                $this->parseErrors = array_merge($this->parseErrors, $rowErrors);
                $data['_has_error'] = true;
                $data['_errors'] = $rowErrors;
            } else {
                $data['_has_error'] = false;
                $data['_errors'] = [];
            }

            $data['_row'] = $rowNumber;
            $this->parsedItems[] = $data;
        }

        fclose($handle);

        if (empty($this->parsedItems)) {
            session()->flash('error', 'No valid data found in the file.');
            return;
        }

        $this->showPreview = true;
    }

    private function validateImportRow(array $data, int $rowNumber): array
    {
        $errors = [];

        if (empty($data['item_name'])) {
            $errors[] = "Row {$rowNumber}: Item name is required";
        } else {
            $item = collect($this->availableItems)->firstWhere('name', $data['item_name']);
            if (!$item) {
                $errors[] = "Row {$rowNumber}: Item '{$data['item_name']}' not found";
            }
        }

        if (!is_numeric($data['quantity'] ?? '') || $data['quantity'] <= 0) {
            $errors[] = "Row {$rowNumber}: Quantity must be a positive number";
        }

        if (!is_numeric($data['price'] ?? '') || $data['price'] < 0) {
            $errors[] = "Row {$rowNumber}: Price must be a valid number";
        }

        $validStatuses = ['received', 'pending', 'canceled'];
        if (!in_array(strtolower($data['status'] ?? ''), $validStatuses)) {
            $errors[] = "Row {$rowNumber}: Status must be 'received', 'pending', or 'canceled'";
        }

        return $errors;
    }

    public function importItems()
    {
        if (empty($this->parsedItems)) {
            session()->flash('error', 'No items to import.');
            return;
        }

        $this->importing = true;
        $this->successCount = 0;
        $this->errorCount = 0;

        DB::beginTransaction();
        try {
            foreach ($this->parsedItems as $index => $data) {
                if ($data['_has_error']) {
                    $this->errorCount++;
                    continue;
                }

                $item = collect($this->availableItems)->firstWhere('name', $data['item_name']);
                if (!$item) {
                    $this->errorCount++;
                    continue;
                }

                $status = strtolower($data['status']);

                $stocktaking = stocktaking::create([
                    'item_id' => $item['id'],
                    'user_id' => Auth::id(),
                    'carwash_id' => $this->selectedCarwash,
                    'quantity' => $data['quantity'],
                    'price' => $data['price'],
                    'stocktaking_status' => $status,
                    'notes' => $data['notes'] ?? null,
                ]);

                if ($status === 'received') {
                    $this->updateItemBalance($item['id'], $data['quantity'], 'restock');
                }

                $this->successCount++;
                $this->parsedItems[$index]['_imported'] = true;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Import failed: ' . $e->getMessage());
        }

        $this->importing = false;
        $this->importComplete = true;

        if ($this->successCount > 0) {
            session()->flash('message', "{$this->successCount} stocktaking record(s) imported successfully.");
        }
    }

    public function removeImportItem($index)
    {
        if (isset($this->parsedItems[$index])) {
            unset($this->parsedItems[$index]);
            $this->parsedItems = array_values($this->parsedItems);
        }
    }

    public function getItemBalance($itemId)
    {
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('carwash_id', $this->selectedCarwash)
            ->latest()
            ->first();

        return $lastBalance ? $lastBalance->current_balance : 0;
    }

    public function render()
    {
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');

        $stocktakings = stocktaking::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
            ->when($this->statusFilter, fn($q) => $q->where('stocktaking_status', $this->statusFilter))
            ->when($this->search, function ($q) {
                $q->whereHas('item', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            })
            ->with(['carwash', 'item', 'user'])
            ->latest()
            ->paginate(10);

        // Summary stats
        $stats = [
            'total' => stocktaking::whereIn('carwash_id', $carwashIds)
                ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
                ->count(),
            'received' => stocktaking::whereIn('carwash_id', $carwashIds)
                ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
                ->received()
                ->count(),
            'pending' => stocktaking::whereIn('carwash_id', $carwashIds)
                ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
                ->pending()
                ->count(),
            'total_value' => stocktaking::whereIn('carwash_id', $carwashIds)
                ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
                ->received()
                ->selectRaw('SUM(quantity * price) as total')
                ->value('total') ?? 0,
        ];

        return view('livewire.owner.stocktaking.index', [
            'stocktakings' => $stocktakings,
            'stats' => $stats,
        ]);
    }
}
