<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Sitepg\Home;
use App\Livewire\Sitepg\About;
use App\Livewire\Sitepg\Services;
use App\Livewire\Sitepg\Contact;
use App\Livewire\Sitepg\Login;
use App\Livewire\Sitepg\Register;

// Site Pages
Route::get('/', Home::class)->name('site.home');
Route::get('/about', About::class)->name('site.about');
Route::get('/services', Services::class)->name('site.services');
Route::get('/contact', Contact::class)->name('site.contact');
Route::get('/login', Login::class)->name('site.login');
Route::get('/register', Register::class)->name('site.register');
