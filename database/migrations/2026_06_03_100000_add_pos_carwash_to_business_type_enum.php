<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The app's UI offers `pos` and `carwash` as business types, but the original
     * enum only had `shop`/`car_wash`. Extend the enum so those values are accepted
     * (legacy values are kept so existing rows stay valid).
     */
    private string $newEnum = "enum('car_wash','carwash','hotel','restaurant','bar','cafe','shop','pos','salon','spa','gym','rental','other')";

    private string $oldEnum = "enum('car_wash','hotel','restaurant','bar','cafe','shop','salon','spa','gym','rental','other')";

    public function up(): void
    {
        DB::statement("ALTER TABLE businesses MODIFY COLUMN type {$this->newEnum} NOT NULL DEFAULT 'car_wash'");
    }

    public function down(): void
    {
        // Re-map the new values back to their closest legacy equivalents before shrinking the enum.
        DB::table('businesses')->where('type', 'carwash')->update(['type' => 'car_wash']);
        DB::table('businesses')->where('type', 'pos')->update(['type' => 'shop']);

        DB::statement("ALTER TABLE businesses MODIFY COLUMN type {$this->oldEnum} NOT NULL DEFAULT 'car_wash'");
    }
};
