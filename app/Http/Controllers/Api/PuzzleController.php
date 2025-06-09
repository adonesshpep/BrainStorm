<?php

namespace App\Http\Controllers\Api;

use App\Events\NewPuzzleCreated;
use App\Events\PuzzleGetVoted;
use App\Events\PuzzlePendingEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\PuzzleResource;
use App\Http\Resources\SolutionResource;
use App\Models\Answer;
use App\Models\Community;
use App\Models\Puzzle;
use App\Models\Solution;
use App\Models\Vote;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PuzzleController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $isNewUser = Answer::where('user_id', $userId)->count() === 0;
        if ($isNewUser) {
            $trendingPuzzles = Puzzle::whereNull('community_id')->orderByDesc(DB::raw('votes_up - votes_down'))->take(10)->get();
            $newPuzzles = Puzzle::whereNull('community_id')->where('created_at', '>=', now()->subDays(7))->take(10)->get();
            $finalFeed = $trendingPuzzles->merge($newPuzzles)->unique()->shuffle();
        } else {
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
            $categories = array_unique(array_merge($topCategories->toArray(), $favoriteCategories->toArray()));
            $answered = Answer::where('user_id', $userId)->pluck('puzzle_id');
            $trendingPuzzles = Puzzle::whereNull('community_id')->where('created_at', '>=', now()->subDays(7))->whereNotIn('id', $answered)
                ->orderByDesc(DB::raw('votes_up - votes_down'))
                ->take(5)
                ->get();
            $recommendedPuzzles = Puzzle::whereNull('community_id')->whereIn('category_id', $categories)->whereNotIn('id', $answered)
                ->selectRaw('*, (votes_up - votes_down) / (1 + TIMESTAMPDIFF(DAY, created_at, NOW()) / 7) as score')
                ->orderByDesc('score')
                ->take(15)
                ->get();
            $finalFeed = $recommendedPuzzles->merge($trendingPuzzles)->unique()->shuffle();
        }
        return PuzzleResource::collection($finalFeed);
    }
    public function getCommunityPuzzles(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        if ($request->user()->communities()->where('community_id', $community->id)->exists()) {
            $communityPuzzles = Puzzle::where('community_id', $id)
                ->selectRaw('*, (votes_up - votes_down + 1) / (1 + TIMESTAMPDIFF(HOUR,created_at, NOW()) / 7) as score')
                ->orderByDesc('score')
                ->take(20)
                ->get();
            return PuzzleResource::collection($communityPuzzles);
        } else {
            return response()->json(['message' => 'You are not in this community'],403);
        }
    }
    public function storeToCommunity(Request $request)
    {
        $request->merge([
            'iscorrect' => array_map(fn($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN), $request->input('iscorrect'))
        ]);
        $atts = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'level' => 'required|integer|in:0,1,2',
            'category_id' => 'nullable|exists:categories,id',
            'community_id' => 'required|exists:communities,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string',
            'iscorrect' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if (count($value) !== count($request->input('values'))) {
                        $fail('The iscorrect array must have the same number of elements as values.');
                    }
                    if (!in_array(true, $value, true)) {
                        $fail('At least one solution must be correct.');
                    }
                }
            ],
            'iscorrect.*' => 'required|boolean'
        ]);
        $community = Community::findOrFail($atts['community_id']);
        if (!$request->user()->communities()->where('community_id', $community->id)->exists()) {
            return response()->json(['message' => 'Sorry,you are not in this community'], 403);
        }
        if ($community->only_admin_can_post && $request->user()->id !== $community->admin_id) {
            return response()->json(['message' => 'Sorry,only admin can post here'], 400);
        }
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('puzzles', 'public');
            $atts['image_path'] = $imagePath;
        } else {
            $atts['image_path'] = null;
        }
        if (isset($atts['category_id'])) {
            if (!$community->categories()->where('category_id', $atts['category_id'])->exists()) {
                return response()->json(['message' => 'the category specified for this puzzle is not included as a category for this community'], 400);
            }
        } else {
            $atts['category_id'] = 1;
        }
        $solutions = [];
        $response = DB::transaction(
            function () use ($community, $atts, $request, &$solutions) {
                $puzzle = $community->puzzles()->create([
                    'user_id' => $request->user()->id,
                    'category_id' => $atts['category_id'],
                    'level' => $atts['level'],
                    'question' => $atts['question'],
                    'title' => $atts['title'],
                    'image_path' => $atts['image_path'],
                    'status' => !($community->puzzles_require_admin_approval && $request->user()->id !== $community->admin_id)
                ]);
                foreach ($atts['values'] as $index => $value) {
                    $solutions[] = Solution::create([
                        'puzzle_id' => $puzzle->id,
                        'value' => $value,
                        'iscorrect' => $atts['iscorrect'][$index]
                    ]);
                }
                if ($puzzle->status) {
                    $message = 'your puzzle has been uploaded';
                } else {
                    $message = 'your puzzle has been uploaded,wait for admin approval';
                    PuzzlePendingEvent::dispatch($puzzle);
                }
                return ['message' => $message, 'puzzle' => PuzzleResource::make($puzzle), 'solutions' => $solutions];
            }
        );
        return response()->json($response);
    }
    public function show(Request $request,$id)
    {
        $puzzle=Puzzle::findOrFail($id);
        if ($puzzle->community_id) {
            if (!$request->user()->communities()->where('community_id', $puzzle->community_id)->exists()) {
                return response()->json([
                    'message' => 'You cannot see this puzzle , You are not in the community'
                ], 403);
            }
        }
        //the idea here if there is no solution with iscorrect==false then the type of this puzzle is input field and we dont need to return the solutions with the puzzle otherwize its options type and we need to return the solution
        $solutions = Solution::where('puzzle_id', $puzzle->id)->get();
        $isOptionsType = $solutions->contains(fn($solution) => !$solution->iscorrect);

        if (!$isOptionsType) {
            return PuzzleResource::make($puzzle);
        } else {
            return response()->json(['puzzle' => PuzzleResource::make($puzzle), 'solutions' => SolutionResource::collection($solutions)]);
        }
    }
    public function store(Request $request)
    {
        $request->merge([
            'iscorrect' => array_map(fn($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN), $request->input('iscorrect'))
        ]);
        $atts = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'level' => 'required|integer|in:0,1,2',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'community_id' => 'nullable|exists:communities,id',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string',
            'iscorrect' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if (count($value) !== count($request->input('values'))) {
                        $fail('The iscorrect array must have the same number of elements as values.');
                    }
                    if (!in_array(true, $value, true)) {
                        $fail('At least one solution must be correct.');
                    }
                }
            ],
            'iscorrect.*' => 'required|boolean'
        ]);

        if (isset($atts['community_id'])) {
            return response()->json([
                'message' => 'community puzzles handled in another function'
            ], 400);
        }
        if (!isset($atts['category_id'])) {
            $atts['category_id'] = 1;
        }
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('puzzles', 'public');
            $atts['image_path'] = $imagePath;
        }
        $puzzle = $request->user()->puzzles()->create($atts);
        $solutions = [];
        DB::transaction(function () use ($atts, $puzzle, &$solutions) {
            foreach ($atts['values'] as $index => $value) {
                $solutions[] = Solution::create([
                    'puzzle_id' => $puzzle->id,
                    'value' => $value,
                    'iscorrect' => $atts['iscorrect'][$index]
                ]);
            }
        });
        NewPuzzleCreated::dispatch($puzzle->id, $solutions);
        //Note : the status in the feed puzzles refer to ai validation and in the community puzzles refer to admin approval
        return response()->json([
            'message' => 'created',
            'puzzle' => PuzzleResource::make($puzzle),
            'solutions' => SolutionResource::collection($solutions)
        ]);
    }
    public function destroy($id)
    {
        $puzzle=Puzzle::findOrFail($id);
        $this->authorize('modify', $puzzle);
        if ($puzzle->image_path) {
            Storage::disk('public')->delete($puzzle->image_path);
        } else {
            Log::error('File not found:', ['path' => $puzzle->image_path]);
        }
        $puzzle->delete();
        return response()->json([
            'message' => 'deleted'
        ]);
    }
    public function arrowMoidify(Request $request)
    {
        $request->validate([
            'vote' => 'required|boolean',
            'puzzle_id' => 'required'
        ]);
        $puzzle = Puzzle::findOrFail($request->puzzle_id);
        $user = $request->user();
        $existingVote = Vote::where('puzzle_id', $puzzle->id)
            ->where('user_id', $user->id)->first();
        //if user is voting again
        if ($existingVote) {
            //if user is clicking the same vote button to cancel it
            if ($existingVote->vote == $request->vote) {
                $existingVote->delete();
                $request->vote === 1 ? $puzzle->decrement('votes_up') : $puzzle->decrement('votes_down');
                return response()->json(['message' => 'previous action cancled', 'puzzle' => $puzzle]);
            } else {
                //if user is switching vote
                $existingVote->update(['vote' => $request->vote]);
                if ($request->vote === 1) {
                    $puzzle->increment('votes_up');
                    $puzzle->decrement('votes_down');
                } else {
                    $puzzle->increment('votes_down');
                    $puzzle->decrement('votes_up');
                }
                return response()->json(['message' => 'your vote has switched', 'puzzle' => $puzzle]);
            }
        }
        //if uesr is voting for the first time
        else {
            Vote::create([
                'user_id' => $user->id,
                'puzzle_id' => $puzzle->id,
                'vote' => $request->vote
            ]);
            $request->vote === 1 ? $puzzle->increment('votes_up') : $puzzle->increment('votes_down');
            PuzzleGetVoted::dispatch($puzzle, $request->user(), $request->vote);
            return response()->json(['message' => 'your vote has been created', 'puzzle' => $puzzle]);
        }
    }
    public function getCommunityPendingPuzzles(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $this->authorize('manage', $community);
        if ($community->only_admin_can_post || !$community->puzzles_require_admin_approval) {
            return response()->json(['message' => 'this type of community does not have pending puzzles'], 204);
        }
        $puzzles = $community->puzzles()->where('status', false)->get();
        return PuzzleResource::collection($puzzles);
    }
    public function handlePendingPuzzles(Request $request, $id)
    {
        $atts = $request->validate([
            'response' => 'required|boolean'
        ]);
        $puzzle = Puzzle::findOrFail($id);
        if (!$puzzle->community_id) {
            return response()->json(['message' => 'This puzzle is not associated with a community'], 400);
        }
        $community = Community::findOrFail($puzzle->community_id);
        $this->authorize('manage', $community);
        if($atts['response']){
            $puzzle->status=1;
            $puzzle->save();
            return response()->json(['message'=>'puzzle was accepted']);
        }else{
            $puzzle->delete();
            return response()->json(['message'=>'puzzle was denied']);
        }
    }
    public function getVotes($id)
    {
        $puzzle = Puzzle::findOrFail($id);
        return response()->json(['votes_up' => $puzzle->votes_up, 'votes_down' => $puzzle->votes_down]);
    }

    public function update(Request $request, $id)
    {
        $puzzle = Puzzle::findOrFail($id);
        $this->authorize('modify', $puzzle);
        Log::error($request);
        $request->merge([
            'iscorrect' => array_map(fn($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN), $request->input('iscorrect'))
        ]);
        $atts = $request->validate([
            'question' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'level' => 'required|integer|in:0,1,2',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'values' => 'required|array|min:1',
            'values.*' => 'required|string',
            'iscorrect' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    if (count($value) !== count($request->input('values'))) {
                        $fail('The iscorrect array must have the same number of elements as values.');
                    }
                    if (!in_array(true, $value, true)) {
                        $fail('At least one solution must be correct.');
                    }
                }
            ],
            'iscorrect.*' => 'required|boolean'
        ]);
        if ($puzzle->community_id) {
            $community = Community::findOrFail($puzzle->community_id);
            if (!$request->user()->communities()->where('community_id', $community->id)->exists()) {
                return response()->json([
                    'message' => 'your are not in this community'
                ], 403);
            }
            if ($community->only_admin_can_post && $request->user()->id !== $community->admin_id) {
                return response()->json(['message' => 'Sorry,only admin can post here'], 400);
            }
            if (isset($atts['category_id'])) {
                if (!$community->categories()->where('category_id', $atts['category_id'])->exists()) {
                    return response()->json(['message' => 'the category specified for this puzzle is not included as a category for this community'], 400);
                }
            }
        }
        try {
            if ($request->hasFile('image')) {
                if ($puzzle->image_path) {
                    Storage::disk('public')->delete($puzzle->image_path);
                }
                $imagePath = $request->file('image')->store('puzzles', 'public');
                $atts['image_path'] = $imagePath;
            }else{
                if ($puzzle->image_path) {
                    Storage::disk('public')->delete($puzzle->image_path);
                    $puzzle->image_path=null;
                }
            }
            $solutions = [];
            DB::transaction(function () use ($atts, $puzzle, &$solutions) {
                $existingSolutions = Solution::where('puzzle_id', $puzzle->id)->get();
                foreach ($atts['values'] as $index => $value) {
                    if (isset($existingSolutions[$index])) {
                        $existingSolutions[$index]->update(['value' => $value, 'iscorrect' => $atts['iscorrect'][$index]]);
                        $solutions[] = $existingSolutions[$index];
                    } else {
                        $solutions[] = Solution::create([
                            'puzzle_id' => $puzzle->id,
                            'value' => $value,
                            'iscorrect' => $atts['iscorrect'][$index]
                        ]);
                    }
                }
            });
            $puzzle->update([
                'question' => $atts['question'],
                'title' => $atts['title'],
                'level' => $atts['level'],
                'category_id' =>isset($atts['category_id'])?$atts['category_id']:$puzzle->category_id??1,
                'community_id' => $puzzle->community_id ?? null,
                'image_path' => $atts['image_path'] ?? $puzzle->image_path ?? null,
                'votes_up' => 0,
                'votes_down' => 0,
            ]);
            if ($puzzle->community_id) {
                if ($community->puzzles_require_admin_approval && $request->user()->id !== $community->admin_id) {
                    $puzzle->status = 0;
                    $puzzle->save();
                    PuzzlePendingEvent::dispatch($puzzle);
                }
            } else {
                //because community puzzles do not need ai validation
                NewPuzzleCreated::dispatch($puzzle->id, $solutions);
            }
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json([
            'message' => 'updated',
            'puzzle' => PuzzleResource::make($puzzle),
            'solutions' => SolutionResource::collection($solutions)
        ]);
    }
}
