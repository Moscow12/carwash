<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected array $messages = [
        'email.required' => 'Please enter your email address.',
        'email.email' => 'Please enter a valid email address.',
        'password.required' => 'Please enter your password.',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            // Block sign-in for accounts that aren't active.
            if (Auth::user()->status !== 'active') {
                Auth::logout();
                $this->addError('email', 'Your account is not active. Please contact support.');
                return;
            }

            session()->regenerate();

            return redirect()->intended($this->getDashboardRoute());
        }

        $this->addError('email', 'The provided credentials do not match our records.');
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

    public function render()
    {
        return view('livewire.sitepg.login')
            ->layout('components.layouts.sitepg', ['title' => 'Login - CAMS']);
    }
}
