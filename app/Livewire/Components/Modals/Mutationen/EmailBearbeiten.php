<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Utils\LdapHelper;
use App\Utils\UserHelper;
use App\Utils\Logging\Logger;
use Livewire\Attributes\Rule;

class EmailBearbeiten extends BaseModal
{
    public ?Mutation $entry = null;

    #[Rule('required|email')]
    public string $mail1 = "";

    #[Rule('nullable|email')]
    public ?string $mail2 = null;

    public string $infoText = "";
    public string $errorMessage = "";

    public array $aliases = [];
    public ?string $generatedMail = null;
    public array $reasons = [];

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Mutation::find($payload["entryId"]);
		
        if (! $this->entry) 
		{
            return false;
        }

        $username = $this->entry->adUser->username ?? null;
        $adUser   = $username ? LdapHelper::getAdUser($username) : null;

        $this->mail1 = $this->entry->adUser->email ?? "";
        $this->mail2 = null;

        if ($adUser) 
		{
            $proxies = $adUser->getAttribute("proxyAddresses", []);
			
			$this->aliases = collect($proxies)
				->filter(fn($v) => substr($v, 0, 5) === 'smtp:') // case-sensitiv
				->map(fn($v) => strtolower(substr($v, 5)))
				->values()
				->toArray();
        }

        if ($this->entry->vorname || $this->entry->nachname || $this->entry->mailendung) 
		{
            $domain = $this->entry->mailendung;
			
            if (! $domain && $this->mail1 && str_contains($this->mail1, "@")) 
			{
                $domain = substr(strrchr($this->mail1, "@"), 1);
            }

            $this->generatedMail = UserHelper::generateEmail(
                $this->entry->vorname ?? $this->entry->adUser->firstname,
                $this->entry->nachname ?? $this->entry->adUser->lastname,
                $domain ?? "",
                $username,
                $this->entry->id
            );

            if ($this->generatedMail) 
			{
                if ($this->mail1) 
				{
                    $this->mail2 = $this->mail1;
                }
				
                $this->mail1 = $this->generatedMail;

                if ($this->entry->vorname)   $this->reasons[] = "Vorname";
                if ($this->entry->nachname)  $this->reasons[] = "Nachname";
                if ($this->entry->mailendung)$this->reasons[] = "Mailendung";
            }
        }

        $this->infoText = "Passe die primäre E-Mail-Adresse und optional eine Alias-Adresse an. "
            . "Alias darf nicht identisch mit der primären Adresse sein.";

        $this->title      = "E-Mail-Adresse bearbeiten";
        $this->size       = "md";
        $this->position   = "centered";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }
			
	public function confirm(): void
	{
		Logger::debug("EmailBearbeiten::confirm gestartet");

		$this->validate();

		if ($this->mail2 && strcasecmp($this->mail1, $this->mail2) === 0) 
		{
			Logger::debug("Fehler: Primär- und Alias-Mail sind identisch");
			$this->addError("mail2", "Primäre E-Mail-Adresse und Alias dürfen nicht gleich sein.");
			return;
		}

		$username = $this->entry->adUser->username;
		Logger::debug("Benutzername aus entry: " . ($username ?: "NULL"));

		if (empty($username)) 
		{
			$this->errorMessage = "Kein Benutzername im Antrag hinterlegt.";
			Logger::error("Abbruch: Benutzername fehlt in entry ID {$this->entry?->id}");
			return;
		}

		$mail1 = strtolower($this->mail1);
		$mail2 = $this->mail2 ? strtolower($this->mail2) : null;

		Logger::debug("mail1={$mail1}, mail2=" . ($mail2 ?: "NULL"));

		try 
		{
			Logger::debug("Hole AD-User für {$username}");
			$adUser = LdapHelper::getAdUser($username);

			if (! $adUser) 
			{
				$this->errorMessage = "Benutzer {$username} im AD nicht gefunden.";
				Logger::error("AD-Benutzer {$username} nicht gefunden");
				return;
			}

			Logger::debug("Prüfe E-Mail-Unique für {$mail1} und Alias {$mail2}");

			if (LdapHelper::emailExists($mail1, $username)) 
			{
				Logger::debug("Fehler: Primäre Mail {$mail1} existiert bereits");
				$this->addError("mail1", "Die primäre E-Mail-Adresse existiert bereits.");
				return;
			}
			
			if ($mail2 && LdapHelper::emailExists($mail2, $username)) 
			{
				Logger::debug("Fehler: Alias-Mail {$mail2} existiert bereits");
				$this->addError("mail2", "Die Alias-E-Mail-Adresse existiert bereits.");
				return;
			}

			$currentProxies = $adUser->getAttribute("proxyAddresses", []) ?? [];
			Logger::debug("Aktuelle AD-Proxies: " . json_encode($currentProxies));

			$normalized = collect($currentProxies)
				->map(fn($v) => strtolower($v))
				->toArray();

			$proxies = [];
			$proxies[] = "SMTP:{$mail1}";
			
			if ($mail2) 
			{
				$proxies[] = "smtp:{$mail2}";
			}

			foreach ($normalized as $alias) 
			{
				$clean = preg_replace("/^smtp:/i", "", $alias);
				
				if ($clean !== $mail1 && $clean !== $mail2) 
				{
					$proxies[] = "smtp:{$clean}";
				}
			}

			$proxies = array_values(array_unique($proxies));
			Logger::debug("Neue Proxy-Liste: " . json_encode($proxies));

			Logger::debug("Schreibe proxyAddresses für {$username}");
			LdapHelper::setAdAttribute($username, "proxyAddresses", $proxies);

			Logger::debug("Schreibe mail-Attribut für {$username}");
			LdapHelper::setAdAttribute($username, "mail", $mail1);

			$this->entry->update([
				"email"       => $mail1,
				"status_mail" => 2,
			]);

			Logger::debug("E-Mail-Adresse angepasst: {$mail1}");

			$this->dispatch("email-updated");
			$this->closeModal();
		} 
		catch (\Exception $e) 
		{
			$this->errorMessage = "Fehler: " . $e->getMessage();
			Logger::error("EmailBearbeiten::confirm Exception: " . $e->getMessage());
		}
	}

    public function render()
    {
        return view("livewire.components.modals.mutationen.email-bearbeiten");
    }
}
