<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
        'discount',
        'commission',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'commission' => 'decimal:2',
        'quantity' => 'integer',
        'date' => 'datetime',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(sales::class, 'sale_id');
    }

    public function item()
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function staff()
    {
        return $this->belongsTo(staffs::class, 'staff_id');
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
