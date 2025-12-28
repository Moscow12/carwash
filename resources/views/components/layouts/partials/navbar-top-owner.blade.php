<div class="navbar-glass navbar navbar-expand-lg px-0 px-lg-4">
  <div class="container-fluid px-lg-0">
    <div class="d-flex align-items-center gap-4">
      <!-- Collapse -->
      <div class="d-block d-lg-none">
        <a class="text-inherit" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-menu-2">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M4 6l16 0" />
            <path d="M4 12l16 0" />
            <path d="M4 18l16 0" />
          </svg>
        </a>
      </div>
      @if(View::exists('components.ui.sidebar-toggle'))
        <x-ui.sidebar-toggle />
      @endif
    </div>

    <!-- Navbar nav -->
    <ul class="list-unstyled d-flex align-items-center mb-0 gap-2">
      <!-- Light dark mode-->
      <li>
        @if(View::exists('components.ui.theme-switcher'))
          <x-ui.theme-switcher iconLibrary="ti" buttonClass="btn-ghost" :withWrapper="false" />
        @endif
      </li>
      <!-- Dropdown -->
      <li class="ms-3 dropdown">
        <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
          <img src="{{ asset('images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-sm rounded-circle" />
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-md p-0">
          <div>
            <div class="d-flex gap-3 align-items-center border-dashed border-bottom px-4 py-4">
              <img src="{{ asset('images/avatar/avatar-1.jpg') }}" alt="" class="avatar avatar-md rounded-circle" />
              <div>
                @auth
                  <h4 class="mb-0 fs-5">{{ Auth::user()->name }}</h4>
                  <p class="mb-0 text-secondary small">{{ Auth::user()->email }}</p>
                  <span class="badge bg-success mt-1">Owner</span>
                @else
                  <h4 class="mb-0 fs-5">Guest</h4>
                @endauth
              </div>
            </div>
            <div class="p-3 d-flex flex-column gap-1">
              <a href="{{ route('owner.dashboard') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span><i class="ti ti-home-2 fs-5"></i></span>
                <span>Dashboard</span>
              </a>
              <a href="{{ route('owner.carwashes') }}" class="dropdown-item d-flex align-items-center gap-2">
                <span><i class="ti ti-car-garage fs-5"></i></span>
                <span>My Carwashes</span>
              </a>
            </div>
            <div class="border-top p-3">
              @auth
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
                    <span><i class="ti ti-logout fs-5"></i></span>
                    <span>Logout</span>
                  </button>
                </form>
              @endauth
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
