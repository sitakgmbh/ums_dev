@extends('livewire.components.modals.base-modal')

@section('body')
    @if($errors->has('general'))
        <div class="alert alert-danger">{{ $errors->first('general') }}</div>
    @endif

    @if(!empty($pendingAuftraege))
        <p>Folgende Aufträge werden versendet:</p>

        <ul class="list-group">

            @foreach($auftraegeDetails as $key => $configs)
                {{-- $configs = Liste der Mail-Konfigurationen (1 oder mehrere) --}}
                
                @foreach($configs as $conf)
                    <li class="list-group-item">

                        {{-- Titel: z. B. "Zutrittsrechte (Chur)" oder "SAP" --}}
                        <strong>
                            {{ $conf['label'] ?? $pendingAuftraege[$key] }}
                            @if(!empty($conf['standort']))
                                Standort {{ $conf['standort'] }}
                            @endif
                        </strong>

                        @if(!empty($conf['to']))
                            <div class="small text-muted mt-1">
                                <strong>An:</strong> {{ implode(', ', $conf['to']) }}
                            </div>
                        @endif

                        @if(!empty($conf['cc']))
                            <div class="small text-muted">
                                <strong>CC:</strong> {{ implode(', ', $conf['cc']) }}
                            </div>
                        @endif

                    </li>
                @endforeach

            @endforeach

        </ul>

    @else
        <p>Keine Aufträge zum Versenden vorhanden.</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>

    @if(!empty($pendingAuftraege))
        <x-action-button action="confirm" class="btn-primary">Senden</x-action-button>
    @endif
@endsection
