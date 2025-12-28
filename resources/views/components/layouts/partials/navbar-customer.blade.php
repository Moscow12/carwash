<ul class="navbar-nav flex-column" id="miniSidebarNav">
  <!-- Dashboard -->
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("customer.dashboard") ? "active" : "" }}' href="{{ route('customer.dashboard') }}">
      <span class="nav-icon"><i class="ti ti-dashboard fs-5"></i></span>
      <span class="text">Dashboard</span>
    </a>
  </li>

  <!-- Services -->
  <li class="nav-item">
    <div class="nav-heading">Services</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("customer.carwashes") ? "active" : "" }}' href="{{ route('customer.carwashes') }}">
      <span class="nav-icon"><i class="ti ti-car-garage fs-5"></i></span>
      <span class="text">Browse Carwashes</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("customer.bookings*") ? "active" : "" }}' href="{{ route('customer.bookings') }}">
      <span class="nav-icon"><i class="ti ti-calendar-event fs-5"></i></span>
      <span class="text">My Bookings</span>
    </a>
  </li>

  <!-- Account -->
  <li class="nav-item">
    <div class="nav-heading">Account</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("customer.profile*") ? "active" : "" }}' href="{{ route('customer.profile') }}">
      <span class="nav-icon"><i class="ti ti-user fs-5"></i></span>
      <span class="text">My Profile</span>
    </a>
  </li>
</ul>
