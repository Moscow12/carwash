<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class purchase extends Model
{
    use HasUuids;
    protected $table = 'purchases';
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(items::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(suplier::class);
    }

    public function carwash()
    {
        return $this->belongsTo(carwashes::class);
    }
}
