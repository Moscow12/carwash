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
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reservation_id')->constrained('reservations')->onDelete('cascade');
            $table->foreignUuid('room_id')->constrained('rooms')->onDelete('restrict');
            $table->timestamp('allocated_at')->nullable();
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            $table->foreignUuid('allocated_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_allocations');
    }
};
