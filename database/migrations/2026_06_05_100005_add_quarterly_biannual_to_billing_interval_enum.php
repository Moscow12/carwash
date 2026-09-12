<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $newEnum = "enum('monthly','quarterly','biannual','yearly')";

    private string $oldEnum = "enum('monthly','yearly')";

    public function up(): void
    {
        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN billing_interval {$this->newEnum} NOT NULL DEFAULT 'monthly'");
    }

    public function down(): void
    {
        // Re-map the new intervals to their closest legacy equivalent before shrinking the enum.
        DB::table('subscription_plans')->where('billing_interval', 'quarterly')->update(['billing_interval' => 'monthly']);
        DB::table('subscription_plans')->where('billing_interval', 'biannual')->update(['billing_interval' => 'yearly']);

        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN billing_interval {$this->oldEnum} NOT NULL DEFAULT 'monthly'");
    }
};
