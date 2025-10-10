<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Eroeffnung;
use Illuminate\Database\Eloquent\Builder;

class EroeffnungenTable extends BaseTable
{
    protected $listeners = ['eroeffnung-deleted' => '$refresh'];

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
        return Eroeffnung::class;
    }

    protected function getColumns(): array
    {
        return [
            "status" => ["label" => "Status", "sortable" => true, "searchable" => true],
            "vertragsbeginn" => ["label" => "Eintrittsdatum", "sortable" => true, "searchable" => false],
            "anrede.name" => ["label" => "Anrede", "sortable" => true, "searchable" => true],
            "titel.name" => ["label" => "Titel", "sortable" => true, "searchable" => true],
            "nachname" => ["label" => "Name", "sortable" => true, "searchable" => true],
            "vorname" => ["label" => "Vorname", "sortable" => true, "searchable" => true],
            "arbeitsort.name" => ["label" => "Arbeitsort", "sortable" => true, "searchable" => true],
            "funktion.name" => ["label" => "Funktion", "sortable" => true, "searchable" => true],
            "bezugsperson.display_name" => ["label" => "Bezugsperson", "sortable" => true, "searchable" => true],
            "vorlageBenutzer.display_name" => ["label" => "Berechtigungen", "sortable" => true, "searchable" => true],
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

    protected array $searchable = ["vorname", "nachname", "email"];

    protected function getCustomSorts(): array
    {
        return [
            "status" => function ($query, $direction) {
                return $query->orderByRaw("
                    CASE
                        WHEN status_info = 2 THEN 3
                        WHEN GREATEST(status_ad, status_tel, status_pep, status_kis, status_sap, status_auftrag) > 1 THEN 2
                        ELSE 1
                    END {$direction}
                ");
            },
            "anrede.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("anreden", "eroeffnungen.anrede_id", "=", "anreden.id")
                    ->orderBy("anreden.name", $direction)
                    ->select("eroeffnungen.*");
            },
            "titel.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("titel", "eroeffnungen.titel_id", "=", "titel.id")
                    ->orderBy("titel.name", $direction)
                    ->select("eroeffnungen.*");
            },
            "arbeitsort.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("arbeitsorte", "eroeffnungen.arbeitsort_id", "=", "arbeitsorte.id")
                    ->orderBy("arbeitsorte.name", $direction)
                    ->select("eroeffnungen.*");
            },
            "funktion.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("funktionen", "eroeffnungen.funktion_id", "=", "funktionen.id")
                    ->orderBy("funktionen.name", $direction)
                    ->select("eroeffnungen.*");
            },
            "bezugsperson.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as bezug", "eroeffnungen.bezugsperson_id", "=", "bezug.id")
                    ->orderBy("bezug.display_name", $direction)
                    ->select("eroeffnungen.*");
            },
            "vorlageBenutzer.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as vorlage", "eroeffnungen.vorlage_benutzer_id", "=", "vorlage.id")
                    ->orderBy("vorlage.display_name", $direction)
                    ->select("eroeffnungen.*");
            },
        ];
    }

    protected function applyFilters(Builder $query): void
    {
        $adUserId = auth()->user()?->adUser?->id;

        if ($adUserId) 
		{
            $query->where("eroeffnungen.antragsteller_id", $adUserId);
        }

        if (! $this->showArchived) 
		{
            $query->where("eroeffnungen.archiviert", false);
        }

        if ($this->search) 
		{
            $search = strtolower($this->search);

            $query->where(function ($q) use ($search) {
                $q->orWhereRaw("LOWER(eroeffnungen.vertragsbeginn) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(eroeffnungen.nachname) LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("LOWER(eroeffnungen.vorname) LIKE ?", ["%{$search}%"]);

                $q->orWhereHas("anrede", fn($sub) =>
                    $sub->whereRaw("LOWER(anreden.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("titel", fn($sub) =>
                    $sub->whereRaw("LOWER(titel.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("arbeitsort", fn($sub) =>
                    $sub->whereRaw("LOWER(arbeitsorte.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("funktion", fn($sub) =>
                    $sub->whereRaw("LOWER(funktionen.name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("bezugsperson", fn($sub) =>
                    $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
                  ->orWhereHas("vorlageBenutzer", fn($sub) =>
                    $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]));

                $statusLabels = [
                    1 => "neu",
                    2 => "bearbeitung",
                    3 => "abgeschlossen",
                ];

                foreach ($statusLabels as $code => $label) 
				{
                    if (str_contains($label, $search)) 
					{
                        $q->orWhereRaw("
                            CASE
                                WHEN status_info = 2 THEN 3
                                WHEN GREATEST(status_ad, status_tel, status_pep, status_kis, status_sap, status_auftrag) > 1 THEN 2
                                ELSE 1
                            END = ?
                        ", [$code]);
                    }
                }
            });
        }
    }

	protected function getColumnFormatters(): array
	{
		return [
			"vertragsbeginn" => function ($row) {
				return $row->vertragsbeginn?->format("d.m.Y");
			},
			"status" => function ($row) {
				$statusLabels = [
					1 => ["label" => "Neu",          "class" => "badge bg-secondary"],
					2 => ["label" => "Bearbeitung",  "class" => "badge bg-info"],
					3 => ["label" => "Abgeschlossen","class" => "badge bg-success"],
				];

				$status = $statusLabels[$row->status_info ?? 1] ?? ["label" => "-", "class" => "badge bg-light text-dark"];
				$html = "<span class='{$status["class"]}'>{$status["label"]}</span>";

				if ($row->archiviert) 
				{
					$html .= " <span class='badge bg-light text-dark p-1' title='Archiviert'>Archiv</span>";
				}

				return "<div class='d-inline-flex align-items-center gap-1 flex-nowrap' style='white-space:nowrap;'>{$html}</div>";
			},
		];
	}

	protected function getColumnButtons(): array
	{
		return [
			"actions" => [
				[
					"url"    => fn($row) => route("eroeffnungen.edit", $row->id),
					"icon"   => "mdi mdi-square-edit-outline",
					"showIf" => fn($row) => $row->status === 1, // Neu
					"title"  => "Antrag bearbeiten",
				],
				[
					"method"  => "openDeleteModal",
					"idParam" => "id",
					"icon"    => "mdi mdi-delete",
					"showIf"  => fn($row) => $row->status === 1, // Neu
					"title"   => "Antrag löschen",
				],
				[
					"url"    => fn($row) => route("eroeffnungen.show", $row->id),
					"icon"   => "mdi mdi-eye",
					"showIf" => fn($row) => $row->status !== 1, // nicht Neu
					"title"  => "Antrag einsehen",
				],
			],
		];
	}


    public function openDeleteModal(int $id): void
    {
        $this->dispatch("open-modal", "components.modals.eroeffnungen.delete", ["id" => $id]);
    }

    protected function getTableActions(): array
    {
        return [
            [
                "method" => "toggleArchived",
                "icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
                "iconClass" => "text-secondary",
                "class"  => $this->showArchived ? "btn-light" : "btn-outline-light",
                "title" => $this->showArchived ? "Archivierte Anträge ausblenden" : "Archivierte Anträge anzeigen",
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
