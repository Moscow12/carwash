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
            $table->string('country', 100)->nullable()->after('nationality');
            $table->string('coming_from', 255)->nullable()->after('country');
            $table->string('going_to', 255)->nullable()->after('coming_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn(['country', 'coming_from', 'going_to']);
        });
    }
};
