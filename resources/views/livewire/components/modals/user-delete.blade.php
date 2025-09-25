@extends('livewire.components.modals.base-modal')

@section('body')
    @if($user)
        Soll der Benutzer <strong>{{ $user->firstname }} {{ $user->lastname }}</strong> wirklich gelöscht werden?
        <div class="mt-2 text-muted small">
            <i class="mdi mdi-information-outline"></i> Dieser Vorgang wird protokolliert.
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <button type="button" class="btn btn-danger" wire:click="delete">Löschen</button>
@endsection
