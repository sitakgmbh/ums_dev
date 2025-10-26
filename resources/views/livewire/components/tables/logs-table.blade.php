<div>

	<div class="card mb-3">
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<!-- Level + Kategorie -->
				<div class="col-12 col-md-4">
					<div class="row g-2">
						<div class="col-12 col-sm-6">
							<label for="filterLevel" class="form-label">Log-Level</label>
							<select id="filterLevel" class="form-select form-select-sm" wire:model.live="filterLevel">
								<option value="">Alle</option>
								@foreach(\App\Enums\LogLevel::cases() as $level)
									<option value="{{ $level->value }}">{{ $level->label() }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-12 col-sm-6">
							<label for="filterCategory" class="form-label">Kategorie</label>
							<select id="filterCategory" class="form-select form-select-sm" wire:model.live="filterCategory">
								<option value="">Alle</option>
								@foreach(\App\Enums\LogCategory::cases() as $cat)
									<option value="{{ $cat->value }}">{{ $cat->label() }}</option>
								@endforeach
							</select>
						</div>
					</div>
				</div>

				<!-- Von + Bis -->
				<div class="col-12 col-md-4">
					<div class="row g-2">
						<div class="col-12 col-sm-6">
							<label for="dateFrom" class="form-label">Von</label>
							<input type="datetime-local" id="dateFrom" class="form-control form-control-sm" wire:model.live="dateFrom">
						</div>
						<div class="col-12 col-sm-6">
							<label for="dateTo" class="form-label">Bis</label>
							<input type="datetime-local" id="dateTo" class="form-control form-control-sm" wire:model.live="dateTo">
						</div>
					</div>
				</div>

				<!-- Buttons -->
				<div class="col-12 col-md-4 text-end d-flex flex-wrap gap-2 justify-content-md-end">
					<button type="button" wire:click="setToday" class="btn btn-sm btn-primary">Heute</button>
					<button type="button" wire:click="setLastWeek" class="btn btn-sm btn-primary">Letzte Woche</button>
					<button type="button" wire:click="resetFilters" class="btn btn-sm btn-secondary" title="Filter zurücksetzen">
						<i class="mdi mdi-filter-remove"></i>
					</button>
				</div>
			</div>
			
		</div>
	</div>





    <!-- Tabelle von BaseTable -->
    @include('livewire.components.tables.base-table', [
        'columns' => $columns,
        'records' => $records,
    ])
</div>
