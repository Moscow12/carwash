<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class stocktaking extends Model
{
    use HasUuids;

    protected $table = 'stocktakings';

    protected $fillable = [
        'item_id',
        'user_id',
        'carwash_id',
        'quantity',
        'price',
        'stocktaking_status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    // Scopes
    public function scopeReceived($query)
    {
        return $query->where('stocktaking_status', 'received');
    }

    public function scopePending($query)
    {
        return $query->where('stocktaking_status', 'pending');
    }

    public function scopeCanceled($query)
    {
        return $query->where('stocktaking_status', 'canceled');
    }

    public function scopeForCarwash($query, $carwashId)
    {
        return $query->where('carwash_id', $carwashId);
    }

    // Helpers
    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->stocktaking_status) {
            'received' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            default => 'secondary',
        };
    }
}
