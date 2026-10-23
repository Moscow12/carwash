<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Stock extends Model
{
    /** @use HasFactory<\Database\Factories\StockFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'business_id',
        'reference_no',
        'stock_date',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'stock_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockItem::class, 'stock_id');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Build a unique reference using the business stocktaking prefix.
     */
    public static function generateReferenceNo(string $businessId): string
    {
        $prefix = CarwashSetting::where('business_id', $businessId)->value('stocktaking_prefix') ?: 'ST';

        do {
            $reference = $prefix.'-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (self::where('reference_no', $reference)->exists());

        return $reference;
    }
}
