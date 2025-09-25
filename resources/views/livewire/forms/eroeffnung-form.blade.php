<form wire:submit.prevent="save">
    <div class="card p-3">

        {{-- Personendaten --}}
        <div class="row">
            <div class="col-md-3 mb-3" wire:ignore>
                <label for="anrede_id" class="form-label">Anrede</label>
                <select id="anrede_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.anrede_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3 mb-3" wire:ignore>
                <label for="titel_id" class="form-label">Titel</label>
                <select id="titel_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.titel_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="vorname" class="form-label">Vorname</label>
                <input type="text" id="vorname" wire:model.defer="form.vorname"
                       class="form-control" @if($form->isReadonly) disabled @endif>
                @error('form.vorname') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3 mb-3">
                <label for="nachname" class="form-label">Nachname</label>
                <input type="text" id="nachname" wire:model.defer="form.nachname"
                       class="form-control" @if($form->isReadonly) disabled @endif>
                @error('form.nachname') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Organisation --}}
        <div class="row">
            <div class="col-md-3 mb-0" wire:ignore>
                <label for="arbeitsort_id" class="form-label">Arbeitsort</label>
                <select id="arbeitsort_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.arbeitsort_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3 mb-0" wire:ignore>
                <label for="unternehmenseinheit_id" class="form-label">Unternehmenseinheit</label>
                <select id="unternehmenseinheit_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.unternehmenseinheit_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3 mb-0" wire:ignore>
                <label for="abteilung_id" class="form-label">Abteilung</label>
                <select id="abteilung_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.abteilung_id') <span class="text-danger">{{ $message }}</span> @enderror

                <div class="form-check mt-1">
                    <input type="checkbox" id="zusatz_abteilung"
                           wire:model="form.has_abteilung2" class="form-check-input"
                           @if($form->isReadonly) disabled @endif>
                    <label class="form-check-label" for="zusatz_abteilung">Zusätzliche Abteilung</label>
                </div>

                <div class="mt-1" x-data>
                    <div wire:ignore x-show="$wire.form.has_abteilung2" x-cloak>
                        <select id="abteilung2_id" class="form-control select2"
                                @if($form->isReadonly) disabled @endif></select>
                        @error('form.abteilung2_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-0" wire:ignore>
                <label for="funktion_id" class="form-label">Funktion</label>
                <select id="funktion_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.funktion_id') <span class="text-danger">{{ $message }}</span> @enderror

                <div class="form-check mt-1">
                    <input type="checkbox" id="neue_konstellation"
                           wire:model="form.neue_konstellation" class="form-check-input"
                           @if($form->isReadonly) disabled @endif>
                    <label class="form-check-label" for="neue_konstellation">Neue Konstellation</label>
                </div>
            </div>
        </div>

        <hr class="my-3">

        {{-- Beziehungen --}}
        <div class="row">
            <div class="col-md-3 mb-0" wire:ignore>
                <label for="bezugsperson_id" class="form-label">Bezugsperson</label>
                <select id="bezugsperson_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.bezugsperson_id') <span class="text-danger">{{ $message }}</span> @enderror

                <div class="form-check mt-1">
                    <input type="checkbox" id="filter_mitarbeiter"
                           wire:model="form.filter_mitarbeiter" class="form-check-input"
                           @if($form->isReadonly) disabled @endif>
                    <label class="form-check-label" for="filter_mitarbeiter">Einträge filtern</label>
                </div>
            </div>

            <div class="col-md-3 mb-0" wire:ignore>
                <label for="vorlage_benutzer_id" class="form-label">PC Berechtigungen übernehmen von</label>
                <select id="vorlage_benutzer_id" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.vorlage_benutzer_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3 mb-0" wire:ignore>
                <label for="mailendung" class="form-label">E-Mail-Domain</label>
                <select id="mailendung" class="form-control select2"
                        @if($form->isReadonly) disabled @endif></select>
                @error('form.mailendung') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="col-md-3 mb-0">
                <label for="vertragsbeginn" class="form-label">Vertragsbeginn</label>
                <input type="date" id="vertragsbeginn" wire:model.defer="form.vertragsbeginn"
                       class="form-control" @if($form->isReadonly) disabled @endif>
                @error('form.vertragsbeginn') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    @if(!$form->isReadonly)
        <button type="submit" class="btn btn-primary mb-3">Speichern</button>
    @endif
</form>


@push('scripts')
<script>
    function initSelect2(selector) {
        $(selector).each(function () {
            const $s = $(this);
            if (!$s.data('select2')) {
                $s.select2({
                    placeholder: 'Bitte auswählen',
                    width: '100%'
                });
            }
            $s.off('change.select2-to-livewire').on('change.select2-to-livewire', function () {
                const id = $(this).attr('id');
                const val = $(this).val();
                const parsed = val ? (isNaN(val) ? val : parseInt(val, 10)) : null;
                @this.set('form.' + id, parsed);
            });
        });
    }

    Livewire.on('select2-options', (payload) => {
        const id = payload.id;
        const options = payload.options || [];
        const value = payload.value ?? null;

        const $select = $('#' + id);
        if (!$select.length) return;

        $select.empty().append('<option></option>');
        options.forEach(opt => {
            if (opt.display_name) {
                $select.append(new Option(opt.display_name, opt.id, false, false));
            } else {
                $select.append(new Option(opt.name, opt.id, false, false));
            }
        });

        initSelect2($select);

        if (value !== null && value !== undefined) {
            $select.val(value.toString()).trigger('change.select2');
        } else {
            $select.val(null).trigger('change.select2');
        }
    });

function initCheckboxes(selector) {
    $(selector).each(function () {
        const $c = $(this);
        if (!$c.data('bound')) {
            $c.data('bound', true);

            $c.on('change', function () {
                const id = $(this).attr('id');
                const checked = $(this).is(':checked');
                console.log("[Checkbox] change:", id, "->", checked);

                // an Livewire schicken
                @this.set('form.' + id, checked);
            });

            console.log("[Checkbox] Handler gebunden für:", $(this).attr('id'));
        }
    });
}


document.addEventListener('livewire:load', () => {
    initSelect2('.select2');
    initCheckboxes('input[type=checkbox]');
});

document.addEventListener('livewire:navigated', () => {
    initCheckboxes('input[type=checkbox]');
});

Livewire.hook('message.processed', () => {
    initSelect2('.select2');
	initCheckboxes('input[type=checkbox]');
});




</script>
@endpush
