@extends('livewire.components.modals.base-modal')

@section('body')
    @if($errors->has('general'))
        <div class="alert alert-danger">{{ $errors->first('general') }}</div>
    @endif

    @if(!empty($pendingAuftraege))
        <p>Folgende Aufträge werden versendet:</p>
        <ul class="list-group">
            @foreach($pendingAuftraege as $label)
                <li class="list-group-item">
                    {{ $label }}
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
        <button type="button" class="btn btn-primary" wire:click="confirm">Senden</button>
    @endif
@endsection
