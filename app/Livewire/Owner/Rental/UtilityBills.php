<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\Payment;
use App\Models\payment_method;
use App\Models\TenancyAgreement;
use App\Models\UtilityBill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class UtilityBills extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $billTypeFilter = '';
    public $statusFilter = '';
    public $monthFilter = '';
    public $agreementFilter = '';

    // Modal state
    public $showModal = false;
    public $showSettleModal = false;
    public $showBulkModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $billId = null;
    public $viewBill = null;

    // Add/edit form
    public $tenancy_agreement_id = '';
    public $bill_type = 'electricity';
    public $billing_month = '';
    public $amount = '';
    public $status = 'unpaid';
    public $notes = '';

    // Settle form
    public $settle_paid_at = '';
    public $settle_payment_method_id = '';
    public $settle_reference_no = '';

    // Bulk-issue form
    public $bulk_bill_type = 'electricity';
    public $bulk_billing_month = '';
    public $bulk_amount = '';
    public $bulk_agreement_ids = [];

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();
        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
        $this->billing_month = now()->startOfMonth()->toDateString();
        $this->bulk_billing_month = now()->startOfMonth()->toDateString();
        $this->settle_paid_at = now()->toDateString();
    }

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingBillTypeFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingMonthFilter(): void { $this->resetPage(); }
    public function updatingAgreementFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void
    {
        $this->reset(['agreementFilter']);
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
        $bill = $this->scopedQuery()->find($id);
        if (!$bill) return;

        $this->billId = $bill->id;
        $this->tenancy_agreement_id = $bill->tenancy_agreement_id;
        $this->bill_type = $bill->bill_type;
        $this->billing_month = $bill->billing_month?->toDateString();
        $this->amount = $bill->amount;
        $this->status = $bill->status;
        $this->notes = $bill->notes ?? '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function openSettleModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $bill = $this->scopedQuery()->find($id);
        if (!$bill || $bill->status === 'paid' || $bill->status === 'waived') return;

        $this->billId = $bill->id;
        $this->amount = $bill->amount;
        $this->settle_paid_at = now()->toDateString();
        $this->settle_payment_method_id = '';
        $this->settle_reference_no = '';
        $this->showSettleModal = true;
    }

    public function openBulkModal(): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->bulk_bill_type = 'electricity';
        $this->bulk_billing_month = now()->startOfMonth()->toDateString();
        $this->bulk_amount = '';
        $this->bulk_agreement_ids = [];
        $this->showBulkModal = true;
        $this->resetValidation();
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->viewBill = $this->scopedQuery()
            ->with([
                'agreement.customer:id,name,phone',
                'agreement.unit:id,unit_number,property_id',
                'agreement.unit.property:id,property_name',
                'payment:id,payable_id,payable_type,payment_method_id,reference_no,receipt_channel,status,paid_at',
                'payment.paymentMethod:id,name',
            ])
            ->find($id);
        if ($this->viewBill) {
            $this->showViewModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }
    public function closeSettleModal(): void { $this->showSettleModal = false; $this->billId = null; }
    public function closeBulkModal(): void { $this->showBulkModal = false; }
    public function closeViewModal(): void { $this->showViewModal = false; $this->viewBill = null; }

    // ─── Save / Settle / Delete ─────────────────────────────────

    public function saveBill(): void
    {
        if (!$this->ensureBusinessSelected()) return;
        if ($this->notes === '') $this->notes = null;

        $businessId = $this->selectedBusiness;
        $billId = $this->billId;

        $agreementScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            $ok = TenancyAgreement::where('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->exists();
            if (!$ok) $fail('Pick an agreement from this business.');
        };

        $unique = function (string $a, $value, \Closure $fail) use ($billId) {
            unset($a);
            $month = Carbon::parse($this->billing_month)->startOfMonth()->toDateString();
            $exists = UtilityBill::where('tenancy_agreement_id', $this->tenancy_agreement_id)
                ->where('bill_type', $this->bill_type)
                ->where('billing_month', $month)
                ->when($billId, fn ($q) => $q->where('id', '!=', $billId))
                ->exists();
            if ($exists) $fail("A {$this->bill_type} bill already exists for this agreement and month.");
        };

        $data = $this->validate([
            'tenancy_agreement_id' => ['required', 'uuid', $agreementScope],
            'bill_type' => ['required', 'in:water,electricity,internet,gas,security,service_charge,other', $unique],
            'billing_month' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'status' => 'required|in:paid,unpaid,partial,waived',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['billing_month'] = Carbon::parse($data['billing_month'])->startOfMonth()->toDateString();

        try {
            if ($this->editMode && $this->billId) {
                $bill = $this->scopedQuery()->find($this->billId);
                if (!$bill) return;
                $bill->update($data);
                session()->flash('message', 'Bill updated.');
            } else {
                UtilityBill::create($data);
                session()->flash('message', 'Bill issued.');
            }
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving bill: ' . $e->getMessage());
        }
    }

    public function settleBill(): void
    {
        if (!$this->ensureBusinessSelected() || !$this->billId) return;
        if ($this->settle_reference_no === '') $this->settle_reference_no = null;

        $data = $this->validate([
            'settle_paid_at' => 'required|date',
            'settle_payment_method_id' => 'nullable|uuid|exists:payment_methods,id',
            'settle_reference_no' => 'nullable|string|max:100',
        ]);

        $businessId = $this->selectedBusiness;
        $billId = $this->billId;

        try {
            DB::transaction(function () use ($data, $businessId, $billId) {
                $bill = $this->scopedQuery()->find($billId);
                if (!$bill) return;

                $bill->update([
                    'status' => 'paid',
                    'paid_at' => $data['settle_paid_at'],
                ]);

                // Mirror in unified ledger only when a payment method is supplied
                if (!empty($data['settle_payment_method_id'])) {
                    Payment::updateOrCreate(
                        ['payable_type' => 'utility_bill', 'payable_id' => $bill->id],
                        [
                            'business_id' => $businessId,
                            'payment_method_id' => $data['settle_payment_method_id'],
                            'amount' => $bill->amount,
                            'currency' => 'TZS',
                            'exchange_rate' => 1,
                            'amount_local' => $bill->amount,
                            'reference_no' => $data['settle_reference_no'],
                            'status' => 'completed',
                            'received_by' => Auth::id(),
                            'paid_at' => $data['settle_paid_at'],
                            'receipt_channel' => 'none',
                        ]
                    );
                }
            });
            session()->flash('message', 'Bill settled.');
            $this->closeSettleModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error settling bill: ' . $e->getMessage());
        }
    }

    public function bulkIssue(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $businessId = $this->selectedBusiness;

        $agreementsScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            if (!is_array($value) || empty($value)) {
                $fail('Pick at least one agreement.');
                return;
            }
            $count = TenancyAgreement::whereIn('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->count();
            if ($count !== count($value)) {
                $fail('One or more agreements do not belong to this business.');
            }
        };

        $data = $this->validate([
            'bulk_bill_type' => 'required|in:water,electricity,internet,gas,security,service_charge,other',
            'bulk_billing_month' => 'required|date',
            'bulk_amount' => 'required|numeric|min:0.01',
            'bulk_agreement_ids' => ['required', 'array', 'min:1', $agreementsScope],
            'bulk_agreement_ids.*' => 'uuid',
        ]);

        $month = Carbon::parse($data['bulk_billing_month'])->startOfMonth()->toDateString();

        $created = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($data, $month, &$created, &$skipped) {
                foreach ($data['bulk_agreement_ids'] as $agreementId) {
                    $exists = UtilityBill::where('tenancy_agreement_id', $agreementId)
                        ->where('bill_type', $data['bulk_bill_type'])
                        ->where('billing_month', $month)
                        ->exists();
                    if ($exists) {
                        $skipped++;
                        continue;
                    }
                    UtilityBill::create([
                        'tenancy_agreement_id' => $agreementId,
                        'bill_type' => $data['bulk_bill_type'],
                        'billing_month' => $month,
                        'amount' => $data['bulk_amount'],
                        'status' => 'unpaid',
                    ]);
                    $created++;
                }
            });

            $msg = "Issued {$created} bill(s)";
            if ($skipped > 0) {
                $msg .= " — {$skipped} skipped (already exist for that month)";
            }
            session()->flash('message', $msg);
            $this->closeBulkModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error during bulk issue: ' . $e->getMessage());
        }
    }

    public function deleteBill(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $bill = $this->scopedQuery()->find($id);
        if (!$bill) return;

        if ($bill->payment) {
            session()->flash('error', 'Cannot delete a bill linked to a ledger payment. Void the payment first.');
            return;
        }

        try {
            $bill->delete();
            session()->flash('message', 'Bill deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting bill.');
        }
    }

    public function setStatus(string $id, string $newStatus): void
    {
        if (!in_array($newStatus, ['unpaid', 'partial', 'waived'], true)) return;
        if (!$this->ensureBusinessSelected()) return;
        $bill = $this->scopedQuery()->find($id);
        if (!$bill) return;

        $bill->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'waived' ? now()->toDateString() : null,
        ]);
        session()->flash('message', "Marked as {$newStatus}.");
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
        return UtilityBill::whereHas('agreement.landlord', function ($q) {
            $q->where('business_id', $this->selectedBusiness);
        });
    }

    public function resetForm(): void
    {
        $this->reset(['billId', 'editMode', 'tenancy_agreement_id', 'amount', 'notes']);
        $this->bill_type = 'electricity';
        $this->billing_month = now()->startOfMonth()->toDateString();
        $this->status = 'unpaid';
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        $agreements = $this->selectedBusiness
            ? TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->whereIn('agreement_status', ['active', 'draft', 'expired'])
                ->with(['customer:id,name', 'unit:id,unit_number'])
                ->orderBy('start_date', 'desc')
                ->get()
            : collect();

        $activeAgreements = $agreements->filter(fn ($a) => $a->agreement_status === 'active')->values();

        $methods = $this->selectedBusiness
            ? payment_method::where('business_id', $this->selectedBusiness)->orderBy('name')->get()
            : collect();

        if (!$this->selectedBusiness) {
            $bills = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $stats = ['issued_this_month' => 0, 'collected_this_month' => 0, 'outstanding_total' => 0, 'unpaid_count' => 0];
        } else {
            $bills = $this->scopedQuery()
                ->with([
                    'agreement.customer:id,name',
                    'agreement.unit:id,unit_number',
                ])
                ->when($this->search, function ($q) {
                    $q->whereHas('agreement.customer', fn ($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                })
                ->when($this->billTypeFilter, fn ($q) => $q->where('bill_type', $this->billTypeFilter))
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->monthFilter, function ($q) {
                    $month = Carbon::parse($this->monthFilter . '-01')->startOfMonth()->toDateString();
                    $q->where('billing_month', $month);
                })
                ->when($this->agreementFilter, fn ($q) => $q->where('tenancy_agreement_id', $this->agreementFilter))
                ->orderBy('billing_month', 'desc')
                ->orderBy('bill_type')
                ->paginate(15);

            $base = $this->scopedQuery();
            $thisMonthStart = now()->startOfMonth()->toDateString();
            $stats = [
                'issued_this_month' => (clone $base)->where('billing_month', $thisMonthStart)->sum('amount'),
                'collected_this_month' => (clone $base)->where('billing_month', $thisMonthStart)->where('status', 'paid')->sum('amount'),
                'outstanding_total' => (clone $base)->whereIn('status', ['unpaid', 'partial'])->sum('amount'),
                'unpaid_count' => (clone $base)->whereIn('status', ['unpaid', 'partial'])->count(),
            ];
        }

        return view('livewire.owner.rental.utility-bills', [
            'bills' => $bills,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'agreements' => $agreements,
            'activeAgreements' => $activeAgreements,
            'methods' => $methods,
        ]);
    }
}
