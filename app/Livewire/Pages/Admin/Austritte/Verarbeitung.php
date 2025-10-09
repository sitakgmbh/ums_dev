<?php

namespace App\Livewire\Pages\Admin\Austritte;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Austritt;
use App\Utils\AntragHelper;

#[Layout('layouts.app')]
class Verarbeitung extends Component
{
    public Austritt $entry;

    protected $listeners = [
        'pep-updated'         => 'refreshEntry',
        'kis-updated'         => 'refreshEntry',
        'telefonie-updated'   => 'refreshEntry',
        'streamline-updated'  => 'refreshEntry',
        'alarmierung-updated' => 'refreshEntry',
        'logimen-updated'     => 'refreshEntry',
        'besitzer-updated'    => 'refreshEntry',
        'archiviert-updated'  => 'refreshEntry',
        'austritt-deleted'    => 'refreshEntry',
    ];

    public function mount(Austritt $austritt): void
    {
        $this->entry = $austritt;
    }

    public function refreshEntry(): void
    {
        $this->entry->refresh();
    }

    public function render()
    {
        $tasksConfig   = config('ums.austritt.taskDefinitions');
        $detailsConfig = config('ums.austritt.detailsSections');

        $this->entry->load([
            'adUser.anrede', 'adUser.titel',
            'adUser.arbeitsort', 'adUser.unternehmenseinheit', 'adUser.abteilung', 'adUser.funktion',
            'owner',
        ]);

        $status = AntragHelper::statusForVerarbeitung($this->entry, auth()->user());

        return view('livewire.pages.admin.austritte.verarbeitung', [
            'entry'          => $this->entry,
            'detailsConfig'  => $detailsConfig,
            'tasksConfig'    => $tasksConfig,
            'canEdit'        => $status['canEdit'],
            'statusMessages' => $status['messages'],
        ])->layoutData([
            'pageTitle' => "Verarbeitung Austritt "
                . ($this->entry->adUser->lastname ?? '') . " "
                . ($this->entry->adUser->firstname ?? ''),
        ]);
    }
}
