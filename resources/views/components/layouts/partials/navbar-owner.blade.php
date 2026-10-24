@php
    // Modules this owner/staff can access (admin sees all). Computed once for the sidebar.
    $moduleKeys = auth()->check() ? auth()->user()->accessibleModuleKeys() : collect();

    // Hide links the user's role can't open (uses each route's own can: middleware); owners see everything.
    $canSee = fn (string $route) => auth()->check() && auth()->user()->canAccessRoute($route);
    $canSeeAny = fn (array $routes) => collect($routes)->contains(fn ($route) => $canSee($route));

    // A link: label, route name, icon, and the routeIs() pattern that marks it active (defaults to the route itself)
    $link = fn (string $label, string $route, string $icon, ?string $active = null) => compact('label', 'route', 'icon') + ['active' => $active ?? $route];

    /*
     * The sidebar, top to bottom, grouped by what people do:
     * sell → run the business (industry modules) → products & stock → people → money → settings.
     * Each group: ['label', 'icon', 'module' (optional), 'sections' => [title|null => [links]]].
     */
    $menu = [
        [
            'heading' => null,
            'entries' => [
                $link('Dashboard', 'owner.dashboard', 'ti-dashboard'),
            ],
        ],
        [
            'heading' => 'Sell',
            'entries' => [
                [
                    'label' => 'POS & Sales', 'icon' => 'ti-shopping-cart', 'module' => 'pos',
                    'sections' => [
                        null => [
                            $link('POS Screen', 'owner.posscreen', 'ti-cash-register'),
                            $link('All Sales', 'owner.sales', 'ti-receipt'),
                            $link('Sales Dashboard', 'owner.sales.dashboard', 'ti-chart-bar'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Operations',
            'entries' => [
                [
                    'label' => 'Hotel', 'icon' => 'ti-bed', 'module' => 'hotel',
                    'sections' => [
                        'Front Desk' => [
                            $link('Front Desk', 'owner.hotel.frontdesk', 'ti-dashboard'),
                            $link('Reservations', 'owner.hotel.reservations', 'ti-calendar-event'),
                            $link('Check-In', 'owner.hotel.checkin', 'ti-login'),
                            $link('Check-Out', 'owner.hotel.checkout', 'ti-logout'),
                            $link('Room Status', 'owner.hotel.room-status', 'ti-door'),
                        ],
                        'Guests' => [
                            $link('Guests', 'owner.hotel.guests', 'ti-users'),
                            $link('Guest Documents', 'owner.hotel.guest-documents', 'ti-file-text'),
                            $link('Communication', 'owner.hotel.communication-log', 'ti-mail'),
                            $link('Amenity Requests', 'owner.hotel.amenity-requests', 'ti-briefcase'),
                            $link('Wake-up Calls', 'owner.hotel.wakeup-calls', 'ti-alarm'),
                            $link('Laundry Orders', 'owner.hotel.laundry-orders', 'ti-wash'),
                        ],
                        'Housekeeping & Maintenance' => [
                            $link('Housekeeping Tasks', 'owner.hotel.housekeeping.tasks', 'ti-checkbox'),
                            $link('Housekeeping Status', 'owner.hotel.housekeeping.status', 'ti-vacuum-cleaner'),
                            $link('Lost & Found', 'owner.hotel.lost-found', 'ti-search'),
                            $link('Maintenance Requests', 'owner.hotel.maintenance.requests', 'ti-tool'),
                            $link('Preventive Maintenance', 'owner.hotel.maintenance.preventive', 'ti-calendar-check'),
                        ],
                        'Food & Beverage' => [
                            $link('POS Outlets', 'owner.hotel.pos-outlets', 'ti-building-store'),
                            $link('Orders', 'owner.hotel.pos-orders', 'ti-receipt-2'),
                            $link('Tables', 'owner.hotel.tables', 'ti-armchair'),
                            $link('Sessions', 'owner.hotel.pos-sessions', 'ti-clock'),
                        ],
                        'Billing' => [
                            $link('Folios', 'owner.hotel.folios', 'ti-file-invoice'),
                            $link('Invoices', 'owner.hotel.invoices', 'ti-receipt'),
                            $link('Payments', 'owner.hotel.payments', 'ti-cash'),
                            $link('Night Audit', 'owner.hotel.night-audit', 'ti-moon'),
                        ],
                        'Rooms & Setup' => [
                            $link('My Hotels', 'owner.hotels', 'ti-building'),
                            $link('Rooms', 'owner.hotel.rooms', 'ti-door'),
                            $link('Room Types', 'owner.hotel.room-types', 'ti-tag'),
                            $link('Rate Plans', 'owner.hotel.rate-plans', 'ti-currency-dollar'),
                            $link('Availability', 'owner.hotel.availability', 'ti-calendar-stats'),
                            $link('Tax Config', 'owner.hotel.tax-config', 'ti-percentage'),
                            $link('Booking Sources', 'owner.hotel.booking-sources', 'ti-source'),
                            $link('Channel Mapping', 'owner.hotel.channel-mapping', 'ti-map-pin'),
                        ],
                        'Hotel Reports' => [
                            $link('Occupancy', 'owner.hotel.reports.occupancy', 'ti-chart-bar'),
                            $link('Revenue', 'owner.hotel.reports.revenue', 'ti-chart-line'),
                            $link('Reservations Report', 'owner.hotel.reports.reservations', 'ti-calendar-stats'),
                            $link('Housekeeping Report', 'owner.hotel.reports.housekeeping', 'ti-report'),
                            $link('Guest History', 'owner.hotel.reports.guest-history', 'ti-history'),
                        ],
                    ],
                ],
                [
                    'label' => 'Restaurant', 'icon' => 'ti-tools-kitchen-2', 'module' => 'restaurant',
                    'sections' => [
                        null => [
                            $link('Dashboard', 'owner.restaurant.dashboard', 'ti-dashboard'),
                            $link('Restaurant POS', 'owner.restaurant.pos', 'ti-cash-register'),
                            $link('Kitchen Screen', 'owner.restaurant.kitchenscreen', 'ti-chef-hat'),
                            $link('Table Reservations', 'owner.restaurant.reservations', 'ti-calendar-event'),
                        ],
                        'Setup' => [
                            $link('Menu Recipes', 'owner.restaurant.recipes', 'ti-notebook'),
                            $link('Waiters', 'owner.restaurant.waiters', 'ti-users'),
                            $link('Restaurant Settings', 'owner.restaurant.settings', 'ti-settings'),
                        ],
                    ],
                ],
                [
                    'label' => 'Bar', 'icon' => 'ti-glass-full', 'module' => 'bar',
                    'sections' => [
                        null => [
                            $link('Dashboard', 'owner.bar.dashboard', 'ti-dashboard'),
                            $link('Bar POS', 'owner.bar.pos', 'ti-beer'),
                            $link('Bar Tabs', 'owner.bar.tabs', 'ti-receipt'),
                        ],
                        'Setup' => [
                            $link('Menu Items', 'owner.bar.menu', 'ti-menu-2'),
                            $link('Bar Stock', 'owner.bar.stock', 'ti-package'),
                        ],
                        'Insights' => [
                            $link('Bar Reports', 'owner.bar.reports', 'ti-chart-bar'),
                        ],
                    ],
                ],
                [
                    'label' => 'Rentals', 'icon' => 'ti-building-community', 'module' => 'rental', 'active' => 'owner.rental*',
                    'sections' => [
                        null => [
                            $link('Dashboard', 'owner.rental.dashboard', 'ti-dashboard'),
                        ],
                        'Operations' => [
                            $link('Tenancy Agreements', 'owner.rental.agreements', 'ti-file-text'),
                            $link('Rent Payments', 'owner.rental.rent-payments', 'ti-receipt'),
                            $link('Utility Bills', 'owner.rental.utility-bills', 'ti-bolt'),
                            $link('Maintenance', 'owner.rental.maintenance', 'ti-tool'),
                        ],
                        'Catalog' => [
                            $link('Properties', 'owner.rental.properties', 'ti-building'),
                            $link('Rental Units', 'owner.rental.units', 'ti-home-2'),
                            $link('Landlords', 'owner.rental.landlords', 'ti-users'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Products & Stock',
            'entries' => [
                [
                    'label' => 'Items & Services', 'icon' => 'ti-package',
                    'sections' => [
                        null => [
                            $link('All Items', 'owner.list-items', 'ti-list'),
                            $link('Add Item', 'owner.itemregister', 'ti-plus'),
                            $link('Import Items', 'owner.uploaditems', 'ti-file-upload'),
                        ],
                        'Organise' => [
                            $link('Categories', 'owner.categories', 'ti-category'),
                            $link('Units', 'owner.units', 'ti-ruler-2', 'owner.units*'),
                        ],
                    ],
                ],
                [
                    'label' => 'Inventory', 'icon' => 'ti-building-warehouse',
                    'sections' => [
                        null => [
                            $link('Purchase Orders', 'owner.inventory.purchases', 'ti-shopping-bag', 'owner.inventory.purchases*'),
                            $link('Stocktaking', 'owner.stocktaking', 'ti-clipboard-list', 'owner.stocktaking*'),
                            $link('Stock Transfers', 'owner.inventory.stocktransfers', 'ti-arrows-exchange'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'People',
            'entries' => [
                $link('Customers', 'owner.people.customers', 'ti-user-heart'),
                $link('Suppliers', 'owner.people.suppliers', 'ti-truck', 'owner.people.suppliers*'),
                [
                    'label' => 'Staff & Access', 'icon' => 'ti-id-badge-2',
                    'sections' => [
                        null => [
                            $link('Staff', 'owner.people.Staffs', 'ti-users'),
                            $link('Assign Roles', 'owner.people.user-roles', 'ti-user-check', 'owner.people.user-roles*'),
                            $link('Roles & Permissions', 'owner.people.roles', 'ti-shield-lock', 'owner.people.roles*'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Money',
            'entries' => [
                [
                    'label' => 'Expenses', 'icon' => 'ti-receipt-2',
                    'sections' => [
                        null => [
                            $link('All Expenses', 'owner.expenses', 'ti-list'),
                            $link('Expense Categories', 'owner.expenses.categories', 'ti-category'),
                        ],
                    ],
                ],
                [
                    'label' => 'Reports', 'icon' => 'ti-chart-pie',
                    'sections' => [
                        null => [
                            $link('Profit & Loss', 'owner.reports.profitandloss', 'ti-scale'),
                            $link('Sales Report', 'owner.reports.sales', 'ti-chart-bar'),
                            $link('Stock Report', 'owner.reports.stock', 'ti-packages'),
                            $link('Staff Commissions', 'owner.reports.staffcommissions', 'ti-coins'),
                        ],
                    ],
                ],
            ],
        ],
        [
            'heading' => 'Settings',
            'entries' => [
                [
                    'label' => 'Businesses', 'icon' => 'ti-building-store',
                    'sections' => [
                        null => [
                            $link('My Businesses', 'owner.businesses', 'ti-list'),
                            $link('Add Business', 'owner.mybusiness', 'ti-plus'),
                        ],
                    ],
                ],
                [
                    'label' => 'Settings', 'icon' => 'ti-settings',
                    'sections' => [
                        null => [
                            $link('General Settings', 'owner.settings', 'ti-adjustments'),
                            ...($moduleKeys->contains('hotel') ? [$link('Hotel Settings', 'owner.settings.hotel', 'ti-bed')] : []),
                        ],
                    ],
                ],
                $link('Subscription', 'owner.subscription', 'ti-credit-card'),
            ],
        ],
    ];

    // Keep only what this user can open: links they have access to, groups whose module is enabled
    // and that still have links, and headings that still have entries.
    $visibleMenu = collect($menu)->map(function (array $block) use ($canSee, $moduleKeys) {
        $block['entries'] = collect($block['entries'])->map(function (array $entry) use ($canSee, $moduleKeys) {
            if (isset($entry['route'])) {
                return $canSee($entry['route']) ? $entry : null;
            }

            if (isset($entry['module']) && ! $moduleKeys->contains($entry['module'])) {
                return null;
            }

            $entry['sections'] = collect($entry['sections'])
                ->map(fn (array $links) => array_values(array_filter($links, fn (array $link) => $canSee($link['route']))))
                ->filter()
                ->all();

            if ($entry['sections'] === []) {
                return null;
            }

            $entry['patterns'] = collect($entry['sections'])->flatten(1)->pluck('active')->all();
            if (isset($entry['active'])) {
                $entry['patterns'][] = $entry['active'];
            }

            return $entry;
        })->filter()->values()->all();

        return $block;
    })->filter(fn (array $block) => $block['entries'] !== [])->values();
@endphp
<ul class="navbar-nav flex-column" id="miniSidebarNav">
  @foreach($visibleMenu as $block)
    @if($block['heading'])
      <li class="nav-item">
        <div class="nav-heading">{{ $block['heading'] }}</div>
        <hr class="mx-5 nav-line mb-1" />
      </li>
    @endif

    @foreach($block['entries'] as $entry)
      @if(isset($entry['route']))
        {{-- Single link --}}
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs($entry['active']) ? 'active' : '' }}" href="{{ route($entry['route']) }}">
            <span class="nav-icon"><i class="ti {{ $entry['icon'] }} fs-5"></i></span>
            <span class="text">{{ $entry['label'] }}</span>
          </a>
        </li>
      @else
        {{-- Group --}}
        @php $isOpen = request()->routeIs(...$entry['patterns']); @endphp
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle {{ $isOpen ? 'active' : '' }}"
             href="#!"
             role="button"
             data-bs-toggle="dropdown"
             aria-expanded="{{ $isOpen ? 'true' : 'false' }}">
            <span class="nav-icon"><i class="ti {{ $entry['icon'] }} fs-5"></i></span>
            <span class="text">{{ $entry['label'] }}</span>
          </a>
          <ul class="dropdown-menu flex-column {{ $isOpen ? 'show' : '' }}">
            @foreach($entry['sections'] as $sectionTitle => $links)
              @if(! $loop->first)
                <li><hr class="dropdown-divider"></li>
              @endif
              @if($sectionTitle !== '')
                <li class="nav-item"><small class="dropdown-header text-muted">{{ $sectionTitle }}</small></li>
              @endif
              @foreach($links as $item)
                <li class="nav-item">
                  <a class="dropdown-item {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                    <i class="ti {{ $item['icon'] }} me-2"></i>{{ $item['label'] }}
                  </a>
                </li>
              @endforeach
            @endforeach
          </ul>
        </li>
      @endif
    @endforeach
  @endforeach
</ul>
