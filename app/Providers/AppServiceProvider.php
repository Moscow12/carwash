<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
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
        // Every permission name is a Gate ability: owners/admins pass everything, staff pass what their role grants
        Gate::before(fn (User $user, string $ability) => $user->hasPermission($ability) ? true : null);

        // The supplier list is open with either supplier permission
        Gate::define('view_suppliers', fn (User $user) => $user->hasPermission('view_all_supplier') || $user->hasPermission('view_own_supplier'));

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
