<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE businesses MODIFY COLUMN type ENUM('car_wash','hotel','restaurant','bar','cafe','shop','salon','spa','gym','rental','other') NOT NULL DEFAULT 'car_wash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE businesses MODIFY COLUMN type ENUM('car_wash','hotel','restaurant','bar','cafe','shop','salon','spa','gym','other') NOT NULL DEFAULT 'car_wash'");
    }
};
