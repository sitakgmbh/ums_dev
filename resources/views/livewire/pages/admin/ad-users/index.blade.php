<div>
	@section('pageActions')
	<div class="btn-group" role="group">
		<button type="button" class="btn btn-primary" title="Details SAP ↔ AD" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.active-directory.sap-ad-mapping'})">
			<i class="mdi mdi-account-sync"></i>
		</button>
		<button type="button" class="btn btn-primary" title="Geburtstage" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.active-directory.birthdays'})">
			<i class="mdi mdi-cake-variant"></i>
		</button>
	</div>
	@endsection

    <livewire:components.tables.ad-users-table />
</div>

@section("modals")
    <livewire:components.modals.active-directory.sap-ad-mapping />
	<livewire:components.modals.active-directory.birthdays />
@endsection
