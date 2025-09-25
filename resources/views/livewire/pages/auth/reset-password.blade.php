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
                            <h4 class="text-dark-50 fw-bold">Neues Passwort setzen</h4>
                            <p class="text-muted mb-4">Bitte gib dein neues Passwort ein.</p>
                        </div>

                        <form wire:submit.prevent="submit" autocomplete="off">
                            @csrf

                            <!-- E-Mail -->
                            <div class="mb-3">
                                <label for="email" class="form-label">E-Mail-Adresse</label>
                                <input wire:model.defer="email" type="email" id="email" class="form-control" required readonly>
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Neues Passwort -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Neues Passwort</label>
                                <div class="input-group input-group-merge">
                                    <input wire:model.defer="password" type="password" id="password" class="form-control" required>
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <!-- Passwort bestätigen -->
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Passwort bestätigen</label>
                                <div class="input-group input-group-merge">
                                    <input wire:model.defer="password_confirmation" type="password" id="password_confirmation" class="form-control" required>
                                    <div class="input-group-text" data-password="false">
                                        <span class="password-eye"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 d-grid text-center">
                                <button class="btn btn-primary" type="submit">Passwort speichern</button>
                            </div>
                        </form>
                    </div> <!-- end card-body -->

                </div> <!-- end card -->
            </div>
        </div>
    </div>
</div>
