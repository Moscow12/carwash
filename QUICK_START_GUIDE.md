# 🚀 Quick Start Guide - Database Updates

## ✅ What's Already Done

- ✅ **5 migration files created & executed**
- ✅ **82 total models** (25 new + 57 existing)
- ✅ **28 new tables** in database
- ✅ **58 new columns** added to existing tables
- ✅ All migrations successfully ran (Batch 14)

---

## 📋 35-Task Todo List Breakdown

### ✅ COMPLETED (4 tasks)
1. Created 5 migration files (ALTER + 4 CREATE)
2. Ran all 5 migrations successfully
3. Created 25 new Eloquent models
4. Updated 4 critical models (items, sales, User, PosOrder)

### ⏳ HIGH PRIORITY - Model Updates (11 tasks)
**Estimated Time: 2-3 hours**

5. Update MenuItem model with recipes & tax relationships
6. Update Guest model with new hotel relationships
7. Update Reservation model with ReservationGuest
8. Update purchase model with PurchaseItem
9. Update staffs model with user_id & relationships
10. Update item_balance model with tracking fields
11. Update PosOutlet model with outlet relationships
12. Update expenses model with outlet_id
13. Update suplier model with business_id
14. Add SoftDeletes to 11 remaining models
15. Update remaining 16 models with relationships

### 🌱 MEDIUM PRIORITY - Seeders (2 tasks)
**Estimated Time: 1 hour**

16. Create tax_rates seeder
17. Create promotions seeder

### 🧪 MEDIUM PRIORITY - Testing (1 task)
**Estimated Time: 2-4 hours**

18. Test basic CRUD on all new tables

### 🎮 LOW PRIORITY - Controllers (7 tasks)
**Estimated Time: 5-7 days**

19. Update payment controllers
20. Update discount controllers
21. Create Bar management controllers
22. Create Restaurant reservation controllers
23. Create Hotel service controllers (amenity/laundry)
24. Create PurchaseOrder controllers
25. Create StockTransfer controllers

### 🎨 LOW PRIORITY - Frontend (3 tasks)
**Estimated Time: 3-5 days**

26. Create bar management views
27. Create restaurant reservation views
28. Create hotel service views

### 🔒 LOW PRIORITY - Security (2 tasks)
**Estimated Time: 1-2 days**

29. Add validation rules for new forms
30. Add permissions/policies for features

### 📦 LOW PRIORITY - API (1 task)
**Estimated Time: 1 day**

31. Create API resources for models

### 🧪 LOW PRIORITY - Testing (2 tasks)
**Estimated Time: 2-3 days**

32. Write unit tests for models
33. Write feature tests for controllers

### 📚 LOW PRIORITY - Documentation (2 tasks)
**Estimated Time: 1 day**

34. Document API endpoints (Postman/Swagger)
35. Create user documentation

---

## 🎯 TODAY'S ACTION PLAN (Start Here!)

### Step 1: Complete Model Updates (2-3 hours) ⏰

Open these files and add the specified code:

#### A. MenuItem.php
```php
// Add to fillable array:
'tax_rate_id', 'printer_station',

// Add these relationships:
public function taxRate(): BelongsTo
{
    return $this->belongsTo(TaxRate::class);
}

public function recipes(): HasMany
{
    return $this->hasMany(MenuItemRecipe::class);
}
```

#### B. Guest.php
```php
// Add SoftDeletes trait
use Illuminate\Database\Eloquent\SoftDeletes;
class Guest extends Model
{
    use HasUuids, SoftDeletes;

// Add relationships:
public function amenityRequests(): HasMany
{
    return $this->hasMany(HotelAmenityRequest::class);
}

public function wakeupCalls(): HasMany
{
    return $this->hasMany(WakeupCall::class);
}

public function laundryOrders(): HasMany
{
    return $this->hasMany(LaundryOrder::class);
}
```

#### C. Reservation.php
```php
// Add to fillable:
'number_of_rooms',

// Add SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;
class Reservation extends Model
{
    use HasUuids, SoftDeletes;

// Add relationship:
public function guests(): HasMany
{
    return $this->hasMany(ReservationGuest::class);
}
```

#### D. purchase.php
```php
// Add to fillable:
'reference_no', 'received_date', 'subtotal', 'tax_amount', 'total_amount', 'outlet_id',

// Add SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;
class purchase extends Model
{
    use HasUuids, SoftDeletes;

// Add relationships:
public function items(): HasMany
{
    return $this->hasMany(PurchaseItem::class);
}

public function outlet(): BelongsTo
{
    return $this->belongsTo(PosOutlet::class);
}
```

#### E. staffs.php
```php
// Add to fillable:
'user_id',

// Add SoftDeletes
use Illuminate\Database\Eloquent\SoftDeletes;
class staffs extends Model
{
    use HasUuids, SoftDeletes;

// Add relationships:
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function shiftSchedules(): HasMany
{
    return $this->hasMany(ShiftSchedule::class, 'staff_id');
}
```

---

### Step 2: Create Tax Rates Seeder (30 mins) ⏰

```bash
# Create seeder
php artisan make:seeder TaxRateSeeder
```

