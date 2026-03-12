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
        Schema::create('folio_charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('folio_id')->constrained('folios')->onDelete('cascade');
            $table->foreignUuid('pos_order_id')->nullable()->constrained('pos_orders')->onDelete('set null')->comment('Set when charge originates from an F&B order');
            $table->enum('charge_type', ['room', 'restaurant', 'bar', 'room_service', 'laundry', 'minibar', 'telephone', 'other'])->default('room');
            $table->string('description', 300);
            $table->decimal('quantity', 8, 3)->default(1.000);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->foreignUuid('posted_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id');
            $table->timestamp('posted_at')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folio_charges');
    }
};
