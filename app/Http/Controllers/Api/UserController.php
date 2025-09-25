<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * @OA\Get(
 *     path="/api/users",
 *     summary="Liste aller Benutzer",
 *     tags={"Users"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste von Benutzern"
 *     ),
 *     @OA\Response(response=401, description="Nicht authentifiziert"),
 *     @OA\Response(response=403, description="Keine Berechtigung")
 * )
 *
 * @OA\Get(
 *     path="/api/users/{id}",
 *     summary="Einen Benutzer abrufen",
 *     tags={"Users"},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(
 *         response=200,
 *         description="Benutzer gefunden",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(response=404, description="Benutzer nicht gefunden")
 * )
 *
 * @OA\Post(
 *     path="/api/users",
 *     summary="Lokalen Benutzer erstellen",
 *     tags={"Users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username","firstname","lastname","email","password"},
 *             @OA\Property(property="username", type="string", example="maxmuster"),
 *             @OA\Property(property="firstname", type="string", example="Max"),
 *             @OA\Property(property="lastname", type="string", example="Mustermann"),
 *             @OA\Property(property="email", type="string", format="email", example="max@example.com"),
 *             @OA\Property(property="password", type="string", example="geheim123"),
 *             @OA\Property(property="role", type="string", example="user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Benutzer erstellt",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     )
 * )
 *
 * @OA\Put(
 *     path="/api/users/{id}",
 *     summary="Lokalen Benutzer bearbeiten",
 *     tags={"Users"},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"firstname","lastname","email"},
 *             @OA\Property(property="firstname", type="string"),
 *             @OA\Property(property="lastname", type="string"),
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="role", type="string")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Benutzer aktualisiert",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(response=403, description="Nur lokale Benutzer dürfen bearbeitet werden"),
 *     @OA\Response(response=404, description="Benutzer nicht gefunden")
 * )
 *
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     summary="Benutzer löschen",
 *     tags={"Users"},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=204, description="Benutzer gelöscht"),
 *     @OA\Response(response=404, description="Benutzer nicht gefunden")
 * )
 */
class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Benutzer nicht gefunden'], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username'  => 'required|string|max:30|unique:users,username',
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'username'   => $validated['username'],
            'firstname'  => $validated['firstname'],
            'lastname'   => $validated['lastname'],
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
            'auth_type'  => 'local', // fix gesetzt
            'is_enabled' => true,
        ]);

        $user->syncRoles([$validated['role']]);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) 
		{
            return response()->json(['message' => 'Benutzer nicht gefunden'], 404);
        }

        if ($user->auth_type !== 'local') 
		{
            return response()->json(['message' => 'Nur lokale Benutzer dürfen bearbeitet werden'], 403);
        }

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password'  => 'nullable|string|min:8',
            'role'      => 'nullable|exists:roles,name',
        ]);

        $user->update([
            'firstname' => $validated['firstname'],
            'lastname'  => $validated['lastname'],
            'email'     => $validated['email'],
            'password'  => !empty($validated['password'])
                ? bcrypt($validated['password'])
                : $user->password,
        ]);

        if (!empty($validated['role'])) 
		{
            $user->syncRoles([$validated['role']]);
        }

        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) 
		{
            return response()->json(['message' => 'Benutzer nicht gefunden'], 404);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
