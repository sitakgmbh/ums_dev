@extends('livewire.components.modals.base-modal')
@section('body')
    @if($errors->has('general'))
        <div class="alert alert-danger">{{ $errors->first('general') }}</div>
    @endif
    @if(!empty($pendingAuftraege))
        <p>Folgende Aufträge werden versendet:</p>
        <ul class="list-group">
            @foreach($auftraegeDetails as $key => $details)
                <li class="list-group-item">
                    <strong>{{ $details['label'] }}</strong>
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