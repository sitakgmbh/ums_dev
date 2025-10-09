<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\AdUser;

class AdUsersTable extends BaseTable
{
    protected function model(): string
    {
        return AdUser::class;
    }

	protected function getColumns(): array
	{
		return [
			"username"       => ["label" => "Benutzername", "sortable" => true, "searchable" => true],
			"display_name"   => ["label" => "Anzeigename", "sortable" => true, "searchable" => true],
			"firstname"      => ["label" => "Vorname", "sortable" => true, "searchable" => true],
			"lastname"       => ["label" => "Nachname", "sortable" => true, "searchable" => true],
			"email"          => ["label" => "E-Mail", "sortable" => true, "searchable" => true],
			"is_enabled"     => ["label" => "Status", "sortable" => true, "searchable" => false],
			"last_synced_at" => ["label" => "Zuletzt synchronisiert", "sortable" => true, "searchable" => false],
			"actions"        => ["label" => "Aktionen", "sortable" => false, "searchable" => false, "class" => "shrink"],
		];
	}

	protected function defaultSortField(): string
	{
		return "username";
	}

	protected function defaultSortDirection(): string
	{
		return "asc";
	}

    protected function getColumnBadges(): array
    {
        return [
            "is_enabled" => [
                true  => ["label" => "Aktiviert", "class" => "success"],
                false => ["label" => "Deaktiviert", "class" => "secondary"],
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
				"method" => "exportCsv",
				"icon"   => "mdi mdi-tray-arrow-down",
				"iconClass" => "text-secondary",
				"class"  => "btn-outline-light",
				"title"  => "Tabelle als CSV-Datei exportieren",
			],
		];
	}

}
