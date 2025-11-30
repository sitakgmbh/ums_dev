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

        //
        // REQUEST LOG
        //
        Logger::debug("ORBIS REQUEST: {$method} {$fullUrl}");

        if (!empty($body)) {
            Logger::debug(
                "ORBIS REQUEST BODY:\n" .
                json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        //
        // HTTP Request
        //
        $response = Http::withHeaders([
                'Authorization' => $this->authHeader,
                'Accept'        => 'application/json',
            ])
            ->withOptions(['verify' => false])
            ->send($method, $fullUrl, ['json' => $body]);

        //
        // ERROR LOGGING
        //
        if ($response->failed()) {

            Logger::error("ORBIS FEHLER {$response->status()} bei {$method} {$fullUrl}");

            // Body
            if (!empty($body)) {
                Logger::debug(
                    "ORBIS REQUEST-BODY:\n" .
                    json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            } else {
                Logger::debug("ORBIS REQUEST-BODY: <leer>");
            }

            // Response body
            $raw = $response->body();
            if ($raw && trim($raw) !== '') {
                Logger::debug("ORBIS RESPONSE:\n" . $raw);
            } else {
                Logger::debug("ORBIS RESPONSE: <leer oder null>");
            }

            // Headers
            Logger::debug(
                "ORBIS RESPONSE-HEADER:\n" .
                json_encode($response->headers(), JSON_PRETTY_PRINT)
            );
        }

        //
        // Return mit Headern
        //
        if ($returnHeaders) {
            return [
                'body'    => $response->json(),
                'headers' => $response->headers(),
            ];
        }

        return $response->json();
    }
}
