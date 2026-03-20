<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PosTable extends Model
{
    use HasUuids;

    protected $fillable = [
        'outlet_id',
        'table_number',
        'capacity',
        'section',
        'status',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'table_id');
    }

    public function waiterAssignments(): HasMany
    {
        return $this->hasMany(WaiterAssignment::class, 'table_id');
    }

    public function barTabs(): HasMany
    {
        return $this->hasMany(BarTab::class, 'table_id');
    }

    public function tableReservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'table_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }
}
