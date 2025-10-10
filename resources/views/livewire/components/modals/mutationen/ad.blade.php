@extends('livewire.components.modals.base-modal')

@section('body')
	@if($errorMessage)
		<div class="alert alert-danger">
			{{ $errorMessage }}
		</div>
	@endif

    @if($entry)
        <div class="mb-3">
            <p>{{ $infoText }}<p>
        </div>

        {{-- Modus Auswahl --}}
        <div class="form-check mb-1">
            <input class="form-check-input" type="radio" wire:model="mode" value="append" id="radioAppend">
            <label class="form-check-label" for="radioAppend">Berechtigungen ergänzen</label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" wire:model="mode" value="overwrite" id="radioOverwrite">
            <label class="form-check-label" for="radioOverwrite">Berechtigungen überschreiben</label>
        </div>

		{{-- Gruppenliste --}}
		@if(!empty($groups))
			<div class="mt-3">
				<strong>Gruppen aus Antrag:</strong>
				<ul class="mt-1 mb-0 ps-0" style="list-style-position: inside;">
					@foreach($groups as $group)
						<li>{{ $group }}</li>
					@endforeach
				</ul>
			</div>
		@endif

    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
