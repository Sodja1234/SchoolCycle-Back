<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): JsonResponse
    {
        // Validation des données
        $validated = Validator::make($request->all(), [
            'email' => 'required|string|email:rfc',
            'password' => 'required|string|min:6'
        ]);

        // Captures des erreurs 
        if ($validated->fails()) {
            return response()->json(['error' => $validated->errors()], 403);

        }

        $credentials = ['email' => $request->email, 'password' => $request->password];

        try {

            if (!auth()->attempt($credentials)) {
                return response()->json(['error' => 'email or password incorrect '], 400);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            
            $token = $user->createToken('token')->plainTextToken;
            $user['token'] = $token;

            return response()->json([
                'data' => $user,
            ], 201);

        } catch (\Exception $exception) {
            return response()->json([
                'error' => [
                    $exception->getMessage()
                ]
            ], 500);
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        try {
            //currentAccessToken pour supprimer que le token de la session en cours
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'deconnexion reussie']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

    }
}
