<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->char('user_id', 36)
                ->nullable()
                ->after('business_id')
                ->comment('NULL = external landlord with no platform login');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->unique(['business_id', 'user_id'], 'landlords_business_user_unique');
        });

        DB::statement("ALTER TABLE user_business_roles MODIFY COLUMN role ENUM(
            'owner','admin','manager','cashier','waiter','bartender',
            'receptionist','housekeeping','kitchen','accountant','viewer','landlord'
        ) NOT NULL DEFAULT 'cashier'");
    }

    public function down(): void
    {
        Schema::table('landlords', function (Blueprint $table) {
            $table->dropUnique('landlords_business_user_unique');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        DB::statement("ALTER TABLE user_business_roles MODIFY COLUMN role ENUM(
            'owner','admin','manager','cashier','waiter','bartender',
            'receptionist','housekeeping','kitchen','accountant','viewer'
        ) NOT NULL DEFAULT 'cashier'");
    }
};
