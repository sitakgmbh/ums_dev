<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;

class MutationVorhanden extends BaseModal
{
    public ?array $mutation = null;
    public ?array $antragsteller = null;

    protected function openWith(array $payload): bool
    {
        if (empty($payload["mutation"]["id"])) {
            return false;
        }

        $mutation = Mutation::with("antragsteller")->find($payload["mutation"]["id"]);
        if (!$mutation) {
            return false;
        }

        $this->mutation = [
            "id"       => $mutation->id,
            "erstellt" => $mutation->created_at?->format("d.m.Y H:i"),
        ];

        if ($mutation->antragsteller) {
            $this->antragsteller = [
                "vorname"  => $mutation->antragsteller->firstname,
                "nachname" => $mutation->antragsteller->lastname,
                "email"    => $mutation->antragsteller->email,
                "telefon"  => $mutation->antragsteller->office_phone
                            ?? $mutation->antragsteller->home_phone
                            ?? "-",
            ];
        }

        $this->title      = "Mutation bereits vorhanden";
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
        return view("livewire.components.modals.mutationen.mutation-vorhanden");
    }
}
