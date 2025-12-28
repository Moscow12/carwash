<!DOCTYPE html>
<html lang="en" data-bs-theme="auto" class="collapsed">
  @include('components.layouts.partials.header')
  <body>
    <!-- Vertical Sidebar -->
    <div>
      <div id="miniSidebar" >
        @include('components.layouts.partials.logo-customer')

        @include('components.layouts.partials.navbar-customer')
      </div>

      <!-- Offcanvas Sidebar -->
      <div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">
            <a class='d-flex align-items-center gap-2' href="{{ route('customer.dashboard') }}">
              <img src="{{ asset('images/brand/logo/logo-icon.svg') }}" alt="" />
              <span class="fw-bold fs-4 site-logo-text">CAMS</span>
            </a>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          @include('components.layouts.partials.navbar-customer')
        </div>
      </div>

      <!-- Main Content -->
      <div id="content" class="position-relative h-100">
        <!-- navbar -->
        @include('components.layouts.partials.navbar-top-customer')

        <!-- container -->
        <div class="custom-container">
          {{ $slot }}
        </div>
      </div>
    </div>
    <!-- Libs JS -->
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme.min.js') }}"></script>
    <script src="{{ asset('assets/js/vendors/sidebarnav.js') }}"></script>
    @if(class_exists('ToastMagic'))
    {!! ToastMagic::scripts() !!}
    @endif
    @livewireScripts
  </body>
</html>
