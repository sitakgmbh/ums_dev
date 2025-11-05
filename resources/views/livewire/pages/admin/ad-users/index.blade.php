<div>
	@section('pageActions')
	<div class="btn-group" role="group">
		<button type="button" class="btn btn-primary" title="Details SAP ↔ AD" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.active-directory.sap-ad-mapping'})">
			<i class="mdi mdi-account-sync"></i>
		</button>
	</div>
	@endsection

    <livewire:components.tables.ad-users-table />
</div>

@section("modals")
    <livewire:components.modals.active-directory.sap-ad-mapping />
@endsection
