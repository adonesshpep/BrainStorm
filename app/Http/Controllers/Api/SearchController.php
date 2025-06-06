<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommunityResource;
use App\Http\Resources\UserResource;
use App\Models\Community;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request){
        $att=$request->validate([
            'data'=>'required|string|max:255'
        ]);
        $communitiesnanoid=Community::where('nanoid',$att['data'])->get();
        $communitiesname=Community::where('name', 'LIKE', "%{$att['data']}%")->where('private',false)->get();
        $communities=$communitiesname->merge($communitiesnanoid);
        $users=User::where('name','LIKE', "%{$att['data']}%")->orWhere('nanoid',$att['data'])->get();
        return response()->json([
            'message'=>'your search result',
            'communities'=>CommunityResource::collection($communities),
            'users'=>UserResource::collection($users)
        ]);
    }
}
