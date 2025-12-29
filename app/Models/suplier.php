<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class suplier extends Model
{
    use HasUuids;

    protected $table = 'supliers';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'status',
    ];

    // Relationships
    public function purchases()
    {
        return $this->hasMany(purchase::class, 'supplier_id');
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
