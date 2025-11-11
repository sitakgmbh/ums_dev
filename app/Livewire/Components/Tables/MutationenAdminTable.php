<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Mutation;
use Illuminate\Database\Eloquent\Builder;

class MutationenAdminTable extends BaseTable
{
    protected $listeners = ['mutation-deleted' => '$refresh'];

    public bool $onlyMine = false;
    public bool $showArchived = false;
    public bool $onlyUnassigned = false;

    protected $queryString = [
        "onlyMine" => ["except" => false],
        "showArchived" => ["except" => false],
        "onlyUnassigned" => ["except" => false],
        "search" => ["except" => ""],
        "perPage" => ["except" => 10],
        "sortField" => ["except" => null],
        "sortDirection" => ["except" => null],
    ];

    public function toggleMine(): void
    {
        $this->onlyMine = ! $this->onlyMine;
        $this->resetPage();
    }

    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
        $this->resetPage();
    }

    public function toggleUnassigned(): void
    {
        $this->onlyUnassigned = ! $this->onlyUnassigned;
        $this->resetPage();
    }

    protected function model(): string
    {
        return Mutation::class;
    }

    protected function getColumns(): array
    {
        return [
            "status_badges"     => [
                "label"      => "Status",
                "sortable"   => false,
                "searchable" => false,
                "raw"        => true,
            ],
            "actions" => [ "label" => "Aktionen", "sortable" => false, "class" => "shrink" ],
            "owner.display_name" => [ "label" => "Besitzer", "sortable" => true ],
            "vertragsbeginn"     => [ "label" => "Änderungsdatum", "sortable" => true ],
            "adUser.display_name"=> [ "label" => "Name", "sortable" => true ],
			"adUser.username"=> [ "label" => "Benutzername", "sortable" => true ],
			"adUser.initials"=> [ "label" => "Personalnummer", "sortable" => true ],
            "antragsteller.display_name"   => [ "label" => "Antragsteller", "sortable" => true ],
        ];
    }

    protected function defaultSortField(): string
    {
        return "vertragsbeginn";
    }

    protected function defaultSortDirection(): string
    {
        return "asc";
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->onlyMine && auth()->check()) 
		{
            $adUserId = auth()->user()->adUser?->id;

            if ($adUserId) 
			{
                $query->where("mutationen.owner_id", $adUserId);
            }
        }

        if (! $this->showArchived) 
		{
            $query->where("mutationen.archiviert", false);
        }

        if ($this->onlyUnassigned) 
		{
            $query->whereNull("mutationen.owner_id");
        }

		if ($this->search) 
		{
			$search = strtolower($this->search);

			$query->where(function ($q) use ($search) {
				$q->orWhereRaw("LOWER(mutationen.vertragsbeginn) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(mutationen.nachname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(mutationen.vorname) LIKE ?", ["%{$search}%"])
				  ->orWhereHas("adUser", fn($sub) =>
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"])
						  ->orWhereRaw("LOWER(ad_users.username) LIKE ?", ["%{$search}%"])
						  ->orWhereRaw("LOWER(ad_users.initials) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("owner", fn($sub) =>
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("antragsteller", fn($sub) =>
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("anrede", fn($sub) =>
					  $sub->whereRaw("LOWER(anreden.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("titel", fn($sub) =>
					  $sub->whereRaw("LOWER(titel.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("arbeitsort", fn($sub) =>
					  $sub->whereRaw("LOWER(arbeitsorte.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("funktion", fn($sub) =>
					  $sub->whereRaw("LOWER(funktionen.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("vorlageBenutzer", fn($sub) =>
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]));
			});
		}
    }

    protected function getCustomSorts(): array
    {
        return [
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
            "arbeitsort.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("arbeitsorte", "mutationen.arbeitsort_id", "=", "arbeitsorte.id")
                    ->orderBy("arbeitsorte.name", $direction)
                    ->select("mutationen.*");
            },
            "funktion.name" => function ($query, $direction) {
                return $query
                    ->leftJoin("funktionen", "mutationen.funktion_id", "=", "funktionen.id")
                    ->orderBy("funktionen.name", $direction)
                    ->select("mutationen.*");
            },
            "vorlageBenutzer.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as vorlage", "mutationen.vorlage_benutzer_id", "=", "vorlage.id")
                    ->orderBy("vorlage.display_name", $direction)
                    ->select("mutationen.*");
            },
			"owner.display_name" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as owner", "mutationen.owner_id", "=", "owner.id")
					->orderBy("owner.display_name", $direction)
					->select("mutationen.*");
			},
			"adUser.display_name" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as adUser", "mutationen.ad_user_id", "=", "adUser.id")
					->orderBy("adUser.display_name", $direction)
					->select("mutationen.*");
			},
			"adUser.username" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as adUserUser", "mutationen.ad_user_id", "=", "adUserUser.id")
					->orderBy("adUserUser.username", $direction)
					->select("mutationen.*");
			},
			"adUser.initials" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as adUserInit", "mutationen.ad_user_id", "=", "adUserInit.id")
					->orderBy("adUserInit.initials", $direction)
					->select("mutationen.*");
			},
			"antragsteller.display_name" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as antragsteller", "mutationen.antragsteller_id", "=", "antragsteller.id")
					->orderBy("antragsteller.display_name", $direction)
					->select("mutationen.*");
			},
        ];
    }

    protected function getColumnFormatters(): array
    {
        return [
            "status_badges" => function ($row) {
                $createBadge = function ($status, $icon, $title) {
                    $class = "badge bg-secondary";
                    if ($status === 2) $class = "badge bg-success";
                    elseif ($status === 3) $class = "badge border border-success text-success";
                    elseif ($status === 4) $class = "badge bg-warning";
                    elseif ($status === 5) $class = "badge bg-danger";

                    return "<span class=\"{$class} p-1\" title=\"{$title}\">
                                <i class=\"mdi {$icon}\"></i>
                            </span>";
                };

                $badges = [];

                if ($row->status_ad)      $badges[] = $createBadge($row->status_ad, "mdi-account-cog", "Berechtigungen");
				if ($row->status_mail)      $badges[] = $createBadge($row->status_ad, "mdi-email", "E-Mail");
                if ($row->status_kis)     $badges[] = $createBadge($row->status_kis, "mdi-doctor", "KIS");
                if ($row->status_tel)     $badges[] = $createBadge($row->status_tel, "mdi-phone", "Telefonie");
                if ($row->status_sap)     $badges[] = $createBadge($row->status_sap, "mdi-hospital-building", "SAP");
                if ($row->status_auftrag) $badges[] = $createBadge($row->status_auftrag, "mdi-clipboard-text", "Aufträge");

                if ($row->archiviert) 
				{
                    $badges[] = "<span class='badge bg-light text-dark p-1' title='Archiviert'>Archiv</span>";
                }

                if (!empty($row->kommentar)) 
				{
                    $badges[] = "<span title='" . e($row->kommentar) . "'>
                                    <i class='mdi mdi-comment text-info'></i>
                                 </span>";
                }

                if ($row->is_lei) 
				{
                    $badges[] = "<span title='Leistungserbringer'>
                                    <i class='mdi mdi-doctor text-info'></i>
                                 </span>";
                }

                return "<div class='d-inline-flex align-items-center gap-1 flex-nowrap' style='white-space:nowrap;'>"
                    . implode('', $badges)
                    . "</div>";
            },
            "vertragsbeginn" => function ($row) {
                return $row->vertragsbeginn?->format("d.m.Y");
            },
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"   => fn($row) => route("admin.mutationen.verarbeitung", $row->id),
                    "icon"  => "mdi mdi-hammer-screwdriver",
                    "class" => "text-info",
                    "title" => "Mutation verarbeiten",
                ],
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
                "method" => "toggleMine",
                "icon"   => "mdi mdi-account",
                "iconClass" => "text-secondary",
                "class"  => $this->onlyMine ? "btn-light" : "btn-outline-light",
                "title"  => $this->onlyMine ? "Alle Mutationen anzeigen" : "Nur meine Mutationen anzeigen",
            ],
            [
                "method" => "toggleArchived",
                "icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
                "iconClass" => "text-secondary",
                "class"  => $this->showArchived ? "btn-light" : "btn-outline-light",
                "title"  => $this->showArchived ? "Archivierte Mutationen ausblenden" : "Archivierte Mutationen anzeigen",
            ],
            [
                "method" => "toggleUnassigned",
                "icon"   => "mdi mdi-account-off",
                "iconClass" => "text-secondary",
                "class"  => $this->onlyUnassigned ? "btn-light" : "btn-outline-light",
                "title"  => $this->onlyUnassigned ? "Alle Mutationen anzeigen" : "Nur Mutationen ohne Besitzer anzeigen",
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
