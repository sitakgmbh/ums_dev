<div>

	@section('pageActions')
		<a href="{{ route('admin.logfiles.index') }}" class="btn btn-primary" title="Logfiles">
			<i class="mdi mdi-file-document-multiple"></i>
		</a>
	@endsection

    {{-- Modals --}}
    <livewire:components.tables.logs-table />
    <livewire:components.modals.log-context />
</div>
