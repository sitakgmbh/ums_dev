<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/incidents",
     *     summary="Incident erstellen",
     *     tags={"Incidents"},
     *     security={{"basicAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "priority", "metadata"},
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}),
     *             @OA\Property(property="metadata", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Incident erstellt",
     *         @OA\JsonContent(ref="#/components/schemas/Incident")  
     *     ),
     *     @OA\Response(response=401, description="Nicht authentifiziert"),
     *     @OA\Response(response=403, description="Keine Berechtigung"),
     *     @OA\Response(response=422, description="Validierungsfehler")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', 'string', Rule::in(['high', 'medium', 'low'])],
            'metadata' => 'nullable|array',
        ]);

        $incident = Incident::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'metadata' => $validated['metadata'] ?? [],
            'created_by' => auth()->id(),  
        ]);

        return response()->json($incident, 201);
    }
}
