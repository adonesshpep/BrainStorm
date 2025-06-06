<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PuzzleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avatars=config('categoryavatars.default');
        $category=null;
        if($this->category_id){
            $category=Category::where('id',$this->category_id)->first();
        }
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'question'=>$this->question,
            'level'=>$this->level,
            'votes_up'=>$this->votes_up,
            'votes_down'=>$this->votes_down,
            'user_id'=>$this->user_id,
            'category_id'=>$this->category_id,
            'community_id'=>$this->community_id,
            'image_url'=> Storage::url(($this->image_path)??($category?$avatars[$category->avatar_id]:$avatars[1]))
        ];
    }
}
