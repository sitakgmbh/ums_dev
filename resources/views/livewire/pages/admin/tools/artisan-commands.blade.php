<div class="card p-3">
    <div class="list-group">
        @foreach($commands as $cmd => $meta)
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div><strong>{{ $meta['name'] }}</strong></div>
                    <div class="text-muted small">{{ $meta['description'] }}</div>
                </div>
                <button wire:click="run('{{ $cmd }}')"
                        class="btn btn-sm btn-primary"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="run('{{ $cmd }}')">Ausführen</span>
                    <span wire:loading wire:target="run('{{ $cmd }}')">
                        <i class="mdi mdi-loading mdi-spin me-1"></i> Läuft...
                    </span>
                </button>
            </div>
        @endforeach
    </div>


    {{-- Modals --}}
    <livewire:components.modals.artisan-output />
	<livewire:components.modals.alert-modal />

</div>
