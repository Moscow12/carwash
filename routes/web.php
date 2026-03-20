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

// Owner Pages
use App\Livewire\Owner\Dashboard as OwnerDashboard;
use App\Livewire\Owner\Businesses\Index as OwnerBusinesses;
use App\Livewire\Owner\Items\Index as OwnerItems;
use App\Livewire\Owner\Staffs\Index as OwnerStaffs;
use App\Livewire\Owner\Customers\Index as OwnerCustomers;
use App\Livewire\Owner\Sales\Index as OwnerSales;
use App\Livewire\Owner\Purchases\Index as OwnerPurchases;
use App\Livewire\Owner\Stocktaking\Index as OwnerStocktaking;
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

// Admin Authentication
use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Owner\Bar\BarManagement;
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
});

// Owner Dashboard (Protected Routes - Owner Only)
Route::middleware(['auth', 'role:owner'])->prefix('owner')->group(function () {
    Route::get('/', OwnerDashboard::class)->name('owner.dashboard');
    Route::get('/businesses', OwnerBusinesses::class)->name('owner.businesses');
    Route::get('/items', OwnerItems::class)->name('owner.items');
    Route::get('/items/edit/{itemId}', Edititems::class)->name('owner.items.edit');
    Route::get('/staffs', OwnerStaffs::class)->name('owner.staffs');
    Route::get('/customers', OwnerCustomers::class)->name('owner.customers');
    Route::get('/sales', OwnerSales::class)->name('owner.sales');
    Route::get('/purchases', OwnerPurchases::class)->name('owner.purchases');
    Route::get('/stocktaking', OwnerStocktaking::class)->name('owner.stocktaking');
    Route::get('/suppliers', OwnerSuppliers::class)->name('owner.suppliers');
    Route::get('/units', Units::class)->name('owner.units');
    Route::get('/my-business', Mybusiness::class)->name('owner.mybusiness');
    Route::get('/categories', Categories::class)->name('owner.categories');
    Route::get('/item-register', Itemregister::class)->name('owner.itemregister');
    Route::get('/upload-items', Uploaditems::class)->name('owner.uploaditems');
    Route::get('/pos-screen', Posscreen::class)->name('owner.posscreen');
    Route::get('/settings', OwnerSettings::class)->name('owner.settings');
    Route::get('/settings/hotel', Hotelsettings::class)->name('owner.settings.hotel');

    // Hotel Management Routes
    Route::get('/hotels', Addhotel::class)->name('owner.hotels');
    Route::get('/hotel/addhotel', Addhotel::class)->name('owner.hotel.list');

    // Front Desk
    Route::get('/hotel/frontdesk', Frontdesk::class)->name('owner.hotel.frontdesk');
    Route::get('/hotel/reservations', Reservations::class)->name('owner.hotel.reservations');
    Route::get('/hotel/checkin', Checkin::class)->name('owner.hotel.checkin');
    Route::get('/hotel/checkout', Checkout::class)->name('owner.hotel.checkout');
    Route::get('/hotel/room-status', RoomStatus::class)->name('owner.hotel.room-status');

    // Rooms & Inventory
    Route::get('/hotel/room-types', RoomTypes::class)->name('owner.hotel.room-types');
    Route::get('/hotel/rooms', Rooms::class)->name('owner.hotel.rooms');
    Route::get('/hotel/rate-plans', RatePlans::class)->name('owner.hotel.rate-plans');
    Route::get('/hotel/availability', RoomAvailability::class)->name('owner.hotel.availability');

    // Guests
    Route::get('/hotel/guests', Guests::class)->name('owner.hotel.guests');
    Route::get('/hotel/guest-documents', GuestDocuments::class)->name('owner.hotel.guest-documents');
    Route::get('/hotel/communication-log', CommunicationLog::class)->name('owner.hotel.communication-log');

    // Housekeeping
    Route::get('/hotel/housekeeping/tasks', HousekeepingTasks::class)->name('owner.hotel.housekeeping.tasks');
    Route::get('/hotel/housekeeping/status', HousekeepingStatus::class)->name('owner.hotel.housekeeping.status');
    Route::get('/hotel/lost-found', LostAndFound::class)->name('owner.hotel.lost-found');

    // Maintenance
    Route::get('/hotel/maintenance/requests', MaintenanceManagement::class)->name('owner.hotel.maintenance.requests');
    Route::get('/hotel/maintenance/preventive', MaintenanceManagement::class)->name('owner.hotel.maintenance.preventive');

    // Billing & Finance
    Route::get('/hotel/folios', BillingFinance::class)->name('owner.hotel.folios');
    Route::get('/hotel/invoices', BillingFinance::class)->name('owner.hotel.invoices');
    Route::get('/hotel/payments', BillingFinance::class)->name('owner.hotel.payments');
    Route::get('/hotel/night-audit', NightAudit::class)->name('owner.hotel.night-audit');
    Route::get('/hotel/tax-config', BillingFinance::class)->name('owner.hotel.tax-config');

    // F&B Management
    Route::get('/hotel/pos-outlets', FoodBeverage::class)->name('owner.hotel.pos-outlets');
    Route::get('/hotel/pos-orders', FoodBeverage::class)->name('owner.hotel.pos-orders');
    Route::get('/hotel/tables', FoodBeverage::class)->name('owner.hotel.tables');
    Route::get('/hotel/pos-sessions', FoodBeverage::class)->name('owner.hotel.pos-sessions');

    // Reports
    Route::get('/hotel/reports/occupancy', HotelReports::class)->name('owner.hotel.reports.occupancy');
    Route::get('/hotel/reports/revenue', HotelReports::class)->name('owner.hotel.reports.revenue');
    Route::get('/hotel/reports/reservations', HotelReports::class)->name('owner.hotel.reports.reservations');
    Route::get('/hotel/reports/housekeeping', HotelReports::class)->name('owner.hotel.reports.housekeeping');
    Route::get('/hotel/reports/guest-history', HotelReports::class)->name('owner.hotel.reports.guest-history');

    // Channels & Integrations
    Route::get('/hotel/booking-sources', BookingSources::class)->name('owner.hotel.booking-sources');
    Route::get('/hotel/channel-mapping', ChannelMapping::class)->name('owner.hotel.channel-mapping');

    Route::get('/expenses/categories', Category::class)->name('owner.expenses.categories');
    Route::get('/expenses', Expenses::class)->name('owner.expenses');
    Route::get('/list-items', Listitems::class)->name('owner.list-items');
    Route::get('/history/{itemId}', History::class)->name('owner.history');
    Route::get('/reports/profit-and-loss', Profitandloss::class)->name('owner.reports.profitandloss');
    Route::get('/reports/sales', Sales::class)->name('owner.reports.sales');
    Route::get('/reports/stock', Stock::class)->name('owner.reports.stock');
    Route::get('/reports/staff-commissions', Staffcommisions::class)->name('owner.reports.staffcommissions');

    Route::get('/barmanagement', BarManagement::class)->name('owner.barmanagement');

    // Restaurant Management
    Route::get('/restaurant/reservations', \App\Livewire\Owner\Restaurant\TableReservations::class)->name('owner.restaurant.reservations');
    Route::get('/restaurant/waiters', \App\Livewire\Owner\Restaurant\WaiterManagement::class)->name('owner.restaurant.waiters');
    Route::get('/restaurant/recipes', \App\Livewire\Owner\Restaurant\MenuRecipes::class)->name('owner.restaurant.recipes');

    // Hotel Guest Services
    Route::get('/hotel/amenity-requests', \App\Livewire\Owner\Hotel\AmenityRequests::class)->name('owner.hotel.amenity-requests');
    Route::get('/hotel/wakeup-calls', \App\Livewire\Owner\Hotel\WakeupCalls::class)->name('owner.hotel.wakeup-calls');
    Route::get('/hotel/laundry-orders', \App\Livewire\Owner\Hotel\LaundryManagement::class)->name('owner.hotel.laundry-orders');

});

// Customer Dashboard (Protected Routes - Customer Only)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/', CustomerDashboard::class)->name('customer.dashboard');
    Route::get('/businesses', BrowseBusinesses::class)->name('customer.businesses');
    Route::get('/businesses/{id}', BusinessDetails::class)->name('customer.business.details');
    Route::get('/bookings', MyBookings::class)->name('customer.bookings');
    Route::get('/profile', CustomerProfile::class)->name('customer.profile');
});
