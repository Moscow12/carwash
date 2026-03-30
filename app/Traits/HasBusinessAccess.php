<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasBusinessAccess
{
    /**
     * Get businesses accessible by current user
     * - Owners see their owned businesses
     * - Staff see only assigned businesses from user_business_roles
     */
    public function getAccessibleBusinesses()
    {
        return Auth::user()->assignedBusinesses();
    }

    /**
     * Get business IDs accessible by current user
     */
    public function getAccessibleBusinessIds(): array
    {
        return $this->getAccessibleBusinesses()->pluck('id')->toArray();
    }

    /**
     * Check if current user has access to a specific business
     */
    public function hasAccessToBusiness(string $businessId): bool
    {
        return in_array($businessId, $this->getAccessibleBusinessIds());
    }

    /**
     * Get the first accessible business for current user
     */
    public function getFirstAccessibleBusiness()
    {
        return $this->getAccessibleBusinesses()->first();
    }
}
