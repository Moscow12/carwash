<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'staff_id',
        'shift_date',
        'shift_type',
        'start_time',
        'end_time',
        'status',
        'actual_start',
        'actual_end',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(staffs::class);
    }
}
