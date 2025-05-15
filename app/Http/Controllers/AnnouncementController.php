<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    //function pour voir toutes les annonces
    public function index()
    {
        return AnnouncementResource::collection(Announcement::class);
    }


    public function show($id)
    {
        try {
            $announcement = Announcement::findOrFail($id);
            return response()->json([
                'data' => $announcement
            ]);
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
        $user=Auth::user();
        try {
            $request->validate([
                'title' => 'required|string|min:5|max:500',
                'description' => 'required|string|max:1000',
                'operation_type' => 'required|string|in:don,sale,exchange',
                'price' => 'nullable|numeric',
                'is_completed' => 'nullable|boolean',
                'is_cancelled' => 'nullable|boolean',
                'exchange_location_address' => 'string|max:255',
                'exchange_location_lng' => 'numeric',
                'exchange_location_lat' => 'numeric',

            ]);

            $announcement = Announcement::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'operation_type' => $request['operation_type'],
                'price' => $request['price'],
                'is_completed' => $request['is_completed'],
                'is_cancelled' => $request['is_cancelled'],
                'exchange_location_address' => $request['exchange_location_address'],
                'exchange_location_lng' => $request['exchange_location_lng'],
                'exchange_location_lat' => $request['exchange_location_lat'],
                'create_by'=> auth()->id
            ]);
            return response()->json([
                'Message' => 'Annonce creer avec success',
                'data' => $announcement
            ],201);
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
            if ($user->id !== $announcement->user_id) {
                return response()->json([
                    'Message' => "Vous n'avez pas le droit de modifier cette annonce"
                ], 403);
            } else {
                $request->validate([
                    'title' => 'required|string|min:5|max:500',
                    'descirption' => 'required|string|max:1000',
                    'operation_type' => 'required|string|in:don,sale,exchange',
                    'price' => 'nullable|numeric',
                    'is_completed' => 'nullable|boolean',
                    'is_cancelled' => 'nullable|boolean',
                    'exchange_location_address' => 'string|max:255',
                    'exchange_location_lng' => 'numeric',
                    'exchange_location_lat' => 'numeric',
                    'category_id' => 'required|exists:category,id'
                ]);

                $announcement->update([
                    'title' => $request['title'],
                    'description' => $request['description'],
                    'operation_type' => $request['operation_type'],
                    'price' => $request['price'],
                    'is_completed' => $request['is_completed'],
                    'is_cancelled' => $request['is_cancelled'],
                    'exchange_location_address' => $request['exchange_location_address'],
                    'exchange_location_lng' => $request['exchange_location_lng'],
                    'exchange_location_lat' => $request['exchange_location_lat'],
                    'category_id' => $request['category_id'],
                ]);

                return response()->json([
                    'Message' => "L'annonce a été mise à jour avec success",
                    'data' => $announcement
                ], 200);
            }
        } catch (\Exception $exception) {

            return response()->json([
                'Message' => "Une erreur est survenue lors de la mise à jour de l'annonce ",
                'Erreur' => $exception->getMessage()
            ]);
        }
    }

    //funnction pour supprimer une annonce
    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $user = Auth::user();

        try {

            if ($user->id !== $announcement->user_id) {
                return response()->json([
                    'Message' => "Vous n'avez pas le droit de supprimer cette annonce",
                ], 403);
            } else {
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
}
