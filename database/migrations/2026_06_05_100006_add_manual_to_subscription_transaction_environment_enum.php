<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $newEnum = "enum('test','live','manual')";

    private string $oldEnum = "enum('test','live')";

    public function up(): void
    {
        DB::statement("ALTER TABLE subscription_transactions MODIFY COLUMN environment {$this->newEnum} NOT NULL");
    }

    public function down(): void
    {
        DB::table('subscription_transactions')->where('environment', 'manual')->update(['environment' => 'test']);

        DB::statement("ALTER TABLE subscription_transactions MODIFY COLUMN environment {$this->oldEnum} NOT NULL");
    }
};
