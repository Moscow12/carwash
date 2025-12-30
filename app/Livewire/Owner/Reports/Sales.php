<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\carwashes;
use App\Models\sales as SalesModel;
use App\Models\sales_item;
use App\Models\items;
use App\Models\category;
use App\Models\customers;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Sales extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    #[Url]
    public $carwash_id = '';

    #[Url]
    public $search = '';

    #[Url]
    public $customer_id = '';

    #[Url]
    public $category_id = '';

    #[Url]
    public $start_date = '';

    #[Url]
    public $end_date = '';

    #[Url]
    public $start_time = '00:00';

    #[Url]
    public $end_time = '23:59';

    #[Url]
    public $payment_method = '';

    #[Url]
    public $activeTab = 'detailed';

    public $perPage = 25;
    public $showFilters = true;

    // Data
    public $carwashes = [];
    public $categories = [];
    public $customersList = [];

    // Summary
    public $totalQuantity = 0;
    public $totalDiscount = 0;
    public $totalTax = 0;
    public $totalAmount = 0;

    public function mount()
    {
        $owner = Auth::user();
        $carwashCollection = $owner->ownedCarwashes()->get();

        $this->carwashes = $carwashCollection->map(function ($carwash) {
            return [
                'id' => $carwash->id,
                'name' => $carwash->name,
            ];
        })->toArray();

        if (count($this->carwashes) > 0 && empty($this->carwash_id)) {
            $this->carwash_id = $this->carwashes[0]['id'];
        }

        // Set default date range to current year
        if (empty($this->start_date)) {
            $this->start_date = Carbon::now()->startOfYear()->format('Y-m-d');
        }
        if (empty($this->end_date)) {
            $this->end_date = Carbon::now()->endOfYear()->format('Y-m-d');
        }

        $this->loadFilterData();
    }

    public function updatedCarwashId()
    {
        $this->loadFilterData();
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCustomerId()
    {
        $this->resetPage();
    }

    public function updatedCategoryId()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function updatedPaymentMethod()
    {
        $this->resetPage();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->customer_id = '';
        $this->category_id = '';
        $this->payment_method = '';
        $this->start_date = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->end_date = Carbon::now()->endOfYear()->format('Y-m-d');
        $this->start_time = '00:00';
        $this->end_time = '23:59';
        $this->resetPage();
    }

    protected function loadFilterData()
    {
        if (empty($this->carwash_id)) {
            return;
        }

        // Load categories
        $this->categories = category::where('carwash_id', $this->carwash_id)
            ->where('status', 'active')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();

        // Load customers
        $this->customersList = customers::where('carwash_id', $this->carwash_id)
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();
    }

    protected function getDateTimeRange()
    {
        $startDateTime = Carbon::parse($this->start_date . ' ' . $this->start_time);
        $endDateTime = Carbon::parse($this->end_date . ' ' . $this->end_time);

        return [$startDateTime, $endDateTime];
    }

    protected function getBaseQuery()
    {
        [$startDateTime, $endDateTime] = $this->getDateTimeRange();

        $query = sales_item::query()
            ->select(
                'sales_items.*',
                'sales.sale_date',
                'sales.customer_id',
                'sales.payment_type',
                'sales.id as sale_id',
                'items.name as product_name',
                'items.barcode as sku',
                'items.category_id',
                'customers.name as customer_name'
            )
            ->join('sales', 'sales_items.sale_id', '=', 'sales.id')
            ->join('items', 'sales_items.item_id', '=', 'items.id')
            ->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.carwash_id', $this->carwash_id)
            ->where('sales.sale_status', 'completed')
            ->whereBetween('sales.sale_date', [$startDateTime, $endDateTime]);

        // Apply filters
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('items.name', 'like', '%' . $this->search . '%')
                    ->orWhere('items.barcode', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->customer_id)) {
            $query->where('sales.customer_id', $this->customer_id);
        }

        if (!empty($this->category_id)) {
            $query->where('items.category_id', $this->category_id);
        }

        if (!empty($this->payment_method)) {
            $query->where('sales.payment_type', $this->payment_method);
        }

        return $query;
    }

    public function getDetailedDataProperty()
    {
        return $this->getBaseQuery()
            ->orderBy('sales.sale_date', 'desc')
            ->paginate($this->perPage);
    }

    public function getGroupedDataProperty()
    {
        return $this->getBaseQuery()
            ->select(
                'items.id as item_id',
                'items.name as product_name',
                'items.barcode as sku',
                DB::raw('SUM(sales_items.quantity) as total_quantity'),
                DB::raw('AVG(sales_items.price) as avg_price'),
                DB::raw('SUM(COALESCE(sales_items.discount, 0)) as total_discount'),
                DB::raw('SUM((sales_items.price * sales_items.quantity) - COALESCE(sales_items.discount, 0)) as total_amount')
            )
            ->groupBy('items.id', 'items.name', 'items.barcode')
            ->orderBy('total_amount', 'desc')
            ->paginate($this->perPage);
    }

    public function getByCategoryDataProperty()
    {
        return $this->getBaseQuery()
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(sales_items.quantity) as total_quantity'),
                DB::raw('SUM(COALESCE(sales_items.discount, 0)) as total_discount'),
                DB::raw('SUM((sales_items.price * sales_items.quantity) - COALESCE(sales_items.discount, 0)) as total_amount')
            )
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_amount', 'desc')
            ->paginate($this->perPage);
    }

    public function getSummaryProperty()
    {
        $query = $this->getBaseQuery();

        return [
            'total_quantity' => (float) $query->sum('sales_items.quantity'),
            'total_discount' => (float) $query->sum('sales_items.discount'),
            'total_amount' => (float) $query->sum(DB::raw('(sales_items.price * sales_items.quantity) - COALESCE(sales_items.discount, 0)')),
        ];
    }

    public function generateInvoiceNumber($saleId)
    {
        return 'INV-' . strtoupper(substr($saleId, 0, 8));
    }

    public function render()
    {
        return view('livewire.owner.reports.sales', [
            'detailedData' => $this->activeTab === 'detailed' ? $this->detailedData : null,
            'groupedData' => $this->activeTab === 'grouped' ? $this->groupedData : null,
            'byCategoryData' => $this->activeTab === 'category' ? $this->byCategoryData : null,
            'summary' => $this->summary,
        ]);
    }
}
