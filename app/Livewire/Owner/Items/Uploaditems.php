<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use App\Models\items;
use App\Models\carwashes;
use App\Models\category;
use App\Models\unit;

#[Layout('components.layouts.app-owner')]
class Uploaditems extends Component
{
    use WithFileUploads;

    public $file;
    public $carwash_id = '';
    public $parsedItems = [];
    public $parseErrors = [];
    public $successCount = 0;
    public $errorCount = 0;
    public $showPreview = false;
    public $importing = false;
    public $importComplete = false;

    public $ownerCarwashes = [];
    public $availableCategories = [];
    public $availableUnits = [];

    protected $rules = [
        'file' => 'required|mimes:csv,txt|max:5120',
        'carwash_id' => 'required|exists:carwashes,id',
    ];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();
        $this->availableUnits = unit::where('status', 'active')->orderBy('name')->get();

        if ($this->ownerCarwashes->count() === 1) {
            $this->carwash_id = $this->ownerCarwashes->first()->id;
            $this->loadCategories();
        }
    }

    public function updatedCarwashId($value)
    {
        $this->loadCategories();
        $this->reset(['parsedItems', 'parseErrors', 'showPreview', 'importComplete']);
    }

    public function loadCategories()
    {
        if ($this->carwash_id) {
            $this->availableCategories = category::where('carwash_id', $this->carwash_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->availableCategories = [];
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="items_template.csv"',
        ];

        $columns = [
            'name',
            'description',
            'cost_price',
            'selling_price',
            'market_price',
            'type',
            'product_stock',
            'require_plate_number',
            'commission',
            'commission_type',
            'category_name',
            'unit_name',
            'status'
        ];

        $exampleRow = [
            'Full Car Wash',
            'Complete exterior and interior cleaning',
            '5000',
            '15000',
            '20000',
            'Service',
            'no',
            'yes',
            '1000',
            'fixed',
            'Premium Services',
            'Per Service',
            'active'
        ];

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
            'carwash_id' => 'required|exists:carwashes,id',
        ]);

        // Verify carwash ownership
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');
        if (!$carwashIds->contains($this->carwash_id)) {
            session()->flash('error', 'Invalid carwash selected.');
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
        $expectedHeaders = ['name', 'description', 'cost_price', 'selling_price', 'market_price', 'type', 'product_stock', 'require_plate_number', 'commission', 'commission_type', 'category_name', 'unit_name', 'status'];

        // Normalize headers
        $header = array_map(function($h) {
            return strtolower(trim($h));
        }, $header);

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count($row) < count($expectedHeaders)) {
                $this->parseErrors[] = "Row {$rowNumber}: Incomplete data - expected " . count($expectedHeaders) . " columns, got " . count($row);
                continue;
            }

            $data = array_combine($header, array_slice($row, 0, count($header)));

            // Validate row
            $rowErrors = $this->validateRow($data, $rowNumber);

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

    private function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        // Required fields
        if (empty($data['name'])) {
            $errors[] = "Row {$rowNumber}: Name is required";
        }
        if (empty($data['description'])) {
            $errors[] = "Row {$rowNumber}: Description is required";
        }
        if (!is_numeric($data['cost_price'] ?? '') || $data['cost_price'] < 0) {
            $errors[] = "Row {$rowNumber}: Cost price must be a valid positive number";
        }
        if (!is_numeric($data['selling_price'] ?? '') || $data['selling_price'] < 0) {
            $errors[] = "Row {$rowNumber}: Selling price must be a valid positive number";
        }

        // Type validation
        $validTypes = ['Service', 'product'];
        if (!in_array($data['type'] ?? '', $validTypes)) {
            $errors[] = "Row {$rowNumber}: Type must be 'Service' or 'product'";
        }

        // Yes/No fields
        $yesNoFields = ['product_stock', 'require_plate_number'];
        foreach ($yesNoFields as $field) {
            if (!in_array(strtolower($data[$field] ?? ''), ['yes', 'no'])) {
                $errors[] = "Row {$rowNumber}: {$field} must be 'yes' or 'no'";
            }
        }

        // Status validation
        if (!in_array(strtolower($data['status'] ?? ''), ['active', 'inactive'])) {
            $errors[] = "Row {$rowNumber}: Status must be 'active' or 'inactive'";
        }

        // Category validation
        if (!empty($data['category_name'])) {
            $category = collect($this->availableCategories)->firstWhere('name', $data['category_name']);
            if (!$category) {
                $errors[] = "Row {$rowNumber}: Category '{$data['category_name']}' not found for this carwash";
            }
        } else {
            $errors[] = "Row {$rowNumber}: Category name is required";
        }

        // Unit validation
        if (!empty($data['unit_name'])) {
            $unit = collect($this->availableUnits)->firstWhere('name', $data['unit_name']);
            if (!$unit) {
                $errors[] = "Row {$rowNumber}: Unit '{$data['unit_name']}' not found";
            }
        } else {
            $errors[] = "Row {$rowNumber}: Unit name is required";
        }

        // Commission type validation (if commission is provided)
        if (!empty($data['commission']) && is_numeric($data['commission'])) {
            if (!in_array(strtolower($data['commission_type'] ?? ''), ['fixed', 'percentage', ''])) {
                $errors[] = "Row {$rowNumber}: Commission type must be 'fixed' or 'percentage'";
            }
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

        foreach ($this->parsedItems as $index => $data) {
            if ($data['_has_error']) {
                $this->errorCount++;
                continue;
            }

            try {
                // Find category and unit
                $category = collect($this->availableCategories)->firstWhere('name', $data['category_name']);
                $unit = collect($this->availableUnits)->firstWhere('name', $data['unit_name']);

                if (!$category || !$unit) {
                    $this->errorCount++;
                    continue;
                }

                items::create([
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'cost_price' => $data['cost_price'],
                    'selling_price' => $data['selling_price'],
                    'market_price' => !empty($data['market_price']) ? $data['market_price'] : null,
                    'type' => $data['type'],
                    'product_stock' => strtolower($data['product_stock']),
                    'require_plate_number' => strtolower($data['require_plate_number']),
                    'commission' => !empty($data['commission']) ? $data['commission'] : null,
                    'commission_type' => !empty($data['commission']) && !empty($data['commission_type']) ? strtolower($data['commission_type']) : null,
                    'status' => strtolower($data['status']),
                    'category_id' => $category['id'],
                    'unit_id' => $unit['id'],
                    'carwash_id' => $this->carwash_id,
                ]);

                $this->successCount++;
                $this->parsedItems[$index]['_imported'] = true;

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->parsedItems[$index]['_import_error'] = $e->getMessage();
            }
        }

        $this->importing = false;
        $this->importComplete = true;

        if ($this->successCount > 0) {
            session()->flash('message', "{$this->successCount} item(s) imported successfully.");
        }
        if ($this->errorCount > 0) {
            session()->flash('warning', "{$this->errorCount} item(s) failed to import.");
        }
    }

    public function removeItem($index)
    {
        if (isset($this->parsedItems[$index])) {
            unset($this->parsedItems[$index]);
            $this->parsedItems = array_values($this->parsedItems);
        }
    }

    public function resetUpload()
    {
        $this->reset(['file', 'parsedItems', 'parseErrors', 'successCount', 'errorCount', 'showPreview', 'importComplete']);
    }

    public function render()
    {
        return view('livewire.owner.items.uploaditems');
    }
}
