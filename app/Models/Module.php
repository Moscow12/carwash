<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    use HasUuids;

    protected $table = 'modules';

    protected $fillable = [
        'key',
        'name',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_modules')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * The module key that a business of the given type should be granted.
     * Every type maps to exactly one module; unknown/other defaults to pos.
     */
    public static function keyForBusinessType(?string $type): string
    {
        return match ($type) {
            'rental' => 'rental',
            'hotel' => 'hotel',
            'restaurant' => 'restaurant',
            'bar' => 'bar',
            'carwash', 'car_wash' => 'carwash',
            // pos, shop, salon, spa, gym, cafe, other, null → POS
            default => 'pos',
        };
    }
}
