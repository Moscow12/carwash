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
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('payment_method_id')->constrained('payment_methods')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade')->nullable();
            $table->foreignUuid('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignUuid('staff_id')->constrained('staffs')->onDelete('cascade')->nullable();
            $table->dateTime('date');
            $table->string('plate_number')->nullable();
            $table->string('discount')->nullable();
            $table->string('commission')->nullable();
            $table->string('price');
            $table->enum('payment_status', ['paid', 'unpaid', 'canceled', 'refunded', 'pending'])->default('unpaid');           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
