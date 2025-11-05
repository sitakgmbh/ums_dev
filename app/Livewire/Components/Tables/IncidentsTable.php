<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Incident;
use Illuminate\Database\Eloquent\Builder;

class IncidentsTable extends BaseTable
{
    public bool $showResolved = false; // Standardmäßig nur offene anzeigen

    protected $queryString = [
        "showResolved" => ["except" => false],
        "search"       => ["except" => ""],
        "perPage"      => ["except" => 10],
        "sortField"    => ["except" => null],
        "sortDirection"=> ["except" => null],
    ];

    public function toggleResolved(): void
    {
        $this->showResolved = !$this->showResolved;
        $this->resetPage();
    }

    protected function model(): string
    {
        return Incident::class;
    }

    protected function getColumns(): array
    {
        return [
            "priority"      => ["label" => "Priorität", "sortable" => true, "searchable" => true],
            "title"         => ["label" => "Titel", "sortable" => true, "searchable" => true],
            "created_at"    => ["label" => "Erstellt am", "sortable" => true, "searchable" => true],
            "resolved_at"   => ["label" => "Gelöst am", "sortable" => true, "searchable" => true],
            "actions"       => ["label" => "Aktionen", "sortable" => false, "searchable" => false, "class" => "shrink"],
        ];
    }

    protected function defaultSortField(): string
    {
        return "created_at";
    }

    protected function defaultSortDirection(): string
    {
        return "desc";
    }

    protected function applyFilters(Builder $query): void
    {
        // Wenn $showResolved = true → alle Incidents, sonst nur offene
        if (!$this->showResolved) {
            $query->open();
        }

        // Suche
        if ($this->search) {
            $search = strtolower($this->search);

            $query->where(function ($q) use ($search) {
                $q->orWhereRaw("LOWER(title) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(priority) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(resolved_at, '%d.%m.%Y %H:%i') LIKE ?", ["%{$search}%"]);
            });
        }
    }

    protected function getColumnBadges(): array
    {
        return [
            "priority" => [
                "critical" => ["label" => "Kritisch", "class" => "danger"],
                "high"     => ["label" => "Hoch", "class" => "warning"],
                "medium"   => ["label" => "Mittel", "class" => "info"],
                "low"      => ["label" => "Tief", "class" => "secondary"],
            ],
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"  => fn($row) => route("admin.incidents.show", $row->id),
                    "icon" => "mdi mdi-eye",
                    "title" => "Details",
                ],
            ],
        ];
    }

    protected function getTableActions(): array
    {
        return [
            [
                "method" => "toggleResolved",
                "icon"   => $this->showResolved ? "mdi mdi-checkbox-marked-outline" : "mdi mdi-checkbox-blank-outline",
                "iconClass" => "text-secondary",
                "class"  => $this->showResolved ? "btn-light" : "btn-outline-light",
                "title"  => $this->showResolved ? "Nur offene anzeigen" : "Alle anzeigen",
            ],
        ];
    }
}
