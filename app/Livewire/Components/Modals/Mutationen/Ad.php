<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Utils\LdapHelper;
use App\Utils\Logging\Logger;

class Ad extends BaseModal
{
    public ?Mutation $entry = null;

    public string $mode = ""; // append | overwrite
    public array $groups = [];
    public string $infoText = "";
	public string $errorMessage = '';

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) {
            return false;
        }

        $this->entry = Mutation::find($payload["entryId"]);

        if (! $this->entry) {
            return false;
        }

        $this->groups = $this->entry->ad_gruppen ?? [];
        $this->mode   = $this->entry->abteilung2_id ? "append" : "overwrite";

        $this->infoText = "Wähle aus, ob du bestehende Berechtigungen ergänzen oder überschreiben möchtest.";

        $this->title      = "Berechtigungen anpassen";
        $this->size       = "md";
        $this->position   = "centered";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

	public function confirm(): void
	{
		Logger::debug("confirm() gestartet für Benutzer {$this->entry->adUser->display_name}");

		if (! $this->entry || empty($this->entry->vorlage_benutzer_id)) {
			$this->errorMessage = "Antrag fehlt oder Vorlage-Benutzer nicht hinterlegt.";
			return;
		}

		$username = $this->entry->adUser->username;
		$groups   = $this->groups ?? [];

		if (empty($groups)) {
			$this->errorMessage = "Keine Gruppen im Antrag vorhanden.";
			return;
		}

		try {
			if ($this->mode === "overwrite") {
				Logger::debug("Überschreibe Gruppen für {$username}");
				$current = LdapHelper::getAdGroups($username);
				foreach ($current as $group) {
					Logger::debug("Entferne Gruppe {$group} von {$username}");
					LdapHelper::updateGroupMembership($username, [$group => false], true);
				}
			}

			foreach ($groups as $group) {
				Logger::debug("Füge Gruppe {$group} hinzu für {$username}");
				LdapHelper::updateGroupMembership($username, [$group => true], true);
			}

			$this->entry->update([
				"status_ad" => 2,
			]);

			Logger::debug("Berechtigungen im Modus {$this->mode} gesetzt");

			$this->dispatch("ad-updated");
			$this->closeModal();
		} catch (\Exception $e) {
			Logger::error("Fehler in confirm(): ".$e->getMessage());
			$this->errorMessage = "Fehler bei Berechtigungen: " . $e->getMessage();
		}
	}


    public function render()
    {
        return view("livewire.components.modals.mutationen.ad");
    }
}
