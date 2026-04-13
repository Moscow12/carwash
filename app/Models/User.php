<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'role_id',
        'status',
        'avatar',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get businesses owned by this user
     */
    public function ownedBusinesses(): HasMany
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    /**
     * Get bookings made by this user (customer)
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    /**
     * Get customer profiles linked to this user
     */
    public function customerProfiles(): HasMany
    {
        return $this->hasMany(customers::class, 'user_id');
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user is a customer
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get business roles for this user
     */
    public function businessRoles(): HasMany
    {
        return $this->hasMany(UserBusinessRole::class);
    }

    /**
     * Get businesses this user is assigned to (for staff)
     */
    public function assignedBusinesses()
    {
        // If user is owner, return owned businesses
        if ($this->role === 'owner') {
            return $this->ownedBusinesses();
        }

        // If user is staff, return businesses from user_business_roles
        if ($this->role === 'staff') {
            return Business::whereIn('id', function($query) {
                $query->select('business_id')
                    ->from('user_business_roles')
                    ->where('user_id', $this->id)
                    ->where('is_active', true);
            });
        }

        // For admin or other roles, return all businesses
        return Business::query();
    }

    /**
     * Get the dynamic role assigned to this user
     */
    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get current business settings for the user
     */
    public function getCurrentBusinessSettings()
    {
        $business = $this->assignedBusinesses()->first();

        if (!$business) {
            return null;
        }

        return CarwashSetting::getForBusiness($business->id);
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        // If user has a dynamic role, check its permissions
        if ($this->role_id && $this->assignedRole) {
            return $this->assignedRole->hasPermission($permissionName);
        }

        // For system roles (admin, owner), grant all permissions
        if (in_array($this->role, ['admin', 'owner'])) {
            return true;
        }

        return false;
    }

    /**
     * Get staff profile linked to this user
     */
    public function staffProfile(): HasMany
    {
        return $this->hasMany(staffs::class);
    }

    /**
     * Get payments received by this user
     */
    public function paymentsReceived(): HasMany
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    /**
     * Get void logs created by this user
     */
    public function voidLogs(): HasMany
    {
        return $this->hasMany(VoidLog::class, 'voided_by');
    }
}
