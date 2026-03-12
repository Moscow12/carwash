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
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reservation_no', 20)->unique()->comment('e.g. RES-2026-00123');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->foreignUuid('branch_id')->nullable()->constrained('hotel_branches')->onDelete('set null');
            $table->foreignUuid('guest_id')->constrained('guests')->onDelete('restrict');
            $table->foreignUuid('room_type_id')->nullable()->constrained('room_types')->onDelete('set null');
            $table->foreignUuid('rate_plan_id')->nullable()->constrained('rate_plans')->onDelete('set null');
            $table->foreignUuid('source_id')->nullable()->constrained('booking_sources')->onDelete('set null');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->tinyInteger('adults')->default(1);
            $table->tinyInteger('children')->default(0);
            $table->smallInteger('total_nights')->default(1);
            $table->decimal('room_rate', 12, 2)->comment('Per-night rate locked at booking time');
            $table->decimal('total_amount', 14, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0.00);
            $table->enum('status', ['tentative', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show'])->default('tentative');
            $table->text('special_requests')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('channel_ref', 100)->nullable()->comment('OTA booking reference');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('FK users.id - staff who created');
            $table->timestamps();

            $table->index(['check_in_date', 'check_out_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
