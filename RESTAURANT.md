# 🍽️ Restaurant Management System Documentation

## Table of Contents
1. [Overview](#overview)
2. [System Flow](#system-flow)
3. [Module Status](#module-status)
4. [Database Architecture](#database-architecture)
5. [Workflows](#workflows)
6. [Features Reference](#features-reference)
7. [Future Enhancements](#future-enhancements)

---

## Overview

### Purpose
The Restaurant Management System is a comprehensive POS solution designed for restaurants, cafes, and bars. It manages the complete dining experience from table allocation to payment processing, including kitchen integration and staff management.

### Key Features
- 📊 **Visual Floor Plan** - Real-time table status with color coding
- 🛎️ **Order Management** - Create, edit, and manage table orders
- 👨‍🍳 **Kitchen Integration** - Send orders to kitchen with ticket generation
- 💰 **Split Bill** - Divide orders between multiple diners
- 🚫 **Void Tracking** - Cancel items with reason documentation
- 📱 **Responsive Design** - Works on desktop, tablet, and mobile
- 🏪 **Multi-Outlet** - Support for multiple restaurant locations

### Technology Stack
- **Backend**: Laravel 10+ with Livewire 3
- **Frontend**: Blade Templates, Bootstrap 5, Tabler Icons
- **Database**: MySQL with UUID primary keys
- **Real-time**: Livewire reactive components

---

## System Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    CUSTOMER ARRIVAL FLOW                         │
└─────────────────────────────────────────────────────────────────┘

1. ARRIVAL
   └─> Customer arrives
       └─> Host checks table availability
           └─> Assign table (or create reservation)

2. SEATING
   └─> Table status: AVAILABLE → OCCUPIED
       └─> Waiter assigned (optional)
           └─> Open new order

3. ORDERING
   └─> Browse menu by category
       └─> Add items to order
           └─> Adjust quantities
               └─> Add special notes
                   └─> Send to Kitchen

4. KITCHEN
   └─> Kitchen ticket generated
       └─> Status: QUEUED → PREPARING → READY
           └─> Notify waiter when ready

5. SERVING
   └─> Waiter serves food
       └─> Items marked as SERVED
           └─> Can add more items (repeat step 3)

6. PAYMENT
   └─> Request bill
       ├─> Option 1: Pay full amount
       ├─> Option 2: Split bill (2-6 diners)
       └─> Option 3: Charge to room (hotel guests)
           └─> Process payment
               └─> Generate receipt

7. CLEANUP
   └─> Table status: OCCUPIED → AVAILABLE
       └─> Ready for next customer
```

---

## Module Status

### ✅ Fully Implemented

#### 1. Restaurant POS (`RestaurantPOS.php`)
**Location**: `app/Livewire/Owner/Restaurant/RestaurantPOS.php`
**View**: `resources/views/livewire/owner/restaurant/restaurant-p-o-s.blade.php`
**Status**: ✅ **COMPLETE** (650+ lines backend, 778+ lines UI)

**Features**:
- [x] Floor plan view with table grid
- [x] Real-time table status (available/occupied/reserved)
- [x] Order creation and management
- [x] Menu browsing with categories
- [x] Search functionality
- [x] Kitchen ticket generation
- [x] Split bill (2-6 diners)
- [x] Void items with reason tracking
- [x] Payment processing
- [x] Multi-outlet support
- [x] Mobile responsive design

**Database Tables Used**:
- `pos_tables` - Table inventory and status
- `pos_orders` - Order headers
- `pos_order_items` - Individual order items
- `kitchen_tickets` - Kitchen workflow tracking
- `menu_items` - Available dishes/drinks
- `menu_categories` - Menu organization
- `pos_outlets` - Restaurant locations
- `customers` - Customer database
- `staffs` - Waiters/staff
- `payment_methods` - Payment options
- `sales_payments` - Payment records

### ⏳ Partially Implemented

#### 2. Table Reservations (`TableReservations.php`)
**Location**: `app/Livewire/Owner/Restaurant/TableReservations.php`
**Status**: ⏳ **DATABASE READY, UI PENDING**

**Database Table Available**:
- ✅ `table_reservations` - Complete schema
  - Reservation number
  - Guest details
  - Date/time/duration
  - Covers (number of diners)
  - Deposit tracking
  - Status workflow
  - Linked to POS orders when seated

**Missing**:
- ❌ Reservation creation UI
- ❌ Calendar view
- ❌ Reservation management interface
- ❌ Seating workflow (reservation → order)

#### 3. Waiter Management (`WaiterManagement.php`)
**Location**: `app/Livewire/Owner/Restaurant/WaiterManagement.php`
**Status**: ⏳ **DATABASE READY, UI PENDING**

**Database Table Available**:
- ✅ `waiter_assignments` - Staff-table linking
  - Session-based assignments
  - Assignment timestamps
  - Table rotation tracking

**Missing**:
- ❌ Waiter assignment UI
- ❌ Performance tracking
- ❌ Commission calculations
- ❌ Shift management

#### 4. Menu Recipes (`MenuRecipes.php`)
**Location**: `app/Livewire/Owner/Restaurant/MenuRecipes.php`
**Status**: ⏳ **DATABASE READY, UI PENDING**

**Database Table Available**:
- ✅ `menu_item_recipes` - Ingredient linkage
  - Links menu items to inventory items
  - Quantity per serving
  - Auto stock deduction
  - Optional modifiers

**Missing**:
- ❌ Recipe management UI
- ❌ Automatic inventory deduction
- ❌ Cost calculation
- ❌ Ingredient substitution logic

### ❌ Not Started

#### 5. Bar Module
**Status**: ❌ **DATABASE COMPLETE, NO CODE**

**Database Tables Available**:
- ✅ `bar_profiles` - Bar configuration
- ✅ `bar_tabs` - Open tabs system
- ✅ `bar_tab_orders` - Orders linked to tabs
- ✅ `bar_bottle_services` - Premium bottle service
- ✅ `bar_happy_hour_prices` - Time-based pricing

**Features to Build**:
- Tab management (open/close tabs)
- Age verification workflow
- Happy hour automation
- Bottle service tracking
- Mixer management

#### 6. Kitchen Display System (KDS)
**Status**: ❌ **TICKETS GENERATED, DISPLAY PENDING**

**What Exists**:
- ✅ Kitchen tickets are generated when orders sent
- ✅ Status tracking (queued/preparing/ready)
- ✅ Station routing

**Missing**:
- ❌ Kitchen display screen
- ❌ Ticket prioritization
- ❌ Timer/alerts
- ❌ Fulfillment workflow UI

#### 7. Printer Station Management
**Status**: ❌ **DATABASE READY, NO CODE**

**Database Table Available**:
- ✅ `outlet_printer_stations` - Printer configuration
  - Station routing
  - IP/network settings
  - Printer types (receipt/kitchen/KDS/label)

**Missing**:
- ❌ Printer configuration UI
- ❌ Print job management
- ❌ Network printer integration
- ❌ Print templates

#### 8. Menu Item Modifiers
**Status**: ❌ **DATABASE READY, NO CODE**

**Database Table Available**:
- ✅ `item_modifiers` - Add-ons and customizations

**Missing**:
- ❌ Modifier management UI
- ❌ Modifier selection in orders
- ❌ Price calculation with modifiers
- ❌ Kitchen note generation

---

## Database Architecture

### Core POS Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `pos_outlets` | Restaurant/bar locations | ✅ Active | → pos_tables, pos_orders, menu_items |
| `pos_tables` | Table inventory | ✅ Active | → pos_orders, waiter_assignments |
| `pos_orders` | Order headers | ✅ Active | → pos_order_items, kitchen_tickets |
| `pos_order_items` | Individual items | ✅ Active | → menu_items, kitchen_tickets |
| `pos_sessions` | Shift/cashier sessions | 📝 Ready | → pos_orders, waiter_assignments |
| `kitchen_tickets` | Kitchen workflow | ✅ Active | → pos_order_items, pos_orders |

### Menu Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `menu_categories` | Organize menu | ✅ Active | → menu_items |
| `menu_items` | Dishes/drinks | ✅ Active | → pos_order_items, menu_item_recipes |
| `item_modifiers` | Customizations | 📝 Ready | → menu_items |
| `menu_item_recipes` | Ingredient links | 📝 Ready | → menu_items, items (inventory) |

### Reservation Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `table_reservations` | Advance bookings | 📝 Ready | → pos_tables, pos_orders, customers |
| `waiter_assignments` | Staff-table links | 📝 Ready | → pos_tables, staffs, pos_sessions |

### Bar Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `bar_profiles` | Bar settings | 📝 Ready | → pos_outlets |
| `bar_tabs` | Open tabs | 📝 Ready | → pos_orders, customers, folios |
| `bar_tab_orders` | Tab-order link | 📝 Ready | → bar_tabs, pos_orders |
| `bar_bottle_services` | Premium service | 📝 Ready | → menu_items, bar_tabs |
| `bar_happy_hour_prices` | Time-based pricing | 📝 Ready | → menu_items |

### Printer Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `outlet_printer_stations` | Printer config | 📝 Ready | → pos_outlets |

### Supporting Tables

| Table Name | Purpose | Status | Relationships |
|------------|---------|--------|---------------|
| `customers` | Customer database | ✅ Active | → pos_orders, table_reservations |
| `staffs` | Employee records | ✅ Active | → pos_orders (served_by), waiter_assignments |
| `payment_methods` | Payment options | ✅ Active | → sales_payments |
| `sales_payments` | Payment records | ✅ Active | → pos_orders |
| `items` | Inventory (stock) | ✅ Active | → menu_item_recipes |
| `businesses` | Business entities | ✅ Active | → pos_outlets, pos_orders |

**Legend**:
- ✅ Active - Currently in use
- 📝 Ready - Schema exists, awaiting implementation
- ⏳ Partial - Some features implemented

---

## Workflows

### 1. Table Lifecycle

```
┌──────────┐
│AVAILABLE │ ◄─────────────────────────────┐
└────┬─────┘                                │
     │ Customer seated                      │
     ▼                                      │
┌──────────┐                                │
│ OCCUPIED │ ◄──────────┐                   │
└────┬─────┘            │                   │
     │ Make reservation │                   │
     ▼                  │                   │
┌──────────┐            │                   │
│ RESERVED │            │                   │
└────┬─────┘            │                   │
     │ Reservation time │                   │
     └──────────────────┘                   │
                                            │
     (During service)                       │
     └─> Order created                      │
     └─> Items sent to kitchen              │
     └─> Food served                        │
     └─> Payment processed                  │
                                            │
     Payment complete ──────────────────────┘
```

### 2. Order Lifecycle

```
┌──────┐
│ OPEN │ - New order created
└──┬───┘
   │
   ▼
┌──────────────────┐
│ SENT_TO_KITCHEN  │ - Items sent to kitchen
└──┬───────────────┘  - Kitchen tickets generated
   │
   ▼
┌──────┐
│READY │ - Kitchen marks ready
└──┬───┘  - Waiter notified
   │
   ▼
┌────────┐
│ SERVED │ - Food delivered to table
└──┬─────┘  - Can add more items (→ SENT_TO_KITCHEN)
   │
   ▼
┌────────┐
│ BILLED │ - Customer requests bill
└──┬─────┘  - Can split bill
   │
   ▼
┌──────┐
│ PAID │ - Payment processed
└──────┘  - Receipt generated
         - Table becomes AVAILABLE

Note: At any stage, items can be VOIDED with reason
```

### 3. Kitchen Ticket Workflow

```
Order Sent to Kitchen
     │
     ▼
┌────────┐
│ QUEUED │ - Ticket created in queue
└────┬───┘  - Sorted by time/priority
     │
     ▼
┌────────────┐
│ PREPARING  │ - Chef starts cooking
└────────┬───┘  - Timer starts
         │
         ▼
┌───────┐
│ READY │ - Dish complete
└───┬───┘  - Notify waiter
    │
    ▼
┌─────────┐
│ SERVED  │ - Item delivered
└─────────┘  - Ticket closed

Alternative: CANCELLED - If item voided
```

### 4. Split Bill Process

```
Customer requests bill
     │
     ▼
Select "Split Bill"
     │
     ▼
Choose number of diners (2-6)
     │
     ▼
┌─────────────────────────┐
│ Assign items to diners  │
│ ┌─────┐  ┌─────┐       │
│ │ D1  │  │ D2  │       │
│ │Item1│  │Item3│       │
│ │Item2│  │Item4│       │
│ └─────┘  └─────┘       │
└─────────────────────────┘
     │
     ▼
Generate separate bills
     │
     ├─> Diner 1: TZS 45,000
     ├─> Diner 2: TZS 38,000
     └─> Diner 3: TZS 52,000
```

---

## Features Reference

### RestaurantPOS Component Methods

#### Data Loading
```php
loadData()           // Load all master data
loadOutlets()        // Get restaurant locations
loadTables()         // Get tables with order status
loadMenu()           // Load menu categories
loadMenuItems()      // Load menu items (filtered)
loadStaffs()         // Get waiters
loadCustomers()      // Get customer list
loadPaymentMethods() // Get payment options
```

#### Table Management
```php
selectTable($tableId)     // Open table for ordering
loadOrder($orderId)       // Load existing order
createNewOrder($tableId)  // Start new order
```

#### Order Management
```php
addMenuItem($menuItemId)         // Add item to order
updateQuantity($index, $qty)     // Change item quantity
removeItem($index)               // Remove item from order
calculateUnsentItems()           // Track unsent items
getTotalProperty()               // Calculate order total
getUnsentTotalProperty()         // Calculate unsent total
```

#### Kitchen Integration
```php
sendToKitchen()  // Send items to kitchen
                 // - Creates PosOrder if new
                 // - Creates PosOrderItem records
                 // - Generates KitchenTicket for each item
                 // - Updates table status to OCCUPIED
```

#### Void Management
```php
openVoidModal($itemId)  // Show void dialog
voidItem()              // Void item with reason
                        // - Marks item as VOIDED
                        // - Updates kitchen ticket to CANCELLED
                        // - Recalculates order total
```

#### Split Bill
```php
openSplitBillModal()              // Show split bill interface
initializeSplitItems()            // Initialize diner columns
assignItemToSplit($item, $diner)  // Assign item to diner
                                  // - Calculates individual totals
```

#### Payment
```php
openPaymentModal()    // Show payment dialog
processPayment()      // Process payment
                      // - Marks order as PAID
                      // - Creates sales_payments record
                      // - Updates table to AVAILABLE
```

#### Utilities
```php
generateOrderNumber()  // Generate ORD-YYYY-NNNNN format
closeOrderModal()      // Close order interface
resetSelection()       // Clear current selection
```

### UI Components

#### Floor Plan
- Grid layout with responsive columns
- Color-coded status: Green (available), Red (occupied), Yellow (reserved)
- Shows capacity and active order badge
- Click to open order modal

#### Order Modal
- Split-screen layout (menu left, order right)
- Searchable menu with category filters
- Quantity controls for unsent items
- Visual indicator for items in kitchen
- Void button for sent items
- Send to Kitchen, Split Bill, and Payment buttons

#### Modals
- **Void Modal**: Required reason field with textarea
- **Split Bill Modal**: Drag-and-drop item assignment
- **Payment Modal**: Amount and method selection

---

## Future Enhancements

### Short-term (Next Sprint)

1. **Table Reservations UI**
   - Calendar view
   - Time slot management
   - Deposit handling
   - Seating workflow

2. **Kitchen Display System**
   - Real-time ticket screen
   - Color-coded priorities
   - Timers and alerts
   - Bump/recall functionality

3. **Menu Item Modifiers**
   - Add-ons and extras
   - Special instructions
   - Price adjustments
   - Kitchen note generation

### Mid-term (Future Releases)

4. **Bar Tab System**
   - Open/close tabs
   - Tab-to-order linking
   - Credit limit enforcement
   - Tab transfer

5. **Waiter Management**
   - Table assignments
   - Performance metrics
   - Commission tracking
   - Shift management

6. **Reporting & Analytics**
   - Sales by period
   - Popular items
   - Kitchen efficiency
   - Staff performance
   - Revenue forecasting

### Long-term (Future Versions)

7. **Happy Hour Automation**
   - Time-based pricing
   - Auto-apply discounts
   - Promotion management

8. **Bottle Service**
   - Premium service tracking
   - Mixer management
   - VIP table handling

9. **Recipe Cost Analysis**
   - Ingredient cost tracking
   - Profitability analysis
   - Auto inventory deduction

10. **Mobile Ordering**
    - QR code menu
    - Self-order from table
    - Order tracking

11. **Integration**
    - Hotel room charges (already linked in DB)
    - Accounting systems
    - Delivery platforms
    - Online reservations

---

## File Locations

### Backend Components
```
app/Livewire/Owner/Restaurant/
├── RestaurantPOS.php          ✅ COMPLETE
├── TableReservations.php      ⏳ PENDING
├── WaiterManagement.php       ⏳ PENDING
└── MenuRecipes.php            ⏳ PENDING
```

### Frontend Views
```
resources/views/livewire/owner/restaurant/
├── restaurant-p-o-s.blade.php      ✅ COMPLETE
├── table-reservations.blade.php   ⏳ PENDING
├── waiter-management.blade.php    ⏳ PENDING
└── menu-recipes.blade.php         ⏳ PENDING
```

### Models
```
app/Models/
├── PosOrder.php           ✅ EXISTS
├── PosOrderItem.php       ✅ EXISTS
├── PosTable.php           ✅ EXISTS
├── PosOutlet.php          ✅ EXISTS
├── PosSession.php         ✅ EXISTS
├── KitchenTicket.php      ✅ EXISTS
├── MenuItem.php           ✅ EXISTS
├── MenuCategory.php       ✅ EXISTS
├── ItemModifier.php       ✅ EXISTS
├── TableReservation.php   ✅ EXISTS
├── WaiterAssignment.php   ⏳ NEEDS CREATION
├── BarTab.php             ⏳ NEEDS CREATION
└── BarProfile.php         ⏳ NEEDS CREATION
```

### Migrations
```
database/migrations/
├── 2026_03_11_100015_create_pos_outlets_table.php
├── 2026_03_11_100016_create_pos_tables_table.php
├── 2026_03_11_100017_create_menu_categories_table.php
├── 2026_03_11_100018_create_menu_items_table.php
├── 2026_03_11_100019_create_item_modifiers_table.php
├── 2026_03_11_100020_create_pos_sessions_table.php
├── 2026_03_11_100021_create_pos_orders_table.php
├── 2026_03_11_100022_create_pos_order_items_table.php
├── 2026_03_11_100023_create_kitchen_tickets_table.php
└── 2026_03_19_100005_create_restaurant_bar_tables.php  ✅ ALL CREATED
```

---

## Getting Started

### Prerequisites
1. Run migrations: `php artisan migrate`
2. Seed sample data:
   - Create a business with type='restaurant'
   - Create POS outlet
   - Add tables with sections
   - Add menu categories and items
   - Add payment methods

### Access
Navigate to: `/owner/restaurant-pos`

### Quick Test Workflow
1. **Select outlet** (if multiple locations)
2. **Click a table** → Opens order modal
3. **Browse menu** → Click items to add
4. **Adjust quantities** → Use +/- buttons
5. **Send to Kitchen** → Generates tickets
6. **Process Payment** → Completes order
7. **Table becomes available** → Ready for next customer

---

## Support & Development

### Database Schema Updates
All tables use:
- UUID primary keys (`char(36)`)
- Soft deletes where applicable
- Foreign key constraints
- Indexed columns for performance

### Adding New Features
1. Check if database table exists (see tables above)
2. Create/update Model with relationships
3. Create Livewire component
4. Create Blade view
5. Add route in `web.php`
6. Update this documentation

### Known Limitations
- Kitchen tickets are generated but no display screen yet
- Table reservations DB ready but no booking interface
- Bar features fully designed but not implemented
- No automated inventory deduction from recipes

---

## Version History

- **v1.0** (Current) - Restaurant POS with floor plan, orders, kitchen tickets, split bill, void tracking
- **v0.9** - Database schema complete, all tables created
- **v0.8** - Models and relationships established

---

**Last Updated**: March 29, 2026
**Status**: Production Ready (Core Features), Development (Extended Features)
**Maintainer**: Development Team
