<?php

namespace App\Livewire\Dashboard\Modules;

use Livewire\Component;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class Index extends Component
{
    public $search = '';
    public $showCreateModal = false;
    public $newModuleName = '';

    public function openCreateModal()
    {
        $this->showCreateModal = true;
        $this->newModuleName = '';
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->newModuleName = '';
        $this->resetValidation();
    }

    public function scanModules()
    {
        try {
            // Run composer dump-autoload to register any new modules
            $basePath = base_path();
            $command = "cd {$basePath} && composer dump-autoload 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                Artisan::call('config:clear');
                Artisan::call('cache:clear');
                session()->flash('success', 'Modules scanned and autoload regenerated successfully!');
            } else {
                session()->flash('error', 'Failed to regenerate autoload: ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to scan modules: ' . $e->getMessage());
        }
    }

    public function toggleModule($moduleName)
    {
        try {
            $module = Module::find($moduleName);

            if (!$module) {
                session()->flash('error', "Module '{$moduleName}' not found.");
                return;
            }

            if ($module->isEnabled()) {
                $module->disable();
                session()->flash('success', "Module '{$moduleName}' has been disabled.");
            } else {
                $module->enable();
                session()->flash('success', "Module '{$moduleName}' has been enabled.");
            }

            // Clear cache after module state change
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to toggle module: ' . $e->getMessage());
        }
    }

    public function createModule()
    {
        $this->validate([
            'newModuleName' => 'required|alpha_dash|min:2|max:50',
        ], [
            'newModuleName.required' => 'Module name is required.',
            'newModuleName.alpha_dash' => 'Module name can only contain letters, numbers, dashes and underscores.',
            'newModuleName.min' => 'Module name must be at least 2 characters.',
            'newModuleName.max' => 'Module name must not exceed 50 characters.',
        ]);

        try {
            // Convert to StudlyCase for module name
            $moduleName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $this->newModuleName)));

            // Check if module already exists
            if (Module::find($moduleName)) {
                session()->flash('error', "Module '{$moduleName}' already exists.");
                return;
            }

            Artisan::call('module:make', ['name' => [$moduleName]]);

            // Update the module's composer.json to register service provider
            $modulePath = base_path("Modules/{$moduleName}");
            $composerJsonPath = $modulePath . '/composer.json';

            if (File::exists($composerJsonPath)) {
                $composerJson = json_decode(File::get($composerJsonPath), true);
                $composerJson['extra']['laravel']['providers'] = [
                    "Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider"
                ];
                File::put($composerJsonPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }

            // Run composer dump-autoload
            $basePath = base_path();
            $command = "cd {$basePath} && composer dump-autoload 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                session()->flash('success', "Module '{$moduleName}' created successfully!");
                $this->closeCreateModal();
            } else {
                session()->flash('error', "Module created but autoload failed. Please run 'composer dump-autoload' manually.");
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create module: ' . $e->getMessage());
        }
    }

    public function deleteModule($moduleName)
    {
        try {
            $module = Module::find($moduleName);

            if (!$module) {
                session()->flash('error', "Module '{$moduleName}' not found.");
                return;
            }

            // Delete module directory
            $modulePath = $module->getPath();
            if (File::exists($modulePath)) {
                File::deleteDirectory($modulePath);
            }

            session()->flash('success', "Module '{$moduleName}' has been deleted.");

            // Clear cache after module deletion
            Artisan::call('config:clear');
            Artisan::call('cache:clear');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete module: ' . $e->getMessage());
        }
    }

    public function getModulesProperty()
    {
        $modules = Module::all();

        if ($this->search) {
            $modules = collect($modules)->filter(function ($module) {
                return stripos($module->getName(), $this->search) !== false ||
                       stripos($module->getDescription(), $this->search) !== false;
            });
        }

        return $modules;
    }

    public function render()
    {
        return view('livewire.dashboard.modules.index', [
            'modules' => $this->modules,
        ]);
    }
}
