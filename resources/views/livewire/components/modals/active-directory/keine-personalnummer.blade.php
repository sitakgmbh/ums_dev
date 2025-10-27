@extends("livewire.components.modals.base-modal")

@section("body")
    @if(empty($userOhnePersNr))
        <div class="alert alert-info mb-0">Keine Benutzer ohne Personalnummer gefunden.</div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Beschreibung</th>
						<th>Personalnummer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(collect($userOhnePersNr) as $adUser)
                        @php
                            $adUser = is_array($adUser) ? (object) $adUser : $adUser;
                            $expiration = \Illuminate\Support\Carbon::parse($adUser->account_expiration_date);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $adUser->display_name ?? "-" }}</strong><br>
                                <small class="text-muted">{{ $adUser->username ?? "-" }}</small>
                            </td>
                            <td>{{ $adUser->description ?? "-" }}</td>
                            <td>{{ $adUser->initials ?? "-" }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                <strong>Total:</strong> {{ count($userOhnePersNr) }} Benutzer ohne Personalnummer
            </small>
        </div>
    @endif
@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection
