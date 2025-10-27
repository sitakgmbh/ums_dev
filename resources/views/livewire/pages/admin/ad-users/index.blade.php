<div>
	@section('pageActions')
	<div class="btn-group" role="group">
		<button type="button" class="btn btn-primary" title="Benutzer ohne Personalnummer" 
			onclick="Livewire.dispatch('open-modal', { modal: 'components.modals.active-directory.keine-personalnummer'})">
			<i class="mdi mdi-account-alert"></i>
		</button>
	</div>
	@endsection

    <livewire:components.tables.ad-users-table />
</div>

@section("modals")
    <livewire:components.modals.active-directory.keine-personalnummer />
@endsection
