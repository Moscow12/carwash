<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LogViewer::auth(function ($request) {
            return $request->user() && $request->user()->role === 'admin';
        });

        // Morph aliases (do NOT enforce — keeps fully-qualified class names that may
        // already exist in storage working). Add new modules here as we wire them in.
        Relation::morphMap([
            'rent_payment' => \App\Models\RentPayment::class,
            'utility_bill' => \App\Models\UtilityBill::class,
        ]);
    }
}
