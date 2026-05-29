<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_images', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('rental_unit_id', 36);
            $table->string('image_url', 500);
            $table->string('caption', 200)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->foreign('rental_unit_id')->references('id')->on('rental_units')->onDelete('cascade');
            $table->index(['rental_unit_id', 'display_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_images');
    }
};
