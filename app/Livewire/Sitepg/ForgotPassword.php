<?php

namespace App\Livewire\Sitepg;

use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPassword extends Component
{
    public string $email = '';

    protected array $rules = [
        'email' => 'required|email',
    ];

    protected array $messages = [
        'email.required' => 'Please enter your email address.',
        'email.email' => 'Please enter a valid email address.',
    ];

    public function sendResetLink()
    {
        $this->validate();

        // Password::sendResetLink emails the reset link (or returns an error status).
        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', __($status));
            $this->reset('email');
            return;
        }

        // INVALID_USER / throttled etc. — surface the translated message.
        $this->addError('email', __($status));
    }

    public function render()
    {
        return view('livewire.sitepg.forgot-password')
            ->layout('components.layouts.sitepg', ['title' => 'Forgot Password - CAMS']);
    }
}
