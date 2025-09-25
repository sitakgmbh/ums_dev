<div class="card">
    <div class="card-body">
        <h3 class="card-title mb-4">
            {{ $adUser->display_name ?? $adUser->username }}
        </h3>

        {{-- Basis Identitaet --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Identität</h5>
            <dl class="row mb-0">
                <dt class="col-sm-3">SID</dt>
                <dd class="col-sm-9"><code>{{ $adUser->sid }}</code></dd>

                <dt class="col-sm-3">GUID</dt>
                <dd class="col-sm-9"><code>{{ $adUser->guid }}</code></dd>

                <dt class="col-sm-3">Benutzername</dt>
                <dd class="col-sm-9">{{ $adUser->username }}</dd>

                <dt class="col-sm-3">Vorname</dt>
                <dd class="col-sm-9">{{ $adUser->firstname }}</dd>

                <dt class="col-sm-3">Nachname</dt>
                <dd class="col-sm-9">{{ $adUser->lastname }}</dd>

                <dt class="col-sm-3">E-Mail</dt>
                <dd class="col-sm-9">{{ $adUser->email }}</dd>

                <dt class="col-sm-3">UserPrincipalName</dt>
                <dd class="col-sm-9">{{ $adUser->user_principal_name }}</dd>

                <dt class="col-sm-3">DistinguishedName</dt>
                <dd class="col-sm-9"><small>{{ $adUser->distinguished_name }}</small></dd>
            </dl>
        </div>

        {{-- Status --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Status</h5>
            <dl class="row mb-0">
                <dt class="col-sm-3">Aktiv</dt>
                <dd class="col-sm-9">
                    @if($adUser->is_enabled)
                        <span class="badge bg-success">Ja</span>
                    @else
                        <span class="badge bg-danger">Nein</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Im AD vorhanden</dt>
                <dd class="col-sm-9">
                    @if($adUser->is_existing)
                        <span class="badge bg-primary">Ja</span>
                    @else
                        <span class="badge bg-secondary">Nein</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Passwort läuft nie ab</dt>
                <dd class="col-sm-9">
                    @if($adUser->password_never_expires)
                        <span class="badge bg-info">Ja</span>
                    @else
                        <span class="badge bg-light text-dark">Nein</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Logon Count</dt>
                <dd class="col-sm-9">{{ $adUser->logon_count }}</dd>
            </dl>
        </div>

        {{-- Zeiten --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Zeiten</h5>
            <dl class="row mb-0">
                <dt class="col-sm-3">Erstellt</dt>
                <dd class="col-sm-9">{{ optional($adUser->created)->format('d.m.Y H:i') }}</dd>

                <dt class="col-sm-3">Geändert</dt>
                <dd class="col-sm-9">{{ optional($adUser->modified)->format('d.m.Y H:i') }}</dd>

                <dt class="col-sm-3">Letztes Logon</dt>
                <dd class="col-sm-9">{{ optional($adUser->last_logon_date)->format('d.m.Y H:i') }}</dd>

                <dt class="col-sm-3">Passwort zuletzt gesetzt</dt>
                <dd class="col-sm-9">{{ optional($adUser->password_last_set)->format('d.m.Y H:i') }}</dd>

                <dt class="col-sm-3">Ablaufdatum Account</dt>
                <dd class="col-sm-9">{{ optional($adUser->account_expiration_date)->format('d.m.Y H:i') }}</dd>

                <dt class="col-sm-3">Letzter falscher Login</dt>
                <dd class="col-sm-9">{{ optional($adUser->last_bad_password_attempt)->format('d.m.Y H:i') }}</dd>
            </dl>
        </div>

        {{-- Kontakt & Organisation --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Kontakt & Organisation</h5>
            <dl class="row mb-0">
                <dt class="col-sm-3">Firma</dt>
                <dd class="col-sm-9">{{ $adUser->company }}</dd>

                <dt class="col-sm-3">Abteilung</dt>
                <dd class="col-sm-9">{{ $adUser->department }}</dd>

                <dt class="col-sm-3">Titel</dt>
                <dd class="col-sm-9">{{ $adUser->title }}</dd>

                <dt class="col-sm-3">Manager DN</dt>
                <dd class="col-sm-9"><small>{{ $adUser->manager_dn }}</small></dd>

                <dt class="col-sm-3">Telefon Büro</dt>
                <dd class="col-sm-9">{{ $adUser->office_phone }}</dd>

                <dt class="col-sm-3">Fax</dt>
                <dd class="col-sm-9">{{ $adUser->fax }}</dd>

                <dt class="col-sm-3">Adresse</dt>
                <dd class="col-sm-9">
                    {{ $adUser->street_address }}<br>
                    {{ $adUser->postal_code }} {{ $adUser->city }}<br>
                    {{ $adUser->state }} {{ $adUser->country }}
                </dd>
            </dl>
        </div>

        {{-- Multi-Value --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Gruppen & E-Mail Aliasse</h5>
            <dl class="row mb-0">
                @if(!empty($adUser->proxy_addresses))
                    <dt class="col-sm-3">ProxyAddresses</dt>
                    <dd class="col-sm-9">
                        <ul class="mb-0">
                            @foreach($adUser->proxy_addresses as $addr)
                                <li>{{ $addr }}</li>
                            @endforeach
                        </ul>
                    </dd>
                @endif

                @if(!empty($adUser->member_of))
                    <dt class="col-sm-3">Mitglied von</dt>
                    <dd class="col-sm-9">
                        <ul class="mb-0">
                            @foreach($adUser->member_of as $group)
                                <li>{{ $group }}</li>
                            @endforeach
                        </ul>
                    </dd>
                @endif
            </dl>
        </div>

        {{-- Extension Attributes --}}
        <div class="border rounded p-3 mb-4">
            <h5 class="mb-3">Extension Attributes</h5>
            <dl class="row mb-0">
                @for($i = 1; $i <= 15; $i++)
                    @php $field = "extensionattribute{$i}"; @endphp
                    @if(!empty($adUser->$field))
                        <dt class="col-sm-3">{{ ucfirst($field) }}</dt>
                        <dd class="col-sm-9">{{ $adUser->$field }}</dd>
                    @endif
                @endfor
            </dl>
        </div>

        <div class="text-end">
            <a href="{{ route('admin.ad-users.index') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left"></i> Zurück
            </a>
        </div>
    </div>
</div>
