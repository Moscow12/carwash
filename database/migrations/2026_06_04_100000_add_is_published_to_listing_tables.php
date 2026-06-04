<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables that become public marketplace listings. */
    private array $tables = ['items', 'rental_units', 'room_types', 'menu_items'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'is_published')) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($table) {
                // Public visibility flag — distinct from internal `status`.
                if (Schema::hasColumn($table, 'status')) {
                    $t->boolean('is_published')->default(false)->after('status');
                } else {
                    $t->boolean('is_published')->default(false);
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'is_published')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('is_published'));
            }
        }
    }
};
