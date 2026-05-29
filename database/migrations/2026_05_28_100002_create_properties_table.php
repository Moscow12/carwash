<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('landlord_id', 36);
            $table->string('property_name', 150);
            $table->enum('property_type', ['apartment', 'standalone', 'hostel', 'commercial']);

            $table->char('country_id', 36)->nullable();
            $table->char('region_id', 36)->nullable();
            $table->char('district_id', 36)->nullable();
            $table->char('ward_id', 36)->nullable();
            $table->char('street_id', 36)->nullable();
            $table->string('postal_address', 100)->nullable();

            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('landlord_id')->references('id')->on('landlords')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('set null');
            $table->foreign('street_id')->references('id')->on('streets')->onDelete('set null');

            $table->index(['landlord_id', 'status']);
            $table->index('property_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
