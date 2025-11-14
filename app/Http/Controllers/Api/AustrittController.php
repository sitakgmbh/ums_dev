<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdUser;
use App\Models\Austritt;
use App\Utils\Logging\Logger;

class AustrittController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/austritte",
     *     summary="Austritt erstellen",
     *     tags={"Austritte"},
     *     security={{"basicAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sid","vertragsende"},
     *             @OA\Property(property="sid", type="string", example="S-1-5-21-2398345284-2631563733-2041457708-1318"),
     *             @OA\Property(property="vertragsende", type="string", format="date", example="yyyy-mm-dd"),
     *             @OA\Property(property="status_pep", type="boolean", example=true),
     *             @OA\Property(property="status_kis", type="boolean", example=false),
     *             @OA\Property(property="status_streamline", type="boolean", example=true),
     *             @OA\Property(property="status_tel", type="boolean", example=false),
     *             @OA\Property(property="status_alarmierung", type="boolean", example=false),
     *             @OA\Property(property="status_logimen", type="boolean", example=false),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Austritt erstellt",
     *         @OA\JsonContent(ref="#/components/schemas/Austritt")
     *     ),
     *     @OA\Response(response=404, description="Kein AD-Benutzer mit der angegebenen SID gefunden."),
     *     @OA\Response(response=422, description="Validierungsfehler")
     * )
     */
    public function store(Request $request)
    {
        // Validierung
        $validated = $request->validate([
            "sid"            => "required|string",
            "vertragsende"   => "required|date_format:Y-m-d",
            "ticket_nr"      => "nullable|string|max:255",
            "status_pep"        => "nullable|boolean",
            "status_kis"        => "nullable|boolean",
            "status_streamline" => "nullable|boolean",
            "status_tel"        => "nullable|boolean",
            "status_alarmierung"=> "nullable|boolean",
            "status_logimen"    => "nullable|boolean",
        ]);

		Logger::debug("API-Request Austritt", [
			"request"   => $request->all(),
			"validated" => $validated,
		]);
        // AD-User suchen
        $adUser = AdUser::where("sid", $validated["sid"])->first();
		
        if (!$adUser) {
            return response()->json([
                "message" => "Kein AD-Benutzer mit der angegebenen SID gefunden."
            ], 404);
        }

        // Austritt erstellen
        $austritt = Austritt::create([
            "ad_user_id"       => $adUser->id,
            "vertragsende"     => $validated["vertragsende"],
            "ticket_nr"        => $validated["ticket_nr"] ?? null,
            "status_pep"        => $validated["status_pep"] ?? 1,
            "status_kis"        => $validated["status_kis"] ?? 1,
            "status_streamline" => $validated["status_streamline"] ?? 1,
            "status_tel"        => $validated["status_tel"] ?? 1,
            "status_alarmierung"=> $validated["status_alarmierung"] ?? 1,
            "status_logimen"    => $validated["status_logimen"] ?? 1,
            "archiviert"        => 0,
        ]);

        return response()->json($austritt, 201);
    }
}
