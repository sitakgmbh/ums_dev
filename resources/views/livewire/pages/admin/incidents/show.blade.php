<div>
    {{-- Incident Details --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
					<dl class="row mb-0">
						<dt class="col-sm-2">Titel:</dt>
						<dd class="col-sm-10">{{ $incident->title }}</dd>

						<dt class="col-sm-2">Priorität:</dt>
						<dd class="col-sm-10">
							<span class="badge 
								{{ $incident->priority === 'critical' ? 'bg-danger' : '' }}
								{{ $incident->priority === 'high' ? 'bg-warning' : '' }}
								{{ $incident->priority === 'medium' ? 'bg-info' : '' }}
								{{ $incident->priority === 'low' ? 'bg-secondary' : '' }}">
								{{ ucfirst($incident->priority) }}
							</span>
						</dd>

						<dt class="col-sm-2">Erstellt von:</dt>
						<dd class="col-sm-10">{{ $incident->creator?->firstname ?? '' }} {{ $incident->creator?->lastname ?? '' }}</dd>

						<dt class="col-sm-2">Erstellt am:</dt>
						<dd class="col-sm-10">{{ $incident->created_at->format('d.m.Y H:i') }}</dd>

						<dt class="col-sm-2">Gelöst von:</dt>
						<dd class="col-sm-10">{{ $incident->resolver?->firstname ?? '' }} {{ $incident->resolver?->lastname ?? '' }}</dd>

						<dt class="col-sm-2">Gelöst am:</dt>
						<dd class="col-sm-10">{{ $incident->resolved_at?->format('d.m.Y H:i') ?? '-' }}</dd>

						<dt class="col-sm-2">Beschreibung:</dt>
						<dd class="col-sm-10 mb-0">{{ $incident->description ?? '-' }}</dd>
					</dl>

                    @if($incident->metadata)
                        <p class="mt-2 mb-1"><strong>Metadata:</strong></p>
<pre class="bg-light p-3 rounded mb-0" style="font-size:0.85rem; overflow-x:auto;">
{{ rtrim(json_encode($incident->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) }}
</pre>
                    @endif
                </div>
            </div>


        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('admin.incidents.index') }}" class="btn btn-secondary me-2">
                <i class="mdi mdi-arrow-left"></i> Zurück
            </a>
            @if(!$incident->resolved_at)
                <button type="button"
                        class="btn btn-success"
                        wire:click="resolveIncident"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="resolveIncident">
                        <i class="mdi mdi-check"></i> Abschliessen
                    </span>
                    <span wire:loading wire:target="resolveIncident">
                        <i class="mdi mdi-loading mdi-spin"></i> Bitte warten
                    </span>
                </button>
            @endif
        </div>
			
        </div>
    </div>
</div>
