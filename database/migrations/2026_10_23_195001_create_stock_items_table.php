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
        Schema::create('stock_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_id')->constrained('stocks')->cascadeOnDelete();
            $table->foreignUuid('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('previous_balance', 14, 3)->default(0);
            $table->decimal('current_balance', 14, 3)->default(0);
            $table->decimal('variance', 14, 3)->default(0);
            $table->timestamps();

            $table->unique(['stock_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
