@extends('livewire.components.modals.base-modal')

@section('body')
    <div class="mb-0">
		<p>Der Vorname oder Nachname enthält ein Leerzeichen. Das kann zu einer ungewöhnlichen E-Mail-Adresse führen. Bitte prüfe die vorläufige E-Mail-Adresse und korrigiere Sie wenn nötig.</p>
        <label for="emailInput" class="form-label">Vorläufige E-Mail-Adresse:</label>
        <input type="email" id="emailInput" wire:model.defer="email" class="form-control">
        <small class="text-muted">Bitte gewünschte E-Mail-Adresse eingeben.</small>
    </div>
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
	<x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
