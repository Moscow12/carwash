<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CarwashSetting extends Model
{
    use HasUuids;

    protected $table = 'carwash_settings';

    protected $fillable = [
        'carwash_id',
        // Business Settings
        'business_name',
        'business_logo',
        'business_phone',
        'business_email',
        'business_address',
        'currency',
        'currency_symbol',
        'currency_position',
        'decimal_places',
        'thousand_separator',
        'decimal_separator',
        // Tax Settings
        'tax_enabled',
        'tax_name',
        'tax_rate',
        'tax_inclusive',
        'tax_number',
        // Product Settings
        'show_product_image',
        'show_product_description',
        'enable_stock_management',
        'allow_negative_stock',
        'low_stock_threshold',
        // Contact Settings
        'contact_phone',
        'contact_email',
        'contact_whatsapp',
        'contact_address',
        // Sale Settings
        'sale_prefix',
        'sale_number_length',
        'auto_generate_sale_number',
        'require_customer_for_sale',
        'allow_discount',
        'max_discount_percent',
        // POS Settings
        'pos_keyboard_shortcuts',
        'pos_disable_multiple_pay',
        'pos_disable_draft',
        'pos_disable_express_checkout',
        'pos_show_product_suggestion',
        'pos_show_recent_transactions',
        'pos_enable_discount',
        'pos_enable_order_tax',
        'pos_subtotal_editable',
        'pos_disable_suspend_sale',
        'pos_show_transaction_date',
        'pos_enable_service_staff',
        'pos_require_service_staff',
        'pos_disable_credit_sale',
        'pos_enable_weighing_scale',
        'pos_show_invoice_scheme',
        'pos_show_invoice_layout',
        'pos_print_invoice_on_suspend',
        // Purchase Settings
        'purchase_prefix',
        'purchase_number_length',
        'require_supplier_for_purchase',
        // Payment Settings
        'enabled_payment_methods',
        'default_payment_method',
        'allow_partial_payment',
        // Dashboard Settings
        'dashboard_start_date',
        'show_dashboard_sales',
        'show_dashboard_purchases',
        'show_dashboard_stock',
        // System Settings
        'date_format',
        'time_format',
        'timezone',
        'language',
        // Prefixes
        'invoice_prefix',
        'receipt_prefix',
        'quotation_prefix',
        'stocktaking_prefix',
        'expense_prefix',
        // Email Settings
        'email_enabled',
        'email_from_name',
        'email_from_address',
        'email_sale_notification',
        'email_payment_notification',
        // SMS Settings
        'sms_enabled',
        'sms_provider',
        'sms_api_key',
        'sms_sender_id',
        'sms_sale_notification',
        'sms_payment_notification',
        // Reward Point Settings
        'reward_enabled',
        'reward_points_per_amount',
        'reward_amount_per_point',
        'reward_min_redeem_points',
        // Modules
        'module_sales',
        'module_purchases',
        'module_stock',
        'module_customers',
        'module_suppliers',
        'module_staffs',
        'module_reports',
        'module_expenses',
        // Custom Labels
        'custom_labels',
        // Receipt/Invoice Settings
        'receipt_header',
        'receipt_footer',
        'show_logo_on_receipt',
        'show_tax_on_receipt',
    ];

    protected $casts = [
        // Booleans
        'tax_enabled' => 'boolean',
        'tax_inclusive' => 'boolean',
        'show_product_image' => 'boolean',
        'show_product_description' => 'boolean',
        'enable_stock_management' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'auto_generate_sale_number' => 'boolean',
        'require_customer_for_sale' => 'boolean',
        'allow_discount' => 'boolean',
        'pos_disable_multiple_pay' => 'boolean',
        'pos_disable_draft' => 'boolean',
        'pos_disable_express_checkout' => 'boolean',
        'pos_show_product_suggestion' => 'boolean',
        'pos_show_recent_transactions' => 'boolean',
        'pos_enable_discount' => 'boolean',
        'pos_enable_order_tax' => 'boolean',
        'pos_subtotal_editable' => 'boolean',
        'pos_disable_suspend_sale' => 'boolean',
        'pos_show_transaction_date' => 'boolean',
        'pos_enable_service_staff' => 'boolean',
        'pos_require_service_staff' => 'boolean',
        'pos_disable_credit_sale' => 'boolean',
        'pos_enable_weighing_scale' => 'boolean',
        'pos_show_invoice_scheme' => 'boolean',
        'pos_show_invoice_layout' => 'boolean',
        'pos_print_invoice_on_suspend' => 'boolean',
        'require_supplier_for_purchase' => 'boolean',
        'allow_partial_payment' => 'boolean',
        'show_dashboard_sales' => 'boolean',
        'show_dashboard_purchases' => 'boolean',
        'show_dashboard_stock' => 'boolean',
        'email_enabled' => 'boolean',
        'email_sale_notification' => 'boolean',
        'email_payment_notification' => 'boolean',
        'sms_enabled' => 'boolean',
        'sms_sale_notification' => 'boolean',
        'sms_payment_notification' => 'boolean',
        'reward_enabled' => 'boolean',
        'module_sales' => 'boolean',
        'module_purchases' => 'boolean',
        'module_stock' => 'boolean',
        'module_customers' => 'boolean',
        'module_suppliers' => 'boolean',
        'module_staffs' => 'boolean',
        'module_reports' => 'boolean',
        'module_expenses' => 'boolean',
        'show_logo_on_receipt' => 'boolean',
        'show_tax_on_receipt' => 'boolean',
        // Decimals
        'tax_rate' => 'decimal:2',
        'max_discount_percent' => 'decimal:2',
        'reward_points_per_amount' => 'decimal:2',
        'reward_amount_per_point' => 'decimal:2',
        // Integers
        'decimal_places' => 'integer',
        'low_stock_threshold' => 'integer',
        'sale_number_length' => 'integer',
        'purchase_number_length' => 'integer',
        'reward_min_redeem_points' => 'integer',
        // JSON
        'pos_keyboard_shortcuts' => 'array',
        'enabled_payment_methods' => 'array',
        'custom_labels' => 'array',
    ];

    // Relationships
    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    // Get or create settings for a carwash
    public static function getForCarwash($carwashId)
    {
        return self::firstOrCreate(
            ['carwash_id' => $carwashId],
            self::getDefaults()
        );
    }

    // Default settings
    public static function getDefaults()
    {
        return [
            'currency' => 'TZS',
            'currency_symbol' => 'TZS',
            'currency_position' => 'before',
            'decimal_places' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'tax_enabled' => false,
            'tax_name' => 'VAT',
            'tax_rate' => 0,
            'show_product_image' => true,
            'show_product_description' => true,
            'enable_stock_management' => true,
            'low_stock_threshold' => 10,
            'sale_prefix' => 'SL',
            'sale_number_length' => 6,
            'auto_generate_sale_number' => true,
            'allow_discount' => true,
            'max_discount_percent' => 100,
            'pos_keyboard_shortcuts' => self::getDefaultKeyboardShortcuts(),
            'pos_show_product_suggestion' => true,
            'pos_show_recent_transactions' => true,
            'pos_enable_discount' => true,
            'pos_enable_order_tax' => true,
            'pos_show_transaction_date' => true,
            'pos_require_service_staff' => true,
            'purchase_prefix' => 'PO',
            'purchase_number_length' => 6,
            'require_supplier_for_purchase' => true,
            'allow_partial_payment' => true,
            'dashboard_start_date' => 'today',
            'show_dashboard_sales' => true,
            'show_dashboard_purchases' => true,
            'show_dashboard_stock' => true,
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'timezone' => 'Africa/Dar_es_Salaam',
            'language' => 'en',
            'invoice_prefix' => 'INV',
            'receipt_prefix' => 'RCP',
            'quotation_prefix' => 'QT',
            'stocktaking_prefix' => 'ST',
            'expense_prefix' => 'EXP',
            'module_sales' => true,
            'module_purchases' => true,
            'module_stock' => true,
            'module_customers' => true,
            'module_suppliers' => true,
            'module_staffs' => true,
            'module_reports' => true,
        ];
    }

    // Default keyboard shortcuts
    public static function getDefaultKeyboardShortcuts()
    {
        return [
            'express_checkout' => 'shift+e',
            'pay_checkout' => 'shift+p',
            'draft' => 'shift+d',
            'cancel' => 'shift+c',
            'product_quantity' => 'f2',
            'weighing_scale' => '',
            'edit_discount' => 'shift+i',
            'edit_order_tax' => 'shift+t',
            'add_payment_row' => 'shift+r',
            'finalize_payment' => 'shift+f',
            'add_new_product' => 'f4',
        ];
    }

    // Helper to format currency
    public function formatCurrency($amount)
    {
        $formatted = number_format(
            $amount,
            $this->decimal_places,
            $this->decimal_separator,
            $this->thousand_separator
        );

        return $this->currency_position === 'before'
            ? "{$this->currency_symbol} {$formatted}"
            : "{$formatted} {$this->currency_symbol}";
    }
}