**Edit:** `database/seeders/TaxRateSeeder.php`
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TaxRate;
use App\Models\Business;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        $businesses = Business::all();

        foreach ($businesses as $business) {
            TaxRate::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'business_id' => $business->id,
                'name' => 'VAT 18%',
                'code' => 'VAT',
                'rate' => 0.1800,
                'is_inclusive' => false,
                'applies_to' => 'all',
                'is_default' => true,
                'status' => 'active',
            ]);
        }
    }
}
```

**Run it:**
```bash
php artisan db:seed --class=TaxRateSeeder
```

---

### Step 3: Test Basic Functionality (1 hour) ⏰

```bash
php artisan tinker
```

**Test 1: Create a tax rate manually**
```php
$taxRate = TaxRate::create([
    'id' => Str::uuid(),
    'business_id' => Business::first()->id,
    'name' => 'Service Charge 10%',
    'code' => 'SC',
    'rate' => 0.1000,
    'is_inclusive' => false,
    'applies_to' => 'food',
    'is_default' => false,
    'status' => 'active',
]);
```

**Test 2: Create a purchase order**
```php
$po = PurchaseOrder::create([
    'id' => Str::uuid(),
    'business_id' => Business::first()->id,
    'po_number' => 'PO-2026-001',
    'supplier_id' => suplier::first()->id,
    'order_date' => today(),
    'status' => 'draft',
    'subtotal' => 100000,
    'total_amount' => 118000,
]);
```

**Test 3: Create a bar tab**
```php
$tab = BarTab::create([
    'id' => Str::uuid(),
    'tab_no' => 'TAB-001',
    'business_id' => Business::first()->id,
    'outlet_id' => PosOutlet::first()->id,
    'status' => 'open',
    'opened_at' => now(),
]);
```

**Test 4: Create a unified payment**
```php
$payment = Payment::create([
    'id' => Str::uuid(),
    'business_id' => Business::first()->id,
    'payable_type' => 'App\Models\sales',
    'payable_id' => sales::first()->id,
    'payment_method_id' => payment_method::first()->id,
    'amount' => 50000,
    'currency' => 'TZS',
    'amount_local' => 50000,
    'status' => 'completed',
    'paid_at' => now(),
]);
```

---

## 🔥 Most Important Tables to Understand

### 1. **tax_rates** - Core for all pricing
- Links to menu_items, sales, purchases
- Supports inclusive/exclusive tax
- Per-business configuration

### 2. **payments** - Unified payment tracking
- Polymorphic (handles sales, orders, invoices, folios, tabs)
- Replaces fragmented payment tables
- Tracks payment methods, currencies, exchange rates

### 3. **order_discounts** - Centralized discounts
- Polymorphic (sales, pos_orders)
- Supports: percentage, fixed, voucher, happy hour, complimentary
- Tracks who approved/applied

### 4. **void_logs** - Immutable audit trail
- Every void action recorded
- Cannot be deleted
- Links to voided entity

### 5. **bar_tabs** - Customer tab system
- Track open tabs
- Link multiple orders
- Charge to hotel folio
- Settlement tracking

### 6. **menu_item_recipes** - Auto stock deduction
- Links menu items to raw inventory
- Calculates ingredient costs
- Supports ML tracking for bar
- Auto-deducts on order

### 7. **stock_transfers** - Inter-outlet movement
- Request → Approve → Dispatch → Receive workflow
- Tracks quantity sent vs received
- Updates item_balances automatically

---

## 📊 Database Statistics

- **Total Tables:** 85+ tables
- **New Tables:** 28 tables
- **Modified Tables:** 15 tables
- **Total Models:** 82 models
- **New Relationships:** 150+ new relationships
- **Soft Deletes:** 20+ tables
- **Polymorphic Relations:** 4 (payments, discounts, void_logs, folio_charges)

---

## 🎓 Key Concepts

### Polymorphic Relationships
```php
// One payment table serves multiple modules
$sale->newPayments() // MorphMany
$posOrder->payments() // MorphMany
$invoice->payments() // MorphMany
```

### Outlet Scoping
```php
// Items can be outlet-specific
$item->outlet_id // NULL = available everywhere

// Stock tracking per outlet
$itemBalance->outlet_id

// Sales per outlet
$sale->outlet_id
```

### Recipe-Based Deduction
```php
// When selling "Gin & Tonic" menu item
// Auto-deducts: 60ml Gordon's Gin, 200ml tonic
$menuItem->recipes()->each(function($recipe) {
    // Deduct from inventory
});
```

---

## 🐛 Troubleshooting

### Migration Issues
```bash
# Check status
php artisan migrate:status

# Rollback last batch
php artisan migrate:rollback

# Fresh migration (WARNING: deletes data)
php artisan migrate:fresh
```

### Foreign Key Errors
```bash
# Disable temporarily (for testing only!)
SET FOREIGN_KEY_CHECKS = 0;
# Your SQL here
SET FOREIGN_KEY_CHECKS = 1;
```

### Model Not Found
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Regenerate autoload
composer dump-autoload
```

---

## 📞 Quick Commands

```bash
# List all models
ls -1 app/Models/*.php | wc -l

# List migrations
php artisan migrate:status

# Run specific seeder
php artisan db:seed --class=TaxRateSeeder

# Create controller
php artisan make:controller Api/BarTabController

# Create test
php artisan make:test BarTabTest

# Interactive testing
php artisan tinker
```

---

## 🎯 Success Criteria

You'll know Phase 3 is complete when:
- [x] All 25 new models exist
- [ ] All models have proper relationships
- [ ] All models have SoftDeletes where needed
- [ ] Tax rates seeder works
- [ ] Can create records in tinker without errors
- [ ] Foreign key constraints are satisfied

---

## 📖 Related Files

- Full roadmap: `IMPLEMENTATION_ROADMAP.md`
- Migrations: `database/migrations/2026_03_19_*.php`
- Models: `app/Models/`
- SQL source: `/home/moscow/Downloads/files(1)/`

---

**Last Updated:** 2026-03-19 18:15
**Status:** Phase 3 (Model Updates) - 25% Complete
