<?php

namespace App\Livewire\Owner\Items;

use App\Models\Business;
use App\Models\category;
use App\Models\items;
use App\Models\unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app-owner')]
class Uploaditems extends Component
{
    use WithFileUploads;

    public $file;

    public $business_id = '';

    public $parsedItems = [];

    public $parseErrors = [];

    public $successCount = 0;

    public $errorCount = 0;

    public $showPreview = false;

    public $importing = false;

    public $importComplete = false;

    public $ownerBusinesses = [];

    public $availableCategories = [];

    public $availableUnits = [];

    /** @var array<int, string> */
    public $newCategoryNames = [];

    /** @var array<int, string> */
    public $newUnitNames = [];

    protected $rules = [
        'file' => 'required|mimes:csv,txt|max:5120',
        'business_id' => 'required|exists:businesses,id',
    ];

    /**
     * CSV template columns, required ones first.
     *
     * @return array<string, array{required: bool, description: string, example: string, default: ?string}>
     */
    public static function templateColumns(): array
    {
        return [
            'name' => ['required' => true, 'description' => 'Item name', 'example' => 'Full Car Wash', 'default' => null],
            'cost_price' => ['required' => true, 'description' => 'Cost price (number)', 'example' => '5000', 'default' => null],
            'selling_price' => ['required' => true, 'description' => 'Selling price (number)', 'example' => '15000', 'default' => null],
            'type' => ['required' => true, 'description' => 'Service or product', 'example' => 'Service', 'default' => null],
            'category_name' => ['required' => true, 'description' => 'Category name (created if it does not exist)', 'example' => 'Premium Services', 'default' => null],
            'unit_name' => ['required' => true, 'description' => 'Unit name (created if it does not exist)', 'example' => 'Per Service', 'default' => null],
            'description' => ['required' => false, 'description' => 'Item description', 'example' => 'Complete exterior and interior cleaning', 'default' => 'empty'],
            'market_price' => ['required' => false, 'description' => 'Market price (number)', 'example' => '20000', 'default' => 'empty'],
            'product_stock' => ['required' => false, 'description' => 'Track stock (yes/no)', 'example' => 'no', 'default' => 'no'],
            'commission' => ['required' => false, 'description' => 'Commission amount', 'example' => '1000', 'default' => 'empty'],
            'commission_type' => ['required' => false, 'description' => 'fixed or percentage', 'example' => 'fixed', 'default' => 'empty'],
        ];
    }

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();
        $this->loadUnits();

