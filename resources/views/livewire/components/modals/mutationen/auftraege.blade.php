@extends('livewire.components.modals.base-modal')

@section('body')
    @if($errors->has('general'))
        <div class="alert alert-danger">{{ $errors->first('general') }}</div>
    @endif

    @if(!empty($pendingAuftraege))
        <p>Folgende Auftraege werden versendet:</p>

        <ul class="list-group">

            @foreach($auftraegeDetails as $key => $configs)
                {{-- $configs = Liste der Mail-Konfigurationen (1 oder mehrere) --}}
                
                @foreach($configs as $details)
                    <li class="list-group-item">

                        <strong>
                            {{ $details['label'] }}
                            @if(!empty($details['standort']))
                                ({{ $details['standort'] }})
                            @endif
                        </strong>

                        @if(!empty($details['to']))
                            <div class="small text-muted mt-1">
                                <strong>An:</strong> {{ implode(', ', $details['to']) }}
                            </div>
                        @endif

                        @if(!empty($details['cc']))
                            <div class="small text-muted">
                                <strong>CC:</strong> {{ implode(', ', $details['cc']) }}
                            </div>
                        @endif

                    </li>
                @endforeach

            @endforeach

        </ul>

    @else
        <p>Keine Auftraege zum Versenden vorhanden.</p>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>

    @if(!empty($pendingAuftraege))
        <x-action-button action="confirm" class="btn-primary">Senden</x-action-button>
    @endif
@endsection
