<div>
	@section('pageActions')
		<a href="{{ route('mutationen.create') }}" class="btn btn-primary" title="Mutation erstellen"><i class="mdi mdi-account-edit"></i></a>
	@endsection

    <livewire:components.tables.mutationen-table />

    {{-- Modals --}}
    <livewire:components.modals.mutationen.delete />
    <livewire:components.modals.alert-modal />
</div>
