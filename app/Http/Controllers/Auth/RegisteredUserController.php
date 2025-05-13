<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Validator;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            //validation des données
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'role' => 'in:admin,tutor'
            ]);
            
            //recuperation des erreurs
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 403);
            }
            
            $validated = $validator->validated();
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'] ?? 'tutor'
            ]);
    
        
            //cet event à utilser ex lors de la verification de l'email
            event(new Registered($user));
            
            //creation d'un token propre à l'utilisateur 
            $token = $user->createToken('token')->plainTextToken;
            return response()->json([
                "data" => [
                    'token' => $token,
                    'user' => $user
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                "error" => $e->getMessage() 
            ], 500);
        }
        
    }
}
