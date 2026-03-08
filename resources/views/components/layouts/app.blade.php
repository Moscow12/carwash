<!DOCTYPE html>
<html lang="en" data-bs-theme="auto" class="collapsed">
  @include('components.layouts.partials.header')
  <body>
    <!-- Vertical Sidebar -->
    <div>
      <div id="miniSidebar" >
        @include('components.layouts.partials.logo')

        @include('components.layouts.partials.navbar-vertical')
      </div>
      
      <!-- Offcanvas Sidebar -->
      <div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
        <div class="offcanvas-header">

            <a class='d-flex align-items-center gap-2' href="{{ route('admin.dashboard') }}">
              <img src="{{ asset('images/brand/logo/logo-icon.svg') }}" alt="" />
              <span class="fw-bold fs-4  site-logo-text">HRP</span>
            </a>

          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          @include('components.layouts.partials.navbar-vertical')
        </div>
      </div>

      <!-- Main Content -->
      <div id="content" class="position-relative h-100">
        <!-- navbar -->
        @include('components.layouts.partials.navbar-top')
        <!--Offcanvas notification-->


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
    <!-- jQuery (required for Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Global Select2 Initialization -->
    <script src="{{ asset('assets/js/select2-init.js') }}"></script>
  </body>

</html>
