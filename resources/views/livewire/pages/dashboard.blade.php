<!-- /dashboard -->
<div>
    <div class="row">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-account-plus widget-icon bg-light"></i>
                    </div>
                <h5 class="text-secondary fw-normal mt-0">Meine Eröffnungen</h5>
                    <h3 class="mt-3 mb-0">{{ $eroeffnungenCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-account-edit widget-icon bg-light"></i>
                    </div>
                    <h5 class="text-secondary fw-normal mt-0">Meine Mutationen</h5>
                    <h3 class="mt-3 mb-0">{{ $mutationenCount }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-12 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Hilfe</h4>
                    <p class="card-text">
                        Klicke auf <strong>Eröffnung erstellen</strong>, um einen neuen Eintritt oder Wiedereintritt zu erfassen.<br>
                        Über <strong>Mutation erstellen</strong> kannst du bestehende Benutzerdaten aktualisieren.<br>
                        <strong>Namensänderungen</strong> sind lediglich dem HR zu melden, der entsprechende Auftrag wird automatisch generiert.
                    </p>

                    <div class="d-flex gap-2">
                        <a href="{{ route('eroeffnungen.create') }}" class="btn btn-light">Eröffnung erstellen</a>
                        <a href="{{ route('mutationen.create') }}" class="btn btn-light">Mutation erstellen</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
