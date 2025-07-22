<?php

namespace App\Http\Controllers;

use App\Events\ChatStatusChanged;
use App\Http\Resources\ChatRessource;
use App\Models\Announcement;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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

        // Empêcher la création d'un chat si l'annonce est clôturée
        if ($announcement->is_completed) {
            return response()->json(['error' => "Impossible de créer un chat : l'annonce est clôturée."], 403);
        }

        // Empêcher la création d'un chat si un chat fermé existe déjà pour cette annonce et cet utilisateur
        $closedChat = Chat::where('posted_by', $announcement->id)
            ->where('created_by', $created_by)
            ->where('is_closed', true)
            ->first();
        if ($closedChat) {
            return response()->json(['error' => "Vous avez déjà eu une conversation clôturée pour cette annonce."], 403);
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
            
            // Gestion decontenu doublement encodé en JSON
            $content = $request->content;
            
            // On Vérifier si le contenu est un JSON encodé
            if (is_string($content) && json_decode($content) !== null) {
                $decoded = json_decode($content, true);
                // Si c'est un objet avec une propriété 'content', extraire le contenu
                if (is_array($decoded) && isset($decoded['content'])) {
                    $content = $decoded['content'];
                }
            }
            
            // On s'assurer que le contenu est bien une chaîne de caractères
            $content = (string) $content;
            
            Message::create([
                'conversation' =>$chat->id,
                'sender' =>$created_by,
                'receiver' =>$announcement->created_by,
                'content' => $content,
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
        // On Vérifie que l'utilisateur est autorisé à fermer le chat (seul le créateur de l'annonce)
        if (Auth::id() !== $chat->announcement->created_by) {
            return response()->json(['error' => 'Non autorisé à clôturer ce chat'], 403);
        }

        // On Vérifie si l'annonce a déjà été marquée comme terminée
        $existingClosedChat = Chat::where('posted_by', $chat->posted_by)
            ->where('is_closed', true)
            ->where('close_to', '!=', null)
            ->first();

        if ($existingClosedChat && $existingClosedChat->id !== $chat->id) {
            return response()->json(['error' => 'Cette annonce a déjà été marquée comme terminée'], 409);
        }

        // Fermer tous les autres chats liés à cette annonce
        $otherChats = Chat::where('posted_by', $chat->posted_by)
            ->where('id', '!=', $chat->id)
            ->get();

        foreach ($otherChats as $otherChat) {
            // Supprimer les messages liés à ces chats
            $otherChat->messages()->delete();

            // Fermer le chat immédiatement
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
         //Récuperer l'annonce liée
         $announcement = $chat->announcement;
         if ($announcement && !$announcement->is_completed){
             $announcement->is_completed = true;
             $announcement->save();
         };

        // Diffuser l'événement de changement de statut
        broadcast(new ChatStatusChanged($chat, 'closed'))->toOthers();

        return response()->json(['message' => 'Les autres chats ont été fermés et celui-ci sera fermé dans 30 jours'], 200);
    }

    public function mychats(Request $request)
    {
        $created_by = Auth::id();

        $perPage = $request->input('per_page', 10); 
        $search = $request->input('search'); // Pour la recherche 

        // Construction de la requête de base
        $query = Chat::with(['announcement', 'messages'])
            ->where(function (
                $query) use ($created_by) {
                $query->where('created_by', $created_by)
                    ->orWhereHas('announcement', function ($q) use ($created_by) {
                        $q->where('created_by', $created_by);
                    });
            });

        // Tri par date du dernier message (descendant)
        $query = $query->withCount(['messages as last_message_at' => function ($q) {
            $q->select(DB::raw('MAX(created_at)'));
        }])->orderByDesc('last_message_at');

        // Filtrage par titre d'annonce si search est présent
        if ($search) {
            $query = $query->whereHas('announcement', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }

        // Pagination
        $chats = $query->paginate($perPage);

        return ChatRessource::collection($chats);
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

        if ($chat->isEmpty()) {
            return response()->json(['error' => 'Aucun chat trouvé pour cette annonce'], 404);
        }

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
        return new ChatRessource($chat);
    }

    public function contactInfo(Chat $chat)
    {
        $userId = Auth::id();

        // Déterminer l'autre participant
        if ($chat->created_by == $userId) {
            $contact = $chat->announcement->user; // user() = créateur de l'annonce
        } else {
            $contact = $chat->user; // user() = créateur du chat
        }

        // Historique des annonces discutées entre ces deux utilisateurs
        $history = Chat::where(function ($q) use ($userId, $contact) {
                $q->where('created_by', $userId)
                  ->whereHas('announcement', function ($q2) use ($contact) {
                      $q2->where('created_by', $contact->id);
                  });
            })
            ->orWhere(function ($q) use ($userId, $contact) {
                $q->where('created_by', $contact->id)
                  ->whereHas('announcement', function ($q2) use ($userId) {
                      $q2->where('created_by', $userId);
                  });
            })
            ->with('announcement:id,title')
            ->get()
            ->pluck('announcement')
            ->unique('id')
            ->values()
            ->map(function ($a) {
                return [
                    'announcement_id' => $a->id,
                    'title' => $a->title,
                ];
            });

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
            ],
            'announcement' => [
                'id' => $chat->announcement->id,
                'title' => $chat->announcement->title,
                'description' => $chat->announcement->description,
                'price' => $chat->announcement->price,
            ],
            'history' => $history,
        ]);
    }
}
