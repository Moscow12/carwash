<?php

namespace App\Livewire\Sitepg;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    // Optional business registration
    public bool $registerBusiness = false;
    public string $business_name = '';
    public string $business_type = '';

    /** Business types offered on sign-up (value => label). */
    public function businessTypes(): array
    {
        return [
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'hotel' => 'Hotel',
            'rental' => 'Rental',
            'pos' => 'POS / Shop',
            'carwash' => 'Carwash',
            'other' => 'Other',
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:200', 'unique:users,email'],
            'phone' => ['required', 'string', 'min:7', 'max:25', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'terms' => ['accepted'],
            'registerBusiness' => ['boolean'],
            'business_name' => ['exclude_unless:registerBusiness,true', 'required', 'string', 'min:2', 'max:150'],
            'business_type' => ['exclude_unless:registerBusiness,true', 'required', Rule::in(array_keys($this->businessTypes()))],
        ];
    }

    protected $messages = [
        'terms.accepted' => 'You must agree to the Terms of Service and Privacy Policy.',
        'email.unique' => 'An account with this email already exists.',
        'phone.unique' => 'An account with this phone number already exists.',
        'business_name.required' => 'Please enter your business name.',
        'business_type.required' => 'Please choose a business type.',
        'business_type.in' => 'Please choose a valid business type.',
    ];

    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function register()
    {
        $data = $this->validate();

        $user = DB::transaction(function () use ($data) {
            // A user who registers a business becomes an owner; otherwise a customer.
            $role = $this->registerBusiness ? 'owner' : 'customer';

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => $role,
                'status' => 'active',
            ]);

            if ($this->registerBusiness) {
                $business = Business::create([
                    'name' => $data['business_name'],
                    'type' => $data['business_type'],
                    'owner_id' => $user->id,
                    'email' => $data['email'],
                    'resentative_name' => $data['name'],
                    'resentative_phone' => $data['phone'],
                    'status' => 'active',
                ]);

                // Auto-assign the module matching the chosen business type.
                $business->assignModuleForType();
            }

            return $user;
        });

        Auth::login($user);
        session()->regenerate();

        if ($this->registerBusiness) {
            session()->flash('success', 'Welcome to CAMS! Your business "' . $this->business_name . '" is ready.');
            return redirect()->route('owner.dashboard');
        }

        session()->flash('success', 'Welcome to CAMS, ' . $user->name . '!');

        return redirect()->route('customer.dashboard');
    }

    public function render()
    {
        return view('livewire.sitepg.register', [
            'businessTypes' => $this->businessTypes(),
        ])->layout('components.layouts.sitepg', ['title' => 'Register - CAMS']);
    }
}
