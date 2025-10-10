<div>
	@section('pageActions')
		<a href="{{ route('mutationen.create') }}" class="btn btn-primary" title="Mutation erstellen"><i class="mdi mdi-account-edit"></i></a>
	@endsection

    <livewire:components.tables.mutationen-table />
</div>

@section("modals")
    <livewire:components.modals.mutationen.status />
	<livewire:components.modals.mutationen.delete />
@endsection
