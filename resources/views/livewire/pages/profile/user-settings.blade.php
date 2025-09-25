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
                            Dark Mode bei Login
                        </label>
                    </div>
                    <small class="text-muted d-block">
                        Aktiviert beim Login den Dark Mode
                    </small>
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
