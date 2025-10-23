<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Eroeffnung;

class EroeffnungController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/eroeffnungen/open",
     *     summary="Liste aller Eröffnungen",
     *     description="Gibt alle Eröffnungen zurück, die nicht archiviert sind.",
     *     tags={"Eröffnungen"},
     *     security={{"basicAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste aller Eröffnungen",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Eroeffnung")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Nicht authentifiziert"),
     *     @OA\Response(response=403, description="Keine Berechtigung")
     * )
     */
    public function open(Request $request)
    {
		$eroeffnungen = Eroeffnung::query()
			->with(['arbeitsort', 'unternehmenseinheit', 'abteilung', 'funktion'])
			->where("archiviert", false)
			->orderBy("id", "asc")
			->get()
			->map(function ($eroeffnung) {
				$data = $eroeffnung->toArray();
				return $data;
			});


        return response()->json($eroeffnungen, 200);
    }

    /**
     * @OA\Patch(
     *     path="/api/eroeffnungen/{id}",
     *     summary="Eröffnung aktualisieren",
     *     description="Aktualisiert ein oder mehrere Attribute einer Eröffnung.",
     *     tags={"Eröffnungen"},
     *     security={{"basicAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID der Eröffnung",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={"status_ad":2}
     *         )
     *     ),
     *     @OA\Response(response=200, description="Eröffnung aktualisiert"),
     *     @OA\Response(response=404, description="Nicht gefunden"),
     *     @OA\Response(response=401, description="Nicht authentifiziert"),
     *     @OA\Response(response=403, description="Keine Berechtigung"),
	 *     @OA\Response(response=422, description="Ungültige Eingabe")
     * )
     */
    public function update(Request $request, int $id)
    {
        $eroeffnung = Eroeffnung::find($id);

        if (! $eroeffnung) 
		{
            return response()->json(["error" => "Eröffnung nicht gefunden"], 404);
        }

        $allowed = [
            "status_ad",
        ];

        $data = $request->only($allowed);

        if (empty($data)) {
            return response()->json(["error" => "Keine gültigen Attribute übergeben"], 422);
        }

        $eroeffnung->fill($data);
        $eroeffnung->save();

        return response()->json($eroeffnung, 200);
    }
}
