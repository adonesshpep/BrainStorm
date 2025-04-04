<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Puzzle;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function showMine(Request $request){
        return AnswerResource::collection(Answer::where('user_id', $request->user()->id)->get());
    }
    public function store(Request $request, Answer $answer){
        $atts=$request->validate([
            'puzzle_id'=>'required',
            'answer'=>'nullable',
            'solution_id'=>'required',
            'iscorrect'=>'required|boolean'
        ]);
        $puzzle=Puzzle::where('id',$request->puzzle_id)->first();
        if(!$puzzle){
            abort(404);
        }
        $answer=$request->user()->answers()->create($atts);
        return AnswerResource::make($answer);
    }
}
