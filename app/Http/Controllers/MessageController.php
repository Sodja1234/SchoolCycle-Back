<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Resources\MessageRessource;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessageController extends Controller
{

    public function sendMessage(Request $request, Chat $chat)
    {
        //On vérifie si l'utilisateur à bien le droit d'envoyer un message
        if (
            Auth::id() !== $chat->created_by &&
            Auth::id() !==$chat->announcement->created_by
        ){
            return response()->json(['error' => "Vous n'avez pas accès à cette conversation"],403);
        }
        $request -> validate([
            'content' => 'required|string',
        ]);
        //On en empêche l'utilisateur de créer un message dans une conversation fermée
        if($chat->is_closed || ($chat->close_to && now()->isAfter($chat->close_to))){
            return response() -> json(['error' => 'Cette conversation est fermée'], 403);
        }

       //On vérifie si l'utilisateur à bien le droit d'envoyer un message
       if (Auth::id() !== $chat -> created_by && Auth::id() !== $chat -> announcement->created_by){
            return response()-> json(['error' => "vous n'avez pas accès à cette conversation"], 403);
       }
        //On vérifie si l'utilisateur a bien le droit d'envoyer un message
        $receiver = (Auth::id() === $chat -> created_by && $chat->announcement)
            ? $chat -> announcement->created_by: $chat->created_by;

        // Gérer le cas où le contenu est doublement encodé en JSON
        $content = $request->content;
        
        // Vérifier si le contenu est un JSON encodé
        if (is_string($content) && json_decode($content) !== null) {
            $decoded = json_decode($content, true);
            // Si c'est un objet avec une propriété 'content', extraire le contenu
            if (is_array($decoded) && isset($decoded['content'])) {
                $content = $decoded['content'];
            }
        }
        
        $message = $chat->messages()->create([
            'conversation' => $chat ->id,
            'sender' => Auth::id(),
            'receiver' => $receiver,
            'content' => (string) $content,
        ]);
        $message->load('senderUser');

        // Diffuser l'événement en temps réel
        broadcast(new MessageSent($message))->toOthers();

        return response() -> json($message, 201);
    }

    public function getMessages(Chat $chat)
    {
        //On vérifie si l'utilisateur a le droit de récupérer les messages
        if (Auth::id() !== $chat -> created_by && Auth::id() !== $chat -> announcement->created_by){
            return response()-> json(['error' => "Vous n'avez pas accès à cette conversation"], 403);
        }

        $messages = $chat -> messages()->with(['senderUser', 'receiverUser'])->oldest()->get();

        return MessageRessource::collection($messages);
    }


}




