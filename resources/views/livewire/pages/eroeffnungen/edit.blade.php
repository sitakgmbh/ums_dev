<div>
	{{-- Meldungen --}}
	@if(!empty($statusMessages))
		@foreach($statusMessages as $msg)
			<div class="alert alert-{{ $msg['type'] }} {{ $loop->last ? 'mb-3' : 'mb-1' }}">
				{{ $msg['text'] }}
			</div>
		@endforeach
	@endif

    @include('livewire.forms.eroeffnung-form')

    <livewire:components.modals.eroeffnungen.eroeffnung-vorhanden />
	<livewire:components.modals.eroeffnungen.wiedereintritt />
	<livewire:components.modals.eroeffnungen.email-bearbeiten />
    <livewire:components.modals.alert-modal />
</div>
