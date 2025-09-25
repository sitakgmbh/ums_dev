<?php

namespace App\Livewire\Components\Tables;

use App\Livewire\Components\Tables\BaseTable;
use App\Models\Eroeffnung;

class EroeffnungenTable extends BaseTable
{
    protected $listeners = ['eroeffnung-deleted' => '$refresh'];

    protected function model(): string
    {
        return Eroeffnung::class;
    }

    protected function getColumns(): array
    {
        return [
            'vorname'        => [ 'label' => 'Vorname',        'sortable' => true, 'searchable' => true ],
            'nachname'       => [ 'label' => 'Nachname',       'sortable' => true, 'searchable' => true ],
            'email'          => [ 'label' => 'E-Mail',         'sortable' => true, 'searchable' => true ],
            'vertragsbeginn' => [ 'label' => 'Vertragsbeginn', 'sortable' => true ],
            'wiedereintritt' => [ 'label' => 'Wiedereintritt', 'sortable' => true ],
            'created_at'     => [ 'label' => 'Erstellt',       'sortable' => true ],
            'actions'        => [ 'label' => 'Aktionen',       'sortable' => false, 'class' => 'shrink' ],
        ];
    }

    protected function defaultSortField(): string
    {
        return 'created_at';
    }

    protected function defaultSortDirection(): string
    {
        return 'desc';
    }

    protected array $searchable = ['vorname', 'nachname', 'email'];

    protected function getColumnBadges(): array
    {
        return [
            'wiedereintritt' => [
                true  => ['label' => 'Ja',  'class' => 'success'],
                false => ['label' => 'Nein', 'class' => 'secondary'],
            ],
        ];
    }

    protected function getColumnButtons(): array
    {
        return [
            'actions' => [
                [
                    'url'   => fn($row) => route('eroeffnungen.edit', $row->id),
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
        $this->dispatch('open-modal', 'eroeffnung-delete-modal', ['id' => $id]);
    }
}
