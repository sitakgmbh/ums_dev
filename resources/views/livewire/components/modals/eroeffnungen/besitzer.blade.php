@extends('livewire.components.modals.base-modal')

@section('body')
    @if($entry)
		<p>Aktueller Besitzer:
			@if($entry->owner)
				{{ $entry->owner->firstname }} {{ $entry->owner->lastname }}
			@else
				Keiner Besitzer zugewiesen
			@endif
		</p>

        <div class="form-group">
            <label for="owner_id" class="form-label">Neuer Besitzer</label>
            <select id="owner_id" wire:model="owner_id" class="form-control">
                <option value="">Bitte auswählen</option>
                @foreach($users as $user)
                    <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                @endforeach
            </select>
			<small>Leer lassen, um aktuellen Besitzer zu entfernen</small>
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-light" wire:click="closeModal">Abbrechen</button>
    <button type="button" class="btn btn-primary" wire:click="confirm">Speichern</button>
@endsection
