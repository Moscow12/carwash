<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class sales extends Model
{
    use HasUuids;
    protected $table = 'sales';
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(items::class);
    }
    
}
