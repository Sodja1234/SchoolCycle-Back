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
     * Display a listing of the resource.
     */
    public function index()
    {
    $categoriesAll = Category::all();
    $categories = CategoryResource::collection($categoriesAll);
    return response()->json(['data' => $categories]);
    }


    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show($id)
    {
        // $category= Category:: findOrFail($id);
        // return new CategoryResource($category);
        try{
            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    "message" => "La catégorie n'existe pas"
                ]);
            }
            $announcements = $category->announcements()->where('is_completed', false)->where('is_cancelled', false)->orderBy('created_at', 'desc')->get();
            return AnnouncementResource::collection($announcements);
        }catch(\Exception $e){
            return response()->json([
                "message" => "Une erreur est survenue",
                "error" => $e->getMessage()
            ]);
        }

    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
            $request->validate([
            'name'=>'required|string|max:500',
            'description'=>'required|string|max:600'
        ]);
        $category->update([
            'name'=>$request['name'],
            'description'=>$request['description']
        ]);
        return new CategoryResource($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Category $category)
    {

        $category->delete();
        return response()->json([

            'Message' => "[]"
        ]);
    }
}
