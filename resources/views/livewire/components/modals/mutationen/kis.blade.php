@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Bitte bearbeite den Benutzer manuell im entsprechenden System. Anschliessend kannst du hier die erfolgreiche Mutation des Benutzers <strong>{{ $entry->vorname }} {{ $entry->nachname }}</strong> bestätigen.</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
