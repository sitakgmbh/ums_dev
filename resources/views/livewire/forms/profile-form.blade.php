<div>
    <form wire:submit.prevent="save">
        @php
            $isLdap = $form->auth_type === 'ldap';
        @endphp

        {{-- Stammdaten --}}
        <div class="card text-white bg-primary mb-3">
            <div class="card-header py-2">
                <p class="mb-0"><strong>Stammdaten</strong></p>
            </div>
            <div class="card-body bg-white text-dark">

                {{-- Vorname --}}
                <div class="mb-3">
                    <label for="firstname" class="form-label">Vorname</label>
                    <input type="text"
                           id="firstname"
                           wire:model.defer="form.firstname"
                           class="form-control @error('form.firstname') is-invalid @enderror"
                           @if($isLdap) disabled @endif>
                    @error('form.firstname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Nachname --}}
                <div class="mb-3">
                    <label for="lastname" class="form-label">Nachname</label>
                    <input type="text"
                           id="lastname"
                           wire:model.defer="form.lastname"
                           class="form-control @error('form.lastname') is-invalid @enderror"
                           @if($isLdap) disabled @endif>
                    @error('form.lastname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- E-Mail --}}
                <div class="mb-0>
                    <label for="email" class="form-label">E-Mail</label>
                    <input type="email"
                           id="email"
                           wire:model.defer="form.email"
                           class="form-control @error('form.email') is-invalid @enderror"
                           @if($isLdap) disabled @endif>
                    @error('form.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        {{-- Passwort nur für lokale User --}}
        @if($form->auth_type === 'local')
            <div class="card text-white bg-primary mb-3">
                <div class="card-header py-2">
                    <p class="mb-0"><strong>Passwort ändern</strong></p>
                </div>
                <div class="card-body bg-white text-dark">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Aktuelles Passwort</label>
                        <input wire:model.defer="form.current_password" type="password"
                               id="current_password"
                               class="form-control @error('form.current_password') is-invalid @enderror">
                        @error('form.current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Neues Passwort</label>
                        <input wire:model.defer="form.password" type="password"
                               id="password"
                               class="form-control @error('form.password') is-invalid @enderror">
                        @error('form.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-0">
                        <label for="password_confirmation" class="form-label">Passwort bestätigen</label>
                        <input wire:model.defer="form.password_confirmation" type="password"
                               id="password_confirmation"
                               class="form-control">
                    </div>
                </div>
            </div>
        @endif

        {{-- Alerts --}}
        @if (session()->has('success'))
            <div class="mb-3">
                <div class="alert alert-success mb-0" role="alert">
                    <strong>Erfolg – </strong> {{ session('success') }}
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-3">
                <div class="alert alert-danger mb-0" role="alert">
                    <strong>Fehler – </strong> Bitte Eingaben überprüfen.
                </div>
            </div>
        @endif

        {{-- Submit --}}
        <div class="mt-3">
            <button type="submit"
                    class="btn btn-primary"
                    wire:loading.attr="disabled"
                    @if($form->auth_type === 'ldap') disabled @endif>
                <span wire:loading.remove wire:target="save">Speichern</span>
                <span wire:loading wire:target="save">
                    <i class="mdi mdi-loading mdi-spin me-1"></i>Bitte warten...
                </span>
            </button>
        </div>
    </form>
</div>
