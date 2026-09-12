<?php

namespace App\Http\Middleware;

use App\Models\BusinessSubscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /** Routes an owner/staff user must always be able to reach, even while lapsed. */
    private const ALLOWED_ROUTES = ['owner.subscription', 'owner.settings'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Admins are not gated by tenant subscription status.
        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        $business = $user->assignedBusinesses()->first();

        if (!$business) {
            return $next($request);
        }

        $subscription = BusinessSubscription::getForBusiness($business->id);

        if ($subscription->isActive()) {
            if ($subscription->isExpiringSoon() && !$request->routeIs('owner.subscription')) {
                $days = $subscription->daysUntilExpiry();
                $when = $days === 0 ? 'today' : 'in ' . $days . ' ' . str('day')->plural($days);

                session()->flash('subscription_warning', "Your subscription expires {$when} ({$subscription->expires_at->format('M j, Y')}). Renew now to avoid interruption.");
            }

            return $next($request);
        }

        session()->flash('error', 'Your business subscription has lapsed. Please renew to continue.');

        return redirect()->route('owner.subscription');
    }
}
