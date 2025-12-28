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

// Dashboard Pages
use App\Livewire\Dashboard\Auth\Login as AdminLogin;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Dashboard\Users\Index as UsersIndex;
use App\Livewire\Dashboard\Locations\CCountries;
use App\Livewire\Dashboard\Locations\CRegions;
use App\Livewire\Dashboard\Locations\CDistricts;
use App\Livewire\Dashboard\Locations\CWards;
use App\Livewire\Dashboard\Locations\Streets;
use App\Livewire\Dashboard\Settings\Index as SettingsIndex;

// Site Pages
Route::get('/', Home::class)->name('site.home');
Route::get('/about', About::class)->name('site.about');
Route::get('/services', Services::class)->name('site.services');
Route::get('/contact', Contact::class)->name('site.contact');
Route::get('/login', Login::class)->name('site.login');
Route::get('/register', Register::class)->name('site.register');

// Admin Authentication
Route::get('/admin/login', AdminLogin::class)->name('admin.login');
Route::post('/admin/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout');

// Admin Dashboard (Protected Routes)
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', DashboardIndex::class)->name('admin.dashboard');

    // User Management
    Route::get('/users', UsersIndex::class)->name('admin.users');

    // Location Management
    Route::get('/countries', CCountries::class)->name('admin.countries');
    Route::get('/regions', CRegions::class)->name('admin.regions');
    Route::get('/districts', CDistricts::class)->name('admin.districts');
    Route::get('/wards', CWards::class)->name('admin.wards');
    Route::get('/streets', Streets::class)->name('admin.streets');

    // Settings
    Route::get('/settings', SettingsIndex::class)->name('admin.settings');
});
