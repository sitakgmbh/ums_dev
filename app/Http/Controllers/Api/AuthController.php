<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use Illuminate\Support\Facades\Hash;
use App\Services\LdapProvisioningService;

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
			'username' => 'required|string',
			'password' => 'required|string',
		]);

		$username = $credentials['username'];
		$password = $credentials['password'];

		try 
		{
			$connection = Container::getDefaultConnection();
			$ldapUser = LdapUser::where('samaccountname', '=', $username)->first();

			if (! $ldapUser) 
			{
				return response()->json(['message' => 'Benutzer nicht gefunden'], 401);
			}

			if (! $connection->auth()->attempt($ldapUser->getDn(), $password)) 
			{
				return response()->json(['message' => 'Ungueltige Anmeldedaten'], 401);
			}

			$provisioner = app(LdapProvisioningService::class);

			$existingUser = \App\Models\User::where('username', $username)->first();
			$user = $provisioner->provisionOrUpdateUserFromLdap($ldapUser, $username, ! $existingUser, $existingUser);

			if (! $user->hasRole('admin')) 
			{
				return response()->json(['message' => 'Keine Berechtigung'], 403);
			}

			$token = $user->createToken('api-token')->plainTextToken;

			return response()->json([
				'access_token' => $token,
				'token_type'   => 'Bearer',
			]);
		} 
		catch (\Exception $e) 
		{
			return response()->json(['message' => 'LDAP-Fehler: ' . $e->getMessage()], 500);
		}
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
