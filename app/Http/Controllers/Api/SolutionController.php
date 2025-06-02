<?php

namespace App\Http\Controllers\Api;

use App\Events\NewPuzzleCreated;
use App\Http\Controllers\Controller;
use App\Http\Resources\SolutionResource;
use App\Models\Puzzle;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    public function store(Request $request){
        Log::error("hello");
        $atts=$request->validate([
            'value'=>'required|max:25',
            'iscorrect'=>'required',
            'puzzle_id'=>'required'
        ]);
        $puzzle=Puzzle::find($request->puzzle_id);
        //Gate::authorize('add',$puzzle);  i didn't get it
        Gate::authorize('modify',$puzzle);
        // $request->user()->puzzles()->first()->solutions()->crreate($atts);
        $solution=Solution::create($atts);
        if($solution->iscorrect==1){
            //this is here because the creation of the solution is isolated form the creation of the puzzle
            NewPuzzleCreated::dispatch($puzzle->id,$solution->id);
        }
        return SolutionResource::make($solution);
    }
    public function showForPuzzle($id){
        $puzzle=Puzzle::find($id);
        return SolutionResource::collection($puzzle->solutions);
    }
}
