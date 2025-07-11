<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Mockery\Exception;

class ToggleUserStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        try {
            $userConnect = auth()->user();

            if ($userConnect->role !== 'admin') {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            if ($userConnect->id == $id) {
                return response()->json(['message' => 'Vous ne pouvez pas vous désactiver'], 403);
            }

            $user = User::find($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $user->state = !$user->state;
            $user->save();

            return response()->json([
                'message' => 'Utilisateur ' . ($user->state ? 'activé' : 'désactivé') . ' avec succès.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

}
