<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login",
     *     description="Bearer-Token anfordern",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="user1"),
     *             @OA\Property(property="password", type="string", example="Password!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login erfolgreich",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Ungültige Anmeldedaten"),
     *     @OA\Response(response=403, description="Keine Berechtigung")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            "username" => ["required", "string"],
            "password" => ["required", "string"],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(["message" => "Ungültige Anmeldedaten"], 401);
        }

        $user = Auth::user();

        if (!$user->hasRole("admin")) 
		{
            return response()->json(["message" => "Keine Berechtigung"], 403);
        }

        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "access_token" => $token,
            "token_type"   => "Bearer",
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Me",
     *     description="Gibt den aktuell authentifizierten Benutzer zurück",
     *     tags={"Auth"},
	 *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Erfolg",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="user1"),
     *             @OA\Property(property="email", type="string", example="user1@example.com"),
     *             @OA\Property(property="roles", type="array",
     *                 @OA\Items(type="string", example="admin")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthentifiziert")
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
