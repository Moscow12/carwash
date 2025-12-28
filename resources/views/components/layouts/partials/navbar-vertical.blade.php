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
  
  
 

  <!-- Nav item -->
  <li class="nav-item dropdown">
    <a class='nav-link dropdown-toggle' href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="nav-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
          <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
        </svg>
      </span>
      <span class="text">Setup & Config</span>
    </a>
    <ul class="dropdown-menu flex-column">
      <li class="nav-item">
        <a class='nav-link {{ request()->routeIs("admin.settings*") ? "active" : "" }}' href="{{ route('admin.settings') }}"><i class="fa-solid fa-sliders"></i> Setup</a>
      </li>
       <li class="nav-item">
        <a class='nav-link  {{ request()->routeIs("admin.countries*") ? "active" : "" }}' href="{{ route('admin.countries') }}"><i class="fa-solid fa-location-dot"></i> Countries</a>
      </li>
      <li class="nav-item">
        <a class='nav-link {{ request()->routeIs("admin.streets*") ? "active" : "" }}' href="{{ route('admin.streets') }}"><i class="fa-solid fa-location-dot"></i> Streets</a>
      </li>
      <li class="nav-item">
        <a class='nav-link {{ request()->routeIs("admin.wards*") ? "active" : "" }}' href="{{ route('admin.wards') }}"><i class="fa-solid fa-money-bill"></i> Wards</a>
      </li>
      <li class="nav-item">
        <a class='nav-link {{ request()->routeIs("admin.districts*") ? "active" : "" }}' href="{{ route('admin.districts') }}"><i class="fa-solid fa-building"></i> Districts</a>
      </li>
      <li class="nav-item">
        <a class='nav-link {{ request()->routeIs("admin.regions*") ? "active" : "" }}' href="{{ route('admin.regions') }}"><i class="fa-solid fa-clipboard-user"></i> Regions</a>
      </li>
    </ul>
  </li>
</ul>
