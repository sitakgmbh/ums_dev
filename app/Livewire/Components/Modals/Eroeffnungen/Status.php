<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;

class Status extends BaseModal
{
    public ?Eroeffnung $eroeffnung = null;
    public array $aufgaben = [];

    public int $total = 0;
    public int $done = 0;
    public int $percentage = 0;

    protected function openWith(array $payload): bool
    {
        $id = $payload["id"] ?? null;

        if (!$id || !($this->eroeffnung = Eroeffnung::find($id))) 
		{
            $this->dispatch("open-modal", modal: "alert", payload: [
                "message"  => "Die Eröffnung konnte nicht gefunden werden.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return false;
        }

        $statusMap = [
            "status_ad"      => "PC Login",
            "status_tel"     => "Telefonie",
            "status_pep"     => "PEP",
            "status_kis"     => "KIS",
            "status_auftrag" => "Versand Aufträge",
            "status_info"    => "Versand Info-Mail",
        ];

        $this->aufgaben = [];

        foreach ($statusMap as $field => $label) 
		{
            $value = (int) $this->eroeffnung->$field;

			// nicht benötigt, überspringen
            if ($value === 0) 
			{
                continue;
            }

            $this->aufgaben[] = [
                "label" => $label,
                "done"  => $value === 2, // erledigt
            ];
        }

        $this->total = count($this->aufgaben);
        $this->done = collect($this->aufgaben)->where("done", true)->count();
        $this->percentage = $this->total > 0 ? round(($this->done / $this->total) * 100) : 0;

        $this->title      = "Status Eröffnung";
        $this->size       = "lg";
        $this->backdrop   = false;
        $this->position   = "centered";
        $this->scrollable = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.status");
    }
}
