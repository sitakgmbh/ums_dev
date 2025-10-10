<div>
	@section('pageActions')
		<a href="{{ route('admin.users.create') }}" class="btn btn-primary" title="Benutzer erstellen">
			<i class="mdi mdi-account-plus"></i>
		</a>
	@endsection

    <livewire:components.tables.users-table />
</div>

@section('modals')
    <livewire:components.modals.user-delete />
@endsection
