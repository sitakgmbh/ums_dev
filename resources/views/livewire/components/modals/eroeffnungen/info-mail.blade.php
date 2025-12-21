@extends('livewire.components.modals.base-modal')

@section('body')
    @if($errors->has('general'))
        <div class="alert alert-danger">{{ $errors->first('general') }}</div>
    @endif

    @if($entry)
        @if($entry->status_info !== 2 && $entry->status_kis == 2)
            <div class="alert alert-info mb-3">Es handelt sich um einen KIS-Benutzer, daher erhalten weitere Stellen das PC-Login ebenfalls.</div>
        @endif

        <div class="mb-3">
            <label class="form-label" for="recipients">
                Empfänger <span class="text-danger">*</span>
            </label>
            <input 
                type="text" 
                class="form-control @error('recipients') is-invalid @enderror" 
                id="recipients" 
                wire:model="recipients"
                placeholder="user1@example.com, user2@example.com"
            >
            @error('recipients')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Mehrere E-Mail-Adressen mit Komma trennen</small>
        </div>

        <div class="mb-0">
            <label class="form-label" for="cc">
                Kopie (CC)
            </label>
            <input 
                type="text" 
                class="form-control @error('cc') is-invalid @enderror" 
                id="cc" 
                wire:model="cc"
                placeholder="user1@example.com, user2@example.com"
            >
            @error('cc')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Mehrere E-Mail-Adressen mit Komma trennen</small>
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Abbrechen</button>
    <x-action-button action="confirm" class="btn-primary">Senden</x-action-button>
@endsection