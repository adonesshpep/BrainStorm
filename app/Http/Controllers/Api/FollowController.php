<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function follow(Request $request, $id)
    {
        $user = User::findOrFail($id);
        //user trying to follow themself
        if ($request->user()->id == $id) {
            abort(404, 'you cannot follow yourself');
        }
        Log::error('jdsfh');
        //cecking if the user has already followed that one
        if (!$request->user()->following()->where('following_id', $id)->exists()) {
            $request->user()->following()->attach($user);
            return response()->json(['message' => 'Followed successfully']);
        } else {
            return response()->json(['message' => 'you cannot follow the same person twice'], 400);
        }
    }
    public function unfollow(Request $request, $id)
    {
        $user = User::findOrFail($id);
        //checking if the user you are trying to unfollow you have actully followed once
        if ($request->user()->following()->where('following_id', $id)->exists()) {
            $request->user()->following()->detach($user);
            return response()->json(['message' => 'Unfollowed successfully']);
        } else {
            return response()->json(['message' => 'you cannot unfollow a person you do not follow'], 400);
        }
    }
    public function myFollowers(Request $request)
    {
        return UserResource::collection($request->user()->followers);
    }
    public function myFollowings(Request $request){
        return UserResource::collection($request->user()->following);
    }
}
