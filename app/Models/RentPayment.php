<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;

class RentPayment extends Model
{
    use HasUuids;

    protected $table = 'rent_payments';

    protected $fillable = [
        'tenancy_agreement_id',
        'payment_date',
        'amount_paid',
        'payment_method_id',
        'reference_no',
        'payment_for_month',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_for_month' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(payment_method::class, 'payment_method_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeForMonth(Builder $query, string $yearMonth): Builder
    {
        return $query->whereRaw("DATE_FORMAT(payment_for_month, '%Y-%m') = ?", [$yearMonth]);
    }

    /**
     * Mirror record in the unified `payments` ledger.
     * Uses the literal string 'rent_payment' to match the convention used by
     * other modules (sale, pos_order, hotel_invoice, folio, bar_tab).
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
