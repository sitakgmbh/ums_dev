<div class="account-pages pt-2 pt-sm-5 pb-4 pb-sm-5 position-relative">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xxl-4 col-lg-5">
                <div class="card">

                    <div class="card-header py-4 text-center bg-primary">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="logo" height="22">
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <div class="text-center w-75 m-auto">
                            <h4 class="text-dark-50 fw-bold">Passwort vergessen?</h4>
                            <p class="text-muted mb-4">
                                Gib deine E-Mail-Adresse ein und wir senden dir einen Link zum Zurücksetzen.
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success text-center">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form wire:submit.prevent="submit" autocomplete="off">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">E-Mail-Adresse</label>
                                <input wire:model.defer="email" type="email" id="email" class="form-control" required autofocus>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="mb-3 d-grid text-center">
                                <button class="btn btn-primary" type="submit">Link senden</button>
                            </div>
                        </form>
                    </div> <!-- end card-body -->

                </div> <!-- end card -->
            </div>
        </div>
    </div>
</div>
