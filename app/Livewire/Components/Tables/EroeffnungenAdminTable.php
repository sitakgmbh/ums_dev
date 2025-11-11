<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Eroeffnung;
use Illuminate\Database\Eloquent\Builder;

class EroeffnungenAdminTable extends BaseTable
{
    protected $listeners = ['eroeffnung-deleted' => '$refresh'];

	public bool $onlyMine = false;
    public bool $showArchived = false;
	public bool $onlyUnassigned = false;
	public bool $onlyVorabLizenzierung = false;

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

	public function toggleVorabLizenzierung(): void
	{
		$this->onlyVorabLizenzierung = ! $this->onlyVorabLizenzierung;
		$this->resetPage();
	}

    protected function model(): string
    {
        return Eroeffnung::class;
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
			"vertragsbeginn" => [ "label" => "Eintrittsdatum", "sortable" => true ],
            "anrede.name" => [ "label" => "Anrede", "sortable" => true ],
            "titel.name" => [ "label" => "Titel", "sortable" => true ],
            "nachname" => [ "label" => "Name", "sortable" => true ],
            "vorname" => [ "label" => "Vorname", "sortable" => true ],
            "arbeitsort.name" => [ "label" => "Arbeitsort", "sortable" => true ],
            "funktion.name" => [ "label" => "Funktion", "sortable" => true ],
			"antragsteller.display_name" => ["label" => "Antragsteller", "sortable" => true, "searchable" => true],
            "bezugsperson.display_name" => [ "label" => "Bezugsperson", "sortable" => true ],
            "vorlageBenutzer.display_name" => [ "label" => "Berechtigungen", "sortable" => true ],
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
				$query->where("eroeffnungen.owner_id", $adUserId);
			}
		}

		if (! $this->showArchived) 
		{
			$query->where("eroeffnungen.archiviert", false);
		}

		if ($this->onlyUnassigned) 
		{
			$query->whereNull("eroeffnungen.owner_id");
		}

		if ($this->onlyVorabLizenzierung) 
		{
			$query->where("eroeffnungen.vorab_lizenzierung", true);
		}

		if ($this->search) 
		{
			$search = strtolower($this->search);

			$query->where(function ($q) use ($search) {
				$q->orWhereRaw("LOWER(eroeffnungen.vertragsbeginn) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(eroeffnungen.nachname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(eroeffnungen.vorname) LIKE ?", ["%{$search}%"])
				  ->orWhereHas("anrede", fn($sub) => 
					  $sub->whereRaw("LOWER(anreden.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("titel", fn($sub) => 
					  $sub->whereRaw("LOWER(titel.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("arbeitsort", fn($sub) => 
					  $sub->whereRaw("LOWER(arbeitsorte.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("funktion", fn($sub) => 
					  $sub->whereRaw("LOWER(funktionen.name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("antragsteller", fn($sub) => 
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
				  ->orWhereHas("bezugsperson", fn($sub) => 
					  $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
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
            "antragsteller.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as antragsteller", "eroeffnungen.antragsteller_id", "=", "antragsteller.id")
                    ->orderBy("antragsteller.display_name", $direction)
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
			"owner.display_name" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as owner", "eroeffnungen.owner_id", "=", "owner.id")
					->orderBy("owner.display_name", $direction)
					->select("eroeffnungen.*");
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

				if ($row->status_ad)       $badges[] = $createBadge($row->status_ad, "mdi-account", "Active Directory");
				if ($row->status_pep)      $badges[] = $createBadge($row->status_pep, "mdi-clock", "PEP");
				if ($row->status_kis)      $badges[] = $createBadge($row->status_kis, "mdi-doctor", "KIS");
				if ($row->status_tel)      $badges[] = $createBadge($row->status_tel, "mdi-phone", "Telefonie");
				if ($row->status_auftrag)  $badges[] = $createBadge($row->status_auftrag, "mdi-clipboard-text", "Aufträge");
				if ($row->status_info)     $badges[] = $createBadge($row->status_info, "mdi-information-variant", "Info-Mail");

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

				if ($row->wiedereintritt) 
				{
					$badges[] = "<span title='Wiedereintritt'>
									<i class='mdi mdi-exclamation-thick text-danger'></i>
								 </span>";
				}

				if ($row->is_lei) 
				{
					$badges[] = "<span title='Leistungserbringer'>
									<i class='mdi mdi-hospital-building text-info'></i>
								 </span>";
				}

				if ($row->vorab_lizenzierung) 
				{
					$badges[] = "<span title='M365-Lizenz bei Erstellung AD-Benutzer zuweisen'>
									<i class='mdi mdi-license text-info'></i>
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
                    "url"   => fn($row) => route("admin.eroeffnungen.verarbeitung", $row->id),
                    "icon"  => "mdi mdi-hammer-screwdriver",
                    "class" => "text-info",
                    "title" => "Antrag verarbeiten",
                ],
                [
                    "url"   => fn($row) => route("eroeffnungen.edit", $row->id),
                    "icon"  => "mdi mdi-square-edit-outline",
                    "title" => "Antrag bearbeiten",
                ],
                [
                    "method"  => "openDeleteModal",
                    "idParam" => "id",
                    "icon"    => "mdi mdi-delete",
                    "title"   => "Antrag löschen",
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
				"method" => "toggleMine",
				"icon"   => "mdi mdi-account",
				"iconClass" => "text-secondary",
				"class"  => $this->onlyMine ? "btn-light" : "btn-outline-light",
				"title"  => $this->onlyMine ? "Alle Anträge anzeigen" : "Nur meine Anträge anzeigen",
			],
			[
				"method" => "toggleVorabLizenzierung",
				"icon"   => "mdi mdi-license",
				"iconClass" => "text-secondary",
				"class"  => $this->onlyVorabLizenzierung ? "btn-light" : "btn-outline-light",
				"title"  => $this->onlyVorabLizenzierung ? "Alle Anträge anzeigen" : "Nur Anträge mit vorab Lizenzierung anzeigen",
			],
			[
				"method" => "toggleUnassigned",
				"icon"   => "mdi mdi-account-off",
				"iconClass" => "text-secondary",
				"class"  => $this->onlyUnassigned ? "btn-light" : "btn-outline-light",
				"title"  => $this->onlyUnassigned ? "Alle Anträge anzeigen" : "Nur Anträge ohne Besitzer anzeigen",
			],
			[
				"method" => "toggleArchived",
				"icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
				"iconClass" => $this->showArchived ? "text-secondary" : "text-secondary",
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
