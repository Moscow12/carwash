<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class items extends Model
{
    use HasUuids;
    protected $table = 'items';
    protected $guarded = [];

    public function carwash()
    {
        return $this->belongsTo(carwashes::class);
    }

    public function unit()
    {
        return $this->belongsTo(unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(suplier::class);
    }

    public function stocktaking()
    {
        return $this->hasMany(stocktaking::class);
    }

    public function purchase()
    {
        return $this->hasMany(purchase::class);
    }

    public function item_balance()
    {
        return $this->hasMany(item_balance::class);
    }
}
