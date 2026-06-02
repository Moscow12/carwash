<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Builder;

class UtilityBill extends Model
{
    use HasUuids;

    protected $table = 'utility_bills';

    protected $fillable = [
        'tenancy_agreement_id',
        'bill_type',
        'billing_month',
        'amount',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'billing_month' => 'date',
        'paid_at' => 'date',
        'amount' => 'decimal:2',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id');
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partial']);
    }

    /**
     * Mirror record in the unified payments ledger (alias registered in AppServiceProvider).
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }
}
