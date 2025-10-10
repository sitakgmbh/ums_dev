@extends("livewire.components.modals.base-modal")

@section("body")
    <div class="mb-0">
        @if($eroeffnung)
            <p>
                Es existiert bereits eine laufende Eröffnung für 
                <strong>{{ $eroeffnung["vorname"] }} {{ $eroeffnung["nachname"] }}</strong>.
            </p>

            <ul class="list-unstyled small mb-2">
                <li><strong>Erstellt am:</strong> {{ $eroeffnung["erstellt"] }}</li>
				<li><strong>Eröffnung-ID:</strong> {{ $eroeffnung["id"] }}</li>
            </ul>
        @endif

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
