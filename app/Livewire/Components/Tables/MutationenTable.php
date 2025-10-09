<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Mutation;
use Illuminate\Database\Eloquent\Builder;

class MutationenTable extends BaseTable
{
    protected $listeners = ['mutation-deleted' => '$refresh'];

    public bool $showArchived = false;

    protected $queryString = [
        "showArchived"  => ["except" => false],
        "search"        => ["except" => ""],
        "perPage"       => ["except" => 10],
        "sortField"     => ["except" => null],
        "sortDirection" => ["except" => null],
    ];

    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->resetPage();
    }

    protected function model(): string
    {
        return Mutation::class;
    }

protected function getColumns(): array
{
    return [
        "status" => ["label" => "Status", "sortable" => true, "searchable" => false],
        "vertragsbeginn" => ["label" => "Änderungsdatum", "sortable" => true],
        "adUser.display_name" => ["label" => "Benutzer", "sortable" => true],
        "antragsteller.display_name" => ["label" => "Antragsteller", "sortable" => true],
        "owner.display_name" => ["label" => "Besitzer", "sortable" => true],
        "actions" => ["label" => "Aktionen", "sortable" => false, "class" => "shrink"],
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

    protected array $searchable = ["vorname", "nachname", "ticket_nr"];

    protected function getCustomSorts(): array
    {
        return [
            "gesamtstatus" => function ($query, $direction) {
                return $query->orderByRaw("
                    CASE
                        WHEN status_info = 2 THEN 3
                        WHEN status_ad = 2 
                          OR status_tel = 2
                          OR status_kis = 2
                          OR status_sap = 2
                          OR status_auftrag = 2
                        THEN 2
                        ELSE 1
                    END {$direction}
                ");
            },
            "anrede.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("anreden", "mutationen.anrede_id", "=", "anreden.id")
                    ->orderBy("anreden.name", $direction)
                    ->select("mutationen.*");
            },
            "titel.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("titel", "mutationen.titel_id", "=", "titel.id")
                    ->orderBy("titel.name", $direction)
                    ->select("mutationen.*");
            },
            "funktion.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("funktionen", "mutationen.funktion_id", "=", "funktionen.id")
                    ->orderBy("funktionen.name", $direction)
                    ->select("mutationen.*");
            },
            "bezugsperson.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as bezug", "mutationen.bezugsperson_id", "=", "bezug.id")
                    ->orderBy("bezug.display_name", $direction)
                    ->select("mutationen.*");
            },
            "owner.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as owner", "mutationen.owner_id", "=", "owner.id")
                    ->orderBy("owner.display_name", $direction)
                    ->select("mutationen.*");
            },
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        if (! $this->showArchived) {
            $query->where("mutationen.archiviert", false);
        }

        if ($this->search) {
            $search = strtolower($this->search);

            $query->where(function ($q) use ($search) {
                $q->orWhereRaw("LOWER(mutationen.vertragsbeginn) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(mutationen.ticket_nr) LIKE ?", ["%{$search}%"])
                  ->orWhereHas("anrede", fn($sub) =>
                      $sub->whereRaw("LOWER(anreden.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("titel", fn($sub) =>
                      $sub->whereRaw("LOWER(titel.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("funktion", fn($sub) =>
                      $sub->whereRaw("LOWER(funktionen.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("bezugsperson", fn($sub) =>
                      $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("owner", fn($sub) =>
                      $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]));
            });
        }
    }

    protected function getColumnFormatters(): array
    {
        return [
            "vertragsbeginn" => fn($row) => $row->vertragsbeginn?->format("d.m.Y"),
        ];
    }

protected function getColumnBadges(): array
{
    return [
        "status" => [
            1 => ["label" => "Neu",          "class" => "secondary"],
            2 => ["label" => "Bearbeitung",  "class" => "info"],
            3 => ["label" => "Abgeschlossen","class" => "success"],
        ],
    ];
}


    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"   => fn($row) => route("mutationen.edit", $row->id),
                    "icon"  => "mdi mdi-square-edit-outline",
                    "title" => "Mutation bearbeiten",
                ],
                [
                    "method"  => "openDeleteModal",
                    "idParam" => "id",
                    "icon"    => "mdi mdi-delete",
                    "title"   => "Mutation löschen",
                ],
                [
                    "url"   => fn($row) => route("mutationen.show", $row->id),
                    "icon"  => "mdi mdi-eye",
                    "title" => "Mutation ansehen",
                ],
            ],
        ];
    }

    public function openDeleteModal(int $id): void
    {
        $this->dispatch("open-modal", "components.modals.mutationen.delete", ["id" => $id]);
    }

    protected function getTableActions(): array
    {
        return [
            [
                "method" => "toggleArchived",
                "icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
                "iconClass" => "text-secondary",
                "class"  => $this->showArchived ? "btn-light" : "btn-outline-light",
                "title" => $this->showArchived ? "Archivierte Mutationen ausblenden" : "Archivierte Mutationen anzeigen",
            ],
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
