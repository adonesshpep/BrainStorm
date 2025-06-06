<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return CategoryResource::collection(Category::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if(!$category){
            return response()->json(['message'=>'category not found'], 404);
        }
        return new CategoryResource($category);
    }
    public function star(Request $request,$id){
        $category=Category::findOrFail($id);
        if(!$request->user()->staredCategories()->where('category_id',$id)->exists()){
            $request->user()->staredCategories()->attach($category);
            return response()->json(['message'=>'category stared']);
        }else{
            return response()->json(['message' => 'you cannot star the same category twice'],400);
        }
    }
    public function unstar(Request $request,$id){
        $category = Category::findOrFail($id);
        if($request->user()->staredCategories()->where('category_id', $id)->exists()){
            $request->user()->staredCategories()->detach($category);
            return response()->json(['message' => 'category unstared']);
        }else{
            return response()->json(['message' => 'you cannot unstar a category you didnt star'],400);
        }
    }
    public function store(Request $request){
        $atts=$request->validate([
            'name'=>'string|max:255|unique:categories,name',
            'avatar_id'=>'nullable|integer|min:1|max:2'
        ]);
        $category=Category::create($atts);
        return response()->json([
            'message'=>'category created',
            'category'=>CategoryResource::make($category)
        ]);
    }
    public function myStaredCategories(Request $request){
        return CategoryResource::collection($request->user()->staredCategories);
    }
}
