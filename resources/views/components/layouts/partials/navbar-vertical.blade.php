<ul class="navbar-nav flex-column" id="miniSidebarNav">
  <!-- Dashboard -->
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.dashboard") ? "active" : "" }}' href="{{ route('admin.dashboard') }}">
      <span class="nav-icon"><i class="ti ti-dashboard fs-5"></i></span>
      <span class="text">Dashboard</span>
    </a>
  </li>

  <!-- User Management -->
  <li class="nav-item">
    <div class="nav-heading">User Management</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.users*") ? "active" : "" }}' href="{{ route('admin.users') }}">
      <span class="nav-icon"><i class="ti ti-users fs-5"></i></span>
      <span class="text">Users</span>
    </a>
  </li>

  <!-- Configuration -->
  <li class="nav-item">
    <div class="nav-heading">Configuration</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.settings*") ? "active" : "" }}' href="{{ route('admin.settings') }}">
      <span class="nav-icon"><i class="ti ti-settings fs-5"></i></span>
      <span class="text">Settings</span>
    </a>
  </li>

  <!-- Location Management -->
  <li class="nav-item">
    <div class="nav-heading">Location Management</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>

  <!-- Location Setup Dropdown -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.countries*') || request()->routeIs('admin.regions*') || request()->routeIs('admin.districts*') || request()->routeIs('admin.wards*') || request()->routeIs('admin.streets*') ? 'active' : '' }}"
       href="#!"
       role="button"
       data-bs-toggle="dropdown"
       aria-expanded="{{ request()->routeIs('admin.countries*') || request()->routeIs('admin.regions*') || request()->routeIs('admin.districts*') || request()->routeIs('admin.wards*') || request()->routeIs('admin.streets*') ? 'true' : 'false' }}">
      <span class="nav-icon"><i class="ti ti-map-pin fs-5"></i></span>
      <span class="text">Location Setup</span>
    </a>
    <ul class="dropdown-menu flex-column {{ request()->routeIs('admin.countries*') || request()->routeIs('admin.regions*') || request()->routeIs('admin.districts*') || request()->routeIs('admin.wards*') || request()->routeIs('admin.streets*') ? 'show' : '' }}">
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("admin.countries*") ? "active" : "" }}' href="{{ route('admin.countries') }}">
          <i class="ti ti-world me-2"></i> Countries
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("admin.regions*") ? "active" : "" }}' href="{{ route('admin.regions') }}">
          <i class="ti ti-map-2 me-2"></i> Regions
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("admin.districts*") ? "active" : "" }}' href="{{ route('admin.districts') }}">
          <i class="ti ti-building-community me-2"></i> Districts
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("admin.wards*") ? "active" : "" }}' href="{{ route('admin.wards') }}">
          <i class="ti ti-home-2 me-2"></i> Wards
        </a>
      </li>
      <li class="nav-item">
        <a class='dropdown-item {{ request()->routeIs("admin.streets*") ? "active" : "" }}' href="{{ route('admin.streets') }}">
          <i class="ti ti-road me-2"></i> Streets
        </a>
      </li>
    </ul>
  </li>
</ul>
