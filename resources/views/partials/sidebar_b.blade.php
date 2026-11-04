
<div class="sidebar p-3">

    <h4 class="text-center mb-4">Inventory</h4>

    <ul class="nav flex-column">

        {{-- Dashboard --}}
        @if (auth()->user()->hasPermission('dashboard', 'view'))
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        {{-- Products --}}
        @if (auth()->user()->hasAnyPermission('products', ['view', 'create', 'edit', 'delete'])
            || auth()->user()->hasAnyPermission('categories', ['view', 'create', 'edit', 'delete'])
            || auth()->user()->hasAnyPermission('units', ['view', 'create', 'edit', 'delete'])
            || auth()->user()->hasAnyPermission('brands', ['view', 'create', 'edit', 'delete']))
        <li class="nav-item sidebar-menu {{ request()->routeIs('products.*', 'categories.*', 'units.*', 'brands.*') ? 'open' : '' }}">

            <a href="#" class="nav-link sidebar-menu-toggle {{ request()->routeIs('products.*', 'categories.*', 'units.*', 'brands.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam me-2"></i>
                <span>Products</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu ">

                @if (auth()->user()->hasPermission('products', 'view'))
                <li>
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') && !request()->routeIs('products.create') ? 'active' : '' }}">
                        <i class="bi bi-list me-2"></i>
                        Product List
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('products', 'create'))
                <li>
                    <a href="{{ route('products.create') }}" class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Product
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('categories', 'view'))
                <li>
                    <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags me-2"></i>
                        Categories
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('units', 'view'))
                <li>
                    <a href="{{ route('units.index') }}" class="nav-link {{ request()->routeIs('units.*') ? 'active' : '' }}">
                        <i class="bi bi-rulers me-2"></i>
                        Units
                    </a>
                </li>
                @endif
                @if (auth()->user()->hasPermission('brands', 'view'))
                <li>
                    <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                        <i class="bi bi-tag me-2"></i>
                        Brands
                    </a>
                </li>
                @endif
            </ul>

        </li>
        @endif


        {{-- Purchases --}}
        @if (auth()->user()->hasAnyPermission('purchases', ['view', 'create', 'edit', 'delete'])
            || auth()->user()->hasAnyPermission('purchase_returns', ['view', 'create', 'edit', 'delete']))
        <li class="nav-item sidebar-menu {{ request()->routeIs('purchases.*', 'purchase-returns.*') ? 'open' : '' }}">

            <a href="#" class="nav-link sidebar-menu-toggle {{ request()->routeIs('purchases.*', 'purchase-returns.*') ? 'active' : '' }}">
                <i class="bi bi-cart-plus me-2"></i>
                <span>Purchases</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                @if (auth()->user()->hasPermission('purchases', 'create'))
                <li>
                    <a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        New Purchase
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('purchases', 'view'))
                <li>
                    <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') && !request()->routeIs('purchases.create') ? 'active' : '' }}">
                        <i class="bi bi-list me-2"></i>
                        Purchase List
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('purchase_returns', 'view'))
                <li>
                    <a href="{{ route('purchase-returns.index') }}" class="nav-link {{ request()->routeIs('purchase-returns.*') && !request()->routeIs('purchase-returns.create') ? 'active' : '' }}">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        Purchase Return
                    </a>
                </li>
                @endif
                @if (auth()->user()->hasPermission('purchase_returns', 'create'))
                <li>
                    <a href="{{ route('purchase-returns.create') }}" class="nav-link {{ request()->routeIs('purchase-returns.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        Create Purchase Return
                    </a>
                </li>
                @endif

            </ul>

        </li>
        @endif


        {{-- Sales --}}
        @if (auth()->user()->hasAnyPermission('sales', ['view', 'create', 'edit', 'delete'])
            || auth()->user()->hasAnyPermission('sale_returns', ['view', 'create', 'edit', 'delete']))
        <li class="nav-item sidebar-menu {{ request()->routeIs('sales.*', 'sale-returns.*') ? 'open' : '' }}">

            <a href="{{ auth()->user()->hasPermission('sales', 'view') ? route('sales.index') : '#' }}" class="nav-link sidebar-menu-toggle {{ request()->routeIs('sales.*', 'sale-returns.*') ? 'active' : '' }}">
                <i class="bi bi-cart-check me-2"></i>
                <span>Sales</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                @if (auth()->user()->hasPermission('sales', 'create'))
                <li>
                    <a href="{{ route('sales.create') }}" class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        New Sale
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('sales', 'view'))
                <li>
                    <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') && !request()->routeIs('sales.create') ? 'active' : '' }}">
                        <i class="bi bi-list me-2"></i>
                        Sales List
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('sale_returns', 'view'))
                <li>
                    <a href="{{ route('sale-returns.index') }}" class="nav-link {{ request()->routeIs('sale-returns.*') && !request()->routeIs('sale-returns.create') ? 'active' : '' }}">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        Sales Return
                    </a>
                </li>
                @endif
                @if (auth()->user()->hasPermission('sale_returns', 'create'))
                <li>
                    <a href="{{ route('sale-returns.create') }}" class="nav-link {{ request()->routeIs('sale-returns.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        Create Sales Return
                    </a>
                </li>
                @endif

            </ul>

        </li>
        @endif


        {{-- Customers --}}
        @if (auth()->user()->hasAnyPermission('customers', ['view', 'create', 'edit', 'delete']))
        <li class="nav-item sidebar-menu {{ request()->routeIs('customers.*') ? 'open' : '' }}">

            <a href="{{ auth()->user()->hasPermission('customers', 'view') ? route('customers.index') : '#' }}" class="nav-link sidebar-menu-toggle {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="bi bi-people me-2"></i>
                <span>Parties</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                @if (auth()->user()->hasPermission('customers', 'view'))
                <li>
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') && !request()->routeIs('customers.create') ? 'active' : '' }}">
                        <i class="bi bi-list me-2"></i>
                        Party List
                    </a>
                </li>
                @endif

                @if (auth()->user()->hasPermission('customers', 'create'))
                <li>
                    <a href="{{ route('customers.create') }}" class="nav-link {{ request()->routeIs('customers.create') ? 'active' : '' }}">
                        <i class="bi bi-person-plus me-2"></i>
                        Add Party
                    </a>
                </li>
                @endif

            </ul>

        </li>
        @endif


        {{-- Suppliers --}}
        {{-- <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-truck me-2"></i>
                <span>Suppliers</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-list me-2"></i>
                        Supplier List
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-person-plus me-2"></i>
                        Add Supplier
                    </a>
                </li>

            </ul>

        </li> --}}


         {{-- Inventory --}}
        @if (auth()->user()->hasAnyPermission('inventory', ['view', 'create']))
        <li class="nav-item sidebar-menu {{ request()->routeIs('inventory.*') ? 'open' : '' }}">
            <a href="{{ auth()->user()->hasPermission('inventory', 'view') ? route('inventory.index') : route('inventory.adjustments.create') }}"
               class="nav-link sidebar-menu-toggle {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                <i class="bi bi-boxes me-2"></i>
                <span>Inventory</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>
            <ul class="sidebar-submenu">
                @if (auth()->user()->hasPermission('inventory', 'view'))
                <li>
                    <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index') ? 'active' : '' }}">
                        <i class="bi bi-grid me-2"></i>
                        Stock Overview
                    </a>
                </li>
                @endif
                @if (auth()->user()->hasPermission('inventory', 'create'))
                <li>
                    <a href="{{ route('inventory.adjustments.create') }}" class="nav-link {{ request()->routeIs('inventory.adjustments.*') ? 'active' : '' }}">
                        <i class="bi bi-sliders me-2"></i>
                        Stock Adjustment
                    </a>
                </li>
                @endif
                @if (auth()->user()->hasPermission('inventory', 'view'))
                <li>
                    <a href="{{ route('inventory.history') }}" class="nav-link {{ request()->routeIs('inventory.history') ? 'active' : '' }}">
                        <i class="bi bi-clock-history me-2"></i>
                        Stock History
                    </a>
                </li>
                <li>
                    <a href="{{ route('inventory.low-stock') }}" class="nav-link {{ request()->routeIs('inventory.low-stock') ? 'active' : '' }}">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Low Stock
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif



        {{-- Expenses --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-wallet2 me-2"></i>
                <span>Expenses</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Expense
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-list me-2"></i>
                        Expense List
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-tags me-2"></i>
                        Expense Categories
                    </a>
                </li>

            </ul>

        </li>


        {{-- Payments --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-cash-stack me-2"></i>
                <span>Payments</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-cash me-2"></i>
                        Payment Received
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-credit-card me-2"></i>
                        Payment Made
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-clock-history me-2"></i>
                        Payment History
                    </a>
                </li>

            </ul>

        </li>


        {{-- Reports --}}
        @if (auth()->user()->hasPermission('reports', 'view'))
        <li class="nav-item sidebar-menu {{ request()->routeIs('reports.*') ? 'open' : '' }}">

            <a href="#" class="nav-link sidebar-menu-toggle {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line me-2"></i>
                <span>Reports</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="{{ route('reports.products') }}" class="nav-link {{ request()->routeIs('reports.products') ? 'active' : '' }}">
                        <i class="bi bi-box-seam me-2"></i>
                        Product Report
                    </a>
                </li>

                <li>
                    <a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                        <i class="bi bi-cart-check me-2"></i>
                        Sales Report
                    </a>
                </li>

                <li>
                    <a href="{{ route('reports.purchases') }}" class="nav-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                        <i class="bi bi-cart-plus me-2"></i>
                        Purchase Report
                    </a>
                </li>

                <li>
                    <a href="{{ route('reports.stock') }}" class="nav-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                        <i class="bi bi-boxes me-2"></i>
                        Stock Report
                    </a>
                </li>

                <li>
                    <a href="{{ route('reports.profit_loss') }}" class="nav-link {{ request()->routeIs('reports.profit_loss') ? 'active' : '' }}">
                        <i class="bi bi-cash-stack me-2"></i>
                        Profit & Loss
                    </a>
                </li>

            </ul>

        </li>
        @endif


        {{-- Settings --}}
        <li class="nav-item sidebar-menu {{ request()->routeIs('profile.*', 'users.*') ? 'open' : '' }}">

            <a href="#" class="nav-link sidebar-menu-toggle {{ request()->routeIs('profile.*', 'users.*') ? 'active' : '' }}">
                <i class="bi bi-gear me-2"></i>
                <span>Settings</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-building me-2"></i>
                        Company Settings
                    </a>
                </li>

                <li>
                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <i class="bi bi-person-gear me-2"></i>
                        Profile
                    </a>
                </li>

                @if (auth()->user()->isAdministrator())
                <li>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i>
                        Users &amp; Access
                    </a>
                </li>
                @endif

            </ul>

        </li>

    </ul>

</div>
