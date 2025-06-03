<?php

namespace App\Http\Controllers\Api;

use App\Events\PuzzlePlayed;
use App\Http\Controllers\Controller;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Interaction;
use App\Models\Puzzle;
use App\Models\Solution;
use App\Models\User;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function showMine(Request $request)
    {
        return AnswerResource::collection(Answer::where('user_id', $request->user()->id)->get());
    }
    public function store(Request $request, $id)
    {
        $atts = $request->validate([
            'puzzle_id' => 'required',
            'answer' => 'nullable|string|max:255|required_without:solution_id|prohibited_with:solution_id',
            'solution_id' => 'nullable|exists:solutions,id|required_without:answer|prohibited_with:answer',
        ]);
        $puzzle = Puzzle::findOrFail($id);
        if($puzzle->answers()->where('user_id',$request->user()->id)->exists()){
            return response()->json([
                'message'=>'you have already submitted an answer to this puzzle'
            ]);
        }
        if($atts['solution_id']){
            $isCorrect=Solution::where('id',$atts['solution_id'])->value('iscorrect');
        }
        if($atts['answer']){
            $correctSolutions=Solution::where('puzzle_id',$puzzle->id)->where('iscorrect',true)->pluck('value');
            //use similar_text
            $isCorrect=$correctSolutions->contains(fn($correctAnswer)=>levenshtein(strtolower(trim($correctAnswer)) , strtolower(trim($atts['answer'])))<=2|| (strtolower(trim($correctAnswer)) === strtolower(trim($atts['answer']))));
        }
        $atts['iscorrect']=$isCorrect;
        $user=$request->user();
        $answer = $user->answers()->create($atts);
        $creator=User::findOrfail($puzzle->user_id);
        PuzzlePlayed::dispatch($puzzle,$user,$creator,$answer->isccorect);
        //the same score logic below moved to listener (HandlePuzzlePlayed)

        // $scoreChanges = [
        //     'hard' => ['gain' => 15,'lose'=>10, 'creator' => 3],
        //     'med' => ['gain' => 10, 'lose' => 5, 'creator' => 2],
        //     'low' => ['gain' => 5, 'lose' => 2, 'creator' => 1],
        // ];
        // if(!$puzzle->community_id){
        //     if($puzzle->status){
        //         if($answer->iscorrect){
        //             $user->overall_score+=$scoreChanges[$puzzle->level]['gain'];
        //         }else{
        //             $user->overall_score -= $scoreChanges[$puzzle->level]['lose'];
        //             $creator->overall_score += $scoreChanges[$puzzle->level]['creator'];
        //         }
        //         $user->save();
        //         $creator->save();
        //     }
        // }
        return AnswerResource::make($answer);
    }
}
