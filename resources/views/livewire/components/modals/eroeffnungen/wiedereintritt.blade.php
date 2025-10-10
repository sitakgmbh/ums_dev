@extends("livewire.components.modals.base-modal")

@section("body")
    @if(!empty($adusers))
        <p class="mb-2">
            Es wurden bereits Benutzer mit dem gleichen Vor- und Nachnamen gefunden. Bitte prüfe, ob einer davon wiederverwendet werden soll:
        </p>

        @if($selectedUserId && $selectedUserEnabled)
            <div class="alert alert-warning mt-0 mb-2">
                Es existiert bereits ein aktiver Benutzer. 
                Erstelle bitte eine <a href="/mutationen/create" class="alert-link">Benutzermutation</a>.
            </div>
        @endif

        <div class="row g-2">
            @foreach($adusers as $user)
                <div class="col-md-6">
                    <div class="card @if($selectedUserId === $user['id']) border-primary @endif h-100">
                        <div class="card-body pt-2 pb-0">
                            <div class="form-check">
                                <input type="radio"
                                       wire:click="selectUser({{ $user['id'] }})"
                                       name="aduser"
                                       id="aduser_{{ $user['id'] }}"
                                       value="{{ $user['id'] }}"
                                       class="form-check-input"
                                       @if($selectedUserId === $user['id']) checked @endif>

                                <label class="form-check-label fw-bold" for="aduser_{{ $user['id'] }}">
                                    {{ $user["vorname"] }} {{ $user["nachname"] }}
                                </label>
                            </div>

                            <ul class="list-unstyled small mt-2 mb-0">
                                <li><strong>E-Mail-Adresse:</strong> {{ $user["email"] ?? "-" }}</li>
                                <li><strong>Personalnummer:</strong> {{ $user["initials"] ?? "-" }}</li>
                                <li><strong>Funktion:</strong> {{ $user["beschreibung"] ?? "-" }}</li>
                                <li>
                                    <strong>PC-Login:</strong>
                                    @if($user["enabled"])
                                        <span class="badge bg-success">Aktiviert</span>
                                    @else
                                        <span class="badge bg-secondary">Deaktiviert</span>
                                    @endif
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Kein Wiedereintritt</button>
    <button type="button" class="btn btn-primary" wire:click="confirm" @disabled(!$selectedUserId || $selectedUserEnabled)>Ja, Wiedereintritt</button>
@endsection
