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
        Schema::create('item_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->string('previous_balance');
            $table->string('current_balance');
            $table->enum('stock_type', ['in', 'out'])->default('in');
            $table->enum('stransaction_type', ['initial_stock', 'restock', 'sale', 'adjustment', 'refund', 'return', 'purchase'])->default('initial_stock');
            $table->string('invoice_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_balances');
    }
};
