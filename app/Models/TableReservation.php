<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableReservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'reservation_no',
        'customer_id',
        'guest_name',
        'guest_phone',
        'table_id',
        'section',
        'covers',
        'reservation_date',
        'reservation_time',
        'duration_mins',
        'occasion',
        'deposit_amount',
        'deposit_paid',
        'status',
        'pos_order_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'deposit_amount' => 'decimal:2',
        'deposit_paid' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(customers::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class);
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
