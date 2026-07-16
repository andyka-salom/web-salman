@php
// Default messages berdasarkan status code
$defaultMessages = [
    400 => 'Bad request. Please check your input and try again.',
    401 => 'You are not authorized to access this page.',
    403 => 'Access forbidden. You don\'t have permission to access this resource.',
    404 => 'The page you requested was not found!',
    405 => 'Method not allowed for this request.',
    419 => 'Page expired. Please refresh and try again.',
    429 => 'Too many requests. Please slow down.',
    500 => 'Internal server error. Something went wrong on our end.',
    503 => 'Service unavailable. We\'ll be back soon!',
];

$statusCode = $exception->getStatusCode() ?? 404; // Assuming $exception is available, or fallback to 404
$defaultMessage = $defaultMessages[$statusCode] ?? 'An error occurred while processing your request.';

// Title berdasarkan status code
$errorTitles = [
    401 => 'Unauthorized!',
    403 => 'Forbidden!',
    404 => 'Ooops!',
    419 => 'Page Expired!',
    429 => 'Too Many Attempts!',
    500 => 'Server Error!',
    503 => 'Maintenance Mode!',
];

$defaultTitle = $errorTitles[$statusCode] ?? 'Ooops!';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>Error {{ $statusCode ?? '404' }} | {{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('src/assets/img/favicon.ico') }}"/>
    <link href="{{ asset('layouts/vertical-light-menu/css/light/loader.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/dark/loader.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('layouts/vertical-light-menu/loader.js') }}"></script>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset('src/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/light/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/light/pages/error/error.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('layouts/vertical-light-menu/css/dark/plugins.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/dark/pages/error/error.css') }}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->

    <style>
        body.dark .theme-logo.dark-element {
            display: inline-block;
        }
        .theme-logo.dark-element {
            display: none;
        }
        body.dark .theme-logo.light-element {
            display: none;
        }
        .theme-logo.light-element {
            display: inline-block;
        }

        .error-content {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .error-number {
            font-size: 10rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .mini-text {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .error-text {
            font-size: 1.1rem;
            color: #888;
            margin-bottom: 2rem;
        }

        .error-img {
            max-width: 400px;
            width: 100%;
            height: auto;
            margin: 2rem 0;
        }

        @media (max-width: 768px) {
            .error-number {
                font-size: 6rem;
            }
            .error-img {
                max-width: 300px;
            }
        }
    </style>
</head>
<body class="error text-center">
    <!-- BEGIN LOADER -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    <!--  END LOADER -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4 mr-auto mt-5 text-md-left text-center">
                <a href="{{ url('/') }}" class="ml-md-5">
                    <img alt="logo" src="{{ asset('src/assets/img/logo.svg') }}" class="dark-element theme-logo">
                    <img alt="logo" src="{{ asset('src/assets/img/logo2.svg') }}" class="light-element theme-logo">
                </a>
            </div>
        </div>
    </div>

    <div class="container-fluid error-content">
        <div class="">
            <h1 class="error-number">{{ $statusCode ?? '404' }}</h1>
            <p class="mini-text">{{ $errorTitle ?? $defaultTitle }}</p>
            <p class="error-text mb-5 mt-1">{{ $errorMessage ?? $message ?? $defaultMessage }}</p>

            @if(($showImage ?? true))
                <img src="{{ asset('src/assets/img/error.svg') }}"
                     alt="Error {{ $statusCode ?? '404' }}"
                     class="error-img"
                     onerror="this.style.display='none'">
            @endif

            <a href="{{ $backUrl ?? url('/') }}" class="btn btn-dark mt-5">
                {{ $backText ?? 'Go Back' }}
            </a>

            @if(($showHomeButton ?? false) && ($backUrl ?? url('/')) !== url('/'))
                <a href="{{ url('/') }}" class="btn btn-outline-dark mt-5 ms-2">
                    Go Home
                </a>
            @endif
        </div>
    </div>

    <!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
    <script src="{{ asset('src/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- END GLOBAL MANDATORY SCRIPTS -->
</body>
</html>
