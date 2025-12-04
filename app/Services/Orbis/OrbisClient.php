<?php

namespace App\Services\Orbis;

use Illuminate\Support\Facades\Http;
use App\Utils\Logging\Logger;

class OrbisClient
{
    private string $baseUrl;
    private string $authHeader;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ums.orbis.base_url'), '/');
        $user = config('ums.orbis.username');
        $pass = config('ums.orbis.password');

        if (!$this->baseUrl || !$user || !$pass) 
		{
            abort(response()->json([
                'status' => 'error',
                'message' => 'Orbis Konfiguration unvollständig.'
            ], 500));
        }

        $this->authHeader = 'Basic ' . base64_encode("{$user}:{$pass}");
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getAuthHeader(): string
    {
        return $this->authHeader;
    }

	public function send(string $url, string $method = 'GET', array $body = [], bool $returnHeaders = false): mixed
	{
		$fullUrl = $url;

		Logger::debug("ORBIS REQUEST: {$method} {$fullUrl}");

		if (!empty($body)) 
		{
			Logger::debug(
				"ORBIS REQUEST BODY:\n" .
				json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
			);
		}

		$response = Http::withHeaders([
				'Authorization' => $this->authHeader,
				'Accept'        => 'application/json',
			])
			->withOptions(['verify' => false])
			->send($method, $fullUrl, ['json' => $body]);

		$rawBody = $response->body();
		Logger::debug("ORBIS RESPONSE (Status {$response->status()}): {$rawBody})");

		// Logger::debug("ORBIS RESPONSE HEADERS:\n" . json_encode($response->headers(), JSON_PRETTY_PRINT));

		$json = null;
		
		try 
		{
			$json = $response->json();
		} 
		catch (\Throwable $e) 
		{
			Logger::error("ORBIS JSON PARSE ERROR: " . $e->getMessage());
			Logger::error("PARSE RAW BODY:\n" . $rawBody);
			return null;
		}

		if (!is_array($json)) 
		{
			Logger::error("ORBIS JSON ist kein Array (Typ: " . gettype($json) . ")");
			Logger::error("JSON CONTENT:\n" . json_encode($json));
		}

		if ($response->failed()) 
		{

			Logger::error("ORBIS FEHLER {$response->status()} bei {$method} {$fullUrl}");

			if (isset($json['errors']) || isset($json['Messages']) || isset($json['message'])) {
				Logger::error(
					"ORBIS FEHLERDETAILS:\n" .
					json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
				);
			}
		}

		if ($returnHeaders) 
		{
			return [
				'body'    => $json,
				'headers' => $response->headers(),
			];
		}

		return $json;
	}
}
