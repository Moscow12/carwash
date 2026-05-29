<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_unit_features', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('rental_unit_id', 36);
            $table->char('unit_feature_id', 36);
            $table->timestamps();

            $table->foreign('rental_unit_id')->references('id')->on('rental_units')->onDelete('cascade');
            $table->foreign('unit_feature_id')->references('id')->on('unit_features')->onDelete('cascade');

            $table->unique(['rental_unit_id', 'unit_feature_id'], 'rental_unit_features_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_unit_features');
    }
};
