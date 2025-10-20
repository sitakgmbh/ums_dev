<div>

@section('pageActions')
<div class="btn-group" role="group">
    {{-- Besitzer zuweisen --}}
    <button type="button" class="btn btn-primary"
        title="Besitzer zuweisen"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.austritte.besitzer', payload: { entryId: {{ $entry->id }} }})"
        @disabled($entry->archiviert)>
        <i class="mdi mdi-account-switch"></i>
    </button>

    {{-- Archivieren --}}
    <button type="button" class="btn btn-primary"
        title="Archivieren"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.austritte.archivieren', payload: { entryId: {{ $entry->id }} }})"
        @disabled($entry->archiviert)>
        <i class="mdi mdi-archive"></i>
    </button>

    {{-- Loeschen --}}
    <button type="button" class="btn btn-danger"
        title="Löschen"
        onclick="window.Livewire && Livewire.dispatch('open-modal', { modal: 'components.modals.austritte.delete', payload: { id: {{ $entry->id }} }})">
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
        {{-- Status --}}
        <div class="card mb-3">
            <div class="card-header text-white bg-primary py-1">
                <p class="mb-0"><strong>Status</strong></p>
            </div>
            <div class="card-body">

                @php
                    $tasks = $tasksConfig ?? [];

					// Nur die Tasks behalten, die benötigt sind (status != 0)
					$activeTasks = collect($tasks)->filter(function ($t) use ($entry) {
						return (int)$entry->{$t['field']} !== 0;
					});

                    $total     = $activeTasks->count();
                    $completed = $activeTasks->filter(fn($t) => $entry->{$t['field']} === 2)->count();

                    $percent  = $total > 0 ? round(($completed / $total) * 100) : 0;
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
                            $status = (int)$entry->{$task['field']};
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

        {{-- Aufgaben --}}
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

                            if ($task['field'] !== 'status_info') {
                                // erledigte Stati sperren
                                $disabled = in_array($status, [2, 3], true);
                            }

                            // alle sperren, wenn kein Besitzer/Admin
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
								title="Ausführen">
								<i class="mdi mdi-hammer-screwdriver"></i>
							</button>



                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

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
                                        // path zerlegen, z. B. "ad_user.firstname"
                                        $segments = explode('.', $path);
                                        $current = $entry;

                                        foreach ($segments as $segment) {
                                            if (is_null($current)) break;
                                            $current = $current->{$segment} ?? null;
                                        }

                                        $value = $current ?? '-';

                                        // Booleans zu Ja/Nein
                                        if ($value === true || $value === 1 || $value === "1") {
                                            $value = 'Ja';
                                        } elseif ($value === false || $value === 0 || $value === "0") {
                                            $value = 'Nein';
                                        }

                                        // Carbon Dates formatieren
                                        if ($value instanceof \Carbon\Carbon) {
                                            $value = $value->format('d.m.Y');
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
            </div>
        </div>
    </div>
</div>



</div>

</div>

@section('modals')
    <livewire:components.modals.austritte.pep />
    <livewire:components.modals.austritte.kis />
    <livewire:components.modals.austritte.streamline />
    <livewire:components.modals.austritte.telefonie />
    <livewire:components.modals.austritte.alarmierung />
    <livewire:components.modals.austritte.logimen />
    <livewire:components.modals.austritte.besitzer />
    <livewire:components.modals.austritte.archivieren />
    <livewire:components.modals.austritte.delete />
@endsection
