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

    // Filters
    public $search = '';
    public $agreementFilter = '';
    public $monthFilter = '';
    public $methodFilter = '';

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $paymentId = null;
    public $viewPayment = null;

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
        $this->payment_date = now()->toDateString();
        $this->payment_for_month = now()->startOfMonth()->toDateString();
    }

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingAgreementFilter(): void { $this->resetPage(); }
    public function updatingMonthFilter(): void { $this->resetPage(); }
    public function updatingMethodFilter(): void { $this->resetPage(); }

    public function updatedSelectedBusiness(): void
    {
        $this->reset(['agreementFilter']);
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
            $this->showViewModal = true;
        }
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

    public function render()
    {
        $agreements = $this->selectedBusiness
            ? TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->whereIn('agreement_status', ['active', 'draft', 'expired'])
                ->with(['customer:id,name', 'unit:id,unit_number'])
                ->orderBy('start_date', 'desc')
                ->get()
            : collect();

        $methods = $this->selectedBusiness
            ? payment_method::where('business_id', $this->selectedBusiness)->orderBy('name')->get()
            : collect();

        if (!$this->selectedBusiness) {
            $payments = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
            $stats = ['this_month' => 0, 'this_month_count' => 0, 'last_30_days' => 0, 'all_time' => 0, 'outstanding' => 0];
        } else {
            $payments = $this->scopedQuery()
                ->with([
                    'agreement.customer:id,name',
                    'agreement.unit:id,unit_number',
                    'paymentMethod:id,name',
                    'receivedBy:id,name',
                ])
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('reference_no', 'like', '%' . $this->search . '%')
                           ->orWhereHas('agreement.customer', fn ($c) => $c->where('name', 'like', '%' . $this->search . '%'));
                    });
                })
                ->when($this->agreementFilter, fn ($q) => $q->where('tenancy_agreement_id', $this->agreementFilter))
                ->when($this->monthFilter, fn ($q) => $q->forMonth($this->monthFilter))
                ->when($this->methodFilter, fn ($q) => $q->where('payment_method_id', $this->methodFilter))
                ->orderBy('payment_date', 'desc')
                ->paginate(15);

            $base = $this->scopedQuery();
            $thisMonth = now()->format('Y-m');

            // Outstanding = sum of active agreements' rent for the current month minus what's already paid
            $expectedThisMonth = TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->where('agreement_status', 'active')
                ->sum('rent_amount');
            $collectedThisMonth = (clone $base)->forMonth($thisMonth)->sum('amount_paid');

            $stats = [
                'this_month' => $collectedThisMonth,
                'this_month_count' => (clone $base)->forMonth($thisMonth)->count(),
                'last_30_days' => (clone $base)->where('payment_date', '>=', now()->subDays(30))->sum('amount_paid'),
                'all_time' => (clone $base)->sum('amount_paid'),
                'outstanding' => max(0, $expectedThisMonth - $collectedThisMonth),
            ];
        }

        return view('livewire.owner.rental.rent-payments', [
            'payments' => $payments,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'agreements' => $agreements,
            'methods' => $methods,
        ]);
    }
}
