<div>
    <div class="row g-3">
        <!-- Stammdaten -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <strong>Stammdaten</strong>
                </div>
                <div class="card-body p-2">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Benutzername</dt>
                        <dd class="col-sm-8">{{ $adUser->username }}</dd>

                        <dt class="col-sm-4">Anrede</dt>
                        <dd class="col-sm-8">{{ $adUser->anrede?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Titel</dt>
                        <dd class="col-sm-8">{{ $adUser->titel?->name ?? $adUser->title ?? '-' }}</dd>

                        <dt class="col-sm-4">Vorname</dt>
                        <dd class="col-sm-8">{{ $adUser->firstname }}</dd>

                        <dt class="col-sm-4">Nachname</dt>
                        <dd class="col-sm-8">{{ $adUser->lastname }}</dd>

						<dt class="col-sm-4">E-Mail</dt>
						<dd class="col-sm-8">
							{{ $adUser->email }}
							@if(!empty($adUser->proxy_addresses))
								@foreach($adUser->proxy_addresses as $addr)
									@php
										$lower = strtolower($addr);
									@endphp
									@if(Str::startsWith($lower, 'smtp:') && !Str::startsWith($addr, 'SMTP:'))
										<small class="text-muted d-block">{{ substr($addr, 5) }}</small>
									@endif
								@endforeach
							@endif
						</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Intern -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <strong>Intern</strong>
                </div>
                <div class="card-body p-2">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Arbeitsort</dt>
                        <dd class="col-sm-8">{{ $adUser->arbeitsort?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Einheit</dt>
                        <dd class="col-sm-8">{{ $adUser->unternehmenseinheit?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Abteilung</dt>
                        <dd class="col-sm-8">{{ $adUser->abteilung?->name ?? $adUser->department ?? '-' }}</dd>

                        <dt class="col-sm-4">Funktion</dt>
                        <dd class="col-sm-8">{{ $adUser->funktion?->name ?? '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Organisation -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <strong>Organisation</strong>
                </div>
                <div class="card-body p-2">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Firma</dt>
                        <dd class="col-sm-8">{{ $adUser->company }}</dd>

                        <dt class="col-sm-4">Abteilung</dt>
                        <dd class="col-sm-8">{{ $adUser->department }}</dd>

                        <dt class="col-sm-4">Tel</dt>
                        <dd class="col-sm-8">{{ $adUser->office_phone }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Account -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <strong>Account</strong>
                </div>
                <div class="card-body p-2">
                    @if(!$adUser->is_existing)
                        <div class="alert alert-danger py-1 mb-2">
                            Benutzer ist nicht (mehr) im Active Directory vorhanden.
                        </div>
                    @endif

                    <dl class="row mb-0">
                        <dt class="col-sm-8">Status</dt>
                        <dd class="col-sm-4">
                            {!! $adUser->is_enabled 
                                ? '<span class="badge bg-success">Aktiviert</span>' 
                                : '<span class="badge bg-secondary">Deaktiviert</span>' !!}
                        </dd>

                        <dt class="col-sm-8">Passwort läuft nie ab</dt>
                        <dd class="col-sm-4">
                            {!! $adUser->password_never_expires 
                                ? '<span class="badge bg-success">Ja</span>' 
                                : '<span class="badge bg-secondary">Nein</span>' !!}
                        </dd>

                        <dt class="col-sm-8">Anmeldungen</dt>
                        <dd class="col-sm-4">{{ $adUser->logon_count }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Adresse -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 mb-0">
                <div class="card-header bg-primary text-white py-1">
                    <strong>Adresse</strong>
                </div>
                <div class="card-body p-2">
                    {{ $adUser->street_address }}<br>
                    {{ $adUser->postal_code }} {{ $adUser->city }}<br>
                    {{ $adUser->state }} {{ $adUser->country }}
                </div>
            </div>
        </div>

        <!-- Zeiten -->
		<div class="col-12 col-md-6 col-lg-4">
			<div class="card h-100 mb-0">
				<div class="card-header bg-primary text-white py-1">
					<strong>Zeiten</strong>
				</div>
				<div class="card-body p-2">
					<dl class="row mb-0">
						<dt class="col-sm-6">Letzte Anmeldung</dt>
						<dd class="col-sm-6">
							{{ $adUser->last_logon_date ? $adUser->last_logon_date->format('d.m.Y H:i') : '-' }}
						</dd>

						<dt class="col-sm-6">Account Ablaufdatum</dt>
						<dd class="col-sm-6">
							{{ $adUser->account_expiration_date ? $adUser->account_expiration_date->format('d.m.Y H:i') : '-' }}
						</dd>

						<dt class="col-sm-6">Passwort zuletzt geändert</dt>
						<dd class="col-sm-6">
							{{ $adUser->password_last_set ? $adUser->password_last_set->format('d.m.Y H:i') : '-' }}
						</dd>

						<dt class="col-sm-6">Letzter falscher Login</dt>
						<dd class="col-sm-6">
							{{ $adUser->last_bad_password_attempt ? $adUser->last_bad_password_attempt->format('d.m.Y H:i') : '-' }}
						</dd>

						<dt class="col-sm-6">Erstellt</dt>
						<dd class="col-sm-6">
							{{ $adUser->created ? $adUser->created->format('d.m.Y H:i') : '-' }}
						</dd>

						<dt class="col-sm-6">Geändert</dt>
						<dd class="col-sm-6">
							{{ $adUser->modified ? $adUser->modified->format('d.m.Y H:i') : '-' }}
						</dd>
					</dl>
				</div>
			</div>
		</div>


        <!-- Gruppen -->
		<div class="col-12 col-md-6 col-lg-4">
			<div class="card h-100 mb-0">
				<div class="card-header bg-primary text-white py-1">
					<strong>Gruppenmitgliedschaften</strong>
				</div>
				<div class="card-body p-2">
					@if(!empty($adUser->member_of))
						<ul class="list-group list-group-flush list-group-sm">
							@foreach(collect($adUser->member_of)->sort()->values() as $group)
								<li class="list-group-item py-1 px-2">{{ $group }}</li>
							@endforeach
						</ul>
					@else
						<em class="text-muted">Keine Gruppen gefunden</em>
					@endif
				</div>
			</div>
		</div>

    </div>

    <div class="mt-3 mb-3">
        <a href="{{ route('admin.ad-users.index') }}" class="btn btn-primary">
            <i class="mdi mdi-arrow-left"></i> Zurück
        </a>
    </div>
</div>
