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
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('pos_orders')->onDelete('cascade');
            $table->foreignUuid('menu_item_id')->constrained('menu_items')->onDelete('restrict');
            $table->tinyInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->comment('Price snapshot at order time');
            $table->json('modifiers')->nullable()->comment('Snapshot of applied modifiers and prices');
            $table->decimal('subtotal', 12, 2);
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'voided'])->default('pending');
            $table->text('kitchen_notes')->nullable();
            $table->string('voided_reason', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};
