<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class sales extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'business_id',
        'outlet_id',
        'sale_status',
        'sale_type',
        'sale_date',
        'payment_date',
        'notes',
        'receipt_type',
        'payment_type',
        'customer_id',
        'user_id',
        'total_amount',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'payment_status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'sale_date' => 'datetime',
        'payment_date' => 'date',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function customer()
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(sales_item::class, 'sale_id');
    }

    public function payments()
    {
        return $this->hasMany(sales_payments::class, 'sale_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function newPayments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(OrderDiscount::class, 'discountable');
    }

    public function voidLogs(): MorphMany
    {
        return $this->morphMany(VoidLog::class, 'voidable');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('sale_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('sale_status', 'pending');
    }

    public function scopeCanceled($query)
    {
        return $query->where('sale_status', 'canceled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    /**
     * The sales list filters, shared by the screen and its printable report.
     * Always limited to $businessIds, so "all businesses" never means other tenants' sales.
     *
     * @param  array<int, string>|\Illuminate\Support\Collection<int, string>  $businessIds
     * @param  array{business?: ?string, sale_status?: ?string, payment_status?: ?string, from?: ?string, to?: ?string, search?: ?string}  $filters
     */
    public function scopeListFilters($query, $businessIds, array $filters)
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return $query
            ->whereIn('sales.business_id', $businessIds)
            ->when($filters['business'] ?? null, fn ($q, $business) => $q->where('sales.business_id', $business))
            ->when($filters['sale_status'] ?? null, fn ($q, $status) => $q->where('sales.sale_status', $status))
            ->when($filters['payment_status'] ?? null, fn ($q, $status) => $q->where('sales.payment_status', $status))
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('sales.sale_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('sales.sale_date', '<=', $to))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->whereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('items', fn ($iq) => $iq->where('plate_number', 'like', "%{$search}%"));
                });
            });
    }

    /**
     * Count, total, paid and unpaid for every sale matched by a (filtered) query, in one SQL query.
     *
     * @return array{count: int, total: float, paid: float, unpaid: float}
     */
    public static function totalsFor($query): array
    {
        $payments = sales_payments::selectRaw('sale_id, SUM(amount) as paid')->groupBy('sale_id');

        $row = (clone $query)
            ->reorder()
            ->leftJoinSub($payments, 'sale_paid', 'sale_paid.sale_id', '=', 'sales.id')
            ->selectRaw('COUNT(*) as sale_count')
            ->selectRaw('COALESCE(SUM(sales.total_amount), 0) as total_amount_sum')
            ->selectRaw('COALESCE(SUM(sale_paid.paid), 0) as paid_sum')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.total_amount > COALESCE(sale_paid.paid, 0) THEN sales.total_amount - COALESCE(sale_paid.paid, 0) ELSE 0 END), 0) as unpaid_sum')
            ->toBase()
            ->first();

        return [
            'count' => (int) ($row->sale_count ?? 0),
            'total' => (float) ($row->total_amount_sum ?? 0),
            'paid' => (float) ($row->paid_sum ?? 0),
            'unpaid' => (float) ($row->unpaid_sum ?? 0),
        ];
    }

    // Computed attributes
    public function getInvoiceNumberAttribute()
    {
        return 'INV-' . strtoupper(substr($this->id, 0, 8));
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getSaleStatusBadgeClassAttribute()
    {
        return match($this->sale_status) {
            'completed' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'unpaid' => 'danger',
            'partial' => 'warning',
            'pending' => 'warning',
            'refunded' => 'info',
            'canceled' => 'secondary',
            default => 'secondary',
        };
    }

    // Generate unique sale number
    public static function generateSaleNumber()
    {
        $prefix = 'SL';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$random}";
    }
}
