<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\RentalMaintenanceRequest;
use App\Models\staffs;
use App\Models\TenancyAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class MaintenanceRequests extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $agreementFilter = '';
    public $assigneeFilter = '';

    // Modal state
    public $showModal = false;
    public $showResolveModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $requestId = null;
    public $viewRequest = null;

    // Form fields
    public $tenancy_agreement_id = '';
    public $maintenance_type = 'plumbing';
    public $description = '';
    public $start_date = '';
    public $end_date = '';
    public $cost = '';
    public $status = 'open';
    public $assigned_to = '';

    // Resolve modal
    public $resolve_cost = '';
    public $resolve_end_date = '';

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();
        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
        $this->start_date = now()->toDateString();
    }

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingAgreementFilter(): void { $this->resetPage(); }
    public function updatingAssigneeFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void
    {
        $this->reset(['agreementFilter', 'assigneeFilter']);
        $this->resetPage();
    }

    // ─── Modal: open/close ──────────────────────────────────────

    public function openAddModal(): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $r = $this->scopedQuery()->find($id);
        if (!$r) return;

        $this->requestId = $r->id;
        $this->tenancy_agreement_id = $r->tenancy_agreement_id;
        $this->maintenance_type = $r->maintenance_type;
        $this->description = $r->description ?? '';
        $this->start_date = $r->start_date?->toDateString();
        $this->end_date = $r->end_date?->toDateString();
        $this->cost = $r->cost ?? '';
        $this->status = $r->status;
        $this->assigned_to = $r->assigned_to ?: '';
        $this->editMode = true;
        $this->showModal = true;
    }

    public function openResolveModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $r = $this->scopedQuery()->find($id);
        if (!$r) return;
        if (in_array($r->status, ['resolved', 'closed', 'cancelled'], true)) {
            session()->flash('error', 'This request is already finished.');
            return;
        }

        $this->requestId = $r->id;
        $this->resolve_cost = $r->cost ?? '';
        $this->resolve_end_date = now()->toDateString();
        $this->showResolveModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->viewRequest = $this->scopedQuery()
            ->with([
                'agreement.customer:id,name,phone',
                'agreement.unit:id,unit_number,property_id',
                'agreement.unit.property:id,property_name',
                'assignee:id,name,phone,position',
            ])
            ->find($id);
        if ($this->viewRequest) {
            $this->showViewModal = true;
        }
    }

    public function closeModal(): void { $this->showModal = false; $this->resetForm(); }
    public function closeResolveModal(): void { $this->showResolveModal = false; $this->requestId = null; }
    public function closeViewModal(): void { $this->showViewModal = false; $this->viewRequest = null; }

    // ─── Save / Status / Resolve / Delete ───────────────────────

    public function saveRequest(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        foreach (['end_date', 'cost', 'description', 'assigned_to'] as $opt) {
            if ($this->$opt === '') $this->$opt = null;
        }

        $businessId = $this->selectedBusiness;

        $agreementScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            $ok = TenancyAgreement::where('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->exists();
            if (!$ok) $fail('Pick an agreement from this business.');
        };

        $assigneeScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            if ($value === null) return;
            $ok = staffs::where('id', $value)->where('business_id', $businessId)->exists();
            if (!$ok) $fail('Assignee must be a staff member of this business.');
        };

        $data = $this->validate([
            'tenancy_agreement_id' => ['required', 'uuid', $agreementScope],
            'maintenance_type' => 'required|in:plumbing,electrical,painting,roofing,furniture,appliance,pest_control,cleaning,security,other',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,in_progress,resolved,closed,cancelled',
            'assigned_to' => ['nullable', 'uuid', $assigneeScope],
        ]);

        try {
            if ($this->editMode && $this->requestId) {
                $r = $this->scopedQuery()->find($this->requestId);
                if (!$r) return;
                $r->update($data);
                session()->flash('message', 'Maintenance request updated.');
            } else {
                RentalMaintenanceRequest::create($data);
                session()->flash('message', 'Maintenance request logged.');
            }
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving request: ' . $e->getMessage());
        }
    }

    public function setStatus(string $id, string $newStatus): void
    {
        if (!in_array($newStatus, ['open', 'in_progress', 'cancelled', 'closed'], true)) return;
        if (!$this->ensureBusinessSelected()) return;
        $r = $this->scopedQuery()->find($id);
        if (!$r) return;

        $payload = ['status' => $newStatus];
        if ($newStatus === 'closed' && !$r->end_date) {
            $payload['end_date'] = now()->toDateString();
        }
        $r->update($payload);
        session()->flash('message', "Status set to {$newStatus}.");
    }

    public function resolveRequest(): void
    {
        if (!$this->ensureBusinessSelected() || !$this->requestId) return;
        if ($this->resolve_cost === '') $this->resolve_cost = null;

        $data = $this->validate([
            'resolve_end_date' => 'required|date',
            'resolve_cost' => 'nullable|numeric|min:0',
        ]);

        try {
            $r = $this->scopedQuery()->find($this->requestId);
            if (!$r) return;
            $r->update([
                'status' => 'resolved',
                'end_date' => $data['resolve_end_date'],
                'cost' => $data['resolve_cost'],
            ]);
            session()->flash('message', 'Maintenance request resolved.');
            $this->closeResolveModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error resolving request: ' . $e->getMessage());
        }
    }

    public function deleteRequest(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $r = $this->scopedQuery()->find($id);
        if (!$r) return;

        try {
            $r->delete();
            session()->flash('message', 'Maintenance request deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting request.');
        }
    }

    // ─── Helpers ────────────────────────────────────────────────

    protected function ensureBusinessSelected(): bool
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a rental business first.');
            return false;
        }
        return true;
    }

    protected function scopedQuery()
    {
        return RentalMaintenanceRequest::whereHas('agreement.landlord', function ($q) {
            $q->where('business_id', $this->selectedBusiness);
        });
    }

    public function resetForm(): void
    {
        $this->reset(['requestId', 'editMode', 'tenancy_agreement_id', 'description', 'end_date', 'cost', 'assigned_to']);
        $this->maintenance_type = 'plumbing';
        $this->start_date = now()->toDateString();
        $this->status = 'open';
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        $agreements = $this->selectedBusiness
            ? TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->whereIn('agreement_status', ['active', 'draft', 'expired', 'terminated'])
                ->with(['customer:id,name', 'unit:id,unit_number'])
                ->orderBy('start_date', 'desc')
                ->get()
            : collect();

        $staff = $this->selectedBusiness
            ? staffs::where('business_id', $this->selectedBusiness)->orderBy('name')->get()
            : collect();

        if (!$this->selectedBusiness) {
            $requests = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $stats = ['open' => 0, 'in_progress' => 0, 'resolved_this_month' => 0, 'cost_this_month' => 0];
        } else {
            $requests = $this->scopedQuery()
                ->with([
                    'agreement.customer:id,name',
                    'agreement.unit:id,unit_number',
                    'assignee:id,name',
                ])
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('description', 'like', '%' . $this->search . '%')
                           ->orWhereHas('agreement.customer', fn ($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->typeFilter, fn ($q) => $q->where('maintenance_type', $this->typeFilter))
                ->when($this->agreementFilter, fn ($q) => $q->where('tenancy_agreement_id', $this->agreementFilter))
                ->when($this->assigneeFilter, fn ($q) => $q->where('assigned_to', $this->assigneeFilter))
                ->latest()
                ->paginate(15);

            $base = $this->scopedQuery();
            $monthStart = now()->startOfMonth();
            $stats = [
                'open' => (clone $base)->where('status', 'open')->count(),
                'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
                'resolved_this_month' => (clone $base)->where('status', 'resolved')->where('end_date', '>=', $monthStart)->count(),
                'cost_this_month' => (clone $base)->where('end_date', '>=', $monthStart)->sum('cost'),
            ];
        }

        return view('livewire.owner.rental.maintenance-requests', [
            'requests' => $requests,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'agreements' => $agreements,
            'staff' => $staff,
        ]);
    }
}
