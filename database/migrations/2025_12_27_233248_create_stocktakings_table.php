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
        Schema::create('stocktakings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->string('quantity');
            $table->string('price');
            $table->enum('stocktaking_status', ['received', 'pending', 'canceled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocktakings');
    }
};
