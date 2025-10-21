<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdUser;
use App\Models\Mutation;

class MutationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/mutationen",
     *     summary="Mutation erstellen",
     *     tags={"Mutationen"},
     *     security={{"basicAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"sid", "vertragsbeginn"},
     *             @OA\Property(property="sid", type="string", example="S-1-5-21-2398345284-2631563733-2041457708-1318"),
     *             @OA\Property(property="vertragsbeginn", type="string", format="date", example="2025-03-31"),
     *             @OA\Property(property="vorname", type="string", example="Max"),
     *             @OA\Property(property="nachname", type="string", example="Mustermann"),
     *             @OA\Property(property="mailendung", type="string", example="@domain.tld"),
     *             @OA\Property(property="status_mail", type="boolean", example=false),
     *             @OA\Property(property="status_kis", type="boolean", example=true),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mutation erstellt",
     *         @OA\JsonContent(ref="#/components/schemas/Mutation")
     *     ),
     *     @OA\Response(response=404, description="Kein AD-Benutzer mit der angegebenen SID gefunden."),
     *     @OA\Response(response=422, description="Validierungsfehler")
     * )
     */
    public function store(Request $request)
    {
        // Validierung
        $validated = $request->validate([
            "sid"             => "required|string",
            "vertragsbeginn"  => "required|date_format:Y-m-d",
            "vorname"         => "nullable|string|max:255",
            "nachname"        => "nullable|string|max:255",
            "mailendung"      => "nullable|string|max:255",
            "status_mail"     => "nullable|bool",
            "status_kis"      => "nullable|bool",
        ]);

        // AD-User suchen
        $adUser = AdUser::where("sid", $validated["sid"])->first();

        if (! $adUser) {
            return response()->json([
                "message" => "Kein AD-Benutzer mit der angegebenen SID gefunden."
            ], 404);
        }

        // Mutation erstellen
        $mutation = Mutation::create([
            "ad_user_id"     => $adUser->id,
            "vertragsbeginn" => $validated["vertragsbeginn"],
            "vorname"        => $validated["vorname"] ?? null,
            "nachname"       => $validated["nachname"] ?? null,
            "mailendung"     => $validated["mailendung"] ?? null,
            "status_mail"    => $validated["status_mail"] ?? 0,
            "status_kis"     => $validated["status_kis"] ?? 0,
            "kommentar" => "Dieser Antrag wurde automatisch erstellt.",
			"ad_gruppen" => [],
			"kalender_berechtigungen" => [],
			"archiviert"     => false,
        ]);

        return response()->json($mutation, 201);
    }
}
