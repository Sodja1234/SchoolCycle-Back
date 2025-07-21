<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privé pour les chats
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    Log::info('Broadcast policy', [
        'user_id' => $user->id,
        'chat_id' => $chatId,
        'chat_created_by' => $chat?->created_by,
        'announcement_created_by' => $chat?->announcement?->created_by,
    ]);
    if (!$chat || !$chat->announcement) {
        return false;
    }
    
    
    // L'utilisateur peut écouter s'il est le créateur du chat ou le créateur de l'annonce
    return $user->id === $chat->created_by || $user->id === $chat->announcement->created_by;
});

// Canal privé pour les utilisateurs
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});