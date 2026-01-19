<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            Log::info('Login successful for: ' . $request->email);

            return redirect($this->getDashboardRoute());
        }

        Log::warning('Login failed for: ' . $request->email);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function getDashboardRoute(): string
    {
        return match (Auth::user()->role) {
            'admin' => route('admin.dashboard'),
            'owner' => route('owner.dashboard'),
            'customer' => route('customer.dashboard'),
            'staff' => route('owner.dashboard'),
            default => route('site.home'),
        };
    }
}
