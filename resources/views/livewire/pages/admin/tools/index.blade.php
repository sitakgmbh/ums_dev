<div>
    <div class="row g-3">
        {{-- Tool: Artisan Commands --}}
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <i class="mdi mdi-console text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5>Artisan Commands</h5>
                    <p class="text-muted small flex-grow-1">
                        Schneller Zugriff auf Artisan-Befehle direkt im Backend.
                    </p>
                    <a href="{{ route('admin.tools.artisan') }}" class="btn btn-primary btn-sm mt-auto">
                        Öffnen
                    </a>
                </div>
            </div>
        </div>

        {{-- Tool: Mail-Test --}}
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <i class="mdi mdi-email-send text-info mb-3" style="font-size: 3rem;"></i>
                    <h5>Testmail senden</h5>
                    <p class="text-muted small flex-grow-1">
                        Sende eine Test-E-Mail zur Überprüfung der Mail-Einstellungen.
                    </p>
                    <a href="{{ route('admin.tools.mail-test') }}" class="btn btn-info btn-sm mt-auto">
                        Öffnen
                    </a>
                </div>
            </div>
        </div>

        {{-- Weitere Tools können hier ergänzt werden --}}
    </div>
</div>
