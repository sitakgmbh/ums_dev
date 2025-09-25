<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <title>{{ $pageTitle ?? config('app.name') }} – Anmeldung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Theme Config -->
    <script src="{{ asset('assets/js/hyper-config.js') }}"></script>

    <!-- Vendor CSS -->
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ asset('assets/css/unicons/css/unicons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/remixicon/remixicon.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/mdi/css/materialdesignicons.min.css') }}" rel="stylesheet" />

    @stack('head')
    @livewireStyles
</head>
<body class="authentication-bg position-relative">

    <div class="position-absolute start-0 end-0 bottom-0 w-100 h-100">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 800 800">
            <g fill-opacity="0.22">
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.1);" cx="400" cy="400" r="600"/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.2);" cx="400" cy="400" r="500"/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.3);" cx="400" cy="400" r="300"/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.4);" cx="400" cy="400" r="200"/>
                <circle style="fill: rgba(var(--ct-primary-rgb), 0.5);" cx="400" cy="400" r="100"/>
            </g>
        </svg>
    </div>

    {{-- Hier kommt Livewire-Page rein --}}
    {{ $slot }}

    <footer class="footer footer-alt text-center">
        &copy; {{ date('Y') }} Sitak GmbH
    </footer>

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>

    @livewireScripts
    @stack('scripts')
</body>
</html>
