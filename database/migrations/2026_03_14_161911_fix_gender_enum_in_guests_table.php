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
        Schema::table('guests', function (Blueprint $table) {
            DB::statement("ALTER TABLE guests MODIFY COLUMN gender ENUM('male', 'female', 'other') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            DB::statement("ALTER TABLE guests MODIFY COLUMN gender ENUM('M', 'F', 'other') NULL");
        });
    }
};
