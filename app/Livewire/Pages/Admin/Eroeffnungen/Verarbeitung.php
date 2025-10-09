<?php

namespace App\Livewire\Pages\Admin\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Eroeffnung;
use App\Utils\AntragHelper;

#[Layout('layouts.app')]
class Verarbeitung extends Component
{
    public Eroeffnung $entry;

    protected $listeners = [
        'pep-updated'         => 'refreshEntry',
        'kis-updated'         => 'refreshEntry',
        'ad-updated'          => 'refreshEntry',
        'telefonie-updated'   => 'refreshEntry',
        'auftraege-versendet' => 'refreshEntry',
        'info-updated'        => 'refreshEntry',
        'besitzer-updated'    => 'refreshEntry',
        'archiviert-updated'  => 'refreshEntry',
        'eroeffnung-deleted'  => 'refreshEntry',
    ];

    public function mount(Eroeffnung $eroeffnung): void
    {
        $this->entry = $eroeffnung;
    }

    public function refreshEntry(): void
    {
        $this->entry->refresh();
    }

    public function render()
    {
        $tasksConfig   = config('ums.eroeffnung.taskDefinitions');
        $detailsConfig = config('ums.eroeffnung.detailsSections');		

        $this->entry->load([
            'anrede', 'titel', 'arbeitsort', 'unternehmenseinheit',
            'abteilung', 'funktion', 'antragsteller', 'bezugsperson', 'vorlageBenutzer',
        ]);

        $status = AntragHelper::statusForVerarbeitung($this->entry, auth()->user());

        return view('livewire.pages.admin.eroeffnungen.verarbeitung', [
            'entry'          => $this->entry,
            'detailsConfig'  => $detailsConfig,
            'tasksConfig'    => $tasksConfig,
            'canEdit'        => $status['canEdit'],
            'statusMessages' => $status['messages'],
        ])->layoutData([
            'pageTitle' => "Verarbeitung Eröffnung {$this->entry->nachname} {$this->entry->vorname}",
        ]);
    }
}
