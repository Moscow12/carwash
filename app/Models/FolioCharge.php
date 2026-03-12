<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class FolioCharge extends Model
{
    use HasUuids;

    protected $fillable = [
        'folio_id',
        'pos_order_id',
        'charge_type',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'tax_amount',
        'posted_by',
        'posted_at',
        'is_voided',
        'void_reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'posted_at' => 'datetime',
        'is_voided' => 'boolean',
    ];

    // Relationships
    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    // Scopes
    public function scopeNotVoided(Builder $query): Builder
    {
        return $query->where('is_voided', false);
    }
}
