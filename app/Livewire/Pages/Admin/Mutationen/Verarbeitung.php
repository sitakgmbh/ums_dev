<?php

namespace App\Livewire\Pages\Admin\Mutationen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Mutation;
use App\Utils\AntragHelper;

#[Layout('layouts.app')]
class Verarbeitung extends Component
{
    public Mutation $entry;

    protected $listeners = [
        'pep-updated'         => 'refreshEntry',
        'kis-updated'         => 'refreshEntry',
        'ad-updated'          => 'refreshEntry',
        'email-updated'        => 'refreshEntry',
        'telefonie-updated'   => 'refreshEntry',
        'auftraege-versendet' => 'refreshEntry',
        'info-updated'        => 'refreshEntry',
        'besitzer-updated'    => 'refreshEntry',
        'archiviert-updated'  => 'refreshEntry',
        'mutation-deleted'    => 'refreshEntry',
    ];

    public function mount(Mutation $mutation): void
    {
        $this->entry = $mutation;
    }

    public function refreshEntry(): void
    {
        $this->entry->refresh();
    }

    public function render()
    {
        $tasksConfig   = config('ums.mutation.taskDefinitions');
        $detailsConfig = config('ums.mutation.detailsSections');

        $this->entry->load([
            'anrede', 'titel', 'arbeitsort', 'unternehmenseinheit', 'abteilung', 'funktion',
            'antragsteller', 'bezugsperson', 'vorlageBenutzer',
            'adUser.anrede', 'adUser.titel', 'adUser.arbeitsort', 'adUser.unternehmenseinheit',
            'adUser.abteilung', 'adUser.funktion',
        ]);

        $status = AntragHelper::statusForVerarbeitung($this->entry, auth()->user());

        return view('livewire.pages.admin.mutationen.verarbeitung', [
            'entry'          => $this->entry,
            'detailsConfig'  => $detailsConfig,
            'tasksConfig'    => $tasksConfig,
            'canEdit'        => $status['canEdit'],
            'statusMessages' => $status['messages'],
        ])->layoutData([
            'pageTitle' => "Verarbeitung Mutation "
                . ($this->entry->nachname ?? '') . " "
                . ($this->entry->vorname ?? ''),
        ]);
    }
}
