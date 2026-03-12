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
        Schema::create('night_audit_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('branch_id')->constrained('hotel_branches')->onDelete('cascade');
            $table->date('audit_date');
            $table->smallInteger('total_rooms')->nullable();
            $table->smallInteger('occupied_rooms')->nullable();
            $table->decimal('occupancy_pct', 5, 2)->nullable()->comment('0.00-100.00');
            $table->decimal('adr', 12, 2)->nullable()->comment('Average Daily Rate');
            $table->decimal('revpar', 12, 2)->nullable()->comment('Revenue Per Available Room');
            $table->decimal('room_revenue', 14, 2)->nullable();
            $table->decimal('fb_revenue', 14, 2)->nullable()->comment('Food & Beverage revenue');
            $table->decimal('total_revenue', 14, 2)->nullable();
            $table->smallInteger('new_arrivals')->nullable();
            $table->smallInteger('departures')->nullable();
            $table->smallInteger('no_shows')->nullable();
            $table->foreignUuid('run_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id');
            $table->timestamp('run_at')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'audit_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('night_audit_snapshots');
    }
};
