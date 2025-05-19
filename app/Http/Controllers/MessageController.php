<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    
    public function sendMessage(Request $request, Chat $chat)
    {
        $request -> validate([
            'content' => 'required|string',
        ]);
        //On en empêche l'utilisateur de créer un message dans une conversation fermée
        if($chat->is_closed || ($chat->close_to && now()->isAfter($chat->close_to))){
            return response() -> json(['error' => 'Cette conversation est fermée'], 403);
        }

        //Comme on peut pas vérifier l'auteur de l'annonce 
        //On va supposer que l'utilisateur courant s'il est créateur de chat
        if (Auth::id() !== $chat->created_by){
            abort(403, 'vous n\'avez pas accès à cette conversation');
        }

        $receiver = $chat->created_by; 
        $message = Message::create([
            'conversation' => $chat ->id,
            'sender' => Auth::id(),
            'receiver' => $receiver,
            'content' => $request -> content,
        ]);
        

        return response() -> json($message, 201);
    }

    public function getMessages(Chat $chat)
    {
        if (Auth::id() !== $chat -> created_by){
            abort(403, 'vous n\'avez pas accès à cette conversation');
        }

        $message = $chat -> messages()->latest()->get();

        return response() -> json($chat -> messages);
    }
    
    
    
    
    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
