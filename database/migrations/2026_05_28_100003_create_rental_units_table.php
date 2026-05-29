<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_units', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('property_id', 36);
            $table->string('unit_number', 50);
            $table->enum('unit_type', ['single', 'double', 'full_house', 'apartment']);
            $table->integer('floor_no')->nullable();
            $table->unsignedSmallInteger('bedrooms')->default(0);
            $table->unsignedSmallInteger('bathrooms')->default(0);
            $table->boolean('has_electricity')->default(false);
            $table->boolean('has_water')->default(false);
            $table->boolean('has_furniture')->default(false);
            $table->decimal('monthly_rent', 12, 2);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->enum('status', ['vacant', 'occupied', 'maintenance', 'reserved'])->default('vacant');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');

            $table->unique(['property_id', 'unit_number']);
            $table->index(['status', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_units');
    }
};
