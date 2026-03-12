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
        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('hotel_branches')->onDelete('cascade');
            $table->foreignUuid('room_type_id')->constrained('room_types')->onDelete('restrict');
            $table->string('number', 10)->comment('e.g. 101, 2A, PH1');
            $table->tinyInteger('floor')->nullable();
            $table->enum('status', ['available', 'occupied', 'dirty', 'maintenance', 'blocked'])->default('available');
            $table->boolean('is_smoking')->default(false);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['branch_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
