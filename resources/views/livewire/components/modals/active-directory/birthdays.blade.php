@extends("livewire.components.modals.base-modal")

@section("body")
    <div class="mb-3">
        <div class="btn-group w-100" role="group">
            <button type="button" class="btn {{ $activeFilter === 'today' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setFilter('today')"> Heute</button>
            <button type="button" class="btn {{ $activeFilter === 'thisWeek' ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setFilter('thisWeek')">Nächste 7 Tage</button>
        </div>
    </div>

    @if(empty($data) || (is_countable($data) && count($data) === 0))
        <div class="alert alert-info mb-0">
            @if($activeFilter === 'today')
                Heute feiert niemand Geburtstag.
            @else
                In den nächsten 7 Tagen feiert niemand Geburtstag.
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-hover table-centered mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Geburtsdatum</th>
                        <th>Alter</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $user)
                        @php
                            try 
							{
                                $birthday = \Carbon\Carbon::parse($user->extensionattribute2);
                            } 
							catch (\Throwable $e) 
							{
                                $birthday = null;
                            }
                        @endphp

                        <tr>
							<td>
								<a href="{{ url('/admin/ad-users/' . $user->id) }}">
									{{ $user->display_name ?? '-' }}
								</a>
							</td>
                            <td>
                                {{ $birthday ? $birthday->format('d.m.Y') : '-' }}
                            </td>
                            <td>
                                {{ $birthday ? $birthday->age : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@section("footer")
    <button type="button" class="btn btn-secondary" wire:click="closeModal">
        Schliessen
    </button>
@endsection
