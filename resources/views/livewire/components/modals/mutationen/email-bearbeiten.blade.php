@extends('livewire.components.modals.base-modal')

@section('body')
    @if($errorMessage)
        <div class="alert alert-danger">
            {{ $errorMessage }}
        </div>
    @endif

    @if($entry)
        <div class="mb-2">
            <p>{{ $infoText }}</p>
        </div>

        {{-- Hinweis auf generierte Adresse --}}
        @if($generatedMail)
            <div class="alert alert-info">
                <strong>Die E-Mail-Adresse wurde neu generiert.</strong><br>
                <small>Grund: {{ implode(", ", $reasons) }}</small>
            </div>
        @endif

        {{-- Primäre Adresse --}}
        <div class="mb-2">
            <label class="form-label">Primäre E-Mail-Adresse (neu)</label>
            <input type="text" class="form-control @error('mail1') is-invalid @enderror"
                   wire:model="mail1">
            @error('mail1') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Alias --}}
        <div class="mb-2">
            <label class="form-label">Alias (alte Adresse)</label>
            <input type="text" class="form-control @error('mail2') is-invalid @enderror"
                   wire:model="mail2">
            @error('mail2') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        {{-- Alle aktuellen Aliases aus AD --}}
        @if(!empty($aliases))
            <div class="mt-3">
                <strong>Vorhandene Aliases im AD:</strong>
                <ul class="list-unstyled ms-2">
                    @foreach($aliases as $alias)
                        <li>- {{ $alias }}</li>
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
