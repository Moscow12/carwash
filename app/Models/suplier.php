<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class suplier extends Model
{
    use HasUuids;
    protected $table = 'supliers';
    protected $guarded = [];
}
