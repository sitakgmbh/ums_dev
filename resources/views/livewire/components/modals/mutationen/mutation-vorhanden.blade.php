@extends("livewire.components.modals.base-modal")

@section("body")
    <div class="mb-0">
        @if($mutation)
            <p>
                Es existiert bereits eine laufende Mutation für den ausgewählten Benutzer.
            </p>

            <ul class="list-unstyled small mb-2">
                <li><strong>Erstellt am:</strong> {{ $mutation["erstellt"] }}</li>
				<li><strong>Eröffnung-ID:</strong> {{ $mutation["id"] }}</li>
            </ul>
        @endif

		<hr class="my-2">

        @if($antragsteller)
        <div class="mb-2"><strong>Antragsteller:</strong></div>
			<ul class="list-unstyled mb-0">
				<li><strong>Vorname:</strong> {{ $antragsteller["vorname"] }}</li>
				<li><strong>Nachname:</strong> {{ $antragsteller["nachname"] }}</li>
				<li><strong>E-Mail:</strong> {{ $antragsteller["email"] }}</li>
				<li><strong>Telefon:</strong> {{ $antragsteller["telefon"] }}</li>
			</ul>
        @endif
    </div>
@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection
