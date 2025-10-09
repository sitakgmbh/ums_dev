<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;

class EroeffnungVorhanden extends BaseModal
{
    public ?array $eroeffnung = null;
    public ?array $antragsteller = null;

    protected function openWith(array $payload): bool
    {
        if (empty($payload["eroeffnung"]["id"])) 
		{
            return false;
        }

        $eroeffnung = Eroeffnung::with("antragsteller")->find($payload["eroeffnung"]["id"]);
		
        if (!$eroeffnung) 
		{
            return false;
        }

        $this->eroeffnung = [
            "id"       => $eroeffnung->id,
            "vorname"  => $eroeffnung->vorname,
            "nachname" => $eroeffnung->nachname,
            "erstellt" => $eroeffnung->created_at?->format("d.m.Y H:i"),
        ];

        if ($eroeffnung->antragsteller) 
		{
            $this->antragsteller = [
                "vorname"  => $eroeffnung->antragsteller->firstname,
                "nachname" => $eroeffnung->antragsteller->lastname,
                "email"    => $eroeffnung->antragsteller->email,
                "telefon"  => $eroeffnung->antragsteller->office_phone
                            ?? $eroeffnung->antragsteller->home_phone
                            ?? "-",
            ];
        }

        $this->title      = "Eröffnung bereits vorhanden";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->position   = "centered";
        $this->scrollable = false;
        $this->headerBg   = "bg-warning";
        $this->headerText = "text-white";

        return true;
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.eroeffnung-vorhanden");
    }
}
