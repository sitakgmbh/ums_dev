<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\User;

class UsersTable extends BaseTable
{
    protected $listeners = ['user-deleted' => '$refresh'];

    protected function model(): string
    {
        return User::class;
    }

    protected function getColumns(): array
    {
        return [
			'auth_type'  => [ 'label' => 'Typ',     'sortable' => true ],
			'username'  => [ 'label' => 'Benutzername', 'sortable' => true, 'searchable' => true ],
			'firstname'  => [ 'label' => 'Vorname', 'sortable' => true, 'searchable' => true ],
            'lastname'   => [ 'label' => 'Nachname', 'sortable' => true, 'searchable' => true ],
            'email'      => [ 'label' => 'E-Mail',  'sortable' => true, 'searchable' => true ],
            'role'       => [ 'label' => 'Rolle',   'sortable' => true ],
            'is_enabled' => [ 'label' => 'Status',  'sortable' => true ],
			'created_at'  => [ 'label' => 'Erstellt', 'sortable' => true, 'searchable' => true ],
            'actions'    => [ 'label' => 'Aktionen','sortable' => false, 'class' => 'shrink' ],
        ];
    }

	protected function defaultSortField(): string
	{
		return 'username';
	}

	protected function defaultSortDirection(): string
	{
		return 'asc';
	}

    protected array $searchable = ['username', 'firstname', 'lastname', 'email', 'created_at'];

    protected function getColumnBadges(): array
    {
        return [
            'auth_type' => [
                'local' => ['label' => 'Local', 'class' => 'secondary'],
                'ldap'  => ['label' => 'LDAP',  'class' => 'info'],
            ],
            'is_enabled' => [
                true  => ['label' => 'Aktiviert', 'class' => 'success'],
                false => ['label' => 'Deaktiviert', 'class' => 'danger'],
                null  => ['label' => 'nicht verfügbar', 'class' => 'light text-dark'],
            ],
            'role' => [
                'admin' => ['label' => 'Admin', 'class' => 'dark'],
                'user'  => ['label' => 'User',  'class' => 'secondary'],
            ],
        ];
    }

	protected function transformRecord($record): array
	{
		if ($record instanceof \App\Models\User) 
		{
			return [
				...parent::transformRecord($record),
				'role' => $record->roles->pluck('name')->first() ?? '—',
			];
		}

		return parent::transformRecord($record);
	}


		/** Eager Loading für Rollen */
	protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query): void
	{
		$query->with('roles'); // Rollen mitladen

		// Debug-Ausgabe
		logger()->debug('applyFilters UsersTable', [
			'sql' => $query->toSql(),
			'bindings' => $query->getBindings(),
		]);

		// nach dem Laden ergänzen wir die erste Rolle
		$query->addSelect([
			'role' => \DB::table('model_has_roles')
				->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
				->whereColumn('model_has_roles.model_id', 'users.id')
				->limit(1)
				->select('roles.name'),
		]);
	}


    protected function getColumnButtons(): array
    {
        return [
            'actions' => [
                [
                    'url'   => fn($row) => route('admin.users.edit', $row->id),
                    'icon'  => 'mdi mdi-square-edit-outline',
                ],
                [
                    'method'  => 'openDeleteModal',
                    'idParam' => 'id',
                    'icon'    => 'mdi mdi-delete',
                ],
            ],
        ];
    }

    public function openDeleteModal(int $id): void
    {
        $this->dispatch('open-modal', 'user-delete-modal', ['id' => $id]);
    }
}
