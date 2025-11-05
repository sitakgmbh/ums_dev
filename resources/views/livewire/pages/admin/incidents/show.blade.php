<div>
    {{-- Incident Details --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <p><strong>Titel:</strong> {{ $incident->title }}</p>

                    <p><strong>Priorität:</strong>
                        <span class="badge 
                            {{ $incident->priority === 'critical' ? 'bg-danger' : '' }}
                            {{ $incident->priority === 'high' ? 'bg-warning' : '' }}
                            {{ $incident->priority === 'medium' ? 'bg-info' : '' }}
                            {{ $incident->priority === 'low' ? 'bg-secondary' : '' }}">
                            {{ ucfirst($incident->priority) }}
                        </span>
                    </p>

                    <p><strong>Erstellt von:</strong> {{ $incident->creator?->firstname ?? 'System' }} {{ $incident->creator?->lastname ?? '' }}</p>
                    <p><strong>Erstellt am:</strong> {{ $incident->created_at->format('d.m.Y H:i') }}</p>

                    <p><strong>Gelöst von:</strong> {{ $incident->resolver?->firstname ?? 'System' }} {{ $incident->resolver?->lastname ?? '' }}</p>
                    <p><strong>Gelöst am:</strong> {{ $incident->resolved_at?->format('d.m.Y H:i') ?? '-' }}</p>

                    <p><strong>Beschreibung:</strong><br>{{ $incident->description ?? '-' }}</p>

                    @if($incident->metadata)
                        <p><strong>Metadata:</strong></p>
                        <pre class="bg-light p-3 rounded" style="font-size:0.85rem; overflow-x:auto;">
{{ json_encode($incident->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                        </pre>
                    @endif
                </div>
            </div>

            {{-- Button zum Abschliessen --}}
            @if(!$incident->resolved_at)
                <div class="mb-3">
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
                </div>
            @endif
        </div>
    </div>
</div>
