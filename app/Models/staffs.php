<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class staffs extends Model
{
    use HasUuids;

    protected $table = 'staffs';

    protected $fillable = [
        'name',
        'position',
        'phone',
        'email',
        'payment_mode',
        'commission_type',
        'amount',
        'status',
        'business_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function salesItems()
    {
        return $this->hasMany(sales_item::class, 'staff_id');
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

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Computed attributes
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getPaymentModeDisplayAttribute()
    {
        return match($this->payment_mode) {
            'salary' => 'Monthly Salary',
            'hourly' => 'Hourly Rate',
            'commission' => 'Commission Based',
            default => '-',
        };
    }

    public function getCommissionDisplayAttribute()
    {
        if (!$this->amount) return '-';

        if ($this->commission_type === 'percentage') {
            return $this->amount . '%';
        }

        return 'TZS ' . number_format($this->amount);
    }

    // Get total commission earned (for reports)
    public function getTotalCommissionAttribute()
    {
        return $this->salesItems()->sum('commission');
    }

    // Get sales count
    public function getSalesCountAttribute()
    {
        return $this->salesItems()->count();
    }
}
