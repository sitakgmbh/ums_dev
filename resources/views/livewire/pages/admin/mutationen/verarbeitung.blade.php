<div>

@section('pageActions')
<div class="btn-group" role="group">
    {{-- Besitzer zuweisen --}}
    <button type="button" class="btn btn-primary"
        title="Besitzer zuweisen"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.mutationen.besitzer', payload: { entryId: {{ $entry->id }} }})"
        @disabled($entry->archiviert)>
        <i class="mdi mdi-account-switch"></i>
    </button>

    {{-- Archivieren --}}
    <button type="button" class="btn btn-primary"
        title="Archivieren"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.mutationen.archivieren', payload: { entryId: {{ $entry->id }} }})"
        @disabled($entry->archiviert)>
        <i class="mdi mdi-archive"></i>
    </button>

    {{-- Löschen --}}
    <button type="button" class="btn btn-danger"
        title="Löschen"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.mutationen.delete', payload: { id: {{ $entry->id }} }})">
        <i class="mdi mdi-delete"></i>
    </button>
</div>
@endsection

{{-- Meldungen --}}
@if(!empty($statusMessages))
    @foreach($statusMessages as $msg)
        <div class="alert alert-{{ $msg['type'] }} {{ $loop->last ? 'mb-3' : 'mb-1' }}">
            {{ $msg['text'] }}
        </div>
    @endforeach
@endif


<div class="row">
    {{-- Linke Spalte: Status + Aufgaben --}}
    <div class="col-12 col-lg-8">
        {{-- Bestehende Cards --}}
        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <p class="mb-0"><strong>Status</strong></p>
            </div>
            <div class="card-body">

                @php
					$tasks = $tasksConfig ?? [];
                    $activeTasks = collect($tasks)->filter(function ($t) use ($entry) {
                        if ($t['field'] === 'status_info') {
                            return true; // Info-Mail immer aktiv
                        }
                        $status = $entry->{$t['field']};
                        return $status !== null && $status !== 0;
                    });

                    $total = $activeTasks->count();
                    $completed = $activeTasks->filter(fn($t) => $entry->{$t['field']} === 2)->count();

                    $percent = $total > 0 ? round(($completed / $total) * 100) : 0;
                    $barClass = $completed === $total && $total > 0 ? 'bg-success' : 'bg-secondary';
                @endphp

                <div class="progress mb-2" style="height: 20px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $barClass }}"
                         role="progressbar"
                         aria-valuenow="{{ $percent }}"
                         aria-valuemin="0"
                         aria-valuemax="100"
                         style="width: {{ $percent }}%">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-2">
                    @foreach($activeTasks as $task)
                        @php
                            $status = $entry->{$task['field']};
                            $badgeClass = match($status) {
                                2 => 'badge bg-success',
                                3 => 'badge badge-outline-success',
                                4 => 'badge bg-warning',
                                5 => 'badge bg-danger',
                                default => 'badge bg-secondary',
                            };
                        @endphp

                        <div class="{{ $badgeClass }} d-inline-flex align-items-center gap-1 p-1 font-14">
                            <i class="{{ $task['icon'] }}"></i> {{ $task['label'] }}
                        </div>
                    @endforeach
                </div>

                <div class="text-muted">
                    Es wurden <strong>{{ $completed }}</strong> von <strong>{{ $total }}</strong> Aufgaben erledigt.
                </div>

            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <p class="mb-0"><strong>Aufgaben</strong></p>
            </div>
            <div class="card-body">

                <ul class="list-group">
					@foreach($activeTasks as $task)
						@php
							$status = $entry->{$task['field']};
							$disabled = false;

							// Info-Mail: Nur wenn alle anderen Tasks erledigt sind
							if ($task['field'] === 'status_info') {
								// Prüfe ob alle ANDEREN aktiven Tasks auf Status 2 sind
								$allOthersDone = $activeTasks
									->reject(fn($t) => $t['field'] === 'status_info')
									->every(fn($t) => $entry->{$t['field']} === 2);
								
								$disabled = !$allOthersDone;
							}
							// Erledigte sperren (außer Info-Mail)
							else {
								$disabled = in_array($status, [2, 3], true);
							}

							// Kein Edit-Recht → alles sperren
							if (! $canEdit) {
								$disabled = true;
							}

							$itemClass = $disabled ? 'list-group-item disabled text-muted' : 'list-group-item';
						@endphp

                        <li class="{{ $itemClass }} d-flex justify-content-between align-items-center">
                            <div>
                                <i class="{{ $task['icon'] }} me-1"></i> {{ $task['label'] }}
                            </div>
							<button
								class="btn btn-sm btn-secondary"
								wire:click="$dispatch('open-modal', { modal: '{{ $task['modal'] }}', payload: { entryId: {{ $entry->id }} } })"
								@disabled($disabled)
								title="Ausführen"
								x-data="{ loading: false }"
								@click="loading = true; setTimeout(() => loading = false, 1000)">
								
								<i class="mdi mdi-hammer-screwdriver" x-show="!loading"></i>
								<span class="spinner-border spinner-border-sm" x-show="loading" x-cloak></span>
							</button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
<script>
    document.addEventListener("livewire:init", () => {
        window.Livewire.on("open-modal", (modal, payload) => {
            console.log("Livewire open-modal Event:", modal, payload);
        });
    });
