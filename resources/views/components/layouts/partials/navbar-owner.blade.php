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
    <a class="nav-link dropdown-toggle {{ request()->routeIs('owner.carwashes*') || request()->routeIs('owner.carwashes*') ? 'active' : '' }}"
       href="#!"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="{{ request()->routeIs('owner.carwashes*') || request()->routeIs('owner.carwashes*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">My Carwashes</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('owner.carwashes*') || request()->routeIs('owner.carwashes*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.carwashes") ? "active" : "" }}' href="{{ route('owner.carwashes') }}">
          <i class="ti ti-package me-2"></i> Manage Carwash(s)
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("owner.mycarwash") ? "active" : "" }}' href="{{ route('owner.mycarwash') }}">
          <i class="ti ti-ruler-2 me-2"></i> Add New Carwash
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
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.stocktaking*") ? "active" : "" }}' href="{{ route('owner.stocktaking') }}">
      <span class="nav-icon"><i class="ti ti-clipboard-list fs-5"></i></span>
      <span class="text">Stocktaking</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.suppliers*") ? "active" : "" }}' href="{{ route('owner.suppliers') }}">
      <span class="nav-icon"><i class="ti ti-truck fs-5"></i></span>
      <span class="text">Suppliers</span>
    </a>
  </li>
</ul>
