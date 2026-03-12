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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('pos_outlets')->onDelete('cascade');
            $table->foreignUuid('category_id')->constrained('menu_categories')->onDelete('cascade');
            $table->foreignUuid('item_id')->nullable()->constrained('items')->onDelete('set null')->comment('FK items.id - for stock deduction');
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->string('sku', 50)->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->nullable();
            $table->string('image', 500)->nullable();
            $table->json('allergens')->nullable()->comment('Array e.g. ["gluten","nuts","dairy"]');
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_vegan')->default(false);
            $table->boolean('is_available')->default(true);
            $table->tinyInteger('prep_time_mins')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
