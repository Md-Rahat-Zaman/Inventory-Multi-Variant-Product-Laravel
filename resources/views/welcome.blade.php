
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inventory Management System') }} | Smart Inventory Management</title>
    <meta name="description"
          content="Manage products, stock, sales, purchases and business reports in one organized inventory platform.">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">


    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">

</head>


<body>


<!-- ================================
     NAVBAR
================================= -->

<nav class="navbar-custom">

    <div class="container">

        <div class="d-flex align-items-center justify-content-between">

            <a href="{{ url('/') }}" class="brand">

                <span class="brand-icon">
                    <i class="bi bi-boxes"></i>
                </span>

                <span>
                    Inventory System
                </span>

            </a>


            <div class="d-flex align-items-center gap-2">

                @auth

                    <a href="{{ route('dashboard') }}"
                       class="register-btn">

                        <i class="bi bi-grid-1x2-fill me-1"></i>

                        Dashboard

                    </a>

                @else

                    <a href="{{ route('login') }}"
                       class="login-btn">

                        Login

                    </a>


                    @if (Route::has('register'))

                        <a href="{{ route('register') }}"
                           class="register-btn">

                            Register

                        </a>

                    @endif

                @endauth

            </div>

        </div>

    </div>

</nav>



<!-- ================================
     HERO
================================= -->

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <div class="hero-badge">

                <i class="bi bi-check-circle-fill"></i>

                Smart Inventory Management

            </div>


            <h1>

                Manage your inventory

                <span>smarter & easier.</span>

            </h1>


            <p>

                Keep track of products, stock, purchases, sales,
                customers and suppliers from one powerful
                inventory management system.

            </p>


            <div class="hero-buttons">

                @auth

                    <a href="{{ route('dashboard') }}"
                       class="primary-btn">

                        <i class="bi bi-grid-1x2-fill"></i>

                        Go to Dashboard

                    </a>

                @else

                    <a href="{{ route('login') }}"
                       class="primary-btn">

                        <i class="bi bi-box-arrow-in-right"></i>

                        Get Started

                    </a>

                @endauth


                <a href="#features"
                   class="secondary-btn">

                    <i class="bi bi-arrow-down"></i>

                    Explore Features

                </a>

            </div>

        </div>



        <!-- Dashboard Preview -->

        <div class="dashboard-preview">

            <div class="preview-card">

                <div class="preview-header">

                    <div class="preview-title">

                        Inventory Overview

                    </div>

                    <div class="preview-status">

                        <i class="bi bi-circle-fill"></i>

                        Sample Preview

                    </div>

                </div>


                <div class="row g-3">

                    <div class="col-6 col-lg-3">

                        <div class="stat-card">

                            <div class="stat-icon">
                                <i class="bi bi-box-seam"></i>
                            </div>

                            <div class="stat-label">
                                Total Products
                            </div>

                            <div class="stat-value">
                                1,248
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-lg-3">

                        <div class="stat-card">

                            <div class="stat-icon">
                                <i class="bi bi-boxes"></i>
                            </div>

                            <div class="stat-label">
                                Total Stock
                            </div>

                            <div class="stat-value">
                                8,540
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-lg-3">

                        <div class="stat-card">

                            <div class="stat-icon">
                                <i class="bi bi-cart-check"></i>
                            </div>

                            <div class="stat-label">
                                Total Sales
                            </div>

                            <div class="stat-value">
                                ৳125K
                            </div>

                        </div>

                    </div>


                    <div class="col-6 col-lg-3">

                        <div class="stat-card">

                            <div class="stat-icon">
                                <i class="bi bi-cart-plus"></i>
                            </div>

                            <div class="stat-label">
                                Purchases
                            </div>

                            <div class="stat-value">
                                ৳89K
                            </div>

                        </div>

                    </div>

                </div>

                <p class="preview-caption">
                    Example dashboard view. Figures are for illustration only.
                </p>

            </div>

        </div>

    </div>

</section>



<!-- ================================
     FEATURES
================================= -->

<section class="features"
         id="features">

    <div class="container">


        <div class="section-title">

            <h2>
                Everything you need
            </h2>

            <p>
                Manage your complete inventory operation
                from a single, organized platform.
            </p>

        </div>


        <div class="row g-4">


            <!-- Products -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-box-seam"></i>

                    </div>

                    <h5>
                        Product Management
                    </h5>

                    <p>
                        Manage products, categories, units,
                        pricing and product information easily.
                    </p>

                </div>

            </div>


            <!-- Stock -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-boxes"></i>

                    </div>

                    <h5>
                        Stock Management
                    </h5>

                    <p>
                        Monitor stock levels, adjustments,
                        stock history and low-stock products.
                    </p>

                </div>

            </div>


            <!-- Sales -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-cart-check"></i>

                    </div>

                    <h5>
                        Sales Management
                    </h5>

                    <p>
                        Manage sales, invoices, sales returns
                        and customer transactions efficiently.
                    </p>

                </div>

            </div>


            <!-- Purchases -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-cart-plus"></i>

                    </div>

                    <h5>
                        Purchase Management
                    </h5>

                    <p>
                        Track purchases, suppliers,
                        purchase returns and incoming stock.
                    </p>

                </div>

            </div>


            <!-- Customers -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-people"></i>

                    </div>

                    <h5>
                        Customer Management
                    </h5>

                    <p>
                        Keep customer information and
                        transaction records organized.
                    </p>

                </div>

            </div>


            <!-- Reports -->

            <div class="col-md-6 col-lg-4">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="bi bi-bar-chart-line"></i>

                    </div>

                    <h5>
                        Reports & Analytics
                    </h5>

                    <p>
                        Get useful insights through sales,
                        purchase, stock and profit reports.
                    </p>

                </div>

            </div>


        </div>

    </div>

</section>



<!-- ================================
     CTA
================================= -->

<section class="cta">

    <div class="container text-center">

        <h2>
            Ready to manage your inventory?
        </h2>

        <p>
            Start managing your products, stock and
            business operations efficiently.
        </p>


        @auth

            <a href="{{ route('dashboard') }}"
               class="primary-btn">

                <i class="bi bi-grid-1x2-fill"></i>

                Open Dashboard

            </a>

        @else

            <a href="{{ route('login') }}"
               class="primary-btn">

                <i class="bi bi-box-arrow-in-right"></i>

                Get Started

            </a>

        @endauth

    </div>

</section>



<!-- ================================
     FOOTER
================================= -->

<footer>

    <div class="container">

        <div class="d-flex flex-column flex-md-row
                    align-items-center
                    justify-content-between
                    gap-2">

            <span>
                © {{ date('Y') }} Inventory Management System
            </span>

            <span>
                Built with Laravel & Bootstrap
            </span>

        </div>

    </div>

</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
```
