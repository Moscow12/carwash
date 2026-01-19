<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
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
