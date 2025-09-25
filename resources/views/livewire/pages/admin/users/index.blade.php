<div>

	@section('pageActions')
		<a href="{{ route('admin.users.create') }}" class="btn btn-primary" title="Benutzer erstellen">
			<i class="mdi mdi-account-plus"></i>
		</a>
	@endsection

    <livewire:components.tables.users-table />

    {{-- Modals --}}
    <livewire:components.modals.user-delete />
    <livewire:components.modals.alert-modal />
</div>
