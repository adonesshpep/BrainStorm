<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PuzzleResource;
use App\Models\Category;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PuzzleController extends Controller
{
    public function index(){
        return PuzzleResource::collection(Puzzle::paginate(20));
    }
    public function show(Puzzle $puzzle){
        return PuzzleResource::make($puzzle);
    }
    public function store(Request $request){
        $atts=$request->validate([
            'title'=>'required',
            'question'=>'required',
            'category_id'=>'required'
        ]);
        $puzzle=$request->user()->puzzles()->create($atts);
        return response()->json([
            'message'=>'created',
            'puzzle'=>PuzzleResource::make($puzzle)
        ]);
    }
    public function destroy(Puzzle $puzzle){
        Gate::authorize('modify',$puzzle);
        $puzzle->delete();
        return response()->json([
            'message'=>'deleted'
        ]);
    }
    public function update(Request $request,Puzzle $puzzle){
        Gate::authorize('modify', $puzzle);
        $atts = $request->validate([
            'title' => 'required',
            'question' => 'required',
        ]);
        $puzzle->update($atts);
        return response()->json([
            'message'=>'updated'
        ]);
    }
}
