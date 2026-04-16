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
        Schema::table('lost_and_found', function (Blueprint $table) {
            $table->enum('category', ['electronics', 'clothing', 'jewelry', 'documents', 'personal_items', 'luggage', 'other'])->nullable()->after('room_id');
            $table->string('item_name', 255)->nullable()->after('category');
            $table->string('found_location', 255)->nullable()->after('found_date');
            $table->string('photo_path', 500)->nullable()->after('claimed_by_guest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lost_and_found', function (Blueprint $table) {
            $table->dropColumn(['category', 'item_name', 'found_location', 'photo_path']);
        });
    }
};
