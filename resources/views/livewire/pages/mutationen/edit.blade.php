<div>
	{{-- Meldungen --}}
	@if(!empty($statusMessages))
		@foreach($statusMessages as $msg)
			<div class="alert alert-{{ $msg['type'] }} {{ $loop->last ? 'mb-3' : 'mb-1' }}">
				{{ $msg['text'] }}
			</div>
		@endforeach
	@endif

    @include('livewire.forms.mutation-form')
</div>

@section("modals")
    <livewire:components.modals.mutationen.mutation-vorhanden />
@endsection
