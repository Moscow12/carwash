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
        Schema::create('pos_outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('branch_id')->nullable()->constrained('hotel_branches')->onDelete('set null')->comment('NULL = standalone non-hotel outlet');
            $table->string('name', 100);
            $table->enum('type', ['restaurant', 'bar', 'cafe', 'room_service', 'pool_bar', 'takeaway'])->default('restaurant');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_outlets');
    }
};
