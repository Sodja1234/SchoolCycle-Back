<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ToggleFavoriteController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TutorController;
use Illuminate\Support\Facades\Hash;
Route::middleware('auth:sanctum')->group(function () {
    //création et récupération d'un chat pour une annonce
    Route::post('/announcements/{announcement}/chats', [ChatController::class, 'getOrCreateChat']);

    //Fermer un chat
    Route::post('/chats/{chat}/close', [ChatController::class, 'closeChat']);

    //Envoyer un message dans un chat
    Route::post('/chats/{chat}/messages', [MessageController::class, 'sendMessage']);

    //Récupérer les massages d'unchat
    Route::get('/chats/{chat}/messages', [MessageController::class, 'getMessages']);

    //Lister tous les chats d'un utilisateur connecté
    Route::get('/my-chats', [ChatController::class, 'myChats']);

    //Chat existant pour l'utilisateur en fonction d'une annonce
    Route::get('/announcements/{announcement}/user-chat', [ChatController::class, 'userChatForAnnouncement']);

    //Récupérer le chat pour une annonce spécifique
    Route::get('/announcements/{announcement}/chats', [ChatController::class, 'chatsForAnnouncement']);

    // Ajouter ou retirer une annonce aux favoris
    Route::post('/favorites/{announcement}', ToggleFavoriteController::class);
    Route::apiResource('/announcements', AnnouncementController::class)->except(['index', 'show']);
    Route::apiResource('/categories', CategoryController::class)->except(['index', 'show']);

    Route::get('/get_creator_announcement', [AnnouncementController::class, 'getCreatorAnnouncement']);
    Route::get('annoncements/favorites', [AnnouncementController::class, 'getUserFavorites']);
    Route::put('/users/update', [UserController::class, 'update']);
    Route::put('/users/update-password', [UserController::class, 'updatePassword']);
    Route::get('/users/profile', [UserController::class, 'show']);

    Route::post('/reports', [ReportController::class, 'store']);
    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/users/get', [UserController::class, 'show']);
    Route::get('/users', [UserController::class, 'index']);
      // CRUD pour les tuteurs
    Route::get('/tutors', [TutorController::class, 'index']);
    Route::get('/tutors/get', [TutorController::class, 'show']);
    Route::post('/tutors/create', [TutorController::class, 'store']);
    Route::put('/tutors/update', [TutorController::class, 'update']);
    Route::delete('/tutors/delete', [TutorController::class, 'destroy']);

    //Route pour bloquer debloquer l'utilisateur
    Route::patch('/user/toggle-status/{id}', \App\Http\Controllers\ToggleUserStatusController::class);
});


// Routes publiques
//Routes liées à la recuperation des annonces ======================================================================================
// Annonces publiques & annonces de l'utilisateur specifique
Route::get('announcements/public/{userId?}', [AnnouncementController::class, 'index'])
    ->where('userId', '[0-9]+')
    ->name('announcements.public.index');

Route::apiResource('announcement/public/single', AnnouncementController::class)
    ->only(['show']);

// Route utilisateur connecté
Route::middleware('auth:sanctum')->get('announcements/user', [AnnouncementController::class, 'index'])
    ->name('announcements.user.index');
//End ==============================================================================================================================


Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);
//route pour recuperer les articles similaires
Route::get('/announcements/{announcement}/similar', [AnnouncementController::class, 'getSimilarAnnoucement']);



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});



require __DIR__ . '/auth.php';
