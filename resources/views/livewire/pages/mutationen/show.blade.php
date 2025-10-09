<div>

	@section('pageActions')
	<div class="btn-group" role="group">
		@php
			$canEdit = $mutation->is_editable || auth()->user()->hasRole('admin');
		@endphp

		<a href="{{ $canEdit ? route('mutationen.edit', $mutation->id) : '#' }}" 
		   class="btn btn-primary {{ $canEdit ? '' : 'disabled' }}" 
		   title="{{ $canEdit ? 'Antrag bearbeiten' : 'Nicht bearbeitbar' }}">
			<i class="mdi mdi-square-edit-outline"></i>
		</a>
		<button type="button" class="btn btn-primary" title="Status anzeigen" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.mutationen.status', payload: { id: {{ $mutation->id }} } })">
			<i class="mdi mdi-information-outline"></i>
		</button>
	</div>
	@endsection


    @if(session('info'))
        <div class="alert alert-info mb-3">
            {{ session('info') }}
        </div>
    @endif

    @include('livewire.forms.mutation-form')

    <livewire:components.modals.alert-modal />
    <livewire:components.modals.mutationen.status />
</div>
