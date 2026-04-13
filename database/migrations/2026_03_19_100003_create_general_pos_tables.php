<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ────────────────────────────────────────────────────────────
        // 1. PURCHASE ORDERS
        // ────────────────────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36)->nullable();
            $table->string('po_number', 40);
            $table->char('supplier_id', 36);
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->enum('status', ['draft', 'submitted', 'approved', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->char('requested_by', 36)->nullable();
            $table->char('approved_by', 36)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'po_number']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('supplier_id')->references('id')->on('supliers')->onDelete('restrict');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 2. PURCHASE ORDER ITEMS
        // ────────────────────────────────────────────────────────────
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('purchase_order_id', 36);
            $table->char('item_id', 36);
            $table->char('unit_id', 36);
            $table->decimal('quantity_ordered', 14, 3)->default(0.000);
            $table->decimal('quantity_received', 14, 3)->default(0.000);
            $table->decimal('unit_cost', 14, 2)->default(0.00);
            $table->char('tax_rate_id', 36)->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->string('notes', 200)->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 3. PURCHASE ITEMS
        // ────────────────────────────────────────────────────────────
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('purchase_id', 36);
            $table->char('item_id', 36);
            $table->char('unit_id', 36);
            $table->decimal('quantity', 14, 3)->default(0.000);
            $table->decimal('unit_cost', 14, 2)->default(0.00);
            $table->char('tax_rate_id', 36)->nullable();
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('subtotal', 14, 2)->default(0.00);
            $table->date('expiry_date')->nullable();
            $table->string('batch_no', 60)->nullable();
            $table->timestamps();

            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 4. ITEM VARIANTS
        // ────────────────────────────────────────────────────────────
        Schema::create('item_variants', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('item_id', 36);
            $table->string('name', 100)->comment('e.g. 500ml, Large, Red');
            $table->string('sku', 60)->nullable();
            $table->string('barcode', 60)->nullable();
            $table->decimal('cost_price', 14, 2)->nullable()->comment('NULL = inherits from parent item');
            $table->decimal('selling_price', 14, 2)->nullable()->comment('NULL = inherits from parent item');
            $table->decimal('stock_qty', 14, 3)->default(0.000);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['item_id', 'barcode']);
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 5. PROMOTIONS
        // ────────────────────────────────────────────────────────────
        Schema::create('promotions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36)->nullable()->comment('NULL = applies to all outlets');
            $table->string('name', 100);
            $table->string('code', 30)->nullable()->comment('Voucher code for manual application');
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'free_item'])->default('percentage');
            $table->decimal('value', 10, 2)->default(0.00);
            $table->enum('applies_to', ['order', 'category', 'item', 'member'])->default('order');
            $table->char('category_id', 36)->nullable();
            $table->char('item_id', 36)->nullable();
            $table->decimal('min_order_amount', 14, 2)->nullable();
            $table->integer('max_uses')->nullable()->comment('NULL = unlimited');
            $table->integer('uses_count')->default(0);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->json('active_days')->nullable()->comment('["monday","friday"]');
            $table->time('active_start_time')->nullable();
            $table->time('active_end_time')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamps();

            $table->unique(['business_id', 'code']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('item_variants');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
