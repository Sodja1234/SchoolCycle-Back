<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    //
    public function update(Request $request)
    {
        // Vérifie si l'utilisateur est authentifié
        $user = auth()->user();
        if (!$user) {
            // L'utilisateur n'est pas authentifié
            return response()->json(["message" => "Autorisation refusée."], 403);
        }

        // Validation des données reçues
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ], [
            "name.required" => "Le nom complet est requis.",
            "name.string" => "Le nom complet doit être une chaine de caractères",
            "name.max" => "Le nom complet ne doit pas dépasser 255 caractères",
        ]);

        if ($validator->fails()) {
            // Retourne les erreurs de validation
            return response()->json([
                'errors' => $validator->errors(),
                'data' => $request->all()
            ], 400);
        }

        // Mise à jour du nom si modifié
        if ($request->input('name') != $user->name) {
            $user->name = $request->input('name');
        } else {
            // Si le nom n'est pas modifié, on ne fait rien
            return response()->json(["message" => "Aucune modification apportée. Veillez insérer un autre nom"], 400);
        }

        $user->save();

        // Retourne un message de succès
        return response()->json(["message" => "Informations mises à jour avec succès."], 200);
    }

    /**
     * Retourne les informations de l'utilisateur connecté.
     */
    public function show()
    {
        $user = auth()->user();
        if (!$user) {
            // Aucun utilisateur connecté
            return response()->json(["message" => "Aucun utilisateur connecté."], 400);
        }
        // Retourne les informations de l'utilisateur
        return response()->json($user, 200);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(["message" => "Aucun utilisateur connecté."], 401);
        }

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:new_password',
        ], [
            "old_password.required" => "Le mot de passe actuel est requis.",
            "new_password.required" => "Le nouveau mot de passe est requis.",
            "new_password.min" => "Le nouveau mot de passe doit comporter au moins 8 caractères.",
            "password_confirmation.required" => "La confirmation du mot de passe est requise.",
            "password_confirmation.same" => "La confirmation du mot de passe ne correspond pas au nouveau mot de passe."
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->input('old_password'), $user->password)) {
            return response()->json(["message" => "Le mot de passe actuel est incorrect."], 403);
        }

        if ($request->input('new_password') === $request->input('old_password')) {
            return response()->json(["message" => "Le nouveau mot de passe doit être différent de l'ancien."], 403);
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->json(["message" => "Mot de passe mis à jour avec succès."], 200);
    }

    public function index(Request $request)
    {
        $users = User::paginate(10);

        return UserResource::collection($users);
    }
}
