<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiterAssignment extends Model
{
    use HasUuids;

    protected $fillable = [
        'session_id',
        'outlet_id',
        'table_id',
        'staff_id',
        'assigned_at',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(staffs::class);
    }
}
