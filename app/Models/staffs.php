<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class staffs extends Model
{
    use HasUuids;
    protected $table = 'staffs';
    protected $guarded = [];

    public function carwashes()
    {
        return $this->belongsTo(carwashes::class);
    }
}
