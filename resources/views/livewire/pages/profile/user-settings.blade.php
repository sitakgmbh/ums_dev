<div>
    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-body">
                {{-- Darkmode --}}
                <div class="border rounded p-3 bg-body-tertiary">
                    <div class="form-check form-switch mb-1">
                        <input type="checkbox"
                               class="form-check-input"
                               id="darkmodeSwitch"
                               wire:model="darkmode_enabled">
                        <label for="darkmodeSwitch" class="form-check-label fw-semibold">
                            Dark Mode
                        </label>
                    </div>
                    <small class="text-muted d-block">Aktiviere ein dunkles Farbschema für die gesamte Anwendung. Kann auch über die Topbar gesteuert werden.</small>
                </div>
            </div>
        </div>

    {{-- Erfolgsmeldung --}}
    @if (session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

        {{-- Button direkt unter der Card --}}
        <div class="mt-3">
            <button type="submit" class="btn btn-primary">
                Speichern
            </button>
        </div>
    </form>
</div>
