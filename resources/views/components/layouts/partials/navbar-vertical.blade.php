<ul class="navbar-nav flex-column">
  <!-- Dashboard -->
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.dashboard") ? "active" : "" }}' href="{{ route('admin.dashboard') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-dashboard">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M12 13m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"/>
          <path d="M13.45 11.55l2.05 -2.05"/>
          <path d="M6.4 20a9 9 0 1 1 11.2 0z"/>
        </svg>
      </span>
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
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-users">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/>
          <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"/>
        </svg>
      </span>
      <span class="text">Users</span>
    </a>
  </li>

  <!-- Location Management -->
  <li class="nav-item">
    <div class="nav-heading">Location Management</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.countries*") ? "active" : "" }}' href="{{ route('admin.countries') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-world">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/>
          <path d="M3.6 9h16.8"/>
          <path d="M3.6 15h16.8"/>
          <path d="M11.5 3a17 17 0 0 0 0 18"/>
          <path d="M12.5 3a17 17 0 0 1 0 18"/>
        </svg>
      </span>
      <span class="text">Countries</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.regions*") ? "active" : "" }}' href="{{ route('admin.regions') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map-2">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M12 18.5l-3 -1.5l-6 3v-13l6 -3l6 3l6 -3v7.5"/>
          <path d="M9 4v13"/>
          <path d="M15 7v5.5"/>
          <path d="M21.121 20.121a3 3 0 1 0 -4.242 0c.418 .419 1.125 1.045 2.121 1.879c1.051 -.89 1.759 -1.516 2.121 -1.879z"/>
          <path d="M19 18v.01"/>
        </svg>
      </span>
      <span class="text">Regions</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.districts*") ? "active" : "" }}' href="{{ route('admin.districts') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-building-community">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M8 9l5 5v7h-5v-4m0 4h-5v-7l5 -5m1 1v-6a1 1 0 0 1 1 -1h10a1 1 0 0 1 1 1v17h-8"/>
          <path d="M13 7l0 .01"/>
          <path d="M17 7l0 .01"/>
          <path d="M17 11l0 .01"/>
          <path d="M17 15l0 .01"/>
        </svg>
      </span>
      <span class="text">Districts</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.wards*") ? "active" : "" }}' href="{{ route('admin.wards') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home-2">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M5 12l-2 0l9 -9l9 9l-2 0"/>
          <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/>
          <path d="M10 12h4v4h-4z"/>
        </svg>
      </span>
      <span class="text">Wards</span>
    </a>
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.streets*") ? "active" : "" }}' href="{{ route('admin.streets') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-road">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M4 19l4 -14"/>
          <path d="M16 5l4 14"/>
          <path d="M12 8v-2"/>
          <path d="M12 13v-2"/>
          <path d="M12 18v-2"/>
        </svg>
      </span>
      <span class="text">Streets</span>
    </a>
  </li>

  <!-- System -->
  <li class="nav-item">
    <div class="nav-heading">System</div>
    <hr class="mx-5 nav-line mb-1" />
  </li>
  <li class="nav-item">
    <a class='nav-link {{ request()->routeIs("admin.settings*") ? "active" : "" }}' href="{{ route('admin.settings') }}">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/>
          <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/>
        </svg>
      </span>
      <span class="text">Settings</span>
    </a>
  </li>
</ul>
