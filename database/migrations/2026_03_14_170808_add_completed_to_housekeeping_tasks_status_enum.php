<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'done', 'verified') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE housekeeping_tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'done', 'verified') NOT NULL DEFAULT 'pending'");
    }
};
