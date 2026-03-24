<ul class="navbar-nav flex-column" id="miniSidebarNav">
  <!-- Dashboard -->
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.dashboard") ? "active" : "" }}' href="{{ route('owner.dashboard') }}">
      <span class="nav-icon"><i class="ti ti-dashboard fs-5"></i></span>
      <span class="text">Dashboard</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.posscreen") ? "active" : "" }}' href="{{ route('owner.posscreen') }}">
      <span class="nav-icon"><i class="fa-solid fa-cash-register fs-6"></i></span>
      <span class="text">POS</span>
    </a>
  </li>
  <!-- My Business -->
  <li class="nav-item">
    <div class="nav-heading">My Business</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.businesses*') || request()->routeIs('owner.mybusiness*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.businesses*') || request()->routeIs('owner.mybusiness*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">My Businesses</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.businesses*') || request()->routeIs('owner.mybusiness*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.businesses") ? "active" : "" }}' href="{{ route('owner.businesses') }}">
          <i class="ti ti-package me-2"></i> Manage Business(es)
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.mybusiness") ? "active" : "" }}' href="{{ route('owner.mybusiness') }}">
          <i class="ti ti-ruler-2 me-2"></i> Add New Business
        </a>
      </li>
    </ul>
  </li>
  <!-- Items & Services Dropdown -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.items*') || request()->routeIs('owner.itemregister*') || request()->routeIs('owner.categories*') || request()->routeIs('owner.units*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.items*') || request()->routeIs('owner.itemregister*') || request()->routeIs('owner.categories*') || request()->routeIs('owner.units*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-tools fs-5"></i></span>
      <span class="text">Items & Services</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.items*') || request()->routeIs('owner.itemregister*') || request()->routeIs('owner.categories*') || request()->routeIs('owner.units*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.itemregister") ? "active" : "" }}' href="{{ route('owner.itemregister') }}">
          <i class="ti ti-package me-2"></i> Items / Services
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.list-items") ? "active" : "" }}' href="{{ route('owner.list-items') }}">
          <i class="ti ti-package me-2"></i> List all Items
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.uploaditems") ? "active" : "" }}' href="{{ route('owner.uploaditems') }}">
          <i class="ti ti-upload me-2"></i> Upload Items
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.categories") ? "active" : "" }}' href="{{ route('owner.categories') }}">
          <i class="ti ti-category me-2"></i> Categories
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.units*") ? "active" : "" }}' href="{{ route('owner.units') }}">
          <i class="ti ti-ruler-2 me-2"></i> Units
        </a>
      </li>
    </ul>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.expenses*') || request()->routeIs('owner.expenses*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.expenses*') || request()->routeIs('owner.expenses*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">Expenses</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.expenses*') || request()->routeIs('owner.expenses*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.expenses") ? "active" : "" }}' href="{{ route('owner.expenses') }}">
          <i class="ti ti-package me-2"></i> List all Expenses
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.expenses.categories") ? "active" : "" }}' href="{{ route('owner.expenses.categories') }}">
          <i class="ti ti-ruler-2 me-2"></i>Category and Sub Category
        </a>
      </li>
    </ul>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.reports*') || request()->routeIs('owner.reports*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.reports*') || request()->routeIs('owner.reports*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-file-text fs-5"></i></span>
      <span class="text">Reports</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.reports*') || request()->routeIs('owner.reports*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.reports.profitandloss") ? "active" : "" }}' href="{{ route('owner.reports.profitandloss') }}">
          <i class="ti ti-package me-2"></i> Profit and Loss
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.reports.sales") ? "active" : "" }}' href="{{ route('owner.reports.sales') }}">
          <i class="ti ti-ruler-2 me-2"></i> Sales
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.reports.stock") ? "active" : "" }}' href="{{ route('owner.reports.stock') }}">
          <i class="ti ti-ruler-2 me-2"></i> Stock Report
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.reports.staffcommissions") ? "active" : "" }}' href="{{ route('owner.reports.staffcommissions') }}">
          <i class="ti ti-users me-2"></i> Staff Commissions
        </a>
      </li>
    </ul>
  </li>
  <!-- Bar Management -->
  <li class="nav-item dropdown" id="barManagementMenu">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.bar.*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.bar.*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-glass-full fs-5"></i></span>
      <span class="text">Bar Management</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.bar.*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.dashboard") ? "active" : "" }}' href="{{ route('owner.bar.dashboard') }}">
          <i class="ti ti-dashboard me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.pos") ? "active" : "" }}' href="{{ route('owner.bar.pos') }}">
          <i class="ti ti-cash-register me-2"></i> Bar POS
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.menu") ? "active" : "" }}' href="{{ route('owner.bar.menu') }}">
          <i class="ti ti-menu-2 me-2"></i> Menu Items
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.tabs") ? "active" : "" }}' href="{{ route('owner.bar.tabs') }}">
          <i class="ti ti-receipt me-2"></i> Bar Tabs
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.stock") ? "active" : "" }}' href="{{ route('owner.bar.stock') }}">
          <i class="ti ti-package me-2"></i> Stock Management
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.bar.reports") ? "active" : "" }}' href="{{ route('owner.bar.reports') }}">
          <i class="ti ti-chart-bar me-2"></i> Reports
        </a>
      </li>
    </ul>
  </li>

  <!-- Restaurant Management -->
  <li class="nav-item dropdown" id="restaurantManagementMenu">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.restaurant*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.restaurant*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-tools-kitchen-2 fs-5"></i></span>
      <span class="text">Restaurant Management</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.restaurant*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.restaurant.reservations") ? "active" : "" }}' href="{{ route('owner.restaurant.reservations') }}">
          <i class="ti ti-calendar-event me-2"></i> Table Reservations
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.restaurant.waiters") ? "active" : "" }}' href="{{ route('owner.restaurant.waiters') }}">
          <i class="ti ti-users me-2"></i> Waiter Management
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.restaurant.recipes") ? "active" : "" }}' href="{{ route('owner.restaurant.recipes') }}">
          <i class="ti ti-chef-hat me-2"></i> Menu Recipes
        </a>
      </li>
    </ul>
  </li>

  <!-- Hotel Management -->
    <li class="nav-item dropdown" id="hotelManagementMenu">
      <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.hotel*') ? 'active' : '' }}"
        href="#!"
        role="button"
        data-bs-toggle="dropdown"
        aria-expanded="{{ request()->routeIs('owner.hotel*') ? 'true' : 'false' }}">
        <span class="nav-icon"><i class="ti ti-bed fs-5"></i></span>
        <span class="text">Hotel Management</span>
      </a>
      <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.hotel*') ? 'show' : '' }}">
        <!-- Hotels Section -->
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotels") ? "active" : "" }}' href="{{ route('owner.hotels') }}">
            <i class="ti ti-bed me-2"></i> My Hotels
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Front Desk -->
        <li class="nav-item"><small class="dropdown-header text-muted">Front Desk</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.frontdesk") ? "active" : "" }}' href="{{ route('owner.hotel.frontdesk') }}">
            <i class="ti ti-dashboard me-2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reservations") ? "active" : "" }}' href="{{ route('owner.hotel.reservations') }}">
            <i class="ti ti-calendar-event me-2"></i> Reservations
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.checkin") ? "active" : "" }}' href="{{ route('owner.hotel.checkin') }}">
            <i class="ti ti-login me-2"></i> Check-In
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.checkout") ? "active" : "" }}' href="{{ route('owner.hotel.checkout') }}">
            <i class="ti ti-logout me-2"></i> Check-Out
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.room-status") ? "active" : "" }}' href="{{ route('owner.hotel.room-status') }}">
            <i class="ti ti-door me-2"></i> Room Status
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Rooms & Inventory -->
        <li class="nav-item"><small class="dropdown-header text-muted">Rooms & Inventory</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.room-types") ? "active" : "" }}' href="{{ route('owner.hotel.room-types') }}">
            <i class="ti ti-tag me-2"></i> Room Types
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.rooms") ? "active" : "" }}' href="{{ route('owner.hotel.rooms') }}">
            <i class="ti ti-door me-2"></i> Rooms
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.rate-plans") ? "active" : "" }}' href="{{ route('owner.hotel.rate-plans') }}">
            <i class="ti ti-currency-dollar me-2"></i> Rate Plans
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.availability") ? "active" : "" }}' href="{{ route('owner.hotel.availability') }}">
            <i class="ti ti-calendar-stats me-2"></i> Availability
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Guests -->
        <li class="nav-item"><small class="dropdown-header text-muted">Guests</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.guests") ? "active" : "" }}' href="{{ route('owner.hotel.guests') }}">
            <i class="ti ti-users me-2"></i> Guest Directory
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.guest-documents") ? "active" : "" }}' href="{{ route('owner.hotel.guest-documents') }}">
            <i class="ti ti-file-text me-2"></i> Documents
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.communication-log") ? "active" : "" }}' href="{{ route('owner.hotel.communication-log') }}">
            <i class="ti ti-mail me-2"></i> Communication
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Housekeeping -->
        <li class="nav-item"><small class="dropdown-header text-muted">Housekeeping</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.housekeeping.tasks") ? "active" : "" }}' href="{{ route('owner.hotel.housekeeping.tasks') }}">
            <i class="ti ti-checkbox me-2"></i> Tasks
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.housekeeping.status") ? "active" : "" }}' href="{{ route('owner.hotel.housekeeping.status') }}">
            <i class="ti ti-vacuum-cleaner me-2"></i> Room Status
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.lost-found") ? "active" : "" }}' href="{{ route('owner.hotel.lost-found') }}">
            <i class="ti ti-search me-2"></i> Lost & Found
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Maintenance -->
        <li class="nav-item"><small class="dropdown-header text-muted">Maintenance</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.maintenance.requests") ? "active" : "" }}' href="{{ route('owner.hotel.maintenance.requests') }}">
            <i class="ti ti-tool me-2"></i> Requests
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.maintenance.preventive") ? "active" : "" }}' href="{{ route('owner.hotel.maintenance.preventive') }}">
            <i class="ti ti-calendar-check me-2"></i> Preventive
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Guest Services -->
        <li class="nav-item"><small class="dropdown-header text-muted">Guest Services</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.amenity-requests") ? "active" : "" }}' href="{{ route('owner.hotel.amenity-requests') }}">
            <i class="ti ti-briefcase me-2"></i> Amenity Requests
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.wakeup-calls") ? "active" : "" }}' href="{{ route('owner.hotel.wakeup-calls') }}">
            <i class="ti ti-alarm me-2"></i> Wakeup Calls
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.laundry-orders") ? "active" : "" }}' href="{{ route('owner.hotel.laundry-orders') }}">
            <i class="ti ti-wash me-2"></i> Laundry Orders
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Billing & Finance -->
        <li class="nav-item"><small class="dropdown-header text-muted">Billing & Finance</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.folios") ? "active" : "" }}' href="{{ route('owner.hotel.folios') }}">
            <i class="ti ti-file-invoice me-2"></i> Folios
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.invoices") ? "active" : "" }}' href="{{ route('owner.hotel.invoices') }}">
            <i class="ti ti-receipt me-2"></i> Invoices
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.payments") ? "active" : "" }}' href="{{ route('owner.hotel.payments') }}">
            <i class="ti ti-cash me-2"></i> Payments
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.night-audit") ? "active" : "" }}' href="{{ route('owner.hotel.night-audit') }}">
            <i class="ti ti-moon me-2"></i> Night Audit
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.tax-config") ? "active" : "" }}' href="{{ route('owner.hotel.tax-config') }}">
            <i class="ti ti-percentage me-2"></i> Tax Config
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- F&B Management -->
        <li class="nav-item"><small class="dropdown-header text-muted">Restaurant & Bar</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.pos-outlets") ? "active" : "" }}' href="{{ route('owner.hotel.pos-outlets') }}">
            <i class="ti ti-building-store me-2"></i> POS Outlets
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.pos-orders") ? "active" : "" }}' href="{{ route('owner.hotel.pos-orders') }}">
            <i class="ti ti-receipt-2 me-2"></i> Orders
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.tables") ? "active" : "" }}' href="{{ route('owner.hotel.tables') }}">
            <i class="ti ti-armchair me-2"></i> Tables
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.pos-sessions") ? "active" : "" }}' href="{{ route('owner.hotel.pos-sessions') }}">
            <i class="ti ti-clock me-2"></i> Sessions
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Reports -->
        <li class="nav-item"><small class="dropdown-header text-muted">Reports</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reports.occupancy") ? "active" : "" }}' href="{{ route('owner.hotel.reports.occupancy') }}">
            <i class="ti ti-chart-bar me-2"></i> Occupancy
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reports.revenue") ? "active" : "" }}' href="{{ route('owner.hotel.reports.revenue') }}">
            <i class="ti ti-chart-line me-2"></i> Revenue
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reports.reservations") ? "active" : "" }}' href="{{ route('owner.hotel.reports.reservations') }}">
            <i class="ti ti-calendar-stats me-2"></i> Reservations
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reports.housekeeping") ? "active" : "" }}' href="{{ route('owner.hotel.reports.housekeeping') }}">
            <i class="ti ti-report me-2"></i> Housekeeping
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.reports.guest-history") ? "active" : "" }}' href="{{ route('owner.hotel.reports.guest-history') }}">
            <i class="ti ti-history me-2"></i> Guest History
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>

        <!-- Channels & Integrations -->
        <li class="nav-item"><small class="dropdown-header text-muted">Channels</small></li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.booking-sources") ? "active" : "" }}' href="{{ route('owner.hotel.booking-sources') }}">
            <i class="ti ti-source me-2"></i> Booking Sources
          </a>
        </li>
        <li class="nav-item">
          <a class='dropdown-item {{ request()->routeIs("owner.hotel.channel-mapping") ? "active" : "" }}' href="{{ route('owner.hotel.channel-mapping') }}">
            <i class="ti ti-map-pin me-2"></i> Channel Mapping
          </a>
        </li>
      </ul>
    </li>
  <!-- People -->
  <li class="nav-item">
    <div class="nav-heading">People</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.staffs*") ? "active" : "" }}' href="{{ route('owner.staffs') }}">
      <span class="nav-icon"><i class="ti ti-users-group fs-5"></i></span>
      <span class="text">Staff</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.customers*") ? "active" : "" }}' href="{{ route('owner.customers') }}">
      <span class="nav-icon"><i class="ti ti-user-check fs-5"></i></span>
      <span class="text">Customers</span>
    </a>
  </li>

  <!-- Transactions -->
  <li class="nav-item">
    <div class="nav-heading">Transactions</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.sales*") ? "active" : "" }}' href="{{ route('owner.sales') }}">
      <span class="nav-icon"><i class="ti ti-currency-dollar fs-5"></i></span>
      <span class="text">Sales</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.purchases*") ? "active" : "" }}' href="{{ route('owner.purchases') }}">
      <span class="nav-icon"><i class="ti ti-shopping-cart fs-5"></i></span>
      <span class="text">Purchases</span>
    </a>
  </li>

  <!-- Inventory -->
  <li class="nav-item">
    <div class="nav-heading">Inventory</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.inventory*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.inventory*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-package fs-5"></i></span>
      <span class="text">Inventory Management</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.inventory*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.inventory.purchases") ? "active" : "" }}' href="{{ route('owner.inventory.purchases') }}">
          <i class="ti ti-shopping-cart me-2"></i> Purchase Orders
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.stocktaking*") ? "active" : "" }}' href="{{ route('owner.stocktaking') }}">
          <i class="ti ti-clipboard-list me-2"></i> Stocktaking
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.inventory.stocktransfers") ? "active" : "" }}' href="{{ route('owner.inventory.stocktransfers') }}">
          <i class="ti ti-arrows-transfer me-2"></i> Stock Transfers
        </a>
      </li>
    </ul>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.suppliers*") ? "active" : "" }}' href="{{ route('owner.suppliers') }}">
      <span class="nav-icon"><i class="ti ti-truck fs-5"></i></span>
      <span class="text">Suppliers</span>
    </a>
  </li>
  <!-- Configuration -->
  <li class="nav-item">
    <div class="nav-heading">Configuration</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.settings*') || request()->routeIs('owner.settings*') ? 'active' : '' }}"
      href="#!"
      role="button"
      data-bs-toggle="dropdown"
      aria-expanded="{{ request()->routeIs('owner.settings*') || request()->routeIs('owner.settings*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">Settings</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.settings*') || request()->routeIs('owner.settings*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.settings") ? "active" : "" }}' href="{{ route('owner.settings') }}">
          <i class="ti ti-package me-2"></i> System Settings
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.settings.hotel") ? "active" : "" }}' href="{{ route('owner.settings.hotel') }}">
          <i class="ti ti-ruler-2 me-2"></i>Hotel Management Settings
        </a>
      </li>
    </ul>
  </li>
</ul>