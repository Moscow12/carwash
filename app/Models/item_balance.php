<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class item_balance extends Model
{
    use HasUuids;

    protected $table = 'item_balances';

    protected $fillable = [
        'item_id',
        'user_id',
        'business_id',
        'previous_balance',
        'current_balance',
        'quantity_changed',
        'stock_type',
        'stransaction_type',
        'invoice_number',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'quantity_changed' => 'decimal:2',
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

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    // Scopes
    public function scopeStockIn($query)
    {
        return $query->where('stock_type', 'in');
    }

    public function scopeStockOut($query)
    {
        return $query->where('stock_type', 'out');
    }

    public function scopeForItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Helpers
    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$random}";
    }

    public function getChangeAmountAttribute()
    {
        return $this->current_balance - $this->previous_balance;
    }

    public function getTransactionTypeLabelAttribute()
    {
        return match($this->stransaction_type) {
            'initial_stock' => 'Initial Stock',
            'restock' => 'Restock',
            'sale' => 'Sale',
            'adjustment' => 'Adjustment',
            'refund' => 'Refund',
            'return' => 'Return',
            'purchase' => 'Purchase',
            default => ucfirst($this->stransaction_type),
        };
    }

    public function getStockTypeBadgeClassAttribute()
    {
        return $this->stock_type === 'in' ? 'success' : 'danger';
    }
}
