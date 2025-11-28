<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Utils\LdapHelper;

class Telefonie extends BaseModal
{
    public ?Eroeffnung $entry = null;

    public ?string $tel_auswahl = null;
    public ?string $tel_nr = null;
    public ?string $cloudExt1 = null;
    public ?string $cloudExt2 = null;
	public ?string $cloudExt3 = null;

    public bool $tel_tischtel = false;
    public bool $tel_mobiltel = false;
    public bool $tel_ucstd = false;
    public bool $tel_alarmierung = false;

    public bool $G_APP_UCC = false;
    public bool $G_APP_Novaalert = false;

    public array $optionsCloudExt1 = [];
    public array $optionsCloudExt2 = [];
    public array $optionsCloudExt3 = [];

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Eroeffnung::find($payload["entryId"]);
		
        if (! $this->entry) 
		{
            return false;
        }

        $this->tel_auswahl   = $this->entry->tel_auswahl;
        $this->tel_nr        = $this->entry->tel_nr ?? '+41 58 225 ';
        $this->cloudExt1     = $this->entry->cloudExt1 ?? null;
        $this->cloudExt2     = $this->entry->cloudExt2 ?? null;
		$this->cloudExt3     = $this->entry->cloudExt3 ?? null;

        $this->tel_tischtel  = (bool)$this->entry->tel_tischtel;
        $this->tel_mobiltel  = (bool)$this->entry->tel_mobiltel;
        $this->tel_ucstd     = (bool)$this->entry->tel_ucstd;
        $this->tel_alarmierung = (bool)$this->entry->tel_alarmierung;

        $this->G_APP_UCC      = (bool)($this->entry->G_APP_UCC ?? false);
        $this->G_APP_Novaalert = (bool)($this->entry->G_APP_Novaalert ?? false);

		if (in_array($this->tel_auswahl, ["neu", "uebernehmen"], true)) 
		{
			$this->G_APP_UCC = true;
		}

        $this->optionsCloudExt1 = config("ums.eroeffnung.telefonie.cloudExt1");
        $this->optionsCloudExt2 = config("ums.eroeffnung.telefonie.cloudExt2");
        $this->optionsCloudExt3 = config("ums.eroeffnung.telefonie.cloudExt3");

        $this->title      = "Telefonie konfigurieren";
        $this->size       = "md";
        $this->position   = "centered";
		$this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

	public function confirm(): void
	{
		if ($this->tel_auswahl === "neu" && empty($this->tel_nr)) 
		{
			$this->addError("tel_nr", "Telefonnummer ist erforderlich.");
			return;
		}

		$username = $this->entry->benutzername ?? null;

		if (!$username) 
		{
			$this->addError("general", "Benutzername nicht vorhanden.");
			return;
		}

		try 
		{
			if ($this->tel_nr) 
			{
				LdapHelper::setAdAttribute($username, "telephoneNumber", $this->tel_nr);
			}
			
			if ($this->cloudExt1) 
			{
				LdapHelper::setAdAttribute($username, "msDS-cloudExtensionAttribute1", $this->cloudExt1);
			}
			
			if ($this->cloudExt2) 
			{
				LdapHelper::setAdAttribute($username, "msDS-cloudExtensionAttribute2", $this->cloudExt2);
			}
			
			if ($this->cloudExt3) 
			{
				LdapHelper::setAdAttribute($username, "msDS-cloudExtensionAttribute3", $this->cloudExt3);
			}

			LdapHelper::updateGroupMembership($username, [
				"G_APP_UCC"       => $this->G_APP_UCC,
				"G_APP_Novaalert" => $this->G_APP_Novaalert,
			]);

			$this->entry->update([
				"tel_nr"     => $this->tel_nr,
				"status_tel" => 2,
			]);

			$this->dispatch("telefonie-updated");
			$this->closeModal();
		} 
		catch (\RuntimeException $e) 
		{
			$this->addError("general", $e->getMessage());
		} 
		catch (\Exception $e) 
		{
			$this->addError("general", "Allgemeiner Fehler: " . $e->getMessage());
		}
	}

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.telefonie");
    }
}
