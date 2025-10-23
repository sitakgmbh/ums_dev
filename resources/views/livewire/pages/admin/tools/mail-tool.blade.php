<div class="card">
    <div class="card-body">
		@if($errors->any())
			<div class="alert alert-danger">
				<p>Etwas hat nicht funktioniert:</p>
				<ul class="mb-0">
					@foreach($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</div>
		@endif

        <div class="row">
            {{-- Auswahl Mailable --}}
            <div class="col-md-6">
                <label class="form-label">Mailable auswählen</label>
                <select id="mailable" class="form-select" wire:model="selectedMailable">
                    <option value="">Bitte wählen</option>
                    @foreach($mailables as $group)
                        <optgroup label="{{ $group['label'] }}">
                            @foreach($group['items'] as $key => $item)
                                <option
                                    value="{{ $key }}"
                                    data-requires-model="{{ $item['model'] ? '1' : '0' }}"
                                    data-model-type="{{ $item['modelType'] ?? '' }}"
                                >
                                    {{ $item['label'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            {{-- Auswahl Model --}}
            <div id="model-wrapper" class="col-md-6" style="display: none;">
                <label class="form-label" id="model-label">Model auswählen</label>
                <select id="model" class="form-select" wire:model="selectedModelId">
                    <option value="">Bitte wählen</option>
                </select>
            </div>
        </div>

        {{-- Vorschau & Testversand --}}
        <div class="row mt-4" id="testmail-section" style="display: none;">
			<div class="col-md-6">
				<label class="form-label">Empfänger</label>
				<input type="email" wire:model="recipient" class="form-control" placeholder="test@example.ch">
			</div>
			<div class="col-md-6 d-flex align-items-end gap-2">
				<x-action-button action="send">E-Mail senden</x-action-button>

				<a id="preview-button" href="{{ $previewUrl ?? '#' }}" target="_blank" class="btn btn-secondary">
					Vorschau anzeigen
				</a>
			</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const mailables = @json($flatMailables);
    const models = @json($models);

    const mailableSelect = document.getElementById("mailable");
    const modelWrapper = document.getElementById("model-wrapper");
    const modelSelect = document.getElementById("model");
    const modelLabel = document.getElementById("model-label");
    const previewButton = document.getElementById("preview-button");
    const testmailSection = document.getElementById("testmail-section");

    const modelTypeTranslations = {
        eroeffnungen: "Eröffnung",
        mutationen: "Mutation",
        austritte: "Austritt",
    };

    function updateModelDropdown(modelType) {
        modelSelect.innerHTML = '<option value="">Bitte wählen</option>';
        if (!models[modelType]) return;

        models[modelType].forEach((item) => {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.label;
            modelSelect.appendChild(option);
        });

        const label = modelTypeTranslations[modelType] ?? modelType;
        modelWrapper.style.display = "block";
        modelLabel.textContent = `${label} auswählen`;
    }

    function updatePreviewButton() {
        const selectedMailable = mailableSelect.value;
        const selectedOption = mailableSelect.options[mailableSelect.selectedIndex];
        const requiresModel = selectedOption?.dataset?.requiresModel === "1";
        const modelType = selectedOption?.dataset?.modelType ?? '';
        const modelId = modelSelect.value;

        let url = null;

        if (selectedMailable && !requiresModel) {
            url = `/admin/tools/mail-preview/render?mailable=${selectedMailable}`;
        }

        if (selectedMailable && requiresModel && modelId) {
            url = `/admin/tools/mail-preview/render?mailable=${selectedMailable}&model_id=${modelId}`;
        }

        if (url) {
            previewButton.href = url;
            previewButton.style.display = "inline-block";
            testmailSection.style.display = "flex";
        } else {
            previewButton.style.display = "none";
            testmailSection.style.display = "none";
        }
    }

    mailableSelect.addEventListener("change", function () {
        const selectedOption = this.options[this.selectedIndex];
        const requiresModel = selectedOption.dataset.requiresModel === "1";
        const modelType = selectedOption.dataset.modelType;

        if (requiresModel && modelType) {
            updateModelDropdown(modelType);
        } else {
            modelWrapper.style.display = "none";
            modelSelect.innerHTML = '';
        }

        updatePreviewButton();
    });

    modelSelect.addEventListener("change", function () {
        updatePreviewButton();
    });
</script>
@endpush
