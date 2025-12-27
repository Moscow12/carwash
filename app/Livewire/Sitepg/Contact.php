<?php

namespace App\Livewire\Sitepg;

use Livewire\Component;

class Contact extends Component
{
    public $name = '';
    public $email = '';
    public $phone = '';
    public $subject = '';
    public $message = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'phone' => 'required',
        'subject' => 'required|min:5',
        'message' => 'required|min:10',
    ];

    public function submit()
    {
        $this->validate();

        // Here you can add logic to save the message or send email
        session()->flash('success', 'Thank you for your message! We will get back to you soon.');

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
    }

    public function render()
    {
        return view('livewire.sitepg.contact')
            ->layout('components.layouts.sitepg', ['title' => 'Contact Us - CAMS']);
    }
}
