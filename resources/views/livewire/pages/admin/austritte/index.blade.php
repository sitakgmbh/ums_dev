<div>
	@section('pageActions')
	<div class="btn-group" role="group">
		<button type="button" class="btn btn-primary" title="Alle Austritte anzeigen" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.austritte.bevorstehende-austritte'})">
			<i class="mdi mdi-hand-wave"></i>
		</button>
	</div>
	@endsection

    <livewire:components.tables.austritte-admin-table />
</div>

@section("modals")
    <livewire:components.modals.austritte.bevorstehende-austritte />
    <livewire:components.modals.austritte.delete />
@endsection
