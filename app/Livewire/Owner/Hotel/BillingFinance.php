<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Folio;
use App\Models\FolioCharge;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Models\HotelTaxConfig;
use App\Models\Reservation;

#[Layout('components.layouts.app-owner')]
class BillingFinance extends Component
{
    use WithPagination;

    public $activeTab = 'folios';
    public $search = '';
    public $selectedHotel = null;
    public $statusFilter = '';
    public $showModal = false;
    public $showChargeModal = false;
    public $showPaymentModal = false;
    public $selectedFolio = null;
    public $editMode = false;

    // Folio Charge Properties
    #[Rule('required|in:room,restaurant,bar,minibar,laundry,telephone,spa,other')]
    public $charge_type = 'other';

    #[Rule('required|string|max:500')]
    public $charge_description = '';

    #[Rule('required|numeric|min:0')]
    public $charge_amount = 0;

    #[Rule('nullable|date')]
    public $charge_date = '';

    // Payment Properties
    #[Rule('required|numeric|min:0')]
    public $payment_amount = 0;

    #[Rule('required|in:cash,card,bank_transfer,mobile_money')]
    public $payment_method = 'cash';

    #[Rule('nullable|string|max:500')]
    public $payment_reference = '';

    // Tax Config Properties
    public $taxId = null;
    #[Rule('required|string|max:100')]
    public $tax_name = '';

    #[Rule('required|numeric|min:0|max:100')]
    public $tax_rate = 0;

    #[Rule('required|in:active,inactive')]
    public $tax_status = 'active';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }

        if (request()->has('tab')) {
            $this->activeTab = request()->get('tab');
        }

        $this->charge_date = now()->format('Y-m-d');
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function viewFolio($id)
    {
        $this->selectedFolio = Folio::with(['charges', 'payments', 'guest', 'reservation'])->findOrFail($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showChargeModal = false;
        $this->showPaymentModal = false;
        $this->selectedFolio = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->charge_type = 'other';
        $this->charge_description = '';
        $this->charge_amount = 0;
        $this->charge_date = now()->format('Y-m-d');
        $this->payment_amount = 0;
        $this->payment_method = 'cash';
        $this->payment_reference = '';
        $this->resetValidation();
    }

    public function openChargeModal($folioId)
    {
        $this->selectedFolio = Folio::findOrFail($folioId);
        $this->showChargeModal = true;
    }

    public function addCharge()
    {
        $this->validate([
            'charge_type' => 'required|in:room,restaurant,bar,minibar,laundry,telephone,spa,other',
            'charge_description' => 'required|string|max:500',
            'charge_amount' => 'required|numeric|min:0',
            'charge_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            FolioCharge::create([
                'folio_id' => $this->selectedFolio->id,
                'charge_type' => $this->charge_type,
                'description' => $this->charge_description,
                'amount' => $this->charge_amount,
                'charge_date' => $this->charge_date ?? now(),
            ]);

            // Update folio totals
            $this->selectedFolio->update([
                'total_charges' => $this->selectedFolio->total_charges + $this->charge_amount,
                'balance' => $this->selectedFolio->balance + $this->charge_amount,
            ]);

            DB::commit();
            session()->flash('message', 'Charge added successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to add charge: ' . $e->getMessage());
        }
    }

    public function openPaymentModal($folioId)
    {
        $this->selectedFolio = Folio::findOrFail($folioId);
        $this->showPaymentModal = true;
    }

    public function recordPayment()
    {
        $this->validate([
            'payment_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,bank_transfer,mobile_money',
            'payment_reference' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            HotelPayment::create([
                'business_id' => $this->selectedHotel,
                'folio_id' => $this->selectedFolio->id,
                'amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'reference' => $this->payment_reference,
                'paid_at' => now(),
            ]);

            // Update folio totals
            $this->selectedFolio->update([
                'total_payments' => $this->selectedFolio->total_payments + $this->payment_amount,
                'balance' => $this->selectedFolio->balance - $this->payment_amount,
            ]);

            DB::commit();
            session()->flash('message', 'Payment recorded successfully.');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }

    public function openTaxModal()
    {
        $this->resetTaxForm();
        $this->showModal = true;
    }

    public function editTax($id)
    {
        $tax = HotelTaxConfig::findOrFail($id);
        $this->taxId = $tax->id;
        $this->tax_name = $tax->tax_name;
        $this->tax_rate = $tax->rate;
        $this->tax_status = $tax->status;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function saveTax()
    {
        $this->validate([
            'tax_name' => 'required|string|max:100',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_status' => 'required|in:active,inactive',
        ]);

        try {
            if ($this->editMode && $this->taxId) {
                HotelTaxConfig::findOrFail($this->taxId)->update([
                    'tax_name' => $this->tax_name,
                    'rate' => $this->tax_rate,
                    'status' => $this->tax_status,
                ]);
                session()->flash('message', 'Tax configuration updated successfully.');
            } else {
                HotelTaxConfig::create([
                    'business_id' => $this->selectedHotel,
                    'tax_name' => $this->tax_name,
                    'rate' => $this->tax_rate,
                    'status' => $this->tax_status,
                ]);
                session()->flash('message', 'Tax configuration created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function deleteTax($id)
    {
        try {
            HotelTaxConfig::findOrFail($id)->delete();
            session()->flash('message', 'Tax configuration deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    public function resetTaxForm()
    {
        $this->editMode = false;
        $this->taxId = null;
        $this->tax_name = '';
        $this->tax_rate = 0;
        $this->tax_status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $folios = collect();
        $invoices = collect();
        $payments = collect();
        $taxes = collect();
        $stats = [
            'open_folios' => 0,
            'total_balance' => 0,
            'payments_today' => 0,
            'revenue_today' => 0,
        ];

        if ($this->selectedHotel) {
            // Folios
            $folioQuery = Folio::where('business_id', $this->selectedHotel)
                ->with(['guest', 'reservation']);

            if ($this->search) {
                $folioQuery->where(function ($q) {
                    $q->where('folio_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('guest', function ($guestQuery) {
                          $guestQuery->where('first_name', 'like', '%' . $this->search . '%')
                                    ->orWhere('last_name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->statusFilter && $this->activeTab === 'folios') {
                $folioQuery->where('status', $this->statusFilter);
            }

            $folios = $folioQuery->latest()->paginate(15);

            // Invoices
            $invoices = HotelInvoice::where('business_id', $this->selectedHotel)
                ->with(['folio'])
                ->latest()
                ->paginate(15);

            // Payments
            $payments = HotelPayment::where('business_id', $this->selectedHotel)
                ->with(['folio'])
                ->latest()
                ->paginate(15);

            // Taxes
            $taxes = HotelTaxConfig::where('business_id', $this->selectedHotel)
                ->orderBy('name')
                ->get();

            // Statistics
            $stats['open_folios'] = Folio::where('business_id', $this->selectedHotel)
                ->where('status', 'open')->count();
            $stats['total_balance'] = Folio::where('business_id', $this->selectedHotel)
                ->where('status', 'open')->sum('balance');
            $stats['payments_today'] = HotelPayment::where('business_id', $this->selectedHotel)
                ->whereDate('paid_at', today())->sum('amount');
            $stats['revenue_today'] = FolioCharge::whereHas('folio', function($q) {
                    $q->where('business_id', $this->selectedHotel);
                })
                ->whereDate('posted_at', today())->sum('amount');
        }

        return view('livewire.owner.hotel.billing-finance', [
            'hotels' => $hotels,
            'folios' => $folios,
            'invoices' => $invoices,
            'payments' => $payments,
            'taxes' => $taxes,
            'stats' => $stats,
        ]);
    }
}
