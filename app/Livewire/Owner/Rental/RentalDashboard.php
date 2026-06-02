<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\RentalMaintenanceRequest;
use App\Models\RentalUnit;
use App\Models\RentPayment;
use App\Models\TenancyAgreement;
use App\Models\UtilityBill;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class RentalDashboard extends Component
{
    public ?string $selectedBusiness = null;
    public $ownerBusinesses = [];

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();

        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
    }

    public function render()
    {
        if (!$this->selectedBusiness) {
            return view('livewire.owner.rental.rental-dashboard', [
                'kpis' => $this->emptyKpis(),
                'cashflow' => $this->emptyCashflow(),
                'occupancy' => ['vacant' => 0, 'occupied' => 0, 'maintenance' => 0, 'reserved' => 0, 'total' => 0],
                'alerts' => $this->emptyAlerts(),
                'recentPayments' => collect(),
                'openTickets' => collect(),
                'topProperties' => collect(),
                'businesses' => $this->ownerBusinesses,
            ]);
        }

        $bizId = $this->selectedBusiness;
        $monthStart = now()->startOfMonth();
        $thisMonth = now()->format('Y-m');

        // ─── Base scoped queries ─────────────────────────────────
        $unitsQ = RentalUnit::whereHas('property.landlord', fn ($q) => $q->where('business_id', $bizId));
        $agreementsQ = TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $bizId));
        $paymentsQ = RentPayment::whereHas('agreement.landlord', fn ($q) => $q->where('business_id', $bizId));
        $billsQ = UtilityBill::whereHas('agreement.landlord', fn ($q) => $q->where('business_id', $bizId));
        $ticketsQ = RentalMaintenanceRequest::whereHas('agreement.landlord', fn ($q) => $q->where('business_id', $bizId));

        // ─── KPIs ─────────────────────────────────────────────────
        $totalUnits = (clone $unitsQ)->count();
        $occupiedUnits = (clone $unitsQ)->where('status', 'occupied')->count();
        $monthlyRevenue = (clone $agreementsQ)->where('agreement_status', 'active')->sum('rent_amount');

        $kpis = [
            'properties' => Property::whereHas('landlord', fn ($q) => $q->where('business_id', $bizId))->count(),
            'units' => $totalUnits,
            'occupancy_pct' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100) : 0,
            'monthly_revenue' => $monthlyRevenue,
            'landlords' => Landlord::where('business_id', $bizId)->count(),
            'active_agreements' => (clone $agreementsQ)->where('agreement_status', 'active')->count(),
        ];

        // ─── Cashflow this month ─────────────────────────────────
        $rentCollectedThisMonth = (clone $paymentsQ)->forMonth($thisMonth)->sum('amount_paid');
        $utilCollectedThisMonth = (clone $billsQ)->where('billing_month', $monthStart->toDateString())->where('status', 'paid')->sum('amount');
        $utilIssuedThisMonth = (clone $billsQ)->where('billing_month', $monthStart->toDateString())->sum('amount');
        $expectedRentThisMonth = $monthlyRevenue;
        $outstandingRent = max(0, $expectedRentThisMonth - $rentCollectedThisMonth);
        $outstandingUtils = (clone $billsQ)->whereIn('status', ['unpaid', 'partial'])->sum('amount');

        $cashflow = [
            'rent_collected' => $rentCollectedThisMonth,
            'rent_expected' => $expectedRentThisMonth,
            'rent_collection_pct' => $expectedRentThisMonth > 0 ? round(($rentCollectedThisMonth / $expectedRentThisMonth) * 100) : 0,
            'util_collected' => $utilCollectedThisMonth,
            'util_issued' => $utilIssuedThisMonth,
            'outstanding_rent' => $outstandingRent,
            'outstanding_utils' => $outstandingUtils,
        ];

        // ─── Occupancy breakdown ─────────────────────────────────
        $statusCounts = (clone $unitsQ)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $occupancy = [
            'vacant' => (int) ($statusCounts['vacant'] ?? 0),
            'occupied' => (int) ($statusCounts['occupied'] ?? 0),
            'maintenance' => (int) ($statusCounts['maintenance'] ?? 0),
            'reserved' => (int) ($statusCounts['reserved'] ?? 0),
            'total' => $totalUnits,
        ];

        // ─── Alerts ─────────────────────────────────────────────
        // Active agreements that have NO rent payment recorded for the current month
        $overdueAgreements = (clone $agreementsQ)
            ->where('agreement_status', 'active')
            ->whereDoesntHave('rentPayments', function ($q) use ($monthStart) {
                $q->where('payment_for_month', $monthStart->toDateString());
            })
            ->count();

        $alerts = [
            'overdue_rent' => $overdueAgreements,
            'unpaid_bills' => (clone $billsQ)->whereIn('status', ['unpaid', 'partial'])->count(),
            'open_tickets' => (clone $ticketsQ)->whereIn('status', ['open', 'in_progress'])->count(),
            'units_in_maintenance' => $occupancy['maintenance'],
        ];

        // ─── Recent payments (last 5) ────────────────────────────
        $recentPayments = (clone $paymentsQ)
            ->with(['agreement.customer:id,name', 'agreement.unit:id,unit_number'])
            ->latest('payment_date')
            ->limit(5)
            ->get();

        // ─── Open maintenance tickets (top 5) ────────────────────
        $openTickets = (clone $ticketsQ)
            ->with(['agreement.customer:id,name', 'agreement.unit:id,unit_number', 'assignee:id,name'])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest()
            ->limit(5)
            ->get();

        // ─── Top properties by unit count ────────────────────────
        $topProperties = Property::whereHas('landlord', fn ($q) => $q->where('business_id', $bizId))
            ->withCount(['units', 'units as occupied_count' => fn ($q) => $q->where('status', 'occupied')])
            ->orderBy('units_count', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.owner.rental.rental-dashboard', [
            'kpis' => $kpis,
            'cashflow' => $cashflow,
            'occupancy' => $occupancy,
            'alerts' => $alerts,
            'recentPayments' => $recentPayments,
            'openTickets' => $openTickets,
            'topProperties' => $topProperties,
            'businesses' => $this->ownerBusinesses,
        ]);
    }

    private function emptyKpis(): array
    {
        return ['properties' => 0, 'units' => 0, 'occupancy_pct' => 0, 'monthly_revenue' => 0, 'landlords' => 0, 'active_agreements' => 0];
    }

    private function emptyCashflow(): array
    {
        return ['rent_collected' => 0, 'rent_expected' => 0, 'rent_collection_pct' => 0, 'util_collected' => 0, 'util_issued' => 0, 'outstanding_rent' => 0, 'outstanding_utils' => 0];
    }

    private function emptyAlerts(): array
    {
        return ['overdue_rent' => 0, 'unpaid_bills' => 0, 'open_tickets' => 0, 'units_in_maintenance' => 0];
    }
}
