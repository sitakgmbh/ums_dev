@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Bitte lege den Benutzer manuell im entsprechenden System an. Anschliessend kannst du hier die erfolgreiche Erstellung des Benutzers für <strong>{{ $entry->vorname }} {{ $entry->nachname }}</strong> bestätigen.</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
