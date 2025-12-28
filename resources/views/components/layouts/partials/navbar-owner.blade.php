<ul class="navbar-nav flex-column" id="miniSidebarNav">
  <!-- Dashboard -->
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.dashboard") ? "active" : "" }}' href="{{ route('owner.dashboard') }}">
      <span class="nav-icon"><i class="ti ti-dashboard fs-5"></i></span>
      <span class="text">Dashboard</span>
    </a>
  </li>

  <!-- My Business -->
  <li class="nav-item">
    <div class="nav-heading">My Business</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.carwashes*") ? "active" : "" }}' href="{{ route('owner.carwashes') }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">My Carwashes</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("owner.items*") ? "active" : "" }}' href="{{ route('owner.items') }}">
      <span class="nav-icon"><i class="ti ti-tools fs-5"></i></span>
      <span class="text">Items & Services</span>
    </a>
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
