<?php

namespace App\Livewire\Dashboard\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Index extends Component
{
    public $activeTab = 'general';

    // General settings
    public $siteName = 'CAMS';
    public $siteEmail = 'info@techscales.co.tz';
    public $sitePhone = '+255659811966';
    public $siteAddress = 'Dodoma, Tanzania';

    // Profile settings
    public $name = '';
    public $email = '';
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function saveGeneral()
    {
        $this->validate([
            'siteName' => 'required|min:2',
            'siteEmail' => 'required|email',
            'sitePhone' => 'required',
            'siteAddress' => 'required',
        ]);

        // Here you would save to a settings table or config
        session()->flash('success', 'General settings saved successfully.');
    }

    public function saveProfile()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('success', 'Profile updated successfully.');
    }

    public function changePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('success', 'Password changed successfully.');
    }

    public function render()
    {
        return view('livewire.dashboard.settings.index');
    }
}
