<div class="card p-3">
    @if($status)
        <div class="alert alert-{{ $statusType }}">{{ $status }}</div>
    @endif

    <form wire:submit.prevent="send">
        <div class="mb-3">
            <label for="to" class="form-label">Empfänger</label>
            <input type="email" id="to" class="form-control" wire:model.defer="to" placeholder="test@example.com">
            @error('to') <small class="text-danger">{{ $message }}</small> @enderror
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="send">Testmail senden</span>
                <span wire:loading wire:target="send">
                    <i class="mdi mdi-loading mdi-spin me-1"></i> Wird gesendet...
                </span>
            </button>

            <a class="btn btn-secondary" data-bs-toggle="collapse" href="#mailConfig" role="button"
               aria-expanded="false" aria-controls="mailConfig">
                Mail-Konfiguration
            </a>
        </div>
    </form>

    {{-- Aktuelle Mail Config --}}
    <div class="collapse mt-3" id="mailConfig">
        <div class="border rounded p-2 bg-light">
            <pre class="mb-0" style="font-size: 0.85rem;">
{{ json_encode($mailConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
            </pre>
        </div>
    </div>
</div>
