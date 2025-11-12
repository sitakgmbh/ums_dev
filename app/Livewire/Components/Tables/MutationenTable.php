<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Mutation;
use Illuminate\Database\Eloquent\Builder;

class MutationenTable extends BaseTable
{
    protected $listeners = ['mutation-deleted' => '$refresh'];

    public bool $showArchived = false;
    public bool $showAllAntraege = false; // NEU

    protected $queryString = [
        "showArchived"  => ["except" => false],
        "showAllAntraege" => ["except" => false], // NEU
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

    public function toggleAllAntraege(): void // NEU
    {
        $this->showAllAntraege = ! $this->showAllAntraege;
        $this->resetPage();
    }

    protected function model(): string
    {
        return Mutation::class;
    }

    protected function getColumns(): array
    {
        return [
            "status" => ["label" => "Status", "sortable" => false, "searchable" => false],
            "vertragsbeginn" => ["label" => "Änderungsdatum", "sortable" => true],
            "adUser.display_name" => ["label" => "Benutzer", "sortable" => true],
            "antragsteller.display_name" => ["label" => "Antragsteller", "sortable" => true],
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
			"adUser.display_name" => function ($query, $direction) {
				return $query
					->leftJoin("ad_users as benutzer", "mutationen.ad_user_id", "=", "benutzer.id")
					->orderBy("benutzer.display_name", $direction)
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
	
protected function applyFilters(Builder $query): void
{
    $user = auth()->user();
    $adUserId = $user?->adUser?->id;

    if (!$adUserId) {
        $query->whereRaw("1 = 0");
        return;
    }

    if ($this->showAllAntraege) {
        // 1️⃣ User-IDs, die mich als Stellvertreter eingetragen haben
        $userIds = \App\Models\Stellvertretung::where('ad_user_id', $adUserId)
            ->pluck('user_id')
            ->toArray();

        // 2️⃣ Deren AD-SIDs holen
        $adSids = \App\Models\User::whereIn('id', $userIds)
            ->whereNotNull('ad_sid')
            ->pluck('ad_sid')
            ->toArray();

        // 3️⃣ Deren AD-User-IDs für die Mutationen
        $adUserIds = \App\Models\AdUser::whereIn('sid', $adSids)
            ->pluck('id')
            ->toArray();

        // 4️⃣ Eigene ID immer mit dazu
        $alleAdUserIds = array_merge([$adUserId], $adUserIds);

        $query->whereIn('mutationen.antragsteller_id', $alleAdUserIds);
    } else {
        $query->where('mutationen.antragsteller_id', $adUserId);
    }

    if (!$this->showArchived) {
        $query->where('mutationen.archiviert', false);
    }

    if ($this->search) {
        $search = strtolower($this->search);

        $query->where(function ($q) use ($search) {
            $q->orWhereRaw("LOWER(mutationen.vertragsbeginn) LIKE ?", ["%{$search}%"])
                ->orWhereHas("adUser", fn($sub) =>
                    $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]))
                ->orWhereHas("antragsteller", fn($sub) =>
                    $sub->whereRaw("LOWER(ad_users.display_name) LIKE ?", ["%{$search}%"]));
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
				$status = \App\Utils\AntragHelper::getStatusBadge($row);
				$html = "<span class='{$status["class"]} d-inline-block text-center' style='min-width: 100px;'>{$status["label"]}</span>";
				
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
                    "url"    => fn($row) => route("mutationen.edit", $row->id),
                    "icon"   => "mdi mdi-square-edit-outline",
                    "showIf" => fn($row) => $row->status === 1,
                    "title"  => "Antrag bearbeiten",
                ],
                [
                    "method"  => "openDeleteModal",
                    "idParam" => "id",
                    "icon"    => "mdi mdi-delete",
                    "showIf"  => fn($row) => $row->status === 1,
                    "title"   => "Antrag löschen",
                ],
                [
                    "url"    => fn($row) => route("mutationen.show", $row->id),
                    "icon"   => "mdi mdi-eye",
                    "showIf" => fn($row) => $row->status !== 1,
                    "title"  => "Antrag einsehen",
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
        $actions = [];

		$actions[] = [
			"method" => "toggleAllAntraege",
			"icon"   => "mdi mdi-account-multiple",
			"iconClass" => "text-secondary",
			"class"  => $this->showAllAntraege ? "btn-light" : "btn-outline-light",
			"title"  => $this->showAllAntraege ? "Nur eigene Anträge anzeigen" : "Alle Anträge (inkl. Stellvertreter) anzeigen",
		];

        $actions[] = [
            "method" => "toggleArchived",
            "icon"   => $this->showArchived ? "mdi mdi-archive-eye" : "mdi mdi-archive",
            "iconClass" => "text-secondary",
            "class"  => $this->showArchived ? "btn-light" : "btn-outline-light",
            "title" => $this->showArchived ? "Archivierte Mutationen ausblenden" : "Archivierte Mutationen anzeigen",
        ];

        $actions[] = [
            "method" => "exportCsv",
            "icon"   => "mdi mdi-tray-arrow-down",
            "iconClass" => "text-secondary",
            "class"  => "btn-outline-light",
            "title"  => "Tabelle als CSV-Datei exportieren",
        ];

        return $actions;
    }
}