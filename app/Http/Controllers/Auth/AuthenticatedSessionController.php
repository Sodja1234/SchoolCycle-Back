<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\Auth\AuthLoginResource;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): AuthLoginResource | JsonResponse
        {
            $user = User::where('email', '=', $request->validated('email'))->first();

            if (! ($user instanceof User) || !Hash::check($request->validated('password'), $user->password)) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }


            $token = $user->createToken($user->email)->plainTextToken;

            $user->token = $token;

            return new AuthLoginResource($user);

        }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user  = $request->user();

        if (!($user instanceof User)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json(['logout' => true]);
    }
}
