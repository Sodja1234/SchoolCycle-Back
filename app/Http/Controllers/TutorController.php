<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tutor;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\TutorResource;

class TutorController extends Controller
{
    //
     // Affiche la liste de tous les tuteurs
    public function index()
    {
        $tutors = Tutor::all();
        return TutorResource::collection($tutors);
    }

    // Affiche un tuteur spécifique par son identifiant
    public function show(Request $request)
    {
        try {
             $user = $request->user();

        $tutor = Tutor::where("user_id", $user->id)->first();   
        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found'], 404);
        }
        $tutorData = new TutorResource($tutor);
        return response()->json(['data'=> $tutorData], 200);
        } catch (\Exception $th) {
            return response()->json(['erros' => $th->getMessage()], 500);
        }

       
    }

    // Crée un nouveau tuteur
   public function store(Request $request)
    {
         $user = auth()->user();

        if ($user->role !== "tutor") {
            return response()->json(["message" => "Vous n'êtes pas tuteur"], 403);
        }

        $validator = Validator::make($request->all(), [
            'telephone' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = $user->id;

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $tutor = Tutor::updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return new TutorResource($tutor);
}
// Met à jour les informations d'un tuteur existant
public function update(Request $request){
        $user = $request->user();
        // Vérifie si l'utilisateur est authentifié
        if (!$user){
            return response()->json(['message' => 'Utilisateur non authentifié'], 401);
        }
        // Recherche le tuteur lié à l'utilisateur
        $tutor = Tutor::where('user_id', $user->id)->first();
        if (!$tutor) {
            return response()->json(['message' => 'Tuteur non trouvé'], 404);
        }
        // Valide les données reçues
        $validator = Validator::make($request->all(), [
            'telephone' => 'string|max:25',
            'profession' => 'string|max:255',
            'adresse' => 'string|max:255',
            'bio' => 'string|max:1000',
            'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);
        // Retourne les erreurs de validation si elles existent
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }
        $validated = $validator->validated();
        $hasChanges = false;
        
        // Gère la mise à jour de l'avatar si un nouveau fichier est envoyé
        if($request->hasFile('avatar')){
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            if($tutor->avatar !== $avatarPath){
                $tutor->avatar = $avatarPath;
                $hasChanges = true;
            }
        }
        // Met à jour la profession si elle a changé
        if (isset($validated['profession']) && $validated['profession'] != $tutor->profession) {
            $tutor->profession = $validated['profession'];
            $hasChanges = true;
        }
        // Met à jour le téléphone si il a changé
        if (isset($validated['telephone']) && $validated['telephone'] != $tutor->telephone ) {
            $tutor->telephone = $validated['telephone'];
            $hasChanges = true;
        }
        // Met à jour l'adresse si elle a changé
        if (isset($validated['adresse']) && $validated['adresse'] != $tutor->adresse ) {
            $tutor->adresse = $validated['adresse'];
            $hasChanges = true;
        }
        // Met à jour la bio si elle a changé
        if (isset($validated['bio']) && $validated['bio'] != $tutor->bio) {
            $tutor->bio = $validated['bio'];
            $hasChanges = true;

        }
        // Si aucune donnée n'a changé, retourne un message
        if (!$hasChanges) {
            return response()->json(['message' => 'Aucune donnée modifiée', 'data'=>new TutorResource($tutor)], 200);
        }
        // Sauvegarde les modifications
        $tutor->save();
        // $tutor->fill();
        $tutor->refresh();
        return response()->json(['message' => 'Tuteur modifié avec succès', 'data'=>new TutorResource($tutor)], 200);
    }
        

    // Supprime un tuteur par son identifiant
    public function destroy(Request $request)
    {

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        $tutor = Tutor::where("user_id", $user->id)->first();   
        if (!$tutor) {
            return response()->json(['message' => 'Tutor not found'], 404);
        }

        // Supprime le tuteur
        $tutor->delete();
       return response()->json(['message' => 'success'], 200); 

}

}