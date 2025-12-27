<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class customers extends Model
{
    use HasUuids;
    protected $table = 'customers';
    protected $guarded = [];

    public function carwashes()
    {
        return $this->belongsTo(carwashes::class);
    }

    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
