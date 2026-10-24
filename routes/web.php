<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Site Pages
use App\Livewire\Sitepg\Home;
use App\Livewire\Sitepg\About;
use App\Livewire\Sitepg\Services;
use App\Livewire\Sitepg\Contact;
use App\Livewire\Sitepg\Login;
use App\Livewire\Sitepg\Register;
use App\Livewire\Sitepg\ForgotPassword;
use App\Livewire\Sitepg\ResetPassword;
use App\Livewire\Sitepg\Marketplace;
use App\Livewire\Sitepg\ShopDetails;

// Dashboard Pages (Admin)
use App\Livewire\Dashboard\Auth\Login as AdminLogin;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Dashboard\Users\Index as UsersIndex;
use App\Livewire\Dashboard\Locations\CCountries;
use App\Livewire\Dashboard\Locations\CRegions;
use App\Livewire\Dashboard\Locations\CDistricts;
use App\Livewire\Dashboard\Locations\CWards;
use App\Livewire\Dashboard\Locations\Streets;
use App\Livewire\Dashboard\Settings\Index as SettingsIndex;
use App\Livewire\Dashboard\Businesses\Index as AdminBusinesses;
use App\Livewire\Dashboard\SubscriptionPlans\Index as AdminSubscriptionPlans;
use App\Livewire\Dashboard\BusinessSubscriptions\Index as AdminBusinessSubscriptions;
use App\Livewire\Dashboard\SubscriptionPayments\Index as AdminSubscriptionPayments;
use App\Livewire\Owner\Subscription\Index as OwnerSubscription;
use App\Http\Controllers\Subscriptions\CallbackController as SubscriptionsCallbackController;
use App\Http\Controllers\Subscriptions\IpnController as SubscriptionsIpnController;

// Owner Pages
use App\Livewire\Owner\Dashboard as OwnerDashboard;
use App\Livewire\Owner\Businesses\Index as OwnerBusinesses;
use App\Livewire\Owner\Items\Index as OwnerItems;
use App\Livewire\Owner\Staffs\Index as OwnerStaffs;
use App\Livewire\Owner\Customers\Index as OwnerCustomers;
use App\Livewire\Owner\Sales\Index as OwnerSales;
use App\Http\Controllers\Owner\SalesPrintController;
use App\Livewire\Owner\Sales\Dashboard as OwnerSalesDashboard;
use App\Livewire\Owner\Purchases\Index as OwnerPurchases;
use App\Livewire\Owner\Stocktaking\Index as OwnerStocktaking;
use App\Livewire\Owner\Stocktaking\Sheet as OwnerStocktakingSheet;
use App\Livewire\Owner\Suppliers\Index as OwnerSuppliers;
use App\Livewire\Owner\Businesses\Mybusiness;
use App\Livewire\Owner\Items\Categories;
use App\Livewire\Owner\Items\Itemregister;
use App\Livewire\Owner\Items\Uploaditems;

// Customer Pages
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\Businesses\Browse as BrowseBusinesses;
use App\Livewire\Customer\Businesses\Details as BusinessDetails;
use App\Livewire\Customer\Bookings\Index as MyBookings;
use App\Livewire\Customer\Profile\Index as CustomerProfile;

// Site Pages
Route::get('/', Home::class)->name('site.home');
Route::get('/about', About::class)->name('site.about');
Route::get('/services', Services::class)->name('site.services');
Route::get('/contact', Contact::class)->name('site.contact');
Route::get('/login', Login::class)->name('site.login');
Route::get('/register', Register::class)->name('site.register');

// Public marketplace (no auth)
Route::get('/marketplace', Marketplace::class)->name('site.marketplace');
Route::get('/shop/{business}', ShopDetails::class)->name('site.shop');

// Password reset (guest only)
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

