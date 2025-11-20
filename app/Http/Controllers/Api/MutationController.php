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
     *     @OA\Response(response=422, description="Ungültige Eingabe")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "sid"             => "required|string",
            "vertragsbeginn"  => "required|date_format:Y-m-d",
            "vorname"         => "nullable|string|max:255",
            "nachname"        => "nullable|string|max:255",
            "mailendung"      => "nullable|string|max:255",
            "status_mail"     => "nullable|bool",
            "status_kis"      => "nullable|bool",
        ]);

        $adUser = AdUser::where("sid", $validated["sid"])->first();

        if (! $adUser) 
		{
            return response()->json([
                "message" => "Kein AD-Benutzer mit der angegebenen SID gefunden."
            ], 404);
        }

		$oldData = [
			"vorname_old" => $adUser->firstname,
			"nachname_old" => $adUser->lastname,
			"anrede_id_old" => $adUser->anrede?->id,
			"titel_id_old" => $adUser->titel?->id,
			"arbeitsort_id_old" => $adUser->arbeitsort?->id,
			"unternehmenseinheit_id_old" => $adUser->unternehmenseinheit?->id,
			"abteilung_id_old" => $adUser->abteilung?->id,
			"funktion_id_old" => $adUser->funktion?->id,
		];

		$mutation = Mutation::create(array_merge($oldData, [
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
		]));

        return response()->json($mutation, 201);
    }

	/**
	 * @OA\Patch(
	 *     path="/api/mutationen/{id}",
	 *     summary="Aktualisiert ein oder mehrere Attribute einer Mutation.",
	 *     tags={"Mutationen"},
	 *     security={{"basicAuth":{}}},
	 *     @OA\Parameter(
	 *         name="id",
	 *         in="path",
	 *         required=true,
	 *         description="ID der Mutation",
	 *         @OA\Schema(type="integer")
	 *     ),
	 *     @OA\RequestBody(
	 *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status_ad":2}
     *         )
	 *     ),
	 *     @OA\Response(response=200, description="Mutation aktualisiert"),
	 *     @OA\Response(response=404, description="Nicht gefunden"),
	 *     @OA\Response(response=422, description="Ungültige Eingabe")
	 * )
	 */
	public function update(Request $request, int $id)
	{
		$mutation = Mutation::find($id);

		if (! $mutation) 
		{
			return response()->json(["error" => "Mutation nicht gefunden"], 404);
		}

		$allowed = [
			"status_ad",
			"status_mail",
		];

		$data = $request->only($allowed);

		if (empty($data)) 
		{
			return response()->json(["error" => "Keine gültigen Attribute übergeben"], 422);
		}

		$mutation->fill($data);
		$mutation->save();

		return response()->json($mutation, 200);
	}
}
