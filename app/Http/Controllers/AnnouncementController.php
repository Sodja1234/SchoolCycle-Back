<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
     //function pour voir toutes les annonces disponibles
     public function index(Request $request, $userId = null)
     {
         // Initialisation de la requête avec les relations optimisées
         $query = Announcement::with([
             'photos' => function($query) {
                 $query->select('id', 'announcement_id', 'url')
                     ->orderBy('created_at')
                     ->limit(1); // On ne prend que la première photo
             },
             'favorites',
             'user',
             'category'
         ]);
 
         // Détermination du scope des annonces
         if ($userId) {
             // Cas 1: Annonces d'un utilisateur spécifique
             $query->where('created_by', $userId);
         } elseif ($request->routeIs('announcements.user.index')) {
             // Cas 2: Annonces de l'utilisateur connecté
             $user = auth()->user();
 
             if (!$user) {
                 return response()->json([
                     'message' => 'Accès non autorisé',
                     'error' => 'Authentification requise'
                 ], 401);
             }
 
             $query->where('created_by', $user->id);
         } else {
             // Cas 3: Annonces publiques (par défaut)
             $query->where('is_completed', false)
                 ->where('is_cancelled', false);
         }
 
         // Filtre: Recherche texte (titre ou description)
         if ($request->filled('search')) {
             $search = strtolower($request->query('search'));
             $query->where(function($q) use ($search) {
                 $q->whereRaw('LOWER(title) LIKE ?', ['%'.$search.'%'])
                     ->orWhereRaw('LOWER(description) LIKE ?', ['%'.$search.'%']);
             });
         }
 
         // Filtre: Type d'opération (vente/don/échange)
         if ($request->filled('operation_type')) {
             $operationTypes = explode(',', $request->query('operation_type'));
 
             $query->where(function($q) use ($operationTypes, $request) {
                 foreach ($operationTypes as $type) {
                     if ($type === 'sale') {
                         $q->orWhere(function($subQuery) use ($request) {
                             $subQuery->where('operation_type', 'sale');
 
                             // Filtres prix pour les ventes
                             if ($request->filled('min_price')) {
                                 $subQuery->where('price', '>=', $request->query('min_price'));
                             }
                             if ($request->filled('max_price')) {
                                 $subQuery->where('price', '<=', $request->query('max_price'));
                             }
                         });
                     } else {
                         $q->orWhere('operation_type', $type);
                     }
                 }
             });
         }
 
         // Filtre: État du produit
         if ($request->filled('state')) {
             $query->whereIn('state', explode(',', $request->query('state')));
         }
 
         // Filtre: Statut d'achèvement ou annulation
         if ($request->filled('is_completed')) {
             $query->where('is_completed', (bool) $request->query('is_completed'));
         }
 
         if ($request->filled('is_cancelled')) {
             $query->where('is_cancelled', (bool) $request->query('is_cancelled'));
         }
 
        if ($request->filled('deleted_at')) {
            $deleted = $request->query('deleted_at');

            if ($deleted === 'true') {
                $query->onlyTrashed();
            } 
        }
 
         // Tri dynamique
         $sortField = $request->query('sort_field', 'created_at');
         $sortDirection = $request->query('sort_direction', 'desc');
         $query->orderBy($sortField, $sortDirection);
 
         // Pagination et retour des résultats
         return AnnouncementResource::collection(
             $query->paginate($request->query('per_page', 12))
         );
     }


    public function show($id)
    {
        try {
            $announcementSingle = Announcement::findOrFail($id);
            $announcement = new AnnouncementResource($announcementSingle);
            return $announcement;

            /*return response()->json([
                'data' => $announcement
            ]);*/
        } catch (\Exception $exception) {
            return response()->json([
                'Message' => 'Une erreur est survenue',
                'Erreur' => $exception->getMessage()
            ]);
        }
    }


    //function pour la creation d'une annonce
    public function store(Request $request)
    {
        //on recupere le user connecter
        $user = Auth::user();

        try {
            if ($user->role !== 'tutor') {
                return response()->json([
                    "Message" => "Vous n'avez pas le droit de creer une annonce"
                ]);
            }
            $request->validate([
                'title' => 'required|string|min:5|max:500',
                'description' => 'required|string|max:1000',
                'category_id' => 'required|exists:categories,id',
                'operation_type' => 'required|string|in:don,sale,exchange',
                'state' => 'required|string|in:new,good,damaged,like new',
                'price' => 'nullable|numeric',
                'is_completed' => 'nullable|boolean',
                'is_cancelled' => 'nullable|boolean',
                'exchange_location_address' => 'string|max:255',
                'exchange_location_lng' => 'numeric',
                'exchange_location_lat' => 'numeric',
                'photos' => 'required|array|min:1',
                'photos.*' => 'required|image|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            $announcement = Announcement::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'category_id' => $request['category_id'],
                'operation_type' => $request['operation_type'],
                'state' => $request['state'],
                'price' => $request['price'],
                'is_completed' => $request['is_completed'] ?? false,
                'is_cancelled' => $request['is_cancelled'] ?? false,
                'exchange_location_address' => $request['exchange_location_address'],
                'exchange_location_lng' => $request['exchange_location_lng'],
                'exchange_location_lat' => $request['exchange_location_lat'],
                'created_by' => $user->id

            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $image) {
                    $path = $image->store('announcements', 'public');

                    Photo::create([
                        'announcement_id' => $announcement->id,
                        'url' => $path,
                    ]);
                }
            }

            $announcement->load(['category', 'user', 'favorites', 'photos']);

            $users = User::whereHas('preferences', function ($query) use ($announcement) {
                $query->where('categories.id', $announcement->category_id);
            })->get();


            // verification si il n'a trouvé aucun utilisateur
            if ($users->count() !== 0) {
                //Envoi des mail aux utilisateurs
                Notification::send($users, new NewAnnouncementNotification($announcement));
            }

            return new AnnouncementResource($announcement);
        } catch (\Exception $exception) {
            return response()->json([
                'Message' => "Une erreur est survenue lors de la creation de l'annonce ",
                'Erreur' => $exception->getMessage()
            ]);
        }
    }


    //function pour mettre à une annonce
    public function update(Request $request, Announcement $announcement)
    {
        $user = Auth::user();
        try {

            //on verifie si l'utilisateur connecter est l'auteur de l'article
            if ($user->id !== $announcement->created_by) {
                return response()->json([
                    'Message' => "Vous n'avez pas le droit de modifier cette annonce"
                ], 403);
            } else {
                $validated = $request->validate([
                    'title' => 'required|string|min:5|max:500',
                    'description' => 'required|string|max:1000',
                    'operation_type' => 'required|string|in:don,sale,exchange',
                    'state' => 'required|string|in:new,good,damaged,like new',
                    'price' => 'nullable|numeric',
                    'is_completed' => 'nullable|boolean',
                    'is_cancelled' => 'nullable|boolean',
                    'exchange_location_address' => 'string|max:255',
                    'exchange_location_lng' => 'numeric',
                    'exchange_location_lat' => 'numeric',
                    'category_id' => 'required|exists:categories,id'
                ]);

                $announcement->update($validated);
                return new AnnouncementResource($announcement);
            }
        } catch (\Exception $exception) {

            return response()->json([
                'Message' => "Une erreur est survenue lors de la mise à jour de l'annonce ",
                'Erreur' => $exception->getMessage()
            ]);
        }
    }

    //funnction pour supprimer une annonce
    public function destroy(Announcement $announcement)
    {
        $user = Auth::user();

        try {

            if ($user->id !== $announcement->created_by && $user->role !=='admin') {
                return response()->json([
                    'Message' => "Vous n'avez pas le droit de supprimer cette annonce",
                ], 403);
            } else {

                foreach ($announcement->photos as $photo) {
                    // Supprimer le fichier du disque (storage/app/public/...)
                    Storage::disk('public')->delete($photo->url);
                    // Supprimer la photo  dans la base de données
                    $photo->delete();
                }
                $announcement->delete();
                return response()->json([
                    'Message' => "Annonce supprimer"
                ]);
            }
        } catch (\Exception $exception) {
            return response()->json([
                'Message' => "Une erreur est survenue lors de la suppression",
                'Erreur' => $exception->getMessage()
            ], 500);
        }
    }
    //methode pour recuperer les annonces de l'utilisateur connecté
    public function getCreatorAnnouncement()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $announcements = Announcement::where('created_by', $user->id)->get();
        return response()->json([
            "data"=> AnnouncementResource::collection($announcements)
        ]);
    }


    public function getUser(request $request, $id)
    {
        $currentUserId = Auth::user();
        $isOwner = $currentUserId == $id;

        if ($isOwner) {
            return Announcement::where('user_id', $id)->get();
        } else {
            return Announcement::where('user_id', $id)->where('status', 'published')->get();
        }
    }
    public function getSimilarAnnoucement(Request $request, Announcement $announcement)
    {
        $similar = Announcement::where('category_id', $announcement->category_id)
            ->where('id', '!=', $announcement->id)
            ->latest()
            ->take(5)
            ->get();

        $similar = AnnouncementResource::collection($similar);

        return response()->json([
            'data' => $similar
        ]);
    }

    public function getUserFavorites(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        $favoriteIds = $user->favorites()->pluck('announcement_id');

        $announcements = Announcement::with(['photos', 'user', 'category'])
            ->whereIn('id', $favoriteIds)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 12));

        return AnnouncementResource::collection($announcements);
    }
}

