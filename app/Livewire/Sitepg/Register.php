<?php

namespace App\Livewire\Sitepg;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $terms = false;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email:rfc,dns', 'max:200', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:7', 'max:25', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
        ];
    }

    protected $messages = [
        'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        'email.unique' => 'An account with this email already exists.',
        'phone.unique' => 'An account with this phone number already exists.',
    ];

    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function register()
    {
        $data = $this->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'status' => 'active',
        ]);

        Auth::login($user);
        session()->regenerate();

        session()->flash('success', 'Welcome to CAMS, ' . $user->name . '!');

        return redirect()->route('customer.dashboard');
    }

    public function render()
    {
        return view('livewire.sitepg.register')
            ->layout('components.layouts.sitepg', ['title' => 'Register - CAMS']);
    }
}
