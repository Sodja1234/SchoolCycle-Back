<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function getOrCreateChat(Request $request)
    {
        $request->validate([
            'posted_by' => 'required|integer',
        ]);

        $created_by = Auth::id();
        $posted_by = $request->posted_by;

        // Recherche d'un chat existant 
        $chat = Chat::where('announcement_id', $posted_by)
                    ->where('created_by', $created_by)
                    ->first();
        
        // S'il n'existe pas, on le crée
        if (!$chat) {
            $chat = Chat::create([
                'created_by' => $created_by,
                'posted_by' => $posted_by,
                'is_closed' => false,
            ]);

            // Retourner un code HTTP 201 pour indiquer une création
            return response()->json($chat, 201);
        }

        // Retourner un code HTTP 200 si le chat existe déjà
        return response()->json($chat, 200);
    }

    public function closeChat(Chat $chat)
    {
        // Vérifiez que l'utilisateur est autorisé à fermer le chat
        if (Auth::id() !== $chat->created_by) {
            return response()->json(['error' => 'Non autorisé à clôturer ce chat'], 403);
        }
    
        // Simulez une annonce liée au chat
        $posted_by = $chat->posted_by;
    
        // Fermez tous les autres chats liés à cette annonce
        Chat::where('posted_by', $posted_by)
            ->where('id', '!=', $chat->id)
            ->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);
    
        // Mettez à jour le chat actuel pour qu'il soit fermé dans 30 jours
        $chat->update([
            'close_to' => now()->addDays(30), // Définit la date de fermeture à 30 jours
        ]);
    
        return response()->json(['message' => 'Les autres chats ont été fermés et celui-ci sera fermé dans 30 jours'], 200);
    }
}
