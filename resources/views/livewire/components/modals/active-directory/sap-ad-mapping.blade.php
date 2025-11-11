@extends("livewire.components.modals.base-modal")

@section("body")
    <div class="mb-3">
		<p>Du findest hier Informationen zum Mapping von SAP-Eintrag und AD-Benutzer. Einträge, die hier aufgelistet und nicht als 'Excluded' markiert sind, werden täglich als Incident gemeldet. Excludes können in den Einstellungen hinterlegt werden.</p>
        <div class="btn-group w-100" role="group">
            @foreach($filters as $filter)
                <button 
                    type="button" 
                    class="btn {{ $activeFilter === $filter ? 'btn-primary' : 'btn-outline-primary' }}"
                    wire:click="setFilter('{{ $filter }}')"
                >
                    @if($filter === 'keine_personalnummer')
                        AD-Benutzer ohne Personalnummer
                    @elseif($filter === 'kein_sap_eintrag')
                        AD-Benutzer ohne SAP-Eintrag
                    @elseif($filter === 'kein_ad_benutzer')
                        SAP-Einträge ohne AD-Benutzer
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <div class="alert alert-info mb-3">
        <i class="ri-information-line"></i>
        @if($activeFilter === 'keine_personalnummer')
            Zeigt AD-Benutzer an, die keine Personalnummer oder den Platzhalter '99999' hinterlegt haben.
        @elseif($activeFilter === 'kein_sap_eintrag')
            Zeigt aktivierte AD-Benutzer mit einer Personalnummer an, für die kein SAP-Eintrag gefunden wurde. AD-Benutzer mit den Personalnummern 99999, 11111 und 00000 werden nicht angezeigt.
        @elseif($activeFilter === 'kein_ad_benutzer')
            Zeigt SAP-Einträge an, für die kein AD-Benutzer gefunden wurde. Einträge, bei denen das Eintrittsdatum in der Zukunft liegt, werden nicht angezeigt.
		@endif
    </div>

    @if(empty($data) || (is_countable($data) && count($data) === 0))
        <div class="alert alert-success mb-0">
            <i class="ri-checkbox-circle-line"></i> Keine Einträge gefunden.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover table-centered mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Funktion</th>
                        <th>Personalnummer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>
                                @if($activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag')
                                    {{ $item->display_name ?? "-" }} ({{ $item->username ?? "-" }})
                                @else
                                    {{ $item->d_name ?? "-" }} @if($item->d_vname || $item->d_rufnm) {{ $item->d_rufnm ?: $item->d_vname }}@endif
                                @endif
                            </td>
                            <td>
                                @if($activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag')
                                    {{ $item->description ?? "-" }}
                                @else
                                    {{ $item->d_0032_batchbez ?? "-" }}
                                @endif
                            </td>
							<td>
								<div class="d-inline-flex align-items-center">

									{{-- Personalnummer oder SAP-Personen-Nr. --}}
									@if($activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag')
										{{ $item->initials ?? "-" }}
										@php
											$personalnummer = $item->initials;
										@endphp
									@else
										{{ $item->d_pernr ?? "-" }}
										@php
											$personalnummer = $item->d_pernr;
										@endphp
									@endif

									{{-- Benutzername --}}
									@php
										$username = $activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag'
											? ($item->username ?? null)
											: ($item->d_name ?? null);
										
										$secondaryPNs = $secondaryPns ?? [];
										$isSecondary = in_array($personalnummer, $secondaryPNs);										
									@endphp

									@if(in_array($personalnummer, $excludedInitials) 
										|| in_array($username, $excludedUsernames) || $isSecondary)
										
									@if($isSecondary)
										<span class="badge bg-success ms-1">Zweite Personalnummer</span>
									@endif

									@if(in_array($personalnummer, $excludedInitials) || in_array($username, $excludedUsernames))
										<span class="badge bg-info ms-1">Ausnahme</span>
									@endif

									@endif

								</div>
							</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <small class="text-muted">
                <strong>Total:</strong> {{ $data->count() }}
                @if($activeFilter === 'keine_personalnummer')
                    Benutzer ohne Personalnummer
                @elseif($activeFilter === 'kein_ad_benutzer')
                    SAP-Einträge ohne AD-Benutzer
                @else
                    AD-Benutzer ohne SAP-Eintrag
                @endif
            </small>
        </div>
    @endif
@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection