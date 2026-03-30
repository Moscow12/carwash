<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BarTab extends Model
{
    use HasUuids;

    protected $fillable = [
        'tab_no',
        'business_id',
        'outlet_id',
        'session_id',
        'table_id',
        'customer_id',
        'guest_id',
        'folio_id',
        'tab_name',
        'status',
        'total_amount',
        'paid_amount',
        'balance',
        'opened_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(customers::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(PosOrder::class, 'bar_tab_orders', 'tab_id', 'order_id')
                    ->withTimestamps();
    }
}
