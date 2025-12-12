@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
        <p>Möchtest du den Antrag archivieren?</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
	<x-action-button action="confirm" class="btn-primary">Archivieren</x-action-button>
@endsection
