<div>

	<div class="card mb-3">
		<div class="card-header bg-primary text-white py-1">
			<p class="mb-0"><strong>Geplante Aufgaben</strong></p>
		</div>

		<div class="card-body p-0">
			<div class="list-group list-group-flush">
				@forelse($tasks as $task)
					<div class="list-group-item d-flex justify-content-between align-items-start flex-column flex-sm-row gap-2">
						<div class="flex-grow-1">
							<div><strong>{{ $task['command'] }}</strong></div>
							<div class="small text-muted">
								<strong>Nächste Ausführung:</strong> {{ $task['nextRun'] }}
							</div>
						</div>

						<div class="text-sm-end">
							<button wire:click="run('{{ $task['command'] }}')"
									class="btn btn-sm btn-primary d-inline-flex align-items-center"
									wire:loading.attr="disabled"
									wire:target="run"
									@if($running) disabled @endif>

								<span wire:loading.remove wire:target="run('{{ $task['command'] }}')">
									Ausführen
								</span>
								<span wire:loading wire:target="run('{{ $task['command'] }}')">
									<i class="mdi mdi-loading mdi-spin me-1"></i> Läuft...
								</span>
							</button>
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

    <div class="card mb-3">
        <div class="card-header bg-primary text-white py-1">
            <p class="mb-0"><strong>Befehle</strong></p>
        </div>

        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($commands as $cmd => $meta)
                    <div class="list-group-item d-flex justify-content-between align-items-start flex-column flex-sm-row gap-2">
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
