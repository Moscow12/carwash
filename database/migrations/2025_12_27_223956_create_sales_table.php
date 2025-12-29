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
            $table->enum('sale_status', ['completed', 'pending', 'canceled', 'refunded'])->default('pending');
            $table->enum('sale_type', ['in-store', 'online'])->default('in-store');
            $table->string('sale_date');
            $table->date('payment_date')->nullable();
            $table->string('notes')->nullable();
            $table->enum('receipt_type', ['email', 'sms', 'none'])->default('none');
            $table->enum('payment_type', ['cash', 'credit'])->default('cash');
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade')->nullable();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('total_amount');
            $table->enum('payment_status', ['paid', 'unpaid', 'canceled', 'refunded', 'pending', 'partial'])->default('unpaid');           
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
