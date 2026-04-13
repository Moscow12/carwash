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
        Schema::create('business_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('business_id')->constrained('businesses')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('code', 20)->comment('Short identifier e.g. CW01, HTL01, RST01');
            $table->enum('type', [
                'car_wash',
                'hotel',
                'restaurant',
                'bar',
                'cafe',
                'shop',
                'salon',
                'spa',
                'gym',
                'other'
            ])->default('car_wash');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 25)->nullable();
            $table->string('email', 200)->nullable();

            // Location details
            $table->foreignUuid('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignUuid('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignUuid('ward_id')->nullable()->constrained('wards')->onDelete('set null');
            $table->foreignUuid('street_id')->nullable()->constrained('streets')->onDelete('set null');

            // Operating details
            $table->json('operating_hours')->nullable()->comment('JSON: {"monday": {"open": "08:00", "close": "18:00"}, ...}');
            $table->boolean('is_main')->default(false)->comment('Is this the main/headquarters location?');
            $table->string('manager_name', 100)->nullable();
            $table->string('manager_phone', 25)->nullable();

            // Status
            $table->enum('status', ['active', 'inactive', 'under_maintenance'])->default('active');
            $table->timestamps();

            // Unique constraint
            $table->unique(['business_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_locations');
    }
};
