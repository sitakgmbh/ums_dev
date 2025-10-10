<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Austritt;
use Illuminate\Database\Eloquent\Builder;

class AustritteAdminTable extends BaseTable
{
    protected $listeners = ['austritt-deleted' => '$refresh'];

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
        return Austritt::class;
    }

    protected function getColumns(): array
    {
        return [
            "status_badges"       => [
                "label"      => "Status",
                "sortable"   => false,
                "searchable" => false,
                "raw"        => true,
            ],
            "owner.display_name"  => [ "label" => "Besitzer", "sortable" => true ],
            "vertragsende"        => [ "label" => "Vertragsende", "sortable" => true ],
            "adUser.firstname"    => [ "label" => "Vorname", "sortable" => true ],
            "adUser.lastname"     => [ "label" => "Name", "sortable" => true ],
            "adUser.initials"     => [ "label" => "Personalnummer", "sortable" => true ],
            "adUser.username"     => [ "label" => "Benutzername", "sortable" => true ],
            "actions"             => [ "label" => "Aktionen", "sortable" => false, "class" => "shrink" ],
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

    protected array $searchable = ["adUser.firstname", "adUser.lastname", "adUser.initials", "adUser.username"];

    protected function applyFilters(Builder $query): void
    {
        if ($this->onlyMine && auth()->check()) 
		{
            $adUserId = auth()->user()->adUser?->id;
			
            if ($adUserId) 
			{
                $query->where("austritte.owner_id", $adUserId);
            }
        }

        if (! $this->showArchived) 
		{
            $query->where("austritte.archiviert", false);
        }

        if ($this->onlyUnassigned) 
		{
            $query->whereNull("austritte.owner_id");
        }

        if ($this->search) 
		{
            $search = strtolower($this->search);
			
            $query->where(function ($q) use ($search) {
                $q->orWhereRaw("LOWER(austritte.vertragsende) LIKE ?", ["%{$search}%"])
                  ->orWhereHas("adUser", function ($sub) use ($search) {
                      $sub->whereRaw("LOWER(ad_users.firstname) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(ad_users.lastname) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(ad_users.initials) LIKE ?", ["%{$search}%"])
                          ->orWhereRaw("LOWER(ad_users.username) LIKE ?", ["%{$search}%"]);
                  });
            });
        }
    }

    protected function getCustomSorts(): array
    {
        return [
            "owner.display_name" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users as owner", "austritte.owner_id", "=", "owner.id")
                    ->orderBy("owner.display_name", $direction)
                    ->select("austritte.*");
            },
            "adUser.firstname" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users", "austritte.ad_user_id", "=", "ad_users.id")
                    ->orderBy("ad_users.firstname", $direction)
                    ->select("austritte.*");
            },
            "adUser.lastname" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users", "austritte.ad_user_id", "=", "ad_users.id")
                    ->orderBy("ad_users.lastname", $direction)
                    ->select("austritte.*");
            },
            "adUser.initials" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users", "austritte.ad_user_id", "=", "ad_users.id")
                    ->orderBy("ad_users.initials", $direction)
                    ->select("austritte.*");
            },
            "adUser.username" => function ($query, $direction) {
                return $query
                    ->leftJoin("ad_users", "austritte.ad_user_id", "=", "ad_users.id")
                    ->orderBy("ad_users.username", $direction)
                    ->select("austritte.*");
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
                if ($row->status_pep)         $badges[] = $createBadge($row->status_pep, "mdi-clock", "PEP");
                if ($row->status_kis)         $badges[] = $createBadge($row->status_kis, "mdi-doctor", "KIS");
                if ($row->status_streamline)  $badges[] = $createBadge($row->status_streamline, "mdi-database", "Streamline");
                if ($row->status_tel)         $badges[] = $createBadge($row->status_tel, "mdi-phone", "Telefonie");
                if ($row->status_alarmierung) $badges[] = $createBadge($row->status_alarmierung, "mdi-bell", "Alarmierung");
                if ($row->status_logimen)     $badges[] = $createBadge($row->status_logimen, "mdi-clipboard-text", "Logimen");

                if ($row->archiviert) {
                    $badges[] = "<span class='badge bg-light text-dark p-1' title='Archiviert'>Archiv</span>";
                }

                return "<div class='d-inline-flex align-items-center gap-1 flex-nowrap' style='white-space:nowrap;'>"
                    . implode('', $badges)
                    . "</div>";
            },
            "vertragsende" => function ($row) {
                return $row->vertragsende?->format("d.m.Y");
            },
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"   => fn($row) => route("admin.austritte.verarbeitung", $row->id),
                    "icon"  => "mdi mdi-hammer-screwdriver",
                    "class" => "text-info",
                    "title" => "Austritt verarbeiten",
                ],
                [
                    "method"  => "openDeleteModal",
                    "idParam" => "id",
                    "icon"    => "mdi mdi-delete",
                    "title"   => "Austritt löschen",
                ],
            ],
        ];
    }

    public function openDeleteModal(int $id): void
    {
        $this->dispatch("open-modal", "components.modals.austritte.delete", ["id" => $id]);
    }

    protected function getTableActions(): array
    {
        return [
            [
                "method" => "toggleMine",
                "icon"   => "mdi mdi-account",
                "iconClass" => "text-secondary",
                "class"  => $this->onlyMine ? "btn-light" : "btn-outline-light",
                "title"  => $this->onlyMine ? "Alle Austritte anzeigen" : "Nur meine Austritte anzeigen",
            ],
            [
                "method" => "toggleArchived",
                "icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
                "iconClass" => "text-secondary",
                "class"  => $this->showArchived ? "btn-light" : "btn-outline-light",
                "title"  => $this->showArchived ? "Archivierte Austritte ausblenden" : "Archivierte Austritte anzeigen",
            ],
            [
                "method" => "toggleUnassigned",
                "icon"   => "mdi mdi-account-off",
                "iconClass" => "text-secondary",
                "class"  => $this->onlyUnassigned ? "btn-light" : "btn-outline-light",
                "title"  => $this->onlyUnassigned ? "Alle Austritte anzeigen" : "Nur Austritte ohne Besitzer anzeigen",
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
