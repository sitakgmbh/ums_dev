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
        "search"        => ["except" => ""],
        "perPage"       => ["except" => 10],
        "sortField"     => ["except" => null],
        "sortDirection" => ["except" => null],
        "filterLevel"   => ["except" => ""],
        "filterCategory"=> ["except" => ""],
        "dateFrom"      => ["except" => null],
        "dateTo"        => ["except" => null],
    ];

    protected function model(): string
    {
        return Log::class;
    }

    protected function defaultSortField(): string
    {
        return "created_at";
    }

    protected function defaultSortDirection(): string
    {
        return "desc";
    }

    protected function getColumns(): array
    {
        return [
            "id" => [
                "label"    => "ID",
                "sortable" => true,
                "hidden"   => true,
            ],
            "created_at" => [
                "label"    => "Datum",
                "sortable" => true,
            ],
            "level" => [
                "label"    => "Level",
                "sortable" => true,
            ],
            "category" => [
                "label"    => "Kategorie",
                "sortable" => true,
            ],
            "message" => [
                "label"      => "Nachricht",
                "sortable"   => true,
                "searchable" => true,
            ],
            "actions" => [
                "label"    => "Aktionen",
                "sortable" => false,
                "class"    => "shrink",
            ],
        ];
    }

	protected function applyFilters(Builder $query): void
	{
		if ($this->filterLevel) 
		{
			$query->where("level", $this->filterLevel->value);
		}

		if ($this->filterCategory) 
		{
			$query->where("category", $this->filterCategory->value);
		}

		if ($this->dateFrom) 
		{
			$query->where("created_at", ">=", $this->dateFrom);
		}

		if ($this->dateTo) 
		{
			$query->where("created_at", "<=", $this->dateTo);
		}
	}

    public function resetFilters(): void
    {
        $this->reset(["filterLevel", "filterCategory", "dateFrom", "dateTo"]);
    }

	protected function getColumnBadges(): array
	{
		return [
			"level" => [
				"error" => [
					"label" => LogLevel::Error->label(),
					"class" => "danger",
				],
				"warning" => [
					"label" => LogLevel::Warning->label(),
					"class" => "warning",
				],
				"info" => [
					"label" => LogLevel::Info->label(),
					"class" => "info",
				],
				"debug" => [
					"label" => LogLevel::Debug->label(),
					"class" => "secondary",
				],
			],
		];
	}

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "method"  => "openContextModal",
                    "idParam" => "id",
                    "icon"    => "mdi mdi-eye",
                    "title"   => "Details",
                ],
            ],
        ];
    }

	protected function getColumnFormatters(): array
	{
		return [
			"level" => fn($row) => LogLevel::from(is_string($row->level) ? $row->level : $row->level->value)->label(),
			"category" => fn($row) => LogCategory::from(is_string($row->category) ? $row->category : $row->category->value)->label(),
		];
	}

	public function renderCell(string $field, $row)
	{
		$columns = $this->getColumns();
		$col = $columns[$field] ?? null;
		$value = data_get($row, $field);

		if ($field === 'level') 
		{
			$key = is_string($value)
				? strtolower($value)
				: strtolower($value->value ?? '');
			$badges = $this->getColumnBadges()['level'] ?? [];

			if (isset($badges[$key])) 
			{
				$badge = $badges[$key];
				
				return view('livewire.components.tables.base-table-badge', [
					'label' => $badge['label'],
					'class' => $badge['class'],
					'icon'  => null,
				]);
			}
		}

		return parent::renderCell($field, $row);
	}

    public function openContextModal(int $id): void
    {
        $this->dispatch("open-modal", "components.modals.log-context", ["id" => $id]);
    }

    public function updating($name, $value)
    {
        if (in_array($name, ["filterLevel", "filterCategory", "dateFrom", "dateTo"])) 
		{
            $this->resetPage();
        }
    }

    public function render()
    {
        return view("livewire.components.tables.logs-table", [
            "columns" => $this->getColumns(),
            "records" => $this->records,
        ]);
    }

	public function setToday(): void
	{
		$this->dateFrom = now()->format("Y-m-d\TH:i");
		$this->dateTo = now()->format("Y-m-d\TH:i");
	}

	public function setLastWeek(): void
	{
		$this->dateFrom = now()->subWeek()->format("Y-m-d\TH:i");
		$this->dateTo = now()->format("Y-m-d\TH:i");
	}
	
	protected function getTableActions(): array
	{
		return [
			[
				"method" => "exportCsv",
				"icon"   => "mdi mdi-tray-arrow-down",
				"iconClass" => "text-secondary",
				"class"  => "btn-outline-light",
				"title"  => "Tabelle als CSV-Datei exportieren",
			],
		];
	}
}
