<form wire:submit.prevent="save">
    <div class="row">
        {{-- Linke Spalte: Stammdaten + Kommentar --}}
        <div class="col-12 col-md-6 order-1 d-flex flex-column gap-3">
            {{-- Stammdaten --}}
            <div class="card mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Stammdaten</strong></p>
                </div>
                <div class="card-body d-flex flex-column gap-3">



                    {{-- Zeile 1: Benutzer + Vertragsbeginn --}}
                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="ad_user_id" class="form-label mb-0">Benutzer</label>
                            <select id="ad_user_id" class="form-control select2"
                                    @disabled($form->isReadonly)></select>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1">
                            <label for="vertragsbeginn" class="form-label mb-0">Vertragsbeginn</label>
                            <input type="date" id="vertragsbeginn" class="form-control"
                                   wire:model="form.vertragsbeginn"
                                   @disabled($form->isReadonly)>
                        </div>
                    </div>

                    {{-- Zeile 2: Anrede + Titel --}}
                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="anrede_id" class="form-label mb-0">Anrede</label>
                            <select id="anrede_id" class="form-control select2"
                                    @disabled(!$form->enable_anrede || $form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="enable_anrede" wire:model="form.enable_anrede"
                                       class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="enable_anrede">Anrede anpassen</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="titel_id" class="form-label mb-0">Titel</label>
                            <select id="titel_id" class="form-control select2"
                                    @disabled(!$form->enable_titel || $form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="enable_titel" wire:model="form.enable_titel"
                                       class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="enable_titel">Titel anpassen</label>
                            </div>
                        </div>
                    </div>

                    {{-- Zeile 3: Arbeitsort + Unternehmenseinheit --}}
                    <div class="row g-3">
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="arbeitsort_id" class="form-label mb-0">Arbeitsort</label>
                            <select id="arbeitsort_id" class="form-control select2"
                                    @disabled(!$form->enable_arbeitsort || $form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="enable_arbeitsort" wire:model="form.enable_arbeitsort"
                                       class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="enable_arbeitsort">Arbeitsort anpassen</label>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
                            <label for="unternehmenseinheit_id" class="form-label mb-0">Unternehmenseinheit</label>
                            <select id="unternehmenseinheit_id" class="form-control select2"
                                    @disabled(!$form->enable_unternehmenseinheit || $form->isReadonly)></select>
                            <div class="form-check">
                                <input type="checkbox" id="enable_unternehmenseinheit"
                                       wire:model="form.enable_unternehmenseinheit"
                                       class="form-check-input" @disabled($form->isReadonly)>
                                <label class="form-check-label" for="enable_unternehmenseinheit">
                                    Unternehmenseinheit anpassen
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Zeile 4: Abteilung + Funktion --}}
                    <div class="row g-3">
						<div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
							<label for="abteilung_id" class="form-label mb-0">Abteilung</label>
							<select id="abteilung_id" class="form-control select2"
									@disabled(!$form->enable_abteilung || $form->isReadonly)></select>

							<div class="d-flex flex-column gap-0">
								<div class="form-check mb-0">
									<input type="checkbox" id="enable_abteilung" wire:model="form.enable_abteilung"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="enable_abteilung">Abteilung anpassen</label>
								</div>

								{{-- zweite Abteilung --}}
								<div class="form-check mb-0">
									<input type="checkbox" id="has_abteilung2" wire:model="form.has_abteilung2"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="has_abteilung2">Zusätzliche Abteilung</label>
								</div>
							</div>

							<div wire:ignore x-data x-show="$wire.form.has_abteilung2" x-cloak>
								<select id="abteilung2_id" class="form-control select2"
										@disabled($form->isReadonly)></select>
							</div>
						</div>

						<div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
							<label for="funktion_id" class="form-label mb-0">Funktion</label>
							<select id="funktion_id" class="form-control select2"
									@disabled(!$form->enable_funktion || $form->isReadonly)></select>

							<div class="d-flex flex-column gap-0">
								<div class="form-check mb-0">
									<input type="checkbox" id="enable_funktion" wire:model="form.enable_funktion"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="enable_funktion">Funktion anpassen</label>
								</div>

								<div class="form-check mb-0">
									<input type="checkbox" id="neue_konstellation" wire:model="form.neue_konstellation"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="neue_konstellation">Neue Konstellation</label>
									<i class="mdi mdi-information-outline text-muted ms-1" data-bs-toggle="tooltip" title="Aktiviere diese Option, wenn es sich um eine neue Konstellation von Arbeitsort, Unternehmenseinheit, Abteilung und Funktion handelt."></i>
								</div>
							</div>
						</div>

                    </div>

					{{-- Zeile 5: Berechtigungen + E-Mail-Domain --}}
					<div class="row g-3">
						{{-- Vorlage-Benutzer (falls noch benötigt) --}}
						<div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
							<label for="vorlage_benutzer_id" class="form-label mb-0">Berechtigungen übernehmen von</label>
							<select id="vorlage_benutzer_id" class="form-control select2"
									@disabled(!$form->enable_vorlage || $form->isReadonly)></select>

							<div class="d-flex flex-column gap-0">
								<div class="form-check mb-0">
									<input type="checkbox" id="enable_vorlage"
										   wire:model="form.enable_vorlage"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="enable_vorlage">Berechtigungen anpassen</label>
								</div>

								<div class="form-check mb-0">
									<input type="checkbox" id="filter_mitarbeiter"
										   wire:model="form.filter_mitarbeiter"
										   class="form-check-input" @disabled($form->isReadonly)>
									<label class="form-check-label" for="filter_mitarbeiter">Mitarbeiter filtern</label>
								</div>
							</div>

						</div>

						{{-- E-Mail Domain --}}
						<div class="col-md-6 d-flex flex-column gap-1" wire:ignore>
							<label for="mailendung" class="form-label mb-0">E-Mail-Domain</label>
							<select id="mailendung" class="form-control select2"
									@disabled(!$form->enable_mailendung || $form->isReadonly)></select>
							<div class="form-check mt-1">
								<input type="checkbox" id="enable_mailendung"
									   wire:model="form.enable_mailendung"
									   class="form-check-input" @disabled($form->isReadonly)>
								<label class="form-check-label" for="enable_mailendung">E-Mail-Domain anpassen</label>
							</div>
						</div>
					</div>

                </div>
            </div>

            {{-- Kommentar --}}
            <div class="card mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Kommentar</strong></p>
                </div>
                <div class="card-body">
                    <textarea id="kommentar" class="form-control" wire:model.defer="form.kommentar" rows="4" @disabled($form->isReadonly)></textarea>
                </div>
            </div>

			{{-- Footer nur Desktop --}}
			<div class="d-none d-md-block">
				@if ($errors->any())
					<div class="alert alert-danger mb-3">
						<ul class="mb-0 ps-2">
							@foreach ($errors->all() as $error)
								<li class="mb-0">{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<button type="submit" class="btn btn-primary mb-3 @disabled($form->isReadonly)" wire:loading.attr="disabled" wire:target="save">
					<span wire:loading.remove wire:target="save">Speichern</span>
					<span wire:loading wire:target="save">
						<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
						Bitte warten
					</span>
				</button>
			</div>

        </div>

        {{-- Rechte Spalte: Optionen --}}
        <div class="col-12 col-md-6 order-2">
            <div class="card mt-3 mt-md-0">
                <div class="card-header bg-primary text-white py-1">
                    <p class="mb-0"><strong>Optionen</strong></p>
                </div>
                <div class="card-body">
                    <div class="row g-3">
						{{-- Checkboxen (links auf Desktop, oben auf Mobile) --}}
						<div class="col-12 col-md-6 d-flex flex-column gap-1">
							@php
								$fields = [
									'sap_status'            => 'SAP-Benutzer erstellen/anpassen',
									'sap_delete'            => 'SAP-Benutzer löschen',
									'is_lei'                => 'SAP-Leistungserbringer',
									'tel_status'            => 'Telefonie',
									'buerowechsel'          => 'Bürowechsel',
									'key_waldhaus'          => 'Schlüsselrecht Klinik Waldhaus',
									'key_beverin'           => 'Schlüsselrecht Klinik Beverin',
									'key_rothenbr'          => 'Schlüsselrecht Rothenbrunnen',
									'berufskleider'         => 'Berufsbekleidung',
									'garderobe'             => 'Garderobe',
								];
							@endphp

							@foreach($fields as $field => $label)
								<div class="form-check d-flex align-items-center">
									<input
										class="form-check-input"
										@disabled($form->isReadonly)
										type="checkbox"
										id="checkbox{{ ucfirst($field) }}"
										wire:model="form.{{ $field }}"
									>
									<label class="form-check-label ms-1" for="checkbox{{ ucfirst($field) }}">
										{{ $label }}
									</label>
										@if(isset($tooltips[$field]))
											<i class="mdi mdi-information-outline text-muted ms-1"
											   data-bs-toggle="tooltip"
											   data-bs-html="true"
											   title="{!! $tooltips[$field] !!}"></i>
										@endif
								</div>
							@endforeach
						</div>


                        {{-- Optionale Felder (rechts auf Desktop, unten auf Mobile) --}}
                        <div class="col-12 col-md-6 d-flex flex-column gap-3">
                            {{-- SAP Rolle --}}
                            <div x-data x-show="$wire.form.sap_status" x-cloak>
                                <label class="form-label" for="inputSapRolle">SAP Rolle</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="mdi mdi-toolbox"></i></span>
                                    <select class="form-select" id="inputSapRolle" wire:model="form.sap_rolle_id" @disabled($form->isReadonly)>
                                        <option value="">Bitte auswählen</option>
                                        @foreach($form->sapRollen as $rolle)
                                            <option value="{{ $rolle['id'] }}">{{ $rolle['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

							{{-- SAP Leistungserbringer --}}
							<div x-data x-show="$wire.form.is_lei" x-cloak>
								<label class="form-label" for="inputKommLei">Kommentar SAP-Leistungserbringer</label>
								<div class="input-group">
									<span class="input-group-text"><i class="mdi mdi-pencil"></i></span>
									<textarea id="inputKommLei" class="form-control"
											  wire:model.defer="form.komm_lei"
											  rows="3"
											  placeholder="Kommentar eingeben"
											  @disabled($form->isReadonly)></textarea>
								</div>
							</div>

							{{-- Telefonie --}}
							<div x-show="$wire.form.tel_status" x-cloak>
								<div x-data="telefonieHandler()" x-init="init()" class="d-flex flex-column gap-2">
									
									{{-- Auswahl --}}
									<div>
										<label class="form-label" for="inputTelefonAuswahl">Telefonie</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-phone"></i></span>
											<select class="form-select" id="inputTelefonAuswahl"
													x-model="auswahl"
													@change="applyState"
													wire:model="form.tel_auswahl"
													@disabled($form->isReadonly)>
												<option value="">Bitte auswählen</option>
												<option value="uebernehmen">Persönliche Nummer übernehmen</option>
												<option value="neu">Neue Nummer</option>
												<option value="manuell">Unpersönliche Nummer</option>
											</select>
										</div>
									</div>

									{{-- Telefonnummer --}}
									<div>
										<label class="form-label" for="inputTelefonnummer">Telefonnummer</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-phone-incoming"></i></span>

											{{-- Wenn "neu" → nur Textfeld mit fixem Wert, kein wire:model --}}
											<template x-if="auswahl === 'neu'">
												<input type="text"
													   id="inputTelefonnummer"
													   class="form-control"
													   value="wird von ICT festgelegt"
													   disabled>
											</template>

											{{-- Sonst → mit Livewire Binding --}}
											<template x-if="auswahl !== 'neu'">
												<input type="text"
													   id="inputTelefonnummer"
													   class="form-control"
													   wire:model="form.tel_nr"
													   placeholder="+41 58 225 XXXX oder XXXX"
													   :disabled="$wire.form.isReadonly || nummerDisabled">
											</template>
										</div>
									</div>

									{{-- Optionen --}}
									<div>
										<label class="form-label">Optionen</label>
										<div class="d-flex flex-column gap-0">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxTischtelefon"
													   x-model="tischtelefon" @change="updateHeadset"
													   wire:model="form.tel_tischtel"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxTischtelefon">Tischtelefon</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxMobiltelefon"
													   wire:model="form.tel_mobiltel" x-model="mobiltelefon"
													   @change="updateAlarmierung"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxMobiltelefon">Mobiltelefon</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxUcStandard"
													   x-model="ucstandard" @change="updateHeadset"
													   wire:model="form.tel_ucstd"
													   @disabled($form->isReadonly)>
												<label class="form-check-label" for="checkboxUcStandard">Softphone / PC-Applikation</label>
											</div>
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkboxAlarmierung"
													   wire:model="form.tel_alarmierung"
														:disabled="{{ $form->isReadonly ? 'true' : '!mobiltelefon' }}">
												<label class="form-check-label" for="checkboxAlarmierung">Alarmierung</label>
											</div>
										</div>
									</div>

									{{-- Headset --}}
									<div>
										<label class="form-label" for="inputHeadset">Headset</label>
										<div class="input-group">
											<span class="input-group-text"><i class="mdi mdi-headset"></i></span>
												<select class="form-select" id="inputHeadset"
													wire:model="form.tel_headset"
													x-model="headset"
													x-init="headset = $wire.form.tel_headset"
													:disabled="$wire.form.isReadonly || headsetDisabled">
													<option value="">Bitte auswählen</option>
													<option value="mono">Mono</option>
													<option value="stereo">Stereo</option>
												</select>
										</div>
									</div>

								</div>
							</div>

                            {{-- Schlüssel Waldhaus --}}
                            <div x-data x-show="$wire.form.key_waldhaus" x-cloak>
                                <label class="form-label">Schlüsselrecht Klinik Waldhaus</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_wh_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_wh_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

                            {{-- Schlüssel Beverin --}}
                            <div x-data x-show="$wire.form.key_beverin" x-cloak>
                                <label class="form-label">Schlüsselrecht Klinik Beverin</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_be_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_be_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

                            {{-- Schlüssel Rothenbrunnen --}}
                            <div x-data x-show="$wire.form.key_rothenbr" x-cloak>
                                <label class="form-label">Schlüsselrecht Rothenbrunnen</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_rb_badge" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Badge</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="form.key_rb_schluessel" @disabled($form->isReadonly)>
                                    <label class="form-check-label">Schlüssel</label>
                                </div>
                            </div>

							{{-- Berufsbekleidung --}}
							<div x-data x-show="$wire.form.berufskleider" x-cloak>
								<label class="form-label" for="inputKommBerufskleider">Kommentar Berufsbekleidung</label>
								<div class="input-group">
									<span class="input-group-text"><i class="mdi mdi-tshirt-crew"></i></span>
									<textarea id="inputKommBerufskleider" class="form-control"
											  wire:model.defer="form.komm_berufskleider"
											  rows="3"
											  placeholder="Kommentar eingeben"
											  @disabled($form->isReadonly)></textarea>
								</div>
							</div>

							{{-- Garderobe --}}
							<div x-data x-show="$wire.form.garderobe" x-cloak>
								<label class="form-label" for="inputKommGarderobe">Kommentar Garderobe</label>
								<div class="input-group">
									<span class="input-group-text"><i class="mdi mdi-hanger"></i></span>
									<textarea id="inputKommGarderobe" class="form-control"
											  wire:model.defer="form.komm_garderobe"
											  rows="3"
											  placeholder="Kommentar eingeben"
											  @disabled($form->isReadonly)></textarea>
								</div>
							</div>

							{{-- Bürowechsel --}}
							<div x-data x-show="$wire.form.buerowechsel" x-cloak>
								<label class="form-label" for="inputKommBuerowechsel">Kommentar Bürowechsel</label>
								<div class="input-group">
									<span class="input-group-text"><i class="mdi mdi-office-building"></i></span>
									<textarea id="inputKommBuerowechsel" class="form-control"
											  wire:model.defer="form.komm_buerowechsel"
											  rows="3"
											  placeholder="Kommentar eingeben"
											  @disabled($form->isReadonly)></textarea>
								</div>
							</div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer nur Mobile --}}
        <div class="col-12 d-block d-md-none order-3 mt-0">
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0 ps-2">
                        @foreach ($errors->all() as $error)
                            <li class="mb-0">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <button type="submit" class="btn btn-primary mb-3" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">Speichern</span>
                <span wire:loading wire:target="save">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Bitte warten
                </span>
            </button>
        </div>
    </div>
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
				let val = $(this).val();
				if ($(this).prop('multiple')) {
					// Bei Multiselect → Array
					@this.set('form.' + id, val || []);
				} else {
					// Normaler Single-Select
					const parsed = val ? (isNaN(val) ? val : parseInt(val, 10)) : null;
					@this.set('form.' + id, parsed);
				}
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

		if (value !== null) {
			$select.val(value).trigger('change.select2');
		}
    });

Livewire.on('toggle-select', (payload) => {
    const $select = $('#' + payload.id);
    if (!$select.length) return;

    $select.prop('disabled', !payload.enabled);

    // Bei Select2 muss man auch den State refreshen
    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2();
    }

    console.log("[Toggle] Select:", payload.id, "enabled:", payload.enabled);
});

