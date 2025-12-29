<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carwash_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carwash_id')->constrained('carwashes')->onDelete('cascade');

            // Business Settings
            $table->string('business_name')->nullable();
            $table->string('business_logo')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->text('business_address')->nullable();
            $table->string('currency')->default('TZS');
            $table->string('currency_symbol')->default('TZS');
            $table->string('currency_position')->default('before'); // before or after
            $table->integer('decimal_places')->default(2);
            $table->string('thousand_separator')->default(',');
            $table->string('decimal_separator')->default('.');

            // Tax Settings
            $table->boolean('tax_enabled')->default(false);
            $table->string('tax_name')->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(false);
            $table->string('tax_number')->nullable();

            // Product Settings
            $table->boolean('show_product_image')->default(true);
            $table->boolean('show_product_description')->default(true);
            $table->boolean('enable_stock_management')->default(true);
            $table->boolean('allow_negative_stock')->default(false);
            $table->integer('low_stock_threshold')->default(10);

            // Contact Settings
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->text('contact_address')->nullable();

            // Sale Settings
            $table->string('sale_prefix')->default('SL');
            $table->integer('sale_number_length')->default(6);
            $table->boolean('auto_generate_sale_number')->default(true);
            $table->boolean('require_customer_for_sale')->default(false);
            $table->boolean('allow_discount')->default(true);
            $table->decimal('max_discount_percent', 5, 2)->default(100);

            // POS Settings
            $table->json('pos_keyboard_shortcuts')->nullable();
            $table->boolean('pos_disable_multiple_pay')->default(false);
            $table->boolean('pos_disable_draft')->default(false);
            $table->boolean('pos_disable_express_checkout')->default(false);
            $table->boolean('pos_show_product_suggestion')->default(true);
            $table->boolean('pos_show_recent_transactions')->default(true);
            $table->boolean('pos_enable_discount')->default(true);
            $table->boolean('pos_enable_order_tax')->default(true);
            $table->boolean('pos_subtotal_editable')->default(false);
            $table->boolean('pos_disable_suspend_sale')->default(false);
            $table->boolean('pos_show_transaction_date')->default(true);
            $table->boolean('pos_enable_service_staff')->default(false);
            $table->boolean('pos_require_service_staff')->default(true);
            $table->boolean('pos_disable_credit_sale')->default(false);
            $table->boolean('pos_enable_weighing_scale')->default(false);
            $table->boolean('pos_show_invoice_scheme')->default(true);
            $table->boolean('pos_show_invoice_layout')->default(true);
            $table->boolean('pos_print_invoice_on_suspend')->default(false);

            // Purchase Settings
            $table->string('purchase_prefix')->default('PO');
            $table->integer('purchase_number_length')->default(6);
            $table->boolean('require_supplier_for_purchase')->default(true);

            // Payment Settings
            $table->json('enabled_payment_methods')->nullable();
            $table->string('default_payment_method')->nullable();
            $table->boolean('allow_partial_payment')->default(true);

            // Dashboard Settings
            $table->string('dashboard_start_date')->default('today');
            $table->boolean('show_dashboard_sales')->default(true);
            $table->boolean('show_dashboard_purchases')->default(true);
            $table->boolean('show_dashboard_stock')->default(true);

            // System Settings
            $table->string('date_format')->default('Y-m-d');
            $table->string('time_format')->default('H:i');
            $table->string('timezone')->default('Africa/Dar_es_Salaam');
            $table->string('language')->default('en');

            // Prefixes
            $table->string('invoice_prefix')->default('INV');
            $table->string('receipt_prefix')->default('RCP');
            $table->string('quotation_prefix')->default('QT');
            $table->string('stocktaking_prefix')->default('ST');
            $table->string('expense_prefix')->default('EXP');

            // Email Settings
            $table->boolean('email_enabled')->default(false);
            $table->string('email_from_name')->nullable();
            $table->string('email_from_address')->nullable();
            $table->boolean('email_sale_notification')->default(false);
            $table->boolean('email_payment_notification')->default(false);

            // SMS Settings
            $table->boolean('sms_enabled')->default(false);
            $table->string('sms_provider')->nullable();
            $table->string('sms_api_key')->nullable();
            $table->string('sms_sender_id')->nullable();
            $table->boolean('sms_sale_notification')->default(false);
            $table->boolean('sms_payment_notification')->default(false);

            // Reward Point Settings
            $table->boolean('reward_enabled')->default(false);
            $table->decimal('reward_points_per_amount', 10, 2)->default(1);
            $table->decimal('reward_amount_per_point', 10, 2)->default(1);
            $table->integer('reward_min_redeem_points')->default(100);

            // Modules
            $table->boolean('module_sales')->default(true);
            $table->boolean('module_purchases')->default(true);
            $table->boolean('module_stock')->default(true);
            $table->boolean('module_customers')->default(true);
            $table->boolean('module_suppliers')->default(true);
            $table->boolean('module_staffs')->default(true);
            $table->boolean('module_reports')->default(true);
            $table->boolean('module_expenses')->default(false);

            // Custom Labels
            $table->json('custom_labels')->nullable();

            // Receipt/Invoice Settings
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->boolean('show_logo_on_receipt')->default(true);
            $table->boolean('show_tax_on_receipt')->default(true);

            $table->timestamps();

            $table->unique('carwash_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carwash_settings');
    }
};
