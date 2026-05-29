<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RentalMaintenanceRequest extends Model
{
    use HasUuids;

    protected $table = 'rental_maintenance_requests';

    protected $fillable = [
        'tenancy_agreement_id',
        'maintenance_type',
        'description',
        'start_date',
        'end_date',
        'cost',
        'status',
        'assigned_to',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(TenancyAgreement::class, 'tenancy_agreement_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'assigned_to');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }
}
