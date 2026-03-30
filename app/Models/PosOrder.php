<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrder extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_no',
        'business_id',
        'outlet_id',
        'session_id',
        'table_id',
        'reservation_id',
        'guest_id',
        'customer_id',
        'order_type',
        'covers',
        'subtotal',
        'discount_amount',
        'discount_id',
        'tax_amount',
        'service_charge',
        'total',
        'status',
        'notes',
        'served_by',
        'closed_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'session_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(PosTable::class, 'table_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }

    public function servedBy(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'served_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PosOrderItem::class, 'order_id');
    }

    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class, 'order_id');
    }

    public function folioCharges(): HasMany
    {
        return $this->hasMany(FolioCharge::class, 'pos_order_id');
    }

    public function hotelPayments(): HasMany
    {
        return $this->hasMany(HotelPayment::class, 'pos_order_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(OrderDiscount::class, 'discount_id');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(OrderDiscount::class, 'discountable');
    }

    public function voidLogs(): MorphMany
    {
        return $this->morphMany(VoidLog::class, 'voidable');
    }

    public function barTabs(): BelongsToMany
    {
        return $this->belongsToMany(BarTab::class, 'bar_tab_orders', 'order_id', 'tab_id')
                    ->withTimestamps();
    }

    public function tableReservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'pos_order_id');
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'sent_to_kitchen', 'ready', 'served']);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }
}
