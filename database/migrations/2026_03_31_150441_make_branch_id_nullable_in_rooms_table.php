<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // branch_id is already nullable, foreign key already removed
        // Just add the new unique constraints

        // MySQL doesn't support WHERE clause in indexes like PostgreSQL
        // Instead, we'll create a regular unique index on business_id + number
        // This ensures room numbers are unique per business
        DB::statement('CREATE UNIQUE INDEX rooms_business_number_unique ON rooms (business_id, number)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse the changes
        DB::statement('DROP INDEX rooms_business_number_unique ON rooms');

        // Note: Cannot fully reverse as the original constraint was on (branch_id, number)
        // and branch_id would need to be NOT NULL again, which requires data cleanup
    }
};
