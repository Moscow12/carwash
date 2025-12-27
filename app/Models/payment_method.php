<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class payment_method extends Model
{
    use HasUuids;
    protected $table = 'payment_methods';
    protected $guarded = [];

    public function carwashes()
    {
        return $this->belongsTo(carwashes::class);
    }
}
