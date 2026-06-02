<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\Payment;
use App\Models\payment_method;
use App\Models\RentPayment;
use App\Models\TenancyAgreement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class RentPayments extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Rent-roll controls
    public $rollMonth = '';            // 'Y-m' — which month's roll to show
    public $search = '';               // tenant / unit search
    public $rollStatusFilter = '';     // '', 'paid', 'partial', 'unpaid'

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $showReceiptsModal = false;
    public $editMode = false;
    public $paymentId = null;
    public $viewPayment = null;
    public $receiptsAgreement = null;   // agreement whose receipt history is shown

    // Form fields (mirror rent_payments table)
    public $tenancy_agreement_id = '';
    public $payment_date = '';
    public $amount_paid = '';
    public $payment_method_id = '';
    public $reference_no = '';
    public $payment_for_month = '';
    public $notes = '';
    public $receipt_channel = 'none'; // applied to the mirrored Payment row

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();

        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
        $this->rollMonth = now()->format('Y-m');
        $this->payment_date = now()->toDateString();
        $this->payment_for_month = now()->startOfMonth()->toDateString();
    }

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRollMonth(): void { $this->resetPage(); }
    public function updatingRollStatusFilter(): void { $this->resetPage(); }

    public function updatedSelectedBusiness(): void
    {
        $this->resetPage();
    }

    public function updatedTenancyAgreementId($value): void
    {
        if (!$value) return;
        $agreement = TenancyAgreement::find($value);
        if ($agreement && ($this->amount_paid === '' || (float)$this->amount_paid === 0.0)) {
            $this->amount_paid = $agreement->rent_amount;
        }
    }

    // ─── Modal: open/close ──────────────────────────────────────

    public function openAddModal(): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    /**
     * Record a payment against a specific agreement for the rent-roll month,
     * pre-filling the outstanding balance. Driven by the "Record Payment"
     * button on each unpaid row.
     */
    public function openRecordModal(string $agreementId): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $agreement = TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
            ->find($agreementId);
        if (!$agreement) {
            session()->flash('error', 'That agreement is not part of this business.');
            return;
        }

        $this->resetForm();
        $this->editMode = false;
        $this->showReceiptsModal = false;

        $month = Carbon::parse($this->rollMonth . '-01');
        $due = $agreement->dueAmountForMonth($month);
        $paid = (float) $agreement->rentPayments()->forMonth($this->rollMonth)->sum('amount_paid');
        $remaining = max(0, $due - $paid);

        $this->tenancy_agreement_id = $agreement->id;
        $this->payment_for_month = $month->startOfMonth()->toDateString();
        // Default to the outstanding balance (fall back to full rent if the month isn't a due month)
        $this->amount_paid = $remaining > 0 ? $remaining : $agreement->rent_amount;

        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $rp = $this->scopedQuery()->find($id);
        if (!$rp) return;

        $this->paymentId = $rp->id;
        $this->tenancy_agreement_id = $rp->tenancy_agreement_id;
        $this->payment_date = $rp->payment_date?->toDateString();
        $this->amount_paid = $rp->amount_paid;
        $this->payment_method_id = $rp->payment_method_id ?: '';
        $this->reference_no = $rp->reference_no ?: '';
        $this->payment_for_month = $rp->payment_for_month?->toDateString();
        $this->notes = $rp->notes ?: '';
        $this->receipt_channel = $rp->payment?->receipt_channel ?? 'none';

        $this->editMode = true;
        $this->showReceiptsModal = false;
        $this->showModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->viewPayment = $this->scopedQuery()
            ->with([
                'agreement.customer:id,name,phone',
                'agreement.unit:id,unit_number,property_id',
                'agreement.unit.property:id,property_name',
                'paymentMethod:id,name',
                'receivedBy:id,name',
                'payment:id,payable_id,payable_type,reference_no,receipt_channel,status',
            ])
            ->find($id);

        if ($this->viewPayment) {
            $this->showReceiptsModal = false;
            $this->showViewModal = true;
        }
    }

    /** Show the full receipt history for one agreement. */
    public function openAgreementReceipts(string $agreementId): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $this->receiptsAgreement = TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
            ->with([
                'customer:id,name,phone',
                'unit:id,unit_number,property_id',
                'unit.property:id,property_name',
                'rentPayments' => fn ($q) => $q->with('paymentMethod:id,name', 'receivedBy:id,name')
                    ->orderBy('payment_date', 'desc'),
            ])
            ->find($agreementId);

        if ($this->receiptsAgreement) {
            $this->showReceiptsModal = true;
        }
    }

    public function closeReceiptsModal(): void
    {
        $this->showReceiptsModal = false;
        $this->receiptsAgreement = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewPayment = null;
    }

    // ─── Save / Void ────────────────────────────────────────────

    public function savePayment(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        foreach (['payment_method_id', 'reference_no', 'notes'] as $opt) {
            if ($this->$opt === '') $this->$opt = null;
        }

        $businessId = $this->selectedBusiness;

        $agreementScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            $ok = TenancyAgreement::where('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->whereIn('agreement_status', ['active', 'terminated', 'expired', 'renewed', 'draft'])
                ->exists();
            if (!$ok) $fail('Select a valid tenancy agreement from this business.');
        };

        $data = $this->validate([
            'tenancy_agreement_id' => ['required', 'uuid', $agreementScope],
            'payment_date' => 'required|date',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_method_id' => 'nullable|uuid|exists:payment_methods,id',
            'reference_no' => 'nullable|string|max:100',
            'payment_for_month' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'receipt_channel' => 'required|in:print,email,sms,whatsapp,none',
        ]);

        // Normalize the "for month" to the first of the month
        $data['payment_for_month'] = Carbon::parse($data['payment_for_month'])->startOfMonth()->toDateString();
        $receiptChannel = $data['receipt_channel'];
        unset($data['receipt_channel']);

        try {
            DB::transaction(function () use ($data, $businessId, $receiptChannel) {
                if ($this->editMode && $this->paymentId) {
                    $rp = $this->scopedQuery()->find($this->paymentId);
                    if (!$rp) return;

                    $rp->update($data + ['received_by' => $rp->received_by ?? Auth::id()]);
                    $this->syncMirroredPayment($rp, $businessId, $receiptChannel);
                    session()->flash('message', 'Receipt updated.');
                } else {
                    $rp = RentPayment::create($data + ['received_by' => Auth::id()]);
                    $this->syncMirroredPayment($rp, $businessId, $receiptChannel);
                    session()->flash('message', 'Receipt recorded.');
                }
            });
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving receipt: ' . $e->getMessage());
        }
    }

    /**
     * Create-or-update the matching row in the unified `payments` table.
     * The morph alias 'rent_payment' matches the convention used elsewhere
     * (sale, pos_order, hotel_invoice…).
     */
    protected function syncMirroredPayment(RentPayment $rp, string $businessId, string $receiptChannel): void
    {
        if (!$rp->payment_method_id) {
            // payments.payment_method_id is required (FK restrict); skip mirror when not set
            $rp->payment()->delete();
            return;
        }

        $payload = [
            'business_id' => $businessId,
            'payment_method_id' => $rp->payment_method_id,
            'amount' => $rp->amount_paid,
            'currency' => 'TZS',
            'exchange_rate' => 1,
            'amount_local' => $rp->amount_paid,
            'reference_no' => $rp->reference_no,
            'status' => 'completed',
            'received_by' => $rp->received_by,
            'paid_at' => $rp->payment_date,
            'receipt_channel' => $receiptChannel,
            'notes' => $rp->notes,
        ];

        Payment::updateOrCreate(
            ['payable_type' => 'rent_payment', 'payable_id' => $rp->id],
            $payload
        );
    }

    public function voidPayment(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $rp = $this->scopedQuery()->find($id);
        if (!$rp) return;

        try {
            DB::transaction(function () use ($rp) {
                // Mark the mirrored Payment row as voided (it soft-deletes)
                Payment::where('payable_type', 'rent_payment')
                    ->where('payable_id', $rp->id)
                    ->update(['status' => 'voided']);
                Payment::where('payable_type', 'rent_payment')
                    ->where('payable_id', $rp->id)
                    ->delete();
                $rp->delete();
            });
            session()->flash('message', 'Receipt voided.');
        } catch (\Throwable) {
            session()->flash('error', 'Error voiding receipt.');
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

    /** Rent payments scoped through tenancy_agreement → landlord → business. */
    protected function scopedQuery()
    {
        return RentPayment::whereHas('agreement.landlord', function ($q) {
            $q->where('business_id', $this->selectedBusiness);
        });
    }

    public function resetForm(): void
    {
        $this->reset([
            'paymentId', 'editMode',
            'tenancy_agreement_id',
            'amount_paid', 'payment_method_id', 'reference_no', 'notes',
        ]);
        $this->payment_date = now()->toDateString();
        $this->payment_for_month = now()->startOfMonth()->toDateString();
        $this->receipt_channel = 'none';
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    /** The first of the roll month as a Carbon instance. */
    protected function rollMonthCarbon(): Carbon
    {
        return Carbon::parse(($this->rollMonth ?: now()->format('Y-m')) . '-01')->startOfMonth();
    }

    public function render()
    {
        $methods = $this->selectedBusiness
            ? payment_method::where('business_id', $this->selectedBusiness)->orderBy('name')->get()
            : collect();

        // Agreements for the modal's picker (record against any active/draft/expired agreement)
        $agreements = $this->selectedBusiness
            ? TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->whereIn('agreement_status', ['active', 'draft', 'expired'])
                ->with(['customer:id,name', 'unit:id,unit_number'])
                ->orderBy('start_date', 'desc')
                ->get()
            : collect();

        if (!$this->selectedBusiness) {
            $roll = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $stats = ['expected' => 0, 'collected' => 0, 'outstanding' => 0, 'unpaid_count' => 0];

            return view('livewire.owner.rental.rent-payments', [
                'roll' => $roll,
                'stats' => $stats,
                'businesses' => $this->ownerBusinesses,
                'agreements' => $agreements,
                'methods' => $methods,
                'rollMonthLabel' => $this->rollMonthCarbon()->format('M Y'),
            ]);
        }

        $month = $this->rollMonthCarbon();
        $monthKey = $month->format('Y-m');
        $monthEnd = $month->copy()->endOfMonth();

        // Agreements (active, draft, expired) that have started by the roll month and
        // whose end_date (if any) hasn't passed. Expired agreements without an end_date
        // are additionally capped at their last due month by isDueInMonth() below.
        $agreementQuery = TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
            ->whereIn('agreement_status', ['active', 'draft', 'expired'])
            ->whereDate('start_date', '<=', $monthEnd)
            ->where(function ($q) use ($month) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $month->toDateString());
            })
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->whereHas('customer', fn ($c) => $c->where('name', 'like', '%' . $this->search . '%'))
                       ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', '%' . $this->search . '%'));
                });
            })
            ->with([
                'customer:id,name,phone',
                'unit:id,unit_number,property_id',
                'unit.property:id,property_name',
            ]);

        // Pull matching agreements once, compute the roll row for each due agreement.
        // Active agreements per business are bounded, so in-memory roll-up is fine.
        $allRows = $agreementQuery->orderBy('start_date', 'desc')->get()
            ->filter(fn ($a) => $a->isDueInMonth($month))   // only bill months that fall due
            ->map(function ($a) use ($monthKey) {
                $due = (float) $a->rent_amount;
                $paid = (float) $a->rentPayments()->forMonth($monthKey)->sum('amount_paid');
                $remaining = max(0, $due - $paid);

                return [
                    'agreement' => $a,
                    'due' => $due,
                    'paid' => $paid,
                    'remaining' => $remaining,
                    'status' => $paid <= 0 ? 'unpaid' : ($remaining > 0 ? 'partial' : 'paid'),
                ];
            })
            ->values();

        // Stats over the full month roll (before the status filter narrows it)
        $expected = $allRows->sum('due');
        $collected = $allRows->sum(fn ($r) => min($r['paid'], $r['due']));
        $stats = [
            'expected' => $expected,
            'collected' => $collected,
            'outstanding' => max(0, $expected - $collected),
            'unpaid_count' => $allRows->filter(fn ($r) => $r['remaining'] > 0)->count(),
        ];

        // Apply the status filter and a stable sort (unpaid first, then by tenant)
        $rows = $allRows
            ->when($this->rollStatusFilter !== '', fn ($c) => $c->where('status', $this->rollStatusFilter))
            ->sortBy([
                fn ($r) => ['unpaid' => 0, 'partial' => 1, 'paid' => 2][$r['status']],
                fn ($r) => $r['agreement']->customer?->name ?? '',
            ])
            ->values();

        // Manual paginator over the computed rows
        $perPage = 15;
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $roll = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.owner.rental.rent-payments', [
            'roll' => $roll,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'agreements' => $agreements,
            'methods' => $methods,
            'rollMonthLabel' => $month->format('M Y'),
        ]);
    }
}
