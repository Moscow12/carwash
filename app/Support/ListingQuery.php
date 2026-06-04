<?php

namespace App\Support;

use App\Models\items;
use App\Models\MenuItem;
use App\Models\Module;
use App\Models\RentalUnit;
use App\Models\RoomType;
use Illuminate\Support\Collection;

/**
 * Builds the public marketplace listing feed across the heterogeneous listing
 * types (shop items, rental units, hotel rooms, bar menu items).
 *
 * Returns normalized card arrays (see each model's toListingCard()) for
 * listings that are: is_published = true, on an active business, whose business
 * has the matching module active.
 */
class ListingQuery
{
    /** Listing type keys, used by the `listingType` filter. */
    public const TYPES = [
        'item' => 'Shop',
        'service' => 'Services',
        'rental' => 'Rental',
        'room' => 'Hotel Rooms',
        'menu' => 'Menu',
    ];

    /**
     * @param  array<string,mixed>  $filters  keys: search, businessType, listingType, category
     * @param  string|null  $businessId  scope to a single business (storefront)
     */
    public static function published(array $filters = [], ?string $businessId = null): Collection
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $businessType = $filters['businessType'] ?? '';
        $listingType = $filters['listingType'] ?? '';   // item|service|rental|room|menu
        $category = $filters['category'] ?? '';

        $cards = collect();

        // item + service share the items table; the service flag is derived in the card.
        $wantsItems = $listingType === '' || $listingType === 'item' || $listingType === 'service';
        $wantsRental = $listingType === '' || $listingType === 'rental';
        $wantsRoom = $listingType === '' || $listingType === 'room';
        $wantsMenu = $listingType === '' || $listingType === 'menu';

        // A category filter only makes sense for items/menu (rentals/rooms have none).
        if ($category !== '') {
            $wantsRental = false;
            $wantsRoom = false;
        }

        // ─── Shop items / services ───────────────────────────────
        if ($wantsItems) {
            $rows = items::query()
                ->where('is_published', true)
                ->where('status', 'active')
                ->whereHas('business', fn ($q) => $q->where('status', 'active')
                    ->when($businessType !== '', fn ($qq) => $qq->where('type', $businessType)))
                ->with(['business.modules', 'category'])
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->when($category !== '', fn ($q) => $q->where('category_id', $category))
                ->when($listingType === 'service', fn ($q) => $q->where('type', 'Service'))
                ->when($listingType === 'item', fn ($q) => $q->where('type', '!=', 'Service'))
                ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
                ->latest()
                ->get();
            $cards = $cards->concat($rows->map->toListingCard());
        }

        // ─── Rental units ────────────────────────────────────────
        if ($wantsRental) {
            $rows = RentalUnit::query()
                ->where('is_published', true)
                ->whereHas('property.landlord.business', fn ($q) => $q->where('status', 'active')
                    ->when($businessType !== '', fn ($qq) => $qq->where('type', $businessType))
                    ->when($businessId, fn ($qq) => $qq->where('id', $businessId)))
                ->with(['property.landlord.business.modules', 'images'])
                ->when($search !== '', fn ($q) => $q->where('unit_number', 'like', "%{$search}%"))
                ->latest()
                ->get();
            $cards = $cards->concat($rows->map->toListingCard());
        }

        // ─── Hotel room types ────────────────────────────────────
        if ($wantsRoom) {
            $rows = RoomType::query()
                ->where('is_published', true)
                ->where('status', 'active')
                ->whereHas('business', fn ($q) => $q->where('status', 'active')
                    ->when($businessType !== '', fn ($qq) => $qq->where('type', $businessType)))
                ->with(['business.modules'])
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
                ->latest()
                ->get();
            $cards = $cards->concat($rows->map->toListingCard());
        }

        // ─── Bar menu items ──────────────────────────────────────
        if ($wantsMenu) {
            $rows = MenuItem::query()
                ->where('is_published', true)
                ->where('status', 'active')
                ->whereHas('outlet.business', fn ($q) => $q->where('status', 'active')
                    ->when($businessType !== '', fn ($qq) => $qq->where('type', $businessType))
                    ->when($businessId, fn ($qq) => $qq->where('id', $businessId)))
                ->with(['outlet.business.modules'])
                ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                ->when($category !== '', fn ($q) => $q->where('category_id', $category))
                ->latest()
                ->get();
            $cards = $cards->concat($rows->map->toListingCard());
        }

        // ─── Module gate + null-business guard ───────────────────
        return $cards
            ->filter(function ($card) {
                $business = $card['business'] ?? null;
                if (!$business) {
                    return false; // orphaned listing (missing business chain)
                }
                return $business->hasModule(Module::keyForBusinessType($business->type));
            })
            ->sortByDesc(fn ($card) => $card['created_at'])
            ->values();
    }
}
