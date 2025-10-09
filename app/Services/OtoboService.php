<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use App\Models\Setting;
use App\Utils\Logging\Logger;

class OtoboService
{
	private ?string $url = null;
	private ?string $username = null;
	private ?string $password = null;
	private array $config = [];
	private bool $isConfigured = false;

	public function __construct()
	{
		$this->url = Setting::getValue("otobo_url") ?? null;
		$this->username = Setting::getValue("otobo_username") ?? null;
		$this->password = Setting::getValue("otobo_password") ?? null;
		$this->config = config("ums.otobo") ?? [];

		$missing = [];

		if (empty($this->url)) $missing[] = "otobo_url";
		if (empty($this->username)) $missing[] = "otobo_username";
		if (empty($this->password)) $missing[] = "otobo_password";
		if (empty($this->config) || !is_array($this->config)) $missing[] = "config(ums.otobo)";

		if (!empty($missing)) 
		{
			Logger::db("otobo", "error", "Konfiguration unvollständig", [
				"fehlende_werte" => $missing,
				"url" => $this->url ?: "(leer)",
				"username" => $this->username ?: "(leer)",
				"password" => $this->password ? "(gesetzt)" : "(leer)",
				"config_keys" => array_keys($this->config ?? []),
			]);

			$this->isConfigured = false;
			return;
		}

		$this->isConfigured = true;
	}


	// Ticket erstellen
	public function createTicket(Model $model): bool|string
	{
		try 
		{
			$type = get_class($model);
			$config = $this->config[$type] ?? null;

			if (!$config) 
			{
				Logger::debug("OtoboService: Kein Mapping für Typ {$type}", [
					"model" => $type,
					"id" => $model->id ?? null,
				]);
				
				return false;
			}

			$body = "Details:\n\n";
			
			foreach ($config["field_mapping"] as $key => $label) 
			{
				if (!empty($model->$key)) 
				{
					$body .= "{$label}: {$model->$key}\n";
				}
			}

			$data = [
				"UserLogin" => $this->username,
				"Password" => $this->password,
				"Ticket" => [
					"Title" => "{$config["title_prefix"]} {$model->nachname} {$model->vorname}",
					"QueueID" => $config["queue_id"],
					"CustomerUser" => optional($model->antragsteller)->email ?? "-",
					"StateID" => $config["state_id"],
					"PriorityID" => $config["priority_id"],
					"TypeID" => $config["ticket_type_id"],
					"ServiceID" => $config["service_id"],
				],
				"Article" => [
					"CommunicationChannel" => "Internal",
					"From" => config("mail.from.address"),
					"Subject" => "Nachricht von UMS",
					"Body" => $body,
					"ContentType" => "text/plain; charset=utf-8",
				],
			];

			$response = $this->sendRequest("/TicketCreate", $data);

			if (!$response || !isset($response["TicketNumber"])) 
			{
				Logger::db("otobo", "error", "Ticket konnte nicht erstellt werden", [
					"model" => $type,
					"id" => $model->id,
					"response" => $response,
				]);
				
				return false;
			}

			$model->update(["ticket_nr" => $response["TicketNumber"]]);

			Logger::db("otobo", "info", "Ticket {$response["TicketNumber"]} erstellt", [
				"model" => $type,
				"id" => $model->id,
				"ticket_nr" => $response["TicketNumber"],
			]);

			return $response["TicketNumber"];
		} 
		catch (\Throwable $e) 
		{
			Logger::db("otobo", "error", "Fehler beim Erstellen des Tickets", [
				"model" => get_class($model),
				"id" => $model->id ?? null,
				"error" => $e->getMessage(),
			]);
			
			return false;
		}
	}

	// Ticket aktualisieren
	public function updateTicket(Model $model, string $message, bool $close = false): bool
	{
		try 
		{
			$type = get_class($model);
			$config = $this->config[$type] ?? null;

			if (!$config) 
			{
				Logger::error("OtoboService: Kein Mapping für Typ {$type}", [
					"model" => $type,
					"id" => $model->id ?? null,
				]);
				
				return false;
			}

			if (empty($model->ticket_nr)) 
			{
				Logger::error("OtoboService: Ticketnummer fehlt", [
					"model" => $type,
					"id" => $model->id ?? null,
				]);
				
				return false;
			}

			$data = [
				"UserLogin" => $this->username,
				"Password" => $this->password,
				"TicketNumber" => $model->ticket_nr,
				"Ticket" => [
					"Title" => "{$config["title_prefix"]} {$model->nachname} {$model->vorname}",
					"QueueID" => $config["queue_id"],
				],
				"Article" => [
					"CommunicationChannel" => "Internal",
					"From" => config("mail.from.address"),
					"Subject" => "Nachricht von UMS",
					"Body" => $message,
					"ContentType" => "text/plain; charset=utf-8",
				],
			];

			if ($close) 
			{
				$data["Ticket"]["StateID"] = 2; // geschlossen
				$data["Ticket"]["Owner"] = auth()->user()->adUser->email; // für Statistik
			}

			$response = $this->sendRequest("/TicketUpdate", $data);

			if ($response) 
			{
				Logger::debug("Ticket aktualisiert", [
					"model" => $type,
					"id" => $model->id,
					"ticket_nr" => $model->ticket_nr,
				]);
				
				return true;
			}

			Logger::db("otobo", "error", "Ticket konnte nicht aktualisiert werden", [
				"model" => $type,
				"id" => $model->id,
				"ticket_nr" => $model->ticket_nr,
			]);
			
			return false;
		} 
		catch (\Throwable $e) 
		{
			Logger::db("otobo", "error", "Fehler beim Aktualisieren des Tickets", [
				"model" => get_class($model),
				"id" => $model->id ?? null,
				"error" => $e->getMessage(),
			]);
			
			return false;
		}
	}

	/**
	 * Interner Request an OTOBO
	 */
	private function sendRequest(string $endpoint, array $data): ?array
	{
		try 
		{
			if (!$this->url) 
			{
				Logger::db("otobo", "error", "Keine OTOBO URL konfiguriert", [
					"endpoint" => $endpoint,
				]);
				
				return null;
			}

			$response = Http::withBasicAuth($this->username, $this->password)
				->withHeaders(["Content-Type" => "application/json"])
				->withOptions(["verify" => false])
				->post(rtrim($this->url, "/") . $endpoint, $data);

			if ($response->successful()) 
			{
				return $response->json();
			}

			Logger::db("otobo", "error", "Unerwartete Antwort von OTOBO", [
				"status" => $response->status(),
				"body" => $response->body(),
				"endpoint" => $endpoint,
			]);
			
			return null;
		} 
		catch (\Throwable $e) 
		{
			Logger::db("otobo", "error", "Fehler beim Senden an OTOBO", [
				"endpoint" => $endpoint,
				"error" => $e->getMessage(),
			]);
			return null;
		}
	}
}
