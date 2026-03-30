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
        // 1. ROOM RATE OVERRIDES
        // ────────────────────────────────────────────────────────────
        Schema::create('room_rate_overrides', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('room_type_id', 36)->nullable()->comment('NULL = applies to all room types');
            $table->char('rate_plan_id', 36)->nullable()->comment('NULL = applies to all rate plans');
            $table->string('name', 100)->comment('e.g. Christmas Rate, Marathon Weekend');
            $table->decimal('override_price', 12, 2);
            $table->date('date_from');
            $table->date('date_to');
            $table->tinyInteger('min_nights')->default(1);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['date_from', 'date_to']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('room_type_id')->references('id')->on('room_types')->onDelete('cascade');
            $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 2. RESERVATION GUESTS
        // ────────────────────────────────────────────────────────────
        Schema::create('reservation_guests', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('reservation_id', 36);
            $table->char('guest_id', 36);
            $table->boolean('is_primary')->default(false);
            $table->char('room_id', 36)->nullable()->comment('Specific room in group booking');
            $table->timestamps();

            $table->unique(['reservation_id', 'guest_id']);
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('restrict');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 3. HOTEL AMENITY REQUESTS
        // ────────────────────────────────────────────────────────────
        Schema::create('hotel_amenity_requests', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('reservation_id', 36)->nullable();
            $table->char('room_id', 36)->nullable();
            $table->char('guest_id', 36)->nullable();
            $table->char('folio_id', 36)->nullable()->comment('Set when request carries a charge');
            $table->string('amenity', 100)->comment('e.g. Extra Bed, Baby Cot, Iron & Board');
            $table->tinyInteger('quantity')->default(1);
            $table->decimal('charge_amount', 10, 2)->default(0.00);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->char('delivered_by', 36)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('folio_id')->references('id')->on('folios')->onDelete('set null');
            $table->foreign('delivered_by')->references('id')->on('staffs')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 4. WAKEUP CALLS
        // ────────────────────────────────────────────────────────────
        Schema::create('wakeup_calls', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('reservation_id', 36)->nullable();
            $table->char('room_id', 36);
            $table->char('guest_id', 36)->nullable();
            $table->dateTime('scheduled_at');
            $table->boolean('repeat_daily')->default(false);
            $table->enum('status', ['pending', 'delivered', 'missed', 'cancelled'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->char('delivered_by', 36)->nullable();
            $table->string('notes', 200)->nullable();
            $table->timestamps();

            $table->index('scheduled_at');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 5. LAUNDRY ORDERS
        // ────────────────────────────────────────────────────────────
        Schema::create('laundry_orders', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('reservation_id', 36)->nullable();
            $table->char('room_id', 36);
            $table->char('guest_id', 36)->nullable();
            $table->char('folio_id', 36)->nullable();
            $table->string('order_no', 30);
            $table->timestamp('collected_at')->nullable();
            $table->char('collected_by', 36)->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->char('delivered_by', 36)->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('status', ['collected', 'processing', 'ready', 'delivered', 'cancelled'])->default('collected');
            $table->boolean('is_express')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'order_no']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('set null');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('restrict');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('folio_id')->references('id')->on('folios')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 6. LAUNDRY ORDER ITEMS
        // ────────────────────────────────────────────────────────────
        Schema::create('laundry_order_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('laundry_order_id', 36);
            $table->string('item_description', 100)->comment('e.g. Shirt, Trouser, Dress');
            $table->enum('service_type', ['wash', 'dry_clean', 'press', 'wash_and_press'])->default('wash');
            $table->tinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('laundry_order_id')->references('id')->on('laundry_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundry_order_items');
        Schema::dropIfExists('laundry_orders');
        Schema::dropIfExists('wakeup_calls');
        Schema::dropIfExists('hotel_amenity_requests');
        Schema::dropIfExists('reservation_guests');
        Schema::dropIfExists('room_rate_overrides');
    }
};
