<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_features', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('business_id', 36)->comment('Catalog scoped to a business');
            $table->string('feature_name', 100);
            $table->string('feature_description', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->unique(['business_id', 'feature_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_features');
    }
};
