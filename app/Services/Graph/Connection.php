<?php

namespace App\Services\Graph;

use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;
use Microsoft\Kiota\Abstractions\ApiException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Throwable;
use App\Models\Setting;
use App\Utils\Logging\Logger;

class Connection
{
    protected ?string $tenantId = null;
    protected ?string $clientId = null;
    protected ?string $clientSecret = null;

    public function __construct() {}

    protected function loadCredentials(): void
    {
        if ($this->tenantId !== null) return;

        $this->tenantId     = env("AZURE_TENANT_ID") ?? null;
        $this->clientId     = env("AZURE_CLIENT_ID") ?? null;
        $this->clientSecret = env("AZURE_CLIENT_SECRET") ?? null;
    }

    public function getClient(): GraphServiceClient
    {
        $this->loadCredentials();

        if (!$this->tenantId || !$this->clientId || !$this->clientSecret) 
		{
            throw new \RuntimeException("Azure Zugangsdaten fehlen");
        }

        $context = new ClientCredentialContext($this->tenantId, $this->clientId, $this->clientSecret);
        return new GraphServiceClient($context, ["https://graph.microsoft.com/.default"]);
    }
	
	public function call(callable $fn, string $contextLabel = "Graph Call")
	{
		try 
		{
			return $fn();
		} 
		catch (ApiException $e) 
		{
			Logger::db("graph", "error", "Graph API Fehler: {$contextLabel}", [
				"code"       => $e->getCode(),
				"message"    => $e->getError()?->getMessage(),
				"stacktrace" => explode("\n", $e->getTraceAsString()),
			]);
			
			throw $e;

		} 
		catch (IdentityProviderException $e) 
		{
			$body    = $e->getResponseBody();
			$decoded = is_array($body) ? $body : json_decode($body, true);

			Logger::db("graph", "error", "Fehler bei der Authentifizierung: {$contextLabel}", [
				"code"          => $e->getCode(),
				"message"       => $e->getMessage(),
				"reason"        => $decoded["error_description"] ?? null,
				"error_code"    => $decoded["error_codes"][0] ?? null,
				"trace_id"      => $decoded["trace_id"] ?? null,
				"correlation_id"=> $decoded["correlation_id"] ?? null,
				"timestamp"     => $decoded["timestamp"] ?? null,
				"error_uri"     => $decoded["error_uri"] ?? null,
				"raw"           => $decoded,
			]);
			throw $e;

		} 
		catch (Throwable $e) 
		{
			Logger::db("graph", "error", "Unbekannter Fehler: {$contextLabel}", [
				"code"       => $e->getCode(),
				"message"    => $e->getMessage(),
				"stacktrace" => explode("\n", $e->getTraceAsString()),
			]);
			
			throw $e;
		}
	}
}
