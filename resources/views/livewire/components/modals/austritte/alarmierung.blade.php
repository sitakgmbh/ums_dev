@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Bitte bearbeite die Aktion manuell und bestätige die getätigten Arbeiten hier.</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
