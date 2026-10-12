
<div class="sidebar p-3">

    <h4 class="text-center mb-4">Inventory</h4>

    <ul class="nav flex-column">

        {{-- Dashboard --}}
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link active">
                <i class="bi bi-speedometer2 me-2"></i>
                <span>Dashboard</span>
            </a>
        </li>


        {{-- Products --}}
        <li class="nav-item sidebar-menu {{ request()->routeIs('products.*') ? 'open' : '' }}">

            <a href="" class="nav-link sidebar-menu-toggle ">
                <i class="bi bi-box-seam me-2"></i>
                <span>Products</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu ">

                <li>
                    <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}">
                        <i class="bi bi-list me-2"></i>
                        Product List
                    </a>
                </li>

                <li>
                    <a href="{{ route('products.create') }}" class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add Product
                    </a>
                </li>

                <li>
                    <a href="{{ route('categories.index') }}" class="nav-link">
                        <i class="bi bi-tags me-2"></i>
                        Categories
                    </a>
                </li>

                <li>
                    <a href="{{ route('units.index') }}" class="nav-link">
                        <i class="bi bi-rulers me-2"></i>
                        Units
                    </a>
                </li>

            </ul>

        </li>


        {{-- Purchases --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-cart-plus me-2"></i>
                <span>Purchases</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-plus-circle me-2"></i>
                        New Purchase
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-list me-2"></i>
                        Purchase List
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        Purchase Return
                    </a>
                </li>

            </ul>

        </li>


        {{-- Sales --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-cart-check me-2"></i>
                <span>Sales</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-plus-circle me-2"></i>
                        New Sale
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-list me-2"></i>
                        Sales List
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-arrow-return-left me-2"></i>
                        Sales Return
                    </a>
                </li>

            </ul>

        </li>


        {{-- Customers --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-people me-2"></i>
                <span>Customers</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-list me-2"></i>
                        Customer List
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-person-plus me-2"></i>
                        Add Customer
                    </a>
                </li>

            </ul>

        </li>


        {{-- Suppliers --}}
        <li class="nav-item sidebar-menu">

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

        </li>


        {{-- Inventory --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-boxes me-2"></i>
                <span>Inventory</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-boxes me-2"></i>
                        Stock Overview
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-arrow-left-right me-2"></i>
                        Stock Adjustment
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-clock-history me-2"></i>
                        Stock History
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Low Stock
                    </a>
                </li>

            </ul>

        </li>


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
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
                <i class="bi bi-bar-chart-line me-2"></i>
                <span>Reports</span>
                <i class="bi bi-chevron-right menu-arrow"></i>
            </a>

            <ul class="sidebar-submenu">

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-box-seam me-2"></i>
                        Product Report
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-cart-check me-2"></i>
                        Sales Report
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-cart-plus me-2"></i>
                        Purchase Report
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-boxes me-2"></i>
                        Stock Report
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-cash-stack me-2"></i>
                        Profit & Loss
                    </a>
                </li>

            </ul>

        </li>


        {{-- Settings --}}
        <li class="nav-item sidebar-menu">

            <a href="#" class="nav-link sidebar-menu-toggle">
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
                    <a href="#" class="nav-link">
                        <i class="bi bi-person-gear me-2"></i>
                        Profile
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-people me-2"></i>
                        Users
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link">
                        <i class="bi bi-shield-lock me-2"></i>
                        Roles & Permissions
                    </a>
                </li>

            </ul>

        </li>

    </ul>

</div>
```
