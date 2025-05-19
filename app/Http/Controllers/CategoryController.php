<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Category::all();
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:500',
            'description'=>'required|string|max:600'
        ]);
        $category=Category::create([
            'name'=>$request['name'],
            'description'=>$request['description']
        ]);
        
        return response()->json([
            'Message'=>"Category creer avec success",
            'data'=>$category
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category= Category:: findOrFail($id);
        return response()->json([
            'data' => $category
        ]);

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
        return response()->json([
            'Message'=>'Category mise à Jour'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json([

            'Message' => "[]"
        ]);
    }
}
