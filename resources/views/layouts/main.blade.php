<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>InternshipKaro - Learn. Intern. Grow. Succeed.</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* The InternshipKaro logo is designed for a navy background. */
        .main_menu, .menu_fixed { background: #002338; }
        .main_menu .navbar { padding: 10px 0; }
        .main_menu .navbar-brand img { height: 56px; width: auto; }
        .main_menu .main-menu-item ul li .nav-link,
        .single_page_menu .main-menu-item ul li .nav-link,
        .menu_fixed .main-menu-item ul li .nav-link { color: #fff; font-weight: 500; }
        .main_menu .main-menu-item ul li .nav-link:hover,
        .main_menu .main-menu-item ul li .nav-link:focus { color: #f26a21; }
        .main_menu .main-menu-item ul .btn_1 { background: #f26a21; border-color: #f26a21; }
        .main_menu .navbar-toggler-icon { background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='%23fff' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E"); }
        @media (max-width: 991px) {
            .main_menu .navbar-collapse { background-color: #0a3a5c !important; }
            .single_page_menu .navbar-collapse ul li .nav-link { color: #fff !important; }
        }
        .menu_fixed .logo_1 { display: inherit; }
        .menu_fixed .logo_2 { display: none; }
    </style>
</head>

<body>
    @include('partials.header')

    @includeWhen($breadcrumb ?? null, 'partials.breadcrumb')

    @yield('content')

    @include('partials.footer')

    <script src="{{ asset('js/jquery-1.12.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.js') }}"></script>
    <script src="{{ asset('js/swiper.min.js') }}"></script>
    <script src="{{ asset('js/masonry.pkgd.js') }}"></script>
    <script src="{{ asset('js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('js/slick.min.js') }}"></script>
    <script src="{{ asset('js/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('js/waypoints.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
</body>

</html>