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
        Schema::create('item_modifiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('menu_item_id')->constrained('menu_items')->onDelete('cascade');
            $table->string('group_name', 60)->comment('e.g. Size, Extras, Sauce');
            $table->string('option_name', 80)->comment('e.g. Large, Extra Cheese');
            $table->decimal('price_adjustment', 8, 2)->default(0.00);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_modifiers');
    }
};
