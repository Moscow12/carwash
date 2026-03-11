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
        Schema::create('channel_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->enum('channel', ['booking_com', 'expedia', 'airbnb', 'agoda', 'direct', 'other']);
            $table->foreignUuid('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->string('channel_room_id', 100)->comment('Room ID on the OTA platform');
            $table->foreignUuid('rate_plan_id')->nullable()->constrained('rate_plans')->onDelete('set null');
            $table->string('channel_rate_id', 100)->nullable()->comment('Rate ID on the OTA platform');
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channel_mappings');
    }
};
