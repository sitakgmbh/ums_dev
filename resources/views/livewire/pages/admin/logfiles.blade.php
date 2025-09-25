<div>
    @if($status)
        <div class="alert alert-success">{{ $status }}</div>
    @endif

    @forelse ($files as $index => $file)
        <div class="card mb-4 shadow-sm">
            {{-- Header --}}
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div><strong>{{ $file['name'] }}</strong></div>
                <div class="small text-white">
                    Letzte Änderung: {{ $file['updated'] }}
                </div>
            </div>

            {{-- Inhalt --}}
            <div class="card-body p-0">
                <pre id="log-content-{{ $index }}"
                     class="mb-0 p-3 bg-light"
                     style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.875rem;">
{{ $file['content'] }}
                </pre>
            </div>

            {{-- Footer --}}
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Grösse: {{ $file['size'] }}
                </div>
                <div>
                    {{-- Download --}}
					<button type="button"
							wire:click="download('{{ $file['name'] }}')"
							class="btn btn-sm btn-primary me-2">
						<i class="mdi mdi-download"></i> Download
					</button>

					{{-- Delete --}}
					<button 
						type="button"
						wire:click="$dispatch('open-modal', { modal: 'logfile-delete-modal', payload: { filename: @js($file['name']) } })"
						class="btn btn-sm btn-danger">
						<i class="mdi mdi-delete"></i> Löschen
					</button>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Keine Logfiles gefunden.</div>
    @endforelse

    {{-- Modals --}}
    <livewire:components.modals.logfile-delete />
	<livewire:components.modals.alert-modal />
</div>

@push('scripts')
<script>
    document.addEventListener("livewire:navigated", () => {
        document.querySelectorAll('[id^="log-content-"]').forEach(pre => {
            pre.scrollTop = pre.scrollHeight;
        });
    });

    document.addEventListener("livewire:update", () => {
        document.querySelectorAll('[id^="log-content-"]').forEach(pre => {
            pre.scrollTop = pre.scrollHeight;
        });
    });
</script>
@endpush
