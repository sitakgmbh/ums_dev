<div class="card p-4">
    @if (session()->has('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="save" autocomplete="off">
        <h5 class="mb-3">Allgemein</h5>
        <div class="form-check mb-3">
            <input type="checkbox" id="debug_mode" class="form-check-input" wire:model.defer="debug_mode">
            <label for="debug_mode" class="form-check-label">Debug Mode aktivieren</label>
            <small class="form-text text-muted">
                Aktiviert zusätzliche Debug-Ausgaben und detaillierte Fehlermeldungen.
            </small>
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
</div>
