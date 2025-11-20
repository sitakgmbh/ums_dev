<?php
namespace App\Services\Orbis;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class OrbisApiClient
{
    protected string $baseUrl;
    protected string $authHeader;
    
    public function __construct()
    {
        $this->baseUrl = env('ORBIS_API_BASE_URL');
        $user = env('ORBIS_USERNAME');
        $pass = env('ORBIS_PASSWORD');
        
        if (!$this->baseUrl || !$user || !$pass) 
		{
            throw new InvalidArgumentException('Orbis-Konfiguration unvollständig. Bitte prüfe die Umgebungsvariablen.');
        }
        
        $this->authHeader = 'Basic ' . base64_encode("{$user}:{$pass}");
    }
    
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
    
	public function send(string $endpoint, string $method = 'GET', array $body = [], bool $withHeaders = false): array
	{
		$url = "{$this->baseUrl}/{$endpoint}";
		$headers = [
			"Authorization: {$this->authHeader}",
			"Accept: application/json",
		];
		
		if (in_array($method, ['POST', 'PUT'])) 
		{
			$headers[] = "Content-Type: application/json";
		}
		
		$context = stream_context_create([
			'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
			'http' => [
				'method' => $method,
				'header' => $headers,
				'content' => !empty($body) ? json_encode($body) : null,
			],
		]);
		
		$response = @file_get_contents($url, false, $context);
		$statusLine = $http_response_header[0] ?? '';
		preg_match('/HTTP\/\d+\.\d+ (\d+)/', $statusLine, $matches);
		$status = (int)($matches[1] ?? 0);

		// Kein Fehler bei 404
		if ($status === 404 && str_contains($endpoint, 'organizationalunitgroups')) 
		{
			Log::info("OE-Gruppe nicht gefunden: {$url}");
			return $withHeaders ? ['body' => null, 'headers' => $http_response_header] : [];
		}

		if ($status >= 400) 
		{
			Log::error("Orbis-Fehler {$status} bei {$method} {$url}");
			throw new RuntimeException("Orbis API Fehler ({$status})", $status);
		}
		
		$decoded = json_decode($response, true) ?? [];
		
		return $withHeaders ? ['body' => $decoded, 'headers' => $http_response_header] : $decoded;
	}
}