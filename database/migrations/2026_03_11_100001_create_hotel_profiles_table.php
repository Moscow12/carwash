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
        Schema::create('hotel_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->unique()->constrained('carwashes')->onDelete('cascade');
            $table->tinyInteger('star_rating')->nullable()->comment('1-5');
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('11:00:00');
            $table->decimal('late_checkout_fee', 12, 2)->nullable();
            $table->smallInteger('total_rooms')->default(0);
            $table->string('tin_number', 60)->nullable()->comment('TRA Tax Identification Number');
            $table->string('vrn_number', 60)->nullable()->comment('VAT Registration Number (TRA)');
            $table->json('amenities')->nullable()->comment('Array of amenity strings e.g. ["Pool","WiFi","Gym"]');
            $table->json('policies')->nullable()->comment('Cancellation, pets, smoking policies');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_profiles');
    }
};
