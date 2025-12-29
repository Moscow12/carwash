<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class expense_category extends Model
{
    use HasUuids;

    protected $table = 'expense_categories';

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'carwash_id',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function parent()
    {
        return $this->belongsTo(expense_category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(expense_category::class, 'parent_id');
    }

    public function expenses()
    {
        return $this->hasMany(expenses::class, 'category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeParentCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSubCategories($query)
    {
        return $query->whereNotNull('parent_id');
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        if ($this->parent_id) {
            return '--' . $this->name;
        }
        return $this->name;
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getIsSubcategoryAttribute()
    {
        return !is_null($this->parent_id);
    }
}
