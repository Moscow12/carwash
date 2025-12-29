<?php

namespace App\Livewire\Owner\Settings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\CarwashSetting;
use App\Models\payment_method;

#[Layout('components.layouts.app-owner')]
class Setup extends Component
{
    use WithFileUploads;

    // Carwash selection
    public $selectedCarwash = '';
    public $ownerCarwashes = [];

    // Active tab
    public $activeTab = 'business';

    // Business Settings
    public $business_name = '';
    public $business_logo;
    public $business_phone = '';
    public $business_email = '';
    public $business_address = '';
    public $currency = 'TZS';
    public $currency_symbol = 'TZS';
    public $currency_position = 'before';
    public $decimal_places = 2;
    public $thousand_separator = ',';
    public $decimal_separator = '.';

    // Tax Settings
    public $tax_enabled = false;
    public $tax_name = 'VAT';
    public $tax_rate = 0;
    public $tax_inclusive = false;
    public $tax_number = '';

    // Product Settings
    public $show_product_image = true;
    public $show_product_description = true;
    public $enable_stock_management = true;
    public $allow_negative_stock = false;
    public $low_stock_threshold = 10;

    // Contact Settings
    public $contact_phone = '';
    public $contact_email = '';
    public $contact_whatsapp = '';
    public $contact_address = '';

    // Sale Settings
    public $sale_prefix = 'SL';
    public $sale_number_length = 6;
    public $auto_generate_sale_number = true;
    public $require_customer_for_sale = false;
    public $allow_discount = true;
    public $max_discount_percent = 100;

    // POS Settings
    public $pos_keyboard_shortcuts = [];
    public $pos_disable_multiple_pay = false;
    public $pos_disable_draft = false;
    public $pos_disable_express_checkout = false;
    public $pos_show_product_suggestion = true;
    public $pos_show_recent_transactions = true;
    public $pos_enable_discount = true;
    public $pos_enable_order_tax = true;
    public $pos_subtotal_editable = false;
    public $pos_disable_suspend_sale = false;
    public $pos_show_transaction_date = true;
    public $pos_enable_service_staff = false;
    public $pos_require_service_staff = true;
    public $pos_disable_credit_sale = false;
    public $pos_enable_weighing_scale = false;
    public $pos_show_invoice_scheme = true;
    public $pos_show_invoice_layout = true;
    public $pos_print_invoice_on_suspend = false;

    // Purchase Settings
    public $purchase_prefix = 'PO';
    public $purchase_number_length = 6;
    public $require_supplier_for_purchase = true;

    // Payment Settings
    public $enabled_payment_methods = [];
    public $default_payment_method = '';
    public $allow_partial_payment = true;
    public $availablePaymentMethods = [];

    // Payment Method Management
    public $showPaymentMethodModal = false;
    public $editingPaymentMethodId = null;
    public $paymentMethodName = '';
    public $paymentMethodDescription = '';
    public $paymentMethodStatus = 'active';

    // Dashboard Settings
    public $dashboard_start_date = 'today';
    public $show_dashboard_sales = true;
    public $show_dashboard_purchases = true;
    public $show_dashboard_stock = true;

    // System Settings
    public $date_format = 'Y-m-d';
    public $time_format = 'H:i';
    public $timezone = 'Africa/Dar_es_Salaam';
    public $language = 'en';

    // Prefixes
    public $invoice_prefix = 'INV';
    public $receipt_prefix = 'RCP';
    public $quotation_prefix = 'QT';
    public $stocktaking_prefix = 'ST';
    public $expense_prefix = 'EXP';

    // Email Settings
    public $email_enabled = false;
    public $email_from_name = '';
    public $email_from_address = '';
    public $email_sale_notification = false;
    public $email_payment_notification = false;

    // SMS Settings
    public $sms_enabled = false;
    public $sms_provider = '';
    public $sms_api_key = '';
    public $sms_sender_id = '';
    public $sms_sale_notification = false;
    public $sms_payment_notification = false;