// Admin Authentication
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Owner\Expenses\Category;
use App\Livewire\Owner\Expenses\Expenses;
use App\Livewire\Owner\Hotel\Addhotel;
use App\Livewire\Owner\Hotel\Frontdesk;
use App\Livewire\Owner\Hotel\Reservations;
use App\Livewire\Owner\Hotel\Checkin;
use App\Livewire\Owner\Hotel\Checkout;
use App\Livewire\Owner\Hotel\RoomTypes;
use App\Livewire\Owner\Hotel\Rooms;
use App\Livewire\Owner\Hotel\RatePlans;
use App\Livewire\Owner\Hotel\Guests;
use App\Livewire\Owner\Hotel\HousekeepingTasks;
use App\Livewire\Owner\Hotel\BookingSources;
use App\Livewire\Owner\Hotel\MaintenanceManagement;
use App\Livewire\Owner\Hotel\BillingFinance;
use App\Livewire\Owner\Hotel\NightAudit;
use App\Livewire\Owner\Hotel\FoodBeverage;
use App\Livewire\Owner\Hotel\HotelReports;
use App\Livewire\Owner\Hotel\RoomStatus;
use App\Livewire\Owner\Hotel\RoomAvailability;
use App\Livewire\Owner\Hotel\GuestDocuments;
use App\Livewire\Owner\Hotel\CommunicationLog;
use App\Livewire\Owner\Hotel\HousekeepingStatus;
use App\Livewire\Owner\Hotel\LostAndFound;
use App\Livewire\Owner\Hotel\ChannelMapping;
use App\Livewire\Owner\Items\Edititems;
use App\Livewire\Owner\Items\History;
use App\Livewire\Owner\Items\Listitems;
use App\Livewire\Owner\Items\Units;
use App\Livewire\Owner\Reports\Profitandloss;
use App\Livewire\Owner\Reports\Sales;
use App\Livewire\Owner\Reports\Stock;
use App\Livewire\Owner\Reports\Staffcommisions;
use App\Livewire\Owner\Sales\Posscreen;
use App\Livewire\Owner\Settings\Hotelsettings;
use App\Livewire\Owner\Settings\Setup as OwnerSettings;
use App\Livewire\Owner\Staffs\Userroles;

Route::get('/admin/login', AdminLogin::class)->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');

Route::match(['get', 'post'], '/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('logout');

Route::match(['get', 'post'], '/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout');

// Admin Dashboard (Protected Routes - Admin Only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', DashboardIndex::class)->name('admin.dashboard');

    // User Management
    Route::get('/users', UsersIndex::class)->name('admin.users');

    // Business Management
    Route::get('/businesses', AdminBusinesses::class)->name('admin.businesses');

    // Location Management
    Route::get('/countries', CCountries::class)->name('admin.countries');
    Route::get('/regions', CRegions::class)->name('admin.regions');
    Route::get('/districts', CDistricts::class)->name('admin.districts');
    Route::get('/wards', CWards::class)->name('admin.wards');
    Route::get('/streets', Streets::class)->name('admin.streets');

    // Settings
    Route::get('/settings', SettingsIndex::class)->name('admin.settings');

    // Subscription Plans
    Route::get('/subscription-plans', AdminSubscriptionPlans::class)->name('admin.subscription-plans');
    Route::get('/business-subscriptions', AdminBusinessSubscriptions::class)->name('admin.business-subscriptions');
    Route::get('/subscription-payments', AdminSubscriptionPayments::class)->name('admin.subscription-payments');
});

// Public Pesapal callback & IPN endpoints (no auth — Pesapal calls these directly).
Route::get('/subscriptions/callback', SubscriptionsCallbackController::class)->name('subscriptions.callback');
Route::match(['get', 'post'], '/subscriptions/ipn', SubscriptionsIpnController::class)->name('subscriptions.ipn');

