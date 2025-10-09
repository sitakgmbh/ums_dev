@extends('livewire.components.modals.base-modal')

@section('body')

    @if($entry)
        {{-- Übersicht --}}
        <div class="mb-3 d-flex flex-column gap-2 small">
            <div class="d-flex justify-content-between">
                <label class="form-label fw-bold mb-0">Auswahl</label>
                <div>
                    @switch($entry->tel_auswahl)
                        @case('uebernehmen')
                            Persönliche Nummer übernehmen
                            @break
                        @case('neu')
                            Neue Nummer
                            @break
                        @case('manuell')
                            Unpersönliche Nummer übernehmen
                            @break
                        @default
                            -
                    @endswitch
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <label class="form-label fw-bold mb-0">Tischtelefon</label>
                <div>{!! $entry->tel_tischtel ? '<i class="ri-check-line text-success"></i>' : '<i class="ri-close-line text-danger"></i>' !!}</div>
            </div>

            <div class="d-flex justify-content-between">
                <label class="form-label fw-bold mb-0">Mobiltelefon</label>
                <div>{!! $entry->tel_mobiltel ? '<i class="ri-check-line text-success"></i>' : '<i class="ri-close-line text-danger"></i>' !!}</div>
            </div>

            <div class="d-flex justify-content-between">
                <label class="form-label fw-bold mb-0">UC Standard</label>
                <div>{!! $entry->tel_ucstd ? '<i class="ri-check-line text-success"></i>' : '<i class="ri-close-line text-danger"></i>' !!}</div>
            </div>

            <div class="d-flex justify-content-between">
                <label class="form-label fw-bold mb-0">Alarmierung</label>
                <div>{!! $entry->tel_alarmierung ? '<i class="ri-check-line text-success"></i>' : '<i class="ri-close-line text-danger"></i>' !!}</div>
            </div>
        </div>

        {{-- Telefonnummer --}}
        <div class="mb-3">
            <label class="form-label">Telefonnummer</label>
            <input type="text"
                   class="form-control @error('tel_nr') is-invalid @enderror"
				   placeholder="+41 58 225 XXXX oder XXXX"
                   wire:model="tel_nr"
            @error('tel_nr') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    @endif

    {{-- AD-Gruppen --}}
    <div class="mb-3">
        <label class="form-label">AD-Gruppenmitgliedschaften</label>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="groupUcc" wire:model="G_APP_UCC">
            <label class="form-check-label" for="groupUcc">G_APP_UCC</label>
        </div>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="groupNovaalert" wire:model="G_APP_Novaalert">
            <label class="form-check-label" for="groupNovaalert">G_APP_Novaalert</label>
        </div>
    </div>

    {{-- CloudExtension Attribute --}}
    <div class="mb-3">
        <label class="form-label">msDS-cloudExtensionAttribute1</label>
        <select class="form-select @error('cloudExt1') is-invalid @enderror" wire:model="cloudExt1">
            <option value="">Bitte auswählen</option>
            @foreach($optionsCloudExt1 as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
        @error('cloudExt1') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">msDS-cloudExtensionAttribute2</label>
        <select class="form-select @error('cloudExt2') is-invalid @enderror" wire:model="cloudExt2">
            <option value="">Bitte auswählen</option>
            @foreach($optionsCloudExt2 as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
        @error('cloudExt2') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-0">
        <label class="form-label">msDS-cloudExtensionAttribute3</label>
        <select class="form-select @error('cloudExt3') is-invalid @enderror" wire:model="cloudExt3">
            <option value="">Bitte auswählen</option>
            @foreach($optionsCloudExt3 as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
        @error('cloudExt3') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Fehleranzeige allgemein --}}
    @if($errors->has('general'))
        <div class="alert alert-danger mt-3 mb-0">
            {{ $errors->first('general') }}
        </div>
    @endif

@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Speichern</x-action-button>
@endsection
