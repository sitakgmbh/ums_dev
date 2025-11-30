<?php

namespace App\Services\Orbis;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrbisClient
{
    private string $baseUrl;
    private string $authHeader;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ums.orbis.base_url'), '/');
        $user = config('ums.orbis.username');
        $pass = config('ums.orbis.password');

        if (!$this->baseUrl || !$user || !$pass) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'Orbis Konfiguration unvollstaendig.'
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

        $response = Http::withHeaders([
            'Authorization' => $this->authHeader,
            'Accept' => 'application/json',
        ])
        ->withOptions(['verify' => false])
        ->send($method, $fullUrl, ['json' => $body]);

        Log::channel('orbis')->debug("{$method} {$url}");

        if ($response->failed()) {
            Log::channel('orbis')->error("Fehler {$response->status()} bei {$method} {$url}");

            if (!empty($body)) {
                Log::channel('orbis')->debug("Request-Body:\n" . json_encode($body, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            } else {
                Log::channel('orbis')->debug("Request-Body: <leer>");
            }

            Log::channel('orbis')->debug("Response:\n" . $response->body());
            Log::channel('orbis')->debug("Response-Header:\n" . json_encode($response->headers()));
        }

        if ($returnHeaders) {
            return [
                'body' => $response->json(),
                'headers' => $response->headers(),
            ];
        }

        return $response->json();
    }
}
