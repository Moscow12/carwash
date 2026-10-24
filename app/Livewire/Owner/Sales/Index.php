<?php

namespace App\Livewire\Owner\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\sales;
use App\Models\sales_payments;
use App\Models\payment_method;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = '';
    public $saleStatusFilter = '';
    public $paymentStatusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 25;

    // View sale details
    public $showDetailsModal = false;
    public $selectedSale = null;
    public $selectedSaleItems = [];
    public $selectedSalePayments = [];
    public $selectedSalePaidAmount = 0;
    public $selectedSaleUnpaidAmount = 0;

    // Add Payment Modal
    public $showPaymentModal = false;
    public $paymentSaleId = '';
    public $paymentRows = [];
    public $availablePaymentMethods = [];

    // Summary stats
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $paidSales = 0;
    public $unpaidSales = 0;
    public $partialSalesCount = 0;
    public $unpaidSalesCount = 0;

    // Receipt Modal
    public $showReceiptModal = false;
    public $receiptSale = null;
    public $receiptSaleItems = [];
    public $receiptSalePayments = [];
    public $receiptBusinessInfo = null;
    public $receiptBusinessSettings = null;

    public function mount()
    {
        $firstBusiness = Auth::user()->assignedBusinesses()->orderBy('name')->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
        }
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedBusiness()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedSaleStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Businesses the signed-in user may see sales for.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function businessIds(): \Illuminate\Support\Collection
    {
        return Auth::user()->assignedBusinesses()->pluck('id');
    }

    /**
     * Sales of the user's own businesses; every action looks sales up through this.
     */
    protected function ownedSales(): \Illuminate\Database\Eloquent\Builder
    {
        return sales::whereIn('business_id', $this->businessIds());
    }

    /**
     * Current filters, in the shape sales::scopeListFilters() and the print route expect.
     *
     * @return array{business: ?string, sale_status: ?string, payment_status: ?string, from: ?string, to: ?string, search: ?string}
     */
    public function listFilters(): array
    {
        return [
            'business' => $this->selectedBusiness ?: null,
            'sale_status' => $this->saleStatusFilter ?: null,
            'payment_status' => $this->paymentStatusFilter ?: null,
            'from' => $this->dateFrom ?: null,
            'to' => $this->dateTo ?: null,
            'search' => $this->search ?: null,
        ];
    }

    public function loadStats()
    {
        $businessIds = $this->businessIds();

        // Use single optimized query to get all stats
        $stats = sales::whereIn('business_id', $businessIds)
            ->when($this->selectedBusiness, fn ($q) => $q->where('business_id', $this->selectedBusiness))
            ->where('sale_status', '!=', 'canceled')
            ->when($this->dateFrom, fn($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(total_amount) as total_revenue,
                SUM(CASE WHEN payment_status = "partial" THEN 1 ELSE 0 END) as partial_count,
                SUM(CASE WHEN payment_status = "unpaid" THEN 1 ELSE 0 END) as unpaid_count
            ')
            ->first();

        $this->totalSales = $stats->total_count ?? 0;
        $this->totalRevenue = (float) ($stats->total_revenue ?? 0);
        $this->partialSalesCount = $stats->partial_count ?? 0;
        $this->unpaidSalesCount = $stats->unpaid_count ?? 0;

        // Calculate actual paid amount from payments table
        $this->paidSales = (float) sales_payments::whereHas('sale', function($query) use ($businessIds) {
            $query->whereIn('business_id', $businessIds)
                  ->when($this->selectedBusiness, fn ($q) => $q->where('business_id', $this->selectedBusiness))
                  ->where('sale_status', '!=', 'canceled')
                  ->when($this->dateFrom, fn($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
                  ->when($this->dateTo, fn($q) => $q->whereDate('sale_date', '<=', $this->dateTo));
        })->sum('amount');

        $this->unpaidSales = max(0, $this->totalRevenue - $this->paidSales);
    }

    public function viewSale($id)
    {
        $sale = $this->ownedSales()->with(['customer', 'user', 'items.item', 'payments.paymentMethod'])->find($id);
        if ($sale) {
            $this->selectedSale = $sale->toArray();
            $this->selectedSaleItems = $sale->items->toArray();
            $this->selectedSalePayments = $sale->payments->toArray();

            // Calculate paid and unpaid amounts
            $this->selectedSalePaidAmount = (float) $sale->payments->sum('amount');
            $this->selectedSaleUnpaidAmount = max(0, (float) $sale->total_amount - $this->selectedSalePaidAmount);

            $this->showDetailsModal = true;
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
        $this->selectedSaleItems = [];
        $this->selectedSalePayments = [];
        $this->selectedSalePaidAmount = 0;
        $this->selectedSaleUnpaidAmount = 0;
    }

    // Add Payment Modal Methods
    public function openAddPaymentModal($saleId)
    {
        $sale = $this->ownedSales()->find($saleId);
        if (!$sale) return;

        $this->paymentSaleId = $saleId;

        // Load payment methods for this business
        $this->availablePaymentMethods = payment_method::where('business_id', $sale->business_id)
            ->where('status', 'active')
            ->select('id', 'name', 'business_id')
            ->get()
            ->toArray();

        // Calculate unpaid amount
        $paidAmount = (float) sales_payments::where('sale_id', $saleId)->sum('amount');
        $unpaidAmount = max(0, (float) $sale->total_amount - $paidAmount);

        // Initialize payment rows with unpaid amount
        $defaultMethodId = $this->availablePaymentMethods[0]['id'] ?? '';
        $this->paymentRows = [
            [
                'amount' => $unpaidAmount,
                'payment_method_id' => $defaultMethodId,
                'note' => '',
            ]
        ];

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentSaleId = '';
        $this->paymentRows = [];
    }

    public function addPaymentRow()
    {
        $defaultMethodId = $this->availablePaymentMethods[0]['id'] ?? '';
        $this->paymentRows[] = [
            'amount' => 0,
            'payment_method_id' => $defaultMethodId,
            'note' => '',
        ];
    }

    public function removePaymentRow($index)
    {
        if (count($this->paymentRows) > 1) {
            unset($this->paymentRows[$index]);
            $this->paymentRows = array_values($this->paymentRows);
        }
    }

    public function getPaymentTotalProperty()
    {
        return collect($this->paymentRows)->sum('amount');
    }

    public function processPayment()
    {
        $sale = $this->ownedSales()->find($this->paymentSaleId);
        if (!$sale) {
            session()->flash('error', 'Sale not found.');
            return;
        }

        // Validate payment rows
        $hasValidPayment = false;
        foreach ($this->paymentRows as $row) {
            if (!empty($row['payment_method_id']) && (float)$row['amount'] > 0) {
                $hasValidPayment = true;
                break;
            }
        }

        if (!$hasValidPayment) {
            session()->flash('error', 'Please add at least one valid payment.');
            return;
        }

        DB::beginTransaction();
        try {
            // Create payment records
            foreach ($this->paymentRows as $paymentRow) {
                $amount = (float) ($paymentRow['amount'] ?? 0);
                $methodId = $paymentRow['payment_method_id'] ?? '';

                if ($amount > 0 && $methodId) {
                    sales_payments::create([
                        'sale_id' => $sale->id,
                        'user_id' => Auth::id(),
                        'amount' => $amount,
                        'payment_date' => now(),
                        'payment_method_id' => $methodId,
                        'notes' => $paymentRow['note'] ?? null,
                    ]);
                }
            }

            // Calculate new total paid
            $totalPaid = (float) sales_payments::where('sale_id', $sale->id)->sum('amount');
            $totalAmount = (float) $sale->total_amount;

            // Update payment status
            if ($totalPaid >= $totalAmount) {
                $sale->update([
                    'payment_status' => 'paid',
                    'payment_date' => now(),
                ]);
            } elseif ($totalPaid > 0) {
                $sale->update([
                    'payment_status' => 'partial',
                ]);
            }

            DB::commit();

            $this->closePaymentModal();
            $this->loadStats();

            // Refresh the details modal if it's open
            if ($this->showDetailsModal && $this->selectedSale && $this->selectedSale['id'] === $sale->id) {
                $this->viewSale($sale->id);
            }

            session()->flash('message', 'Payment added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function markAsPaid($id)
    {
        $sale = $this->ownedSales()->find($id);
        if ($sale) {
            $sale->update(['payment_status' => 'paid', 'payment_date' => now()]);
            session()->flash('message', 'Sale marked as paid.');
            $this->loadStats();
        }
    }

    public function cancelSale($id)
    {
        $sale = $this->ownedSales()->find($id);
        if ($sale && $sale->sale_status !== 'canceled') {
            $sale->update(['sale_status' => 'canceled']);
            session()->flash('message', 'Sale canceled successfully.');
            $this->loadStats();
        }
    }

    public function showReceipt($saleId)
    {
        $sale = $this->ownedSales()->with(['customer', 'user', 'items.item', 'payments.paymentMethod', 'business.settings'])
            ->find($saleId);
        if (!$sale) return;

        $this->receiptSale = $sale->toArray();
        $this->receiptSaleItems = $sale->items->toArray();
        $this->receiptSalePayments = $sale->payments->toArray();
        $this->receiptBusinessInfo = $sale->business ? $sale->business->toArray() : null;
        $this->receiptBusinessSettings = $sale->business && $sale->business->settings ? $sale->business->settings->toArray() : null;
        $this->showReceiptModal = true;
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->receiptSale = null;
        $this->receiptSaleItems = [];
        $this->receiptSalePayments = [];
        $this->receiptBusinessInfo = null;
        $this->receiptBusinessSettings = null;
    }

    public function render()
    {
        $this->loadStats();

        $businesses = Auth::user()->assignedBusinesses()
            ->select('businesses.id', 'businesses.name')
            ->orderBy('name')
            ->get();

        $filtered = sales::query()->listFilters($this->businessIds(), $this->listFilters());

        $query = (clone $filtered)
            ->select([
                'id', 'business_id', 'sale_date', 'customer_id', 'user_id',
                'total_amount', 'payment_status', 'sale_status', 'created_at'
            ])
            ->withCount('items')
            ->with([
                'customer:id,name,phone',
                'user:id,name',
                'items' => function($query) {
                    $query->select('id', 'sale_id', 'item_id', 'price', 'plate_number', 'staff_id', 'discount', 'commission')
                          ->limit(3);
                },
                'items.item:id,name',
                'payments' => function($query) {
                    $query->select('id', 'sale_id', 'amount', 'payment_method_id');
                }
            ])
            ->latest('sale_date');

        // Handle "all" option
        if ($this->perPage === 'all') {
            $sales = $query->paginate($query->count() ?: 1);
        } else {
            $sales = $query->paginate((int) $this->perPage);
        }

        $pageTotals = [
            'total' => (float) $sales->sum('total_amount'),
            'paid' => (float) $sales->sum(fn ($sale) => $sale->payments->sum('amount')),
            'unpaid' => (float) $sales->sum(fn ($sale) => max(0, (float) $sale->total_amount - (float) $sale->payments->sum('amount'))),
        ];

        return view('livewire.owner.sales.index', [
            'sales' => $sales,
            'businesses' => $businesses,
            'pageTotals' => $pageTotals,
            'grandTotals' => sales::totalsFor($filtered),
        ]);
    }
}