        if ($this->ownerBusinesses->count() === 1) {
            $this->business_id = $this->ownerBusinesses->first()->id;
            $this->loadCategories();
        }
    }

    public function updatedBusinessId($value)
    {
        $this->loadCategories();
        $this->reset(['parsedItems', 'parseErrors', 'showPreview', 'importComplete', 'newCategoryNames', 'newUnitNames']);
    }

    public function loadCategories()
    {
        if ($this->business_id) {
            $this->availableCategories = category::where('business_id', $this->business_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->availableCategories = [];
        }
    }

    public function loadUnits(): void
    {
        $this->availableUnits = unit::where('status', 'active')->orderBy('name')->get();
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="items_template.csv"',
        ];

        $templateColumns = self::templateColumns();

        $columns = collect($templateColumns)
            ->map(fn ($column, $key) => $key.($column['required'] ? ' (required)' : ' (optional)'))
            ->values()
            ->all();

        $exampleRow = collect($templateColumns)->pluck('example')->all();

        $callback = function () use ($columns, $exampleRow) {
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
            'business_id' => 'required|exists:businesses,id',
        ]);

        // Verify business ownership
        $businessIds = Auth::user()->assignedBusinesses()->pluck('id');
        if (! $businessIds->contains($this->business_id)) {
            session()->flash('error', 'Invalid business selected.');

            return;
        }

        $this->reset(['parsedItems', 'parseErrors', 'successCount', 'errorCount', 'importComplete', 'newCategoryNames', 'newUnitNames']);
        $this->loadCategories();
        $this->loadUnits();

        $path = $this->file->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            session()->flash('error', 'Unable to read the file.');

            return;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            session()->flash('error', 'The file is empty.');

            return;
        }

        $header = array_map(fn ($h) => $this->normalizeHeader((string) $h), $header);

        $requiredColumns = collect(self::templateColumns())->filter(fn ($column) => $column['required'])->keys();
        $missingColumns = $requiredColumns->diff($header);

        if ($missingColumns->isNotEmpty()) {
            fclose($handle);
            session()->flash('error', 'Missing required column(s): '.$missingColumns->implode(', ').'. Please use the latest template.');

            return;
        }

        $rowNumber = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $data = [];
            foreach (array_keys(self::templateColumns()) as $column) {
                $position = array_search($column, $header, true);
                $data[$column] = $position !== false ? trim((string) ($row[$position] ?? '')) : '';
            }

            $rowErrors = $this->validateRow($data, $rowNumber);

            $data['_new_category'] = $data['category_name'] !== '' && ! $this->findByName($this->availableCategories, $data['category_name']);
            $data['_new_unit'] = $data['unit_name'] !== '' && ! $this->findByName($this->availableUnits, $data['unit_name']);

            if (! empty($rowErrors)) {
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

        $this->refreshNewNames();
        $this->showPreview = true;
    }

    /**
     * Strip "(required)", "(optional)" and "*" markers from a header cell.
     */
    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = preg_replace('/\((required|optional)\)|\*/i', '', $header);

        return strtolower(trim($header));
    }

    /**
     * Case-insensitive lookup of a model by name in a collection.
     */
    private function findByName(iterable $records, string $name): mixed
    {
        $name = Str::lower(trim($name));

        return collect($records)->first(fn ($record) => Str::lower(trim($record['name'])) === $name);
    }

    private function refreshNewNames(): void
    {
        $validItems = collect($this->parsedItems)->reject(fn ($item) => $item['_has_error']);

        $this->newCategoryNames = $validItems->where('_new_category', true)
            ->pluck('category_name')
            ->unique(fn ($name) => Str::lower($name))
            ->values()
            ->all();

        $this->newUnitNames = $validItems->where('_new_unit', true)
            ->pluck('unit_name')
            ->unique(fn ($name) => Str::lower($name))
            ->values()
            ->all();
    }

    private function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors[] = "Row {$rowNumber}: Name is required";
        }
        if (! is_numeric($data['cost_price']) || $data['cost_price'] < 0) {
            $errors[] = "Row {$rowNumber}: Cost price must be a valid positive number";
        }
        if (! is_numeric($data['selling_price']) || $data['selling_price'] < 0) {
            $errors[] = "Row {$rowNumber}: Selling price must be a valid positive number";
        }
        if ($data['market_price'] !== '' && (! is_numeric($data['market_price']) || $data['market_price'] < 0)) {
            $errors[] = "Row {$rowNumber}: Market price must be a valid positive number";
        }

        if (! in_array(Str::lower($data['type']), ['service', 'product'], true)) {
            $errors[] = "Row {$rowNumber}: Type must be 'Service' or 'product'";
        }

        if ($data['product_stock'] !== '' && ! in_array(Str::lower($data['product_stock']), ['yes', 'no'], true)) {
            $errors[] = "Row {$rowNumber}: product_stock must be 'yes' or 'no'";
        }

        if ($data['category_name'] === '') {
            $errors[] = "Row {$rowNumber}: Category name is required";
        }
        if ($data['unit_name'] === '') {
            $errors[] = "Row {$rowNumber}: Unit name is required";
        }

        if ($data['commission'] !== '') {
            if (! is_numeric($data['commission']) || $data['commission'] < 0) {
                $errors[] = "Row {$rowNumber}: Commission must be a valid positive number";
            } elseif (! in_array(Str::lower($data['commission_type']), ['fixed', 'percentage', ''], true)) {
                $errors[] = "Row {$rowNumber}: Commission type must be 'fixed' or 'percentage'";
            }
        }

        return $errors;
    }

    /**
     * Find a category for the selected business by name, creating it when missing.
     */
    private function resolveCategory(string $name): category
    {
        $existing = category::where('business_id', $this->business_id)
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim($name))])
            ->first();

        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->update(['status' => 'active']);
            }

            return $existing;
        }

        return category::create([
            'name' => trim($name),
            'slug' => Str::slug($name),
            'status' => 'active',
            'business_id' => $this->business_id,
        ]);
    }

    /**
     * Find a unit by name, creating it when missing.
     */
    private function resolveUnit(string $name): unit
    {
        $existing = unit::whereRaw('LOWER(name) = ?', [Str::lower(trim($name))])->first();

        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->update(['status' => 'active']);
            }

            return $existing;
        }

        return unit::create([
            'name' => trim($name),
            'status' => 'active',
        ]);
    }

    public function importItems()
    {
        if (empty($this->parsedItems)) {
            session()->flash('error', 'No items to import.');

            return;
        }

        $businessIds = Auth::user()->assignedBusinesses()->pluck('id');
        if (! $businessIds->contains($this->business_id)) {
            session()->flash('error', 'Invalid business selected.');

            return;
        }

        $this->importing = true;
        $this->successCount = 0;
        $this->errorCount = 0;

        $categories = [];
        $units = [];

        foreach ($this->parsedItems as $index => $data) {
            if ($data['_has_error']) {
                $this->errorCount++;

                continue;
            }

            try {
                $categoryKey = Str::lower($data['category_name']);
                $unitKey = Str::lower($data['unit_name']);

                $categories[$categoryKey] ??= $this->resolveCategory($data['category_name']);
                $units[$unitKey] ??= $this->resolveUnit($data['unit_name']);

                $hasCommission = $data['commission'] !== '';

                items::create([
                    'name' => $data['name'],
                    'description' => $data['description'] !== '' ? $data['description'] : null,
                    'cost_price' => $data['cost_price'],
                    'selling_price' => $data['selling_price'],
                    'market_price' => $data['market_price'] !== '' ? $data['market_price'] : null,
                    'type' => Str::lower($data['type']) === 'service' ? 'Service' : 'product',
                    'product_stock' => $data['product_stock'] !== '' ? Str::lower($data['product_stock']) : 'no',
                    'require_plate_number' => 'no',
                    'commission' => $hasCommission ? $data['commission'] : null,
                    'commission_type' => $hasCommission && $data['commission_type'] !== '' ? Str::lower($data['commission_type']) : null,
                    'status' => 'active',
                    'category_id' => $categories[$categoryKey]->id,
                    'unit_id' => $units[$unitKey]->id,
                    'business_id' => $this->business_id,
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
        $this->loadCategories();
        $this->loadUnits();

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
            $this->refreshNewNames();
        }
    }

    public function resetUpload()
    {
        $this->reset(['file', 'parsedItems', 'parseErrors', 'successCount', 'errorCount', 'showPreview', 'importComplete', 'newCategoryNames', 'newUnitNames']);
    }

    public function render()
    {
        return view('livewire.owner.items.uploaditems', [
            'templateColumns' => self::templateColumns(),
        ]);
    }
}
