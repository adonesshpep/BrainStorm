<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolutionResource;
use App\Models\Puzzle;
use App\Models\Solution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SolutionController extends Controller
{
    public function store(Request $request){
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
        return SolutionResource::make($solution);
    }
    public function showForPuzzle($id){
        $puzzle=Puzzle::find($id);
        return SolutionResource::collection($puzzle->solutions);
    }
}
