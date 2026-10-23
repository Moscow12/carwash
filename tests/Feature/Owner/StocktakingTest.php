<?php

namespace Tests\Feature\Owner;

use App\Livewire\Owner\Stocktaking\Index;
use App\Livewire\Owner\Stocktaking\Sheet;
use App\Models\Business;
use App\Models\category;
use App\Models\item_balance;
use App\Models\items;
use App\Models\Stock;
use App\Models\StockItem;
use App\Models\unit;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class StocktakingTest extends TestCase
{
    private User $owner;

    private Business $business;

    private category $category;

    private unit $unit;

    /**
     * The project migrations use MySQL-only statements, so the tables
     * these components touch are built directly on the in-memory SQLite database.
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

        Schema::create('carwash_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->string('stocktaking_prefix')->nullable();
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
            $table->string('cost_price')->default('0');
            $table->string('type')->default('Service');
            $table->string('product_stock')->default('no');
            $table->string('selling_price')->default('0');
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

        Schema::create('item_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('user_id');
            $table->uuid('business_id');
            $table->uuid('outlet_id')->nullable();
            $table->uuid('order_id')->nullable();
            $table->decimal('previous_balance', 14, 3)->default(0);
            $table->decimal('current_balance', 14, 3)->default(0);
            $table->decimal('quantity_changed', 14, 3)->default(0);
            $table->decimal('quantity_ml', 10, 2)->nullable();
            $table->string('stock_type')->default('in');
            $table->string('stransaction_type')->default('initial_stock');
            $table->string('movement_reason')->default('normal');
            $table->string('invoice_number');
            $table->timestamps();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id');
            $table->string('reference_no')->unique();
            $table->date('stock_date');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->uuid('created_by');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('stock_id');
            $table->uuid('item_id');
            $table->decimal('previous_balance', 14, 3)->default(0);
            $table->decimal('current_balance', 14, 3)->default(0);
            $table->decimal('variance', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['stock_id', 'item_id']);
        });

        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->business = Business::create(['name' => 'TS Houses', 'status' => 'active', 'owner_id' => $this->owner->id]);
        $this->category = category::create(['name' => 'Drinks', 'slug' => 'drinks', 'status' => 'active', 'business_id' => $this->business->id]);
        $this->unit = unit::create(['name' => 'PC', 'status' => 'active']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function product(string $name, array $attributes = []): items
    {
        return items::create(array_merge([
            'name' => $name,
            'cost_price' => '100',
            'selling_price' => '200',
            'type' => 'product',
            'status' => 'active',
            'category_id' => $this->category->id,
            'unit_id' => $this->unit->id,
            'business_id' => $this->business->id,
        ], $attributes));
    }

    private function recordBalance(items $item, float $balance): void
    {
        $this->travel(1)->seconds();

        item_balance::create([
            'item_id' => $item->id,
            'user_id' => $this->owner->id,
            'business_id' => $this->business->id,
            'previous_balance' => 0,
            'current_balance' => $balance,
            'quantity_changed' => $balance,
            'stock_type' => 'in',
            'stransaction_type' => 'restock',
            'invoice_number' => 'INV-TEST',
        ]);
    }

    public function test_sheet_lists_only_active_products_and_sorts_by_name(): void
    {
        $this->product('Soda');
        $this->product('Apple Juice');
        $this->product('Water');
        $this->product('Old Stock', ['status' => 'inactive']);
        $this->product('Car Wash', ['type' => 'Service']);

        $component = Livewire::actingAs($this->owner)->test(Sheet::class);

        $this->assertSame(['Apple Juice', 'Soda', 'Water'], $component->viewData('products')->pluck('name')->all());

        $component->call('toggleSort');

        $this->assertSame(['Water', 'Soda', 'Apple Juice'], $component->viewData('products')->pluck('name')->all());
    }

    public function test_previous_balance_is_the_latest_ledger_balance(): void
    {
        $soda = $this->product('Soda');
        $water = $this->product('Water');
        $this->recordBalance($soda, 10);
        $this->recordBalance($soda, 7);

        $balances = Livewire::actingAs($this->owner)->test(Sheet::class)->viewData('balances');

        $this->assertSame(7.0, $balances[$soda->id]);
        $this->assertArrayNotHasKey($water->id, $balances);
    }

    public function test_saving_a_draft_does_not_touch_item_balances(): void
    {
        $soda = $this->product('Soda');
        $water = $this->product('Water');
        $this->recordBalance($soda, 10);

        $component = Livewire::actingAs($this->owner)
            ->test(Sheet::class)
            ->set("counts.{$soda->id}", '8')
            ->set("counts.{$water->id}", '5')
            ->call('saveDraft')
            ->assertHasNoErrors();

        $stock = Stock::firstOrFail();
        $component->assertRedirect(route('owner.stocktaking.sheet', $stock));

        $this->assertSame('draft', $stock->status);
        $this->assertStringStartsWith('ST-', $stock->reference_no);
        $this->assertSame(2, $stock->items()->count());
        $this->assertSame('-2.000', $stock->items()->where('item_id', $soda->id)->value('variance'));
        $this->assertSame(1, item_balance::count());
    }

    public function test_clearing_a_count_removes_its_draft_line(): void
    {
        $soda = $this->product('Soda');
        $water = $this->product('Water');

        $stock = Stock::factory()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);
        StockItem::factory()->create(['stock_id' => $stock->id, 'item_id' => $soda->id, 'current_balance' => 4]);
        StockItem::factory()->create(['stock_id' => $stock->id, 'item_id' => $water->id, 'current_balance' => 6]);

        Livewire::actingAs($this->owner)
            ->test(Sheet::class, ['stock' => $stock])
            ->assertSet("counts.{$soda->id}", '4')
            ->set("counts.{$water->id}", '')
            ->call('saveDraft');

        $this->assertSame([$soda->id], $stock->items()->pluck('item_id')->all());
    }

    public function test_approving_posts_adjustments_and_skips_unchanged_items(): void
    {
        $soda = $this->product('Soda');
        $water = $this->product('Water');
        $juice = $this->product('Juice');
        $this->recordBalance($soda, 10);
        $this->recordBalance($water, 5);

        Livewire::actingAs($this->owner)
            ->test(Sheet::class)
            ->set("counts.{$soda->id}", '12')
            ->set("counts.{$water->id}", '5')
            ->set("counts.{$juice->id}", '3.5')
            ->call('approve')
            ->assertRedirect(route('owner.stocktaking'));

        $stock = Stock::firstOrFail();
        $this->assertSame('approved', $stock->status);
        $this->assertSame($this->owner->id, $stock->approved_by);
        $this->assertNotNull($stock->approved_at);

        $adjustments = item_balance::where('invoice_number', $stock->reference_no)->get()->keyBy('item_id');

        $this->assertCount(2, $adjustments);
        $this->assertFalse($adjustments->has($water->id));

        $this->assertSame('in', $adjustments[$soda->id]->stock_type);
        $this->assertSame('adjustment', $adjustments[$soda->id]->stransaction_type);
        $this->assertSame('closing_count', $adjustments[$soda->id]->movement_reason);
        $this->assertEquals(10, $adjustments[$soda->id]->previous_balance);
        $this->assertEquals(12, $adjustments[$soda->id]->current_balance);
        $this->assertEquals(2, $adjustments[$soda->id]->quantity_changed);

        $this->assertEquals(0, $adjustments[$juice->id]->previous_balance);
        $this->assertEquals(3.5, $adjustments[$juice->id]->current_balance);
    }

    public function test_approving_uses_the_live_balance_when_it_changed_after_the_draft(): void
    {
        $soda = $this->product('Soda');
        $this->recordBalance($soda, 10);

        $stock = Stock::factory()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);
        StockItem::factory()->create(['stock_id' => $stock->id, 'item_id' => $soda->id, 'previous_balance' => 10, 'current_balance' => 6, 'variance' => -4]);

        $this->recordBalance($soda, 8);

        Livewire::actingAs($this->owner)
            ->test(Sheet::class, ['stock' => $stock])
            ->call('approve');

        $adjustment = item_balance::where('invoice_number', $stock->reference_no)->firstOrFail();

        $this->assertSame('out', $adjustment->stock_type);
        $this->assertEquals(8, $adjustment->previous_balance);
        $this->assertEquals(6, $adjustment->current_balance);
        $this->assertEquals(-2, $adjustment->quantity_changed);
        $this->assertSame('-2.000', $stock->items()->value('variance'));
    }

    public function test_approved_stock_is_read_only(): void
    {
        $soda = $this->product('Soda');

        $stock = Stock::factory()->approved()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);
        StockItem::factory()->create(['stock_id' => $stock->id, 'item_id' => $soda->id, 'current_balance' => 4]);

        Livewire::actingAs($this->owner)
            ->test(Sheet::class, ['stock' => $stock])
            ->assertSet('isReadOnly', true)
            ->set("counts.{$soda->id}", '99')
            ->call('approve')
            ->call('saveDraft');

        $this->assertSame('4.000', $stock->items()->value('current_balance'));
        $this->assertSame(0, item_balance::count());
    }

    public function test_another_owners_stock_is_forbidden(): void
    {
        $otherOwner = User::factory()->create(['role' => 'owner']);
        $otherBusiness = Business::create(['name' => 'Other', 'status' => 'active', 'owner_id' => $otherOwner->id]);
        $stock = Stock::factory()->create(['business_id' => $otherBusiness->id, 'created_by' => $otherOwner->id]);

        Livewire::actingAs($this->owner)
            ->test(Sheet::class, ['stock' => $stock])
            ->assertForbidden();
    }

    public function test_approving_without_counts_shows_an_error(): void
    {
        $this->product('Soda');

        Livewire::actingAs($this->owner)
            ->test(Sheet::class)
            ->call('approve')
            ->assertNoRedirect()
            ->assertSee('Enter at least one counted quantity before approving.');

        $this->assertSame(0, Stock::count());
    }

    public function test_negative_counts_are_rejected(): void
    {
        $soda = $this->product('Soda');

        Livewire::actingAs($this->owner)
            ->test(Sheet::class)
            ->set("counts.{$soda->id}", '-3')
            ->call('saveDraft')
            ->assertHasErrors("counts.{$soda->id}");

        $this->assertSame(0, Stock::count());
    }

    public function test_new_count_reopens_the_existing_draft(): void
    {
        $draft = Stock::factory()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);

        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('startCount')
            ->assertRedirect(route('owner.stocktaking.sheet', $draft));

        Livewire::actingAs($this->owner)
            ->test(Sheet::class)
            ->assertSet('stockId', $draft->id);
    }

    public function test_only_drafts_can_be_deleted(): void
    {
        $draft = Stock::factory()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);
        $approved = Stock::factory()->approved()->create(['business_id' => $this->business->id, 'created_by' => $this->owner->id]);

        Livewire::actingAs($this->owner)
            ->test(Index::class)
            ->call('deleteDraft', $draft->id)
            ->call('deleteDraft', $approved->id);

        $this->assertSame([$approved->id], Stock::pluck('id')->all());
    }
}
