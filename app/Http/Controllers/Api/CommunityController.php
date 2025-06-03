<?php

namespace App\Http\Controllers;

use App\Events\JoinRequestSubmitted;
use App\Http\Resources\UserResource;
use App\Models\Community;
use App\Models\User;
use Hidehalo\Nanoid\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommunityController extends Controller
{
    public function join(Request $request,$id){
        $community=Community::findOrFail($id);
        if($request->user()->communities()->where('community_id',$community->id)->exists()){
            return response()->json(['message' => 'You are already in the community'], 400);
        }
        if($community->joining_requires_admin_approval){
            if($request->user()->joinRequests()->where('community_id',$community->id)->exists()){
                return response()->json(['message' => 'Sorry, you cannot try again until the admin takes action'], 400);
            }else{
                $request->user()->joinRequests()->attach($community);
                JoinRequestSubmitted::dispatch($request->user(),$community);
                return response()->json(['message'=>'your request is submitted'],200);
            }
        }else{
            $request->user()->communities()->attach($community);
            $community->size+=1;
            $community->save();
            return response()->json(['message'=> 'you are in the community'], 200);
        }
    }
    public function store(Request $request){
        $atts=$request->validate([
            'name'=>'required|string|max:255|unique:communities,name',
            'description'=>'nullable|string|max:1000',
            'private'=>'boolean',
            'joining_requires_admin_approval'=>'boolean',
            'puzzles_require_admin_approval'=>'boolean',
            'only_admin_can_post'=>'boolean'
        ]);
        $client=new Client();
        $nanoId=$client->generateId(12);
        $community=Community::create([
            'id'=>$nanoId,
            'name'=>$atts['name'],
            'description'=>$atts['description'] ?? '',
            'joining_requires_admin_approval'=>$atts['joining_requires_admin_approval'],
            'puzzles_require_admin_approval'=>$atts['puzzles_require_admin_approval'],
            'only_admin_can_post'=>$atts['only_admin_can_post'],
            'admin_id'=>$request->user()->id,
            'size'=>1
        ]);
        $community->users()->attach($request->user()->id);
        return response()->json(['message' => 'Community created successfully!', 'community' => $community], 201);
    }
    public function leave(Request $request,$id){
        $community=Community::findOrFail($id);
        if(!$request->user()->communities()->where('community_id',$community->id)->exists()){
            return response()->json(['message'=>'you are not in this community in the first place']);
        }else{
            $request->user()->communities()->detach($community);
            $community->size = max(1, $community->size - 1);
            $community->save();
            return response()->json(['message'=>'you left the community']);
        }
    }
    public function adminResponse(Request $request,$id){
        $atts=$request->validate([
            'requester_id'=>'required|exists:users,id',
            'response'=>'required|boolean'
            ]);
        $community=Community::findOrFail($id);
        $this->authorize('manage',$community);
        $joinRequest = $community->joinRequests()->where('user_id', $request->requester_id)->first();
        if (!$joinRequest) {
            return response()->json(['message' => 'User did not submit a request'], 400);
        }
        if(!$atts['response']){
            $joinRequest->detach();
            return response()->json(['message'=>'request was denied']);
        }else{
            $community->users()->attach($request->requester_id);
            $joinRequest->detach();
            return response()->json(['message'=>'request was approved']);
        }
    }
    public function destroy(Request $request,$id){
        $community=Community::finOrFail($id);
        $this->authorize('manage',$community);
        $community->joinRequests()->detach();
        $community->users()->detach();
        $community->categories()->detach();
        $community->puzzles()->delete();
        $community->delete();
        return response()->json(['message' => 'Community successfully deleted'], 200);
    }
    public function getJoinRequests(Request $request,$id){
        $community=Community::findOrFail($id);
        $this->authorize('manage',$community);
        $users=$community->joinRequests()->orderBy('created_at', 'desc')->get();
        return UserResource::collection($users);
        }
}
