@extends('layouts.error')

@section('content')
<div class="account-pages pt-5 pb-5 position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-5 col-lg-6">
                <div class="card text-center">

                    <!-- Logo Header -->
                    <div class="card-header py-4 bg-primary text-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="logo" height="22">
                        </a>
                    </div>

                    <div class="card-body p-5">
                        <h1 class="display-3 text-danger">419</h1>
                        <h2 class="fw-bold">Sicherheitsfehler</h2>
                        <p class="text-muted mt-2">
                            Deine Sitzung ist abgelaufen oder ungültig.<br>
                            Bitte lade die Seite neu und versuche es erneut.
                        </p>

                        <a href="{{ url('/') }}" class="btn btn-primary mt-3">Neu laden</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
