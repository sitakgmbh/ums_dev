@extends('livewire.components.modals.base-modal')

@section('body')
    @if(empty($austritte))
        <div class="alert alert-info mb-0">
            <i class="mdi mdi-information-outline me-2"></i>
            Keine bevorstehenden Austritte gefunden.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Arbeitsort</th>
                        <th>Unternehmenseinheit</th>
                        <th>Abteilung</th>
                        <th>Funktion</th>
                        <th>Ablaufdatum AD-Benuter</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(collect($austritte) as $adUser)
                        @php
                            $adUser = is_array($adUser) ? (object) $adUser : $adUser;
                            $expiration = \Illuminate\Support\Carbon::parse($adUser->account_expiration_date);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $adUser->display_name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $adUser->username ?? '-' }}</small>
                            </td>
                            <td>{{ $adUser->arbeitsort['name'] ?? '-' }}</td>
                            <td>{{ $adUser->unternehmenseinheit['name'] ?? '-' }}</td>
                            <td>{{ $adUser->abteilung['name'] ?? '-' }}</td>
                            <td>{{ $adUser->funktion['name'] ?? '-' }}</td>
                            <td>{{ $expiration->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <small class="text-muted">
                <strong>Gesamt:</strong> {{ count($austritte) }} bevorstehende(r) Austritt(e)
            </small>
        </div>
    @endif
@endsection

@section('footer')
    <button type="button" class="btn btn-secondary" wire:click="closeModal">Schliessen</button>
@endsection
