<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Log;
use App\Enums\LogLevel;
use App\Enums\LogCategory;
use Illuminate\Database\Eloquent\Builder;

class LogsTable extends BaseTable
{
    public ?LogLevel $filterLevel = null;
    public ?LogCategory $filterCategory = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    protected $queryString = [
        'search'        => ['except' => ''],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => null],
        'sortDirection' => ['except' => null],
        'filterLevel'   => ['except' => ''],
        'filterCategory'=> ['except' => ''],
        'dateFrom'      => ['except' => null],
        'dateTo'        => ['except' => null],
    ];

    protected function model(): string
    {
        return Log::class;
    }

    /** Standard-Sortierung für Logs */
    protected function defaultSortField(): string
    {
        return 'created_at';
    }

    protected function defaultSortDirection(): string
    {
        return 'desc';
    }

    protected function getColumns(): array
    {
        return [
            'id' => [
                'label'    => 'ID',
                'sortable' => true,
                'hidden'   => true,
            ],
            'created_at' => [
                'label'    => 'Datum',
                'sortable' => true,
            ],
            'level' => [
                'label'    => 'Level',
                'sortable' => true,
            ],
            'category' => [
                'label'    => 'Kategorie',
                'sortable' => true,
            ],
            'message' => [
                'label'      => 'Nachricht',
                'sortable'   => true,
                'searchable' => true,
            ],
            'actions' => [
                'label'    => 'Aktionen',
                'sortable' => false,
                'class'    => 'shrink',
            ],
        ];
    }

    /** Filter-Logik */
	protected function applyFilters(Builder $query): void
	{
		if ($this->filterLevel) 
		{
			$query->where('level', $this->filterLevel->value);
		}

		if ($this->filterCategory) 
		{
			$query->where('category', $this->filterCategory->value);
		}

		if ($this->dateFrom) 
		{
			$query->where('created_at', '>=', $this->dateFrom);
		}

		if ($this->dateTo) 
		{
			$query->where('created_at', '<=', $this->dateTo);
		}
	}


    public function resetFilters(): void
    {
        $this->reset(['filterLevel', 'filterCategory', 'dateFrom', 'dateTo']);
    }

    protected function getColumnButtons(): array
    {
        return [
            'actions' => [
                [
                    'method'  => 'openContextModal',
                    'idParam' => 'id',
                    'icon'    => 'mdi mdi-eye',
                    // 'label'   => 'Details',
                ],
            ],
        ];
    }

    public function openContextModal(int $id): void
    {
        $this->dispatch('open-modal', 'log-context-modal', ['id' => $id]);
    }

    /** Damit Pagination nach Filteränderung zurückspringt */
    public function updating($name, $value)
    {
        if (in_array($name, ['filterLevel', 'filterCategory', 'dateFrom', 'dateTo'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        return view('livewire.components.tables.logs-table', [
            'columns' => $this->getColumns(),
            'records' => $this->records,
        ]);
    }

	public function setToday(): void
	{
		// Von = heute, gleiche Uhrzeit wie jetzt
		$this->dateFrom = now()->format('Y-m-d\TH:i');

		// Bis = ebenfalls jetzt (kannst auch endOfDay nehmen, falls alles bis Tagesende gemeint ist)
		$this->dateTo = now()->format('Y-m-d\TH:i');
	}

	public function setLastWeek(): void
	{
		// Von = gleiche Uhrzeit, aber vor 7 Tagen
		$this->dateFrom = now()->subWeek()->format('Y-m-d\TH:i');

		// Bis = aktuelle Uhrzeit heute
		$this->dateTo = now()->format('Y-m-d\TH:i');
	}

}
