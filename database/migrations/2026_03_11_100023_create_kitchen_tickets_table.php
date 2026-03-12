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
        Schema::create('kitchen_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_item_id')->constrained('pos_order_items')->onDelete('cascade');
            $table->foreignUuid('order_id')->constrained('pos_orders')->onDelete('cascade');
            $table->foreignUuid('outlet_id')->constrained('pos_outlets')->onDelete('cascade');
            $table->string('station', 40)->nullable()->comment('e.g. Hot Kitchen, Bar, Pastry');
            $table->enum('status', ['queued', 'preparing', 'ready', 'served', 'cancelled'])->default('queued');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_tickets');
    }
};
