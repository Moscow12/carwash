<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class unit extends Model
{
    use HasUuids;

    protected $table = 'units';

    protected $fillable = [
        'name',
        'symbol',
        'description',
        'status',
    ];

    /**
     * Get items that use this unit
     */
    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'unit_id');
    }

    /**
     * Check if unit is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
