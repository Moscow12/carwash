<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Gate owner/staff routes behind module access. Usage: `module:rental`,
     * or `module:bar,restaurant` to allow any one of several modules.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$modules
     */
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        // Admins have implicit access to every module.
        if ($user->role === 'admin') {
            return $next($request);
        }

        $accessible = $user->accessibleModuleKeys();

        // Pass when the user can access at least one of the required modules.
        foreach ($modules as $module) {
            if ($accessible->contains($module)) {
                return $next($request);
            }
        }

        $label = ucfirst($modules[0] ?? 'this');
        session()->flash('error', "Your business doesn't have access to the {$label} module. Contact your administrator.");

        return redirect()->route('owner.dashboard');
    }
}
