@extends('livewire.components.modals.base-modal')

@section('body')
    @if($filename)
        Soll das Logfile <strong>{{ $filename }}</strong> wirklich gelöscht werden?
        <div class="mt-2 text-muted small">
            <i class="mdi mdi-information-outline"></i> Dieser Vorgang kann nicht rückgängig gemacht werden.
        </div>
    @endif
@endsection


@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-danger">Löschen</x-action-button>
@endsection
