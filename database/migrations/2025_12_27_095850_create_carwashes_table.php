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
        Schema::create('carwashes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('address');
            
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('operating_hours')->nullable();
            $table->string('resentative_name');
            $table->string('resentative_phone');
            $table->foreignUuid('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignUuid('district_id')->constrained('districts')->onDelete('cascade');
            $table->foreignUuid('ward_id')->constrained('wards')->onDelete('cascade');
            $table->foreignUuid('street_id')->constrained('streets')->onDelete('cascade')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carwashes');
    }
};
