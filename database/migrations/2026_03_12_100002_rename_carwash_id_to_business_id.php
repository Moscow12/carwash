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
        // List of all tables with carwash_id foreign key
        $tables = [
            // Core tables
            'categories',
            'payment_methods',
            'staffs',
            'items',
            'customers',
            'sales',
            'item_balances',
            'purchases',
            'stocktakings',
            'bookings',
            'carwash_settings',
            'expense_categories',
            'expenses',

            // Hotel module tables
            'hotel_profiles',
            'hotel_branches',
            'room_types',
            'rooms',
            'rate_plans',
            'guests',
            'booking_sources',
            'reservations',
            'housekeeping_tasks',
            'maintenance_requests',
            'lost_and_found',
            'night_audit_snapshots',

            // Restaurant/Bar module tables
            'pos_outlets',
            'pos_orders',

            // Billing module tables
            'folios',
            'hotel_invoices',
            'hotel_payments',
            'hotel_tax_configs',
            'channel_mappings',
            'communication_logs',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'carwash_id')) {
                continue;
            }

            // Get actual foreign key constraint name from database
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = 'carwash_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            // Drop foreign key if exists
            if (!empty($foreignKeys)) {
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }

            // Rename the column
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('carwash_id', 'business_id');
            });

            // Re-add foreign key constraint with new name
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreign('business_id', "{$tableName}_business_id_foreign")
                    ->references('id')
                    ->on('businesses')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'categories',
            'payment_methods',
            'staffs',
            'items',
            'customers',
            'sales',
            'item_balances',
            'purchases',
            'stocktakings',
            'bookings',
            'carwash_settings',
            'expense_categories',
            'expenses',
            'hotel_profiles',
            'hotel_branches',
            'room_types',
            'rooms',
            'rate_plans',
            'guests',
            'booking_sources',
            'reservations',
            'housekeeping_tasks',
            'maintenance_requests',
            'lost_and_found',
            'night_audit_snapshots',
            'pos_outlets',
            'pos_orders',
            'folios',
            'hotel_invoices',
            'hotel_payments',
            'hotel_tax_configs',
            'channel_mappings',
            'communication_logs',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            if (!Schema::hasColumn($tableName, 'business_id')) {
                continue;
            }

            // Get actual foreign key constraint name
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND COLUMN_NAME = 'business_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$tableName]);

            // Drop foreign key if exists
            if (!empty($foreignKeys)) {
                foreach ($foreignKeys as $fk) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                }
            }

            // Rename back
            Schema::table($tableName, function (Blueprint $table) {
                $table->renameColumn('business_id', 'carwash_id');
            });

            // Re-add old foreign key
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreign('carwash_id', "{$tableName}_carwash_id_foreign")
                    ->references('id')
                    ->on('carwashes')
                    ->onDelete('cascade');
            });
        }
    }
};
