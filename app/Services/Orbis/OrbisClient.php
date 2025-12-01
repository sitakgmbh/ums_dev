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
    // RAW RESPONSE LOGGING (immer, nicht nur bei Fehler)
    //
    Logger::debug("ORBIS RESPONSE STATUS: " . $response->status());

    $rawBody = $response->body();
    Logger::debug("ORBIS RESPONSE RAW:\n" . ($rawBody ?: "<leer>"));

    Logger::debug(
        "ORBIS RESPONSE HEADERS:\n" .
        json_encode($response->headers(), JSON_PRETTY_PRINT)
    );

    //
    // JSON-PARSE DEBUG (Fehlerquelle fuer Closure!)
    //
    $json = null;
    try {
        $json = $response->json(); // immer als array oder null
    } catch (\Throwable $e) {
        Logger::error("ORBIS JSON PARSE ERROR: " . $e->getMessage());
        Logger::error("PARSE RAW BODY:\n" . $rawBody);
        return null; // API nicht brauchbar -> Fehler propagieren
    }

    // Falls json() unerwartet kein array liefert (z.B. null/false/string)
    if (!is_array($json)) {
        Logger::error("ORBIS JSON ist kein Array (Typ: " . gettype($json) . ")");
        Logger::error("JSON CONTENT:\n" . json_encode($json));
    }

    //
    // Fehler ausdruecklich loggen
    //
    if ($response->failed()) {

        Logger::error("ORBIS FEHLER {$response->status()} bei {$method} {$fullUrl}");

        // Wenn Body Fehler enthaelt
        if (isset($json['errors']) || isset($json['Messages']) || isset($json['message'])) {
            Logger::error(
                "ORBIS FEHLERDETAILS:\n" .
                json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    //
    // Return mit Headern
    //
    if ($returnHeaders) {
        return [
            'body'    => $json,
            'headers' => $response->headers(),
        ];
    }

    return $json;
}

}
