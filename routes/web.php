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
use App\Livewire\Dashboard\Carwashes\Index as AdminCarwashes;

// Owner Pages
use App\Livewire\Owner\Dashboard as OwnerDashboard;
use App\Livewire\Owner\Carwashes\Index as OwnerCarwashes;
use App\Livewire\Owner\Items\Index as OwnerItems;
use App\Livewire\Owner\Staffs\Index as OwnerStaffs;
use App\Livewire\Owner\Customers\Index as OwnerCustomers;
use App\Livewire\Owner\Sales\Index as OwnerSales;
use App\Livewire\Owner\Purchases\Index as OwnerPurchases;
use App\Livewire\Owner\Stocktaking\Index as OwnerStocktaking;
use App\Livewire\Owner\Suppliers\Index as OwnerSuppliers;
use App\Livewire\Owner\Carwashes\Mycarwash;
use App\Livewire\Owner\Items\Categories;
use App\Livewire\Owner\Items\Itemregister;
use App\Livewire\Owner\Items\Uploaditems;

// Customer Pages
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\Carwashes\Browse as BrowseCarwashes;
use App\Livewire\Customer\Carwashes\Details as CarwashDetails;
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
use App\Livewire\Owner\Expenses\Category;
use App\Livewire\Owner\Expenses\Expenses;
use App\Livewire\Owner\Items\History;
use App\Livewire\Owner\Items\Listitems;
use App\Livewire\Owner\Items\Units;
use App\Livewire\Owner\Sales\Posscreen;
use App\Livewire\Owner\Settings\Setup as OwnerSettings;

Route::get('/admin/login', AdminLogin::class)->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.post');

// Debug route - remove after testing
Route::get('/auth-test', function () {
    if (Auth::check()) {
        return 'Logged in as: ' . Auth::user()->email . ' (Role: ' . Auth::user()->role . ') <a href="/admin">Admin</a> | <a href="/owner">Owner</a> | <a href="/customer">Customer</a>';
    }
    return 'Not logged in. <a href="/admin/login">Login</a>';
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('logout');

Route::post('/admin/logout', function () {
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

    // Carwash Management
    Route::get('/carwashes', AdminCarwashes::class)->name('admin.carwashes');

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
    Route::get('/carwashes', OwnerCarwashes::class)->name('owner.carwashes');
    Route::get('/items', OwnerItems::class)->name('owner.items');
    Route::get('/staffs', OwnerStaffs::class)->name('owner.staffs');
    Route::get('/customers', OwnerCustomers::class)->name('owner.customers');
    Route::get('/sales', OwnerSales::class)->name('owner.sales');
    Route::get('/purchases', OwnerPurchases::class)->name('owner.purchases');
    Route::get('/stocktaking', OwnerStocktaking::class)->name('owner.stocktaking');
    Route::get('/suppliers', OwnerSuppliers::class)->name('owner.suppliers');
    Route::get('/units', Units::class)->name('owner.units');
    Route::get('/my-carwash', Mycarwash::class)->name('owner.mycarwash');
    Route::get('/categories', Categories::class)->name('owner.categories');
    Route::get('/item-register', Itemregister::class)->name('owner.itemregister');
    Route::get('/upload-items', Uploaditems::class)->name('owner.uploaditems');
    Route::get('/pos-screen', Posscreen::class)->name('owner.posscreen');
    Route::get('/settings', OwnerSettings::class)->name('owner.settings');
    Route::get('/expenses/categories', Category::class)->name('owner.expenses.categories');
    Route::get('/expenses', Expenses::class)->name('owner.expenses');
    Route::get('/list-items', Listitems::class)->name('owner.list-items');
    Route::get('/history/{itemId}', History::class)->name('owner.history');
});

// Customer Dashboard (Protected Routes - Customer Only)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->group(function () {
    Route::get('/', CustomerDashboard::class)->name('customer.dashboard');
    Route::get('/carwashes', BrowseCarwashes::class)->name('customer.carwashes');
    Route::get('/carwashes/{id}', CarwashDetails::class)->name('customer.carwash.details');
    Route::get('/bookings', MyBookings::class)->name('customer.bookings');
    Route::get('/profile', CustomerProfile::class)->name('customer.profile');
});
