<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnswerResource;
use App\Models\Answer;
use App\Models\Interaction;
use App\Models\Puzzle;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function showMine(Request $request)
    {
        return AnswerResource::collection(Answer::where('user_id', $request->user()->id)->get());
    }
    public function store(Request $request, Answer $answer)
    {
        $atts = $request->validate([
            'puzzle_id' => 'required',
            'answer' => 'nullable',
            'solution_id' => 'required',
            'iscorrect' => 'required|boolean'
        ]);
        $puzzle = Puzzle::where('id', $request->puzzle_id)->first();
        if (!$puzzle) {
            abort(404);
        }
        $user = $request->user();
        $answer = $user->answers()->create($atts);
        // if ($puzzle->category_id) {
        //         $request->user()->interactions()->create(['puzzle_id'=>$puzzle->id,'category_id'=>$puzzle->category_id,'solved'=>$answer->iscorrect]);
        // }
        if ($puzzle->status == 1) {
            if ($answer->iscorrect == 1) {
                switch ($puzzle->level) {
                    case 'hard':
                        $user->overall_score += 15;
                        $user->save();
                        break;
                    case 'med':
                        $user->overall_score += 10;
                        $user->save();
                        break;
                    case 'low':
                        $user->overall_score += 5;
                        $user->save();
                        break;
                }
            } else {
                switch ($puzzle->level) {
                    case 'hard':
                        $user->overall_score -= 10;
                        $user->save();
                        break;
                    case 'med':
                        $user->overall_score -= 5;
                        $user->save();
                        break;
                    case 'low':
                        $user->overall_score -= 2;
                        $user->save();
                        break;
                }
            }
        }
        return AnswerResource::make($answer);
    }
}
