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
        // 1. TABLE RESERVATIONS
        // ────────────────────────────────────────────────────────────
        Schema::create('table_reservations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->string('reservation_no', 30);
            $table->char('customer_id', 36)->nullable();
            $table->string('guest_name', 100)->comment('Walk-in name if no customer record');
            $table->string('guest_phone', 25)->nullable();
            $table->char('table_id', 36)->nullable()->comment('Specific table or NULL for next available');
            $table->string('section', 40)->nullable()->comment('e.g. Indoor, Terrace, VIP Booth');
            $table->tinyInteger('covers')->default(1);
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->smallInteger('duration_mins')->default(90);
            $table->string('occasion', 100)->nullable()->comment('e.g. Birthday, Anniversary');
            $table->decimal('deposit_amount', 10, 2)->default(0.00);
            $table->boolean('deposit_paid')->default(false);
            $table->enum('status', ['pending', 'confirmed', 'seated', 'completed', 'no_show', 'cancelled'])->default('pending');
            $table->char('pos_order_id', 36)->nullable()->comment('Linked when guest seated');
            $table->text('notes')->nullable();
            $table->char('created_by', 36)->nullable();
            $table->timestamps();

            $table->unique(['outlet_id', 'reservation_no']);
            $table->index(['reservation_date', 'reservation_time']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('table_id')->references('id')->on('pos_tables')->onDelete('set null');
            $table->foreign('pos_order_id')->references('id')->on('pos_orders')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 2. WAITER ASSIGNMENTS
        // ────────────────────────────────────────────────────────────
        Schema::create('waiter_assignments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('session_id', 36);
            $table->char('outlet_id', 36);
            $table->char('table_id', 36);
            $table->char('staff_id', 36);
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('released_at')->nullable()->comment('When table cleared/reassigned');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('pos_sessions')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('cascade');
            $table->foreign('table_id')->references('id')->on('pos_tables')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 3. MENU ITEM RECIPES
        // ────────────────────────────────────────────────────────────
        Schema::create('menu_item_recipes', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('menu_item_id', 36);
            $table->char('item_id', 36)->comment('Raw stock item (spirit, ingredient)');
            $table->decimal('quantity', 10, 3)->comment('Amount per 1 unit sold e.g. 0.060 for 60ml');
            $table->char('unit_id', 36);
            $table->boolean('is_optional')->default(false)->comment('Only deduct if modifier selected');
            $table->string('notes', 150)->nullable();
            $table->timestamps();

            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
        });

        // ────────────────────────────────────────────────────────────
        // 4. OUTLET PRINTER STATIONS
        // ────────────────────────────────────────────────────────────
        Schema::create('outlet_printer_stations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->string('name', 60)->comment('e.g. Bar Printer, Hot Kitchen KDS');
            $table->string('station_key', 40)->comment('Matches menu_items.printer_station');
            $table->enum('printer_type', ['receipt', 'kitchen', 'kds', 'label'])->default('kitchen');
            $table->string('printer_ip', 45)->nullable();
            $table->smallInteger('printer_port')->default(9100);
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['outlet_id', 'station_key']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 5. BAR PROFILES
        // ────────────────────────────────────────────────────────────
        Schema::create('bar_profiles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->boolean('enforce_age_check')->default(true);
            $table->tinyInteger('min_drinking_age')->default(18);
            $table->boolean('tab_enabled')->default(true);
            $table->decimal('tab_credit_limit', 10, 2)->nullable();
            $table->boolean('happy_hour_enabled')->default(false);
            $table->time('happy_hour_start')->nullable();
            $table->time('happy_hour_end')->nullable();
            $table->json('happy_hour_days')->nullable();
            $table->decimal('happy_hour_discount_pct', 5, 2)->default(0.00);
            $table->boolean('bottle_service_enabled')->default(false);
            $table->string('receipt_message', 300)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique('outlet_id');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 6. BAR TABS
        // ────────────────────────────────────────────────────────────
        Schema::create('bar_tabs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('tab_no', 20);
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->char('session_id', 36)->nullable();
            $table->char('table_id', 36)->nullable();
            $table->char('customer_id', 36)->nullable();
            $table->char('guest_id', 36)->nullable();
            $table->char('folio_id', 36)->nullable();
            $table->string('tab_name', 100)->nullable();
            $table->enum('status', ['open', 'billed', 'settled', 'voided'])->default('open');
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->char('opened_by', 36)->nullable();
            $table->char('closed_by', 36)->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('tab_no');
            $table->index('status');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('restrict');
            $table->foreign('session_id')->references('id')->on('pos_sessions')->onDelete('set null');
            $table->foreign('table_id')->references('id')->on('pos_tables')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('folio_id')->references('id')->on('folios')->onDelete('set null');
            $table->foreign('opened_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('closed_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 7. BAR TAB ORDERS
        // ────────────────────────────────────────────────────────────
        Schema::create('bar_tab_orders', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('tab_id', 36);
            $table->char('order_id', 36);
            $table->timestamps();

            $table->unique(['tab_id', 'order_id']);
            $table->foreign('tab_id')->references('id')->on('bar_tabs')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('pos_orders')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 8. BAR BOTTLE SERVICES
        // ────────────────────────────────────────────────────────────
        Schema::create('bar_bottle_services', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->char('tab_id', 36)->nullable();
            $table->char('order_id', 36)->nullable();
            $table->char('table_id', 36)->nullable();
            $table->char('menu_item_id', 36);
            $table->tinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->char('assigned_staff', 36)->nullable();
            $table->json('mixers')->nullable();
            $table->enum('status', ['ordered', 'delivered', 'consumed', 'voided'])->default('ordered');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('restrict');
            $table->foreign('tab_id')->references('id')->on('bar_tabs')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('pos_orders')->onDelete('set null');
            $table->foreign('table_id')->references('id')->on('pos_tables')->onDelete('set null');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('restrict');
            $table->foreign('assigned_staff')->references('id')->on('staffs')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 9. BAR HAPPY HOUR PRICES
        // ────────────────────────────────────────────────────────────
        Schema::create('bar_happy_hour_prices', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36);
            $table->char('menu_item_id', 36);
            $table->enum('discount_type', ['percentage', 'fixed_price', 'fixed_discount'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->json('override_days')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['outlet_id', 'menu_item_id']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('cascade');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bar_happy_hour_prices');
        Schema::dropIfExists('bar_bottle_services');
        Schema::dropIfExists('bar_tab_orders');
        Schema::dropIfExists('bar_tabs');
        Schema::dropIfExists('bar_profiles');
        Schema::dropIfExists('outlet_printer_stations');
        Schema::dropIfExists('menu_item_recipes');
        Schema::dropIfExists('waiter_assignments');
        Schema::dropIfExists('table_reservations');
    }
};
