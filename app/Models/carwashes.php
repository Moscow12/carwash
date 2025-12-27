<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class carwashes extends Model
{
    use HasUuids;
    protected $table = 'carwashes';
    protected $guarded = [];

    public function regions()
    {
        return $this->belongsTo(regions::class);
    }

    public function districts()
    {
        return $this->belongsTo(districts::class);
    }

    public function wards()
    {
        return $this->belongsTo(wards::class);
    }

    public function streets()
    {
        return $this->belongsTo(street::class);
    }
}
