<?php

namespace App\Http\Controllers;

use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
        /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"Category"},
     *     summary="Liste des categories disponibles",
     *     @OA\Response(
     *         response=200,
     *         description="Liste récupérée avec succès",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Category")
     *         )
     *     )
     * )
     */
    public function index()
    {
    $categoriesAll = Category::all();
    $categories = CategoryResource::collection($categoriesAll);
    return response()->json(['data' => $categories]);
    }

    /**
 * @OA\Post(
 *     path="/api/categories",
 *     summary="Créer une nouvelle catégorie",
 *     description="Seul un administrateur peut créer une nouvelle catégorie. L'utilisateur doit envoyer les champs obligatoires, dont une image.",
 *     operationId="storeCategory",
 *     tags={"Category"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"name", "description", "photo"},
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     maxLength=500,
 *                     description="Nom de la catégorie"
 *                 ),
 *                 @OA\Property(
 *                     property="description",
 *                     type="string",
 *                     maxLength=600,
 *                     description="Description de la catégorie"
 *                 ),
 *                 @OA\Property(
 *                     property="photo",
 *                     type="string",
 *                     format="binary",
 *                     description="Image de la catégorie (jpg, jpeg, png, gif, 2Mo max.)"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Catégorie créée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé: seul un administrateur peut effectuer cette action"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation (données manquantes ou image non reçue)"
 *     )
 * )
 */
    public function store(Request $request)
    {
        $user = Auth::user();
        if($user->role !=='admin'){
            return response()->json([
                "Message" => "Vous devez etre administrateur pour faire cette action"
            ]);
        }
        $request->validate([
            'name'=>'required|string|max:500',
            'description'=>'required|string|max:600',
            'photo' => 'required|mimes:jpg,jpeg,png,gif|max:2048'
        ]);
            if ($request->hasFile('photo')) {
        $filename = time() . '_' . $request->file('photo')->getClientOriginalName();
        $path = $request->file('photo')->storeAs('categories', $filename, 'public');
    } else {
        return response()->json(['error' => 'Aucune image reçue'], 422);
    }
        $category=Category::create([
            'name'=>$request['name'],
            'description'=>$request['description'],
            'photo'=>$path
        ]);

        return new CategoryResource($category);
    }
/**
 * @OA\Get(
 *     path="/api/categories/{id}",
 *     tags={"Category"},
 *     summary="Afficher une catégorie spécifique",
 *     description="Récupère les détails d'une catégorie en fonction de son ID.",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID de la catégorie",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Catégorie récupérée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Catégorie non trouvée",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="La catégorie n'existe pas")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Une erreur est survenue"),
 *             @OA\Property(property="error", type="string", example="Exception message")
 *         )
 *     )
 * )
 */

    public function show($id)
    {
        try{
            $category = Category::findOrFail($id);
            if (!$category) {
                return response()->json([
                    "message" => "La catégorie n'existe pas"
                ]);
            }
            //$announcements = $category->announcements()->where('is_completed', false)->where('is_cancelled', false)->orderBy('created_at', 'desc')->get();
            return CategoryResource::collection($category);
        }catch(\Exception $e){
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ]);
        }

    }


/**
 * @OA\Put(
 *     path="/categories/{id}",
 *     summary="Met à jour une catégorie existante",
 *     description="Seul un administrateur peut mettre à jour une catégorie.",
 *     operationId="updateCategory",
 *     tags={"Category"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de la catégorie à mettre à jour",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "description"},
 *             @OA\Property(property="name", type="string", maxLength=500, example="Nouvel intitulé"),
 *             @OA\Property(property="description", type="string", maxLength=600, example="Nouvelle description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Catégorie mise à jour avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/Category")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé: seul un administrateur peut effectuer cette action"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation des données échouée"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Catégorie non trouvée"
 *     )
 * )
 */
    public function update(Request $request, Category $category)
    {
        try{
        $user = Auth::user();
        if($user->role !=='admin'){
            return response()->json([
                "Message" => "Vous devez etre administrateur pour faire cette action"
            ]);
        }else{
            $validated = $request->validate([
                'name'=>'required|string|max:500',
                'description'=>'required|string|max:600'
            ]);
            $category->update($validated);
            return new CategoryResource($category);
        }
        }catch(\Exception $e){
            return response()->json([
                "Message"=> $e
            ]);
        }
    }

    /**
 * @OA\Delete(
 *     path="/categories/{id}",
 *     summary="Supprime une catégorie existante",
 *     description="Supprimer une catégorie par son ID. Seul un administrateur devrait pouvoir effectuer cette action (à gérer dans le contrôleur).",
 *     operationId="deleteCategory",
 *     tags={"Category"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID de la catégorie à supprimer",
 *         required=true,
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Catégorie supprimée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="Message",
 *                 type="string",
 *                 example="[]"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé: accès refusé"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Catégorie non trouvée"
 *     )
 * )
 */
    public function destroy( Category $category)
    {

        $category->delete();
        return response()->json([

            'Message' => "[]"
        ]);
    }

    public function getMostUsedCategories()
    {
        $categories_count = Category::withCount('announcements')->orderBy('announcements_count', 'desc')->limit(6)->get();
        $categories = CategoryResource::collection($categories_count);
        return response()->json(['data' => $categories]);
    }
}
