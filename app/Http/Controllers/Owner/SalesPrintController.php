<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SalesPrintRequest;
use App\Models\Business;
use App\Models\sales;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Printable documents for the sales list: they open in a new tab, print on A4 and can be saved as PDF.
 */
class SalesPrintController extends Controller
{
    public function list(SalesPrintRequest $request): View
    {
        $businessIds = Auth::user()->assignedBusinesses()->pluck('id');
        $filters = $request->validated();

        $filtered = sales::query()->listFilters($businessIds, $filters);

        $sales = (clone $filtered)
            ->with(['customer:id,name,phone', 'payments:id,sale_id,amount'])
            ->withCount('items')
            ->latest('sale_date')
            ->get();

        $business = ! empty($filters['business']) && $businessIds->contains($filters['business'])
            ? Business::with('settings')->find($filters['business'])
            : null;

        return view('owner.sales.print.list', [
            'sales' => $sales,
            'totals' => sales::totalsFor($filtered),
            'filters' => $filters,
            'business' => $business,
        ]);
    }

    public function invoice(string $sale): View
    {
        $sale = sales::whereIn('business_id', Auth::user()->assignedBusinesses()->pluck('id'))
            ->with(['customer', 'user', 'items.item', 'items.staff', 'payments.paymentMethod', 'business.settings'])
            ->findOrFail($sale);

        $paid = (float) $sale->payments->sum('amount');

        return view('owner.sales.print.invoice', [
            'sale' => $sale,
            'paid' => $paid,
            'balance' => max(0, (float) $sale->total_amount - $paid),
        ]);
    }
}
