<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Ensures staff users can only access businesses they are assigned to via user_business_roles.
     * Owners can access all their owned businesses.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only apply to staff users
        if ($user && $user->role === 'staff') {
            $businessId = $request->route('business_id')
                ?? $request->input('business_id')
                ?? $request->query('business_id');

            if ($businessId) {
                // Check if staff has access to this business
                $hasAccess = $user->businessRoles()
                    ->where('business_id', $businessId)
                    ->where('is_active', true)
                    ->exists();

                if (!$hasAccess) {
                    abort(403, 'You do not have access to this business.');
                }
            }
        }

        return $next($request);
    }
}
