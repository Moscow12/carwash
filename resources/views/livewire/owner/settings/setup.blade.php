<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Settings</h4>
            <p class="text-muted mb-0">Configure your carwash settings and preferences</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if(count($ownerCarwashes) > 1)
                <select wire:model.live="selectedCarwash" class="form-select" style="width: 200px;">
                    @foreach($ownerCarwashes as $carwash)
                        <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                    @endforeach
                </select>
            @endif
            <button wire:click="resetToDefaults" class="btn btn-outline-secondary">
                <i class="ti ti-refresh me-1"></i> Reset to Defaults
            </button>
            <button wire:click="saveSettings" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Save Settings
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Sidebar - Tabs -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        @foreach($tabs as $key => $tab)
                            <li class="nav-item" role="presentation">
                                <button
                                    wire:click="setTab('{{ $key }}')"
                                    class="nav-link text-start w-100 {{ $activeTab === $key ? 'active' : '' }}"
                                    type="button"
                                >
                                    <i class="ti {{ $tab['icon'] }} me-2"></i>
                                    {{ $tab['label'] }}
                                    @if($key === 'tax' && $tax_enabled)
                                        <i class="ti ti-info-circle text-info ms-1" title="Tax Enabled"></i>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Business Settings -->
                    @if($activeTab === 'business')
                        <h5 class="card-title mb-4"><i class="ti ti-building-store me-2"></i>Business Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business Name</label>
                                <input type="text" wire:model="business_name" class="form-control" placeholder="Enter business name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Logo</label>
                                <input type="file" wire:model="business_logo" class="form-control" accept="image/*">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Phone</label>
                                <input type="text" wire:model="business_phone" class="form-control" placeholder="+255 xxx xxx xxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Business Email</label>
                                <input type="email" wire:model="business_email" class="form-control" placeholder="business@example.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Business Address</label>
                                <textarea wire:model="business_address" class="form-control" rows="2" placeholder="Enter business address"></textarea>
                            </div>
                            <div class="col-12">
                                <hr class="my-3">
                                <h6 class="mb-3">Currency Settings</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency</label>
                                <select wire:model="currency" class="form-select">
                                    <option value="TZS">TZS - Tanzanian Shilling</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="KES">KES - Kenyan Shilling</option>
                                    <option value="UGX">UGX - Ugandan Shilling</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" wire:model="currency_symbol" class="form-control" placeholder="TZS">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Symbol Position</label>
                                <select wire:model="currency_position" class="form-select">
                                    <option value="before">Before Amount (TZS 100)</option>
                                    <option value="after">After Amount (100 TZS)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Decimal Places</label>
                                <select wire:model="decimal_places" class="form-select">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Thousand Separator</label>
                                <select wire:model="thousand_separator" class="form-select">
                                    <option value=",">, (Comma)</option>
                                    <option value=".">. (Period)</option>
                                    <option value=" ">(Space)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Decimal Separator</label>
                                <select wire:model="decimal_separator" class="form-select">
                                    <option value=".">. (Period)</option>
                                    <option value=",">, (Comma)</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Tax Settings -->
                    @if($activeTab === 'tax')
                        <h5 class="card-title mb-4">
                            <i class="ti ti-receipt-tax me-2"></i>Tax Settings
                            <i class="ti ti-info-circle text-info ms-2" data-bs-toggle="tooltip" title="Configure tax settings for your sales"></i>
                        </h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model.live="tax_enabled" class="form-check-input" id="taxEnabled">
                                    <label class="form-check-label" for="taxEnabled">Enable Tax</label>
                                </div>
                            </div>
                            @if($tax_enabled)
                                <div class="col-md-6">
                                    <label class="form-label">Tax Name</label>
                                    <input type="text" wire:model="tax_name" class="form-control" placeholder="e.g., VAT, GST">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax Rate (%)</label>
                                    <input type="number" wire:model="tax_rate" class="form-control" min="0" max="100" step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax Number / TIN</label>
                                    <input type="text" wire:model="tax_number" class="form-control" placeholder="Enter your tax identification number">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input type="checkbox" wire:model="tax_inclusive" class="form-check-input" id="taxInclusive">
                                        <label class="form-check-label" for="taxInclusive">Prices are tax inclusive</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Product Settings -->
                    @if($activeTab === 'product')
                        <h5 class="card-title mb-4"><i class="ti ti-package me-2"></i>Product Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_product_image" class="form-check-input" id="showProductImage">
                                    <label class="form-check-label" for="showProductImage">Show Product Image</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_product_description" class="form-check-input" id="showProductDesc">
                                    <label class="form-check-label" for="showProductDesc">Show Product Description</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="enable_stock_management" class="form-check-input" id="enableStock">
                                    <label class="form-check-label" for="enableStock">Enable Stock Management</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="allow_negative_stock" class="form-check-input" id="allowNegative">
                                    <label class="form-check-label" for="allowNegative">Allow Negative Stock</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Low Stock Alert Threshold</label>
                                <input type="number" wire:model="low_stock_threshold" class="form-control" min="0">
                            </div>
                        </div>
                    @endif

                    <!-- Contact Settings -->
                    @if($activeTab === 'contact')
                        <h5 class="card-title mb-4"><i class="ti ti-address-book me-2"></i>Contact Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Contact Phone</label>
                                <input type="text" wire:model="contact_phone" class="form-control" placeholder="+255 xxx xxx xxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Email</label>
                                <input type="email" wire:model="contact_email" class="form-control" placeholder="contact@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp Number</label>
                                <input type="text" wire:model="contact_whatsapp" class="form-control" placeholder="+255 xxx xxx xxx">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Contact Address</label>
                                <textarea wire:model="contact_address" class="form-control" rows="3" placeholder="Enter contact address"></textarea>
                            </div>
                        </div>
                    @endif

                    <!-- Sale Settings -->
                    @if($activeTab === 'sale')
                        <h5 class="card-title mb-4"><i class="ti ti-shopping-cart me-2"></i>Sale Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Sale Number Prefix</label>
                                <input type="text" wire:model="sale_prefix" class="form-control" placeholder="SL">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number Length</label>
                                <input type="number" wire:model="sale_number_length" class="form-control" min="4" max="10">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Max Discount %</label>
                                <input type="number" wire:model="max_discount_percent" class="form-control" min="0" max="100">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="auto_generate_sale_number" class="form-check-input" id="autoGenerate">
                                    <label class="form-check-label" for="autoGenerate">Auto Generate Sale Number</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="require_customer_for_sale" class="form-check-input" id="requireCustomer">
                                    <label class="form-check-label" for="requireCustomer">Require Customer for Sale</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="allow_discount" class="form-check-input" id="allowDiscount">
                                    <label class="form-check-label" for="allowDiscount">Allow Discount</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- POS Settings -->
                    @if($activeTab === 'pos')
                        <h5 class="card-title mb-4"><i class="ti ti-device-desktop me-2"></i>POS Settings</h5>

                        <!-- Keyboard Shortcuts -->
                        <div class="mb-4">
                            <h6 class="mb-3">Add keyboard shortcuts:</h6>
                            <p class="text-muted small mb-2">
                                Shortcut should be the names of the keys separated by '+'; Example: <strong>ctrl+shift+b</strong>, <strong>ctrl+h</strong>
                            </p>
                            <p class="text-muted small mb-3">
                                <strong>Available key names are:</strong><br>
                                shift, ctrl, alt, backspace, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end, home, left, up, right, down, ins, del, and plus
                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Operations</th>
                                                <th>Keyboard Shortcut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Express Checkout:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.express_checkout" class="form-control form-control-sm" placeholder="shift+e"></td>
                                            </tr>
                                            <tr>
                                                <td>Pay & Checkout:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.pay_checkout" class="form-control form-control-sm" placeholder="shift+p"></td>
                                            </tr>
                                            <tr>
                                                <td>Draft:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.draft" class="form-control form-control-sm" placeholder="shift+d"></td>
                                            </tr>
                                            <tr>
                                                <td>Cancel:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.cancel" class="form-control form-control-sm" placeholder="shift+c"></td>
                                            </tr>
                                            <tr>
                                                <td>Go to product quantity:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.product_quantity" class="form-control form-control-sm" placeholder="f2"></td>
                                            </tr>
                                            <tr>
                                                <td>Weighing Scale:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.weighing_scale" class="form-control form-control-sm" placeholder=""></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Operations</th>
                                                <th>Keyboard Shortcut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Edit Discount:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.edit_discount" class="form-control form-control-sm" placeholder="shift+i"></td>
                                            </tr>
                                            <tr>
                                                <td>Edit Order Tax:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.edit_order_tax" class="form-control form-control-sm" placeholder="shift+t"></td>
                                            </tr>
                                            <tr>
                                                <td>Add Payment Row:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.add_payment_row" class="form-control form-control-sm" placeholder="shift+r"></td>
                                            </tr>
                                            <tr>
                                                <td>Finalize Payment:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.finalize_payment" class="form-control form-control-sm" placeholder="shift+f"></td>
                                            </tr>
                                            <tr>
                                                <td>Add new product:</td>
                                                <td><input type="text" wire:model="pos_keyboard_shortcuts.add_new_product" class="form-control form-control-sm" placeholder="f4"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- POS Settings Checkboxes -->
                        <h6 class="mb-3">POS settings:</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_disable_multiple_pay" class="form-check-input" id="disableMultiplePay">
                                    <label class="form-check-label" for="disableMultiplePay">Disable Multiple Pay</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_disable_draft" class="form-check-input" id="disableDraft">
                                    <label class="form-check-label" for="disableDraft">Disable Draft</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_disable_express_checkout" class="form-check-input" id="disableExpress">
                                    <label class="form-check-label" for="disableExpress">Disable Express Checkout</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_show_product_suggestion" class="form-check-input" id="showSuggestion">
                                    <label class="form-check-label" for="showSuggestion">Show product suggestion</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_show_recent_transactions" class="form-check-input" id="showRecent">
                                    <label class="form-check-label" for="showRecent">Show recent transactions</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_enable_discount" class="form-check-input" id="enablePosDiscount">
                                    <label class="form-check-label" for="enablePosDiscount">Enable Discount</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_enable_order_tax" class="form-check-input" id="enableOrderTax">
                                    <label class="form-check-label" for="enableOrderTax">Enable order tax</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_subtotal_editable" class="form-check-input" id="subtotalEditable">
                                    <label class="form-check-label" for="subtotalEditable">
                                        Subtotal Editable
                                        <i class="ti ti-info-circle text-info" data-bs-toggle="tooltip" title="Allow editing the subtotal directly"></i>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_disable_suspend_sale" class="form-check-input" id="disableSuspend">
                                    <label class="form-check-label" for="disableSuspend">Disable Suspend Sale</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_show_transaction_date" class="form-check-input" id="showTransDate">
                                    <label class="form-check-label" for="showTransDate">Enable transaction date on POS screen</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_enable_service_staff" class="form-check-input" id="enableServiceStaff">
                                    <label class="form-check-label" for="enableServiceStaff">
                                        Enable service staff in product line
                                        <i class="ti ti-info-circle text-info" data-bs-toggle="tooltip" title="Show staff assignment per item"></i>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_require_service_staff" class="form-check-input" id="requireServiceStaff">
                                    <label class="form-check-label" for="requireServiceStaff">Is service staff required</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_disable_credit_sale" class="form-check-input" id="disableCreditSale">
                                    <label class="form-check-label text-danger" for="disableCreditSale">
                                        Disable credit sale button
                                        <i class="ti ti-info-circle text-info" data-bs-toggle="tooltip" title="Hide the credit sale option"></i>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_enable_weighing_scale" class="form-check-input" id="enableWeighing">
                                    <label class="form-check-label" for="enableWeighing">Enable Weighing Scale</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_show_invoice_scheme" class="form-check-input" id="showInvoiceScheme">
                                    <label class="form-check-label" for="showInvoiceScheme">Show invoice scheme</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_show_invoice_layout" class="form-check-input" id="showInvoiceLayout">
                                    <label class="form-check-label" for="showInvoiceLayout">Show invoice layout dropdown</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="pos_print_invoice_on_suspend" class="form-check-input" id="printOnSuspend">
                                    <label class="form-check-label" for="printOnSuspend">Print invoice on suspend</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Purchase Settings -->
                    @if($activeTab === 'purchases')
                        <h5 class="card-title mb-4"><i class="ti ti-truck me-2"></i>Purchase Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Purchase Number Prefix</label>
                                <input type="text" wire:model="purchase_prefix" class="form-control" placeholder="PO">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Number Length</label>
                                <input type="number" wire:model="purchase_number_length" class="form-control" min="4" max="10">
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-4">
                                    <input type="checkbox" wire:model="require_supplier_for_purchase" class="form-check-input" id="requireSupplier">
                                    <label class="form-check-label" for="requireSupplier">Require Supplier for Purchase</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Payment Settings -->
                    @if($activeTab === 'payment')
                        <h5 class="card-title mb-4"><i class="ti ti-credit-card me-2"></i>Payment Settings</h5>

                        <!-- Payment Methods Management -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Payment Methods</h6>
                                <button wire:click="openPaymentMethodModal" class="btn btn-primary btn-sm">
                                    <i class="ti ti-plus me-1"></i> Add Payment Method
                                </button>
                            </div>

                            @if(count($availablePaymentMethods) > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th class="text-center" style="width: 100px;">Status</th>
                                                <th class="text-center" style="width: 150px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($availablePaymentMethods as $method)
                                                <tr>
                                                    <td>
                                                        <i class="ti ti-credit-card me-2 text-primary"></i>
                                                        {{ $method['name'] }}
                                                    </td>
                                                    <td class="text-muted">{{ $method['description'] ?? '-' }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-{{ $method['status'] === 'active' ? 'success' : 'secondary' }}">
                                                            {{ ucfirst($method['status']) }}
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <button wire:click="editPaymentMethod('{{ $method['id'] }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <button wire:click="togglePaymentMethodStatus('{{ $method['id'] }}')" class="btn btn-sm btn-outline-{{ $method['status'] === 'active' ? 'warning' : 'success' }}" title="{{ $method['status'] === 'active' ? 'Disable' : 'Enable' }}">
                                                            <i class="ti ti-{{ $method['status'] === 'active' ? 'ban' : 'check' }}"></i>
                                                        </button>
                                                        <button wire:click="deletePaymentMethod('{{ $method['id'] }}')" wire:confirm="Are you sure you want to delete this payment method?" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>No payment methods configured. Click "Add Payment Method" to create one.
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        <!-- Payment Preferences -->
                        <h6 class="mb-3">Payment Preferences</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Default Payment Method</label>
                                <select wire:model="default_payment_method" class="form-select">
                                    <option value="">Select default method</option>
                                    @foreach($availablePaymentMethods as $method)
                                        @if($method['status'] === 'active')
                                            <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-4">
                                    <input type="checkbox" wire:model="allow_partial_payment" class="form-check-input" id="allowPartial">
                                    <label class="form-check-label" for="allowPartial">Allow Partial Payment</label>
                                </div>
                            </div>
                            @if(count($availablePaymentMethods) > 0)
                                <div class="col-12">
                                    <label class="form-label">Enabled Payment Methods for POS</label>
                                    <div class="row">
                                        @foreach($availablePaymentMethods as $method)
                                            @if($method['status'] === 'active')
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input type="checkbox" wire:model="enabled_payment_methods" value="{{ $method['id'] }}" class="form-check-input" id="method_{{ $method['id'] }}">
                                                        <label class="form-check-label" for="method_{{ $method['id'] }}">{{ $method['name'] }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Dashboard Settings -->
                    @if($activeTab === 'dashboard')
                        <h5 class="card-title mb-4"><i class="ti ti-dashboard me-2"></i>Dashboard Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Dashboard Start Date</label>
                                <select wire:model="dashboard_start_date" class="form-select">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="year">This Year</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Show on Dashboard</label>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_dashboard_sales" class="form-check-input" id="showDashSales">
                                    <label class="form-check-label" for="showDashSales">Sales Summary</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_dashboard_purchases" class="form-check-input" id="showDashPurchases">
                                    <label class="form-check-label" for="showDashPurchases">Purchases Summary</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_dashboard_stock" class="form-check-input" id="showDashStock">
                                    <label class="form-check-label" for="showDashStock">Stock Summary</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- System Settings -->
                    @if($activeTab === 'system')
                        <h5 class="card-title mb-4"><i class="ti ti-settings me-2"></i>System Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date Format</label>
                                <select wire:model="date_format" class="form-select">
                                    <option value="Y-m-d">2024-12-29 (Y-m-d)</option>
                                    <option value="d-m-Y">29-12-2024 (d-m-Y)</option>
                                    <option value="m/d/Y">12/29/2024 (m/d/Y)</option>
                                    <option value="d/m/Y">29/12/2024 (d/m/Y)</option>
                                    <option value="M d, Y">Dec 29, 2024</option>
                                    <option value="d M Y">29 Dec 2024</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time Format</label>
                                <select wire:model="time_format" class="form-select">
                                    <option value="H:i">24-hour (14:30)</option>
                                    <option value="h:i A">12-hour (02:30 PM)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Timezone</label>
                                <select wire:model="timezone" class="form-select">
                                    <option value="Africa/Dar_es_Salaam">Africa/Dar_es_Salaam (EAT)</option>
                                    <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                    <option value="Africa/Kampala">Africa/Kampala (EAT)</option>
                                    <option value="UTC">UTC</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select wire:model="language" class="form-select">
                                    <option value="en">English</option>
                                    <option value="sw">Swahili</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Prefixes -->
                    @if($activeTab === 'prefixes')
                        <h5 class="card-title mb-4"><i class="ti ti-hash me-2"></i>Document Prefixes</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Invoice Prefix</label>
                                <input type="text" wire:model="invoice_prefix" class="form-control" placeholder="INV">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Receipt Prefix</label>
                                <input type="text" wire:model="receipt_prefix" class="form-control" placeholder="RCP">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quotation Prefix</label>
                                <input type="text" wire:model="quotation_prefix" class="form-control" placeholder="QT">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stocktaking Prefix</label>
                                <input type="text" wire:model="stocktaking_prefix" class="form-control" placeholder="ST">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expense Prefix</label>
                                <input type="text" wire:model="expense_prefix" class="form-control" placeholder="EXP">
                            </div>
                        </div>
                    @endif

                    <!-- Email Settings -->
                    @if($activeTab === 'email')
                        <h5 class="card-title mb-4"><i class="ti ti-mail me-2"></i>Email Settings</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model.live="email_enabled" class="form-check-input" id="emailEnabled">
                                    <label class="form-check-label" for="emailEnabled">Enable Email Notifications</label>
                                </div>
                            </div>
                            @if($email_enabled)
                                <div class="col-md-6">
                                    <label class="form-label">From Name</label>
                                    <input type="text" wire:model="email_from_name" class="form-control" placeholder="Your Business Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">From Email</label>
                                    <input type="email" wire:model="email_from_address" class="form-control" placeholder="noreply@example.com">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="email_sale_notification" class="form-check-input" id="emailSale">
                                        <label class="form-check-label" for="emailSale">Send Sale Notification</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="email_payment_notification" class="form-check-input" id="emailPayment">
                                        <label class="form-check-label" for="emailPayment">Send Payment Notification</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- SMS Settings -->
                    @if($activeTab === 'sms')
                        <h5 class="card-title mb-4"><i class="ti ti-message me-2"></i>SMS Settings</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model.live="sms_enabled" class="form-check-input" id="smsEnabled">
                                    <label class="form-check-label" for="smsEnabled">Enable SMS Notifications</label>
                                </div>
                            </div>
                            @if($sms_enabled)
                                <div class="col-md-6">
                                    <label class="form-label">SMS Provider</label>
                                    <select wire:model="sms_provider" class="form-select">
                                        <option value="">Select Provider</option>
                                        <option value="nexmo">Nexmo</option>
                                        <option value="twilio">Twilio</option>
                                        <option value="africastalking">Africa's Talking</option>
                                        <option value="beem">Beem Africa</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sender ID</label>
                                    <input type="text" wire:model="sms_sender_id" class="form-control" placeholder="Your Business">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">API Key</label>
                                    <input type="password" wire:model="sms_api_key" class="form-control" placeholder="Enter API key">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="sms_sale_notification" class="form-check-input" id="smsSale">
                                        <label class="form-check-label" for="smsSale">Send Sale SMS</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" wire:model="sms_payment_notification" class="form-check-input" id="smsPayment">
                                        <label class="form-check-label" for="smsPayment">Send Payment SMS</label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Reward Point Settings -->
                    @if($activeTab === 'reward')
                        <h5 class="card-title mb-4"><i class="ti ti-gift me-2"></i>Reward Point Settings</h5>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model.live="reward_enabled" class="form-check-input" id="rewardEnabled">
                                    <label class="form-check-label" for="rewardEnabled">Enable Reward Points</label>
                                </div>
                            </div>
                            @if($reward_enabled)
                                <div class="col-md-4">
                                    <label class="form-label">Points per Amount Spent</label>
                                    <div class="input-group">
                                        <input type="number" wire:model="reward_points_per_amount" class="form-control" min="0" step="0.01">
                                        <span class="input-group-text">pts per 1 {{ $currency_symbol }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Amount per Point Redeemed</label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $currency_symbol }}</span>
                                        <input type="number" wire:model="reward_amount_per_point" class="form-control" min="0" step="0.01">
                                        <span class="input-group-text">per pt</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Minimum Points to Redeem</label>
                                    <input type="number" wire:model="reward_min_redeem_points" class="form-control" min="0">
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Modules -->
                    @if($activeTab === 'modules')
                        <h5 class="card-title mb-4"><i class="ti ti-puzzle me-2"></i>Modules</h5>
                        <p class="text-muted mb-4">Enable or disable modules for your carwash</p>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_sales" class="form-check-input" id="modSales">
                                    <label class="form-check-label" for="modSales">Sales Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_purchases" class="form-check-input" id="modPurchases">
                                    <label class="form-check-label" for="modPurchases">Purchases Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_stock" class="form-check-input" id="modStock">
                                    <label class="form-check-label" for="modStock">Stock Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_customers" class="form-check-input" id="modCustomers">
                                    <label class="form-check-label" for="modCustomers">Customers Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_suppliers" class="form-check-input" id="modSuppliers">
                                    <label class="form-check-label" for="modSuppliers">Suppliers Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_staffs" class="form-check-input" id="modStaffs">
                                    <label class="form-check-label" for="modStaffs">Staff Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_reports" class="form-check-input" id="modReports">
                                    <label class="form-check-label" for="modReports">Reports Module</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="module_expenses" class="form-check-input" id="modExpenses">
                                    <label class="form-check-label" for="modExpenses">Expenses Module</label>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Custom Labels -->
                    @if($activeTab === 'labels')
                        <h5 class="card-title mb-4"><i class="ti ti-tag me-2"></i>Custom Labels</h5>
                        <p class="text-muted mb-4">Customize labels used throughout the application</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Receipt Header</label>
                                <textarea wire:model="receipt_header" class="form-control" rows="3" placeholder="Enter text to appear at the top of receipts"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Receipt Footer</label>
                                <textarea wire:model="receipt_footer" class="form-control" rows="3" placeholder="Enter text to appear at the bottom of receipts (e.g., Thank you!)"></textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_logo_on_receipt" class="form-check-input" id="showLogoReceipt">
                                    <label class="form-check-label" for="showLogoReceipt">Show Logo on Receipt</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="checkbox" wire:model="show_tax_on_receipt" class="form-check-input" id="showTaxReceipt">
                                    <label class="form-check-label" for="showTaxReceipt">Show Tax Details on Receipt</label>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Card Footer -->
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">
                            <i class="ti ti-info-circle me-1"></i>
                            Changes will be applied after saving
                        </span>
                        <button wire:click="saveSettings" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Save Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link {
            border-radius: 0;
            padding: 0.75rem 1rem;
            color: #495057;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .nav-pills .nav-link i {
            width: 20px;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .form-switch .form-check-input {
            width: 3em;
        }
    </style>

    <!-- Payment Method Modal -->
    @if($showPaymentMethodModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-credit-card me-2"></i>
                            {{ $editingPaymentMethodId ? 'Edit Payment Method' : 'Add Payment Method' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closePaymentMethodModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="paymentMethodName" class="form-control @error('paymentMethodName') is-invalid @enderror" placeholder="e.g., Cash, M-Pesa, Bank Transfer">
                            @error('paymentMethodName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="paymentMethodDescription" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="paymentMethodStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePaymentMethodModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="savePaymentMethod">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingPaymentMethodId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
