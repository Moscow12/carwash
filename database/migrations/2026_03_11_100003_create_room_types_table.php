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
        Schema::create('room_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->tinyInteger('max_adults')->default(2);
            $table->tinyInteger('max_children')->default(1);
            $table->decimal('base_price', 12, 2);
            $table->decimal('weekend_price', 12, 2)->nullable()->comment('Overrides base_price on Fri-Sat if set');
            $table->json('amenities')->nullable();
            $table->json('images')->nullable()->comment('Array of image URLs');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