</script>

    {{-- Rechte Spalte: Details --}}
    <div class="col-12 col-lg-4">
        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <p class="mb-0"><strong>Details</strong></p>
            </div>

            <div class="card-body px-3 pb-3">
                <div class="d-flex flex-column gap-3">
                    @foreach($detailsConfig ?? [] as $section => $fields)
                        <div>
                            <div class="fw-bold mb-2">{{ $section }}</div>
                            <table class="table table-centered text-nowrap w-100 mb-0" style="border-collapse: collapse;">
                                <tbody style="line-height: 1.2;">
                                    @foreach($fields as $label => $path)
                                        @php
                                            $value = data_get($entry, $path, '-');

                                            if ($value === true || $value === 1 || $value === "1") {
                                                $value = 'Ja';
                                            } elseif ($value === false || $value === 0 || $value === "0") {
                                                $value = 'Nein';
                                            }

                                            if (in_array($path, ['vertragsbeginn','vertragsende']) && $value && $value !== '-') {
                                                try {
                                                    $value = \Carbon\Carbon::parse($value)->format('d.m.Y');
                                                } catch (\Exception $e) {
                                                    // lassen
                                                }
                                            }

                                            if ($path === 'ticket_nr' && $value && $value !== '-') {
                                                $value = '<a class="link-secondary" target="_blank" href="https://pdgr-otobo/otobo/index.pl?Action=AgentTicketZoom;TicketNumber=' . e($value) . '">' . e($value) . '</a>';
                                            }

                                            // Passwort behandeln → ● + Icon zum Anzeigen
                                            if ($path === 'passwort' && $value && $value !== '-') {
                                                $id = 'pw_' . uniqid();
                                                $value = '
                                                    <span id="'.$id.'">●●●●●●●●</span>
                                                    <i class="mdi mdi-eye toggle-password-icon ms-1"
                                                       style="cursor:pointer;"
                                                       onclick="togglePassword(\''.$id.'\', \''.e($value).'\', this)"></i>
                                                ';
                                            }
                                        @endphp

                                        <tr>
                                            <td style="width:1px; white-space: nowrap; padding: 5px 10px 2px 0;">
                                                {{ $label }}
                                            </td>
                                            <td style="padding: 2px 0;">
                                                {!! $value !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach




                {{-- Telefonie Section --}}
                @php
                    $telFelder = [
                        'tel_auswahl'   => ['label' => 'Auswahl', 'map' => ['neu'=>'Neue Nummer','uebernehmen'=>'Persönliche Nummer übernehmen','manuell'=>'Unpersönliche Nummer übernehmen']],
                        'tel_tischtel'  => ['label' => 'Tischtelefon'],
                        'tel_mobiltel'  => ['label' => 'Mobiltelefon'],
                        'tel_ucstd'     => ['label' => 'UC Standard'],
                        'tel_alarmierung'=>['label' => 'Alarmierung'],
                        'tel_headset'   => ['label' => 'Headset', 'map'=>['mono'=>'Mono','stereo'=>'Stereo']],
                        'tel_nr'        => ['label' => 'Telefonnummer'],
                    ];

                    $rows = [];
                    foreach ($telFelder as $key => $def) {
                        $raw = data_get($entry, $key);
                        if ($raw === null || $raw === '') continue;
                        $val = $raw;

                        if (!empty($def['map']) && isset($def['map'][$raw])) {
                            $val = $def['map'][$raw];
                        } elseif ($raw === true || $raw === 1 || $raw === "1") {
                            $val = '<i class="ri-check-line text-success"></i>';
                        } elseif ($raw === false || $raw === 0 || $raw === "0") {
                            $val = '<i class="ri-close-line text-danger"></i>';
                        }

                        $rows[] = ['label'=>$def['label'],'value'=>$val];
                    }
                @endphp

                @if($entry->status_tel > 0)
                    <div>
                        <div class="fw-bold mb-2">Telefonie</div>
                        <table class="table table-centered text-nowrap w-100 mb-0" style="border-collapse: collapse;">
                            <tbody style="line-height: 1.2;">
                                @foreach($rows as $row)
                                    <tr>
                                        <td style="width:1px; white-space: nowrap; padding: 5px 10px 2px 0;">
                                            {{ $row['label'] }}
                                        </td>
                                        <td style="padding: 2px 0;">
                                            {!! $row['value'] !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

				@if($entry->kommentar)
					<div>
						<div class="fw-bold mb-2">Kommentar</div>
						<div class="text-muted">
							{!! nl2br(e($entry->kommentar)) !!}
						</div>
					</div>
				@endif

                </div>
            </div>
        </div>
    </div>
</div>

</div>

<script>
    function togglePassword(spanId, realValue, iconEl) {
        const el = document.getElementById(spanId);
        if (!el) return;

        const isHidden = el.innerText === '●●●●●●●●';
        if (isHidden) {
            el.innerText = realValue;
            iconEl.classList.remove('mdi-eye');
            iconEl.classList.add('mdi-eye-off');
        } else {
            el.innerText = '●●●●●●●●';
            iconEl.classList.remove('mdi-eye-off');
            iconEl.classList.add('mdi-eye');
        }
    }
</script>

@section('modals')
    <livewire:components.modals.mutationen.pep />
    <livewire:components.modals.mutationen.kis />
    <livewire:components.modals.mutationen.ad />
	<livewire:components.modals.mutationen.email-bearbeiten />
    <livewire:components.modals.mutationen.telefonie />
    <livewire:components.modals.mutationen.auftraege />
    <livewire:components.modals.mutationen.info-mail />
    <livewire:components.modals.mutationen.besitzer />
    <livewire:components.modals.mutationen.archivieren />
    <livewire:components.modals.mutationen.delete />
@endsection
