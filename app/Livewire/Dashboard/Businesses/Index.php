<?php

namespace App\Livewire\Dashboard\Businesses;

use App\Models\Business;
use App\Models\Module;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    // Module-management modal state
    public bool $showModulesModal = false;
    public ?string $manageBusinessId = null;
    public ?string $manageBusinessName = null;

    /** module_id => bool (checked state in the modal) */
    public array $selectedModules = [];

    public function openModules(string $businessId): void
    {
        $business = Business::with('modules')->findOrFail($businessId);

        $this->manageBusinessId = $business->id;
        $this->manageBusinessName = $business->name;

        // Pre-check the modules already granted (and active) for this business.
        $assigned = $business->modules
            ->filter(fn ($m) => (bool) ($m->pivot->is_active ?? true))
            ->pluck('id')
            ->all();

        $this->selectedModules = Module::pluck('id')
            ->mapWithKeys(fn ($id) => [$id => in_array($id, $assigned, true)])
            ->all();

        $this->showModulesModal = true;
    }

    public function saveModules(): void
    {
        if (!$this->manageBusinessId) {
            return;
        }

        $business = Business::findOrFail($this->manageBusinessId);

        // Build the sync payload: only the checked modules, all active.
        $sync = [];
        foreach ($this->selectedModules as $moduleId => $checked) {
            if ($checked) {
                $sync[$moduleId] = ['is_active' => true];
            }
        }

        $business->modules()->sync($sync);

        session()->flash('success', 'Modules updated for ' . $business->name . '.');
        $this->closeModules();
    }

    public function closeModules(): void
    {
        $this->showModulesModal = false;
        $this->manageBusinessId = null;
        $this->manageBusinessName = null;
        $this->selectedModules = [];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $businesses = Business::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['owner', 'regions', 'districts', 'wards', 'modules'])
            ->paginate(10);

        return view('livewire.dashboard.businesses.index', [
            'businesses' => $businesses,
            'allModules' => Module::orderBy('name')->get(),
        ]);
    }
}
