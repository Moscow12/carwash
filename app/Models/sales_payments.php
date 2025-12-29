<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class sales_payments extends Model
{
    use HasUuids;

    protected $table = 'sales_payments';

    protected $fillable = [
        'sale_id',
        'user_id',
        'amount',
        'payment_date',
        'notes',
        'receipt_type',
        'payment_method_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(sales::class, 'sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(payment_method::class, 'payment_method_id');
    }
}
