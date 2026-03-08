<?php

namespace App\Livewire\Dashboard\Settings;

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
