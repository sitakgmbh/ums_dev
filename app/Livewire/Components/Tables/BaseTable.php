<?php

namespace App\Livewire\Components\Tables;

use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseTable extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public string $sortField;
    public string $sortDirection;

    public bool $responsiveCollapse = false;

    protected $queryString = [
        'search'        => ['except' => ''],
        'perPage'       => ['except' => 10],
        'sortField'     => ['except' => null],
        'sortDirection' => ['except' => null],
    ];

    // Kind muss definieren
    abstract protected function model(): string;
    abstract protected function getColumns(): array;

    public function mount()
    {
        // Defaults setzen
        $this->sortField     = $this->defaultSortField();
        $this->sortDirection = $this->defaultSortDirection();
    }

    /** Überschreibbare Defaults */
    protected function defaultSortField(): string
    {
        return 'id';
    }

    protected function defaultSortDirection(): string
    {
        return 'asc';
    }

    protected function getColumnBadges(): array
    {
        return [];
    }

    protected function getColumnButtons(): array
    {
        return [];
    }

    /** Reset Pagination bei Suche */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /** Sortierlogik */
    public function sortBy(string $field): void
    {
        $columns = $this->getColumns();
        $col = $columns[$field] ?? null;

        // Nur sortieren, wenn sortable = true
        if (is_array($col) && ($col['sortable'] ?? true) === false) 
		{
            return;
        }

        if ($this->sortField === $field) 
		{
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else 
		{
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
		
        $this->resetPage();
    }

    /** Records laden */
    public function getRecordsProperty()
    {
        /** @var Builder $query */
        $query = $this->model()::query();

        // Filter anwenden (kann von Kindklasse überschrieben werden)
        $this->applyFilters($query);

        // Suche nur auf searchable Feldern
        if ($this->search && $this->getSearchableColumns()) 
		{
            $query->where(function ($q) {
                foreach ($this->getSearchableColumns() as $field) 
				{
                    $q->orWhere($field, 'like', "%{$this->search}%");
                }
            });
        }

        return $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    /** Suche-Spalten aus getColumns */
    protected function getSearchableColumns(): array
    {
        return collect($this->getColumns())
            ->filter(fn($col) => is_array($col) ? ($col['searchable'] ?? false) : false)
            ->keys()
            ->toArray();
    }

    /** CSV-Export */
    public function exportCsv(): StreamedResponse
    {
        $columns = $this->getColumns();
        $visibleColumns = collect($columns)
            ->reject(fn($col) => is_array($col) ? ($col['hidden'] ?? false) : false);

        $filename = 'export_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($visibleColumns) {
            $handle = fopen('php://output', 'w');

            // Labels
            fputcsv($handle, $visibleColumns->map(fn($col) => is_array($col) ? $col['label'] : $col)->toArray());

            // Rows
            $this->model()::chunk(500, function ($rows) use ($handle, $visibleColumns) {
                foreach ($rows as $item) {
                    $row = [];
                    foreach ($visibleColumns as $field => $col) {
                        $row[] = data_get($item, $field);
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /** Zell-Rendering (Badges/Buttons etc.) */
    public function renderCell(string $field, $row)
    {
        $value = data_get($row, $field);

        if ($badge = $this->getColumnBadges()[$field][$value] ?? null) 
		{
            return view('livewire.components.tables.base-table-badge', [
                'label' => $badge['label'] ?? $value,
                'class' => $badge['class'] ?? 'secondary',
                'icon'  => $badge['icon'] ?? null,
            ]);
        }

        if ($buttons = $this->getColumnButtons()[$field] ?? null) 
		{
            return view('livewire.components.tables.base-table-button', [
                'buttons' => $buttons,
                'row'     => $row,
            ]);
        }

        return e($value);
    }

    /** Optionale Filter – Default: keine */
    protected function applyFilters(Builder $query): void
    {
        // leer, Kindklasse kann überschreiben
    }

    public function render()
    {
        return view('livewire.components.tables.base-table', [
            'columns' => $this->getColumns(),
            'records' => $this->records,
        ]);
    }
}
