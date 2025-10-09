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
     *     summary="Liste aller offenen Eröffnungen",
     *     description="Gibt alle Eröffnungen zurück, die nicht archiviert sind (archiviert = 0).",
     *     tags={"Eröffnungen"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste der offenen Eröffnungen",
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
            ->where('archiviert', 0)
            ->with([
                'sapRolle',
                'antragsteller',
                'bezugsperson',
                'vorlageBenutzer',
                'arbeitsort',
                'unternehmenseinheit',
                'abteilung',
                'abteilung2',
                'funktion',
                'owner',
            ])
            ->orderBy('vertragsbeginn', 'asc')
            ->get();

        return response()->json($eroeffnungen, 200);
    }
}
