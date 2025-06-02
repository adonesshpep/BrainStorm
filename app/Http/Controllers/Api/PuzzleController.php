<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\DeepseekController;
use App\Http\Resources\PuzzleResource;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Community;
use App\Models\Puzzle;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PuzzleController extends Controller
{
    public function index(Request $request){
        $userId = $request->user()->id; // Get authenticated user ID
        $isNewUser = Answer::where('user_id', $userId)->count() === 0;
        if($isNewUser){
            $trendingPuzzles = Puzzle::whereNull('community_id')->orderByDesc(DB::raw('votes_up - votes_down'))->take(10)->get();
            $newPuzzles = Puzzle::whereNull('community_id')->where('created_at', '>=', now()->subDays(7))->take(10)->get();
            $finalFeed=$trendingPuzzles->merge($newPuzzles)->shuffle();
        }else{
        $topCategories = DB::table('user_answers_categories')
            ->where('user_id', $userId)
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as category_count')
            ->orderByDesc('category_count')
            ->limit(3)
            ->pluck('category_id'); // Get only the IDs
            $favoriteCategories = DB::table('star_categories')
                ->where('user_id', $userId)
                ->pluck('category_id'); // Get only category IDs
            $categories = array_merge($topCategories->toArray(), $favoriteCategories->toArray());
            $trendingPuzzles = Puzzle::whereNull('community_id')->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc(DB::raw('votes_up - votes_down'))
            ->take(5)
            ->get();
        $recommendedPuzzles = Puzzle::whereNull('community_id')->whereIn('category_id', $categories)
            ->selectRaw('*, (votes_up - votes_down) / (1 + TIMESTAMPDIFF(DAY, created_at, NOW()) / 7) as score')
            ->orderByDesc('score')
            ->take(15)
            ->get();
            $finalFeed = $recommendedPuzzles->merge($trendingPuzzles)->shuffle();
            }
        return PuzzleResource::collection($finalFeed);
    }
    public function getCommunityPuzzle(Request $request,$id){
        $community=Community::findOrFail($id);
        if($request->user()->communities()->where('community_id',$community->id)->exists()){
        $communityPuzzles = Puzzle::where('community_id', $id)
            ->selectRaw('*, (votes_up - votes_down) / (1 + TIMESTAMPDIFF(DAY, created_at, NOW()) / 7) as score')
            ->orderByDesc('score')
            ->take(20)
            ->get();
            return PuzzleResource::collection($communityPuzzles);
            }else{
                return response()->json(['message'=>'you are not in this community']);
            }
    }
    public function show(Puzzle $puzzle){
        return PuzzleResource::make($puzzle);
    }
    public function store(Request $request){
        $atts=$request->validate([
            'level'=>'required',
            'title'=>'required',
            'question'=>'required',
            'category_id'=>'required'
        ]);
        $puzzle = $request->user()->puzzles()->create($atts);
        return response()->json([
            'message' => 'created',
            'puzzle' => PuzzleResource::make($puzzle)
        ]);
    }
    public function destroy(Puzzle $puzzle){
        $this->authorize('modify',$puzzle);
        $puzzle->delete();
        return response()->json([
            'message'=>'deleted'
        ]);
    }
    public function arrowMoidify(Request $request){
        $request->validate([
            'vote'=>'required|boolean',
            'puzzle_id'=>'required'
        ]);
        $puzzle=Puzzle::findOrFail($request->puzzle_id);
        $user=$request->user();
        Log::error($puzzle);
        $existingVote=Vote::where('puzzle_id',$puzzle->id)
        ->where('user_id',$user->id)->first();
        //if user is voting again
        if($existingVote){
            //if user is clicking the same vote button to cancel it
            if($existingVote->vote==$request->vote){
                $existingVote->delete();
                $request->vote === 1 ? $puzzle->decrement('votes_up') : $puzzle->decrement('votes_down');
                return response()->json(['message' => 'previous action cancled', 'puzzle' => $puzzle]);
            }else{
                //if user is switching vote
                $existingVote->update(['vote'=>$request->vote]);
                if($request->vote === 1){ $puzzle->increment('votes_up'); $puzzle->decrement('votes_down');}else{$puzzle->increment('votes_down');$puzzle->decrement('votes_up');}
                return response()->json(['message' => 'your vote has switched', 'puzzle' => $puzzle]);
            }
        }
        //if uesr is voting for the first time
        else{
            Vote::create([
                'user_id'=>$user->id,
                'puzzle_id'=>$puzzle->id,
                'vote'=>$request->vote
            ]);
            $request->vote===1?$puzzle->increment('votes_up'):$puzzle->increment('votes_down');
            return response()->json(['message' => 'your vote has been created', 'puzzle' => $puzzle]);
        }
        
    }
    // public function getVotes($id){
    //     $puzzle=Puzzle::findOrFail($id);
    //     return response()->json(['votes_up'=>$puzzle->votes_up,'votes_down'=>$puzzle->votes_down]);
    // }

    // public function update(Request $request,Puzzle $puzzle){
    //     Gate::authorize('modify', $puzzle);
    //     $atts = $request->validate([
    //         'title' => 'required',
    //         'question' => 'required',
    //     ]);
    //     $puzzle->update($atts);
    //     return response()->json([
    //         'message'=>'updated'
    //     ]);
    // }
}
