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
        Schema::create('lost_and_found', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('hotel_branches')->onDelete('cascade');
            $table->foreignUuid('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->string('item_description', 300);
            $table->date('found_date');
            $table->enum('status', ['found', 'claimed', 'disposed', 'donated'])->default('found');
            $table->foreignUuid('found_by')->nullable()->constrained('staffs')->onDelete('set null')->comment('FK staffs.id');
            $table->foreignUuid('claimed_by_guest')->nullable()->constrained('guests')->onDelete('set null')->comment('FK guests.id');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lost_and_found');
    }
};
