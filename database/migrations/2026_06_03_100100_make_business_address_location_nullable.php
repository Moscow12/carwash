<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sign-up only collects business name + type, so address and the location
     * hierarchy must be optional (the owner completes them later in My Business).
     * The location columns carry FKs, so we make the underlying columns nullable
     * directly — a nullable FK column is still valid (NULL is simply not checked).
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE businesses MODIFY COLUMN address VARCHAR(255) NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN region_id CHAR(36) NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN district_id CHAR(36) NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN ward_id CHAR(36) NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN street_id CHAR(36) NULL');
    }

    public function down(): void
    {
        // Only revert if no rows would violate the NOT NULL constraint.
        $hasNulls = DB::table('businesses')
            ->where(function ($q) {
                $q->whereNull('address')->orWhereNull('region_id')->orWhereNull('district_id')
                  ->orWhereNull('ward_id')->orWhereNull('street_id');
            })->exists();

        if ($hasNulls) {
            return; // leave nullable to avoid breaking on existing NULL rows
        }

        DB::statement('ALTER TABLE businesses MODIFY COLUMN address VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN region_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN district_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN ward_id CHAR(36) NOT NULL');
        DB::statement('ALTER TABLE businesses MODIFY COLUMN street_id CHAR(36) NOT NULL');
    }
};
