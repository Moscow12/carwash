<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\carwashes;
use App\Models\sales;
use App\Models\sales_item;
use App\Models\purchase;
use App\Models\expenses;
use App\Models\items;
use App\Models\category;
use App\Models\customers;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Profitandloss extends Component
{
    #[Url]
    public $carwash_id = '';

    #[Url]
    public $date_filter = 'this_year';

    #[Url]
    public $start_date = '';

    #[Url]
    public $end_date = '';

    public $carwashes = [];
    public $activeTab = 'products';

    // Report data
    public $openingStockPurchase = 0;
    public $openingStockSale = 0;
    public $closingStockPurchase = 0;
    public $closingStockSale = 0;
    public $totalPurchase = 0;
    public $totalStockAdjustment = 0;
    public $totalExpense = 0;
    public $totalPurchaseShipping = 0;
    public $purchaseAdditionalExpenses = 0;
    public $totalTransferShipping = 0;
    public $totalSellDiscount = 0;
    public $totalCustomerReward = 0;
    public $totalSellReturn = 0;
    public $totalSales = 0;
    public $totalSellShipping = 0;
    public $sellAdditionalExpenses = 0;
    public $totalStockRecovered = 0;
    public $totalPurchaseReturn = 0;
    public $totalPurchaseDiscount = 0;
    public $totalSellRoundOff = 0;
    public $grossProfit = 0;
    public $netProfit = 0;

    // Breakdown data
    public $profitByProducts = [];
    public $profitByCategories = [];
    public $profitByCustomers = [];
    public $profitByDate = [];
    public $profitByDay = [];

    public function mount()
    {
        $owner = Auth::user();
        $carwashCollection = $owner->ownedCarwashes()->get();

        // Convert to array for Livewire serialization
        $this->carwashes = $carwashCollection->map(function ($carwash) {
            return [
                'id' => $carwash->id,
                'name' => $carwash->name,
            ];
        })->toArray();

        if (count($this->carwashes) > 0 && empty($this->carwash_id)) {
            $this->carwash_id = $this->carwashes[0]['id'];
        }

        $this->calculateReport();
    }

    public function updatedCarwashId()
    {
        $this->calculateReport();
    }

    public function updatedDateFilter()
    {
        if ($this->date_filter !== 'custom') {
            $this->start_date = '';
            $this->end_date = '';
        }
        $this->calculateReport();
    }

    public function updatedStartDate()
    {
        if ($this->date_filter === 'custom') {
            $this->calculateReport();
        }
    }

    public function updatedEndDate()
    {
        if ($this->date_filter === 'custom') {
            $this->calculateReport();
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    protected function getDateRange()
    {
        $now = Carbon::now();

        return match ($this->date_filter) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'last_7_days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            'last_30_days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_month_last_year' => [$now->copy()->subYear()->startOfMonth(), $now->copy()->subYear()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'current_fy' => $this->getCurrentFinancialYear(),
            'last_fy' => $this->getLastFinancialYear(),
            'custom' => [
                $this->start_date ? Carbon::parse($this->start_date)->startOfDay() : $now->copy()->startOfYear(),
                $this->end_date ? Carbon::parse($this->end_date)->endOfDay() : $now->copy()->endOfDay()
            ],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }

    protected function getCurrentFinancialYear()
    {
        $now = Carbon::now();
        $fyStart = $now->month >= 7
            ? Carbon::create($now->year, 7, 1)
            : Carbon::create($now->year - 1, 7, 1);
        $fyEnd = $fyStart->copy()->addYear()->subDay();

        return [$fyStart, $fyEnd];
    }

    protected function getLastFinancialYear()
    {
        [$currentStart, $currentEnd] = $this->getCurrentFinancialYear();
        return [$currentStart->copy()->subYear(), $currentEnd->copy()->subYear()];
    }

    public function calculateReport()
    {
        if (empty($this->carwash_id)) {
            return;
        }

        try {
            [$startDate, $endDate] = $this->getDateRange();

        // Calculate Total Sales (excluding tax, discount already applied)
        $this->totalSales = (float) sales_item::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->where('carwash_id', $this->carwash_id)
                ->where('sale_status', 'completed')
                ->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum(DB::raw('(price * quantity) - COALESCE(discount, 0)'));

        // Calculate Total Sell Discount
        $this->totalSellDiscount = (float) sales_item::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->where('carwash_id', $this->carwash_id)
                ->where('sale_status', 'completed')
                ->whereBetween('sale_date', [$startDate, $endDate]);
        })->sum('discount');

        // Calculate Total Purchase (excluding tax, discount)
        $this->totalPurchase = (float) purchase::where('carwash_id', $this->carwash_id)
            ->where('purchase_status', 'received')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum(DB::raw('(price * quantity) - COALESCE(discount, 0)'));

        // Calculate Total Purchase Discount
        $this->totalPurchaseDiscount = (float) purchase::where('carwash_id', $this->carwash_id)
            ->where('purchase_status', 'received')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('discount');

        // Calculate Total Expenses
        $this->totalExpense = (float) expenses::where('carwash_id', $this->carwash_id)
            ->where('status', 'active')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Calculate Stock Values
        $this->calculateStockValues($startDate, $endDate);

        // Calculate Gross Profit (Total Sales - Total Purchase Cost of items sold)
        $cogs = (float) $this->getCostOfGoodsSold($startDate, $endDate);
        $this->grossProfit = (float) $this->totalSales - $cogs;

        // Calculate Net Profit
        // Net = Gross Profit + (Sell shipping + Sell additional + Stock recovered + Purchase discount + Sell round off)
        //     - (Stock Adjustment + Expense + Purchase shipping + Purchase additional + Transfer shipping + Sell discount + Customer reward)
        $additions = (float) $this->totalSellShipping + (float) $this->sellAdditionalExpenses + (float) $this->totalStockRecovered +
                     (float) $this->totalPurchaseDiscount + (float) $this->totalSellRoundOff;

        $deductions = (float) $this->totalStockAdjustment + (float) $this->totalExpense + (float) $this->totalPurchaseShipping +
                      (float) $this->purchaseAdditionalExpenses + (float) $this->totalTransferShipping + (float) $this->totalSellDiscount +
                      (float) $this->totalCustomerReward;

        $this->netProfit = (float) $this->grossProfit + $additions - $deductions;

        // Calculate breakdowns
        $this->calculateProfitByProducts($startDate, $endDate);
        $this->calculateProfitByCategories($startDate, $endDate);
        $this->calculateProfitByCustomers($startDate, $endDate);
        $this->calculateProfitByDate($startDate, $endDate);
        $this->calculateProfitByDay($startDate, $endDate);

        } catch (\Throwable $e) {
            // Log error and reset values to prevent infinite loading
            \Log::error('Profit and Loss Report Error: ' . $e->getMessage() . ' at line ' . $e->getLine());
            $this->resetReportValues();
        }
    }

    protected function resetReportValues()
    {
        $this->openingStockPurchase = 0;
        $this->openingStockSale = 0;
        $this->closingStockPurchase = 0;
        $this->closingStockSale = 0;
        $this->totalPurchase = 0;
        $this->totalStockAdjustment = 0;
        $this->totalExpense = 0;
        $this->totalSales = 0;
        $this->totalSellDiscount = 0;
        $this->totalPurchaseDiscount = 0;
        $this->grossProfit = 0;
        $this->netProfit = 0;
        $this->profitByProducts = [];
        $this->profitByCategories = [];
        $this->profitByCustomers = [];
        $this->profitByDate = [];
        $this->profitByDay = [];
    }

    protected function calculateStockValues($startDate, $endDate)
    {
        // Get all items for this carwash
        $items = items::where('carwash_id', $this->carwash_id)->get();

        $openingPurchase = 0;
        $openingSale = 0;
        $closingPurchase = 0;
        $closingSale = 0;

        foreach ($items as $item) {
            $currentStock = (float) ($item->product_stock ?? 0);
            $costPrice = (float) ($item->cost_price ?? 0);
            $sellingPrice = (float) ($item->selling_price ?? 0);

            // Closing stock values
            $closingPurchase += $currentStock * $costPrice;
            $closingSale += $currentStock * $sellingPrice;

            // Opening stock - calculate by reversing transactions in the period
            $stockSold = (float) sales_item::whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->where('carwash_id', $this->carwash_id)
                    ->where('sale_status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })->where('item_id', $item->id)->sum('quantity');

            $stockPurchased = (float) purchase::where('carwash_id', $this->carwash_id)
                ->where('item_id', $item->id)
                ->where('purchase_status', 'received')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('quantity');

            $openingStock = $currentStock + $stockSold - $stockPurchased;
            $openingPurchase += $openingStock * $costPrice;
            $openingSale += $openingStock * $sellingPrice;
        }

        $this->openingStockPurchase = max(0, $openingPurchase);
        $this->openingStockSale = max(0, $openingSale);
        $this->closingStockPurchase = max(0, $closingPurchase);
        $this->closingStockSale = max(0, $closingSale);
    }

    protected function getCostOfGoodsSold($startDate, $endDate)
    {
        // COGS = Sum of (quantity sold * cost price) for each item
        return sales_item::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->where('carwash_id', $this->carwash_id)
                ->where('sale_status', 'completed')
                ->whereBetween('sale_date', [$startDate, $endDate]);
        })->with('item')->get()->sum(function ($saleItem) {
            $costPrice = (float) ($saleItem->item->cost_price ?? 0);
            $quantity = (float) ($saleItem->quantity ?? 0);
            return $quantity * $costPrice;
        });
    }

    protected function calculateProfitByProducts($startDate, $endDate)
    {
        $this->profitByProducts = sales_item::select(
                'item_id',
                DB::raw('SUM((price * quantity) - COALESCE(discount, 0)) as total_sales'),
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->where('carwash_id', $this->carwash_id)
                    ->where('sale_status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->groupBy('item_id')
            ->with('item')
            ->get()
            ->map(function ($record) {
                $costPrice = (float) ($record->item->cost_price ?? 0);
                $totalQuantity = (float) ($record->total_quantity ?? 0);
                $totalSales = (float) ($record->total_sales ?? 0);
                $cogs = $totalQuantity * $costPrice;
                return [
                    'name' => $record->item->name ?? 'Unknown',
                    'code' => $record->item->barcode ?? '-',
                    'total_sales' => $totalSales,
                    'gross_profit' => $totalSales - $cogs,
                ];
            })
            ->sortByDesc('gross_profit')
            ->values()
            ->toArray();
    }

    protected function calculateProfitByCategories($startDate, $endDate)
    {
        $this->profitByCategories = sales_item::select(
                'items.category_id',
                DB::raw('SUM((sales_items.price * sales_items.quantity) - COALESCE(sales_items.discount, 0)) as total_sales'),
                DB::raw('SUM(sales_items.quantity) as total_quantity')
            )
            ->join('items', 'sales_items.item_id', '=', 'items.id')
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->where('carwash_id', $this->carwash_id)
                    ->where('sale_status', 'completed')
                    ->whereBetween('sale_date', [$startDate, $endDate]);
            })
            ->groupBy('items.category_id')
            ->get()
            ->map(function ($record) {
                $category = category::find($record->category_id);
                $categoryItems = items::where('category_id', $record->category_id)->get();
                $avgCostPrice = (float) ($categoryItems->avg('cost_price') ?? 0);
                $totalQuantity = (float) ($record->total_quantity ?? 0);
                $totalSales = (float) ($record->total_sales ?? 0);
                $cogs = $totalQuantity * $avgCostPrice;

                return [
                    'name' => $category->name ?? 'Uncategorized',
                    'total_sales' => $totalSales,
                    'gross_profit' => $totalSales - $cogs,
                ];
            })
            ->sortByDesc('gross_profit')
            ->values()
            ->toArray();
    }

    protected function calculateProfitByCustomers($startDate, $endDate)
    {
        $this->profitByCustomers = sales::select(
                'customer_id',
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('carwash_id', $this->carwash_id)
            ->where('sale_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->whereNotNull('customer_id')
            ->groupBy('customer_id')
            ->with('customer')
            ->get()
            ->map(function ($record) use ($startDate, $endDate) {
                $cogs = (float) sales_item::whereHas('sale', function ($query) use ($record, $startDate, $endDate) {
                    $query->where('carwash_id', $this->carwash_id)
                        ->where('customer_id', $record->customer_id)
                        ->where('sale_status', 'completed')
                        ->whereBetween('sale_date', [$startDate, $endDate]);
                })->with('item')->get()->sum(function ($saleItem) {
                    return (float) ($saleItem->quantity ?? 0) * (float) ($saleItem->item->cost_price ?? 0);
                });

                $totalSales = (float) ($record->total_sales ?? 0);

                return [
                    'name' => $record->customer->name ?? 'Walk-in Customer',
                    'total_sales' => $totalSales,
                    'gross_profit' => $totalSales - $cogs,
                ];
            })
            ->sortByDesc('gross_profit')
            ->values()
            ->toArray();
    }

    protected function calculateProfitByDate($startDate, $endDate)
    {
        $this->profitByDate = sales::select(
                DB::raw('DATE(sale_date) as sale_date'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('carwash_id', $this->carwash_id)
            ->where('sale_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('sale_date', 'desc')
            ->get()
            ->map(function ($record) {
                $date = Carbon::parse($record->sale_date);
                $cogs = (float) sales_item::whereHas('sale', function ($query) use ($date) {
                    $query->where('carwash_id', $this->carwash_id)
                        ->where('sale_status', 'completed')
                        ->whereDate('sale_date', $date);
                })->with('item')->get()->sum(function ($saleItem) {
                    return (float) ($saleItem->quantity ?? 0) * (float) ($saleItem->item->cost_price ?? 0);
                });

                $totalSales = (float) ($record->total_sales ?? 0);

                return [
                    'date' => $date->format('Y-m-d'),
                    'formatted_date' => $date->format('M d, Y'),
                    'total_sales' => $totalSales,
                    'gross_profit' => $totalSales - $cogs,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function calculateProfitByDay($startDate, $endDate)
    {
        $this->profitByDay = sales::select(
                DB::raw('DAYNAME(sale_date) as day_name'),
                DB::raw('DAYOFWEEK(sale_date) as day_number'),
                DB::raw('SUM(total_amount) as total_sales')
            )
            ->where('carwash_id', $this->carwash_id)
            ->where('sale_status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy(DB::raw('DAYNAME(sale_date)'), DB::raw('DAYOFWEEK(sale_date)'))
            ->orderBy('day_number')
            ->get()
            ->map(function ($record) use ($startDate, $endDate) {
                // Calculate COGS for this day of week
                $cogs = (float) sales_item::whereHas('sale', function ($query) use ($record, $startDate, $endDate) {
                    $query->where('carwash_id', $this->carwash_id)
                        ->where('sale_status', 'completed')
                        ->whereBetween('sale_date', [$startDate, $endDate])
                        ->whereRaw('DAYOFWEEK(sale_date) = ?', [$record->day_number]);
                })->with('item')->get()->sum(function ($saleItem) {
                    return (float) ($saleItem->quantity ?? 0) * (float) ($saleItem->item->cost_price ?? 0);
                });

                $totalSales = (float) ($record->total_sales ?? 0);

                return [
                    'day' => $record->day_name,
                    'total_sales' => $totalSales,
                    'gross_profit' => $totalSales - $cogs,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getDateFilterLabelProperty()
    {
        return match ($this->date_filter) {
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'last_7_days' => 'Last 7 Days',
            'last_30_days' => 'Last 30 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'this_month_last_year' => 'This month last year',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
            'current_fy' => 'Current financial year',
            'last_fy' => 'Last financial year',
            'custom' => 'Custom Range',
            default => 'This Year',
        };
    }

    public function render()
    {
        return view('livewire.owner.reports.profitandloss');
    }
}