    // Reward Point Settings
    public $reward_enabled = false;
    public $reward_points_per_amount = 1;
    public $reward_amount_per_point = 1;
    public $reward_min_redeem_points = 100;

    // Modules
    public $module_sales = true;
    public $module_purchases = true;
    public $module_stock = true;
    public $module_customers = true;
    public $module_suppliers = true;
    public $module_staffs = true;
    public $module_reports = true;
    public $module_expenses = false;

    // Custom Labels
    public $custom_labels = [];

    // Receipt Settings
    public $receipt_header = '';
    public $receipt_footer = '';
    public $show_logo_on_receipt = true;
    public $show_tax_on_receipt = true;

    // Settings tabs
    public $tabs = [
        'business' => ['label' => 'Business', 'icon' => 'ti-building-store'],
        'tax' => ['label' => 'Tax', 'icon' => 'ti-receipt-tax'],
        'product' => ['label' => 'Product', 'icon' => 'ti-package'],
        'contact' => ['label' => 'Contact', 'icon' => 'ti-address-book'],
        'sale' => ['label' => 'Sale', 'icon' => 'ti-shopping-cart'],
        'pos' => ['label' => 'POS', 'icon' => 'ti-device-desktop'],
        'purchases' => ['label' => 'Purchases', 'icon' => 'ti-truck'],
        'payment' => ['label' => 'Payment', 'icon' => 'ti-credit-card'],
        'dashboard' => ['label' => 'Dashboard', 'icon' => 'ti-dashboard'],
        'system' => ['label' => 'System', 'icon' => 'ti-settings'],
        'prefixes' => ['label' => 'Prefixes', 'icon' => 'ti-hash'],
        'email' => ['label' => 'Email Settings', 'icon' => 'ti-mail'],
        'sms' => ['label' => 'SMS Settings', 'icon' => 'ti-message'],
        'reward' => ['label' => 'Reward Points', 'icon' => 'ti-gift'],
        'modules' => ['label' => 'Modules', 'icon' => 'ti-puzzle'],
        'labels' => ['label' => 'Custom Labels', 'icon' => 'ti-tag'],
    ];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $firstCarwash = $this->ownerCarwashes->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
            $this->loadSettings();
        }

        $this->pos_keyboard_shortcuts = CarwashSetting::getDefaultKeyboardShortcuts();
    }

    public function updatedSelectedCarwash()
    {
        $this->loadSettings();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function loadSettings()
    {
        if (!$this->selectedCarwash) return;

        $settings = CarwashSetting::getForCarwash($this->selectedCarwash);

        // Load payment methods
        $this->loadPaymentMethods();

        // Business Settings
        $this->business_name = $settings->business_name ?? '';
        $this->business_phone = $settings->business_phone ?? '';
        $this->business_email = $settings->business_email ?? '';
        $this->business_address = $settings->business_address ?? '';
        $this->currency = $settings->currency ?? 'TZS';
        $this->currency_symbol = $settings->currency_symbol ?? 'TZS';
        $this->currency_position = $settings->currency_position ?? 'before';
        $this->decimal_places = $settings->decimal_places ?? 2;
        $this->thousand_separator = $settings->thousand_separator ?? ',';
        $this->decimal_separator = $settings->decimal_separator ?? '.';

        // Tax Settings
        $this->tax_enabled = $settings->tax_enabled ?? false;
        $this->tax_name = $settings->tax_name ?? 'VAT';
        $this->tax_rate = $settings->tax_rate ?? 0;
        $this->tax_inclusive = $settings->tax_inclusive ?? false;
        $this->tax_number = $settings->tax_number ?? '';

        // Product Settings
        $this->show_product_image = $settings->show_product_image ?? true;
        $this->show_product_description = $settings->show_product_description ?? true;
        $this->enable_stock_management = $settings->enable_stock_management ?? true;
        $this->allow_negative_stock = $settings->allow_negative_stock ?? false;
        $this->low_stock_threshold = $settings->low_stock_threshold ?? 10;

        // Contact Settings
        $this->contact_phone = $settings->contact_phone ?? '';
        $this->contact_email = $settings->contact_email ?? '';
        $this->contact_whatsapp = $settings->contact_whatsapp ?? '';
        $this->contact_address = $settings->contact_address ?? '';

        // Sale Settings
        $this->sale_prefix = $settings->sale_prefix ?? 'SL';
        $this->sale_number_length = $settings->sale_number_length ?? 6;
        $this->auto_generate_sale_number = $settings->auto_generate_sale_number ?? true;
        $this->require_customer_for_sale = $settings->require_customer_for_sale ?? false;
        $this->allow_discount = $settings->allow_discount ?? true;
        $this->max_discount_percent = $settings->max_discount_percent ?? 100;

        // POS Settings
        $this->pos_keyboard_shortcuts = $settings->pos_keyboard_shortcuts ?? CarwashSetting::getDefaultKeyboardShortcuts();
        $this->pos_disable_multiple_pay = $settings->pos_disable_multiple_pay ?? false;
        $this->pos_disable_draft = $settings->pos_disable_draft ?? false;
        $this->pos_disable_express_checkout = $settings->pos_disable_express_checkout ?? false;
        $this->pos_show_product_suggestion = $settings->pos_show_product_suggestion ?? true;
        $this->pos_show_recent_transactions = $settings->pos_show_recent_transactions ?? true;
        $this->pos_enable_discount = $settings->pos_enable_discount ?? true;
        $this->pos_enable_order_tax = $settings->pos_enable_order_tax ?? true;
        $this->pos_subtotal_editable = $settings->pos_subtotal_editable ?? false;
        $this->pos_disable_suspend_sale = $settings->pos_disable_suspend_sale ?? false;
        $this->pos_show_transaction_date = $settings->pos_show_transaction_date ?? true;
        $this->pos_enable_service_staff = $settings->pos_enable_service_staff ?? false;
        $this->pos_require_service_staff = $settings->pos_require_service_staff ?? true;
        $this->pos_disable_credit_sale = $settings->pos_disable_credit_sale ?? false;
        $this->pos_enable_weighing_scale = $settings->pos_enable_weighing_scale ?? false;
        $this->pos_show_invoice_scheme = $settings->pos_show_invoice_scheme ?? true;
        $this->pos_show_invoice_layout = $settings->pos_show_invoice_layout ?? true;
        $this->pos_print_invoice_on_suspend = $settings->pos_print_invoice_on_suspend ?? false;

        // Purchase Settings
        $this->purchase_prefix = $settings->purchase_prefix ?? 'PO';
        $this->purchase_number_length = $settings->purchase_number_length ?? 6;
        $this->require_supplier_for_purchase = $settings->require_supplier_for_purchase ?? true;

        // Payment Settings
        $this->enabled_payment_methods = $settings->enabled_payment_methods ?? [];
        $this->default_payment_method = $settings->default_payment_method ?? '';
        $this->allow_partial_payment = $settings->allow_partial_payment ?? true;

        // Dashboard Settings
        $this->dashboard_start_date = $settings->dashboard_start_date ?? 'today';
        $this->show_dashboard_sales = $settings->show_dashboard_sales ?? true;
        $this->show_dashboard_purchases = $settings->show_dashboard_purchases ?? true;
        $this->show_dashboard_stock = $settings->show_dashboard_stock ?? true;

        // System Settings
        $this->date_format = $settings->date_format ?? 'Y-m-d';
        $this->time_format = $settings->time_format ?? 'H:i';
        $this->timezone = $settings->timezone ?? 'Africa/Dar_es_Salaam';
        $this->language = $settings->language ?? 'en';

        // Prefixes
        $this->invoice_prefix = $settings->invoice_prefix ?? 'INV';
        $this->receipt_prefix = $settings->receipt_prefix ?? 'RCP';
        $this->quotation_prefix = $settings->quotation_prefix ?? 'QT';
        $this->stocktaking_prefix = $settings->stocktaking_prefix ?? 'ST';
        $this->expense_prefix = $settings->expense_prefix ?? 'EXP';

        // Email Settings
        $this->email_enabled = $settings->email_enabled ?? false;
        $this->email_from_name = $settings->email_from_name ?? '';
        $this->email_from_address = $settings->email_from_address ?? '';
        $this->email_sale_notification = $settings->email_sale_notification ?? false;
        $this->email_payment_notification = $settings->email_payment_notification ?? false;

        // SMS Settings
        $this->sms_enabled = $settings->sms_enabled ?? false;
        $this->sms_provider = $settings->sms_provider ?? '';
        $this->sms_api_key = $settings->sms_api_key ?? '';
        $this->sms_sender_id = $settings->sms_sender_id ?? '';
        $this->sms_sale_notification = $settings->sms_sale_notification ?? false;
        $this->sms_payment_notification = $settings->sms_payment_notification ?? false;

        // Reward Point Settings
        $this->reward_enabled = $settings->reward_enabled ?? false;
        $this->reward_points_per_amount = $settings->reward_points_per_amount ?? 1;
        $this->reward_amount_per_point = $settings->reward_amount_per_point ?? 1;
        $this->reward_min_redeem_points = $settings->reward_min_redeem_points ?? 100;

        // Modules
        $this->module_sales = $settings->module_sales ?? true;
        $this->module_purchases = $settings->module_purchases ?? true;
        $this->module_stock = $settings->module_stock ?? true;
        $this->module_customers = $settings->module_customers ?? true;
        $this->module_suppliers = $settings->module_suppliers ?? true;
        $this->module_staffs = $settings->module_staffs ?? true;
        $this->module_reports = $settings->module_reports ?? true;
        $this->module_expenses = $settings->module_expenses ?? false;

        // Custom Labels
        $this->custom_labels = $settings->custom_labels ?? [];

        // Receipt Settings
        $this->receipt_header = $settings->receipt_header ?? '';
        $this->receipt_footer = $settings->receipt_footer ?? '';
        $this->show_logo_on_receipt = $settings->show_logo_on_receipt ?? true;
        $this->show_tax_on_receipt = $settings->show_tax_on_receipt ?? true;
    }

    public function saveSettings()
    {
        if (!$this->selectedCarwash) {
            session()->flash('error', 'Please select a carwash first.');
            return;
        }

        try {
            $settings = CarwashSetting::getForCarwash($this->selectedCarwash);

            // Handle logo upload
            $logoPath = $settings->business_logo;
            if ($this->business_logo && is_object($this->business_logo)) {
                $logoPath = $this->business_logo->store('settings/logos', 'public');
            }

            $settings->update([
                // Business Settings
                'business_name' => $this->business_name,
                'business_logo' => $logoPath,
                'business_phone' => $this->business_phone,
                'business_email' => $this->business_email,
                'business_address' => $this->business_address,
                'currency' => $this->currency,
                'currency_symbol' => $this->currency_symbol,
                'currency_position' => $this->currency_position,
                'decimal_places' => $this->decimal_places,
                'thousand_separator' => $this->thousand_separator,
                'decimal_separator' => $this->decimal_separator,
                // Tax Settings
                'tax_enabled' => $this->tax_enabled,
                'tax_name' => $this->tax_name,
                'tax_rate' => $this->tax_rate,
                'tax_inclusive' => $this->tax_inclusive,
                'tax_number' => $this->tax_number,
                // Product Settings
                'show_product_image' => $this->show_product_image,
                'show_product_description' => $this->show_product_description,
                'enable_stock_management' => $this->enable_stock_management,
                'allow_negative_stock' => $this->allow_negative_stock,
                'low_stock_threshold' => $this->low_stock_threshold,
                // Contact Settings
                'contact_phone' => $this->contact_phone,
                'contact_email' => $this->contact_email,
                'contact_whatsapp' => $this->contact_whatsapp,
                'contact_address' => $this->contact_address,
                // Sale Settings
                'sale_prefix' => $this->sale_prefix,
                'sale_number_length' => $this->sale_number_length,
                'auto_generate_sale_number' => $this->auto_generate_sale_number,
                'require_customer_for_sale' => $this->require_customer_for_sale,
                'allow_discount' => $this->allow_discount,
                'max_discount_percent' => $this->max_discount_percent,
                // POS Settings
                'pos_keyboard_shortcuts' => $this->pos_keyboard_shortcuts,
                'pos_disable_multiple_pay' => $this->pos_disable_multiple_pay,
                'pos_disable_draft' => $this->pos_disable_draft,
                'pos_disable_express_checkout' => $this->pos_disable_express_checkout,
                'pos_show_product_suggestion' => $this->pos_show_product_suggestion,
                'pos_show_recent_transactions' => $this->pos_show_recent_transactions,
                'pos_enable_discount' => $this->pos_enable_discount,
                'pos_enable_order_tax' => $this->pos_enable_order_tax,
                'pos_subtotal_editable' => $this->pos_subtotal_editable,
                'pos_disable_suspend_sale' => $this->pos_disable_suspend_sale,
                'pos_show_transaction_date' => $this->pos_show_transaction_date,
                'pos_enable_service_staff' => $this->pos_enable_service_staff,
                'pos_require_service_staff' => $this->pos_require_service_staff,
                'pos_disable_credit_sale' => $this->pos_disable_credit_sale,
                'pos_enable_weighing_scale' => $this->pos_enable_weighing_scale,
                'pos_show_invoice_scheme' => $this->pos_show_invoice_scheme,
                'pos_show_invoice_layout' => $this->pos_show_invoice_layout,
                'pos_print_invoice_on_suspend' => $this->pos_print_invoice_on_suspend,
                // Purchase Settings
                'purchase_prefix' => $this->purchase_prefix,
                'purchase_number_length' => $this->purchase_number_length,
                'require_supplier_for_purchase' => $this->require_supplier_for_purchase,
                // Payment Settings
                'enabled_payment_methods' => $this->enabled_payment_methods,
                'default_payment_method' => $this->default_payment_method,
                'allow_partial_payment' => $this->allow_partial_payment,
                // Dashboard Settings
                'dashboard_start_date' => $this->dashboard_start_date,
                'show_dashboard_sales' => $this->show_dashboard_sales,
                'show_dashboard_purchases' => $this->show_dashboard_purchases,
                'show_dashboard_stock' => $this->show_dashboard_stock,
                // System Settings
                'date_format' => $this->date_format,
                'time_format' => $this->time_format,
                'timezone' => $this->timezone,
                'language' => $this->language,
                // Prefixes
                'invoice_prefix' => $this->invoice_prefix,
                'receipt_prefix' => $this->receipt_prefix,
                'quotation_prefix' => $this->quotation_prefix,
                'stocktaking_prefix' => $this->stocktaking_prefix,
                'expense_prefix' => $this->expense_prefix,
                // Email Settings
                'email_enabled' => $this->email_enabled,
                'email_from_name' => $this->email_from_name,
                'email_from_address' => $this->email_from_address,
                'email_sale_notification' => $this->email_sale_notification,
                'email_payment_notification' => $this->email_payment_notification,
                // SMS Settings
                'sms_enabled' => $this->sms_enabled,
                'sms_provider' => $this->sms_provider,
                'sms_api_key' => $this->sms_api_key,
                'sms_sender_id' => $this->sms_sender_id,
                'sms_sale_notification' => $this->sms_sale_notification,
                'sms_payment_notification' => $this->sms_payment_notification,
                // Reward Point Settings
                'reward_enabled' => $this->reward_enabled,
                'reward_points_per_amount' => $this->reward_points_per_amount,
                'reward_amount_per_point' => $this->reward_amount_per_point,
                'reward_min_redeem_points' => $this->reward_min_redeem_points,
                // Modules
                'module_sales' => $this->module_sales,
                'module_purchases' => $this->module_purchases,
                'module_stock' => $this->module_stock,
                'module_customers' => $this->module_customers,
                'module_suppliers' => $this->module_suppliers,
                'module_staffs' => $this->module_staffs,
                'module_reports' => $this->module_reports,
                'module_expenses' => $this->module_expenses,
                // Custom Labels
                'custom_labels' => $this->custom_labels,
                // Receipt Settings
                'receipt_header' => $this->receipt_header,
                'receipt_footer' => $this->receipt_footer,
                'show_logo_on_receipt' => $this->show_logo_on_receipt,
                'show_tax_on_receipt' => $this->show_tax_on_receipt,
            ]);

            session()->flash('message', 'Settings saved successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving settings: ' . $e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        if (!$this->selectedCarwash) return;

        $defaults = CarwashSetting::getDefaults();

        foreach ($defaults as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        session()->flash('message', 'Settings reset to defaults. Click Save to apply.');
    }

    // Payment Method Management
    public function openPaymentMethodModal()
    {
        $this->resetPaymentMethodForm();
        $this->showPaymentMethodModal = true;
    }

    public function closePaymentMethodModal()
    {
        $this->showPaymentMethodModal = false;
        $this->resetPaymentMethodForm();
    }

    public function resetPaymentMethodForm()
    {
        $this->editingPaymentMethodId = null;
        $this->paymentMethodName = '';
        $this->paymentMethodDescription = '';
        $this->paymentMethodStatus = 'active';
    }

    public function editPaymentMethod($id)
    {
        $method = payment_method::find($id);
        if ($method) {
            $this->editingPaymentMethodId = $id;
            $this->paymentMethodName = $method->name;
            $this->paymentMethodDescription = $method->description ?? '';
            $this->paymentMethodStatus = $method->status;
            $this->showPaymentMethodModal = true;
        }
    }

    public function savePaymentMethod()
    {
        $this->validate([
            'paymentMethodName' => 'required|min:2',
        ]);

        if (!$this->selectedCarwash) {
            session()->flash('error', 'Please select a carwash first.');
            return;
        }

        try {
            if ($this->editingPaymentMethodId) {
                // Update existing
                $method = payment_method::find($this->editingPaymentMethodId);
                if ($method) {
                    $method->update([
                        'name' => $this->paymentMethodName,
                        'description' => $this->paymentMethodDescription ?: null,
                        'status' => $this->paymentMethodStatus,
                    ]);
                    session()->flash('message', 'Payment method updated successfully.');
                }
            } else {
                // Create new
                payment_method::create([
                    'name' => $this->paymentMethodName,
                    'description' => $this->paymentMethodDescription ?: null,
                    'status' => $this->paymentMethodStatus,
                    'carwash_id' => $this->selectedCarwash,
                ]);
                session()->flash('message', 'Payment method added successfully.');
            }

            $this->closePaymentMethodModal();
            $this->loadPaymentMethods();
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving payment method: ' . $e->getMessage());
        }
    }

    public function togglePaymentMethodStatus($id)
    {
        $method = payment_method::find($id);
        if ($method) {
            $method->update([
                'status' => $method->status === 'active' ? 'inactive' : 'active',
            ]);
            $this->loadPaymentMethods();
            session()->flash('message', 'Payment method status updated.');
        }
    }

    public function deletePaymentMethod($id)
    {
        try {
            $method = payment_method::find($id);
            if ($method) {
                $method->delete();
                $this->loadPaymentMethods();
                session()->flash('message', 'Payment method deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Cannot delete payment method. It may be in use.');
        }
    }

    public function loadPaymentMethods()
    {
        if (!$this->selectedCarwash) {
            $this->availablePaymentMethods = [];
            return;
        }

        $this->availablePaymentMethods = payment_method::where('carwash_id', $this->selectedCarwash)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.owner.settings.setup');
    }
}
