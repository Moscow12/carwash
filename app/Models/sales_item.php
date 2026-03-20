<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class sales_item extends Model
{
    use HasUuids;

    protected $table = 'sales_items';

    protected $fillable = [
        'sale_id',
        'item_id',
        'staff_id',
        'date',
        'plate_number',
        'price',
        'quantity',
        'discount',
        'tax_amount',
        'total',
        'commission',
        'is_voided',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'commission' => 'decimal:2',
        'quantity' => 'decimal:3',
        'date' => 'datetime',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
    ];

    // Relationships
    public function sale(): BelongsTo
    {
        return $this->belongsTo(sales::class, 'sale_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'staff_id');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function voidLogs(): MorphMany
    {
        return $this->morphMany(VoidLog::class, 'voidable');
    }

    // Computed attributes
    public function getSubtotalAttribute()
    {
        $quantity = $this->quantity ?? 1;
        return ($this->price * $quantity) - ($this->discount ?? 0);
    }

    public function getLineTotalAttribute()
    {
        return $this->subtotal;
    }
}
