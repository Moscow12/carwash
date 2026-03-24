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
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchase_id');
            $table->uuid('payment_method_id');
            $table->uuid('business_id');
            $table->uuid('user_id'); // Who recorded the payment
            $table->decimal('amount', 14, 2);
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('payment_date')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('restrict');
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('purchase_id');
            $table->index('payment_method_id');
            $table->index('business_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
