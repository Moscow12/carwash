<?php

namespace App\Livewire\Dashboard\Settings;

use App\Models\PaymentGatewaySetting;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalException;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    public $activeTab = 'general';
    public $showSystemInfo = false;

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

    // Payment gateway (Pesapal) settings
    public $active_environment = 'test';
    public $ipn_notification_type = 'GET';
    public $test_consumer_key = '';
    public $test_consumer_secret = '';
    public $test_ipn_id = '';
    public $live_consumer_key = '';
    public $live_consumer_secret = '';
    public $live_ipn_id = '';
    public $last_test_connection_at = null;
    public $last_test_connection_ok = null;
    public $last_test_connection_message = null;

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;

        $this->hydrateGatewaySettings();
    }

    private function hydrateGatewaySettings(): void
    {
        $settings = PaymentGatewaySetting::current();

        $this->active_environment = $settings->active_environment;
        $this->ipn_notification_type = $settings->ipn_notification_type;
        $this->test_consumer_key = $settings->test_consumer_key ?? '';
        $this->test_consumer_secret = $settings->test_consumer_secret ?? '';
        $this->test_ipn_id = $settings->test_ipn_id ?? '';
        $this->live_consumer_key = $settings->live_consumer_key ?? '';
        $this->live_consumer_secret = $settings->live_consumer_secret ?? '';
        $this->live_ipn_id = $settings->live_ipn_id ?? '';
        $this->last_test_connection_at = $settings->last_test_connection_at?->diffForHumans();
        $this->last_test_connection_ok = $settings->last_test_connection_ok;
        $this->last_test_connection_message = $settings->last_test_connection_message;
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

    public function saveGatewaySettings()
    {
        $this->validate([
            'active_environment' => 'required|in:test,live',
            'ipn_notification_type' => 'required|in:GET,POST',
            'test_consumer_key' => 'nullable|string',
            'test_consumer_secret' => 'nullable|string',
            'live_consumer_key' => 'nullable|string',
            'live_consumer_secret' => 'nullable|string',
        ]);

        PaymentGatewaySetting::current()->update([
            'active_environment' => $this->active_environment,
            'ipn_notification_type' => $this->ipn_notification_type,
            'test_consumer_key' => $this->test_consumer_key ?: null,
            'test_consumer_secret' => $this->test_consumer_secret ?: null,
            'live_consumer_key' => $this->live_consumer_key ?: null,
            'live_consumer_secret' => $this->live_consumer_secret ?: null,
        ]);

        $this->hydrateGatewaySettings();

        session()->flash('success', 'Payment gateway settings saved successfully.');
    }

    public function registerIpn()
    {
        try {
            $client = PesapalClient::forActiveEnvironment();
            $client->registerIpn(route('subscriptions.ipn'));

            $this->hydrateGatewaySettings();
            session()->flash('success', 'IPN URL registered successfully with Pesapal.');
        } catch (PesapalException $e) {
            session()->flash('error', 'Failed to register IPN URL: ' . $e->getMessage());
        }
    }

    public function testConnection()
    {
        $settings = PaymentGatewaySetting::current();

        try {
            PesapalClient::forActiveEnvironment()->getToken();

            $settings->update([
                'last_test_connection_at' => now(),
                'last_test_connection_ok' => true,
                'last_test_connection_message' => 'Connection successful — token retrieved.',
            ]);

            session()->flash('success', 'Pesapal connection test succeeded.');
        } catch (PesapalException $e) {
            $settings->update([
                'last_test_connection_at' => now(),
                'last_test_connection_ok' => false,
                'last_test_connection_message' => $e->getMessage(),
            ]);

            session()->flash('error', 'Pesapal connection test failed: ' . $e->getMessage());
        }

        $this->hydrateGatewaySettings();
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

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            session()->flash('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    public function createBackup()
    {
        try {
            $database = config('database.connections.' . config('database.default'));
            $dbName = $database['database'];
            $dbUser = $database['username'];
            $dbPass = $database['password'];
            $dbHost = $database['host'];

            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backupDir . '/' . $filename;

            // MySQL dump command
            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                escapeshellarg($dbHost),
                escapeshellarg($dbUser),
                escapeshellarg($dbPass),
                escapeshellarg($dbName),
                escapeshellarg($filepath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0 && File::exists($filepath)) {
                session()->flash('success', 'Database backup created successfully: ' . $filename);
                return response()->download($filepath)->deleteFileAfterSend(false);
            } else {
                session()->flash('error', 'Failed to create database backup.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function toggleSystemInfo()
    {
        $this->showSystemInfo = !$this->showSystemInfo;
    }

    public function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_type' => config('database.default'),
            'database_name' => config('database.connections.' . config('database.default') . '.database'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'storage_path' => storage_path(),
            'disk_free_space' => $this->formatBytes(disk_free_space(storage_path())),
            'memory_limit' => ini_get('memory_limit'),
            'max_upload_size' => ini_get('upload_max_filesize'),
            'timezone' => config('app.timezone'),
        ];
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function render()
    {
        return view('livewire.dashboard.settings.index', [
            'systemInfo' => $this->getSystemInfo(),
        ]);
    }
}
