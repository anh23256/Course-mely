<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title> {{ !empty($title) ? $title : 'Dashboard' }} - CourseMeLy </title>
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    {{-- <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-9MJ6G4PMEJ"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-9MJ6G4PMEJ');
    </script> --}}
    <!-- CSS -->
    @include('layouts.partials.css')

    @stack('page-css')
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            @include('layouts.partials.topbar')
        </header>

        <!-- removeNotificationModal -->
        {{-- <div id="removeNotificationModal" class="modal fade zoomIn" tabindex="-1" aria-hidden="true">
            @include('layouts.partials.remove-notifice')
        </div> --}}

        <!-- /.modal -->
        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                @include('layouts.partials.logo')
            </div>

            <div id="scrollbar">
                @include('layouts.partials.sidebar')
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                @yield('content')
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                @include('layouts.partials.footer')
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    {{-- @include('layouts.partials.theme-settings') --}}
    <!-- JAVASCRIPT -->
    @include('layouts.partials.scripts')

    @stack('page-scripts')

    @yield('scripts')
</body>

</html>
