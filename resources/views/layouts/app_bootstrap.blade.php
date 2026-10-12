<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>
        @yield('title', 'Inventory System')
    </title>

    <!-- Bootstrap CSS -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Dashboard CSS -->

    <link rel="stylesheet"
          href="{{ asset('build/assets/css/element/dashboard.css') }}">

    <link rel="stylesheet"
          href="{{ asset('build/assets/css/element/sidebar.css') }}">

    <link rel="stylesheet"
          href="{{ asset('build/assets/css/element/navbar.css') }}">

    <link rel="stylesheet"
          href="{{ asset('build/assets/css/element/responsive.css') }}">

    {{-- <link rel="stylesheet"
          href="{{ asset('build/assets/css/product/product-form.css') }}">  
    <link rel="stylesheet"
          href="{{ asset('build/assets/css/product/product-list.css') }}">       --}}

     <link rel="stylesheet"
          href="{{ asset('build/assets/css/global/global_list.css') }}">

      <link rel="stylesheet"
          href="{{ asset('build/assets/css/global/global_form.css') }}">

     <link rel="stylesheet"
          href="{{ asset('build/assets/css/component/advance_filter.css') }}">


    <script src="{{ asset('build/assets/js/dashboard.js') }}"></script>
    <script src="{{ asset('build/assets/js/component/advance_filter.js') }}"></script>

    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

</head>

<body>

    {{-- Sidebar --}}
    @include('partials.sidebar_b')

    {{-- Main --}}
    <div class="main-wrapper">

        {{-- Navbar --}}
        @include('partials.navbar_b')

        {{-- Page Content --}}
        <main class="content-wrapper">

            @yield('content')

        </main>

        {{-- Footer --}}
        @include('partials.footer_b')

    </div>

    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dashboard JS -->

    {{-- <script src="{{ asset('assets/js/dashboard.js') }}"></script> --}}

    @stack('scripts')

</body>

</html>