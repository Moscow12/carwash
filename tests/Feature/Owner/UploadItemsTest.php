<?php

namespace Tests\Feature\Owner;

use App\Livewire\Owner\Items\Uploaditems;
use App\Models\Business;
use App\Models\category;
use App\Models\items;
use App\Models\unit;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class UploadItemsTest extends TestCase
{
    private User $owner;

    private Business $business;

    /**
     * The project migrations use MySQL-only statements, so the tables
     * this component touches are built directly on the in-memory SQLite database.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('customer');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('businesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('status')->default('active');
            $table->uuid('owner_id')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->uuid('business_id');
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('symbol')->nullable();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('barcode')->nullable();
            $table->string('description')->nullable();
            $table->string('cost_price');
            $table->string('type')->default('Service');
            $table->string('product_stock')->default('no');
            $table->string('selling_price');
            $table->string('market_price')->nullable();
            $table->string('image')->nullable();
            $table->string('commission')->nullable();
            $table->string('commission_type')->nullable();
            $table->string('require_plate_number')->default('no');
            $table->uuid('unit_id');
            $table->string('status')->default('active');
            $table->boolean('is_published')->default(false);
            $table->uuid('category_id');
            $table->uuid('business_id');
            $table->uuid('outlet_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->business = Business::create(['name' => 'TS Houses', 'status' => 'active', 'owner_id' => $this->owner->id]);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function csv(array $rows): UploadedFile
    {
        $content = collect($rows)
            ->map(fn (array $row) => collect($row)->map(fn ($value) => '"'.str_replace('"', '""', $value).'"')->implode(','))
            ->implode("\n");

        return UploadedFile::fake()->createWithContent('items.csv', $content);
    }

    /**
     * @return array<int, string>
     */
    private function header(): array
    {
        return [
            'name (required)', 'cost_price (required)', 'selling_price (required)', 'type (required)',
            'category_name (required)', 'unit_name (required)', 'description (optional)', 'market_price (optional)',
            'product_stock (optional)', 'commission (optional)', 'commission_type (optional)',
        ];
    }

    public function test_template_marks_required_and_optional_columns_without_status_or_plate(): void
    {
        $response = Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->call('downloadTemplate');

        $response->assertFileDownloaded('items_template.csv');

        $columns = array_keys(Uploaditems::templateColumns());

        $this->assertNotContains('status', $columns);
        $this->assertNotContains('require_plate_number', $columns);
        $this->assertTrue(Uploaditems::templateColumns()['name']['required']);
        $this->assertFalse(Uploaditems::templateColumns()['description']['required']);
    }

    public function test_missing_categories_and_units_are_created_once_on_import(): void
    {
        $file = $this->csv([
            $this->header(),
            ['Maji Kubwa', '500', '1000', 'product', 'Vinywaji', 'PC', '', '', '', '', ''],
            ['Soda', '400', '800', 'product', 'vinywaji', 'pc', '', '', '', '', ''],
            ['Full Car Wash', '5000', '15000', 'Service', 'Premium Services', 'Per Service', 'Complete wash', '', 'no', '1000', 'fixed'],
        ]);

        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $file)
            ->call('parseFile')
            ->assertSet('parseErrors', [])
            ->assertSet('newCategoryNames', ['Vinywaji', 'Premium Services'])
            ->assertSet('newUnitNames', ['PC', 'Per Service'])
            ->call('importItems')
            ->assertSet('successCount', 3)
            ->assertSet('errorCount', 0);

        $this->assertSame(2, category::where('business_id', $this->business->id)->count());
        $this->assertSame(2, unit::count());

        $vinywaji = category::where('name', 'Vinywaji')->firstOrFail();
        $this->assertSame('vinywaji', $vinywaji->slug);
        $this->assertSame('active', $vinywaji->status);
        $this->assertSame(2, items::where('category_id', $vinywaji->id)->count());
    }

    public function test_existing_category_and_unit_are_matched_case_insensitively(): void
    {
        $category = category::create(['name' => 'Books', 'slug' => 'books', 'status' => 'active', 'business_id' => $this->business->id]);
        $unit = unit::create(['name' => 'Piece', 'status' => 'active']);

        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $this->csv([
                $this->header(),
                ['Note Book', '1000', '2000', 'product', 'BOOKS', 'piece', '', '', '', '', ''],
            ]))
            ->call('parseFile')
            ->assertSet('newCategoryNames', [])
            ->assertSet('newUnitNames', [])
            ->call('importItems')
            ->assertSet('successCount', 1);

        $this->assertSame(1, category::count());
        $this->assertSame(1, unit::count());
        $this->assertDatabaseHas('items', ['name' => 'Note Book', 'category_id' => $category->id, 'unit_id' => $unit->id]);
    }

    public function test_imported_items_default_to_active_without_plate_number_and_blank_optionals(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $this->csv([
                $this->header(),
                ['Laptop', '600000', '750000', 'product', 'Computer', 'PC', '', '', '', '', ''],
            ]))
            ->call('parseFile')
            ->call('importItems')
            ->assertSet('successCount', 1);

        $item = items::where('name', 'Laptop')->firstOrFail();

        $this->assertSame('active', $item->status);
        $this->assertSame('no', $item->require_plate_number);
        $this->assertSame('no', $item->product_stock);
        $this->assertNull($item->description);
        $this->assertNull($item->market_price);
        $this->assertNull($item->commission);
    }

    public function test_row_missing_a_required_field_is_rejected(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $this->csv([
                $this->header(),
                ['Laptop', '600000', '', 'product', 'Computer', 'PC', '', '', '', '', ''],
                ['', '100', '200', 'gadget', '', '', '', '', 'maybe', '', ''],
            ]))
            ->call('parseFile')
            ->assertSet('parseErrors', [
                'Row 2: Selling price must be a valid positive number',
                'Row 3: Name is required',
                "Row 3: Type must be 'Service' or 'product'",
                "Row 3: product_stock must be 'yes' or 'no'",
                'Row 3: Category name is required',
                'Row 3: Unit name is required',
            ])
            ->assertSet('newCategoryNames', [])
            ->call('importItems')
            ->assertSet('successCount', 0)
            ->assertSet('errorCount', 2);

        $this->assertSame(0, items::count());
        $this->assertSame(0, category::count());
    }

    public function test_file_missing_required_column_shows_error(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $this->csv([
                ['name', 'cost_price', 'selling_price', 'type', 'category_name'],
                ['Laptop', '600000', '750000', 'product', 'Computer'],
            ]))
            ->call('parseFile')
            ->assertSet('showPreview', false)
            ->assertSet('parsedItems', [])
            ->assertSee('Missing required column(s): unit_name');
    }

    public function test_old_template_with_status_and_plate_columns_still_imports_as_active(): void
    {
        Livewire::actingAs($this->owner)
            ->test(Uploaditems::class)
            ->set('file', $this->csv([
                ['name', 'description', 'cost_price', 'selling_price', 'market_price', 'type', 'product_stock', 'require_plate_number', 'commission', 'commission_type', 'category_name', 'unit_name', 'status'],
                ['Full Car Wash', 'Complete wash', '5000', '15000', '20000', 'Service', 'no', 'yes', '1000', 'fixed', 'Premium Services', 'Per Service', 'inactive'],
            ]))
            ->call('parseFile')
            ->assertSet('parseErrors', [])
            ->call('importItems')
            ->assertSet('successCount', 1);

        $this->assertDatabaseHas('items', [
            'name' => 'Full Car Wash',
            'status' => 'active',
            'require_plate_number' => 'no',
            'commission_type' => 'fixed',
        ]);
    }
}
