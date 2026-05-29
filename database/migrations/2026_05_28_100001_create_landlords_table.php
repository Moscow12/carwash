<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlords', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36);
            $table->string('name', 150);
            $table->string('phone', 25);
            $table->string('email', 200)->nullable();
            $table->string('address', 255)->nullable();

            $table->char('country_id', 36)->nullable();
            $table->char('region_id', 36)->nullable();
            $table->char('district_id', 36)->nullable();
            $table->char('ward_id', 36)->nullable();
            $table->char('street_id', 36)->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('set null');
            $table->foreign('street_id')->references('id')->on('streets')->onDelete('set null');

            $table->index(['business_id', 'status']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlords');
    }
};
