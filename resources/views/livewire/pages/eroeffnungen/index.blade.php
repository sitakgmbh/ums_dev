<div>
    @section('pageActions')
        <a href="{{ route('eroeffnungen.create') }}" class="btn btn-primary" title="Eröffnung erstellen">Neu</a>
    @endsection

    <livewire:components.tables.eroeffnungen-table />

    {{-- Modals --}}
    <livewire:components.modals.eroeffnung-delete />
    <livewire:components.modals.alert-modal />
</div>
