<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarBottleService extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'tab_id',
        'order_id',
        'table_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'total',
        'assigned_staff',
        'mixers',
        'status',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'mixers' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function tab(): BelongsTo
    {
        return $this->belongsTo(BarTab::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'assigned_staff');
    }
}