// Owner Dashboard (Protected Routes - Owner and Staff)
Route::middleware(['auth', 'role:owner,staff', 'subscription.active'])->prefix('owner')->group(function () {
    Route::get('/', OwnerDashboard::class)->name('owner.dashboard');
    Route::get('/subscription', OwnerSubscription::class)->can('manage_subscription')->name('owner.subscription');
    Route::get('/sales/dashboard', OwnerSalesDashboard::class)->middleware('module:pos')->can('view_sale')->name('owner.sales.dashboard');
    Route::get('/businesses', OwnerBusinesses::class)->can('manage_business')->name('owner.businesses');
    Route::get('/items', OwnerItems::class)->can('view_item')->name('owner.items');
    Route::get('/items/edit/{itemId}', Edititems::class)->can('edit_item')->name('owner.items.edit');
    Route::get('/staffs', OwnerStaffs::class)->can('view_staff')->name('owner.people.Staffs');
    Route::get('/user-roles', Userroles::class)->can('view_user')->name('owner.people.user-roles');
    Route::get('/roles', \App\Livewire\Owner\Staffs\RolesIndex::class)->can('view_role')->name('owner.people.roles');
    Route::get('/roles/create', \App\Livewire\Owner\Staffs\CreateRole::class)->can('add_role')->name('owner.people.roles.create');
    Route::get('/roles/edit/{roleId}', \App\Livewire\Owner\Staffs\EditRole::class)->can('edit_role')->name('owner.people.roles.edit');
    Route::get('/customers', OwnerCustomers::class)->can('view_customer')->name('owner.people.customers');
    Route::get('/sales', OwnerSales::class)->middleware('module:pos')->can('view_sale')->name('owner.sales');
    Route::get('/sales/print', [SalesPrintController::class, 'list'])->middleware('module:pos')->can('view_sale')->name('owner.sales.print');
    Route::get('/sales/{sale}/invoice', [SalesPrintController::class, 'invoice'])->middleware('module:pos')->can('view_sale')->name('owner.sales.invoice');
    Route::get('/purchases', OwnerPurchases::class)->can('view_purchase')->name('owner.purchases');
    Route::get('/stocktaking', OwnerStocktaking::class)->can('stocktaking')->name('owner.stocktaking');
    Route::get('/stocktaking/count/{stock?}', OwnerStocktakingSheet::class)->can('stocktaking')->name('owner.stocktaking.sheet');
    Route::get('/suppliers', OwnerSuppliers::class)->can('view_suppliers')->name('owner.people.suppliers');
    Route::get('/units', Units::class)->can('manage_units')->name('owner.units');
    Route::get('/my-business', Mybusiness::class)->can('manage_business')->name('owner.mybusiness');
    Route::get('/categories', Categories::class)->can('manage_categories')->name('owner.categories');
    Route::get('/item-register', Itemregister::class)->can('add_item')->name('owner.itemregister');
    Route::get('/upload-items', Uploaditems::class)->can('import_items')->name('owner.uploaditems');
    Route::get('/pos-screen', Posscreen::class)->middleware('module:pos')->can('create_sale')->name('owner.posscreen');
    Route::get('/settings', OwnerSettings::class)->can('view_settings')->name('owner.settings');
    Route::get('/settings/hotel', Hotelsettings::class)->can('view_settings')->name('owner.settings.hotel');

    // Hotel Management Routes
    Route::middleware('module:hotel')->group(function () {
    Route::get('/hotels', Addhotel::class)->can('manage_hotel')->name('owner.hotels');
    Route::get('/hotel/addhotel', Addhotel::class)->can('manage_hotel')->name('owner.hotel.list');

    // Front Desk
    Route::get('/hotel/frontdesk', Frontdesk::class)->can('hotel_front_desk')->name('owner.hotel.frontdesk');
    Route::get('/hotel/reservations', Reservations::class)->can('view_reservations')->name('owner.hotel.reservations');
    Route::get('/hotel/checkin', Checkin::class)->can('checkin')->name('owner.hotel.checkin');
    Route::get('/hotel/checkout', Checkout::class)->can('checkout')->name('owner.hotel.checkout');
    Route::get('/hotel/room-status', RoomStatus::class)->can('manage_rooms')->name('owner.hotel.room-status');

    // Rooms & Inventory
    Route::get('/hotel/room-types', RoomTypes::class)->can('manage_rooms')->name('owner.hotel.room-types');
    Route::get('/hotel/rooms', Rooms::class)->can('manage_rooms')->name('owner.hotel.rooms');
    Route::get('/hotel/rate-plans', RatePlans::class)->can('manage_rooms')->name('owner.hotel.rate-plans');
    Route::get('/hotel/availability', RoomAvailability::class)->can('view_reservations')->name('owner.hotel.availability');

    // Guests
    Route::get('/hotel/guests', Guests::class)->can('manage_guests')->name('owner.hotel.guests');
    Route::get('/hotel/guest-documents', GuestDocuments::class)->can('manage_guests')->name('owner.hotel.guest-documents');
    Route::get('/hotel/communication-log', CommunicationLog::class)->can('manage_guests')->name('owner.hotel.communication-log');

    // Housekeeping
    Route::get('/hotel/housekeeping/tasks', HousekeepingTasks::class)->can('housekeeping')->name('owner.hotel.housekeeping.tasks');
    Route::get('/hotel/housekeeping/status', HousekeepingStatus::class)->can('housekeeping')->name('owner.hotel.housekeeping.status');
    Route::get('/hotel/lost-found', LostAndFound::class)->can('housekeeping')->name('owner.hotel.lost-found');

    // Maintenance
    Route::get('/hotel/maintenance/requests', MaintenanceManagement::class)->can('hotel_maintenance')->name('owner.hotel.maintenance.requests');
    Route::get('/hotel/maintenance/preventive', MaintenanceManagement::class)->can('hotel_maintenance')->name('owner.hotel.maintenance.preventive');

    // Billing & Finance
    Route::get('/hotel/folios', BillingFinance::class)->can('hotel_billing')->name('owner.hotel.folios');
    Route::get('/hotel/invoices', BillingFinance::class)->can('hotel_billing')->name('owner.hotel.invoices');
    Route::get('/hotel/payments', BillingFinance::class)->can('hotel_billing')->name('owner.hotel.payments');
    Route::get('/hotel/night-audit', NightAudit::class)->can('hotel_night_audit')->name('owner.hotel.night-audit');
    Route::get('/hotel/tax-config', BillingFinance::class)->can('hotel_billing')->name('owner.hotel.tax-config');

    // F&B Management
    Route::get('/hotel/pos-outlets', FoodBeverage::class)->can('hotel_food_beverage')->name('owner.hotel.pos-outlets');
    Route::get('/hotel/pos-orders', FoodBeverage::class)->can('hotel_food_beverage')->name('owner.hotel.pos-orders');
    Route::get('/hotel/tables', FoodBeverage::class)->can('hotel_food_beverage')->name('owner.hotel.tables');
    Route::get('/hotel/pos-sessions', FoodBeverage::class)->can('hotel_food_beverage')->name('owner.hotel.pos-sessions');

    // Reports
    Route::get('/hotel/reports/occupancy', HotelReports::class)->can('hotel_reports')->name('owner.hotel.reports.occupancy');
    Route::get('/hotel/reports/revenue', HotelReports::class)->can('hotel_reports')->name('owner.hotel.reports.revenue');
    Route::get('/hotel/reports/reservations', HotelReports::class)->can('hotel_reports')->name('owner.hotel.reports.reservations');
    Route::get('/hotel/reports/housekeeping', HotelReports::class)->can('hotel_reports')->name('owner.hotel.reports.housekeeping');
    Route::get('/hotel/reports/guest-history', HotelReports::class)->can('hotel_reports')->name('owner.hotel.reports.guest-history');

    // Channels & Integrations
    Route::get('/hotel/booking-sources', BookingSources::class)->can('hotel_channels')->name('owner.hotel.booking-sources');
    Route::get('/hotel/channel-mapping', ChannelMapping::class)->can('hotel_channels')->name('owner.hotel.channel-mapping');
    });

    Route::get('/expenses/categories', Category::class)->can('manage_expense_categories')->name('owner.expenses.categories');
    Route::get('/expenses', Expenses::class)->can('view_expense')->name('owner.expenses');
    Route::get('/list-items', Listitems::class)->can('view_item')->name('owner.list-items');
    Route::get('/history/{itemId}', History::class)->can('view_item')->name('owner.history');
    Route::get('/reports/profit-and-loss', Profitandloss::class)->can('view_profit_loss_report')->name('owner.reports.profitandloss');
    Route::get('/reports/sales', Sales::class)->can('view_sales_report')->name('owner.reports.sales');
    Route::get('/reports/stock', Stock::class)->can('view_stock_report')->name('owner.reports.stock');
    Route::get('/reports/staff-commissions', Staffcommisions::class)->can('view_staff_commission_report')->name('owner.reports.staffcommissions');

    // Bar Management Routes
    Route::get('/barmanagement', function() {
        return redirect()->route('owner.bar.dashboard');
    })->can('bar_dashboard')->name('owner.barmanagement'); // Backward compatibility redirect

    Route::middleware('module:bar')->group(function () {
        Route::get('/bar/dashboard', \App\Livewire\Owner\Bar\BarDashboard::class)->can('bar_dashboard')->name('owner.bar.dashboard');
        Route::get('/bar/pos', \App\Livewire\Owner\Bar\BarPOS::class)->can('bar_pos')->name('owner.bar.pos');
        Route::get('/bar/menu-items', \App\Livewire\Owner\Bar\BarMenuItems::class)->can('bar_menu')->name('owner.bar.menu');
        Route::get('/bar/tabs', \App\Livewire\Owner\Bar\BarTabs::class)->can('bar_tabs')->name('owner.bar.tabs');
        Route::get('/bar/stock', \App\Livewire\Owner\Bar\BarStock::class)->can('bar_stock')->name('owner.bar.stock');
        Route::get('/bar/reports', \App\Livewire\Owner\Bar\BarReports::class)->can('bar_reports')->name('owner.bar.reports');
    });

    // Restaurant Management
    Route::middleware('module:restaurant')->group(function () {
        Route::get('/restaurant/dashboard', \App\Livewire\Owner\Restaurant\Dashboard::class)->can('restaurant_dashboard')->name('owner.restaurant.dashboard');
        Route::get('/restaurant/reservations', \App\Livewire\Owner\Restaurant\TableReservations::class)->can('manage_table_reservations')->name('owner.restaurant.reservations');
        Route::get('/restaurant/waiters', \App\Livewire\Owner\Restaurant\WaiterManagement::class)->can('manage_waiters')->name('owner.restaurant.waiters');
        Route::get('/restaurant/recipes', \App\Livewire\Owner\Restaurant\MenuRecipes::class)->can('manage_recipes')->name('owner.restaurant.recipes');
        Route::get('/restaurant/restaurant-pos', \App\Livewire\Owner\Restaurant\RestaurantPOS::class)->can('access_pos')->name('owner.restaurant.pos');
        Route::get('/restaurant/kitchenscreen', \App\Livewire\Owner\Restaurant\KitchenScreen::class)->can('kitchen_display')->name('owner.restaurant.kitchenscreen');
        Route::get('restaurant/settings', \App\Livewire\Owner\Restaurant\RestaurantSettings::class)->can('restaurant_settings')->name('owner.restaurant.settings');
    });

    // Hotel Guest Services
    Route::middleware('module:hotel')->group(function () {
        Route::get('/hotel/amenity-requests', \App\Livewire\Owner\Hotel\AmenityRequests::class)->can('hotel_guest_services')->name('owner.hotel.amenity-requests');
        Route::get('/hotel/wakeup-calls', \App\Livewire\Owner\Hotel\WakeupCalls::class)->can('hotel_guest_services')->name('owner.hotel.wakeup-calls');
        Route::get('/hotel/laundry-orders', \App\Livewire\Owner\Hotel\LaundryManagement::class)->can('hotel_guest_services')->name('owner.hotel.laundry-orders');
    });

    // Inventory & Purchase Management
    Route::get('/inventory/purchases', \App\Livewire\Owner\Inventory\PurchaseManagement::class)->can('view_purchase')->name('owner.inventory.purchases');
    Route::get('/inventory/purchases/create', \App\Livewire\Owner\Inventory\CreatePurchase::class)->can('create_purchase')->name('owner.inventory.purchases.create');
    Route::get('/inventory/stocktransfers', \App\Livewire\Owner\Inventory\StockTransferManagement::class)->can('transfer_stock')->name('owner.inventory.stocktransfers');

    // rental management
    Route::middleware('module:rental')->group(function () {
        Route::get('/rental/dashboard', \App\Livewire\Owner\Rental\RentalDashboard::class)->can('rental_dashboard')->name('owner.rental.dashboard');
        Route::get('/rental/landlords', \App\Livewire\Owner\Rental\Landlords::class)->can('manage_landlords')->name('owner.rental.landlords');
        Route::get('/rental/properties', \App\Livewire\Owner\Rental\RentalProperties::class)->can('manage_properties')->name('owner.rental.properties');
        Route::get('/rental/units', \App\Livewire\Owner\Rental\RentalUnits::class)->can('manage_rental_units')->name('owner.rental.units');
        Route::get('/rental/rental-agreements', \App\Livewire\Owner\Rental\RentalAgreements::class)->can('manage_rental_agreements')->name('owner.rental.agreements');
        Route::get('/rental/rent-payments', \App\Livewire\Owner\Rental\RentPayments::class)->can('manage_rent_payments')->name('owner.rental.rent-payments');
        Route::get('/rental/utility-bills', \App\Livewire\Owner\Rental\UtilityBills::class)->can('manage_utility_bills')->name('owner.rental.utility-bills');
        Route::get('/rental/maintenance', \App\Livewire\Owner\Rental\MaintenanceRequests::class)->can('rental_maintenance')->name('owner.rental.maintenance');
    });

});

// Customer Dashboard (Protected Routes - Customer Only)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/', CustomerDashboard::class)->name('customer.dashboard');
    Route::get('/businesses', BrowseBusinesses::class)->name('customer.businesses');
    Route::get('/businesses/{id}', BusinessDetails::class)->name('customer.business.details');
    Route::get('/bookings', MyBookings::class)->name('customer.bookings');
    Route::get('/profile', CustomerProfile::class)->name('customer.profile');
});
