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
        // ────────────────────────────────────────────────────────────
        // 1. items — fix varchar money columns
        // ────────────────────────────────────────────────────────────
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('cost_price', 14, 2)->default(0.00)->change();
            $table->decimal('selling_price', 14, 2)->default(0.00)->change();
            $table->decimal('market_price', 14, 2)->nullable()->change();
            $table->decimal('commission', 10, 2)->nullable()->change();

            // Add outlet scoping
            if (!Schema::hasColumn('items', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('NULL = available to all outlets');
            }

            // Add soft deletes
            if (!Schema::hasColumn('items', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 2. sales — fix varchar money, add outlet link
        // ────────────────────────────────────────────────────────────
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total_amount', 14, 2)->default(0.00)->change();

            if (!Schema::hasColumn('sales', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('Links shop sale to specific outlet/counter');
            }

            if (!Schema::hasColumn('sales', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->default(0.00)->after('total_amount');
            }

            if (!Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('subtotal');
            }

            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('discount_amount');
            }

            if (!Schema::hasColumn('sales', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 3. sales_items — fix varchar money columns
        // ────────────────────────────────────────────────────────────
        Schema::table('sales_items', function (Blueprint $table) {
            $table->decimal('price', 14, 2)->default(0.00)->change();
            $table->decimal('discount', 10, 2)->nullable()->change();
            $table->decimal('commission', 10, 2)->nullable()->change();

            if (!Schema::hasColumn('sales_items', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('discount');
            }

            if (!Schema::hasColumn('sales_items', 'total')) {
                $table->decimal('total', 14, 2)->default(0.00)->after('tax_amount');
            }
        });

        // ────────────────────────────────────────────────────────────
        // 4. purchases — restructure for multi-item support
        // ────────────────────────────────────────────────────────────
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('quantity', 14, 3)->default(0.000)->change();
            $table->decimal('price', 14, 2)->default(0.00)->change();
            $table->decimal('discount', 10, 2)->nullable()->change();

            if (!Schema::hasColumn('purchases', 'reference_no')) {
                $table->string('reference_no', 60)->nullable()
                    ->after('business_id')
                    ->comment('Supplier invoice / delivery note number');
            }

            if (!Schema::hasColumn('purchases', 'received_date')) {
                $table->date('received_date')->nullable()->after('reference_no');
            }

            if (!Schema::hasColumn('purchases', 'subtotal')) {
                $table->decimal('subtotal', 14, 2)->default(0.00)->after('discount');
            }

            if (!Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('subtotal');
            }

            if (!Schema::hasColumn('purchases', 'total_amount')) {
                $table->decimal('total_amount', 14, 2)->default(0.00)->after('tax_amount');
            }

            if (!Schema::hasColumn('purchases', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('user_id')
                    ->comment('Which outlet received this stock');
            }

            if (!Schema::hasColumn('purchases', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 5. stocktakings — fix varchar money columns
        // ────────────────────────────────────────────────────────────
        Schema::table('stocktakings', function (Blueprint $table) {
            $table->decimal('quantity', 14, 3)->default(0.000)->change();
            $table->decimal('price', 14, 2)->default(0.00)->change();

            if (!Schema::hasColumn('stocktakings', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('Which outlet this stocktake was performed at');
            }

            if (!Schema::hasColumn('stocktakings', 'expected_quantity')) {
                $table->decimal('expected_quantity', 14, 3)->nullable()
                    ->after('quantity')
                    ->comment('System expected balance at time of count');
            }

            if (!Schema::hasColumn('stocktakings', 'variance')) {
                $table->decimal('variance', 14, 3)->nullable()
                    ->after('expected_quantity')
                    ->comment('quantity - expected_quantity');
            }
        });

        // ────────────────────────────────────────────────────────────
        // 6. item_balances — the unified stock ledger
        // ────────────────────────────────────────────────────────────
        Schema::table('item_balances', function (Blueprint $table) {
            $table->decimal('previous_balance', 14, 3)->default(0.000)->change();
            $table->decimal('current_balance', 14, 3)->default(0.000)->change();
            $table->decimal('quantity_changed', 14, 3)->default(0.000)->change();

            if (!Schema::hasColumn('item_balances', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('Which outlet triggered this movement');
                $table->index('outlet_id');
            }

            if (!Schema::hasColumn('item_balances', 'order_id')) {
                $table->char('order_id', 36)->nullable()
                    ->after('outlet_id')
                    ->comment('pos_orders.id that triggered this movement');
                $table->index('order_id');
            }

            if (!Schema::hasColumn('item_balances', 'order_item_id')) {
                $table->char('order_item_id', 36)->nullable()
                    ->after('order_id')
                    ->comment('pos_order_items.id for recipe-level deduction');
                $table->index('order_item_id');
            }

            if (!Schema::hasColumn('item_balances', 'quantity_ml')) {
                $table->decimal('quantity_ml', 10, 2)->nullable()
                    ->after('quantity_changed')
                    ->comment('Volume consumed in ml — bar liquids only');
            }

            if (!Schema::hasColumn('item_balances', 'movement_reason')) {
                $table->enum('movement_reason', [
                    'normal', 'spillage', 'waste', 'void', 'transfer',
                    'expired', 'damage', 'theft', 'opening_count', 'closing_count'
                ])->default('normal')->after('quantity_ml');
            }

            // Update transaction_type enum
            DB::statement("ALTER TABLE item_balances MODIFY COLUMN stransaction_type ENUM(
                'initial_stock', 'restock', 'sale', 'adjustment', 'refund', 'return',
                'purchase', 'waste', 'spillage', 'void', 'transfer_in', 'transfer_out',
                'recipe_deduction'
            ) NOT NULL DEFAULT 'initial_stock'");
        });

        // ────────────────────────────────────────────────────────────
        // 7. pos_orders — add discount tracking column
        // ────────────────────────────────────────────────────────────
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'discount_id')) {
                $table->char('discount_id', 36)->nullable()
                    ->after('discount_amount')
                    ->comment('order_discounts.id if a discount was applied');
            }

            if (!Schema::hasColumn('pos_orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 8. pos_order_items — add item_id link for stock deduction
        // ────────────────────────────────────────────────────────────
        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->decimal('quantity', 8, 3)->default(1.000)->change();

            if (!Schema::hasColumn('pos_order_items', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0.00)->after('subtotal');
            }

            if (!Schema::hasColumn('pos_order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0.00)->after('tax_amount');
            }

            if (!Schema::hasColumn('pos_order_items', 'total')) {
                $table->decimal('total', 12, 2)->default(0.00)->after('discount_amount');
            }
        });

        // ────────────────────────────────────────────────────────────
        // 9. staffs — add user_id link and outlet assignment
        // ────────────────────────────────────────────────────────────
        Schema::table('staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('staffs', 'user_id')) {
                $table->char('user_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('users.id — if this staff member has system login');
            }

            if (!Schema::hasColumn('staffs', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 10. users — add deleted_at for soft deletes
        // ────────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 11. guests — add deleted_at
        // ────────────────────────────────────────────────────────────
        Schema::table('guests', function (Blueprint $table) {
            if (!Schema::hasColumn('guests', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 12. reservations — add number_of_rooms for group bookings
        // ────────────────────────────────────────────────────────────
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'number_of_rooms')) {
                $table->tinyInteger('number_of_rooms')->default(1)
                    ->after('children')
                    ->comment('For group bookings requiring multiple rooms');
            }

            if (!Schema::hasColumn('reservations', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 13. menu_items — add tax_rate and preparation station
        // ────────────────────────────────────────────────────────────
        Schema::table('menu_items', function (Blueprint $table) {
            if (!Schema::hasColumn('menu_items', 'tax_rate_id')) {
                $table->char('tax_rate_id', 36)->nullable()
                    ->after('cost_price')
                    ->comment('tax_rates.id — overrides outlet default');
            }

            if (!Schema::hasColumn('menu_items', 'printer_station')) {
                $table->string('printer_station', 40)->nullable()
                    ->after('prep_time_mins')
                    ->comment('e.g. Bar, Hot Kitchen — routes kitchen ticket');
            }
        });

        // ────────────────────────────────────────────────────────────
        // 14. expenses — add outlet_id scoping
        // ────────────────────────────────────────────────────────────
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'outlet_id')) {
                $table->char('outlet_id', 36)->nullable()
                    ->after('business_id')
                    ->comment('Which outlet incurred this expense');
            }

            if (!Schema::hasColumn('expenses', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // ────────────────────────────────────────────────────────────
        // 15. supliers — add business_id (currently missing!)
        // ────────────────────────────────────────────────────────────
        Schema::table('supliers', function (Blueprint $table) {
            if (!Schema::hasColumn('supliers', 'business_id')) {
                $table->char('business_id', 36)->nullable()
                    ->after('id')
                    ->comment('Supplier may be global or business-specific');
            }

            if (!Schema::hasColumn('supliers', 'tin_number')) {
                $table->string('tin_number', 30)->nullable()
                    ->after('email')
                    ->comment('TRA Tax Identification Number');
            }

            if (!Schema::hasColumn('supliers', 'vrn_number')) {
                $table->string('vrn_number', 30)->nullable()
                    ->after('tin_number');
            }

            if (!Schema::hasColumn('supliers', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Create a clean alias view for the misspelled table
        DB::statement('CREATE OR REPLACE VIEW suppliers AS SELECT * FROM supliers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop supplier view
        DB::statement('DROP VIEW IF EXISTS suppliers');

        // Reverse all changes (only drop added columns, can't revert type changes easily)
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['outlet_id', 'deleted_at']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['outlet_id', 'subtotal', 'discount_amount', 'tax_amount', 'deleted_at']);
        });

        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'total']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['reference_no', 'received_date', 'subtotal', 'tax_amount', 'total_amount', 'outlet_id', 'deleted_at']);
        });

        Schema::table('stocktakings', function (Blueprint $table) {
            $table->dropColumn(['outlet_id', 'expected_quantity', 'variance']);
        });

        Schema::table('item_balances', function (Blueprint $table) {
            $table->dropColumn(['outlet_id', 'order_id', 'order_item_id', 'quantity_ml', 'movement_reason']);
        });

        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_id', 'deleted_at']);
        });

        Schema::table('pos_order_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'discount_amount', 'total']);
        });

        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'deleted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['number_of_rooms', 'deleted_at']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['tax_rate_id', 'printer_station']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['outlet_id', 'deleted_at']);
        });

        Schema::table('supliers', function (Blueprint $table) {
            $table->dropColumn(['business_id', 'tin_number', 'vrn_number', 'deleted_at']);
        });
    }
};
