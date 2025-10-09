@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Möchtest du den Antrag archivieren?</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <button type="button" class="btn btn-primary" wire:click="confirm">Archivieren</button>
@endsection
