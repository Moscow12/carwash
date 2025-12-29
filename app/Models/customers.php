<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class customers extends Model
{
    use HasUuids;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'status',
        'carwash_id',
        'user_id',
    ];

    // Relationships
    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(sales::class, 'customer_id');
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

    public function scopeForCarwash($query, $carwashId)
    {
        return $query->where('carwash_id', $carwashId);
    }

    // Computed attributes
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->sales()->sum('total_amount');
    }

    public function getSalesCountAttribute()
    {
        return $this->sales()->count();
    }
}
