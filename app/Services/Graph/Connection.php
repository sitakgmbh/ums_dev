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
    protected string $tenantId;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->tenantId     = Setting::getValue("azure_tenant_id", "");
        $this->clientId     = Setting::getValue("azure_client_id", "");
        $this->clientSecret = Setting::getValue("azure_client_secret", "");

        if (!$this->tenantId || !$this->clientId || !$this->clientSecret) 
		{
            Logger::db("graph", "error", "Azure Zugangsdaten fehlen", [
                "tenantId" => $this->tenantId,
                "clientId" => $this->clientId,
            ]);
			
            throw new \RuntimeException("Azure Zugangsdaten fehlen");
        }
    }

    public function getClient(array $scopes = ["https://graph.microsoft.com/.default"]): GraphServiceClient
    {
        $context = new ClientCredentialContext(
            $this->tenantId,
            $this->clientId,
            $this->clientSecret
        );

        return new GraphServiceClient($context, $scopes);
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
