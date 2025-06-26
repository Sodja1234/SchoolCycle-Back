<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatRessource;
use App\Models\Announcement;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChatController extends Controller
{
    use AuthorizesRequests;

    public function getOrCreateChat(Request $request, Announcement $announcement)
    {


        $created_by = Auth::id();

        // On empêche la création d'un chat si l'utilisateur est le même que celui qui a posté l'annonce
        if ($created_by == $announcement->created_by) {
            return response()->json(['error' => 'Vous ne pouvez pas créer un chat avec vous-même'], 403);
        }

        // Recherche d'un chat existant
        $chat = Chat::where('posted_by', $announcement->id)
                    ->where('is_closed', false)
                    ->where('created_by', $created_by)
                    ->first();

        // S'il n'existe pas, on le crée
        if (!$chat) {
            //Le message est obligatoire pour créer un chat
            $request->validate(['content' => 'required|string']);
            $chat = Chat::create([
                'created_by' => $created_by,
                'posted_by' => $announcement->id,
                'is_closed' => false,
            ]);
            Message::create([
                'conversation' =>$chat->id,
                'sender' =>$created_by,
                'receiver' =>$announcement->created_by,
                'content' =>$request->content,
            ]);

            // Retourner un code HTTP 201 pour indiquer une création
            return response()->json($chat, 201);
        }
        if (!$chat){
            return  response()->json(['error' => 'impossible de créer un chat sans message'], 422);
        }

        $chat->load(['announcement', 'messages']);

        // Retourner un code HTTP 200 si le chat existe déjà
        return response()->json($chat, 200);
    }

    public function closeChat(Chat $chat)
    {
        // Vérifiez que l'utilisateur est autorisé à fermer le chat
        if (Auth::id() !== $chat->announcement->created_by) {
            if (Auth::id() !== $chat->announcement->created_by) {
                return response()->json(['error' => 'Non autorisé à clôturer ce chat'], 403);
            }

            // Fermez tous les autres chats liés à cette annonce
            $otherChats = Chat::where('posted_by', $chat->posted_by)
                ->where('id', '!=', $chat->id)
                ->get();
            foreach ($otherChats as $otherChat) {
                //Supprimer les messages liés à ces chats
                $otherChat->messages()->delete();

                //Fermet le chat
                $otherChat->update([
                    'is_closed' => true,
                    'closed_at' => now(),
                ]);
            }
            // Mettre à jour le chat actuel pour qu'il soit fermé dans 30 jours
            $chat->update([
                'close_to' => now()->addDays(30), // Définit la date de fermeture à 30 jours
                'is_closed' => true,
                'closed_at' => now(),
            ]);
        }

            return response()->json(['message' => 'Les autres chats ont été fermés et celui-ci sera fermé dans 30 jours'], 200);

    }

    public function mychats()
    {
        $created_by = Auth::id();


        // On charge les messages et l'annonce associée
        $chat = Chat::with(['announcement', 'messages'])
            ->where(function ($query) use ($created_by) {
                $query->where('created_by', $created_by)
                      ->orWhereHas('announcement', function ($q) use ($created_by) {
                          $q->where('created_by', $created_by);
                      });
            })
            ->latest()
            ->distinct()
            ->get();
        return ChatRessource::collection($chat);
    }

    public function chatsForAnnouncement(Announcement $announcement)
    {
        $userId = Auth::id();

        //On vérifie si l'utilisateur connecté est le créateur de l'annonce
        $titulaire = $announcement->created_by == $userId;

        //On vérifie si l'utilisateur connecté et initiateur d'un chat lié à l'annonce
        $initiateur = $announcement->chats()->where('created_by', $userId)->exists();

        if (!$titulaire && !$initiateur){
            return response()->json(['error' => "Vous n'avez pas accès à ces conversations"], 403);
        }

        //On retourne les messages liés à l'annonce
        $chat = $announcement->chats()->with(['messages', 'user'])->get();

        return ChatRessource::collection($chat);
    }

    public function userChatForAnnouncement(Announcement $announcement)
    {
        $userId = Auth::id();

        //On empêche le créateur de l'annonce d'accéder à cette route
        if ($announcement->created_by == $userId){
            return response()->json(['error' => 'Le créateur ne peut pas avoir de chat avec lui même'],403);
        }

        $chat = $announcement->chats()
            ->where('created_by', $userId)
            ->with(['messages', 'user'])
            ->first();
        if (!$chat){
            return response()->json(['error' => 'Aucun chat trouvé'], 404);
        }
        return ChatRessource::collection($chat);
    }
}
