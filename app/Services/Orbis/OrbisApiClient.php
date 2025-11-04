<?php
namespace App\Services\Orbis;

use Illuminate\Support\Facades\Log;
use App\Services\Orbis\Exceptions\OrbisRequestException;

class OrbisApiClient
{
    protected string $baseUrl;
    protected string $authHeader;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.orbis.base_url'), '/');
        $user = config('services.orbis.username');
        $pass = config('services.orbis.password');

        if (!$this->baseUrl || !$user || !$pass) {
            throw new \RuntimeException('Orbis-Konfiguration unvollstaendig.');
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

        if (in_array($method, ['POST', 'PUT'])) {
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

        if ($status >= 400) {
            Log::error("Orbis-Fehler {$status} bei {$method} {$url}");
            throw new OrbisRequestException("Orbis API Fehler ({$status})", $status);
        }

        $decoded = json_decode($response, true) ?? [];

        return $withHeaders ? ['body' => $decoded, 'headers' => $http_response_header] : $decoded;
    }
}
