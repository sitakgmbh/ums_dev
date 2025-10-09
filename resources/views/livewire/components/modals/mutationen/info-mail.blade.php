@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Möchtest du die Info-Mail für den Antrag von <strong>{{ $entry->vorname }} {{ $entry->nachname }}</strong> versenden?
        </p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Senden</x-action-button>
@endsection
