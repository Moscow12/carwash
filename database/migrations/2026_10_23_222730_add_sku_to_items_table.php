<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedInteger('sku')->nullable()->after('barcode')
                ->comment('Auto-numbered per business, starting at 1');
        });

        // Number existing items per business in the order they were created (soft-deleted ones too, so numbers are never reused)
        DB::table('items')
            ->select('business_id')
            ->distinct()
            ->pluck('business_id')
            ->each(function (string $businessId) {
                $sku = 0;

                DB::table('items')
                    ->where('business_id', $businessId)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->select('id')
                    ->each(function (object $item) use (&$sku) {
                        DB::table('items')->where('id', $item->id)->update(['sku' => ++$sku]);
                    });
            });

        Schema::table('items', function (Blueprint $table) {
            $table->unique(['business_id', 'sku'], 'items_business_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique('items_business_sku_unique');
            $table->dropColumn('sku');
        });
    }
};
