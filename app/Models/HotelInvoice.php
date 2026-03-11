<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class HotelInvoice extends Model
{
    use HasUuids;

    protected $fillable = [
        'invoice_no',
        'carwash_id',
        'folio_id',
        'pos_order_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount',
        'tax_total',
        'grand_total',
        'status',
        'notes',
        'pdf_url',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelPayment::class, 'invoice_id');
    }

    // Scopes
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'issued']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'issued')
            ->where('due_date', '<', today());
    }
}
