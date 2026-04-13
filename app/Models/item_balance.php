<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class item_balance extends Model
{
    use HasUuids;

    protected $table = 'item_balances';

    protected $fillable = [
        'item_id',
        'user_id',
        'business_id',
        'outlet_id',
        'order_id',
        'previous_balance',
        'current_balance',
        'quantity_changed',
        'quantity_ml',
        'stock_type',
        'stransaction_type',
        'movement_reason',
        'invoice_number',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:3',
        'current_balance' => 'decimal:3',
        'quantity_changed' => 'decimal:3',
        'quantity_ml' => 'decimal:2',
    ];

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
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
