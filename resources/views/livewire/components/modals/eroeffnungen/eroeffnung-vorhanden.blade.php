@extends('livewire.components.modals.base-modal')

@section('body')
    <div class="mb-0">
        @if($eroeffnung)
            <p>
                Es existiert bereits eine laufende Eröffnung für 
                <strong>{{ $eroeffnung['vorname'] }} {{ $eroeffnung['nachname'] }}</strong>.
            </p>

            <ul class="list-unstyled small mb-3">
                <li><strong>Eröffnung-ID:</strong> {{ $eroeffnung['id'] }}</li>
                <li><strong>Erstellt am:</strong> {{ $eroeffnung['erstellt'] }}</li>
            </ul>
        @endif

        @if($antragsteller)
            <div class="card">
                <div class="card-header py-1">
                    <strong>Antragsteller</strong>
                </div>
                <div class="card-body py-2">
                    <ul class="list-unstyled small mb-0">
                        <li><strong>Vorname:</strong> {{ $antragsteller['vorname'] }}</li>
                        <li><strong>Nachname:</strong> {{ $antragsteller['nachname'] }}</li>
                        <li><strong>E-Mail:</strong> {{ $antragsteller['email'] }}</li>
                        <li><strong>Telefon:</strong> {{ $antragsteller['telefon'] }}</li>
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">
        Schliessen
    </button>
@endsection
