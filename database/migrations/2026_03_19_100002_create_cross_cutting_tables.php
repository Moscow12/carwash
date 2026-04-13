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
        // 1. TAX RATES — unified across all modules
        // ────────────────────────────────────────────────────────────
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('name', 60)->comment('e.g. VAT 18%, Service Charge 10%');
            $table->string('code', 20)->nullable()->comment('e.g. VAT, SC, LEVY');
            $table->decimal('rate', 6, 4)->comment('0.1800 = 18%, 0.0000 = exempt');
            $table->boolean('is_inclusive')->default(false)->comment('1 = tax already included in price');
            $table->enum('applies_to', ['all', 'food', 'beverages', 'accommodation', 'services', 'goods'])->default('all');
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['business_id', 'code']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
        });

        // ────────────────────────────────────────────────────────────
        // 2. USER BUSINESS ROLES
        // ────────────────────────────────────────────────────────────
        Schema::create('user_business_roles', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('user_id', 36);
            $table->char('business_id', 36);
            $table->char('outlet_id', 36)->nullable()->comment('NULL = role applies to whole business');
            $table->enum('role', [
                'owner', 'admin', 'manager', 'cashier', 'waiter', 'bartender',
                'receptionist', 'housekeeping', 'kitchen', 'accountant', 'viewer'
            ])->default('cashier');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'business_id', 'outlet_id', 'role'], 'user_business_roles_unique');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 3. PAYMENTS — unified table
        // ────────────────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('payable_type', 50)->comment('sale | pos_order | hotel_invoice | folio | bar_tab');
            $table->char('payable_id', 36);
            $table->char('payment_method_id', 36);
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('TZS');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->decimal('amount_local', 14, 2)->comment('amount × exchange_rate, in TZS');
            $table->string('reference_no', 100)->nullable()->comment('M-Pesa TxID, card auth code, cheque no.');
            $table->string('gateway_ref', 200)->nullable();
            $table->char('charged_to_folio', 36)->nullable()->comment('folios.id — room charge');
            $table->char('charged_to_tab', 36)->nullable()->comment('bar_tabs.id — tab charge');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded', 'voided'])->default('completed');
            $table->char('received_by', 36)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->enum('receipt_channel', ['print', 'email', 'sms', 'whatsapp', 'none'])->default('none');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['payable_type', 'payable_id']);
            $table->index(['status', 'paid_at']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('charged_to_folio')->references('id')->on('folios')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 4. ORDER DISCOUNTS
        // ────────────────────────────────────────────────────────────
        Schema::create('order_discounts', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('discountable_type', 50)->comment('sale | pos_order');
            $table->char('discountable_id', 36);
            $table->enum('discount_type', ['percentage', 'fixed_amount', 'voucher', 'complimentary', 'happy_hour', 'staff'])->default('percentage');
            $table->decimal('value', 10, 2)->comment('Percentage (18.00) or fixed amount');
            $table->decimal('amount_deducted', 10, 2)->default(0.00)->comment('Actual TZS deducted');
            $table->string('voucher_code', 40)->nullable();
            $table->string('reason', 200)->nullable();
            $table->char('approved_by', 36)->nullable();
            $table->char('applied_by', 36)->nullable();
            $table->timestamps();

            $table->index(['discountable_type', 'discountable_id']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 5. VOID LOGS
        // ────────────────────────────────────────────────────────────
        Schema::create('void_logs', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('voidable_type', 60)->comment('pos_order | pos_order_item | payment | folio_charge | sale | sale_item');
            $table->char('voidable_id', 36);
            $table->text('reason');
            $table->decimal('amount', 14, 2)->nullable()->comment('Value of what was voided');
            $table->char('voided_by', 36);
            $table->char('approved_by', 36)->nullable();
            $table->timestamp('voided_at')->useCurrent();
            $table->timestamps();

            $table->index(['voidable_type', 'voidable_id']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('voided_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 6. STOCK TRANSFERS
        // ────────────────────────────────────────────────────────────
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('reference_no', 40)->nullable();
            $table->char('from_outlet_id', 36)->nullable()->comment('NULL = main warehouse');
            $table->char('to_outlet_id', 36)->nullable()->comment('NULL = main warehouse');
            $table->enum('status', ['draft', 'requested', 'approved', 'dispatched', 'received', 'cancelled'])->default('draft');
            $table->char('requested_by', 36)->nullable();
            $table->char('approved_by', 36)->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'reference_no']);
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('from_outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
            $table->foreign('to_outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });

        // ────────────────────────────────────────────────────────────
        // 7. STOCK TRANSFER ITEMS
        // ────────────────────────────────────────────────────────────
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('transfer_id', 36);
            $table->char('item_id', 36);
            $table->decimal('quantity_sent', 14, 3)->default(0.000);
            $table->decimal('quantity_received', 14, 3)->nullable()->comment('NULL until receiving outlet confirms');
            $table->char('unit_id', 36);
            $table->string('notes', 200)->nullable();
            $table->timestamps();

            $table->foreign('transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('restrict');
        });

        // ────────────────────────────────────────────────────────────
        // 8. SHIFT SCHEDULES
        // ────────────────────────────────────────────────────────────
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->char('outlet_id', 36)->nullable()->comment('NULL = business-level e.g. hotel front desk');
            $table->char('staff_id', 36);
            $table->date('shift_date');
            $table->enum('shift_type', ['morning', 'afternoon', 'evening', 'night', 'full_day', 'custom'])->default('morning');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['scheduled', 'confirmed', 'started', 'completed', 'absent', 'late'])->default('scheduled');
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();
            $table->string('notes', 200)->nullable();
            $table->timestamps();

            $table->index('shift_date');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('outlet_id')->references('id')->on('pos_outlets')->onDelete('set null');
            $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('void_logs');
        Schema::dropIfExists('order_discounts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('user_business_roles');
        Schema::dropIfExists('tax_rates');
    }
};
