<div>
	@section('pageActions')
		<a href="{{ route('eroeffnungen.create') }}" class="btn btn-primary" title="Eröffnung erstellen"><i class="mdi mdi-account-plus"></i></a>
	@endsection

    <livewire:components.tables.eroeffnungen-table />

    {{-- Modals --}}
    <livewire:components.modals.eroeffnungen.delete />
    <livewire:components.modals.alert-modal />
</div>
