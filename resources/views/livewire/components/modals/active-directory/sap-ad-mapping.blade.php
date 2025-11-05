@extends("livewire.components.modals.base-modal")

@section("body")
    <div class="mb-3">
        <div class="btn-group w-100" role="group">
            <button 
                type="button" 
                class="btn {{ $activeFilter === 'keine_personalnummer' ? 'btn-primary' : 'btn-outline-primary' }}"
                wire:click="setFilter('keine_personalnummer')"
            >
                Benutzer ohne Personalnummer
            </button>
            <button 
                type="button" 
                class="btn {{ $activeFilter === 'kein_ad_benutzer' ? 'btn-primary' : 'btn-outline-primary' }}"
                wire:click="setFilter('kein_ad_benutzer')"
            >
                SAP-Einträge ohne AD-Benutzer
            </button>
            <button 
                type="button" 
                class="btn {{ $activeFilter === 'kein_sap_eintrag' ? 'btn-primary' : 'btn-outline-primary' }}"
                wire:click="setFilter('kein_sap_eintrag')"
            >
                AD-Benutzer ohne SAP-Eintrag
            </button>
        </div>
    </div>

    <div class="alert alert-info mb-3">
        <i class="ri-information-line"></i>
        @if($activeFilter === 'keine_personalnummer')
            Zeigt AD-Benutzer an, die keine Personalnummer oder den Platzhalter '99999' hinterlegt haben.
        @elseif($activeFilter === 'kein_ad_benutzer')
            Zeigt SAP-Einträge an, für die kein AD-Benutzer gefunden wurde.
        @elseif($activeFilter === 'kein_sap_eintrag')
            Zeigt aktivierte AD-Benutzer an, für die kein SAP-Eintrag gefunden wurde. Einträge mit den Personalnummern 99999, 11111 und 00000 werden nicht angezeigt.
        @endif
    </div>

	@if(empty($data) || (is_countable($data) && count($data) === 0))
		<div class="alert alert-success mb-0">
			<i class="ri-checkbox-circle-line"></i> Keine Einträge gefunden.
		</div>
	@else
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead>
                    <tr>
                        @if($activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag')
                            <th>Name</th>
                            <th>Beschreibung</th>
                            <th>Personalnummer</th>
                        @else
                            <th>Name</th>
                            <th>Beschreibung</th>
                            <th>Personalnummer</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $item)
                        <tr>
                            @if($activeFilter === 'keine_personalnummer' || $activeFilter === 'kein_sap_eintrag')
                                <td>
                                    <strong>{{ $item->display_name ?? "-" }}</strong><br>
                                    <small class="text-muted">{{ $item->username ?? "-" }}</small>
                                </td>
                                <td>{{ $item->description ?? "-" }}</td>
								<td>{{ $item->initials ?? "-" }}</td>
                            @else
                                <td>
                                    <strong>{{ $item->d_name ?? "-" }}</strong>
                                    @if($item->d_vname || $item->d_rufnm)
                                        , {{ $item->d_rufnm ?: $item->d_vname }}
                                    @endif
                                </td>
                                <td>{{ $item->d_0032_batchbez ?? "-" }}</td>
								<td>{{ $item->d_pernr ?? "-" }}</td>
                            @endif
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