<div>
    <form wire:submit.prevent="save">
        <div class="card">
            <div class="card-body">
                {{-- Darkmode --}}
                <div class="border rounded p-3 bg-body-tertiary mb-3">
                    <div class="form-check form-switch mb-1">
                        <input type="checkbox" class="form-check-input" id="darkmodeSwitch"
                               wire:model="darkmode_enabled">
                        <label for="darkmodeSwitch" class="form-check-label fw-semibold">
                            Dark Mode
                        </label>
                    </div>
                    <small class="text-muted d-block">
                        Aktiviere ein dunkles Farbschema für die gesamte Anwendung. Lade die Seite neu, um die gespeicherten Änderungen anzuwenden. Du kannst das Farbschema auch über die Topbar ändern.
                    </small>
                </div>

                {{-- Stellvertretungen --}}
                <div class="border rounded p-3 bg-body-tertiary" wire:ignore>
                    <label class="form-label" for="representations">Stellvertretungen</label>
                    <select id="representations" class="form-control" multiple>
                        @foreach($adUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->display_name }}</option>
                        @endforeach
                    </select>
                    <small class="d-block mt-1 lh-sm text-muted">
                        Hinterlege Personen, die dich vertreten dürfen. Stellvertreter dürfen deine Anträge einsehen und bearbeiten.
                    </small>

					@if(count($representingUsers) > 0)
						<small class="d-block mt-2 text-muted">
							<span class="fw-semibold">Du wirst vertreten durch:</span>
							<span class="ms-1">{{ implode(', ', $representingUsers) }}</span>
						</small>
					@else
						<small class="d-block mt-2 text-muted">
							<span class="fw-semibold">Du wirst bei keinem anderen Benutzer als Stellvertretung hinterlegt.</span>
						</small>
					@endif


                </div>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
        @endif

        <div class="mt-3">
            <button type="submit" class="btn btn-primary">Speichern</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    const $select = $('#representations');
    
    function initSelect2(values = []) {
        // Destroy existing instance
        if ($select.data('select2')) {
            $select.off('change.select2custom');
            $select.select2('destroy');
        }
        
        // Initialize Select2
        $select.select2({
            placeholder: 'Bitte auswählen',
            width: '100%',
            allowClear: true,
        });
        
        // Set values
        $select.val(values).trigger('change.select2');
        
        // Sync with Livewire on change
        $select.on('change.select2custom', function () {
            const ids = $(this).val() || [];
            @this.set('representations', ids);
        });
    }
    
    // Initial setup
    initSelect2(@json($representations));
    
    // Listen for updates from Livewire
    Livewire.on('select2Updated', (event) => {
        initSelect2(event.representations);
    });
});
</script>
@endpush