@extends('livewire.components.modals.base-modal')

@section('body')
    @if($eroeffnung && $total > 0)
        {{-- Fortschrittsbalken --}}
        <h5 class="mb-2">Fortschritt</h5>
        <div class="mb-3">
            <div class="progress" style="height: 20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated
                            {{ $percentage < 100 ? 'bg-secondary' : 'bg-success' }}"
                     role="progressbar"
                     aria-valuenow="{{ $percentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100"
                     style="width: {{ $percentage }}%">
                    {{ $percentage }}%
                </div>
            </div>
        </div>

        {{-- Aufgaben --}}
        <div class="row g-2">
            @foreach($aufgaben as $task)
                <div class="col-md-6">
                    <div class="card border border-secondary-subtle h-100">
                        <div class="card-body d-flex flex-column justify-content-center p-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="mdi {{ $task['done'] ? 'mdi-check-circle text-success' : 'mdi-close-circle text-danger' }} me-2"></i>
                                <strong>{{ $task['label'] }}</strong>
                            </div>
                            <div>
                                Status:
                                @if($task['done'])
                                    <span class="badge bg-success">Erledigt</span>
                                @else
                                    <span class="badge bg-secondary">Offen</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

		{{-- Letzte Aktualisierung --}}
		@if($eroeffnung)
			<div class="mt-2">
				<small class="text-muted">
					<i class="mdi mdi-information-outline"></i>
					Letzte Aktualisierung:
					{{ $eroeffnung->updated_at ? $eroeffnung->updated_at->format('d.m.y H:i') : '-' }}
				</small>
			</div>
		@endif

    @else
        <div class="alert alert-info mb-0">
            Für diese Eröffnung sind keine Aufgaben erforderlich.
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">
        Schliessen
    </button>
@endsection
