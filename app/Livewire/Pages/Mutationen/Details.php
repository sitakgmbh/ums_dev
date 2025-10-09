<?php

namespace App\Livewire\Pages\Mutationen;

use App\Models\Eroeffnung;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Details extends Component
{
    public Eroeffnung $eroeffnung;

    public function mount(Eroeffnung $eroeffnung): void
    {
        $this->eroeffnung = $eroeffnung;
    }

    public function render()
    {
		$aufgaben = [
			["label" => "Active Directory", "done" => (bool) $this->eroeffnung->status_ad],
			["label" => "Telefon", "done" => (bool) $this->eroeffnung->status_tel],
			["label" => "PEP", "done" => (bool) $this->eroeffnung->status_pep],
			["label" => "KIS", "done" => (bool) $this->eroeffnung->status_kis],
			["label" => "SAP", "done" => (bool) $this->eroeffnung->status_sap],
			["label" => "Auftrag erfasst", "done" => (bool) $this->eroeffnung->status_auftrag],
			["label" => "Information versendet", "done" => (bool) $this->eroeffnung->status_info],
		];

        return view("livewire.pages.eroeffnungen.details", [
            "eroeffnung" => $this->eroeffnung,
            "aufgaben"   => $aufgaben,
        ]);
    }
}
