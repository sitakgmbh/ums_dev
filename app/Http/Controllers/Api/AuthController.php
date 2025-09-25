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
     *     summary="Benutzer-Login",
     *     description="Ermöglicht lokalen Benutzern und LDAP-Benutzern den API-Login. Nur Admins erhalten ein Token.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","password"},
     *             @OA\Property(property="username", type="string", example="mmustermann"),
     *             @OA\Property(property="password", type="string", example="Passwort123!")
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
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Ungültige Anmeldedaten'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->hasRole('admin')) 
		{
            return response()->json(['message' => 'Keine Berechtigung'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }
	
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
