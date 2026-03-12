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
        Schema::create('hotel_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->nullable()->constrained('hotel_invoices')->onDelete('set null');
            $table->foreignUuid('folio_id')->nullable()->constrained('folios')->onDelete('set null');
            $table->foreignUuid('pos_order_id')->nullable()->constrained('pos_orders')->onDelete('set null');
            $table->foreignUuid('payment_method_id')->constrained('payment_methods')->onDelete('restrict')->comment('FK payment_methods.id');
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('TZS');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->string('reference_no', 100)->nullable()->comment('Mobile money TxID or card auth code');
            $table->string('gateway_ref', 200)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->foreignUuid('received_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id - cashier');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_payments');
    }
};
