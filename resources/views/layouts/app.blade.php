<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <title>{{ $pageTitle ?? config('app.name') }}</title>
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

@php
    $darkMode = session('darkmode_enabled', false);

    $layoutConfig = json_encode([
        'darkMode' => $darkMode,
    ]);
@endphp

<body class="loading" data-layout="detached" data-layout-config='{{ $layoutConfig }}'>
    <div class="wrapper">
        @livewire('layout.topbar')
        @livewire('layout.sidebar')

        <div class="content-page">
            <div class="content">
                <div class="container-fluid">

                    @if (!empty($pageTitle ?? '') || View::hasSection('pageActions'))
                        <div class="page-title-box d-flex justify-content-between align-items-center">
                            @if (!empty($pageTitle))
                                <h4 class="page-title mb-0">{{ $pageTitle }}</h4>
                            @endif

                            {{-- Page Actions --}}
                            <div class="page-actions">
                                @yield('pageActions')
                            </div>
                        </div>
                    @endif

                    {{-- Seiteninhalt --}}
                    {{ $slot }}

                </div>
            </div>

            @livewire('layout.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
	<script src="{{ asset('assets/js/custom.js') }}"></script>

	<livewire:components.modals.modal-manager />

    @livewire('actions.logout')

    @livewireScripts
    @stack('scripts')

</body>
</html>

