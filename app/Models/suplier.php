<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class suplier extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'supliers';

    protected $fillable = [
        'business_id',
        'name',
        'contact_person',
        'address',
        'phone',
        'email',
        'tin_number',
        'vrn_number',
        'payment_terms',
        'credit_limit',
        'status',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(purchase::class, 'supplier_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Computed attributes
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()->count();
    }

    public function getTotalPurchaseValueAttribute()
    {
        return $this->purchases()
            ->received()
            ->selectRaw('SUM(quantity * price - COALESCE(discount, 0)) as total')
            ->value('total') ?? 0;
    }

    public function getUnpaidBalanceAttribute()
    {
        return $this->purchases()
            ->received()
            ->unpaid()
            ->selectRaw('SUM(quantity * price - COALESCE(discount, 0)) as total')
            ->value('total') ?? 0;
    }
}
