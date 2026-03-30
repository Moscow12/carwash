<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class staffs extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'user_id',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function salesItems(): HasMany
    {
        return $this->hasMany(sales_item::class, 'staff_id');
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'staff_id');
    }

    public function waiterAssignments(): HasMany
    {
        return $this->hasMany(WaiterAssignment::class, 'waiter_id');
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
