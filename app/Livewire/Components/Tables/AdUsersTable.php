<?php
namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\AdUser;
use Illuminate\Database\Eloquent\Builder;

class AdUsersTable extends BaseTable
{
    public bool $showInactive = true; // Standard: Deaktivierte werden angezeigt
    public bool $showDeleted = false;

    protected $queryString = [
        "showInactive" => ["except" => true],
        "showDeleted" => ["except" => false],
        "search" => ["except" => ""],
        "perPage" => ["except" => 10],
        "sortField" => ["except" => null],
        "sortDirection" => ["except" => null],
    ];

    public function toggleInactive(): void
    {
        $this->showInactive = !$this->showInactive;
        $this->resetPage();
    }

    public function toggleDeleted(): void
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    protected function model(): string
    {
        return AdUser::class;
    }

    protected function getColumns(): array
    {
        return [
            "display_name"   => ["label" => "Anzeigename", "sortable" => true, "searchable" => true],
            "firstname"      => ["label" => "Vorname", "sortable" => true, "searchable" => true],
            "lastname"       => ["label" => "Nachname", "sortable" => true, "searchable" => true],
            "username"       => ["label" => "Benutzername", "sortable" => true, "searchable" => true],
            "is_enabled"     => ["label" => "Account-Status", "sortable" => true, "searchable" => false],
            "is_existing"    => ["label" => "AD-Status", "sortable" => true, "searchable" => false],
            "actions"        => ["label" => "Aktionen", "sortable" => false, "searchable" => false, "class" => "shrink"],
        ];
    }

    protected function defaultSortField(): string
    {
        return "display_name";
    }

    protected function defaultSortDirection(): string
    {
        return "asc";
    }

	protected function applyFilters(Builder $query): void
	{
		if (!$this->showDeleted) 
		{
			$query->where("is_existing", true);
		}

		if (!$this->showInactive) 
		{
			$query->where("is_enabled", true);
		}

		if ($this->search) 
		{
			$search = strtolower($this->search);

			$query->where(function ($q) use ($search) {
				$q->orWhereRaw("LOWER(display_name) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(firstname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(lastname) LIKE ?", ["%{$search}%"])
				  ->orWhereRaw("LOWER(username) LIKE ?", ["%{$search}%"]);
			});
		}
	}

    protected function getColumnBadges(): array
    {
        return [
            "is_enabled" => [
                true  => ["label" => "Aktiviert", "class" => "success"],
                false => ["label" => "Deaktiviert", "class" => "secondary"],
            ],
            "is_existing" => [
                true  => ["label" => "Vorhanden", "class" => "success"],
                false => ["label" => "Gelöscht", "class" => "secondary"],
            ],
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            "actions" => [
                [
                    "url"  => fn($row) => route("admin.ad-users.show", $row->id),
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
                "method" => "toggleInactive",
                "icon"   => $this->showInactive ? "mdi mdi-lock" : "mdi mdi-lock-outline",
                "iconClass" => "text-secondary",
                "class"  => $this->showInactive ? "btn-light" : "btn-outline-light",
                "title"  => $this->showInactive ? "Deaktivierte Benutzer ausblenden" : "Deaktivierte Benutzer anzeigen",
            ],
            [
                "method" => "toggleDeleted",
                "icon"   => $this->showDeleted ? "mdi mdi-trash-can" : "mdi mdi-trash-can-outline",
                "iconClass" => "text-secondary",
                "class"  => $this->showDeleted ? "btn-light" : "btn-outline-light",
                "title"  => $this->showDeleted ? "Gelöschte Benutzer ausblenden" : "Gelöschte Benutzer anzeigen",
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