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
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description');
            $table->string('cost_price');
            $table->enum('type', ['Service', 'product'])->default('Service');
            $table->enum('product_stock', ['yes', 'no'])->default('no');
            $table->string('selling_price');
            $table->string('market_price')->nullable();
            $table->string('image')->nullable();
            $table->string('commission')->nullable();
            $table->enum('commission_type', ['fixed', 'percentage'])->nullable();
            $table->enum('require_plate_number', ['yes', 'no'])->default('no');
            $table->foreignUuid('unit_id')->constrained('units')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignUuid('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
