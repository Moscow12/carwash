<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('admin.login');
        }

        $userRole = $request->user()->role;

        if (!in_array($userRole, $roles)) {
            // Redirect to appropriate dashboard based on user role
            return $this->redirectToRoleDashboard($userRole);
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard
     */
    protected function redirectToRoleDashboard(string $role): Response
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'owner' => redirect()->route('owner.dashboard'),
            'customer' => redirect()->route('customer.dashboard'),
            'staff' => redirect()->route('owner.dashboard'), // Staff redirects to owner area
            default => redirect()->route('site.home'),
        };
    }
}
