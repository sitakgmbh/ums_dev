<div>
    {{-- Geplante Aufgaben (nur Anzeige, kein Button) --}}
    <div class="card mb-3">
        <div class="card-header bg-primary text-white py-1">
            <p class="mb-0"><strong>Geplante Aufgaben</strong></p>
        </div>
        <div class="card-body p-0">
			<div class="list-group list-group-flush">
				@forelse($tasks as $task)
					<div class="list-group-item">
						<div>
							<strong>{{ $task['command'] }}</strong>
						</div>
						<div class="text-muted small mt-1">
							{{ $task['description'] }}
						</div>
						<div class="mt-2">
							<div class="mb-1">
								<span class="badge bg-secondary fs-6 py-1 px-2">
									<i class="mdi mdi-clock-outline me-1"></i>{{ $task['interval'] }}
								</span>
							</div>
							<div class="small text-muted">
								<strong>Nächste Ausführung:</strong> {{ $task['nextRun'] }}
							</div>
						</div>
					</div>
				@empty
					<div class="list-group-item text-muted">
						Keine geplanten Tasks gefunden.
					</div>
				@endforelse
			</div>
        </div>
    </div>

    {{-- Befehle (mit Button) --}}
    <div class="card mb-3">
        <div class="card-header bg-primary text-white py-1">
            <p class="mb-0"><strong>Manuell ausführen</strong></p>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($commands as $cmd => $meta)
                    <div class="list-group-item d-flex justify-content-between align-items-center flex-column flex-sm-row gap-2">
                        <div class="flex-grow-1">
                            <div><strong>{{ $meta['name'] }}</strong></div>
                            <div class="text-muted small">{{ $meta['description'] }}</div>
                        </div>
                        <div class="text-sm-end">
                            <button wire:click="run('{{ $cmd }}')"
                                    class="btn btn-sm btn-primary d-inline-flex align-items-center"
                                    @disabled($running)
                                    wire:loading.attr="disabled"
                                    wire:target="run">
                                <span wire:loading.remove wire:target="run('{{ $cmd }}')">
                                    Ausführen
                                </span>
                                <span wire:loading wire:target="run('{{ $cmd }}')">
                                    <i class="mdi mdi-loading mdi-spin me-1"></i> Läuft...
                                </span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Modals --}}
    <livewire:components.modals.artisan-output />
    <livewire:components.modals.alert-modal />
</div>