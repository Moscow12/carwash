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
        Schema::table('items', function (Blueprint $table) {
            $table->string('barcode')->nullable()->after('name');

            // Composite unique index: barcode must be unique per carwash
            $table->unique(['barcode', 'carwash_id'], 'items_barcode_carwash_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique('items_barcode_carwash_unique');
            $table->dropColumn('barcode');
        });
    }
};
