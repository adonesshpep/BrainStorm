<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                $request->user()->joinRequests()->syncWithPivotValues($community->id, [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                JoinRequestSubmitted::dispatch($request->user(),$community);
                return response()->json(['message'=>'your request is submitted'],200);
            }
        }else{
            $request->user()->communities()->attach($community);
            $community->size+=1;
            $community->save();
            return response()->json(['message'=> 'Congrats,you are in the community'], 200);
        }
    }
    public function store(Request $request){
        $atts=$request->validate([
            'name'=>'required|string|max:255|unique:communities,name',
            'description'=>'nullable|string|max:1000',
            'private'=>'nullable|boolean',
            'joining_requires_admin_approval'=>'nullable|boolean',
            'puzzles_require_admin_approval'=>'nullable|boolean',
            'only_admin_can_post'=>'nullable|boolean',
            'category_ids'=>'nullable|array',
            'category_ids.*'=>'exists:categories,id'
        ]);
        $client=new Client();
        do {
            $nanoId = $client->generateId(12);
        } while (Community::where('nanoid', $nanoId)->exists());

        $community=Community::create([
            'nanoid'=>$nanoId,
            'name'=>$atts['name'],
            'description'=>$atts['description'] ?? '',
            'private'=>$atts['private']??false,
            'joining_requires_admin_approval'=>$atts['joining_requires_admin_approval']??false,
            'puzzles_require_admin_approval'=>$atts['puzzles_require_admin_approval']??false,
            'only_admin_can_post'=>$atts['only_admin_can_post']??false,
            'admin_id'=>$request->user()->id,
            'size'=>1
        ]);
        $community->users()->attach($request->user()->id);
        if(isset($atts['category_ids'])){
            if(!in_array(1,$atts['category_ids'])){
                $atts['category_ids'][]=1;
            }
            $community->categories()->attach($atts['category_ids']);
        }else{
            $community->categories()->attach(1);
        }
        return response()->json(['message' => 'Community created successfully!', 'community' => $community], 201);
    }
    public function leave(Request $request,$id){
        $community=Community::findOrFail($id);
        if(!$request->user()->communities()->where('community_id',$community->id)->exists()){
            return response()->json(['message'=>'you are not in this community in the first place'],400);
        }elseif($request->user()->id===$community->admin_id){
            return response()->json(['message'=>'you are the admin you cannot leave the community you can deleted instade'],400);
        }
        else{
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
            $community->joinRequests()->detach($joinRequest);
            return response()->json(['message'=>'request was denied']);
        }else{
            $community->users()->attach($request->requester_id);
            $community->joinRequests()->detach($joinRequest);
            return response()->json(['message'=>'request was approved']);
        }
    }
    public function destroy(Request $request,$id){
        $community=Community::findOrFail($id);
        $this->authorize('manage',$community);
        //just added cascade instade
        // $community->joinRequests()->detach();
        // $community->users()->detach();
        // $community->categories()->detach();
        // $community->puzzles()->delete();
        $community->delete();
        return response()->json(['message' => 'Community successfully deleted'], 200);
    }
    public function getJoinRequests(Request $request,$id){
        $community=Community::findOrFail($id);
        $this->authorize('manage',$community);
        $users=$community->joinRequests()->orderBy('created_at','desc')->get();
        return UserResource::collection($users);
        }
        public function update(Request $request,$id){
        $atts = $request->validate([
            'name' => 'nullable|string|max:255|unique:communities,name',
            'description' => 'nullable|string|max:1000',
            'private' => 'nullable|boolean',
            'joining_requires_admin_approval' => 'nullable|boolean',
            'puzzles_require_admin_approval' => 'nullable|boolean',
            'only_admin_can_post' => 'nullable|boolean',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id'
        ]);
        $community=Community::findOrFail($id);
        $this->authorize('manage',$community);
        if(isset($atts['category_ids'])){
            if (!in_array(1, $atts['category_ids'])) {
                $atts['category_ids'][] = 1;
            }
        $community->categories()->sync($atts['category_ids']);
        }
        $community->update([
            'name'=>$atts['name']??$community->name,
            'description'=>$atts['description']??$community->description?:'No Description Availabe',
            'private'=>$atts['private']??$community->private,
            'puzzles_require_admin_approval'=>$atts['puzzles_require_admin_approval']??$community->puzzles_require_admin_approval,
            'only_admin_can_post'=>$atts['only_admin_can_post']??$community->only_admin_can_post,
            'joining_requires_admin_approval'=>$atts['joining_requires_admin_approval']??$community->joining_requires_admin_approval,
        ]);
        return response()->json(['message'=>'community has beed updated'],200);
        }
}
