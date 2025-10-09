<div>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white py-1">
            <h5 class="mb-0">Eröffnung #{{ $eroeffnung->id }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Antragsteller:</strong> {{ $eroeffnung->antragsteller?->fullname ?? '-' }}</p>
            <p><strong>Bezugsperson:</strong> {{ $eroeffnung->bezugsperson?->fullname ?? '-' }}</p>
            <p><strong>Erstellt am:</strong> {{ $eroeffnung->created_at->format('d.m.Y H:i') }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white py-1">
            <h6 class="mb-0">Aufgaben / Status</h6>
        </div>
        <div class="card-body">
            <ul class="list-group">
                @foreach($aufgaben as $task)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>
                            <i class="mdi {{ $task['done'] ? 'mdi-check-circle text-success' : 'mdi-close-circle text-danger' }}"></i>
                            {{ $task['label'] }}
                        </span>
                        <span class="badge {{ $task['done'] ? 'bg-success' : 'bg-danger' }}">
                            {{ $task['done'] ? 'Erledigt' : 'Offen' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
