<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}"/>

    <!-- Preconnect for fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Loader CSS (must be in HEAD) -->
    <link href="{{ asset('layouts/vertical-light-menu/css/light/loader.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/dark/loader.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('layouts/vertical-light-menu/loader.js') }}"></script>
    {{-- CRITICAL FIX: Permissions Policy untuk Camera --}}
    <meta http-equiv="Permissions-Policy" content="camera=(self), microphone=()">
    <!-- GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700&display=swap" rel="stylesheet">
    <link href="{{ asset('src/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/light/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/dark/plugins.css') }}" rel="stylesheet" type="text/css" />

    <!-- Additional Component Styles -->
    <link href="{{ asset('src/assets/css/light/components/modal.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/dark/components/modal.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/src/tomSelect/tom-select.default.min.css') }}">
    @stack('styles')
</head>
<body class="{{ $bodyClass ?? 'layout-boxed' }}">

    <!-- LOADER -->
    <x-loader />

    <!-- NAVBAR -->
    <x-navbar />

    <!-- MAIN CONTAINER -->
    <div class="main-container" id="container">

        <div class="overlay"></div>
        <div class="cs-overlay"></div>
        <div class="search-overlay"></div>

        <!-- SIDEBAR -->
        <x-sidebar />

        <!-- CONTENT -->
        <div id="content" class="main-content">
            <div class="layout-px-spacing">
                <div class="middle-content container-xxl p-0">

                    @if(isset($breadcrumbs))
                        <x-breadcrumb :items="$breadcrumbs" />
                    @endif

                    @yield('content')

                </div>
            </div>

            <x-footer />
        </div>

    </div>
    <!-- END MAIN CONTAINER -->

    <!-- GLOBAL JS -->
    <script src="{{ asset('plugins/src/global/vendors.min.js') }}"></script>
    <script src="{{ asset('bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/src/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('plugins/src/mousetrap/mousetrap.min.js') }}"></script>
    <script src="{{ asset('plugins/src/waves/waves.min.js') }}"></script>
    <script src="{{ asset('plugins/src/tomSelect/tom-select.base.js') }}"></script>
    <!-- APP MAIN JS -->
    <script src="{{ asset('layouts/vertical-light-menu/app.js') }}"></script>

    <!-- FIX: init App setelah load -->
    <script>
        window.addEventListener('load', function () {
            if (typeof App !== "undefined" && typeof App.init === "function") {
                try {
                    App.init();
                } catch (e) {
                    console.warn("App.init() error:", e);
                }
            }
        });
    </script>

    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script src="{{ asset('layouts/vertical-light-menu/app.js') }}"></script>
    <script src="{{ asset('js/health-check-helper.js') }}"></script>
    @stack('scripts')

</body>
</html>
