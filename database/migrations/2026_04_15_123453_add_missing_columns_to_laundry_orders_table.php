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
        Schema::table('laundry_orders', function (Blueprint $table) {
            $table->timestamp('received_at')->nullable()->after('folio_id');
            $table->string('item_type', 100)->nullable()->after('order_no');
            $table->integer('quantity')->default(1)->after('item_type');
            $table->enum('service_type', ['regular', 'express'])->default('regular')->after('quantity');
            $table->timestamp('expected_completion')->nullable()->after('ready_at');
            $table->text('special_instructions')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laundry_orders', function (Blueprint $table) {
            $table->dropColumn([
                'received_at',
                'item_type',
                'quantity',
                'service_type',
                'expected_completion',
                'special_instructions',
            ]);
        });
    }
};