Livewire.on("select2-clear", ({ id }) => {
    const el = document.getElementById(id);
    if (el) $(el).val(null).trigger("change");
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



function telefonieHandler() {
    return {
        auswahl: @entangle('form.tel_auswahl').live,
        tischtelefon: @entangle('form.tel_tischtel').live,
        mobiltelefon: @entangle('form.tel_mobiltel').live,
        ucstandard: @entangle('form.tel_ucstd').live,
        alarmierung: @entangle('form.tel_alarmierung').live,
        headset: @entangle('form.tel_headset').live,

        nummerDisabled: false,
        headsetDisabled: true,

        init() {
            if (@this.form.isReadonly) {
                this.nummerDisabled = true;
                this.headsetDisabled = true;
                return;
            }
            this.updateHeadset();
            this.updateAlarmierung();
        },

        applyState() {
            // nur Headset/Alarmierung steuern
            this.updateHeadset();
            this.updateAlarmierung();
        },

        updateHeadset() {
            if (@this.form.isReadonly) return;
            this.headsetDisabled = !(this.tischtelefon || this.ucstandard);
        },

        updateAlarmierung() {
            if (!this.mobiltelefon) {
                this.alarmierung = false;
            }
        }
    }
}






document.addEventListener('livewire:load', () => {
    initSelect2('.select2, .select2-multiple');
    initCheckboxes('input[type=checkbox]');
});

document.addEventListener('livewire:navigated', () => {
    initCheckboxes('input[type=checkbox]');
});

Livewire.hook('message.processed', () => {
    initSelect2('.select2, .select2-multiple');
    initCheckboxes('input[type=checkbox]');

    // enable_* Flags auf Select2 disabled-Status anwenden
    const mapping = {
        enable_anrede: 'anrede_id',
        enable_titel: 'titel_id',
        enable_arbeitsort: 'arbeitsort_id',
        enable_unternehmenseinheit: 'unternehmenseinheit_id',
        enable_abteilung: 'abteilung_id',
        enable_funktion: 'funktion_id',
        enable_mailendung: 'mailendung',
        enable_vorlage: 'vorlage_benutzer_id'
    };

    for (const flag in mapping) {
        const selectId = mapping[flag];
        const enabled = @this.get('form.' + flag);
        const readonly = @this.get('form.isReadonly');
        const $select = $('#' + selectId);

        if ($select.length) {
            $select.prop('disabled', readonly || !enabled);
        }
    }
});



</script>
@endpush
