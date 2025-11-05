@extends('livewire.components.modals.base-modal')

@section('body')
    @if($incident)
        <div class="mb-2">
            <strong>Titel:</strong><br>
            {{ $incident->title }}
        </div>

        <div class="mb-2">
            <strong>Priorität:</strong><br>
            @if($incident->priority === 'critical')
                <span class="badge bg-danger">Critical</span>
            @elseif($incident->priority === 'high')
                <span class="badge bg-warning text-dark">High</span>
            @elseif($incident->priority === 'medium')
                <span class="badge bg-info text-dark">Medium</span>
            @else
                <span class="badge bg-secondary">Low</span>
            @endif
        </div>

        <div class="mb-2">
            <strong>Erstellt von:</strong><br>
            {{ $incident->creator?->firstname ?? 'System' }} {{ $incident->creator?->lastname ?? '' }}
        </div>

        <div class="mb-2">
            <strong>Erstellt am:</strong><br>
            {{ $incident->created_at->format('d.m.Y H:i') }}
        </div>

        <div class="mb-2">
            <strong>Gelöst von:</strong><br>
            {{ $incident->resolver?->firstname ?? 'System' }} {{ $incident->resolver?->lastname ?? '' }}
        </div>

        <div class="mb-2">
            <strong>Gelöst am:</strong><br>
            {{ $incident->resolved_at->format('d.m.Y H:i') }}
        </div>

        <div class="mb-2">
            <strong>Beschreibung:</strong><br>
            {{ $incident->description ?? '-' }}
        </div>

        @if($incident->metadata)
            <div class="mt-3">
                <strong>Metadata:</strong>
                <pre class="bg-light p-3 rounded"
                     style="max-height: 400px; overflow-y: auto; font-size: 0.85rem; white-space: pre;">
{!! json_encode($incident->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
                </pre>
            </div>
        @endif
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection
