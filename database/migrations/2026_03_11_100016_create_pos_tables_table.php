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
        Schema::create('pos_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('outlet_id')->constrained('pos_outlets')->onDelete('cascade');
            $table->string('table_number', 10);
            $table->tinyInteger('capacity')->default(4);
            $table->string('section', 40)->nullable()->comment('e.g. Indoor, Terrace, VIP');
            $table->enum('status', ['available', 'occupied', 'reserved', 'dirty'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['outlet_id', 'table_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_tables');
    }
};
